<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

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
				[ $wpdb->postmeta, 'rank_math_title' ],
				[ $wpdb->postmeta, '_genesis_title' ],
				[ $transformer_class, '_title_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, 'rank_math_description' ],
				[ $wpdb->postmeta, '_genesis_description' ],
				[ $transformer_class, '_description_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, 'rank_math_canonical_url' ],
				[ $wpdb->postmeta, '_genesis_canonical_uri' ],
				null,
				'\\esc_url_raw',
			],
			[
				[ $wpdb->postmeta, 'rank_math_robots' ],
				null,
				null,
				null,
				[
					'name'    => 'Rank Math Robots',
					'to'      => [
						[ $this, '_robots_transmuter_existing' ],
						[ $this, '_robots_transmuter' ],
					],
					'to_data' => [
						// This could've been a simple transformer,
						// but then we don't get to split the data if we add more robots types.
						'transmuters'  => [
							'noindex'   => [ $wpdb->postmeta, '_genesis_noindex' ],
							'nofollow'  => [ $wpdb->postmeta, '_genesis_nofollow' ],
							'noarchive' => [ $wpdb->postmeta, '_genesis_noarchive' ],
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, 'rank_math_facebook_title' ],
				[ $wpdb->postmeta, '_open_graph_title' ],
				[ $transformer_class, '_title_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, 'rank_math_facebook_description' ],
				[ $wpdb->postmeta, '_open_graph_description' ],
				[ $transformer_class, '_description_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, 'rank_math_facebook_image' ],
				[ $wpdb->postmeta, '_social_image_url' ],
				null,
				'\\esc_url_raw',
			],
			[
				[ $wpdb->postmeta, 'rank_math_facebook_image_id' ],
				[ $wpdb->postmeta, '_social_image_id' ],
				null,
				'\\absint',
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_use_facebook' ],
				[ $wpdb->postmeta, 'rank_math_twitter_use_facebook' ], // stall on identical index.
				null,
				null,
				[
					'name' => 'Twitter use Facebook',
					'to' => [
						null,
						[ $this, '_purge_rank_math_twitter_if_facebook' ],
					],
					'to_data' => [
						'valueisnot' => 'off', //= if on, then purge; also means if absent, don't purge.
						'purge'      => [
							[ $wpdb->postmeta, 'rank_math_twitter_title' ],
							[ $wpdb->postmeta, 'rank_math_twitter_description' ],
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_title' ],
				[ $wpdb->postmeta, '_twitter_title' ],
				[ $transformer_class, '_title_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_description' ],
				[ $wpdb->postmeta, '_twitter_description' ],
				[ $transformer_class, '_description_syntax' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, 'rank_math_pillar_content' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_facebook_enable_image_overlay' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_facebook_image_overlay' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_enable_image_overlay' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_image_overlay' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_image' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_image_id' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_twitter_card_type' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_advanced_robots' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_focus_keyword' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_breadcrumb_title' ], // delete
			],
			[
				[ $wpdb->postmeta, 'rank_math_seo_score' ], // delete
			],
		];
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
	}

	/**
	 * Gets existing advanced robots values.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param array $data Any useful data pertaining to the current transmutation type.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return array An array with existing and transport values -- if any.
	 */
	public function _robots_transmuter_existing( $data ) {
		global $wpdb;

		$ret             = [
			'existing'  => [],
			'transport' => [],
		];
		$transport_value = null; // reserve, only set once when one existing value isn't.

		foreach ( [
			'noindex',
			'nofollow',
			'noarchive',
			// 'noimageindex', // reserved for later
			// 'nosnippet', // reserved for later
		] as $type ) {
			// Defined in $this->conversion_sets
			[ $to_table, $to_index ] = array_map( '\\esc_sql', $data['to_data']['transmuters'][ $type ] );

			// TODO improve performance make this get_col->WHERE IN? Do we even improve performance then?
			$current_value = $wpdb->get_var( $wpdb->prepare(
				// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $to_table is escaped.
				"SELECT meta_value FROM `$to_table` WHERE post_id = %d AND meta_key = %s",
				$data['item_id'],
				$to_index
			) );
			if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

			if ( isset( $current_value ) ) {
				$ret['existing'][ $type ] = $current_value;
			} else {
				// Get transport value if not fetched before.
				if ( ! isset( $transport_value ) ) {
					[ $from_table, $from_index ] = $data['from'];

					$transport_value = $wpdb->get_var( $wpdb->prepare(
						// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table is escaped.
						"SELECT meta_value FROM `$from_table` WHERE `{$this->id_key}` = %d AND meta_key = %s",
						$data['item_id'],
						$from_index
					) );
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

					$transport_value = $this->maybe_unserialize_no_class( $transport_value );

					// Makes [ 'noarchive' => 1, 'nosnippet' => 1, 'noimageindex' => 1 ] when index is found.
					$transport_value = \is_array( $transport_value )
						? array_fill_keys( $transport_value, 1 ) // no_robots = 1
						: [];
				}

				if ( isset( $transport_value[ $type ] ) )
					$ret['transport'][ $type ] = $transport_value[ $type ];
			}
		}

		return $ret;
	}

	/**
	 * Transmutes serialized robots to simple values.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	public function _robots_transmuter( $data, &$actions, &$results ) {

		[ $from_table, $from_index ] = $data['from'];

		foreach ( $data['to_data']['transmuters'] as $type => $transmuter ) {
			[ $to_table, $to_index ] = array_map( '\\esc_sql', $transmuter );

			$_actions = $actions;
			$_results = $results;

			$_actions['transport'] = true;
			$_actions['delete']    = false;

			$existing_value  = $data['set_value']['existing'][ $type ] ?? null;
			$transport_value = $data['set_value']['transport'][ $type ] ?? null;

			$set_value = $existing_value ?? $transport_value;

			$_results['transformed'] += (int) ( $existing_value !== $set_value );

			if ( \in_array( $set_value, $this->useless_data, true ) ) {
				$set_value               = null;
				$_results['transformed'] = 0;
				$_actions['transport']   = false;
			}

			$this->transmute(
				$set_value,
				$data['item_id'],
				// We cleanup later; data comes from "nowhere."
				[ null, null ],
				[ $to_table, $to_index ],
				$_actions,
				$_results
			);

			yield 'transmutedResults' => [ $_results, $_actions ];
		}

		// $results gets forwarded from here to yield 'results'.
		$this->delete(
			$data['item_id'],
			[ $from_table, $from_index ],
			$results,
		);
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
	protected function _purge_rank_math_twitter_if_facebook( $data, &$actions, &$results ) {

		if ( $data['to_data']['valueisnot'] !== $data['set_value'] )
			foreach ( $data['to_data']['purge'] as [ $from_table, $from_index ] )
				$this->delete(
					$data['item_id'],
					[ $from_table, $from_index ],
					$results,
				);

		[ $from_table, $from_index ] = $data['from'];

		// Clean thyself.
		$this->delete(
			$data['item_id'],
			[ $from_table, $from_index ],
			$results,
		);

		yield 'transmutedResults' => [ $results, $actions ];
	}
}
