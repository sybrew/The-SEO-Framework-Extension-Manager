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
 * Require user interface trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'ui' );

/**
 * Class TSF_Extension_Manager\AdminPages
 *
 * Holds plugin admin page functions.
 *
 * @since 1.0.0
 * @access private
 */
class AdminPages extends AccountActivation {
	use Enclose, Construct_Sub, UI;

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

		//* Initialize TSF Extension Manager page actions.
		add_action( 'admin_init', array( $this, 'load_tsfem_admin_actions' ) );

	}

	/**
	 * Initializes extension manager menu.
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
			'parent_slug' => the_seo_framework_options_page_slug(),
			'page_title'  => esc_html__( 'SEO Extensions', 'the-seo-framework-extension-manager' ),
			'menu_title'  => esc_html__( 'Extensions', 'the-seo-framework-extension-manager' ),
			'capability'  => 'install_plugins',
			'menu_slug'   => $this->seo_extensions_page_slug,
			'callback'    => array( $this, 'init_extension_manager_page' ),
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
	 * @staticvar bool $run
	 * @access private
	 *
	 * @return bool True on actions loaded, false on second load.
	 */
	public function do_tsfem_admin_actions() {

		if ( false === $this->is_tsf_extension_manager_page() )
			return;

		static $run = false;

		if ( $run )
			return false;

		//* Initialize user interface.
		$this->init_tsfem_ui();

		//* Add something special for Vivaldi
		add_action( 'admin_head', array( $this, 'output_theme_color_meta' ), 0 );

		//* Add footer output.
		add_action( 'in_admin_footer', array( $this, 'init_footer_wrap' ) );

		//* Output notices.
		add_action( 'admin_notices', array( $this, 'do_error_notices' ) );

		return $run = true;
	}

	/**
	 * Initializes user interface.
	 *
	 * @since 1.0.0
	 */
	protected function init_tsfem_ui() {

		$this->ui_hook = $this->seo_extensions_menu_page_hook;

		$this->init_ui();
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
	public function init_footer_wrap() {
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

		$this->do_page_top_wrap( true );

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
	 *
	 * @param bool $options Whether to output the options.
	 */
	protected function do_page_top_wrap( $options = true ) {
		$this->get_view( 'layout/general/header', get_defined_vars() );
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
	 * @access private
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
	public function do_pane_wrap( $title = '', $content = '', $args = array(), $extra = '' ) {

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
		return '<input type="hidden" name="' . esc_attr( $this->get_field_name( 'nonce-action' ) ) . '" value="' . esc_attr( $key ) . '">';
	}
}
