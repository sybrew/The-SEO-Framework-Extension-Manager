<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * Copyright (C) 2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Importer for SEOPress.
 *
 * @since 1.1.0
 * @access private
 *
 * Inherits abstract setup_vars.
 */
final class WP_SEOPress extends Base {

	/**
	 * Sets up variables.
	 *
	 * @since 1.1.0
	 * @abstract
	 */
	protected function setup_vars() {
		global $wpdb;

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment -- deeply nested is still simple here.

		// Construct and fetch classname.
		$transformer_class = \get_class(
			\TSF_Extension_Manager\Extension\Transport\Transformers\WP_SEOPress::get_instance()
		);

		$tsf = \tsf();

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
				[ $wpdb->postmeta, '_seopress_titles_title' ],
				[ $wpdb->postmeta, '_genesis_title' ],
				[ $transformer_class, '_title_syntax' ],
				[ $tsf, 's_title_raw' ],
				[
					'name' => 'Meta Title',
					'to'   => [
						null,
						[ $this, '_title_transmuter' ],
					],
					'to_data' => [
						'titleset' => [
							'index' => [ $wpdb->postmeta, '_tsf_title_no_blogname' ],
							'value' => 1,
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, '_seopress_titles_desc' ],
				[ $wpdb->postmeta, '_genesis_description' ],
				[ $transformer_class, '_description_syntax' ],
				[ $tsf, 's_description_raw' ],
			],
			[
				[ $wpdb->postmeta, '_seopress_social_fb_title' ],
				[ $wpdb->postmeta, '_open_graph_title' ],
				null,
				[ $tsf, 's_title_raw' ],
			],
			[
				[ $wpdb->postmeta, '_seopress_social_fb_desc' ],
				[ $wpdb->postmeta, '_open_graph_description' ],
				null,
				[ $tsf, 's_description_raw' ],
			],
			[
				[ $wpdb->postmeta, '_seopress_social_fb_img' ],
				[ $wpdb->postmeta, '_social_image_url' ],
				null,
				'\\esc_url_raw',
			],
			[
				[ $wpdb->postmeta, '_seopress_social_fb_img_attachment_id' ],
				[ $wpdb->postmeta, '_social_image_id' ],
				null,
				'\\absint',
			],
			[
				[ $wpdb->postmeta, '_seopress_social_twitter_title' ],
				[ $wpdb->postmeta, '_twitter_title' ],
				null,
				[ $tsf, 's_title_raw' ],
			],
			[
				[ $wpdb->postmeta, '_seopress_social_twitter_desc' ],
				[ $wpdb->postmeta, '_twitter_description' ],
				null,
				[ $tsf, 's_description_raw' ],
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_canonical' ],
				[ $wpdb->postmeta, '_genesis_canonical_uri' ],
				null,
				'\\esc_url_raw',
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_index' ],
				[ $wpdb->postmeta, '_genesis_noindex' ],
				[ $transformer_class, '_robots_qubit' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_follow' ],
				[ $wpdb->postmeta, '_genesis_nofollow' ],
				[ $transformer_class, '_robots_qubit' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_archive' ],
				[ $wpdb->postmeta, '_genesis_noindex' ],
				[ $transformer_class, '_robots_qubit' ], // also sanitizes
			],
			[
				[ $wpdb->postmeta, '_seopress_redirections_enabled' ],
				[ $wpdb->postmeta, '_seopress_redirections_enabled' ], // HACK FIXME: stall on identical index.
				null,
				null,
				[
					'name' => 'Redirect conditional enabled',
					'to' => [
						null,
						[ $this, '_purge_seopress_redirect_if' ],
					],
					'to_data' => [
						'valueisnot' => 'yes', // if not, then purge; also means if absent, then purge.
						'purge'      => [
							// Delete this so we can skip the next check.
							[ $wpdb->postmeta, '_seopress_redirections_logged_status' ],
							// Delete the value so we won't transport it.
							[ $wpdb->postmeta, '_seopress_redirections_value' ],

							// We already delete the ones below separately.
							// [ $wpdb->postmeta, '_seopress_redirections_type' ],
							// [ $wpdb->postmeta, '_seopress_redirections_param' ],
							// [ $wpdb->postmeta, '_seopress_redirections_enabled_regex' ],
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, '_seopress_redirections_logged_status' ],
				[ $wpdb->postmeta, '_seopress_redirections_logged_status' ], // HACK FIXME: stall on identical index.
				null,
				null,
				[
					'name' => 'Redirect conditional logged in',
					'to' => [
						null,
						[ $this, '_purge_seopress_redirect_if' ],
					],
					'to_data' => [
						// We could allow 'only_not_logged_in', but that'd cause edge-case problems.
						'valueisnot' => 'both', // if not, then purge; also means if absent, then purge.
						'purge'      => [
							// Delete the value so we won't transport it.
							[ $wpdb->postmeta, '_seopress_redirections_value' ],

							// We already delete the ones below separately.
							// [ $wpdb->postmeta, '_seopress_redirections_type' ],
							// [ $wpdb->postmeta, '_seopress_redirections_param' ],
							// [ $wpdb->postmeta, '_seopress_redirections_enabled_regex' ],
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, '_seopress_redirections_value' ],
				[ $wpdb->postmeta, 'redirect' ],
				null,
				'\\esc_url_raw',
			],
			[
				// They only support categories and product categories for they don't know how to abstract a program.
				[ $wpdb->postmeta, '_seopress_robots_primary_cat' ],
				null,
				null,
				null,
				[
					'name' => 'Primary term',
					'to' => [
						[ $this, '_primary_term_transmuter_existing' ],
						[ $this, '_primary_term_transmuter' ],
					],
					'to_data' => [
						'valueisnot'            => 'none',
						'post_type_transmuters' => [
							'post'    => [ $wpdb->postmeta, '_primary_term_category' ],
							'product' => [ $wpdb->postmeta, '_primary_term_product_cat' ],
						],
					],
				],
			],
			[
				[ $wpdb->postmeta, '_seopress_social_fb_img_width' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_social_fb_img_height' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_social_twitter_img' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_social_twitter_img_attachment_id' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_social_twitter_img_width' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_social_twitter_img_height' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_breadcrumbs' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_snippet' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_odp' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_robots_imageindex' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_redirections_type' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_redirections_param' ], // delete, not even used by SEOPress.
			],
			[
				[ $wpdb->postmeta, '_seopress_redirections_enabled_regex' ], // delete, not even used by SEOPress.
			],
			[
				[ $wpdb->postmeta, '_seopress_analysis_target_kw' ], // delete
			],
			[
				[ $wpdb->postmeta, '_seopress_analysis_data' ], // delete
			],
			[
				[ $wpdb->postmeta, 'seopress_404_count' ], // delete
			],
		];
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
	}

	/**
	 * Sets `_tsf_title_no_blogname` to `1` if title is transformed.
	 *
	 * @since 1.1.0
	 * @generator
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 */
	protected function _title_transmuter( $data, &$actions, &$results ) {

		// Set _tsf_title_no_blogname to 1 if data isn't useless:
		if ( $actions['transport'] && ! \in_array( $data['set_value'], $this->useless_data, true ) ) {
			[ $to_table, $to_index ] = $data['to_data']['titleset']['index'];

			$_actions = [
				'transport' => true,
				'delete'    => false,
			];
			$_results = [
				'updated'     => 0,
				'transformed' => 0,
				'deleted'     => 0,
				'sanitized'   => 0,
			];

			$this->transmute(
				$data['to_data']['titleset']['value'],
				$data['item_id'],
				[ null, null ], // data comes from nowhere.
				[ $to_table, $to_index ],
				$_actions,
				$_results
			);

			yield 'transmutedResults' => [ $_results, $_actions ];
		}

		// Pass through all results for the following transmutation.
		$results['only_end'] = false;

		// Pass actual title through to transmute. $actions and $results get written by reference here.
		$this->transmute(
			$data['set_value'],
			$data['item_id'],
			$data['from'],
			$data['to'],
			$actions,
			$results,
		);
	}

	/**
	 * Purges Redirect data if `_seopress_redirections_enabled` is set to `off`.
	 *
	 * @since 1.1.0
	 * @generator
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 */
	protected function _purge_seopress_redirect_if( $data, &$actions, &$results ) {

		if ( $data['to_data']['valueisnot'] !== $data['set_value'] ) {
			foreach ( $data['to_data']['purge'] as [ $from_table, $from_index ] )
				$this->delete(
					$data['item_id'],
					[ $from_table, $from_index ],
					$results,
				);
		}

		[ $from_table, $from_index ] = $data['from'];

		// Clean thyself.
		$this->delete(
			$data['item_id'],
			[ $from_table, $from_index ],
			$results,
		);

		yield 'transmutedResults' => [ $results, $actions ];
	}

	/**
	 * Gets existing primary term data.
	 *
	 * @since 1.1.0
	 *
	 * @param array $data Any useful data pertaining to the current transmutation type.
	 * @return array An array with existing and transport values -- if any.
	 */
	public function _primary_term_transmuter_existing( $data ) {

		$ret = [
			'existing'    => null,
			'transport'   => null,
			'destination' => $data['to_data']['post_type_transmuters']
				[ \get_post_type( $data['item_id'] ) ]
				?? null,
		];

		// Nothing to do here, fail transmutation at next step.
		if ( ! $ret['destination'] ) return $ret;

		$ret['existing'] = $this->get_existing_meta( [
			// Defined in $this->conversion_sets
			'to'      => $ret['destination'],
			'item_id' => $data['item_id'],
		] );

		// If there's no existing value, we can go ahead and fetch the transport value.
		if ( empty( $ret['existing'] ) ) {
			// We'll determine if this is useless in the next step. ('none')
			$ret['transport'] = $this->get_transport_value( [
				'from'    => $data['from'],
				'item_id' => $data['item_id'],
			] );
		}

		return $ret;
	}

	/**
	 * Transmutes primary term data.
	 *
	 * @since 1.1.0
	 * @generator
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 */
	public function _primary_term_transmuter( $data, &$actions, &$results ) {

		[ $from_table, $from_index ] = $data['from'];
		[ $to_table, $to_index ]     = $data['set_value']['destination'] ?? [ null, null ];

		$set_value = null;

		$actions = [
			'transport' => true,
			'delete'    => true,
		];

		// We cannot trust SEOPress with data -- if this failed, delete their nonsense.
		// Don't set $actions['transport'] to false -- report it failed to user.
		if ( empty( $data['set_value']['destination'] ) ) goto useless;

		$existing_value  = $data['set_value']['existing'];
		$transport_value = $data['set_value']['transport'];

		if ( isset( $existing_value ) )
			$actions['transport'] = false;

		$set_value = $existing_value ?? $transport_value;

		if ( 'none' === $set_value )
			goto useless;

		$_pre_sanitize_value   = $set_value;
		$set_value             = \absint( $set_value );
		$results['sanitized'] += (int) ( $_pre_sanitize_value !== $set_value );

		if ( \in_array( $set_value, $this->useless_data, true ) ) {
			useless:;
			$set_value            = null;
			$actions['transport'] = false;
		}

		$this->transmute(
			$set_value,
			$data['item_id'],
			[ $from_table, $from_index ],
			[ $to_table, $to_index ],
			$actions,
			$results
		);

		yield 'transmutedResults' => [ $results, $actions ];
	}
}
