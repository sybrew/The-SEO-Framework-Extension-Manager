<?php
/**
 * @package TSF_Extension_Manager\Extension\Transporter\Admin
 */
namespace TSF_Extension_Manager\Extension\Transporter;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Transporter extension for The SEO Framework
 * Copyright (C) 2017-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/ui' );

/**
 * Require extension options trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/options' );

/**
 * Require extension forms trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/forms' );

/**
 * Require error trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/error' );

/**
 * Class TSF_Extension_Manager\Extension\Transporter\Admin
 *
 * Holds extension admin page methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 * @uses TSF_Extension_Manager\Traits
 */
final class Admin {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Master_Once_Final_Interface,
		\TSF_Extension_Manager\UI,
		\TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Extension_Forms,
		\TSF_Extension_Manager\Error;

	/**
	 * The POST nonce validation name, action and name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 * @var string The validation request name.
	 * @var string The validation nonce action.
	 */
	protected $nonce_name;
	protected $request_name = [];
	protected $nonce_action = [];

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page hook.
	 */
	protected $transporter_menu_page_hook;

	/**
	 * The extension page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	protected $transporter_page_slug;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->nonce_name = 'tsfem_e_transporter_nonce_name';
		$this->request_name = [
			//* Reference convenience.
			'default' => 'default',

			//* Export init settings data.
			'export' => 'export',

			//* Export init settings data.
			'import' => 'import',

			//* Upload settings data.
			'upload' => 'upload',

			//* Download settings data.
			'download' => 'download',

			//* Confirm settings data.
			'confirm_upload' => 'confirm_upload',
		];
		$this->nonce_action = [
			//* Reference convenience.
			'default' => 'tsfem_e_transporter_nonce_action',

			//* Export init settings data.
			'export' => 'tsfem_e_transporter_nonce_action_export',

			//* Export init settings data.
			'import' => 'tsfem_e_transporter_nonce_action_import',

			//* Upload settings data.
			'upload' => 'tsfem_e_transporter_nonce_action_upload_data',

			//* Download settings data.
			'download' => 'tsfem_e_transporter_nonce_action_download_data',

			//* Confirm settings data.
			'confirm_upload' => 'tsfem_e_transporter_nonce_action_confirm_data',
		];

		$this->transporter_page_slug = 'theseoframework-transporter';

		/**
		 * Set error notice option.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_transporter_error_notice_option';

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'transporter';

		//* Initialize menu links
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		//* Initialize Transporter page actions.
		\add_action( 'admin_init', [ $this, '_load_transporter_admin_actions' ] );

		//* Update POST listener.
		\add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		//* AJAX export request listener.
		\add_action( 'wp_ajax_tsfem_e_transporter_request_settings_export', [ $this, '_wp_ajax_request_settings_export' ] );

		//* AJAX download request listener.
		\add_action( 'wp_ajax_tsfem_e_transporter_request_settings_download', [ $this, '_wp_ajax_request_settings_download' ] );

	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @uses \the_seo_framework()->load_options variable. Applies filters 'the_seo_framework_load_options'
	 * @uses \tsf_extension_manager()->can_do_settings()
	 * @access private
	 */
	public function _init_menu() {

		if ( \tsf_extension_manager()->can_do_settings() && \the_seo_framework()->load_options )
			\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 9001 );
	}

	/**
	 * Adds menu link for transporter, when possible, at the bottom of The SEO
	 * Framework SEO settings.
	 *
	 * @since 1.0.0
	 * @uses \the_seo_framework()->seo_settings_page_slug.
	 * @access private
	 */
	public function _add_menu_link() {

		$menu = [
			'parent_slug' => \the_seo_framework()->seo_settings_page_slug,
			'page_title'  => \esc_html__( 'SEO Transporter', 'the-seo-framework-extension-manager' ),
			'menu_title'  => \esc_html__( 'Transporter', 'the-seo-framework-extension-manager' ),
			'capability'  => 'manage_options',
			'menu_slug'   => $this->transporter_page_slug,
			'callback'    => [ $this, '_init_transporter_page' ],
		];

		$this->transporter_menu_page_hook = \add_submenu_page(
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
	 * @uses $this->transporter_menu_page_hook variable.
	 * @access private
	 */
	public function _load_transporter_admin_actions() {
		\add_action( 'load-' . $this->transporter_menu_page_hook, [ $this, '_do_transporter_admin_actions' ] );
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
	public function _do_transporter_admin_actions() {

		if ( false === $this->is_transporter_page() )
			return false;

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) )
			return false;

		/**
		 * Initialize user interface.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_tsfem_ui();

		/**
		 * Initialize error interface.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->init_errors();

		//* Add something special for Vivaldi
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		//* Add footer output.
		\add_action( 'tsfem_footer', [ $this, '_init_transporter_footer_wrap' ] );

		//* Update POST listener.
		\add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		return true;
	}

	/**
	 * Handles Transporter POST requests.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void If nonce failed.
	 */
	public function _handle_update_post() {

		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ]['nonce-action'] ) )
			return;

		$options = $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ];

		if ( false === $this->handle_update_nonce( $options['nonce-action'], false ) )
			return;

		switch ( $options['nonce-action'] ) :
			case $this->request_name['download'] :
				$this->download_seo_settings_file();
				break;

			default :
				$this->set_error_notice( [ 1060101 => '' ] );
				break;
		endswitch;

		$args = WP_DEBUG ? [ 'did-' . $options['nonce-action'] => 'true' ] : [];
		\the_seo_framework()->admin_redirect( $this->transporter_page_slug, $args );
		exit;
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
	 * @staticvar bool $validated Determines whether the nonce has already been verified.
	 *
	 * @param string $key The nonce action used for caching.
	 * @param bool $check_post Whether to check for POST variables containing TSFEM settings.
	 * @return bool True if verified and matches. False if can't verify.
	 */
	final protected function handle_update_nonce( $key = 'default', $check_post = true ) {

		static $validated = [];

		if ( isset( $validated[ $key ] ) )
			return $validated[ $key ];

		if ( false === $this->is_transporter_page() && false === \tsf_extension_manager()->can_do_settings() )
			return $validated[ $key ] = false;

		if ( $check_post ) {
			/**
			 * If this page doesn't parse the site options,
			 * there's no need to check them on each request.
			 */
			if ( empty( $_POST )
			|| ( ! isset( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) ) // input var, CSRF ok.
			|| ( ! is_array( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) ) // input var, CSRF ok.
			) {
				return $validated[ $key ] = false;
			}
		}

		$result = isset( $_POST[ $this->nonce_name ] ) // input var ok.
				? \wp_verify_nonce( \wp_unslash( $_POST[ $this->nonce_name ] ), $this->nonce_action[ $key ] ) // input var, sanitization ok.
				: false;

		if ( false === $result ) {
			//* Nonce failed. Set error notice and reload.
			$this->set_error_notice( [ 1069001 => '' ] );
			\the_seo_framework()->admin_redirect( $this->transporter_page_slug );
			exit;
		}

		return $validated[ $key ] = (bool) $result;
	}

	/**
	 * Sets up and returns Transporter_Steps.
	 *
	 * @since 1.0.0
	 *
	 * @return object \TSF_Extension_Manager\Extension\Transporter\Steps
	 */
	protected function get_transporter_steps_instance() {

		$steps_instance = Steps::get_instance();
		$steps_instance->_set_instance_properties( [
			'nonce_name'   => $this->nonce_name,
			'request_name' => $this->request_name,
			'nonce_action' => $this->nonce_action,
			'transporter_page_slug' => $this->transporter_page_slug,
			'o_index'      => $this->o_index,
		] );

		return $steps_instance;
	}

	/**
	 * Fetches and returns export data in JSON encoded form.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _wp_ajax_request_settings_export() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( \tsf_extension_manager()->can_do_settings() ) :

				if ( \check_ajax_referer( 'tsfem-e-transporter-ajax-nonce', 'nonce', false ) ) {

					$export_data = static::get_the_seo_framework_options_export_data();

					if ( empty( $export_data ) ) {
						$type = 'failure';
						$notice = \esc_html__( 'No export data is found.', 'the-seo-framework-extension-manager' );
					} else {
						$type = 'success';
						$notice = '';
					}

					//* Initialize menu hooks.
					\tsf_extension_manager()->_set_ajax_menu_link( $this->transporter_page_slug, 'manage_options' );

					$steps_instance = $this->get_transporter_steps_instance();

					$html = $steps_instance->_get_step( 2, 'settings-export', true );
				}

				\tsf_extension_manager()->send_json( compact( 'html', 'type', 'notice' ), \tsf_extension_manager()->coalesce_var( $type, 'failure' ) );
			endif;
		endif;

		exit;
	}

	final public function _wp_ajax_request_settings_download() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( \tsf_extension_manager()->can_do_settings() ) :
				// TODO fix php 7.3 compact.....

				if ( \check_ajax_referer( 'tsfem-e-transporter-ajax-nonce', 'nonce', false ) ) {
					$results = $this->wp_ajax_test_seo_settings_file();

					if ( true === $results ) {
						//* Initialize menu hooks.
						\tsf_extension_manager()->_set_ajax_menu_link( $this->transporter_page_slug, 'manage_options' );

						$post = \tsf_extension_manager()->_get_ajax_post_object( [
							'options_key' => TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS,
							'options_index' => $this->o_index,
							'menu_slug' => $this->transporter_page_slug,
							'nonce_name' => $this->nonce_name,
							'request_name' => $this->request_name['download'],
							'nonce_action' => $this->nonce_action['download'],
						] );

						if ( $post ) {
							$results = $this->get_ajax_notice( true, 1060401 );
							$type = 'success';
						} else {
							$results = $this->get_ajax_notice( false, 1060402 );
							$type = 'failure';
						}
					}
				}

				\tsf_extension_manager()->send_json( compact( 'results', 'post' ), \tsf_extension_manager()->coalesce_var( $type, 'failure' ) );
			endif;
		endif;

		exit;
	}

	/**
	 * Creates a stream for the Settings file and closes PHP.
	 *
	 * This function is data sensitive. Always confirm user authority before
	 * calling this.
	 *
	 * @since 1.0.0
	 *
	 * @return array|bool|void The error code on AJAX. Bool false on form call.
	 *         Void on success.
	 */
	protected function download_seo_settings_file() {

		$results = $this->pre_seo_settings_file_stream( $content, $filesize, $filename );

		if ( true !== $results )
			return $results;

		$this->stream_content( $content, $filesize, $filename, 'json' );
	}

	/**
	 * Tests settings file for ajax.
	 *
	 * @return mixed The error message contents on failure. True on success.
	 */
	protected function wp_ajax_test_seo_settings_file() {
		return $this->pre_seo_settings_file_stream( $a, $b, $c, true );
	}

	/**
	 * Tests and renders SEO settings file stream.
	 * Passes stream contents and requirements back through reference.
	 *
	 * Also returns test results.
	 *
	 * @NOTE: Tries to clean up headers. Will fail if headers_sent().
	 * @since 1.0.0
	 *
	 * @param string $content The file's content. Passed by reference.
	 * @param int $filesize The filesize in bytes. Passed by reference.
	 * @param string $filename The file name. Passed by reference.
	 * @param bool $ajax. Whether the call is for AJAX.
	 * @return mixed : bool true on success, bool false on failure.
	 *               : On AJAX, it will return the error message on failure.
	 */
	protected function pre_seo_settings_file_stream( &$content = '', &$filesize = 0, &$filename = '', $ajax = false ) {

		$filename_raw = sprintf(
			'TSF-SEO-Settings-%s.json',
			str_replace(
				[ ' ', '_', "\r\n", "\r", "\n", '\\' ],
				'-',
				trim( \get_bloginfo( 'name', 'raw' ) )
			)
		);
		$filename = \sanitize_file_name( $filename_raw );

		/**
		 * If this is NULL, then it will return an empty file. This is fine.
		 * However, we want to inform the cause to the user.
		 */
		$content  = static::get_the_seo_framework_options_export_data( true );
		$filesize = \tsf_extension_manager()->get_filesize( $content );

		if ( 0 === $filesize ) {
			$ajax or $this->set_error_notice( [ 1060301 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1060301 ) : false;
		}

		//* Arbitrary header cleanup test.
		\tsf_extension_manager()->_clean_response_header();

		if ( headers_sent() ) {
			$ajax or $this->set_error_notice( [ 1060302 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1060302 ) : false;
		}

		return true;
	}

	/**
	 * Streams file's content based on input and closes PHP.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content The file's content. Required.
	 * @param int $filesize The filesize in bytes. Required.
	 * @param string $filename The file name. Required.
	 * @param string $type The expected file type. Leave empty for generic text/html.
	 */
	protected function stream_content( $content, $filesize, $filename, $type = '' ) {

		/**
		 * This will output an error in Chrome. It's a bug.
		 * https://bugs.chromium.org/p/chromium/issues/detail?id=9891
		 *
		 * The fix would be to remove the type. But that would enable gzip for
		 * an octet stream. We do not want that to prevent failure in transfer.
		 */
		\tsf_extension_manager()->set_status_header( 200, $type );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . $filesize );

		//* Should've already been escaped.
		print( $content );
		exit;
	}

	/**
	 * Returns TSF Site SEO Options export data.
	 *
	 * On TSF 2.9.2 and later it will also clear its options cache.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param bool $encode Whether to encode the data.
	 * @return array The SEO Framework options.
	 */
	public static function get_the_seo_framework_options_export_data( $encode = false ) {

		$options = \the_seo_framework()->get_all_options( null, true );

		return $encode ? \wp_json_encode( $options ) : $options;
	}

	/**
	 * Initializes user interface styles, scripts and footer.
	 *
	 * @since 1.0.0
	 */
	protected function init_tsfem_ui() {

		/**
		 * Set UI hook.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->ui_hook = $this->transporter_menu_page_hook;

		$this->additional_css[] = [
			'name' => 'tsfem-transporter',
			'base' => TSFEM_E_TRANSPORTER_DIR_URL,
			'ver' => TSFEM_E_TRANSPORTER_VERSION,
		];

		$this->additional_js[] = [
			'name' => 'tsfem-transporter',
			'base' => TSFEM_E_TRANSPORTER_DIR_URL,
			'ver' => TSFEM_E_TRANSPORTER_VERSION,
		];

		$this->additional_l10n[] = [
			'dependency' => 'tsfem-transporter',
			'name' => 'tsfem_e_transporterL10n',
			'strings' => [
				'nonce' => \wp_create_nonce( 'tsfem-e-transporter-ajax-nonce' ),
			],
		];

		$this->init_ui();
	}

	/**
	 * Determines whether we're on the transporter overview page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function is_transporter_page() {

		static $cache = null;

		//* Don't load from $_GET request.
		return isset( $cache ) ? $cache : $cache = \the_seo_framework()->is_menu_page( $this->transporter_menu_page_hook );
	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_transporter_page() {
		?>
		<div class="wrap tsfem tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">
			<?php
			$this->output_transporter_overview_wrapper();
			\do_action( 'tsfem_footer' );
			?>
		</div>
		<?php
	}

	/**
	 * Echos main page wrapper for transporter.
	 *
	 * @since 1.0.0
	 */
	protected function output_transporter_overview_wrapper() {

		$this->do_page_top_wrap();

		?>
		<div class="tsfem-panes-wrap tsfem-flex tsfem-flex-nowrap">
			<?php
			$this->do_transporter_overview();
			?>
		</div>
		<?php
	}

	/**
	 * Echos the page top wrap.
	 *
	 * @since 1.0.0
	 */
	protected function do_page_top_wrap( $options = false ) {
		$this->get_view( 'layout/general/top' );
	}

	/**
	 * Echos the transporter overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_transporter_overview() {
		$this->get_view( 'layout/pages/transporter' );
	}

	/**
	 * Returns the transport pane HTML.
	 *
	 * @since 1.0.0
	 */
	protected function get_transport_settings_overview() {
		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-transporter-transport-wrap">%s</div>', $this->get_transport_settings_output() );
	}

	/**
	 * Returns the transport SEO settings output pane contents.
	 *
	 * Used to initialize pane for non-JS, JS takes over in the browser.
	 *
	 * @since 1.0.0
	 *
	 * @return string The pane contents.
	 */
	protected function get_transport_settings_output() {

		$steps_instance = $this->get_transporter_steps_instance();

		$js_steps = $this->get_transport_settings_js_steps( $steps_instance );
		$nojs_steps = $this->get_transport_settings_nojs_steps( $steps_instance );

		return $js_steps . $nojs_steps;
	}

	protected function get_transport_settings_js_steps( Steps $steps_instance ) {

		$step_1 = $steps_instance->_get_step( 1, 'settings', false );

		// Step 2 and 3 are loaded through AJAX.
		$step_1 = sprintf( '<div class="tsfem-e-transporter-step-1 tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">%s</div>', $step_1 );
		$step_2 = sprintf( '<div class="tsfem-e-transporter-step-2 tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">%s</div>', '' );
		$step_3 = sprintf( '<div class="tsfem-e-transporter-step-3 tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">%s</div>', '' );

		$output = $step_1 . $step_2 . $step_3;

		//* It's a row because we need vertical scrolling. Steps are stacked caused by 100% width. #flexlife
		return sprintf( '<div class="tsfem-e-transporter-transport tsfem-flex tsfem-flex-row tsfem-flex-hide-if-no-js">%s</div>', $output );
	}

	protected function get_transport_settings_nojs_steps( Steps $steps_instance ) {

		$exporter = $steps_instance->_get_step( 2, 'settings-export', false );
		$importer = $steps_instance->_get_step( 2, 'settings-import', false );

		$exporter = sprintf( '<div class="tsfem-e-transporter-step tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">%s</div>', $exporter );
		$importer = sprintf( '<div class="tsfem-e-transporter-step tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">%s</div>', $importer );

		$output = $exporter . $importer;

		return sprintf( '<div class="tsfem-e-transporter-transport tsfem-flex tsfem-flex-row tsfem-flex-hide-if-js">%s</div>', $output );
	}

	/**
	 * Returns the validate pane HTML.
	 *
	 * @since 1.0.0
	 */
	protected function get_transport_meta_overview() {
		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-transporter-validate-wrap">%s</div>', $this->get_transport_meta_output() );
	}

	/**
	 * Returns the transport meta settings output pane contents.
	 *
	 * @since @TODO
	 *
	 * @return string The meta transporter pane contents.
	 */
	protected function get_transport_meta_output() {

		$output = 'To be continued...';

		return sprintf( '<div class="tsfem-e-transporter-validate tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Initializes the admin footer output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_transporter_footer_wrap() {
		$this->do_page_footer_wrap();
	}

	/**
	 * Echos the page footer wrap.
	 *
	 * @since 1.0.0
	 */
	protected function do_page_footer_wrap() {
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
		$this->get_view( 'layout/pages/meta' );
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 *        Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, array $args = [] ) {

		foreach ( $args as $key => $val ) {
			$$key = $val;
		}

		$file = TSFEM_E_TRANSPORTER_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

		include( $file );
	}
}
