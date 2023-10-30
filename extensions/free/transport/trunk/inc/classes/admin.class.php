<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Admin
 */

namespace TSF_Extension_Manager\Extension\Transport;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	is_headless,
};

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

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
 * Require user interface trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/ui' );

/**
 * Require extension views trait.
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
		\TSF_Extension_Manager\Extension_Views,
		\TSF_Extension_Manager\Error;

	/**
	 * @since 1.0.0
	 * @var string The validation nonce name.
	 */
	private $ajax_nonce_action;

	/**
	 * @since 1.0.0
	 * @var string Page hook name
	 */
	private $transport_menu_page_hook;

	/**
	 * @since 1.0.0
	 * @var string Page ID/Slug
	 */
	private $transport_page_slug;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		/**
		 * @see trait TSF_Extension_Manager\Extension_Views
		 */
		$this->view_location_base = \TSFEM_E_TRANSPORT_DIR_PATH . 'views' . \DIRECTORY_SEPARATOR;

		$this->ajax_nonce_action = 'tsfem_e_transport_ajax';

		$this->transport_page_slug = 'theseoframework-transport';

		/**
		 * Set error notice option.
		 *
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_transport_error_notice_option';

		// Nothing to do here...
		if ( is_headless( 'settings' ) ) return;

		// Initialize menu links
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		// Initialize Transport page actions.
		\add_action( 'admin_init', [ $this, '_load_transport_admin_actions' ] );

		// Update POST listener.
		\add_action( 'wp_ajax_tsfem_e_transport', [ $this, '_wp_ajax_transport' ] );
	}

	/**
	 * Returns a list of supported importers.
	 *
	 * @since 1.0.0
	 *
	 * @return array A list of supported importers.
	 */
	private function get_importers() {
		return [
			''                 => [
				'title'     => sprintf(
					'&mdash; %s &mdash;',
					\__( 'Select plugin', 'the-seo-framework-extension-manager' )
				),
				'importers' => [
					'settings' => false,
					'postmeta' => false,
					'termmeta' => false,
				],
			],
			'WordPress_SEO'    => [
				'title'     => 'Yoast SEO',
				'importers' => [
					'settings' => false, // Let's keep this at false, for now. Perhaps we want to move the homepage stuff, but that's tricky.
					'postmeta' => [
						'supports'  => [
							'title',
							'description',
							'og_title',
							'og_description',
							'og_image',
							'og_image_id',
							'twitter_title',
							'twitter_description',
							'canonical_url',
							'noindex',
							'nofollow',
							'noarchive',
							'primary_term',
							// 'article_type', TODO?
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
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
							'og_title',
							'og_description',
							'og_image',
							'twitter_title',
							'twitter_description',
							'canonical_url',
							'noindex',
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
							'og_title',
							'og_description',
							'twitter_title',
							'twitter_description',
							'canonical_url',
						],
					],
				],
			],
			'SEO_By_Rank_Math' => [
				'title'     => 'Rank Math',
				'importers' => [
					'settings' => false, // Let's keep this at false, for now. Perhaps we want to move the homepage stuff, but that's tricky.
					'postmeta' => [
						'supports'  => [
							'title',
							'description',
							'og_title',
							'og_description',
							'og_image',
							'og_image_id',
							'twitter_title',
							'twitter_description',
							'canonical_url',
							'noindex',
							'nofollow',
							'noarchive',
							// 'article_type', TODO?
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
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
							'og_title',
							'og_description',
							'og_image',
							'og_image_id',
							'twitter_title',
							'twitter_description',
							'canonical_url',
							'noindex',
							'nofollow',
							'noarchive',
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
							'og_title',
							'og_description',
							'twitter_title',
							'twitter_description',
						],
					],
				],
			],
			'WP_SEOPress'      => [
				'title'     => 'SEOPress',
				'importers' => [
					'settings' => false, // Let's keep this at false, for now. Perhaps we want to move the homepage stuff, but that's tricky.
					'postmeta' => [
						'supports'  => [
							'title',
							'description',
							'og_title',
							'og_description',
							'og_image',
							'og_image_id',
							'twitter_title',
							'twitter_description',
							'canonical_url',
							'noindex',
							'nofollow',
							'noarchive',
							'redirect',
							// 'article_type', TODO?
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
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
							'og_title',
							'og_description',
							'og_image',
							'og_image_id',
							'twitter_title',
							'twitter_description',
							'canonical_url',
							'noindex',
							'nofollow',
							'noarchive',
							'redirect',
						],
						'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
							'title',
							'description',
							'og_title',
							'og_description',
							'twitter_title',
							'twitter_description',
						],
					],
				],
			],
			// TODO
			// 'All_In_One_SEO_Pack' => [
			// 	'title' => 'All In One SEO',
			// ],
		];
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
	 * @access private
	 */
	public function _add_menu_link() {

		$this->transport_menu_page_hook = \add_submenu_page(
			\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
				? \tsf()->admin()->menu()->get_top_menu_args()['menu_slug'] // parent_slug
				: \tsf()->seo_settings_page_slug,
			'Transport &beta;eta', // page_title
			'Transport (&beta;eta)', // menu_title
			\TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE,
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
			\tsfem()->send_json( [ 'results' => $this->get_ajax_notice( false, 1069001 ) ], 'failure' ); // nice

		switch ( $_REQUEST['handle'] ?? null ) :
			case 'import':
				( new Handler )->_import( $this->get_importers() );
				break;

			default:
				\tsfem()->send_json( [ 'results' => $this->get_ajax_notice( false, 1060106 ) ], 'failure' );
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

		\add_action( 'the_seo_framework_scripts', [ $this, '_register_transport_scripts' ] );

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
	 * @param array $scripts The default CSS and JS loader settings.
	 * @return array More CSS and JS loaders.
	 */
	public function _register_transport_scripts( $scripts ) {

		$scripts[] = [
			'id'       => 'tsfem-transport',
			'type'     => 'js',
			'deps'     => [ 'tsf-tt', 'tsfem-worker', 'tsfem-ui' ],
			'autoload' => true,
			'name'     => 'tsfem-transport',
			'base'     => \TSFEM_E_TRANSPORT_DIR_URL . 'lib/js/',
			'ver'      => \TSFEM_E_TRANSPORT_VERSION,
			'l10n'     => [
				'name' => 'tsfem_e_transportL10n',
				'data' => [
					// This won't ever run when the user can't. But, sanity.
					'nonce'   => \TSF_Extension_Manager\can_do_extension_settings() ? \wp_create_nonce( $this->ajax_nonce_action ) : '',
					'i18n'    => [
						'optionNames' => [
							'settings' => \esc_html__( 'SEO Settings', 'the-seo-framework-extension-manager' ),
							'postmeta' => \esc_html__( 'Post Metadata', 'the-seo-framework-extension-manager' ),
							'termmeta' => \esc_html__( 'Term Metadata', 'the-seo-framework-extension-manager' ),
						],
						'logMessages' => [
							/* translators: %s = plugin name, such as Yoast SEO */
							'requestImport'     => \esc_html__( 'Request Importer for %s&hellip;', 'the-seo-framework-extension-manager' ),
							'unknownError'      => \esc_html__( 'Unknown error', 'the-seo-framework-extension-manager' ),
							'unknownErrorFull'  => \esc_html__( 'An unknown error occured. Please refresh this page and try again.', 'the-seo-framework-extension-manager' ),
							/* translators: %d = Seconds */
							'retryCountdown'    => \esc_html__( 'Retrying in %d&hellip;', 'the-seo-framework-extension-manager' ),
							'retryLimitReached' => \esc_html__( 'Automated retry limit reached.', 'the-seo-framework-extension-manager' ),
						],
					],
					'scripts' => [
						'sseWorker' => $this->get_sse_worker_location(),
					],
				],
			],
		];

		return $scripts;
	}

	/**
	 * Returns the SSE worker file location.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_sse_worker_location() {
		$min = \SCRIPT_DEBUG ? '' : '.min';
		return \esc_url( \set_url_scheme( \TSFEM_E_TRANSPORT_DIR_URL . "lib/js/sse.worker{$min}.js" ) );
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

		if ( ! $this->is_transport_page() )
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

		// Add something special for Vivaldi & Android.
		\add_action( 'admin_head', [ \tsfem(), '_output_theme_color_meta' ], 0 );

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
		return $this->transport_menu_page_hook
			&& ( $GLOBALS['page_hook'] ?? null ) === $this->transport_menu_page_hook;
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

	/**
	 * Outputs logger footer pane.
	 *
	 * @since 1.0.0
	 */
	public function _logger_bottom_wrap() {
		printf(
			'<div class=tsf-tooltip-wrap><button type=button id=tsfem-e-transport-copy-log class="%s" data-copyconfirm="%s" data-copyfail="%s">%s</button></div>',
			'hide-if-no-tsf-js tsfem-button tsfem-button-clipboard tsf-tooltip-item',
			\esc_attr__( 'Copied!', 'the-seo-framework-extension-manager' ),
			\esc_attr__( 'Failed to copy', 'the-seo-framework-extension-manager' ),
			\esc_html__( 'Copy log', 'the-seo-framework-extension-manager' )
		);
		printf(
			'<button type=button id=tsfem-e-transport-scroll-log class="%s">%s</button>',
			'hide-if-no-tsf-js tsfem-button-primary tsfem-button-primary-dark tsfem-button-down',
			\esc_html__( 'Scroll to bottom', 'the-seo-framework-extension-manager' )
		);
	}
}
