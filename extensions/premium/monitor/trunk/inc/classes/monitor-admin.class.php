<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Require user interface trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'ui' );

/**
 * Require extension options trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'extension-options' );

/**
 * Require extension forms trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'extension-forms' );

/**
 * Require error trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'error' );

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use TSF_Extension_Manager\Construct_Master_Once_Interface as Construct_Master_Once_Interface;
use TSF_Extension_Manager\UI as UI;
use TSF_Extension_Manager\Extension_Options as Extension_Options;
use TSF_Extension_Manager\Extension_Forms as Extension_Forms;
use TSF_Extension_Manager\Error as Error;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager_Extension\Monitor_Admin
 *
 * Holds extension admin page functions.
 *
 * @since 1.0.0
 * @access private
 * @errorval 101xxxx
 */
final class Monitor_Admin extends Monitor_Api {
	use Enclose_Stray_Private, Construct_Master_Once_Interface, UI, Extension_Options, Extension_Forms, Error;

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
	protected $monitor_menu_page_hook;

	/**
	 * The extension page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	protected $monitor_page_slug;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->nonce_name = 'tsfem_e_monitor_nonce_name';
		$this->request_name = array(
			//* Reference convenience.
			'default' => 'default',

			//* Connect site to API
			'connect' => 'connect',

			//* Disconnect site from API
			'disconnect' => 'disconnect',

			//* Statistics fetch.
			'update' => 'update',

			//* Request crawl.
			'crawl' => 'crawl',

			//* Fix instance.
			'fix' => 'fix',
		);
		$this->nonce_action = array(
			//* Reference convenience.
			'default' => 'tsfem_e_monitor_nonce_action',

			//* Connect site to API
			'connect' => 'tsfem_e_monitor_nonce_action_connect_site',

			//* Disconnect site from API
			'disconnect' => 'tsfem_e_monitor_nonce_action_disconnect_site',

			//* Statistics fetch.
			'update' => 'tsfem_e_monitor_nonce_action_monitor_update',

			//* Request crawl.
			'crawl' => 'tsfem_e_monitor_nonce_action_monitor_crawl',

			//* Fix instance.
			'fix' => 'tsfem_e_monitor_nonce_action_monitor_fix',
		);

		$this->monitor_page_slug = 'theseoframework-monitor';

		/**
		 * Set error notice option.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_monitor_error_notice_option';

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'monitor';

		//* Initialize menu links
		add_action( 'admin_menu', array( $this, '_init_menu' ) );

		//* Initialize Monitor page actions.
		add_action( 'admin_init', array( $this, '_load_monitor_admin_actions' ) );

		//* Update POST listener.
		add_action( 'admin_init', array( $this, '_handle_update_post' ) );
	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework()->load_options variable. Applies filters 'the_seo_framework_load_options'
	 * @uses tsf_extension_manager()->can_do_settings()
	 * @access private
	 */
	public function _init_menu() {

		if ( tsf_extension_manager()->can_do_settings() && the_seo_framework()->load_options )
			add_action( 'admin_menu', array( $this, '_add_menu_link' ), 11 );
	}

	/**
	 * Adds menu link for extension manager, when possible, underneath The
	 * SEO Framework SEO settings.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework_options_page_slug().
	 * @access private
	 */
	public function _add_menu_link() {

		$menu = array(
			'parent_slug' => the_seo_framework_options_page_slug(),
			'page_title'  => esc_html__( 'SEO Monitor', 'the-seo-framework-extension-manager' ),
			'menu_title'  => esc_html__( 'Monitor', 'the-seo-framework-extension-manager' ),
			'capability'  => 'install_plugins',
			'menu_slug'   => $this->monitor_page_slug,
			'callback'    => array( $this, '_init_monitor_page' ),
		);

		$this->monitor_menu_page_hook = add_submenu_page(
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
		add_action( 'load-' . $this->monitor_menu_page_hook, array( $this, '_do_monitor_admin_actions' ) );
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
	public function _do_monitor_admin_actions() {

		if ( false === $this->is_monitor_page() )
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
		add_action( 'admin_head', array( $this, '_output_theme_color_meta' ), 0 );

		//* Add footer output.
		add_action( 'in_admin_footer', array( $this, '_init_monitor_footer_wrap' ) );

		//* Update POST listener.
		add_action( 'admin_init', array( $this, '_handle_update_post' ) );
		// add_action( 'wp_ajax_tsfem_e_monitor_update', array( $this, 'handle_post_data' ) );

		return $run = true;
	}

	public function _handle_update_post() {

		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ]['nonce-action'] ) )
			return;

