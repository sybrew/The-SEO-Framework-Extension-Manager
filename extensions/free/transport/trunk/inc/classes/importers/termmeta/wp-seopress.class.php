<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\TermMeta;

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
		 * NOTE: I considered making a separate transaction for each term meta entry
		 * from SEOPress, and merge each new value into the "existing" serialized
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
				[ $wpdb->termmeta, THE_SEO_FRAMEWORK_TERM_OPTIONS ],
				null,
				null,
				[
					'name'    => 'SEOPress Term Meta',
					'from' => [
						[ $this, '_get_populated_term_ids' ],
						[ $this, '_get_congealed_transport_value' ],
					],
					'from_data' => [
						'table'   => $wpdb->termmeta,
						'indexes' => [
							'_seopress_titles_title',
							'_seopress_titles_desc',
							'_seopress_social_fb_title',
							'_seopress_social_fb_desc',
							'_seopress_social_fb_img',
							'_seopress_social_fb_img_attachment_id',
							'_seopress_social_twitter_title',
							'_seopress_social_twitter_desc',
							'_seopress_robots_canonical',
							'_seopress_robots_index',
							'_seopress_robots_follow',
							'_seopress_robots_archive',
							'_seopress_redirections_enabled',
							'_seopress_redirections_logged_status',
							'_seopress_redirections_value',
						],
					],
					'to'      => [
						null,
						[ $this, '_term_meta_transmuter' ],
					],
					'to_data' => [
						'pretransmute' => [
							'_seopress_redirections_enabled' => [
								'cb'   => [ $this, '_seopress_pretransmute_redirect' ],
								'data' => [
									[
										'test_value' => '_seopress_redirections_enabled',
										// If off, do use own data. If on, don't use own data. See,
										'isnot'      => 'yes', // if not, then purge; also means if absent, then purge.
										'unset'      => [
											// Delete this so we can skip the next check.
											'_seopress_redirections_logged_status',
											'_seopress_redirections_value',
										],
									],
									[
										'test_value' => '_seopress_redirections_logged_status',
										// We could allow 'only_not_logged_in', but that'd cause edge-case problems.
										'isnot'      => 'both', // if not, then purge; also means if absent, then purge.
										'unset'      => [
											'_seopress_redirections_value',
										],
									],
								],
							],
						],
						'transmuters'  => [
							'_seopress_titles_title'                => 'doctitle',
							'_seopress_titles_desc'                 => 'description',
							'_seopress_social_fb_title'             => 'og_title',
							'_seopress_social_fb_desc'              => 'og_description',
							'_seopress_social_fb_img'               => 'social_image_url',
							'_seopress_social_fb_img_attachment_id' => 'social_image_id',
							'_seopress_social_twitter_title'        => 'tw_title',
							'_seopress_social_twitter_desc'         => 'tw_description',
							'_seopress_robots_canonical'            => 'canonical',
							'_seopress_robots_index'                => 'noindex',
							'_seopress_robots_follow'               => 'nofollow',
							'_seopress_robots_archive'              => 'noarchive',
							'_seopress_redirections_value'          => 'redirect',
						],
						'transformers' => [
							'_seopress_titles_title'         => [ $transformer_class, '_title_syntax' ],
							'_seopress_titles_desc'          => [ $transformer_class, '_description_syntax' ],
							'_seopress_social_fb_title'      => [ $transformer_class, '_title_syntax' ],
							'_seopress_social_fb_desc'       => [ $transformer_class, '_description_syntax' ],
							'_seopress_social_twitter_title' => [ $transformer_class, '_title_syntax' ],
							'_seopress_social_twitter_desc'  => [ $transformer_class, '_description_syntax' ],
							'_seopress_robots_index'         => [ $transformer_class, '_robots_qubit' ], // also sanitizes
							'_seopress_robots_follow'        => [ $transformer_class, '_robots_qubit' ], // also sanitizes
							'_seopress_robots_archive'       => [ $transformer_class, '_robots_qubit' ], // also sanitizes
						],
						'sanitizers' => [
							'_seopress_titles_title'                => [ $tsf, 's_title_raw' ],
							'_seopress_titles_desc'                 => [ $tsf, 's_description_raw' ],
							'_seopress_social_fb_title'             => [ $tsf, 's_title_raw' ],
							'_seopress_social_fb_desc'              => [ $tsf, 's_description_raw' ],
							'_seopress_social_fb_img'               => '\\esc_url_raw',
							'_seopress_social_fb_img_attachment_id' => '\\absint',
							'_seopress_social_twitter_title'        => [ $tsf, 's_title_raw' ],
							'_seopress_social_twitter_desc'         => [ $tsf, 's_description_raw' ],
							'_seopress_robots_canonical'            => '\\esc_url_raw',
							'_seopress_redirections_value'          => '\\esc_url_raw',
						],
						'cleanup' => [
							[ $wpdb->termmeta, '_seopress_titles_title' ],
							[ $wpdb->termmeta, '_seopress_titles_desc' ],
							[ $wpdb->termmeta, '_seopress_social_fb_title' ],
							[ $wpdb->termmeta, '_seopress_social_fb_desc' ],
							[ $wpdb->termmeta, '_seopress_social_fb_img' ],
							[ $wpdb->termmeta, '_seopress_social_fb_img_attachment_id' ],
							[ $wpdb->termmeta, '_seopress_social_twitter_title' ],
							[ $wpdb->termmeta, '_seopress_social_twitter_desc' ],
							[ $wpdb->termmeta, '_seopress_robots_canonical' ],
							[ $wpdb->termmeta, '_seopress_robots_index' ],
							[ $wpdb->termmeta, '_seopress_robots_follow' ],
							[ $wpdb->termmeta, '_seopress_robots_archive' ],
							[ $wpdb->termmeta, '_seopress_redirections_enabled' ],
							[ $wpdb->termmeta, '_seopress_redirections_logged_status' ],
							[ $wpdb->termmeta, '_seopress_redirections_value' ],
						],
					],
				],
			],
			[
				[ $wpdb->termmeta, '_seopress_social_fb_img_width' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_social_fb_img_height' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_social_twitter_img' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_social_twitter_img_width' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_social_twitter_img_height' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_robots_imageindex' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_robots_snippet' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_robots_breadcrumbs' ], // delete
			],
			[
				[ $wpdb->termmeta, '_seopress_redirections_type' ], // delete
			],
		];
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
	}

	/**
	 * Pretransmutes SEOPress redirect value by testing whether user filled other data.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $data      The pretransmutation data ('cbdata').
	 * @param array  $set_value The current $set_value data used for actual transmuation, passed by reference.
	 * @param ?array $actions   The actions for and after transmuation, passed by reference.
	 * @param ?array $results   The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function _seopress_pretransmute_redirect( $data, &$set_value, &$actions, &$results ) {
		foreach ( $data as $_data )
			if ( ( $set_value[ $_data['test_value'] ] ?? null ) !== $_data['isnot'] )
				$set_value = array_diff_key( $set_value, array_flip( $_data['unset'] ) );
	}
}
