<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Importer for Yoast SEO.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits abstract setup_vars.
 */
final class WordPress_SEO extends Core {

	/**
	 * Sets up variables.
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	protected function setup_vars() {
		global $wpdb;

		// Construct and fetch classname.
		$transformer_class = \get_class( new \TSF_Extension_Manager\Extension\Transport\Transformers\WordPress_SEO_Transformer );

		/**
		 * [ $from_table, $from_index ]
		 * [ $to_table, $to_index ]
		 * $transformer
		 * $sanitizer
		 * $transmuter
		 */
		$this->conversion_sets = [
			[
				[ $wpdb->postmeta, '_yoast_wpseo_title' ],
				[ $wpdb->postmeta, '_genesis_title' ],
				[ $transformer_class, '_title_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_metadesc' ],
				[ $wpdb->postmeta, '_genesis_description' ],
				[ $transformer_class, '_description_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-noindex' ],
				[ $wpdb->postmeta, '_genesis_noindex' ],
				[ $transformer_class, '_robots_qubit' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-nofollow' ],
				[ $wpdb->postmeta, '_genesis_nofollow' ],
				[ $transformer_class, '_robots_qubit' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-adv' ],
				null,
				null,
				null,
				[
					'name'    => 'Robots Advanced',
					'to'      => [
						[ $this, '_robots_adv_transmuter_existing' ],
						[ $this, '_robots_adv_transmuter' ],
					],
					'to_data' => [
						'transmuters'  => [
							'noarchive' => [ $wpdb->postmeta, '_genesis_noarchive' ],
						],
						'transformers' => [
							'noarchive' => [ $transformer_class, '_robots_advanced' ], // also sanitizes
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_canonical' ],
				[ $wpdb->postmeta, '_genesis_canonical_uri' ],
				null,
				'\\esc_url_raw',
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_redirect' ],
				[ $wpdb->postmeta, 'redirect' ],
				null,
				'\\esc_url_raw',
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_opengraph-title' ],
				[ $wpdb->postmeta, '_open_graph_title' ],
				[ $transformer_class, '_title_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_opengraph-description' ],
				[ $wpdb->postmeta, '_open_graph_description' ],
				[ $transformer_class, '_description_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_twitter-title' ],
				[ $wpdb->postmeta, '_twitter_title' ],
				[ $transformer_class, '_title_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_twitter-description' ],
				[ $wpdb->postmeta, '_twitter_description' ],
				[ $transformer_class, '_description_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_content_score' ], // delete
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_wordproof_timestamp' ], // delete
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_estimated-reading-time-minutes' ], // delete
			],
		];

		foreach ( $this->get_taxonomy_list_with_pt_support() as $_taxonomy ) {
			$this->conversion_sets += [
				[ $wpdb->postmeta, "_yoast_wpseo_primary-{$_taxonomy}" ],
				[ $wpdb->postmeta, "_primary_term_{$_taxonomy}" ],
				null,
				null,
				'absint',
			];
		}
	}

	/**
	 * Gets existing advanced robots values.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param mixed $data Any useful data pertaining to the current transmutation type.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	public function _robots_adv_transmuter_existing( $data ) {
		global $wpdb;

		$ret = [];

		foreach ( [
			'noarchive',
			// 'noimageindex', // reserved for later
			// 'nosnippet', // reserved for later
		] as $type ) {
			[ $to_table, $to_index ] = array_map( '\\esc_sql', $data['to_data']['transmuters'][ $type ] );

			$current_value = $wpdb->get_var( $wpdb->prepare(
				// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $to_table is escaped.
				"SELECT meta_value FROM `$to_table` WHERE post_id = %d AND meta_key = %s",
				$data['post_id'],
				$to_index
			) );
			if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

			if ( $current_value )
				$ret[ $type ] = $current_value;
		}

		if ( $ret ) {
			// Convert new data to Yoast-esque data.
			$ret = implode( ',', array_keys( $ret ) );
		}

		// If no current data was found, fall back to default "from".
		return $ret ?: null;
	}

	/**
	 * Transmutes comma-separated advanced robots to a single value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	public function _robots_adv_transmuter( $data, &$actions = null, &$results = null ) {

		[ $from_table, $from_index ] = $data['from'];

		$transmutations = \count( $data['to_data']['transmuters'] );
		$i              = 0;

		foreach ( $data['to_data']['transmuters'] as $type => $transmuter ) {
			$i++;

			[ $to_table, $to_index ] = array_map( '\\esc_sql', $transmuter );

			$_actions = $actions;
			$_results = $results;

			$_set_value = false !== strpos( $data['set_value'], $type ) ? $type : null;

			$_actions['transport'] = (bool) $_set_value;
			$_actions['transform'] = (bool) $_set_value;
			$_actions['delete']    = $i === $transmutations; // Delete only if completed.

			if ( $_actions['transform'] ) {
				$__pre_transform_value = $_set_value;

				$_set_value = \call_user_func_array(
					$data['to_data']['transformers'][ $type ],
					[
						$_set_value,
						$data['post_id'],
						[ $from_table, $from_index ],
						[ $to_table, $to_index ],
					]
				);

				$_results['transformed'] = $__pre_transform_value !== $_set_value;
			}

			if ( \in_array( $_set_value, $this->useless_data, true ) ) {
				$_set_value              = null;
				$_results['transformed'] = false;
				$_actions['transport']   = false;
			}

			$this->transmute(
				$_set_value,
				$data['post_id'],
				[ $from_table, $from_index ],
				[ $to_table, $to_index ],
				$_actions,
				$_results
			);

			yield 'transmutedResults' => [ $_results, $_actions, $data['post_id'] ];
		}
	}
}