		$options = $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ];

		if ( false === $this->handle_update_nonce( $options['nonce-action'], false ) )
			return;

		switch ( $options['nonce-action'] ) :
			case $this->request_name['connect'] :
				$this->api_register_site();
				break;

			case $this->request_name['fix'] :
				$this->api_register_site( false );
				break;

			case $this->request_name['disconnect'] :
				$this->api_disconnect_site();
				break;

			case $this->request_name['crawl'] :
				$this->api_request_crawl();
				break;

			case $this->request_name['update'] :
				$this->api_request_data();
				break;

			default :
				$this->set_error_notice( array( 1010101 => '' ) );
				break;
		endswitch;

		$args = WP_DEBUG ? array( 'did-' . $options['nonce-action'] => 'true' ) : array();
		the_seo_framework()->admin_redirect( $this->monitor_page_slug, $args );
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

		if ( false === $this->is_monitor_page() && false === tsf_extension_manager()->can_do_settings() )
			return $validated[ $key ] = false;

		if ( $check_post ) {
			/**
			 * If this page doesn't parse the site options,
			 * there's no need to check them on each request.
			 */
			if ( empty( $_POST )
			  || ! isset( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] )
			  || ! is_array( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] )
			   )
				return $validated[ $key ] = false;
		}

		$result = isset( $_POST[ $this->nonce_name ] ) ? wp_verify_nonce( $_POST[ $this->nonce_name ], $this->nonce_action[ $key ] ) : false;

		if ( false === $result ) {
			//* Nonce failed. Set error notice and reload.
			$this->set_error_notice( array( 1019001 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		return $validated[ $key ] = (bool) $result;
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
		$this->ui_hook = $this->monitor_menu_page_hook;

		$this->additional_css = array(
			array(
				'name' => 'tsfem-monitor',
				'base' => TSFEM_E_MONITOR_DIR_URL,
				'ver' => TSFEM_E_MONITOR_VERSION,
			),
		);

		$this->additional_js = array(
			array(
				'name' => 'tsfem-monitor',
				'base' => TSFEM_E_MONITOR_DIR_URL,
				'ver' => TSFEM_E_MONITOR_VERSION,
			),
		);

		$this->additional_l10n = array(
			array(
				'dependency' => 'tsfem-monitor',
				'name' => 'tsfem_e_monitorL10n',
				'strings' => array(
					'nonce' => wp_create_nonce( 'tsfem-e-monitor-ajax-nonce' ),
				),
			),
		);

		$this->init_ui();
	}

	/**
	 * Determines whether we're on the monitor overview page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function is_monitor_page() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		//* Don't load from $_GET request.
		return $cache = the_seo_framework()->is_menu_page( $this->monitor_menu_page_hook );
	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_monitor_page() {
		?>
		<div class="wrap tsfem tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">
			<?php

			if ( $this->is_api_connected() ) {
				$this->output_monitor_overview_wrapper();
			} else {
				$this->output_monitor_connect_wrapper();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Echos main page wrapper for monitor.
	 *
	 * @since 1.0.0
	 */
	protected function output_monitor_overview_wrapper() {

		$this->do_page_top_wrap( true );

		?>
		<div class="tsfem-panes-wrap tsfem-flex tsfem-flex-nowrap">
			<?php
			$this->do_monitor_overview();
			?>
		</div>
		<?php
	}

	/**
	 * Echos main page connect wrapper for monitor.
	 *
	 * @since 1.0.0
	 */
	protected function output_monitor_connect_wrapper() {

		$this->do_page_top_wrap( false );

		?>
		<div class="tsfem-connect-wrap">
			<?php
			$this->do_connect_overview();
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
		$this->get_view( 'layout/general/top', get_defined_vars() );
	}

	/**
	 * Echos the monitor connection overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_connect_overview() {
		$this->get_view( 'layout/pages/connect' );
	}

	/**
	 * Echos the monitor overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_monitor_overview() {
		$this->get_view( 'layout/pages/monitor' );
	}

	/**
	 * Initializes the admin footer output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_monitor_footer_wrap() {
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
	 */
	public function _output_theme_color_meta() {
		$this->get_view( 'layout/pages/meta' );
	}

	/**
	 * Creates issues overview for the issues pane.
	 *
	 * @since 1.0.0
	 *
	 * @return string The parsed issues overview HTML data.
	 */
	protected function get_issues_overview() {

		$output = '';
		$issues = $this->get_data( 'issues', array() );

		if ( empty( $issues ) ) {
			$output .= sprintf( '<div class="tsfem-e-monitor-issues-wrap-line"><h4 class="tsfem-status-title">%s</h4></div>', $this->get_string_no_data_found() );
		} else {
			$instance = Monitor_Output::get_instance();
			$output = $instance->_get_data( $issues, 'issues' );
		}

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-monitor-issues-wrap tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Creates Control Panel overview for the cp pane.
	 *
	 * @since 1.0.0
	 *
	 * @return string The parsed Control Panel overview HTML data.
	 */
	protected function get_cp_overview() {

		$output = $this->get_cp_output();

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-monitor-cp-wrap tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Renders and returns Control Panel pane output content.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Control Panel pane output.
	 */
	protected function get_cp_output() {

		$left = $this->get_cp_left_output();
		$right = $this->get_cp_right_output();

		return sprintf( '<div class="tsfem-e-monitor-cp tsfem-pane-split tsfem-flex tsfem-flex-row">%s</div>', $left . $right );
	}

	/**
	 * Wraps the left side of the Control Panel pane.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Control Panel pane left side output.
	 */
	protected function get_cp_left_output() {

		$output = $this->get_site_actions();

		return sprintf( '<div class="tsfem-e-monitor-cp-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $output );
	}

	/**
	 * Wraps Monitor site action buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Monitor site action buttons.
	 */
	protected function get_site_actions() {

		$buttons = array();
		$description = array();

		$buttons[1] = $this->get_update_button();
		$description[1] = __( 'Get the latest data of your website from Monitor.', 'the-seo-framework-extension-manager' );

		$buttons[2] = $this->get_crawl_button();
		$description[2] = __( 'If your website has recently been updated, ask us to re-crawl your site. This can take up to three minutes.', 'the-seo-framework-extension-manager' );

		$title = sprintf( '<h4 class="tsfem-cp-title">%s</h4>', esc_html__( 'Actions', 'the-seo-framework-extension-manager' ) );

		$content = '';
		foreach ( $buttons as $key => $button ) {
			$extra = sprintf( '<span class="tsfem-description">%s</span>', esc_html( $description[ $key ] ) );
			$content .= sprintf( '<div class="tsfem-cp-buttons tsfem-flex tsfem-flex-nogrow tsfem-flex-nowrap">%s%s</div>', $button, $extra );
		}

		return sprintf( '<div class="tsfem-e-monitor-cp-actions">%s%s</div>', $title, $content );
	}

	/**
	 * Wraps and outputs the left side of the Control Panel pane.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Control Panel pane left side output.
	 */
	protected function get_cp_right_output() {

		$output = '';
		$output .= $this->get_account_information();
		$output .= $this->get_disconnect_button();

		return sprintf( '<div class="tsfem-e-monitor-cp-right-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $output );
	}

	/**
	 * Renders and returns update button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The update button.
	 */
	protected function get_update_button() {

		$class = 'tsfem-button-primary tsfem-button-green tsfem-button-cloud tsfem-button-ajax';
		$name = __( 'Update Data', 'the-seo-framework-extension-manager' );
		$title = __( 'Request Monitor to send you the latest data', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'update' );
		$nonce = $this->_get_nonce_field( 'update' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = array(
			'id'         => 'tsfem-e-monitor-update-form',
			'input'      => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'       => true,
			'ajax-id'    => 'tsfem-e-monitor-update-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		);

		return $this->_get_action_form( tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ), $args );
	}
	/**
	 * Renders and returns crawl button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The crawl button.
	 */
	protected function get_crawl_button() {

		$class = 'tsfem-button tsfem-button-cloud tsfem-button-ajax';
		$name = __( 'Request Crawl', 'the-seo-framework-extension-manager' );
		$title = __( 'Request Monitor to re-crawl this website', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'crawl' );
		$nonce = $this->_get_nonce_field( 'crawl' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = array(
			'id'         => 'tsfem-e-monitor-crawl-form',
			'input'      => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'       => true,
			'ajax-id'    => 'tsfem-e-monitor-crawl-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		);

		return $this->_get_action_form( tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ), $args );
	}

	/**
	 * Wraps and returns the Monitor account information.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Monitor account information wrap.
	 */
	protected function get_account_information() {

	}

	/**
	 * Renders and returns Monitor disconnect button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The disconnect button.
	 */
	protected function get_disconnect_button() {

		$nonce_action = $this->_get_nonce_action_field( 'disconnect' );
		$nonce = $this->_get_nonce_field( 'disconnect' );
		$field_id = 'disconnect-switcher';
		$disconnect_i18n = __( 'Disconnect', 'the-seo-framework-extension-manager' );
		$ays_i18n = __( 'Are you sure?', 'the-seo-framework-extension-manager' );
		$da_i18n = __( 'Disconnect site?', 'the-seo-framework-extension-manager' );

		$button = '<input type="submit" id="' . $field_id . '-validator">'
				. '<label for="' . $field_id . '-validator" title="' . esc_attr( $ays_i18n ) . '" class="tsfem-button-primary tsfem-switcher-button tsfem-button-warning">' . esc_html( $disconnect_i18n ) . '</label>';

		$switcher = '<div class="tsfem-switch-button-container-wrap"><div class="tsfem-switch-button-container">'
						. '<input type="checkbox" id="' . $field_id . '-action" value="1" />'
						. '<label for="' . $field_id . '-action" title="' . esc_attr( $da_i18n ) . '" class="tsfem-button tsfem-button-flag">' . esc_html( $disconnect_i18n ) . '</label>'
						. $button
					. '</div></div>';

		$button = sprintf(
			'<form name="deactivate" action="%s" method="post" id="tsfem-e-monitor-disconnect-form">%s</form>',
			esc_url( tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ) ),
			$nonce_action . $nonce . $switcher
		);

		$title = sprintf( '<h4 class="tsfem-info-title">%s</h4>', esc_html__( 'Disconnect site', 'the-seo-framework-extension-manager' ) );

		return sprintf( '<div class="tsfem-account-deactivate">%s%s</div>', $title, $button );
	}

	/**
	 * Creates statistics overview for the statistics pane.
	 *
	 * @since 1.0.0
	 * @todo Soon.
	 *
	 * @return string The HTMl parsed statistics data.
	 */
	protected function get_stats_overview() {
		return sprintf(
			'<div class="tsfem-pane-inner-wrap tsfem-e-monitor-stats-wrap tsfem-flex"><h4 class="tsfem-status-title">%s</h4><p class="tsfem-description">%s</p></div>',
			$this->get_string_coming_soon(), esc_html__( 'Statistics will show you website uptime, performance and visitor count.', 'the-seo-framework-extension-manager' )
		);

		$output = '';
		$stats = $this->get_data( 'stats', array() );

		if ( empty( $stats ) ) {
			$output .= $this->get_string_no_data_found();
		} else {
			$instance = Monitor_Output::get_instance();
			$output = $instance->_get_data( $stats, 'stats' );
		}

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-e-monitor-stats-wrap tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Returns no data found string.
	 *
	 * @since 1.0.0
	 *
	 * @return string Translatable no data found string.
	 */
	protected function get_string_no_data_found() {
		return esc_html__( 'No data has been found as of yet.', 'the-seo-framework-extension-manager' );
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
		return esc_html__( 'Coming soon!', 'the-seo-framework-extension-manager' );
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

		$file = TSFEM_E_MONITOR_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

		include( $file );
	}
}
