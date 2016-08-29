<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
 * Class TSF_Extension_Manager\AdminPages
 *
 * Holds plugin admin page functions.
 *
 * @since 1.0.0
 */
class AdminPages extends AccountActivation {
	use Enclose, Construct_Sub;

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page hook.
	 */
	public $seo_extensions_menu_page_hook;

	/**
	 * The plugin page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	public $seo_extensions_page_slug;

	/**
	 * The plugin settings field.
	 *
	 * @since 1.0.0
	 *
	 * @var string TSF Extension Manager Settings Field.
	 */
	public $settings_field;

	/**
	 * CSS script name identifier to be used with enqueuing.
	 *
	 * @since 1.0.0
	 *
	 * @var string CSS name identifier.
	 */
	public $css_name;

	/**
	 * JavaScript name identifier to be used with enqueuing.
	 *
	 * @since 1.0.0
	 *
	 * @var string JavaScript name identifier.
	 */
	public $js_name;

	/**
	 * Constructor, initializes WordPress actions and set up variables.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->seo_extensions_page_slug = 'theseoframework-extensions';
		$this->settings_field = TSF_EXTENSION_MANAGER_SITE_OPTIONS;

		//* Initialize menu links
		add_action( 'admin_menu', array( $this, 'init_menu' ) );

		//* Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 0, 1 );

		//* Initialize TSF Extension Manager page actions.
		add_action( 'admin_init', array( $this, 'load_tsfem_admin_actions' ) );

	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework()->load_options variable. Applies filters 'the_seo_framework_load_options'
	 *
	 * @todo determine network activation @see core class.
	 */
	public function init_menu() {

		if ( ! $this->can_do_settings() )
			return;

		$network_mode = $this->is_plugin_in_network_mode();

		if ( $network_mode ) {
			//* TODO. var_dump()
			//	add_action( 'network_admin_menu', array( $this, 'add_network_menu_link' ), 11 );
		} else {
			if ( the_seo_framework()->load_options )
				add_action( 'admin_menu', array( $this, 'add_menu_link' ), 11 );
		}

	}

	/**
	 * Adds menu link for extension manager, when possible, underneath The
	 * SEO Framework SEO settings.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework()->page_id variable.
	 * @access private
	 */
	public function add_menu_link() {

		$menu = array(
			'parent_slug'	=> the_seo_framework_options_page_slug(),
			'page_title'	=> esc_html__( 'SEO Extensions', 'the-seo-framework-extension-manager' ),
			'menu_title'	=> esc_html__( 'Extensions', 'the-seo-framework-extension-manager' ),
			'capability'	=> 'install_plugins',
			'menu_slug'		=> $this->seo_extensions_page_slug,
			'callback'		=> array( $this, 'init_extension_manager_page' ),
		);

		$this->seo_extensions_menu_page_hook = add_submenu_page(
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
	 * @uses $this->seo_extensions_menu_page_hook variable.
	 * @access private
	 */
	public function load_tsfem_admin_actions() {

		add_action( 'load-' . $this->seo_extensions_menu_page_hook, array( $this, 'do_tsfem_admin_actions' ) );

	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 * Early enough for admin_notices and admin_head :).
	 *
	 * @since 1.0.0
	 * @uses $this->seo_extensions_menu_page_hook variable.
	 * @access private
	 */
	public function do_tsfem_admin_actions() {

		if ( false === $this->is_tsf_extension_manager_page() )
			return;

		static $run = false;

		if ( $run )
			return false;

		//* Remove WordPress footer strings.
		add_filter( 'admin_footer_text', '__return_empty_string' );
		add_filter( 'update_footer', '__return_empty_string' );

		//* Add something special for Vivaldi
		add_action( 'admin_head', array( $this, 'output_theme_color_meta' ), 0 );

		//* Add footer output.
		add_action( 'in_admin_footer', array( $this, 'init_extension_footer_wrap' ) );

		//* Add body class.
		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ), 999, 1 );

		//* Output notices.
		add_action( 'admin_notices', array( $this, 'do_error_notices' ) );

		return $run = true;
	}

