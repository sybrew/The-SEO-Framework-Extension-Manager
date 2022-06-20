<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Admin
 */

namespace TSF_Extension_Manager\Extension\Transport;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

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
 * Transports HTML.
 */
// use \TSF_Extension_Manager\HTML as HTML;

/**
 * Require user interface trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/ui' );

/**
 * Require extension settings trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/options' );

/**
 * Require extension forms trait.
 *
 * @since 1.0.0
 */
// \TSF_Extension_Manager\_load_trait( 'extension/forms' );

/**
 * Require extension forms trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/views' );

/**
 * Class TSF_Extension_Manager\Extension\Transport\Admin
 *
 * Holds extension admin page methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin {
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface,
		\TSF_Extension_Manager\UI,
		// \TSF_Extension_Manager\Extension_Options,
		// \TSF_Extension_Manager\Extension_Forms,
		\TSF_Extension_Manager\Extension_Views,
		\TSF_Extension_Manager\Error;

	/**
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 */
	protected $ajax_nonce_action;

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page hook.
	 */
	protected $transport_menu_page_hook;

	/**
	 * The extension page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	protected $transport_page_slug;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		/**
		 * @see trait TSF_Extension_Manager\Extension_Views
		 */
		$this->view_location_base = TSFEM_E_TRANSPORT_DIR_PATH . 'views' . DIRECTORY_SEPARATOR;

		$this->ajax_nonce_action = 'tsfem_e_transport_ajax';

		$this->importers = [
			'WordPress_SEO' => [
				'title'     => 'Yoost SEO',
				'importers' => [
					'settings' => false, // Let's keep this at false, for now. Perhaps we want to move the homepage stuff, but that's tricky.
					'postmeta' => [
						'supports'  => [
							'title',
							'description',
							'canonical_url',
							'noindex',
							'nofollow',
							'noarchive',
							'og_title',
							'og_description',
							'twitter_title',
							'twitter_description',
							'og_image',
							'article_type',
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
							'noindex',
							'nofollow',
							'noarchive',
							'og_title',
							'og_description',
							'twitter_title',
							'twitter_description',
						],
					],
					'termmeta' => [
						'supports'  => [
							'title',
							'description',
							'canonical_url',
							'noindex',
							'nofollow',
							'noarchive',
							'og_title',
							'og_description',
							'twitter_title',
							'twitter_description',
							'og_image',
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
							'noindex',
							'nofollow',
							'noarchive',
							'og_title',
							'og_description',
							'twitter_title',
							'twitter_description',
						],
					],
				],
			],
			// TODO
			// 'SEO_By_Rank_Math' => [
			// 	'title' => 'Rank Math SEO',
			// ],
			// 'WP_SEOPress' => [
			// 	'title' => 'SEOPress',
			// ],
			// 'All_In_One_SEO_Pack' => [
			// 	'title' => 'All In One SEO',
			// ],
		];

		$this->transport_page_slug = 'theseoframework-transport';

		/**
		 * Set error notice option.
		 *
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_transport_error_notice_option';

		// Nothing to do here...
		if ( \tsf()->is_headless['settings'] ) return;

		// Initialize menu links
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		// Initialize Transport page actions.
		\add_action( 'admin_init', [ $this, '_load_transport_admin_actions' ] );

		// Update POST listener.
		\add_action( 'wp_ajax_tsfem_e_transport', [ $this, '_wp_ajax_transport' ] );
	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @uses \TSF_Extension_Manager\can_do_extension_settings()
	 * @access private
	 */
	public function _init_menu() {
		if ( \TSF_Extension_Manager\can_do_extension_settings() )
			\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 100 );
	}

	/**
	 * Adds menu link for transport, when possible, underneath The SEO Framework
	 * SEO settings.
	 *
	 * @since 1.0.0
	 * @uses \tsf()->seo_settings_page_slug.
	 * @access private
	 */
	public function _add_menu_link() {
		$this->transport_menu_page_hook = \add_submenu_page(
			\tsf()->seo_settings_page_slug, // parent_slug
			'Transport', // page_title
			'Transport', // menu_title
			TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE,
			$this->transport_page_slug, // menu_slug
			[ $this, '_init_transport_page' ] // callback
		);
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 *
	 * @since 1.0.0
	 * @uses $this->transport_menu_page_hook variable.
	 * @access private
	 */
	public function _load_transport_admin_actions() {
		\add_action( "load-{$this->transport_menu_page_hook}", [ $this, '_do_transport_admin_actions' ] );
	}

	/**
	 * Handles Transport AJAX POST requests.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void If nonce failed.
	 */
	public function _wp_ajax_transport() {

		if ( ! \wp_doing_ajax() ) exit;

		if ( ! \TSF_Extension_Manager\can_do_extension_settings() || ! \check_ajax_referer( $this->ajax_nonce_action, 'nonce', false ) )
			\tsf_extension_manager()->send_json( [ 'results' => $this->get_ajax_notice( false, 1069001 ) ], 'failure' ); // nice

		switch ( $_REQUEST['handle'] ?? null ) :
			case 'import':
				( new Handler )->_import( $this->importers );
				break;

			default:
				\tsf_extension_manager()->send_json( [ 'results' => $this->get_ajax_notice( false, 1060106 ) ], 'failure' );
				break;
		endswitch;
	}
	/**
	 * Initializes user interface styles, scripts and footer.
	 *
	 * @since 1.0.0
	 */
	protected function init_tsfem_ui() {

		/**
		 * Set UI hook.
		 *
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->ui_hook = $this->transport_menu_page_hook;

		\add_action( 'tsfem_before_enqueue_scripts', [ $this, '_register_transport_scripts' ] );

		$this->init_ui();
	}

	/**
	 * Registers default Transport admin scripts.
	 * Also registers TSF scripts, for TT (tooltip) support.
	 *
	 * @since 1.0.0
	 * @access private
	 * @internal
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	public function _register_transport_scripts( $scripts ) {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) ) return;

		$scripts::register( [
			// [
			// 	'id'       => 'tsfem-transport',
			// 	'type'     => 'css',
			// 	'deps'     => [ 'tsf-tt', 'tsfem-ui' ],
			// 	'autoload' => true,
			// 	'hasrtl'   => true,
			// 	'name'     => 'tsfem-transport',
			// 	'base'     => TSFEM_E_TRANSPORT_DIR_URL . 'lib/css/',
			// 	'ver'      => TSFEM_E_TRANSPORT_VERSION,
			// 	'inline'   => null,
			// ],
			[
				'id'       => 'tsfem-transport',
				'type'     => 'js',
				'deps'     => [ 'tsf-tt', 'tsfem-ui' ],
				'autoload' => true,
				'name'     => 'tsfem-transport',
				'base'     => TSFEM_E_TRANSPORT_DIR_URL . 'lib/js/',
				'ver'      => TSFEM_E_TRANSPORT_VERSION,
				'l10n'     => [
					'name' => 'tsfem_e_transportL10n',
					'data' => [
						// This won't ever run when the user can't. But, sanity.
						'nonce' => \TSF_Extension_Manager\can_do_extension_settings() ? \wp_create_nonce( $this->ajax_nonce_action ) : '',
						'i18n'  => [
							'optionNames' => [
								'settings' => \esc_html__( 'SEO Settings', 'the-seo-framework-extension-manager' ),
								'postmeta' => \esc_html__( 'Post Metadata', 'the-seo-framework-extension-manager' ),
								'termmeta' => \esc_html__( 'Term Metadata', 'the-seo-framework-extension-manager' ),
							],
							'logMessages' => [
								/* translators: %s = plugin name, such as Yoost SEO */
								'requestImport'    => \esc_html__( 'Request Importer for %s&hellip;', 'the-seo-framework-extension-manager' ),
								'unknownError'     => \esc_html__( 'Unknown error', 'the-seo-framework-extension-manager' ),
								'unknownErrorFull' => \esc_html__( 'An unknown error occured. Please refresh this page and try again.', 'the-seo-framework-extension-manager' ),
							],
						],
					],
				],
			],
		] );
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 * Early enough for admin_notices and admin_head :).
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool True on actions loaded, false on second load.
	 */
	public function _do_transport_admin_actions() {

		if ( false === $this->is_transport_page() )
			return false;

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) )
			return false;

		/**
		 * Initialize user interface.
		 *
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_tsfem_ui();

		/**
		 * Initialize error interface.
		 *
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->init_errors();

		// Add something special.
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		return true;
	}
	/**
	 * Determines whether we're on the transport overview page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_transport_page() {
		// Don't load from $_GET request.
		return \The_SEO_Framework\memo( \tsf()->is_menu_page( $this->transport_menu_page_hook ) );
	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_transport_page() {
		\add_action( 'tsfem_header', [ $this, '_output_transport_header' ] );
		\add_action( 'tsfem_content', [ $this, '_output_transport_content' ] );
		\add_action( 'tsfem_footer', [ $this, '_output_transport_footer' ] );

		$this->ui_wrap( 'panes' );
	}

	/**
	 * Outputs transport header.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	public function _output_transport_header() {
		$this->get_view( 'layout/general/top' );
	}

	/**
	 * Outputs transport content.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	public function _output_transport_content() {
		$this->get_view( 'layout/pages/transport' );
	}

	/**
	 * Outputs transport footer.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	public function _output_transport_footer() {
		$this->get_view( 'layout/general/footer' );
	}

	/**
	 * Outputs theme color meta tag for Vivaldi and mobile browsers.
	 * Does not always work. So many browser bugs... It's just fancy.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _output_theme_color_meta() {
		$this->get_view( 'layout/general/meta' );
	}

	/**
	 * Outpouts importer pane.
	 *
	 * @since 1.0.0
	 */
	public function _importer_overview() {
		$this->get_view( 'layout/panes/importer' );
	}

	/**
	 * Outputs logger pane.
	 *
	 * @since 1.0.0
	 */
	public function _logger_overview() {
		$this->get_view( 'layout/panes/logger' );
	}
}
