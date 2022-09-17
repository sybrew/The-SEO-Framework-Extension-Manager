<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin
 */

namespace TSF_Extension_Manager\Extension\Monitor;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

$tsfem = \tsfem();

if ( $tsfem->_has_died() or false === ( $tsfem->_verify_instance( $_instance, $bits[1] ) or $tsfem->_maybe_die() ) )
	return;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Imports HTML.
 */
use \TSF_Extension_Manager\HTML as HTML;

/**
 * Require user interface trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/ui' );

/**
 * Require extension options trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/options' );

/**
 * Require extension forms trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/forms' );

/**
 * Require time factory trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'factory/time' );

/**
 * Class TSF_Extension_Manager\Extension\Monitor\Admin
 *
 * Holds extension admin page methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 101xxxx
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin extends Api {
	use \TSF_Extension_Manager\Construct_Master_Once_Interface,
		\TSF_Extension_Manager\Time,
		\TSF_Extension_Manager\UI,
		\TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Extension_Forms,
		\TSF_Extension_Manager\Error;

	/**
	 * @since 1.0.0
	 * @var string The validation nonce name.
	 */
	protected $nonce_name;

	/**
	 * @since 1.0.0
	 * @var string The validation request name.
	 */
	protected $request_name = [];

	/**
	 * @since 1.0.0
	 * @var string The validation nonce action.
	 */
	protected $nonce_action = [];

	/**
	 * @since 1.0.0
	 * @var string Page hook name.
	 */
	protected $monitor_menu_page_hook;

	/**
	 * @since 1.0.0
	 * @var string Page ID/Slug.
	 */
	protected $monitor_page_slug;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->nonce_name = 'tsfem_e_monitor_nonce_name';
		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		$this->request_name = [
			// Reference convenience.
			'default' => 'default',

			// Connect site to API.
			'connect' => 'connect',

			// Disconnect site from API.
			'disconnect' => 'disconnect',

			// Data fetch.
			'fetch' => 'fetch',

			// Request crawl.
			'crawl' => 'crawl',

			// Settings update.
			'update' => 'update',

			// Fix instance.
			'fix' => 'fix',
		];
		$this->nonce_action = [
			// Reference convenience.
			'default' => 'tsfem_e_monitor_nonce_action',

			// Connect site to API.
			'connect' => 'tsfem_e_monitor_nonce_action_connect_site',

			// Disconnect site from API.
			'disconnect' => 'tsfem_e_monitor_nonce_action_disconnect_site',

			// Data fetch.
			'fetch' => 'tsfem_e_monitor_nonce_action_remote_fetch',

			// Request crawl.
			'crawl' => 'tsfem_e_monitor_nonce_action_remote_crawl',

			// Settings update.
			'update' => 'tsfem_e_monitor_nonce_action_remote_update',

			// Fix instance.
			'fix' => 'tsfem_e_monitor_nonce_action_remote_fix',
		];
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

		$this->monitor_page_slug = 'theseoframework-monitor';

		/**
		 * Set error notice option.
		 *
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_monitor_error_notice_option';

		/**
		 * Set options index.
		 *
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'monitor';

		// Nothing to do here...
		if ( \tsf()->is_headless['settings'] ) return;

		// Initialize menu links
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		// Initialize Monitor page actions.
		\add_action( 'admin_init', [ $this, '_load_monitor_admin_actions' ] );

		// Update POST listener.
		\add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		// AJAX update listener.
		\add_action( 'wp_ajax_tsfem_e_monitor_update', [ $this, '_wp_ajax_update_settings' ] );

		// AJAX fetch listener.
		\add_action( 'wp_ajax_tsfem_e_monitor_fetch', [ $this, '_wp_ajax_fetch_data' ] );

		// AJAX crawl listener.
		\add_action( 'wp_ajax_tsfem_e_monitor_crawl', [ $this, '_wp_ajax_request_crawl' ] );

		// AJAX get required fix listener.
		\add_action( 'wp_ajax_tsfem_e_monitor_get_requires_fix', [ $this, '_wp_ajax_get_requires_fix' ] );
	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @since 1.2.6 The extension access level is now controlled via another constant.
	 * @uses \TSF_Extension_Manager\can_do_extension_settings()
	 * @access private
	 */
	public function _init_menu() {
		if ( \TSF_Extension_Manager\can_do_extension_settings() )
			\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 100 );
	}

	/**
	 * Adds menu link for monitor, when possible, underneath The SEO Framework
	 * SEO settings.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added TSF v3.1 compat.
	 * @uses \tsf()->seo_settings_page_slug.
	 * @access private
	 */
	public function _add_menu_link() {

		$menu = [
			'parent_slug' => \tsf()->seo_settings_page_slug,
			'page_title'  => 'Monitor',
			'menu_title'  => 'Monitor',
			'capability'  => TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE,
			'menu_slug'   => $this->monitor_page_slug,
			'callback'    => [ $this, '_init_monitor_page' ],
		];

		$this->monitor_menu_page_hook = \add_submenu_page(
			$menu['parent_slug'],
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 *
	 * @since 1.0.0
	 * @uses $this->monitor_menu_page_hook variable.
	 * @access private
	 */
	public function _load_monitor_admin_actions() {
		\add_action( 'load-' . $this->monitor_menu_page_hook, [ $this, '_do_monitor_admin_actions' ] );
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
	public function _do_monitor_admin_actions() {

		if ( false === $this->is_monitor_page() )
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

		// Add something special for Vivaldi
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		return true;
	}

	/**
	 * Handles Monitor POST requests.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void If nonce failed.
	 */
	public function _handle_update_post() {

		// phpcs:disable, WordPress.Security.NonceVerification -- No data is processed in this method.

		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ]['nonce-action'] ) )
			return;

		$options = $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ];

		if ( false === $this->handle_update_nonce( $options['nonce-action'], false ) )
			return;

		switch ( $options['nonce-action'] ) :
			case $this->request_name['connect']:
				$this->api_register_site();
				break;

			case $this->request_name['fix']:
				$this->api_register_site( false );
				break;

			case $this->request_name['disconnect']:
				$this->api_disconnect_site();
				break;

			case $this->request_name['crawl']:
				$this->api_request_crawl();
				break;

			case $this->request_name['fetch']:
				$this->api_get_remote_data();
				break;

			case $this->request_name['update']:
				$this->api_update_remote_settings( $options );
				break;

			default:
				$this->set_error_notice( [ 1010101 => '' ] );
				break;
		endswitch;

		$args = WP_DEBUG ? [ 'did-' . $options['nonce-action'] => 'true' ] : [];
		\tsf()->admin_redirect( $this->monitor_page_slug, $args );
		exit;

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Checks the Extension's page nonce. Returns false if nonce can't be found
	 * or if user isn't allowed to perform nonce.
	 * Performs wp_die() when nonce verification fails.
	 *
	 * Never run a sensitive function when it's returning false. This means no
	 * nonce can or has been been verified.
	 *
	 * @since 1.0.0
	 * @since 1.2.6 The extension access level is now controlled via another constant.
	 *
	 * @param string $key        The nonce action used for caching.
	 * @param bool   $check_post Whether to check for POST variables containing TSFEM settings.
	 * @return bool True if verified and matches. False if can't verify.
	 */
	protected function handle_update_nonce( $key = 'default', $check_post = true ) {

		static $validated = [];

		if ( isset( $validated[ $key ] ) )
			return $validated[ $key ];

		if ( ! \TSF_Extension_Manager\can_do_extension_settings() )
			return $validated[ $key ] = false;

		if ( $check_post ) {
			/**
			 * If this page doesn't parse the site options,
			 * there's no need to check them on each request.
			 */
			if ( ! isset( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] )
			|| ! \is_array( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] )
			) {
				return $validated[ $key ] = false;
			}
		}

		$result = isset( $_POST[ $this->nonce_name ] )
				? \wp_verify_nonce( \wp_unslash( $_POST[ $this->nonce_name ] ), $this->nonce_action[ $key ] )
				: false;

		if ( false === $result ) {
			// Nonce failed. Set error notice and reload.
			$this->set_error_notice( [ 1019001 => '' ] );
			\tsf()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		return $validated[ $key ] = (bool) $result;
	}

	/**
	 * Updates settings.
	 *
	 * @since 1.1.0
	 * @since 1.2.6 The extension access level is now controlled via another constant.
	 * @access private
	 */
	public function _wp_ajax_update_settings() {

		if ( \wp_doing_ajax() ) :
			if ( \TSF_Extension_Manager\can_do_extension_settings() ) :
				$tsfem  = \tsfem();
				$option = '';
				$send   = [];
				if ( \check_ajax_referer( 'tsfem-e-monitor-ajax-nonce', 'nonce', false ) ) {
					// Option is cleaned and requires unpacking.
					$option = isset( $_POST['option'] ) ? $tsfem->s_ajax_string( $_POST['option'] ) : ''; // Sanitization, input var OK.
					$value  = isset( $_POST['value'] ) ? \absint( $_POST['value'] ) : 0;                  // Input var OK.
				} else {
					$send['results'] = $this->get_ajax_notice( false, 1019002 );
				}

				if ( $option ) {
					// Unpack option.
					$_option = \TSF_Extension_Manager\FormFieldParser::get_last_value( \TSF_Extension_Manager\FormFieldParser::umatosa( $option ) );
					$options = [
						$_option => $value,
					];

					$response = $this->api_update_remote_settings( $options, true );

					// Get new options, regardless of response.
					foreach ( [ 'uptime_setting', 'performance_setting' ] as $setting ) {
						$send['settings'][ $setting ] = $this->get_option( $setting, 0 );
					}

					$type            = empty( $response['success'] ) ? 'failure' : 'success';
					$send['results'] = $response;
				} else {
					$send['results'] = $this->get_ajax_notice( false, 1010702 );
				}

				$tsfem->send_json( $send, $type ?? 'failure' );
			endif;
		endif;

		exit;
	}

	/**
	 * Fetches Monitor Data through AJAX and echos the output through AJAX response.
	 *
	 * @since 1.0.0
	 * @since 1.2.6 The extension access level is now controlled via another constant.
	 * @TODO update to newer ajax handler.
	 * @access private
	 */
	public function _wp_ajax_fetch_data() {

		if ( \wp_doing_ajax() ) :
			if ( \TSF_Extension_Manager\can_do_extension_settings() ) :

				if ( ! \check_ajax_referer( 'tsfem-e-monitor-ajax-nonce', 'nonce', false ) ) {
					$status = [
						'content' => null,
						'type'    => 'unknown',
						'notice'  => \esc_html__( 'Something went wrong. Please reload the page.', 'the-seo-framework-extension-manager' ),
					];
				} else {
					$timeout = isset( $_POST['remote_data_timeout'] ) ? \absint( $_POST['remote_data_timeout'] ) : 0; // Input var OK.

					$current_timeout = $this->get_remote_data_timeout();

					if ( $this->is_remote_data_expired() || ( $timeout + $this->get_remote_data_buffer() ) < $current_timeout ) :
						// There's possibly new data found. This should certainly be true with statistics.
						$api = $this->api_get_remote_data( true );

						switch ( $api['code'] ) :
							case 1010602:
							case 1010603:
								$type = 'requires_fix';
								break;

							default:
								$type = $api['success'] ? 'success' : 'failure';
								break;
						endswitch;

						$status = [
							'content' => [
								'issues'   => $this->ajax_get_issues_data(),
								'lc'       => $this->get_last_crawled_field(),
								'settings' => [
									'uptime_setting'      => $this->get_option( 'uptime_setting', 0 ),
									'performance_setting' => $this->get_option( 'performance_setting', 0 ),
								],
							],
							'type'    => $type,
							'notice'  => $api['notice'],
							'code'    => $api['code'],
							// Get new timeout.
							'timeout' => $current_timeout = $this->get_remote_data_timeout(),
						];
					else :
						// No new data has been found.
						$seconds = $current_timeout + $this->get_remote_data_buffer() - time();
						$status  = [
							'content' => null,
							'type'    => 'yield_unchanged',
							'notice'  => $this->get_try_again_notice( $seconds ),
							'timeout' => $current_timeout,
						];
					endif;
				}

				if ( WP_DEBUG ) {
					$response = [
						'status'   => $status,
						'timeout'  => [
							'old' => $timeout ?? null,
							'new' => $current_timeout ?? null,
						],
						'response' => isset( $api ) ? [ 'response' => $api ] : [],
					];
				} else {
					$response = [ 'status' => $status ];
				}

				\tsfem()->send_json( $response, null );
			endif;
		endif;

		exit;
	}

	/**
	 * Requests Monitor to crawl the site and echos the output through AJAX response.
	 *
	 * @since 1.0.0
	 * @since 1.2.6 The extension access level is now controlled via another constant.
	 * @TODO update to newer ajax handler.
	 * @access private
	 */
	public function _wp_ajax_request_crawl() {

		if ( \wp_doing_ajax() ) :
			if ( \TSF_Extension_Manager\can_do_extension_settings() ) :

				$timeout = null;

				if ( ! \check_ajax_referer( 'tsfem-e-monitor-ajax-nonce', 'nonce', false ) ) {
					$status = [
						'type'   => 'unknown',
						'notice' => \esc_html__( 'Something went wrong. Please reload the page.', 'the-seo-framework-extension-manager' ),
					];
				} else {
					$timeout = isset( $_POST['remote_crawl_timeout'] ) ? \absint( $_POST['remote_crawl_timeout'] ) : 0; // Input var OK.

					$current_timeout = $this->get_remote_crawl_timeout();

					if ( $this->can_request_next_crawl() || ( $timeout + $this->get_request_next_crawl_buffer() ) < $current_timeout ) :
						// Crawl can be requested.
						$api = $this->api_request_crawl( true );

						switch ( $api['code'] ) :
							case 1010504:
								$type = 'yield_unchanged';
								break;

							case 1010502:
							case 1010503:
								$type = 'requires_fix';
								break;

							default:
								$type = $api['success'] ? 'success' : 'failure';
								break;
						endswitch;

						// Get new timeout.
						$current_timeout = $this->get_remote_crawl_timeout();

						$status = [
							'type'    => $type,
							'code'    => $api['code'],
							'notice'  => $api['notice'],
							'timeout' => $current_timeout,
						];
					else :
						// Crawl has already been requested recently.
						$seconds = $current_timeout + $this->get_request_next_crawl_buffer() - time();
						$status  = [
							'type'    => 'yield_unchanged',
							'notice'  => $this->get_try_again_notice( $seconds ),
							'timeout' => $current_timeout,
						];
					endif;
				}

				if ( WP_DEBUG ) {
					$response = [
						'status'   => $status,
						'timeout'  => [
							'old' => $timeout ?? null,
							'new' => $current_timeout ?? null,
						],
						'response' => isset( $api ) ? [ 'response' => $api ] : [],
					];
				} else {
					$response = [ 'status' => $status ];
				}

				\tsfem()->send_json( $response, null );
			endif;
		endif;

		exit;
	}

	/**
	 * Returns required fix fields through AJAX request.
	 *
	 * @since 1.0.0
	 * @since 1.2.6 The extension access level is now controlled via another constant.
	 * @access private
	 */
	public function _wp_ajax_get_requires_fix() {

		if ( \wp_doing_ajax() ) {
			if ( \TSF_Extension_Manager\can_do_extension_settings() ) {

				$send = [];

				if ( \check_ajax_referer( 'tsfem-e-monitor-ajax-nonce', 'nonce', false ) ) {
					// Initialize menu hooks.
					\tsf()->add_menu_link();
					$this->_add_menu_link();

					$send['html'] = $this->get_site_fix_fields();
				}

				\tsfem()->send_json( $send, null );
			}
		}

		exit;
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
		$this->ui_hook = $this->monitor_menu_page_hook;

		\add_action( 'tsfem_before_enqueue_scripts', [ $this, '_register_monitor_scripts' ] );

		$this->init_ui();
	}

	/**
	 * Registers default TSFEM Monitor admin scripts.
	 * Also registers TSF scripts, for TT (tooltip) support.
	 *
	 * @since 1.1.3
	 * @access private
	 * @internal
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	public function _register_monitor_scripts( $scripts ) {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) ) return;

		$scripts::register( [
			[
				'id'       => 'tsfem-monitor',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt', 'tsfem-ui' ],
				'autoload' => true,
				'hasrtl'   => true,
				'name'     => 'tsfem-monitor',
				'base'     => TSFEM_E_MONITOR_DIR_URL . 'lib/css/',
				'ver'      => TSFEM_E_MONITOR_VERSION,
				'inline'   => null,
			],
			[
				'id'       => 'tsfem-monitor',
				'type'     => 'js',
				'deps'     => [ 'tsf-tt', 'tsfem-ui' ],
				'autoload' => true,
				'name'     => 'tsfem-monitor',
				'base'     => TSFEM_E_MONITOR_DIR_URL . 'lib/js/',
				'ver'      => TSFEM_E_MONITOR_VERSION,
				'l10n'     => [
					'name' => 'tsfem_e_monitorL10n',
					'data' => [
						// This won't ever run when the user can't. But, sanity.
						'nonce'                => \TSF_Extension_Manager\can_do_extension_settings() ? \wp_create_nonce( 'tsfem-e-monitor-ajax-nonce' ) : '',
						'remote_data_timeout'  => $this->get_remote_data_timeout(),
						'remote_crawl_timeout' => $this->get_remote_crawl_timeout(),
					],
				],
			],
		] );
	}

	/**
	 * Determines whether we're on the monitor overview page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_monitor_page() {
		static $cache;
		// Don't load from $_GET request.
		return $cache ?? ( $cache = \tsf()->is_menu_page( $this->monitor_menu_page_hook ) );
	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_monitor_page() {
		\add_action( 'tsfem_header', [ $this, '_output_monitor_header' ] );
		\add_action( 'tsfem_content', [ $this, '_output_monitor_content' ] );
		\add_action( 'tsfem_footer', [ $this, '_output_monitor_footer' ] );

		if ( $this->is_api_connected() ) {
			$this->prepare_data();
			$this->wrap_type = 'row';
			$this->ui_wrap( 'panes' );
		} else {
			$this->ui_wrap( 'connect' );
		}
	}

	/**
	 * Outputs monitor header.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	public function _output_monitor_header() {
		$this->get_view(
			'layout/general/top',
			[
				'options' => $this->is_api_connected(),
			]
		);
	}

	/**
	 * Outputs monitor content.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	public function _output_monitor_content() {
		if ( $this->is_api_connected() ) {
			$this->get_view( 'layout/pages/monitor' );
		} else {
			$this->get_view( 'layout/pages/connect' );
		}
	}

	/**
	 * Outputs monitor footer.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	public function _output_monitor_footer() {
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
	 * Creates issues overview for the issues pane.
	 *
	 * @since 1.0.0
	 * @since 1.2.6 Now outputs notice when no issues can be processed.
	 *
	 * @return string The parsed issues overview HTML data.
	 */
	protected function get_issues_overview() {

		$output = '';
		$issues = $this->get_data( 'issues', [] );

		if ( ! empty( $issues ) ) {
			$output = Output::get_instance()->_get_data( $issues, 'issues' );
		}

		if ( ! $output ) {
			$output = sprintf(
				'<div class=tsfem-e-monitor-issues-wrap-line><h4 class=tsfem-status-title>%s</h4></div>',
				$this->get_string_no_data_found()
			);
		}

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-monitor-issues-wrap">%s</div>', $output );
	}

	/**
	 * Creates issues overview data for the issues pane.
	 *
	 * @since 1.0.0
	 *
	 * @return array : {
	 *     'found' => boolean Whether data is found,
	 *     'data' => array|string The parsed issues overview HTML data.
	 * }
	 */
	protected function ajax_get_issues_data() {

		$issues = $this->get_data( 'issues', [] );
		$found  = true;

		if ( ! empty( $issues ) ) {
			$data = Output::get_instance()->_ajax_get_pane_data( $issues, 'issues' );
		}

		if ( empty( $data['info'] ) ) {
			$found = false;
			$data  = sprintf(
				'<div class=tsfem-e-monitor-issues-wrap-line><h4 class=tsfem-status-title>%s</h4></div>',
				$this->get_string_no_data_found()
			);
		}

		return [
			'found' => $found,
			'data'  => $data,
		];
	}

	/**
	 * Creates Control Panel overview for the cp pane.
	 *
	 * @since 1.0.0
	 *
	 * @return string The parsed Control Panel overview HTML data.
	 */
	protected function get_cp_overview() {
		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-monitor-cp-wrap">%s</div>', $this->get_cp_output() );
	}

	/**
	 * Renders and returns Control Panel pane output content.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Control Panel pane output.
	 */
	protected function get_cp_output() {
		return sprintf(
			'<div class="tsfem-e-monitor-cp tsfem-flex">%s</div>',
			$this->get_account_information()
				. $this->get_site_actions_view()
				. $this->get_site_settings_view()
				. $this->get_disconnect_site_view()
		);
	}

	/**
	 * Wraps Monitor site action buttons.
	 *
	 * @since 1.1.0
	 *
	 * @return string The Monitor site action buttons.
	 */
	protected function get_site_actions_view() {

		$title   = sprintf( '<h4 class=tsfem-cp-title>%s</h4>', \esc_html__( 'Actions', 'the-seo-framework-extension-manager' ) );
		$content = '';

		$buttons = [
			$this->get_fetch_button(),
			$this->get_crawl_button(),
		];
		foreach ( $buttons as $button ) {
			$content .= sprintf( '<div class=tsfem-cp-buttons>%s</div>', $button );
		}

		return sprintf( '<div class="tsfem-e-monitor-cp-actions tsfem-pane-section">%s%s</div>', $title, $content );
	}

	/**
	 * Wraps Monitor site settings view.
	 *
	 * @since 1.1.0
	 *
	 * @return string The Monitor site settings view.
	 */
	protected function get_site_settings_view() {

		$title = sprintf( '<h4 class=tsfem-cp-title>%s</h4>', \esc_html__( 'Settings', 'the-seo-framework-extension-manager' ) );

		$content = '';
		$form_id = 'tsfem-e-monitor-update-settings';

		$content .= sprintf(
			'<p><small>%s</small></p>',
			\esc_html__( 'These settings are in development. Enable these to participate in the beta tests.', 'the-seo-framework-extension-manager' )
		);

		$_disabled_i18n = \__( 'Disabled', 'the-seo-framework-extension-manager' );
		$time_settings  = [
			'uptime_setting'      => [
				'title'   => \__( 'Uptime monitoring:', 'the-seo-framework-extension-manager' ),
				'help'    => \__( 'Set how often you want Monitor to test your website for availability.', 'the-seo-framework-extension-manager' ),
				'option'  => 'uptime_setting',
				'value'   => $this->get_option( 'uptime_setting', 0 ),
				'options' => [
					'values'   => [ 0, 5, 10, 30 ],
					'in'       => 'minutes',
					'scale'    => 0,
					'if-empty' => $_disabled_i18n,
				],
			],
			'performance_setting' => [
				'title'   => \__( 'Performance monitoring:', 'the-seo-framework-extension-manager' ),
				'help'    => \__( 'Set how often you want Monitor to test your website for performance.', 'the-seo-framework-extension-manager' ),
				'option'  => 'performance_setting',
				'value'   => $this->get_option( 'performance_setting', 0 ),
				'options' => [
					'values'   => [ 0, 60, 180, 720, 1440 ],
					'in'       => 'minutes',
					'scale'    => 1,
					'if-empty' => $_disabled_i18n,
				],
			],
		];

		$options = [];
		foreach ( $time_settings as $id => $args ) :
			$_options = [];
			foreach ( $args['options']['values'] as $_value ) {
				$_options[] = vsprintf(
					'<option value="%s" %s>%s</option>',
					[
						$_value,
						$args['value'] === $_value ? 'selected' : '',
						\esc_html( $_value
							? static::scale_time( $_value, $args['options']['in'], $args['options']['scale'], false )
							: $args['options']['if-empty']
						),
					]
				);
			}

			$_field_id = ! empty( $args['id'] ) ? $args['id'] : $this->_get_field_id( $args['option'] );

			$options[ $id ] = [
				'edit' => vsprintf(
					'<select form=%s id=%s name=%s class=hide-if-tsf-js>%s</select>',
					[
						$form_id,
						\esc_attr( $_field_id ),
						$this->_get_field_name( $args['option'] ),
						implode( '', $_options ),
					]
				),
				'js'   => vsprintf(
					'<span class="hide-if-no-tsf-js tsfem-e-monitor-edit tsfem-dashicon tsfem-edit" data-for=%s tabindex=0>%s</span>',
					[
						\esc_attr( $_field_id ),
						\esc_html( $args['value']
							? static::scale_time( $args['value'], $args['options']['in'], $args['options']['scale'], false )
							: $args['options']['if-empty']
						),
					]
				),
			];
		endforeach;

		$_rows = '';
		foreach ( $options as $id => $_fields ) :
			$_rows .= \TSF_Extension_Manager\Layout::wrap_row_content(
				HTML::wrap_inline_tooltip( HTML::make_inline_tooltip(
					\esc_html( $time_settings[ $id ]['title'] ),
					\esc_attr( $time_settings[ $id ]['help'] )
				) ),
				vsprintf(
					'<div class=tsfem-e-monitor-settings-holder data-option-id=%1$s id=%1$s>%2$s</div>',
					[
						\esc_attr( $id ),
						$_fields['edit'] . $_fields['js'],
					]
				),
				false
			);
		endforeach;

		$content .= sprintf( '<div class="tsfem-flex-account-setting-rows tsfem-flex tsfem-flex-nogrowshrink">%s</div>', $_rows );

		$nonce_action = $this->_get_nonce_action_field( 'update' );
		$nonce        = $this->_get_nonce_field( 'update' );

		$submit = $this->_get_submit_button(
			\__( 'Update Settings', 'the-seo-framework-extension-manager' ),
			'',
			'tsfem-button-primary tsfem-button-cloud'
		);

		$content .= sprintf(
			'<form action=%s method=post id=%s class="%s" autocomplete=off data-form-type=other>%s</form>',
			\menu_page_url( $this->monitor_page_slug, false ),
			$form_id,
			'hide-if-tsf-js',
			$nonce_action . $nonce . $submit
		);

		return sprintf( '<div class="tsfem-e-monitor-cp-settings tsfem-pane-section">%s%s</div>', $title, $content );
	}

	/**
	 * Renders and returns fetch button.
	 *
	 * @since 1.0.0
	 * @uses trait \TSF_Extension_Manager\Extension_Forms
	 *
	 * @return string The fetch button.
	 */
	protected function get_fetch_button() {

		$class          = 'tsfem-button-primary tsfem-button-primary-bright tsfem-button-cloud';
		$name           = \__( 'Fetch Data', 'the-seo-framework-extension-manager' );
		$title          = \__( 'Request Monitor to send you the latest data', 'the-seo-framework-extension-manager' );
		$question_title = \__( 'Get the latest data of your website from Monitor.', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'fetch' );
		$nonce        = $this->_get_nonce_field( 'fetch' );
		$submit       = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'         => 'tsfem-e-monitor-fetch-form',
			'input'      => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'       => true,
			'ajax-id'    => 'tsfem-e-monitor-fetch-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		];

		return $this->_get_action_button(
			\menu_page_url( $this->monitor_page_slug, false ),
			$args
		) . \TSF_Extension_Manager\HTML::make_inline_question_tooltip( $question_title );
	}
	/**
	 * Renders and returns crawl button.
	 *
	 * @since 1.0.0
	 * @uses trait \TSF_Extension_Manager\Extension_Forms
	 *
	 * @return string The crawl button.
	 */
	protected function get_crawl_button() {

		$class          = 'tsfem-button-primary tsfem-button-cloud';
		$name           = \__( 'Request Crawl', 'the-seo-framework-extension-manager' );
		$title          = \__( 'Request Monitor to re-crawl this website', 'the-seo-framework-extension-manager' );
		$question_title = \__( 'If your website has recently been updated, ask Monitor to re-crawl your site. This can take up to three minutes.', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'crawl' );
		$nonce        = $this->_get_nonce_field( 'crawl' );
		$submit       = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'         => 'tsfem-e-monitor-crawl-form',
			'input'      => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'       => true,
			'ajax-id'    => 'tsfem-e-monitor-crawl-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		];

		return $this->_get_action_button(
			\menu_page_url( $this->monitor_page_slug, false ),
			$args
		) . \TSF_Extension_Manager\HTML::make_inline_question_tooltip( $question_title );
	}

	/**
	 * Wraps and returns the Monitor account information.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Monitor account information wrap.
	 */
	protected function get_account_information() {
		return $this->get_account_data_fields() . $this->get_site_fix_fields();
	}

	/**
	 * Returns account data fields.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Now outputs last crawled field.
	 * @uses TSF_Extension_Manager\Layout::wrap_row_content()
	 *
	 * @return string The account data fields.
	 */
	protected function get_account_data_fields() {

		$title   = sprintf( '<h4 class=tsfem-info-title>%s</h4>', \esc_html__( 'Overview', 'the-seo-framework-extension-manager' ) );
		$content = '';

		$domain  = str_ireplace( [ 'https://', 'http://' ], '', \esc_url( \get_home_url(), [ 'https', 'http' ] ) );
		$_domain = $this->get_expected_domain();
		$class   = $_domain === $domain ? 'tsfem-success' : 'tsfem-error';
		$domain  = sprintf( '<span class="tsfem-dashicon %s">%s</span>', \esc_attr( $class ), \esc_html( $_domain ) );

		$content .= \TSF_Extension_Manager\Layout::wrap_row_content(
			\esc_html__( 'Connected site:', 'the-seo-framework-extension-manager' ),
			$domain,
			false
		);
		$content .= \TSF_Extension_Manager\Layout::wrap_row_content(
			\esc_html__( 'Last crawled:', 'the-seo-framework-extension-manager' ),
			HTML::wrap_inline_tooltip( $this->get_last_crawled_field() ),
			false
		);

		return sprintf(
			'<div class="tsfem-account-info tsfem-pane-section">%s%s</div>',
			$title,
			sprintf( '<div class="tsfem-flex-account-info-rows tsfem-flex tsfem-flex-nogrowshrink">%s</div>', $content )
		);
	}

	/**
	 * Wraps and returns the Monitor last crawled field.
	 *
	 * @since 1.1.0
	 *
	 * @return string The Monitor last crawled field.
	 */
	protected function get_last_crawled_field() {

		$last_crawl      = $this->get_last_issues_crawl();
		$last_crawl_i18n = $last_crawl ? static::get_time_ago_i18n( $last_crawl ) : \esc_html__( 'Never', 'the-seo-framework-extension-manager' );

		$class = $last_crawl ? 'tsfem-success' : 'tsfem-error';
		$title = $last_crawl
			? static::get_rectified_date_i18n( 'F j, Y, g:i A (\G\M\TP)', $last_crawl )
			: \__( 'No completed crawl has been recorded yet.', 'the-seo-framework-extension-manager' );

		return sprintf(
			'<time class="tsfem-dashicon tsf-tooltip-item %s" id=tsfem-e-monitor-last-crawled datetime=%s title="%s">%s</time>',
			\esc_attr( $class ),
			\esc_attr( gmdate( 'c', $last_crawl ) ),
			\esc_attr( $title ),
			\esc_html( $last_crawl_i18n )
		);
	}

	/**
	 * Wraps and returns the Monitor site fix fields.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Monitor site fix fields.
	 */
	protected function get_site_fix_fields() {

		$requires_fix    = $this->get_option( 'site_requires_fix' );
		$marked_inactive = $this->get_option( 'site_marked_inactive' );

		if ( $requires_fix || $marked_inactive ) {
			$title = \esc_html__( 'Reconnect site', 'the-seo-framework-extension-manager' );
			if ( $marked_inactive ) {
				// Inactive is marked more severely, and most likely $requires_fix would also be true.
				$description = \esc_html__( 'Your website has been marked inactive.', 'the-seo-framework-extension-manager' );
			} else {
				$description = \esc_html__( 'The instance ID of your site does not match the remote server.', 'the-seo-framework-extension-manager' );
			}

			$title = sprintf( '<h4 class=tsfem-info-title>%s</h4>', $title );

			$output  = '';
			$output .= sprintf( '<p class=tsfem-description>%s</p>', $description );
			$output .= $this->get_fix_button();

			return sprintf( '<div class="tsfem-account-fix tsfem-pane-section">%s%s</div>', $title, $output );
		}

		return '';
	}

	/**
	 * Renders and returns Monitor fix button.
	 *
	 * @since 1.0.0
	 * @uses trait \TSF_Extension_Manager\Extension_Forms
	 *
	 * @return string The fix button.
	 */
	protected function get_fix_button() {

		$class = 'tsfem-button tsfem-button-red tsfem-button-cloud';
		$name  = \__( 'Request Reactivation', 'the-seo-framework-extension-manager' );
		$title = \__( 'Request Monitor to reconnect your website', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'fix' );
		$nonce        = $this->_get_nonce_field( 'fix' );

		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'    => 'tsfem-e-monitor-fix-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => false,
		];

		return $this->_get_action_button(
			\menu_page_url( $this->monitor_page_slug, false ),
			$args
		);
	}

	/**
	 * Renders and returns Monitor disconnect button.
	 *
	 * @since 1.1.0
	 *
	 * @return string The disconnect button.
	 */
	protected function get_disconnect_site_view() {

		$nonce        = $this->_get_nonce_field( 'disconnect' );
		$nonce_action = $this->_get_nonce_action_field( 'disconnect' );

		$s_field_id = 'disconnect-switcher';

		$i18n = [
			'disconnect' => \__( 'Disconnect', 'the-seo-framework-extension-manager' ),
			'ays'        => \__( 'Are you sure?', 'the-seo-framework-extension-manager' ),
			'da_ays'     => \__( 'Disconnect site?', 'the-seo-framework-extension-manager' ),
		];

		$switcher = '<div class=tsfem-switch-button-container-wrap><div class=tsfem-switch-button-container>'
						. sprintf(
							'<input type=checkbox id="%s-action" value=1 />',
							$s_field_id
						)
						. sprintf(
							'<label for="%s-action" title="%s" class="tsfem-button tsfem-button-flag">%s</label>',
							$s_field_id,
							\esc_attr( $i18n['da_ays'] ),
							\esc_html( $i18n['disconnect'] )
						)
						. vsprintf(
							'<button type=submit for="%s-validator" title="%s" class="%s">%s</button>',
							[
								$s_field_id,
								\esc_attr( $i18n['ays'] ),
								'tsfem-switcher-button tsfem-button tsfem-button-red tsfem-button-warning',
								\esc_html( $i18n['disconnect'] ),
							]
						)
					. '</div></div>';

		$button = sprintf(
			'<form name=deactivate action="%s" method=post id=tsfem-e-monitor-disconnect-form autocomplete=off data-form-type=other>%s</form>',
			\menu_page_url( $this->monitor_page_slug, false ),
			$nonce_action . $nonce . $switcher
		);

		$title = sprintf( '<h4 class=tsfem-info-title>%s</h4>', \esc_html__( 'Disconnect site', 'the-seo-framework-extension-manager' ) );

		return sprintf( '<div class="tsfem-account-disconnect tsfem-pane-section">%s%s</div>', $title, $button );
	}

	/**
	 * Returns no data found string.
	 *
	 * @since 1.0.0
	 * @since 1.2.6 Now recommends fetching new data.
	 *
	 * @return string Translatable no data found string.
	 */
	protected function get_string_no_data_found() {
		return \esc_html__( 'No processable data was found. Try fetching new data.', 'the-seo-framework-extension-manager' );
	}

	/**
	 * Returns coming soon string.
	 *
	 * @since 1.0.0
	 * @todo Replace this with actual data.
	 *
	 * @return string Translatable coming soon string.
	 */
	protected function get_string_coming_soon() {
		return \esc_html__( 'Coming soon!', 'the-seo-framework-extension-manager' );
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view   The file name.
	 * @param array  $__args The arguments to be supplied within the file name.
	 *                       Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, array $__args = [] ) {

		foreach ( $__args as $__k => $__v ) $$__k = $__v;
		unset( $__k, $__v, $__args );

		$file = TSFEM_E_MONITOR_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . "$view.php";

		include $file;
	}
}
