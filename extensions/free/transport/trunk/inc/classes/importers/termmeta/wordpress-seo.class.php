<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\TermMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * Copyright (C) 2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

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

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment -- deeply nested is still simple here.

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
				null,
				[ $wpdb->termmeta, THE_SEO_FRAMEWORK_TERM_OPTIONS ],
				null,
				null,
				[
					'name'    => 'Yoost Term Meta',
					'from' => [
						[ $this, '_extract_ids_from_wpseo_taxonomy_meta' ],
						[ $this, '_extract_data_from_wpseo_taxonomy_meta' ],
					],
					'to'      => [
						[ $this, '_term_meta_existing' ],
						[ $this, '_term_meta_transmuter' ],
					],
					'to_data' => [
						'transmuters'  => [
							'wpseo_title'                 => 'doctitle',
							'wpseo_desc'                  => 'description',
							'wpseo_canonical'             => 'canonical',
							'wpseo_noindex'               => 'noindex',
							'wpseo_opengraph-title'       => 'og_title',
							'wpseo_opengraph-description' => 'og_description',
							'wpseo_opengraph-image'       => 'social_image_url',
							'wpseo_opengraph-image-id'    => 'social_image_id',
							'wpseo_twitter-title'         => 'tw_title',
							'wpseo_twitter-description'   => 'tw_description',
						],
						'transformers' => [
							'wpseo_title'                 => [ $transformer_class, '_title_syntax' ], // also sanitizes
							'wpseo_desc'                  => [ $transformer_class, '_description_syntax' ], // also sanitizes
							'wpseo_canonical'             => '\\esc_url_raw',
							'wpseo_noindex'               => [ $transformer_class, '_robots_term' ], // also sanitizes
							'wpseo_opengraph-title'       => [ $transformer_class, '_title_syntax' ], // also sanitizes
							'wpseo_opengraph-description' => [ $transformer_class, '_description_syntax' ], // also sanitizes
							'wpseo_opengraph-image'       => '\\esc_url_raw',
							'wpseo_opengraph-image-id'    => '\\absint',
							'wpseo_twitter-title'         => [ $transformer_class, '_title_syntax' ], // also sanitizes
							'wpseo_twitter-description'   => [ $transformer_class, '_description_syntax' ], // also sanitizes
						],
					],
				],
			],
		];
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
	}

	/**
	 * Returns data from wpseo_taxonomy_meta.
	 *
	 * @since 1.0.0
	 *
	 * @return array Yoast SEO data: {
	 *     string $taxonomy => array $terms {
	 *        int $term_id => array $meta {
	 *           ?string $meta_index => ?mixed $meta_value
	 *        }
	 *     }
	 * }
	 */
	private function get_yoast_meta() {
		static $data;
		return $data ?? ( $data = \get_option( 'wpseo_taxonomy_meta' ) ?: [] );
	}

	/**
	 * Obtains ids from Yoast SEO's taxonomy metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data Any useful data pertaining to the current transmutation type.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	public function _extract_ids_from_wpseo_taxonomy_meta( $data ) {

		$ids = [];

		foreach ( $this->get_yoast_meta() as $taxonomy => $data )
			$ids = array_merge( $ids, array_keys( $data ) );

		return $ids;
	}

	/**
	 * Gets existing advanced robots values.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $data    Any useful data pertaining to the current transmutation type.
	 * @param array  $actions The actions for and after transmuation, passed by reference.
	 * @param array  $results The results before and after transmuation, passed by reference.
	 * @param ?array $cleanup The extraneous database indexes to clean up, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	public function _extract_data_from_wpseo_taxonomy_meta( $data, &$actions, &$results, &$cleanup ) {

		if ( \is_null( $data['existing_value'] ) ) {
			// No need to access the index a dozen time times, store pointer in var.
			$item_id = &$data['item_id'];

			$meta = $this->get_yoast_meta();
			// Walk until index is found. We are unaware of taxonomies during transportation.
			foreach ( $meta as $taxonomy => $data ) {
				if ( \array_key_exists( $item_id, $data ) ) {
					$transport_value = $meta[ $taxonomy ][ $item_id ];
					break;
				}
			}
		}

		return $transport_value ?? null;
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
	public function _term_meta_existing( $data ) {
		global $wpdb;

		// Defined at $this->conversion_sets
		[ $to_table, $to_index ] = $data['to'];

		$existing_value = $wpdb->get_var( $wpdb->prepare(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $to_table is escaped.
			"SELECT meta_value FROM `$to_table` WHERE `{$this->id_key}` = %d AND meta_key = %s",
			$data['item_id'],
			$to_index
		) );
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $existing_value;
	}

	/**
	 * Transmutes comma-separated advanced robots to a single value.
	 *
	 * @since 1.0.0
	 * @generator
	 *
	 * @param mixed  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	public function _term_meta_transmuter( $data, &$actions = null, &$results = null ) {

		[ $from_table, $from_index ] = $data['from'];
		[ $to_table, $to_index ]     = $data['to'];

		$_set_value = [];

		// Nothing to do here, TSF already has value set. Skip to next item.
		if ( ! $actions['transport'] ) goto useless;

		foreach ( $data['to_data']['transmuters'] as $from => $to ) {
			$__pre_transform_value = $data['set_value'][ $from ] ?? null;

			if ( \in_array( $__pre_transform_value, $this->useless_data, true ) ) continue;

			$_set_value[ $to ] = \call_user_func_array(
				$data['to_data']['transformers'][ $from ],
				[
					$__pre_transform_value,
					$data['item_id'],
					$this->type,
					[ $from_table, $from_index ],
					[ $to_table, $to_index ],
				]
			);

			if ( \in_array( $_set_value[ $to ], $this->useless_data, true ) ) {
				unset( $_set_value[ $to ] );
			} else {
				// We actually only read this as boolean. Still, might be fun later.
				$results['transformed'] += (int) ( $__pre_transform_value !== $_set_value[ $to ] );
			}
		}

		if ( \in_array( $_set_value, $this->useless_data, true ) ) {
			useless:;
			$_set_value             = null;
			$actions['transport']   = false;
			$results['transformed'] = 0;
		}

		$this->transmute(
			$_set_value,
			$data['item_id'],
			[ $from_table, $from_index ], // Should be [ null, null ]
			[ $to_table, $to_index ],
			$actions,
			$results,
		);

		if ( ! $results['updated'] )
			$results['transformed'] = 0;

		// Gotta be a generator, tick.
		yield 'transmutedResults' => [ $results, $actions, $data['item_id'] ];
	}
}
