<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\TermMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * copyright (C) 2022-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Importer for Rank Math.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits abstract setup_vars.
 */
final class SEO_By_Rank_Math extends Base {

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
			\TSF_Extension_Manager\Extension\Transport\Transformers\SEO_By_Rank_Math::get_instance()
		);

		$tsf = \tsf();

		/**
		 * NOTE: I considered making a separate transaction for each term meta entry
		 * from Rank Math, and merge each new value into the "existing" serialized
		 * array for TSF. However, in doing so, we must keep a list of what has
		 * yet to be transmuted. This list can grow in massive proportions, not suitable
		 * for storing in temp. Therefore, I opted for the more complex custom
		 * transmutation route: fetch IDs containing ANY data, then grab ALL data for each
		 * term ID, and merge into TSF's meta.
		 */

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
				[ $wpdb->termmeta, \THE_SEO_FRAMEWORK_TERM_OPTIONS ],
				null,
				null,
				[
					'name'    => 'Rank Math Term Meta',
					'from' => [
						[ $this, '_get_populated_term_ids' ],
						[ $this, '_get_congealed_transport_value' ],
					],
					'from_data' => [
						'table'   => $wpdb->termmeta,
						'indexes' => [
							'rank_math_title',
							'rank_math_description',
							'rank_math_facebook_title',
							'rank_math_facebook_description',
							'rank_math_facebook_image',
							'rank_math_facebook_image_id',
							'rank_math_twitter_use_facebook',
							'rank_math_twitter_title',
							'rank_math_twitter_description',
							'rank_math_canonical_url',
							'rank_math_robots',
						],
					],
					'to'      => [
						null,
						[ $this, '_term_meta_transmuter' ],
					],
					'to_data' => [
						'pretransmute' => [
							'rank_math_twitter_use_facebook' => [
								'cb'   => [ $this, '_rank_math_pretransmute_twitter' ],
								'data' => [
									'test_value' => 'rank_math_twitter_use_facebook',
									// If off, do use own data. If on, don't use own data. See,
									'isnot'      => 'off', // if on, then unset; also means if absent, don't unset.
									'unset'      => [
										'rank_math_twitter_title',
										'rank_math_twitter_description',
									],
								],
							],
							'rank_math_robots'               => [
								'cb'   => [ $this, '_rank_math_pretransmute_robots' ],
								'data' => [
									'rank_math_robots' => [
										// new index => from recognized tag
										'_rm_transm_robots_noindex'   => 'noindex',
										'_rm_transm_robots_nofollow'  => 'nofollow',
										'_rm_transm_robots_noarchive' => 'noarchive',
									],
								],
							],
						],
						'transmuters'  => [
							'rank_math_title'                => 'doctitle',
							'rank_math_description'          => 'description',
							'rank_math_facebook_title'       => 'og_title',
							'rank_math_facebook_description' => 'og_description',
							'rank_math_facebook_image'       => 'social_image_url',
							'rank_math_facebook_image_id'    => 'social_image_id',
							'rank_math_twitter_title'        => 'tw_title',
							'rank_math_twitter_description'  => 'tw_description',
							'rank_math_canonical_url'        => 'canonical',
							'_rm_transm_robots_noindex'      => 'noindex',
							'_rm_transm_robots_nofollow'     => 'nofollow',
							'_rm_transm_robots_noarchive'    => 'noarchive',
						],
						'transformers' => [
							'rank_math_title'                 => [ $transformer_class, '_title_syntax' ],
							'rank_math_description'           => [ $transformer_class, '_description_syntax' ],
							'rank_math_facebook_title'        => [ $transformer_class, '_title_syntax' ],
							'rank_math_facebook_description'  => [ $transformer_class, '_description_syntax' ],
							'rank_math_twitter_title'         => [ $transformer_class, '_title_syntax' ],
							'rank_math_twitter_description'   => [ $transformer_class, '_description_syntax' ],
							'_rm_transm_robots_noindex'       => [ $transformer_class, '_robots_text_to_qubit' ], // also sanitizes
							'_rm_transm_robots_nofollow'      => [ $transformer_class, '_robots_text_to_qubit' ], // also sanitizes
							'_rm_transm_robots_noarchive'     => [ $transformer_class, '_robots_text_to_qubit' ], // also sanitizes
						],
						'sanitizers' => [
							'rank_math_title'                 => [ $tsf, 's_title_raw' ],
							'rank_math_description'           => [ $tsf, 's_description_raw' ],
							'rank_math_facebook_title'        => [ $tsf, 's_title_raw' ],
							'rank_math_facebook_description'  => [ $tsf, 's_description_raw' ],
							'rank_math_facebook_image'        => '\\esc_url_raw',
							'rank_math_facebook_image_id'     => '\\absint',
							'rank_math_twitter_title'         => [ $tsf, 's_title_raw' ],
							'rank_math_twitter_description'   => [ $tsf, 's_description_raw' ],
							'rank_math_canonical_url'         => '\\esc_url_raw',
						],
						'cleanup' => [
							[ $wpdb->termmeta, 'rank_math_title' ],
							[ $wpdb->termmeta, 'rank_math_description' ],
							[ $wpdb->termmeta, 'rank_math_facebook_title' ],
							[ $wpdb->termmeta, 'rank_math_facebook_description' ],
							[ $wpdb->termmeta, 'rank_math_facebook_image' ],
							[ $wpdb->termmeta, 'rank_math_facebook_image_id' ],
							[ $wpdb->termmeta, 'rank_math_twitter_use_facebook' ],
							[ $wpdb->termmeta, 'rank_math_twitter_title' ],
							[ $wpdb->termmeta, 'rank_math_twitter_description' ],
							[ $wpdb->termmeta, 'rank_math_canonical_url' ],
							[ $wpdb->termmeta, 'rank_math_robots' ],
						],
					],
				],
			],
			[
				[ $wpdb->termmeta, 'rank_math_facebook_enable_image_overlay' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_facebook_image_overlay' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_twitter_image' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_twitter_image_id' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_twitter_card_type' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_twitter_enable_image_overlay' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_twitter_image_overlay' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_advanced_robots' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_breadcrumb_title' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_focus_keyword' ], // delete
			],
		];
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
	}

	/**
	 * Pretransmutes Rank Math robots value by splitting it for later transmutation.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data      The pretransmutation data ('cbdata').
	 * @param array  $set_value The current $set_value data used for actual transmuation, passed by reference.
	 * @param ?array $actions   The actions for and after transmuation, passed by reference.
	 * @param ?array $results   The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when \WP_DEBUG is enabled.
	 */
	protected function _rank_math_pretransmute_robots( $data, &$set_value, &$actions, &$results ) {

		foreach ( $data as $original_key => $subkeys ) {
			$robots = isset( $set_value[ $original_key ] )
				? (
					\is_serialized( $set_value[ $original_key ] )
						? $this->maybe_unserialize_no_class( $set_value[ $original_key ], false )
						: null
				)
				: null;

			if ( ! $robots ) continue;

			$set_value = array_merge(
				$set_value,
				// This makes:  $set_value[ recognized tag ] = new index
				array_intersect( $subkeys, $robots )
			);
		}
	}

	/**
	 * Pretransmutes Rank Math Twitter value by testing whether user used values.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data      The pretransmutation data ('cbdata').
	 * @param array  $set_value The current $set_value data used for actual transmuation, passed by reference.
	 * @param ?array $actions   The actions for and after transmuation, passed by reference.
	 * @param ?array $results   The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when \WP_DEBUG is enabled.
	 */
	protected function _rank_math_pretransmute_twitter( $data, &$set_value, &$actions, &$results ) {

		if ( empty( $set_value[ $data['test_value'] ] ) ) return;

		if ( $set_value[ $data['test_value'] ] !== $data['isnot'] )
			$set_value = array_diff_key( $set_value, array_flip( $data['unset'] ) );
	}
}
