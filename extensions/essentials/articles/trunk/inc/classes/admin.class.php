<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Classes
 */

namespace TSF_Extension_Manager\Extension\Articles;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Articles\Admin
 *
 * @since 1.2.0
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin extends Core {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * Constructor.
	 */
	private function construct() {

		$this->prepare_inpostgui();
		$this->prepare_listedit();
		$this->prepare_settings();

		\add_filter( 'display_post_states', [ $this, '_add_post_state' ], 9, 2 );
	}

	/**
	 * Prepares inpost GUI.
	 *
	 * @since 1.2.0
	 */
	private function prepare_inpostgui() {

		//= Prepares InpostGUI's class for nonce checking.
		\TSF_Extension_Manager\InpostGUI::prepare();

		//= Called late because we need to access the meta object after current_screen.
		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_prepare_inpost_views' ] );

		\add_action( 'tsfem_inpostgui_verified_nonce', [ $this, '_save_meta' ], 10, 3 );
	}

	/**
	 * Prepares list edit.
	 *
	 * @since 2.1.0
	 */
	private function prepare_listedit() {

		\TSF_Extension_Manager\ListEdit::prepare();

		\add_action( 'the_seo_framework_before_quick_edit', [ $this, '_prepare_quick_views' ], 10, 2 );
		\add_action( 'the_seo_framework_before_bulk_edit', [ $this, '_prepare_bulk_views' ], 10, 2 );

		\add_filter( 'the_seo_framework_list_table_data', [ $this, '_add_list_table_data' ], 10, 2 );

		\add_action( 'tsfem_quick_edit_verified_nonce', [ $this, '_save_meta_quick_edit' ], 10, 2 );
		\add_action( 'tsfem_bulk_edit_verified_nonce', [ $this, '_save_meta_bulk_edit' ], 10, 2 );
	}

	/**
	 * Prepares settings GUI.
	 *
	 * @since 2.0.0
	 */
	private function prepare_settings() {

		\TSF_Extension_Manager\ExtensionSettings::prepare();

		\add_action( 'tsfem_register_settings_fields', [ $this, '_register_settings' ] );
		\add_action( 'tsfem_register_settings_sanitization', [ $this, '_register_sanitization' ] );
	}

	/**
	 * Adds post states for the post/page edit.php query.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Added the disabled type. When used, it won't add the state.
	 * @access private
	 * @staticvar string $default
	 * @staticvar array $type_i18n
	 *
	 * @param array    $states The current post states array
	 * @param \WP_Post $post The Post Object.
	 * @return array Adjusted $states
	 */
	public function _add_post_state( $states = [], $post ) {

		if ( ! $this->is_post_type_supported( $post->post_type ) ) return $states;

		static $default = null;
		if ( ! $default ) {
			$settings = $this->get_option( 'post_types' );
			$default  = static::filter_article_type( \tsf_extension_manager()->coalesce_var( $settings[ $post->post_type ]['default_type'], 'Article' ) );
		}

		static $type_i18n = null;
		if ( ! $type_i18n ) {
			$type_i18n = static::filter_article_keys( [
				'disabled'    => false,
				'Article'     => \__( 'Article', 'the-seo-framework-extension-manager' ),
				'NewsArticle' => \__( 'News Article', 'the-seo-framework-extension-manager' ),
				'BlogPosting' => \__( 'Blog Posting', 'the-seo-framework-extension-manager' ),
			] );
		}

		$this->set_extension_post_meta_id( $post->ID );

		$type     = $type_i18n[ static::filter_article_type( $this->get_post_meta( 'type', $default ) ) ]
			and
		$states[] = $type;

		return $states;
	}

	/**
	 * Registers settings for Articles.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $settings \TSF_Extension_Manager\ExtensionSettings
	 */
	public function _register_settings( $settings ) {

		if ( \has_filter( 'the_seo_framework_articles_supported_post_types' )
		|| \has_filter( 'the_seo_framework_articles_default_meta' ) ) {
			\add_action( 'tsfem_notices', [ $this, '_do_filter_upgrade_notice' ] );
		}

		$_settings = [
			'post_types' => $this->generate_post_type_settings(),
		];

		if ( static::is_organization() ) {
			$_settings += [
				'news_sitemap' => [
					'_default' => null,
					'_edit'    => true,
					'_ret'     => 'bool',
					'_req'     => false,
					'_type'    => 'checkbox',
					'_desc'    => [
						\__( 'Google News Sitemap', 'the-seo-framework-extension-manager' ),
						sprintf(
							/* translators: %s = Articles FAQ link. Markdown. */
							\__( 'For more information, please refer to the [Articles FAQ](%s).', 'the-seo-framework-extension-manager' ),
							'https://theseoframework.com/extensions/articles/#faq'
						),
						\__( 'The Google News sitemap will list all news articles and annotate them accordingly for Google News.', 'the-seo-framework-extension-manager' ),
					],
					'_md'      => true,
					'_check'   => [
						\__( 'Enable Google News sitemap?', 'the-seo-framework-extension-manager' ),
					],
				],
				'logo'         => [
					'_default'  => [
						'url' => '',
						'id'  => '',
					],
					'_ph'       => \the_seo_framework()->get_option( 'knowledge_logo_url' ) ?: '',
					'_edit'     => true,
					'_ret'      => 'image',
					'_req'      => false,
					'_type'     => 'image',
					'_readonly' => true,
					'_desc'     => [
						\__( 'Publisher Logo', 'the-seo-framework-extension-manager' ),
						sprintf(
							/* translators: %s = Logo guidelines link. Markdown. */
							\__( 'Please refer to the [logo guidelines](%s).', 'the-seo-framework-extension-manager' ),
							'https://developers.google.com/search/docs/data-types/article#logo-guidelines'
						),
						\__( 'The logo must be a horizontally wide rectangle, not a square, and at least 60px high.', 'the-seo-framework-extension-manager' ),
					],
					'_md'       => true,
				],
			];
		}

		$settings::register_settings(
			$this->o_index,
			[
				'title'    => 'Articles',
				'logo'     => [
					'svg' => TSFEM_E_ARTICLES_DIR_URL . 'lib/images/icon.svg',
					'2x'  => TSFEM_E_ARTICLES_DIR_URL . 'lib/images/icon-58x58.png',
					'1x'  => TSFEM_E_ARTICLES_DIR_URL . 'lib/images/icon-29x29px.png',
				],
				'before'   => '',
				'after'    => '',
				'pane'     => [],
				'settings' => $_settings,
				// When we add more panes, we can order them by adding up to 9.9999 to this value.
				'priority' => \tsf_extension_manager()->get_extension_order()['articles'],
			]
		);

		$settings::register_defaults( $this->o_index, $this->o_defaults );
	}

	/**
	 * Returns the post type related settings.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Added the 'disabled' default type.
	 * @see $this->_register_settings()
	 *
	 * @return array The post type settings.
	 */
	private function generate_post_type_settings() {

		$fields = [
			'enabled'      => [
				'_default' => null,
				'_edit'    => true,
				'_ret'     => 's',
				'_req'     => false,
				'_type'    => 'checkbox',
				'_desc'    => [
					\__( 'Enable Post Type', 'the-seo-framework-extension-manager' ),
					'',
					'',
				],
				'_check'   => [
					\__( 'Enable article markup support?', 'the-seo-framework-extension-manager' ),
				],
				'_data'    => [
					'is-type-listener'     => '1',
					'set-type-to-if-value' => [
						'enabled'  => '1',
						'disabled' => '0',
					],
					'showif-catcher'       => 'posttype.status',
				],
			],
			'default_type' => [
				'_default' => null,
				'_edit'    => true,
				'_ret'     => 's',
				'_req'     => false, // required _must_ have an empty select-option value. This is redundant.
				'_type'    => 'select',
				'_desc'    => [
					\__( 'Default Article Type', 'the-seo-framework-extension-manager' ),
					'',
					\__( 'This setting can be overwritten on a per-page basis. Changing this setting does not affect pages that have a type already stored.', 'the-seo-framework-extension-manager' ),
				],
				'_select'  => [],
				'_data'    => [
					'is-showif-listener' => '1',
					'showif'             => [
						'posttype.status' => 'enabled',
					],
				],
			],
		];

		foreach ( static::get_available_article_types() as $_type ) :
			switch ( $_type ) :
				case 'disabled':
					$_select_item = [
						'disabled',
						\__( '&mdash; Disabled &mdash;', 'the-seo-framework-extension-manager' ),
					];
					break;
				case 'Article':
					$_select_item = [
						'Article',
						\__( 'Article', 'the-seo-framework-extension-manager' ),
					];
					break;
				case 'NewsArticle':
					$_select_item = [
						'NewsArticle',
						\__( 'News Article', 'the-seo-framework-extension-manager' ),
					];
					break;
				case 'BlogPosting':
					$_select_item = [
						'BlogPosting',
						\__( 'Blog Posting', 'the-seo-framework-extension-manager' ),
					];
					break;
				default:
					break;
			endswitch;
			$fields['default_type']['_select'][] = $_select_item;
		endforeach;

		$tsf        = \the_seo_framework();
		$post_types = $tsf->get_supported_post_types();

		$settings = [];

		foreach ( $post_types as $post_type ) {

			// This is definitely not an Article type.
			if ( 'attachment' === $post_type ) continue;

			$pto             = \get_post_type_object( $post_type );
			$post_type_label = isset( $pto->labels->name ) ? $pto->labels->name : $tsf->get_post_type_label( $post_type );

			$settings[ $post_type ] = [
				'_default' => null,
				'_edit'    => true,
				'_ret'     => '',
				'_req'     => false,
				'_type'    => 'multi_placeholder',
				'_desc'    => [
					$post_type_label,
					\__( 'Adjust article settings for this post type.', 'the-seo-framework-extension-manager' ),
				],
				'_fields'  => $fields,
			];
		}

		return [
			'_default'                => null,
			'_edit'                   => true,
			'_ret'                    => '',
			'_req'                    => false,
			'_type'                   => 'multi_dropdown',
			'_desc'                   => [
				\__( 'Post Type Settings', 'the-seo-framework-extension-manager' ),
				\__( 'Article markup should only be applied to content that is ephemeral and may be subject to change, like an opinionated blog post, a news article, or a research document. Timeless content, such as a contact, product, or about page, should not have the article markup.', 'the-seo-framework-extension-manager' ),
				\__( 'Be mindful of the post types you enable. For instance, a product, app, recipe, or an event page should not always be recognized as an article.', 'the-seo-framework-extension-manager' ),
			],
			'_fields'                 => $settings,
			'_dropdown_title_dynamic' => [
				'checkbox' => 'enabled',
			],
			'_dropdown_title_checked' => \__( 'Supported', 'the-seo-framework-extension-manager' ),
		];
	}

	/**
	 * Adds settings page warning for Articles.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	public function _do_filter_upgrade_notice() {

		if ( \has_filter( 'the_seo_framework_articles_supported_post_types' ) ) {
			\the_seo_framework()->do_dismissible_notice(
				'Filter <code>the_seo_framework_articles_supported_post_types</code> is deprecated. Please remove it and use the settings below instead.',
				'error',
				true,
				false,
				true
			);
		}
		if ( \has_filter( 'the_seo_framework_articles_default_meta' ) ) {
			\the_seo_framework()->do_dismissible_notice(
				'Filter <code>the_seo_framework_articles_default_meta</code> is deprecated. Please remove it and use the settings below instead.',
				'error',
				true,
				false,
				true
			);
		}
	}

	/**
	 * Registers sanitization callbacks for Articles.
	 *
	 * @since 2.0.0
	 *
	 * @param string $settings \TSF_Extension_Manager\ExtensionSettings
	 */
	public function _register_sanitization( $settings ) {
		$settings::register_sanitization(
			$this->o_index,
			[
				'post_types'   => [ static::class, '_sanitize_option_post_type' ],
				'news_sitemap' => [ static::class, '_sanitize_option_one_zero' ],
				'logo'         => [ static::class, '_sanitize_option_logo' ],
			]
		);
	}

	/**
	 * Sanitizes the post type options.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $value The input value.
	 * @return string The sanitized input value.
	 */
	public static function _sanitize_option_post_type( $value ) {

		if ( ! \is_array( $value ) )
			$value = [];

		$post_types = \the_seo_framework()->get_supported_post_types();

		// TODO do we want to strip unknown entries from payload?
		// Only sanitize known post types.
		foreach ( $post_types as $type ) {
			if ( ! isset( $value[ $type ] ) ) continue;

			// This is definitely not an Article type.
			if ( 'attachment' === $type ) continue;

			if ( ! isset( $value[ $type ]['enabled'] ) )
				$value[ $type ]['enabled'] = 0;

			$value[ $type ]['enabled']      = static::_sanitize_option_one_zero( $value[ $type ]['enabled'] );
			$value[ $type ]['default_type'] = static::_sanitize_option_article_type( $value[ $type ]['default_type'] );
		}

		return $value;
	}

	/**
	 * Sanitizes option to only contain one and zero values.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $value The input value.
	 * @return int The sanitized input value. Either 1 or 0.
	 */
	public static function _sanitize_option_one_zero( $value ) {
		return (int) (bool) $value;
	}

	/**
	 * Sanitizes option to contain correct URL values.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param array $values The input values.
	 * @return array The sanitized option
	 */
	public static function _sanitize_option_logo( $values ) {

		$url = isset( $values['url'] ) ? \esc_url_raw( $values['url'] ) : '';
		$id  = isset( $values['id'] ) ? \absint( $values['id'] ) : 0;

		if ( ! $url || ! $id ) {
			$url = '';
			$id  = 0;
		}

		return [
			'url' => $url,
			'id'  => $id,
		];
	}

	/**
	 * Sanitizes the article type option.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Now sanitizes 'disabled'.
	 * @access private
	 *
	 * @param string $type The input value.
	 * @return int The sanitized input value. Either 'disabled', 'Article', 'NewsArticle', or 'BlogPosting'.
	 */
	public static function _sanitize_option_article_type( $type ) {
		return static::filter_article_type( $type );
	}

	/**
	 * Tests whether post type is supported for the current request.
	 *
	 * @since 2.1.0
	 *
	 * @param string $post_type The post type to validate.
	 * @return bool
	 */
	private function is_post_type_supported( $post_type = '' ) {

		static $supported = null;

		if ( isset( $supported ) ) return $supported;

		$tsf = \the_seo_framework();

		$post_type = $post_type ?: $tsf->get_admin_post_type();
		$settings  = $this->get_option( 'post_types' );

		return $supported = ! empty( $settings[ $post_type ]['enabled'] ) && $tsf->is_post_type_supported( $post_type );
	}

	/**
	 * Prepares inpost options.
	 *
	 * Defered because we need to access meta.
	 *
	 * @since 1.2.0
	 * @since 1.4.0 Now uses a new filter to determine support.
	 * @since 2.0.0 Now filters the available types.
	 * @access private
	 */
	public function _prepare_inpost_views() {

		if ( ! $this->is_post_type_supported() ) return;

		\TSF_Extension_Manager\InpostGUI::activate_tab( 'structure' );

		$post_type = \the_seo_framework()->get_admin_post_type();
		$settings  = $this->get_option( 'post_types' );

		$_default = static::filter_article_type( \tsf_extension_manager()->coalesce_var( $settings[ $post_type ]['default_type'], 'Article' ) );

		$post_meta = [
			'pm_index' => $this->pm_index,
			'type'     => [
				'label'  => [
					'title' => \__( 'Article Type', 'the-seo-framework-extension-manager' ),
					'desc'  => \__( 'Set the article type.', 'the-seo-framework-extension-manager' ),
					'link'  => 'https://theseoframework.com/extensions/articles/#usage/types',
				],
				'option' => [
					'name'          => 'type',
					'input'         => 'select',
					'default'       => $_default,
					'value'         => static::filter_article_type( $this->get_post_meta( 'type', $_default ) ),
					'select_values' => static::filter_article_keys( [
						'disabled'    => \__( '&mdash; Disabled &mdash;', 'the-seo-framework-extension-manager' ),
						'Article'     => \__( 'Article', 'the-seo-framework-extension-manager' ),
						'NewsArticle' => \__( 'News Article', 'the-seo-framework-extension-manager' ),
						'BlogPosting' => \__( 'Blog Posting', 'the-seo-framework-extension-manager' ),
					] ),
				],
			],
		];

		\TSF_Extension_Manager\InpostGUI::register_view(
			$this->get_view_location( 'inpost/inpost' ),
			compact( 'post_meta' ),
			'structure'
		);
	}

	/**
	 * Prepares quick-edit views.
	 *
	 * @since 2.1.0
	 * @access private
	 * @uses \TSF_Extension_Manager\ListEdit
	 *
	 * @param string $post_type The current post type.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	public function _prepare_quick_views( $post_type, $taxonomy ) {

		if ( $taxonomy ) return;
		if ( ! $this->is_post_type_supported( $post_type ) ) return;

		\TSF_Extension_Manager\ListEdit::activate_quick_section( 'structure' );

		$pm_index = $this->pm_index;

		$post_meta = [
			'type' => [
				'label'   => \__( 'Article Type', 'the-seo-framework-extension-manager' ),
				'options' => static::filter_article_keys( [
					'disabled'    => \__( '&mdash; Disabled &mdash;', 'the-seo-framework-extension-manager' ),
					'Article'     => \__( 'Article', 'the-seo-framework-extension-manager' ),
					'NewsArticle' => \__( 'News Article', 'the-seo-framework-extension-manager' ),
					'BlogPosting' => \__( 'Blog Posting', 'the-seo-framework-extension-manager' ),
				] ),
			],
		];

		\TSF_Extension_Manager\ListEdit::register_quick_view(
			$this->get_view_location( 'list/quickedit' ),
			get_defined_vars(),
			'structure',
			10
		);
	}

	/**
	 * Prepares bulk-edit views.
	 *
	 * @since 2.1.0
	 * @access private
	 * @uses \TSF_Extension_Manager\ListEdit
	 *
	 * @param string $post_type The current post type.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	public function _prepare_bulk_views( $post_type, $taxonomy ) {

		if ( $taxonomy ) return;
		if ( ! $this->is_post_type_supported( $post_type ) ) return;

		\TSF_Extension_Manager\ListEdit::activate_bulk_section( 'structure' );

		$pm_index = $this->pm_index;

		$post_meta = [
			'type' => [
				'label'   => \__( 'Article Type', 'the-seo-framework-extension-manager' ),
				'options' => [
					'nochange' => \__( '&mdash; No Change &mdash;', 'default' ),
				] + static::filter_article_keys( [
					'disabled'    => \__( '&mdash; Disabled &mdash;', 'the-seo-framework-extension-manager' ),
					'Article'     => \__( 'Article', 'the-seo-framework-extension-manager' ),
					'NewsArticle' => \__( 'News Article', 'the-seo-framework-extension-manager' ),
					'BlogPosting' => \__( 'Blog Posting', 'the-seo-framework-extension-manager' ),
				] ),
			],
		];

		\TSF_Extension_Manager\ListEdit::register_bulk_view(
			$this->get_view_location( 'list/bulkedit' ),
			get_defined_vars(),
			'structure',
			10
		);
	}

	/**
	 * Adds list table data, so that the quick-edit values are correctly preselected.
	 *
	 * @since 2.1.0
	 *
	 * @param array $data  The current LE data.
	 * @param array $query The current item's query.
	 */
	public function _add_list_table_data( $data, $query ) {

		// This should never happen...
		if ( ! empty( $query['taxonomy'] ) ) return;

		static $default = null;
		if ( ! $default ) {
			$post_type = \the_seo_framework()->get_admin_post_type();
			$settings  = $this->get_option( 'post_types' );
			$default   = static::filter_article_type( \tsf_extension_manager()->coalesce_var( $settings[ $post_type ]['default_type'], 'Article' ) );
		}

		$this->set_extension_post_meta_id( $query['id'] );

		$data['tsfem'][ $this->pm_index ] = [
			'type' => [
				'value'    => static::filter_article_type( $this->get_post_meta( 'type', $default ) ),
				'isSelect' => true,
			],
		];

		return $data;
	}

	/**
	 * Processes quick-edit data for Articles.
	 *
	 * Does NOT delete data on empty POST data.
	 *
	 * @since 2.1.0
	 *
	 * @param \WP_Post $post The post object
	 * @param array    $data The quick-edit data.
	 */
	public function _save_meta_quick_edit( $post, $data ) {

		$this->set_extension_post_meta_id( $post->ID );

		$store = [];
		foreach ( (array) $data[ $this->pm_index ] as $key => $value ) :
			switch ( $key ) :
				case 'type':
					$store[ $key ] = static::_sanitize_option_article_type( $value );
					break;

				default:
					break;
			endswitch;
		endforeach;

		foreach ( $store as $key => $value ) {
			$this->update_post_meta( $key, $value );
		}
	}

	/**
	 * Processes bulk-edit data for Articles.
	 *
	 * @since 2.1.0
	 *
	 * @param \WP_Post $post The post object
	 * @param array    $data The quick-edit data.
	 */
	public function _save_meta_bulk_edit( $post, $data ) {

		static $store = null;

		if ( null === $store ) {
			$store = [];

			if ( empty( $data[ $this->pm_index ] ) ) return;

			foreach ( (array) $data[ $this->pm_index ] as $key => $value ) :
				switch ( $key ) :
					case 'type':
						if ( 'nochange' === $value ) continue 2;
						$store[ $key ] = static::_sanitize_option_article_type( $value );
						break;

					default:
						break;
				endswitch;
			endforeach;
		}

		// Unlike the post-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		if ( ! $store ) return;

		$this->set_extension_post_meta_id( $post->ID );

		foreach ( $store as $key => $value ) {
			$this->update_post_meta( $key, $value );
		}
	}

	/**
	 * Saves or deletes post meta.
	 *
	 * @since 1.2.0
	 * @see \TSF_Extension_Manager\InpostGUI::_verify_nonce()
	 * @see action 'tsfem_inpostgui_verified_nonce'
	 *
	 * @param \WP_Post      $post              The post object.
	 * @param array|null    $data              The meta data.
	 * @param int (bitwise) $save_access_state The state the save is in.
	 */
	public function _save_meta( $post, $data, $save_access_state ) {

		if ( ! \TSF_Extension_Manager\InpostGUI::is_state_safe( $save_access_state ) )
			return;

		if ( empty( $data[ $this->pm_index ] ) )
			return;

		$this->set_extension_post_meta_id( $post->ID );

		$store = [];
		foreach ( $data[ $this->pm_index ] as $key => $value ) :
			switch ( $key ) {
				case 'type':
					$store[ $key ] = static::_sanitize_option_article_type( $value );
					break;

				default:
					break;
			}
		endforeach;

		if ( empty( $store ) ) {
			//= Delete everything. Using defaults.
			$this->delete_post_meta_index();
		} else {
			foreach ( $store as $key => $value ) {
				$this->update_post_meta( $key, $value );
			}
		}
	}

	/**
	 * Returns view location.
	 *
	 * @since 1.2.0
	 *
	 * @param string $view The relative file location and name without '.php'.
	 * @return string The view file location.
	 */
	protected function get_view_location( $view ) {
		return TSFEM_E_ARTICLES_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
	}
}
