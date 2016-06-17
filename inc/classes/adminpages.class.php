<?php
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
 * Class TSF_Extension_Manager_AdminPages
 *
 * Holds plugin admin page functions.
 *
 * @since 1.0.0
 */
class TSF_Extension_Manager_AdminPages extends TSF_Extension_Manager_Activation {

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page hook.
	 */
	public $pagehook;

	/**
	 * The plugin page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	public $plugin_page_id;

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
	 * Constructor. Loads parent constructor and initializes actions.
	 */
	public function __construct() {
		parent::__construct();

		$this->plugin_page_id = 'the-seo-framework-extensions';

		//* Initialize menu links
		add_action( 'admin_menu', array( $this, 'init_menu' ) );

		//* Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 0, 1 );

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
			'parent_slug'	=> the_seo_framework()->page_id,
			'page_title'	=> __( 'SEO Extensions', 'the-seo-framework-extension-manager' ),
			'menu_title'	=> __( 'Extensions', 'the-seo-framework-extension-manager' ),
			'capability'	=> 'install_plugins',
			'menu_slug'		=> $this->plugin_page_id,
			'callback'		=> array( $this, 'init_extension_manager_page' ),
		);

		$this->pagehook = add_submenu_page(
			$menu['parent_slug'],
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);

	}

	/**
	 * Enqueues styles and scripts in the admin area on the extension page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {

		if ( $this->pagehook === $hook ) {

			//* Set names.
			$this->css_name = 'tsf-extension-manager';
			$this->js_name = 'tsf-extension-manager';

			wp_enqueue_style( 'google-tillitium-font', '//fonts.googleapis.com/css?family=Titillium+Web:600', false );

			//* Enqueue styles
			add_action( 'admin_print_styles-' . $this->pagehook, array( $this, 'enqueue_admin_css' ), 11 );
			//* Enqueue scripts
			add_action( 'admin_print_scripts-' . $this->pagehook, array( $this, 'enqueue_admin_javascript' ), 11 );
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

		wp_register_style( $this->css_name, TSF_EXTENSION_MANAGER_DIR_URL . "lib/css/tsf-extension-manager{$rtl}{$suffix}.css", array(), TSF_EXTENSION_MANAGER_VERSION, 'all' );

		$registered = true;

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $registered : Prevents Re-registering of the style.
	 * @access private
	 */
	public function register_admin_javascript() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$suffix = the_seo_framework()->script_debug ? '' : '.min';

		wp_register_script( $this->js_name, TSF_EXTENSION_MANAGER_DIR_URL . "lib/js/tsf-extension-manager{$suffix}.js", array( 'jquery' ), TSF_EXTENSION_MANAGER_VERSION, true );

		$registered = true;

	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 1.0.0
	 */
	public function init_extension_manager_page() {
		?>
		<div class="wrap tsf-extension-manager">
			<?php

			if ( $this->is_plugin_connected() )
				$this->output_extension_overview_wrapper();
			else
				$this->output_plugin_connect_wrapper();

			?>
		</div>
		<?php
	}

	/**
	 * Echos main page wrapper for extension activation.
	 *
	 * @since 1.0.0
	 */
	protected function output_extension_overview_wrapper() {

		$network = $this->is_plugin_in_network_mode();
		$type = $network ? __( 'network', 'the-seo-framework-extension-manager' ) : __( 'website', 'the-seo-framework-extension-manager' );

		?>
		<div class="top-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		</div>
		<div class="extensions-wrap">
			<?php
			$this->output_extensions_overview( $network );
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

		$network = $this->is_plugin_in_network_mode();
		$mode = $network ? '&mdash;' . esc_html__( 'Network Mode', 'the-seo-framework-extension-manager' ) : '';
		$type = $this->is_plugin_in_network_mode() ? esc_html__( 'network', 'the-seo-framework-extension-manager' ) : esc_html__( 'website', 'the-seo-framework-extension-manager' );

		?>
		<div class="top-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ) . $mode; ?></h1>
		</div>
		<div class="connect-wrap">
			<p><?php printf( esc_html__( 'Add more powerful SEO features to your %s. To get started, use one of the options below.', 'the-seo-framework-extension-manager' ), $type ); ?></p>

			<div class="connect-option connect-highlighted">
				<div class="connect-description">
					<h3><?php esc_html_e( 'Activate', 'the-seo-framework-extension-manager' ); ?></h3>
					<strong><?php esc_html_e( 'Log in or sign up now.', 'the-seo-framework-extension-manager' ); ?></strong>
					<p><?php esc_html_e( 'Connect your account. Fast and secure.', 'the-seo-framework-extension-manager' ); ?></p>
				</div>
				<div class="connect-action">
					<div class="connect-fields-row">
						<?php $this->get_view( 'activate', array( 'name' => 'activate', 'action' => 'https://premium.theseoframework.com/get/', 'text' => __( 'Get your API key', 'the-seo-framework-extension-manager' ), 'classes' => array( 'button', 'button-primary' ) ) ); ?>
						<?php $this->get_view( 'activate', array( 'name' => 'activate', 'action' => 'https://premium.theseoframework.com/get/', 'text' => __( 'Connect', 'the-seo-framework-extension-manager' ), 'classes' => array( 'button' ) ) ); ?>
					</div>
				</div>
			</div>

			<div class="connect-option">
				<div class="connect-description">
					<h3><?php esc_html_e( 'Use key', 'the-seo-framework-extension-manager' ); ?></h3>
					<strong><?php esc_html_e( 'Manually enter an API key', 'the-seo-framework-extension-manager' ); ?></strong>
					<p><?php esc_html_e( 'Already have your key? Enter it here.', 'the-seo-framework-extension-manager' ); ?></p>
				</div>
				<div class="connect-action">
					<?php $this->get_view( 'key', array( 'name' => 'validate-key', 'id' => 'key', 'nonce' => $this->pagehook, 'text' => __( 'Use this key', 'the-seo-framework-extension-manager' ) ) ); ?>
				</div>
			</div>

			<div class="connect-option connect-secondary">
				<div class="connect-description">
					<h3><?php esc_html_e( 'Go free', 'the-seo-framework-extension-manager' ); ?></h3>
					<strong><?php esc_html_e( 'Unlimited free access', 'the-seo-framework-extension-manager' ); ?></strong>
					<p><?php esc_html_e( 'Rather go for a test-drive? You can always upgrade later.', 'the-seo-framework-extension-manager' ); ?></p>
				</div>
				<div class="connect-action">
					<?php $this->get_view( 'free', array( 'name' => 'go-free', 'id' => 'go-free', 'nonce' => $this->pagehook, 'text' => __( 'Save a few bucks', 'the-seo-framework-extension-manager' ) ) ); ?>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 * 		Each array key is converted to a variable with its value attached.
	 *
	 * @credits Akismet For most code.
	 */
	protected function get_view( $view, array $args = array() ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$file = TSF_EXTENSION_MANAGER_DIR_PATH . 'views/'. $view . '.php';

		include( $file );
	}

}
