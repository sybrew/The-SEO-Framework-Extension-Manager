<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds User Interface functionality.
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
	private $ui_hook;

	/**
	 * CSS script name identifier to be used with enqueuing.
	 *
	 * @since 1.0.0
	 *
	 * @var string CSS name identifier.
	 */
	private $css_name;

	/**
	 * JavaScript name identifier to be used with enqueuing.
	 *
	 * @since 1.0.0
	 *
	 * @var string JavaScript name identifier.
	 */
	private $js_name;

	/**
	 * Additional CSS scripts to be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var array Additional CSS scripts containing name and location.
	 */
	private $additional_css = [];

	/**
	 * Additional JS scripts to be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var array Additional JS scripts containing name and location.
	 */
	private $additional_js = [];

	/**
	 * Additional l10n strings to be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var array Additional l10n strings containing name, dependency and strings.
	 */
	private $additional_l10n = [];

	/**
	 * Initializes the UI traits.
	 *
	 * @since 1.0.0
	 */
	final protected function init_ui() {

		$this->ui_hook or \the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to specify property <code>ui_hook</code>' );

		//* Remove WordPress footer strings.
		\add_action( 'admin_footer_text', '__return_empty_string' );
		\add_action( 'update_footer', '__return_empty_string' );

		//* Add body class.
		\add_action( 'admin_body_class', [ $this, 'add_admin_body_class' ], 999, 1 );

		//* Enqueue admin scripts.
		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 0, 1 );

	}

	/**
	 * Enqueues styles and scripts in the admin area on the extension manager page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook.
	 */
	final public function enqueue_admin_scripts( $hook ) {

		if ( $this->ui_hook === $hook ) {

			/**
			 * Set JS and CSS names for the base (tsfem) layout.
			 *
			 * Currently, it works best with Default and Midnight. And a little
			 * with Blue, Ectoplasm, Ocean.
			 * @TODO consider visually appealing versions for other Admin Color Schemes.
			 */
			$this->css_name = 'tsfem';
			$this->js_name = 'tsfem';

			//* Enqueue styles
			\add_action( 'admin_print_styles-' . $this->ui_hook, [ $this, 'enqueue_admin_css' ], 11 );
			//* Enqueue scripts
			\add_action( 'admin_print_scripts-' . $this->ui_hook, [ $this, 'enqueue_admin_javascript' ], 11 );
			\add_action( 'admin_footer', [ $this, '_localize_admin_javascript' ] );
		}
	}

	/**
	 * Enqueues required CSS for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook
	 */
	final public function enqueue_admin_css( $hook ) {

		//* Register the script.
		$this->register_admin_css();

		\wp_enqueue_style( $this->css_name );

		if ( ! empty( $this->additional_css ) ) {
			foreach ( $this->additional_css as $script )
				\wp_enqueue_style( $script['name'] );
		}
	}

	/**
	 * Enqueues required JS for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current page hook
	 */
	final public function enqueue_admin_javascript( $hook ) {

		//* Register the script.
		$this->register_admin_javascript();

		\wp_enqueue_script( $this->js_name );

		if ( ! empty( $this->additional_js ) ) {
			foreach ( $this->additional_js as $script )
				\wp_enqueue_script( $script['name'] );
		}
	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $registered : Prevents Re-registering of the style.
	 */
	protected function register_admin_css() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$rtl = \is_rtl() ? '-rtl' : '';

		$suffix = \the_seo_framework()->script_debug ? '' : '.min';

		\wp_register_style(
			$this->css_name,
			TSF_EXTENSION_MANAGER_DIR_URL . "lib/css/{$this->css_name}{$rtl}{$suffix}.css",
			[ 'dashicons' ],
			TSF_EXTENSION_MANAGER_VERSION,
			'all'
		);

		if ( ! empty( $this->additional_css ) ) :
			foreach ( $this->additional_css as $script ) {
				\wp_register_style(
					$script['name'],
					$script['base'] . "lib/css/{$script['name']}{$rtl}{$suffix}.css",
					[ $this->css_name ],
					$script['ver'],
					'all'
				);
			}
		endif;

		$registered = true;

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $registered : Prevents Re-registering of the script.
	 */
	protected function register_admin_javascript() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$suffix = \the_seo_framework()->script_debug ? '' : '.min';

		\wp_register_script(
			$this->js_name,
			TSF_EXTENSION_MANAGER_DIR_URL . "lib/js/{$this->js_name}{$suffix}.js",
			[ 'jquery' ],
			TSF_EXTENSION_MANAGER_VERSION,
			true
		);

		if ( ! empty( $this->additional_js ) ) :
			foreach ( $this->additional_js as $script ) {
				\wp_register_script(
					$script['name'],
					$script['base'] . "lib/js/{$script['name']}{$suffix}.js",
					[ $this->js_name ],
					$script['ver'],
					'all'
				);
			}
		endif;

		$registered = true;

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $l7d : Prevents relocalizing of the scripts.
	 * @access private
	 *
	 * @return void early If run twice or more.
	 */
	final public function _localize_admin_javascript() {

		//* Localized.
		static $l7d = null;

		if ( isset( $l7d ) )
			return;

		$strings = [
			'nonce' => \wp_create_nonce( 'tsfem-ajax-nonce' ),
			'debug' => (bool) WP_DEBUG,
			'i18n' => [
				'Activate' => \esc_html__( 'Activate', 'the-seo-framework-extension-manager' ),
				'Deactivate' => \esc_html__( 'Deactivate', 'the-seo-framework-extension-manager' ),
				'InvalidResponse' => \esc_html__( 'Received invalid AJAX response.', 'the-seo-framework-extension-manager' ),
				'UnknownError' => \esc_html__( 'An unknown error occurred.', 'the-seo-framework-extension-manager' ),
				'TimeoutError' => \esc_html__( 'Timeout: Server took too long to respond. Reload the page.', 'the-seo-framework-extension-manager' ),
				'FatalError' => \esc_html__( 'A fatal error occurred on the server.', 'the-seo-framework-extension-manager' ),
				'ParseError' => \esc_html__( 'A parsing error occurred in your browser.', 'the-seo-framework-extension-manager' ),
			],
			'rtl' => (bool) \is_rtl(),
		];

		\wp_localize_script( $this->js_name, 'tsfemL10n', $strings );

		if ( ! empty( $this->additional_l10n ) ) :
			foreach ( $this->additional_l10n as $l10n ) {
				\wp_localize_script( $l10n['dependency'], $l10n['name'], $l10n['strings'] );
			}
		endif;

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
	final public function add_admin_body_class( $classes = '' ) {
		return trim( $classes ) . ' tsfem ';
	}
}
