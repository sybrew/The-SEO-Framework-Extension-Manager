<?php
/**
 * @package TSF_Extension_Manager\Extension\Transporter\Admin
 */
namespace TSF_Extension_Manager\Extension;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Transporter extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
\TSF_Extension_Manager\_load_trait( 'ui' );

/**
 * Require extension options trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension-options' );

/**
 * Require extension forms trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension-forms' );

/**
 * Require error trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'error' );

/**
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface as Construct_Master_Once_Final_Interface;
use \TSF_Extension_Manager\UI as UI;
use \TSF_Extension_Manager\Extension_Options as Extension_Options;
use \TSF_Extension_Manager\Extension_Forms as Extension_Forms;
use \TSF_Extension_Manager\Error as Error;

/**
 * Class TSF_Extension_Manager\Extension\Transporter_Admin
 *
 * Holds extension admin page functions.
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 */
final class Transporter_Admin {
	use Enclose_Core_Final, Construct_Master_Once_Final_Interface, UI, Extension_Options, Extension_Forms, Error;

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
	protected $request_name = array();
	protected $nonce_action = array();

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
		$this->request_name = array(
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
		);
		$this->nonce_action = array(
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
		);

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
		\add_action( 'admin_menu', array( $this, '_init_menu' ) );

		//* Initialize Transporter page actions.
		\add_action( 'admin_init', array( $this, '_load_transporter_admin_actions' ) );

		//* Update POST listener.
		\add_action( 'admin_init', array( $this, '_handle_update_post' ) );

