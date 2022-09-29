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
		 * yet to be transmuted. this list can grow in massive proportions, not suitable
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
				[ $wpdb->termmeta, THE_SEO_FRAMEWORK_TERM_OPTIONS ],
				null,
				null,
				[
					'name'    => 'Rank Math Term Meta',
					'from' => [
						[ $this, '_get_rank_math_populated_term_ids' ],
						[ $this, '_get_rank_math_congealed_transport_value' ],
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
						[ $this, '_rank_math_term_meta_transmuter' ],
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
								'cb'   => [ $this, 'pretransmute_robots' ],
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
				[ $wpdb->termmeta, 'rank_math_twitter_image' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_twitter_image_id' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_twitter_card_type' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_focus_keyword' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_facebook_enable_image_overlay' ], // delete
			],
			[
				[ $wpdb->termmeta, 'rank_math_facebook_image_overlay' ], // delete
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
		];
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
	}

	/**
	 * Obtains ids from Rank Math's taxonomy metadata.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param array $data Any useful data pertaining to the current transmutation type.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	protected function _get_rank_math_populated_term_ids( $data ) {
		global $wpdb;

		// Redundant. If 'indexes' is a MD-array, though, we'd get 'Array', which is undesirable.
		// MD = multidimensional (we refer to that more often using MD).
		$indexes    = implode( "', '", static::esc_sql_in( $data['from_data']['indexes'] ) );
		$from_table = \esc_sql( $data['from_data']['table'] );

		$item_ids = $wpdb->get_col(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table/$indexes are escaped.
			"SELECT DISTINCT `{$this->id_key}` FROM `$from_table` WHERE meta_key IN ('$indexes')",
		);
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $item_ids ?: [];
	}

	/**
	 * Returns combined metadata from Rank Math for ID.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param array  $actions The actions for and after transmuation, passed by reference.
	 * @param array  $results The results before and after transmuation, passed by reference.
	 * @param ?array $cleanup The extraneous database indexes to clean up, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	protected function _get_rank_math_congealed_transport_value( $data, &$actions, &$results, &$cleanup ) {
		global $wpdb;

		// Redundant. If 'indexes' is a MD-array, though, we'd get 'Array', which is undesirable.
		// MD = multidimensional (we refer to that more often using MD).
		$indexes    = implode( "', '", static::esc_sql_in( $data['from_data']['indexes'] ) );
		$from_table = \esc_sql( $data['from_data']['table'] );

		$metadata = $wpdb->get_results( $wpdb->prepare(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table/$indexes are escaped.
			"SELECT meta_key, meta_value FROM `$from_table` WHERE `{$this->id_key}` = %d AND meta_key IN ('$indexes')",
			$data['item_id'],
		) );
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $metadata ? array_column( $metadata, 'meta_value', 'meta_key' ) : [];
	}

	/**
	 * Transmutes comma-separated advanced robots to a single value.
	 *
	 * @since 1.0.0
	 * @generator
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function _rank_math_term_meta_transmuter( $data, &$actions, &$results ) {

		[ $from_table, $from_index ] = $data['from'];
		[ $to_table, $to_index ]     = $data['to'];

		$set_value = [];

		// Nothing to do here, TSF already has value set. Skip to next item.
		if ( ! $actions['transport'] ) goto useless;

		foreach ( $data['to_data']['pretransmute'] as $type => $pretransmutedata ) {
			\call_user_func_array(
				$pretransmutedata['cb'],
				[
					$pretransmutedata['data'],
					&$data['set_value'],
					&$actions,
					&$results,
				]
			);
		}

		foreach ( $data['to_data']['transmuters'] as $from => $to ) {
			$_set_value = $data['set_value'][ $from ] ?? null;

			// We assume here that all Rank Math data without value is useless.
			// This might prove an issue later, where 0 carries significance.
			// Though, no developer in their right mind would store 0 or empty string...
			if ( \in_array( $_set_value, $this->useless_data, true ) ) continue;

			$_transformed = 0;

			if ( isset( $data['to_data']['transformers'][ $from ] ) ) {
				$_pre_transform_value = $_set_value;

				$_set_value = \call_user_func_array(
					$data['to_data']['transformers'][ $from ],
					[
						$_set_value,
						$data['item_id'],
						$this->type,
						[ $from_table, $from_index ],
						[ $to_table, $to_index ],
					]
				);

				// We actually only read this as boolean. Still, might be fun later.
				$_transformed = (int) ( $_pre_transform_value !== $_set_value );
			}

			if ( isset( $data['to_data']['sanitizers'][ $from ] ) ) {
				$_pre_sanitize_value = $_set_value;

				$_set_value = \call_user_func( $data['to_data']['sanitizers'][ $from ], $_set_value );

				$results['sanitized'] += (int) ( $_pre_sanitize_value !== $set_value );
			}

			if ( ! \in_array( $_set_value, $this->useless_data, true ) ) {
				$set_value[ $to ]        = $_set_value;
				$results['transformed'] += $_transformed;

				// If the title is not useless, assume it must remain how the user set it.
				if ( 'doctitle' === $to )
					$set_value['title_no_blog_name'] = 1;
			}
		}

		if ( \in_array( $set_value, $this->useless_data, true ) ) {
			useless:;
			$set_value              = null;
			$actions['transport']   = false;
			$results['transformed'] = 0;
		}

		$this->transmute(
			$set_value,
			$data['item_id'],
			[ $from_table, $from_index ], // Should be [ null, null ]
			[ $to_table, $to_index ],
			$actions,
			$results,
			$data['to_data']['cleanup']
		);

		yield 'transmutedResults' => [ $results, $actions ];
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
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function pretransmute_robots( $data, &$set_value, &$actions, &$results ) {

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
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function _rank_math_pretransmute_twitter( $data, &$set_value, &$actions, &$results ) {

		if ( empty( $set_value[ $data['test_value'] ] ) ) return;

		if ( $set_value[ $data['test_value'] ] !== $data['isnot'] )
			$set_value = array_diff_key( $set_value, array_flip( $data['unset'] ) );
	}
}