	/**
	 * Enqueues styles and scripts in the admin area on the extension page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {

		if ( $this->seo_extensions_menu_page_hook === $hook ) {

			//* Set names.
			$this->css_name = 'tsf-extension-manager';
			$this->js_name = 'tsf-extension-manager';

			//* Enqueue styles
			add_action( 'admin_print_styles-' . $this->seo_extensions_menu_page_hook, array( $this, 'enqueue_admin_css' ), 11 );
			//* Enqueue scripts
			add_action( 'admin_print_scripts-' . $this->seo_extensions_menu_page_hook, array( $this, 'enqueue_admin_javascript' ), 11 );
			add_action( 'admin_footer', array( $this, 'localize_admin_javascript' ) );
		}

	}

	/**
	 * Enqueues required CSS for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook
	 */
	public function enqueue_admin_css( $hook ) {

		//* Register the script.
		$this->register_admin_css();

		wp_enqueue_style( $this->css_name );

	}

	/**
	 * Enqueues required JS for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook
	 */
	public function enqueue_admin_javascript( $hook ) {

		//* Register the script.
		$this->register_admin_javascript();

		wp_enqueue_script( $this->js_name );

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $registered : Prevents Re-registering of the style.
	 * @access private
	 */
	public function register_admin_css() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$rtl = is_rtl() ? '-rtl' : '';

		$suffix = the_seo_framework()->script_debug ? '' : '.min';

		wp_register_style(
			$this->css_name,
			TSF_EXTENSION_MANAGER_DIR_URL . "lib/css/tsf-extension-manager{$rtl}{$suffix}.css",
			array( 'dashicons' ),
			TSF_EXTENSION_MANAGER_VERSION,
			'all'
		);

		$registered = true;

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $registered : Prevents Re-registering of the script.
	 * @access private
	 */
	public function register_admin_javascript() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$suffix = the_seo_framework()->script_debug ? '' : '.min';

		wp_register_script(
			$this->js_name,
			TSF_EXTENSION_MANAGER_DIR_URL . "lib/js/tsf-extension-manager{$suffix}.js",
			array( 'jquery' ),
			TSF_EXTENSION_MANAGER_VERSION,
			true
		);

		$registered = true;

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $l7d : Prevents relocalizing of the scripts.
	 * @access private
	 * @return void early If run twice or more.
	 */
	public function localize_admin_javascript() {

		//* Localized.
		static $l7d = null;

		if ( isset( $l7d ) )
			return;

		$strings = array(
			'nonce' => wp_create_nonce( 'tsfem-ajax-nonce' ),
			'i18n' => array(
				'Activate' => esc_html__( 'Activate', 'the-seo-framework-extension-manager' ),
				'Deactivate' => esc_html__( 'Deactivate', 'the-seo-framework-extension-manager' ),
			),
		);

		wp_localize_script( $this->js_name, 'tsfemL10n', $strings );

		$l7d = true;

	}

	/**
	 * Adds an extra body class on the extensions manager page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classes The current body classes.
	 * @return string The expanded body classes.
	 */
	public function add_admin_body_class( $classes = '' ) {
		return $classes . ' tsfem ';
	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function init_extension_manager_page() {
		?>
		<div class="wrap tsfem tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">
			<?php
			if ( $this->is_plugin_activated() ) {
				$this->output_extension_overview_wrapper();
			} else {
				$this->output_plugin_connect_wrapper();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Initializes the admin footer output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function init_extension_footer_wrap() {
		?>
		<div class="tsfem-footer-wrap tsfem-flex tsfem-flex-nowrap tsfem-disable-cursor">
			<?php
			$this->do_page_footer_wrap();
			?>
		</div>
		<?php
	}

	/**
	 * Outputs theme color meta tag for Vivaldi and mobile browsers.
	 * Does not always work. So many browser bugs... It's just fancy.
	 *
	 * @since 1.0.0
	 */
	public function output_theme_color_meta() {
		$this->get_view( 'layout/pages/meta' );
	}

	/**
	 * Echos main page wrapper for extension activation.
	 *
	 * @since 1.0.0
	 */
	protected function output_extension_overview_wrapper() {

		$this->do_page_header_wrap( true );

		?>
		<div class="tsfem-panes-wrap tsfem-flex tsfem-flex-nowrap">
			<?php
			$this->do_extensions_overview();
			?>
		</div>
		<?php
	}

	/**
	 * Echos main page wrapper for account activation.
	 *
	 * @since 1.0.0
	 */
	protected function output_plugin_connect_wrapper() {

		$this->do_page_header_wrap( false );

		?>
		<div class="tsfem-connect-wrap">
			<?php
			$this->do_connect_overview();
			?>
		</div>
		<?php
	}

	/**
	 * Echos the page title wrap.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $options Whether to output the options.
	 */
	protected function do_page_header_wrap( $options = true ) {
		$this->get_view( 'layout/general/header', get_defined_vars() );
	}

	/**
	 * Echos the page title wrap.
	 *
	 * @since 1.0.0
	 */
	protected function do_page_footer_wrap() {
		$this->get_view( 'layout/general/footer' );
	}

	/**
	 * Echos the activation overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_connect_overview() {
		$this->get_view( 'layout/pages/activation' );
	}

	/**
	 * Echos the extension overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_extensions_overview() {
		$this->get_view( 'layout/pages/extensions' );
	}

	/**
	 * Echos a pane wrap.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The pane title.
	 * @param string $content The escaped pane content.
	 * @param array $args The output arguments : {
	 *		'full' bool : Whether to output a half or full pane.
	 *		'collapse' bool : Whether able to collapse the pane.
	 *		'move' bool : Whether to be able to move the pane.
	 *		'ajax' bool : Whether to use ajax.
	 *		'ajax_id' string : The AJAX div ID.
	 * }
	 * @param string $extra Extra header output placed between the title and ajax loader.
	 */
	protected function do_pane_wrap( $title = '', $content = '', $args = array(), $extra = '' ) {

		$defaults = array(
			'full' => true,
			'collapse' => true,
			'move' => false,
			'ajax' => false,
			'ajax_id' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		unset( $defaults );

		$this->get_view( 'layout/general/pane', get_defined_vars() );
	}

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Other page implementation classes may wish to construct and use a
	 * get_field_id() method, if the naming format needs to be different.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Field name base
	 * @return string Full field name
	 */
	public function get_field_name( $name ) {
		return sprintf( '%s[%s]', $this->settings_field, $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 1.0.0
	 * @uses $this->get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public function field_name( $name ) {
		echo esc_attr( $this->get_field_name( $name ) );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Field id base
	 * @return string Full field id
	 */
	public function get_field_id( $id ) {
		return sprintf( '%s[%s]', $this->settings_field, $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 1.0.0
	 * @uses $this->get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string $id Field id base
	 * @param boolean $echo echo or return
	 * @return string Full field id
	 */
	public function field_id( $id, $echo = true ) {

		if ( $echo ) {
			echo esc_attr( $this->get_field_id( $id ) );
		} else {
			return $this->get_field_id( $id );
		}
	}

	/**
	 * Outputs nonce action field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The escaped action key.
	 */
	public function nonce_action_field( $key ) {
		echo $this->get_nonce_action_field( $key );
	}

	/**
	 * Returns nonce action field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The action key.
	 */
	public function get_nonce_action_field( $key ) {
		return '<input type="hidden" name="' . esc_attr( $this->get_field_name( 'action' ) ) . '" value="' . esc_attr( $key ) . '">';
	}
}
