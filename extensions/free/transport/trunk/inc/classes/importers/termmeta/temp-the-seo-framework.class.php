<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\TermMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Importer for TSF from Yoast SEO.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits abstract setup_vars.
 */
final class Temp_The_SEO_Framework extends Base {

	/**
	 * Sets up variables.
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	protected function setup_vars() {
		global $wpdb;

		$transformer_class = \get_class(
			\TSF_Extension_Manager\Extension\Transport\Transformers\Temp_TSF::get_instance()
		);

		/**
		 * [ $from_table, $from_index ]
		 * [ $to_table, $to_index ]
		 * $transformer
		 * $sanitizer
		 * $transmuter
		 */
		$this->conversion_sets = [
			[
				[ $wpdb->postmeta, '_genesis_title' ],
				[ null, '_yoast_wpseo_title' ],
			],
			[
				[ $wpdb->postmeta, '_genesis_description' ],
				[ $wpdb->postmeta, '_yoast_wpseo_metadesc' ],
			],
			[
				[ $wpdb->postmeta, '_genesis_noindex' ],
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-noindex' ],
				[ $transformer_class, '_robots_unqubit' ],
			],
			[
				[ $wpdb->postmeta, '_genesis_nofollow' ],
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-nofollow' ],
				[ $transformer_class, '_robots_unqubit' ],
			],
			[
				null,
				[ $wpdb->postmeta, '_yoast_wpseo_meta-robots-adv' ],
				null, // [ $transformer_class, '_robots_advanced' ],
				null,
				[
					'name'      => 'Robots Advanced',
					'from'      => [
						[ $this, '_robots_adv_transmuter_postids' ],
						[ $this, '_robots_adv_transmuter' ],
					],
					'from_data' => [
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
				[ $wpdb->postmeta, '_genesis_canonical_uri' ],
				[ $wpdb->postmeta, '_yoast_wpseo_canonical' ],
				'\\esc_url_raw',
				null,
			],
			[
				[ $wpdb->postmeta, 'redirect' ],
				[ $wpdb->postmeta, '_yoast_wpseo_redirect' ],
				'\\esc_url_raw',
				null,
			],
			[
				[ $wpdb->postmeta, '_open_graph_title' ],
				[ $wpdb->postmeta, '_yoast_wpseo_opengraph-title' ],
				[ $transformer_class, '_title_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_open_graph_description' ],
				[ $wpdb->postmeta, '_yoast_wpseo_opengraph-description' ],
				[ $transformer_class, '_description_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_twitter_title' ],
				[ $wpdb->postmeta, '_yoast_wpseo_twitter-title' ],
				[ $transformer_class, '_title_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_twitter_description' ],
				[ $wpdb->postmeta, '_yoast_wpseo_twitter-description' ],
				[ $transformer_class, '_description_syntax' ],
				null,
			],
			[
				[ $wpdb->postmeta, '_twitter_description' ],
				[ $wpdb->postmeta, '_yoast_wpseo_twitter-description' ],
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
	 * @param mixed $data Any useful data pertaining to the current transmutation type.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	public function _robots_adv_transmuter_postids( $data ) {
		global $wpdb;

		[ $from_table, $from_index ] = array_map( '\\esc_sql', $data['from_data']['transmuters']['noarchive'] );

		$post_ids = $wpdb->get_col( $wpdb->prepare(
			// "SELECT DISTINCT post_id FROM `$from_table` WHERE meta_key = %s",
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table is escaped.
			"SELECT post_id FROM `$from_table` WHERE meta_key = %s", // No "DISTINCT", show "skipped" and explain in FAQ what it means.
			$from_index
		) ) ?: [];
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $post_ids;
	}

	/**
	 * Transmutes single value to comma-separated data.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param mixed  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @param ?array $cleanup The indexes to clean up when done, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return string|null The new robots value if set, otherwise null.
	 */
	public function _robots_adv_transmuter( $data, &$actions, &$results, &$cleanup ) {
		global $wpdb;

		[ $from_table, $from_index ] = array_map( '\\esc_sql', $data['from_data']['transmuters']['noarchive'] );

		// This isn't the right use-case for this type of transmuter.

		$transport_value = $wpdb->get_var( $wpdb->prepare(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $to_table is escaped.
			"SELECT meta_value FROM `$from_table` WHERE post_id = %d AND meta_key = %s",
			$data['item_id'],
			$from_index
		) );
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		// We omit this very much later, and therefore, all code below is useless. See notes below.
		// if ( $data['existing_value'] )
		// 	$actions['transport'] = false;

		// Actually, we do not want to merge, but exit here. This is a proof of concept, though.
		// All code below is not a real-world analogue, yet for testing purposes only.
		if ( isset( $transport_value ) ) {
			$transport_value = \call_user_func(
				$data['from_data']['transformers']['noarchive'],
				$transport_value
			);

			$cleanup[] = [ $from_table, $from_index ];
		}

		$set_value = $data['existing_value'] ? implode(
			',',
			array_unique( array_merge(
				explode( ',', $data['existing_value'] ),
				array_filter( [ $transport_value ] )
			) )
		) : $transport_value;

		$results['transformed'] = $set_value !== $data['existing_value'];

		return $set_value;
	}
}
