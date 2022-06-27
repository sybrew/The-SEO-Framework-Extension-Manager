<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Importer for Yoost SEO.
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

		// 'supports'  => [
		// 	'title',
		// 	'description',
		// 	'canonical_url',
		// 	'noindex',
		// 	'nofollow',
		// 	'noarchive',
		// 	'og_title',
		// 	'og_description',
		// 	'twitter_title',
		// 	'twitter_description',
		// 	'og_image',
		// 	'article_type',
		// ],

		// 'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
		// 	'title',
		// 	'description',
		// 	'noindex',
		// 	'nofollow',
		// 	'noarchive',
		// 	'og_title',
		// 	'og_description',
		// 	'twitter_title',
		// 	'twitter_description',
		// ],

		$transformer_class = \TSF_Extension_Manager\Extension\Transport\Transformers\WordPress_SEO_Transformer::class;

		// var_dump() TODO primary term... _yoast_wpseo_primary_category -> _yoast_wpseo_primary_product_cat, etc.
		// We should get the supported cats from TSF and generate the keys here.

		/**
		 * [ $from_table, $from_index ]
		 * [ $to_table, $to_index ]
		 * $transformer
		 */
		$this->conversion_sets = [
			[
				[ $wpdb->postmeta, '_yoast_wpseo_title' ],
				[ $wpdb->postmeta, '_genesis_title' ],
				[ $transformer_class, '_title_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_metadesc' ],
				[ $wpdb->postmeta, '_genesis_description' ],
				[ $transformer_class, '_description_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-noindex' ],
				[ $wpdb->postmeta, '_genesis_noindex' ],
				[ $transformer_class, '_robots_qubit' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-nofollow' ],
				[ $wpdb->postmeta, '_genesis_nofollow' ],
				[ $transformer_class, '_robots_qubit' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-adv' ],
				null,
				null,// [ $transformer_class, '_robots_qubit' ],
				[
					'name'    => 'Robots Advanced',
					'to'      => [ $this, '_robots_adv_transmuter' ],
					'to_data' => [
						'transmuters'  => [
							'noarchive' => [ $wpdb->postmeta, '_genesis_noarchive' ],
						],
						'transformers' => [
							'noarchive' => [ $transformer_class, '_robots_advanced' ],
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_canonical' ],
				[ $wpdb->postmeta, '_genesis_canonical_uri' ],
				'\\esc_url_raw', // var_dump() sanitize_url, is this necessary -> yes.
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_redirect' ],
				[ $wpdb->postmeta, 'redirect' ],
				'\\esc_url_raw', // var_dump() sanitize_url, is this necessary -> yes.
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_opengraph-title' ],
				[ $wpdb->postmeta, '_open_graph_title' ],
				[ $transformer_class, '_title_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_opengraph-description' ],
				[ $wpdb->postmeta, '_open_graph_description' ],
				[ $transformer_class, '_description_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_twitter-title' ],
				[ $wpdb->postmeta, '_twitter_title' ],
				[ $transformer_class, '_title_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_yoast_wpseo_twitter-description' ],
				[ $wpdb->postmeta, '_twitter_description' ],
				[ $transformer_class, '_description_syntax' ],
				null,
			],
		];
	}

	/**
	 * Transmutes comma-separated advanced robots to a single value.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param string $type    The type of transmutation.
	 * @param mixed  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	public function _robots_adv_transmuter( $type, $data, &$actions = null, &$results = null ) {
		global $wpdb;

		switch ( $type ) {
			case 'get:existing_value:to':
				[ $to_table, $to_index ] = array_map( '\\esc_sql', $data['to_data']['transmuters']['noarchive'] );

				$current_value = $wpdb->get_var( $wpdb->prepare(
					// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $to_table is escaped.
					"SELECT meta_value FROM `$to_table` WHERE post_id = %d AND meta_key = %s",
					$data['post_id'],
					$to_index
				) );
				if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

				return $current_value;

			case 'transmute:set_value:to':
				[ $from_table, $from_index ] = $data['from'];

				foreach ( $data['to_data']['transmuters'] as $trans_type => $transmuter ) {
					[ $to_table, $to_index ] = array_map( '\\esc_sql', $transmuter );

					if ( $actions['transform'] ) {
						switch ( $trans_type ) {
							case 'noarchive':
							// case 'noimageindex': // reserved for later.
							// case 'nosnippet':    // reserved for later.
								$_pre_transform_value = strpos( $trans_type, $_pre_transform_value ) ? $trans_type : '';
								break;
							default:
								break;
						}

						$_pre_transform_value = $data['set_value'];

						$data['set_value'] = /*yield from*/ \call_user_func_array(
							$data['to_data']['transformers'][ $trans_type ],
							[
								$data['set_value'],
								$data['post_id'],
								[ $from_table, $from_index ],
								[ $to_table, $to_index ],
							]
						);

						$actions['transformed'] = $_pre_transform_value !== $data['set_value'];
					}

					$_actions = $actions;
					$_results = $results;

					$this->transmute(
						$data['set_value'],
						$data['post_id'],
						[ $from_table, $from_index ],
						[ $to_table, $to_index ],
						$_actions,
						$_results
					);

					yield 'transmutedResults' => [ $_results, $_actions, $data['post_id'] ];
				}
				break;
		}
	}
}