		//* AJAX export request listener.
		\add_action( 'wp_ajax_tsfem_e_transporter_request_export', array( $this, '_wp_ajax_request_export' ) );

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
			\add_action( 'admin_menu', array( $this, '_add_menu_link' ), 9001 );
	}

	/**
	 * Adds menu link for transporter, when possible, at the bottom of The SEO
	 * Framework SEO settings.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework_options_page_slug().
	 * @access private
	 */
	public function _add_menu_link() {

		$menu = array(
			'parent_slug' => \the_seo_framework_options_page_slug(),
			'page_title'  => \esc_html__( 'SEO Transporter', 'the-seo-framework-extension-manager' ),
			'menu_title'  => \esc_html__( 'Transporter', 'the-seo-framework-extension-manager' ),
			'capability'  => 'manage_options',
			'menu_slug'   => $this->transporter_page_slug,
			'callback'    => array( $this, '_init_transporter_page' ),
		);

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
		\add_action( 'load-' . $this->transporter_menu_page_hook, array( $this, '_do_transporter_admin_actions' ) );
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 * Early enough for admin_notices and admin_head :).
	 *
	 * @since 1.0.0
	 * @staticvar bool $run
	 * @access private
	 *
	 * @return bool True on actions loaded, false on second load.
	 */
	public function _do_transporter_admin_actions() {

		if ( false === $this->is_transporter_page() )
			return;

		static $run = false;

		if ( $run )
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
		\add_action( 'admin_head', array( $this, '_output_theme_color_meta' ), 0 );

		//* Add footer output.
		\add_action( 'in_admin_footer', array( $this, '_init_transporter_footer_wrap' ) );

		//* Update POST listener.
		\add_action( 'admin_init', array( $this, '_handle_update_post' ) );

		return $run = true;
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
			case $this->request_name['export'] :
				// TODO
				// $this->api_register_site();
				break;

			default :
				$this->set_error_notice( array( 1060101 => '' ) );
				break;
		endswitch;

		$args = WP_DEBUG ? array( 'did-' . $options['nonce-action'] => 'true' ) : array();
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

		static $validated = array();

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
			|| ( ! isset( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) )
			|| ( ! is_array( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) )
			) {
				return $validated[ $key ] = false;
			}
		}

		$result = isset( $_POST[ $this->nonce_name ] ) ? \wp_verify_nonce( \wp_unslash( $_POST[ $this->nonce_name ] ), $this->nonce_action[ $key ] ) : false;

		if ( false === $result ) {
			//* Nonce failed. Set error notice and reload.
			$this->set_error_notice( array( 1019001 => '' ) );
			\the_seo_framework()->admin_redirect( $this->transporter_page_slug );
			exit;
		}

		return $validated[ $key ] = (bool) $result;
	}

	/**
	 * Fetches and returns export data in JSON encoded form.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _wp_ajax_request_export() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( \tsf_extension_manager()->can_do_settings() ) :

				if ( \check_ajax_referer( 'tsfem-e-transporter-ajax-nonce', 'nonce', false ) ) {

					$export_data = $this->get_the_seo_framework_options_export_data();

					if ( empty( $export_data ) ) {
						$type = 'failure';
						$notice = \esc_html__( 'No export data is found.', 'the-seo-framework-extension-manager' );
					} else {
						$type = 'success';
						$notice = '';
					}

					//* Initialize menu hooks.
					\the_seo_framework()->add_menu_link();
					$this->_add_menu_link();

					$textarea = sprintf( '<textarea rows="5" class="tsfem-e-transporter-transport-data-text" id="tsfem-e-transporter-transport-data-text" readonly="readonly">%s</textarea>', json_encode( $export_data ) );
					$clipboard_button = $this->get_seo_settings_clipboard_button_output( 'tsfem-e-transporter-transport-data-text' );
					$download_button = $this->get_seo_settings_download_button_output();
					$html = sprintf( '<div class="tsfem-e-transporter-transport-data tsfem-flex tsfem-flex-nogrowshrink">%s<div>', $download_button . $clipboard_button . $textarea );
				}

				$response = compact( 'html', 'type', 'notice' );

				\tsf_extension_manager()->_clean_ajax_reponse_header();

				echo json_encode( $response );
			endif;
		endif;

		exit;
	}

	/**
	 * Returns TSF Site SEO Options export data.
	 *
	 * On TSF 2.9.2 and later it will also clear its options cache.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $encode Whether to encode the data.
	 * @return array The SEO Framework options.
	 */
	protected function get_the_seo_framework_options_export_data( $encode = false ) {

		$options = \the_seo_framework()->get_all_options( null, true );

		return $encode ? json_encode( $options ) : $options;
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

		$this->additional_css = array(
			array(
				'name' => 'tsfem-transporter',
				'base' => TSFEM_E_TRANSPORTER_DIR_URL,
				'ver' => TSFEM_E_TRANSPORTER_VERSION,
			),
		);

		$this->additional_js = array(
			array(
				'name' => 'tsfem-transporter',
				'base' => TSFEM_E_TRANSPORTER_DIR_URL,
				'ver' => TSFEM_E_TRANSPORTER_VERSION,
			),
		);

		$this->additional_l10n = array(
			array(
				'dependency' => 'tsfem-transporter',
				'name' => 'tsfem_e_transporterL10n',
				'strings' => array(
					'nonce' => \wp_create_nonce( 'tsfem-e-transporter-ajax-nonce' ),
				),
			),
		);

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

		if ( isset( $cache ) )
			return $cache;

		//* Don't load from $_GET request.
		return $cache = \the_seo_framework()->is_menu_page( $this->transporter_menu_page_hook );
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
	protected function get_transport_overview() {
		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-transporter-transport-wrap tsfem-flex tsfem-flex-row">%s</div>', $this->get_transport_output() );
	}

	protected function get_transport_output() {

		$left = sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_export_option_output() );
		$right = sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_import_option_output() );

		$start = sprintf( '<div class="tsfem-e-transporter-start tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>', $left . $right );

		//* Steps, pre-rendered for non-ajax. @TODO prerender
		$steps = sprintf( '<div class="tsfem-e-transporter-steps tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">%s</div>', '' );

		$output = $start . $steps;

		return sprintf( '<div class="tsfem-e-transporter-transport tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	protected function get_export_option_output() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Export SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_export_button();

		return sprintf( '<div class="tsfem-e-transporter-export-option">%s</div>', $title . $button );
	}

	protected function get_export_button() {

		$class = 'tsfem-button-primary tsfem-button-blue tsfem-button-upload tsfem-button-ajax';
		$name = \__( 'Export SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Export SEO Settings to text or file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'export' );
		$nonce = $this->_get_nonce_field( 'export' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = array(
			'id'    => 'tsfem-e-transporter-export-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-export-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		);

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}

	protected function get_seo_settings_clipboard_button_output( $textarea_id = '' ) {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Copy SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_seo_settings_clipboard_button( $textarea_id );

		return sprintf( '<div class="tsfem-e-transporter-clipboard-option">%s</div>', $title . $button );
	}

	protected function get_seo_settings_clipboard_button( $textarea_id ) {

		$class = 'tsfem-button-primary tsfem-button-primary-bright tsfem-button-clipboard';
		$name = \__( 'Copy SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Copy the SEO Settings to clipboard', 'the-seo-framework-extension-manager' );

		$args = array(
			'url'   => '#',
			'class' => $class,
			'title' => $title,
			'content' => $name,
			'id'    => $textarea_id . '-clipboard-button',
			'input' => $filename,
			'data'  => array(
				'clipboardid' => $textarea_id,
			),
		);

		return \tsf_extension_manager()->get_link( $args );
	}

	protected function get_seo_settings_download_button_output() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Download SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_seo_settings_download_button();

		return sprintf( '<div class="tsfem-e-transporter-download-option">%s</div>', $title . $button );
	}

	protected function get_seo_settings_download_button() {

		$class = 'tsfem-button-primary tsfem-button-green tsfem-button-download';
		$name = \__( 'Download SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Download the SEO Settings file', 'the-seo-framework-extension-manager' );

		$url_args = array(
			'action' => $this->nonce_action['download'],
			$this->nonce_name => \wp_create_nonce( $this->request_name['download'] ),
		);
		$location = \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug, $url_args );

		$filename = sprintf( 'SEO-Settings-%s.json.txt', str_replace( array( ' ', '_', "\r\n", "\r", '\\', "\n" ), '-', trim( \get_bloginfo( 'name', 'raw' ) ) ) );

		$args = array(
			'url'   => $location,
			'class' => $class,
			'title' => $title,
			'content' => $name,
			'id'    => 'tsfem-e-transporter-download-form',
			'input' => $filename,
		);

		return $this->get_download_link( $args );
	}

	protected function get_download_link( array $args = array() ) {

		$defaults = array(
			'url'     => '',
			'target'  => '_self',
			'class'   => '',
			'title'   => '',
			'content' => '',
			'download' => true,
			'filename' => '',
		);

		return \tsf_extension_manager()->get_link( \wp_parse_args( $args, $defaults ) );
	}

	protected function get_import_option_output() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Import SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_import_button();

		return sprintf( '<div class="tsfem-e-transporter-import-option">%s</div>', $title . $button );
	}

	protected function get_import_button() {

		$class = 'tsfem-button-primary tsfem-button-blue tsfem-button-download tsfem-button-ajax';
		$name = \__( 'Import SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Import SEO Settings from text or file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'import' );
		$nonce = $this->_get_nonce_field( 'import' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = array(
			'id'    => 'tsfem-e-transporter-import-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-import-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		);

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}

	/**
	 * Returns the validate pane HTML.
	 *
	 * @since 1.0.0
	 */
	protected function get_validate_overview() {
		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-transporter-validate-wrap tsfem-flex tsfem-flex-row">%s</div>', $this->get_validate_output() );
	}

	protected function get_validate_output() {

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
		?>
		<div class="tsfem-footer-wrap tsfem-flex tsfem-flex-nowrap tsfem-disable-cursor">
			<?php
			$this->do_page_footer_wrap();
			?>
		</div>
		<?php
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
	protected function get_view( $view, array $args = array() ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$file = TSFEM_E_TRANSPORTER_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

		include( $file );
	}
}
