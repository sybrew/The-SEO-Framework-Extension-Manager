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
final class WordPress_SEO extends Base {

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
		$transformer_class = \get_class(
			\TSF_Extension_Manager\Extension\Transport\Transformers\WordPress_SEO::get_instance()
		);

		/**
		 * [ $from_table, $from_index ]
		 * [ $to_table, $to_index ]
		 * $transformer
		 * $sanitizer
		 * $transmuter
		 * $cb_after_loop
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
						[ $this, '_get_wpseo_transport_term_ids' ],
						[ $this, '_get_wpseo_term_transport_value' ],
					],
					'to'      => [
						null,
						[ $this, '_wpseo_term_meta_transmuter' ],
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
							'wpseo_noindex'               => [ $transformer_class, '_robots_text_to_qubit' ], // also sanitizes
							'wpseo_opengraph-title'       => [ $transformer_class, '_title_syntax' ], // also sanitizes
							'wpseo_opengraph-description' => [ $transformer_class, '_description_syntax' ], // also sanitizes
							'wpseo_opengraph-image'       => '\\esc_url_raw',
							'wpseo_opengraph-image-id'    => '\\absint',
							'wpseo_twitter-title'         => [ $transformer_class, '_title_syntax' ], // also sanitizes
							'wpseo_twitter-description'   => [ $transformer_class, '_description_syntax' ], // also sanitizes
						],
					],
				],
				[ $this, '_term_meta_option_cleanup' ],
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
	private function get_wpseo_taxonomy_meta() {
		static $data;
		return $data ?? ( $data = \get_option( 'wpseo_taxonomy_meta' ) ?: [] );
	}

	/**
	 * Obtains ids from Yoast SEO's taxonomy metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Any useful data pertaining to the current transmutation type.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	protected function _get_wpseo_transport_term_ids( $data ) {

		$ids = [];

		foreach ( $this->get_wpseo_taxonomy_meta() as $taxonomy => $data )
			$ids = array_merge( $ids, array_keys( $data ) );

		return $ids;
	}

	/**
	 * Returns existing advanced robots values.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param array  $actions The actions for and after transmuation, passed by reference.
	 * @param array  $results The results before and after transmuation, passed by reference.
	 * @param ?array $cleanup The extraneous database indexes to clean up, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	protected function _get_wpseo_term_transport_value( $data, &$actions, &$results, &$cleanup ) {

		// No need to access the index a dozen times, store pointer in var.
		$item_id = &$data['item_id'];

		// Walk until index is found. We are unaware of taxonomies during transportation.
		foreach ( $this->get_wpseo_taxonomy_meta() as $taxonomy => $meta ) {
			if ( \array_key_exists( $item_id, $meta ) ) {
				$transport_value = $meta[ $item_id ];
				break;
			}
		}

		return $transport_value ?? null;
	}

	/**
	 * Transmutes Yoast SEO Meta to TSF's serialized metadata.
	 *
	 * @since 1.0.0
	 * @generator
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function _wpseo_term_meta_transmuter( $data, &$actions, &$results ) {

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

		yield 'transmutedResults' => [ $results, $actions ];
	}

	/**
	 * Cleans Yoast SEO Meta from the database.
	 *
	 * @since 1.0.0
	 * @generator
	 *
	 * @param array $item_ids The term IDs looped over.
	 * @return ?void Early when option is not registered.
	 */
	protected function _term_meta_option_cleanup( $item_ids ) {

		// If no items are looped over, test if the option even exists before deleting.
		// If option is false, $item_ids is always empty, but not vice versa. Saved db call.
		if ( ! $item_ids ) {
			global $wpdb;

			if ( null === $wpdb->get_var(
				$wpdb->prepare(
					"SELECT option_value FROM `$wpdb->options` WHERE option_name = %s",
					'wpseo_taxonomy_meta'
				)
			) ) return;
		}

		yield 'afterResults' => [
			// This also invokes all necessary cache clearning. This function runs only once.
			(bool) \delete_option( 'wpseo_taxonomy_meta' ), // assert success
			[ // onsuccess
				'message' => \__( 'Cleanup: Deleted old term meta successfully.', 'the-seo-framework-extension-manager' ),
				'addTo'   => 'deleted', // writes variable, must never be untrusted
				'count'   => \count( $item_ids ), // Success "count."
			],
			[ // onfailure
				'message' => \__( 'Cleanup: Failed to delete old term meta.', 'the-seo-framework-extension-manager' ),
				'addTo'   => 'failed', // writes variable, must never be untrusted
				'count'   => 1, // "failure" count
			],
		];
	}
}
