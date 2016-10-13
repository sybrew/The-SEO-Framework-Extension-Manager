<?php
/**
 * @package TSF_Extension_Manager\Traits
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
 * Holds User Interface traits.
 *
 * @since 1.0.0
 * @access private
 */
trait UI {

	/**
	 * The User Interface hook where all scripts should be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var string The UI loader hook.
	 */
	public $ui_hook = '';

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
	 * Initializes the UI traits.
	 *
	 * @since 1.0.0
	 */
	final protected function init_ui() {

		//* Remove WordPress footer strings.
		add_filter( 'admin_footer_text', '__return_empty_string' );
		add_filter( 'update_footer', '__return_empty_string' );

		//* Add body class.
		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ), 999, 1 );

		//* Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 0, 1 );

	}

	/**
	 * Enqueues styles and scripts in the admin area on the extension manager page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {

		if ( $this->ui_hook === $hook ) {

			//* Set names.
			$this->css_name = 'tsf-extension-manager';
			$this->js_name = 'tsf-extension-manager';

			//* Enqueue styles
			add_action( 'admin_print_styles-' . $this->ui_hook, array( $this, 'enqueue_admin_css' ), 11 );
			//* Enqueue scripts
			add_action( 'admin_print_scripts-' . $this->ui_hook, array( $this, 'enqueue_admin_javascript' ), 11 );
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
		return trim( $classes ) . ' tsfem ';
	}
}