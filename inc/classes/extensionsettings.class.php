<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	is_headless,
};

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 2.2.0
 */
_load_trait( 'core/ui' );

/**
 * Require error trait.
 *
 * @since 2.2.0
 */
_load_trait( 'core/error' );

/**
 * Require extension settings trait.
 *
 * @since 2.2.0
 */
_load_trait( 'extension/options' );

/**
 * Class TSF_Extension_Manager\ExtensionSettings
 *
 * Holds extension settings layout grid.
 *
 * @since 2.2.0
 * @access private
 */
final class ExtensionSettings {
	use UI,
		Error,
		Extension_Options,
		Construct_Core_Static_Stray_Private_Instance;

	/**
	 * @since 2.2.0
	 * @var array The extension settings, will be forwarded to the FormGenerator instance.
	 */
	private static $settings = [];

	/**
	 * @since 2.2.0
	 * @var array The extension setting defaults, will be forwarded to the FormGenerator instance.
	 */
	private static $defaults = [];

	/**
	 * @since 2.2.0
	 * @var array The registered settings sanitization callbacks.
	 */
	private static $sanitize = [];

	/**
	 * @since 2.2.0
	 * @var string The settings page slug.
	 */
	private static $settings_page_slug = 'theseoframework-extension-settings';

	/**
	 * @since 2.2.0
	 * @see static::verify()
	 * @var string|null The inclusion secret generated on tab load.
	 */
	private static $include_secret;

	/**
	 * Initializes the options, once.
	 *
	 * Must be called before this system is activated.
	 *
	 * @since 2.2.0
	 */
	public static function prepare() {
		has_run( __METHOD__ ) or static::get_instance()->prepare_settings();
	}

	/**
	 * Registers the extension slug settings.
	 *
	 * @since 2.2.0
	 * @uses static::$settings
	 *
	 * @param string $extension_slug The extension slug to register settings for.
	 * @param array  $settings       The extension settings, will be forwarded to the FormGenerator instance.
	 */
	public static function register_settings( $extension_slug, $settings ) {
		/**
		 * This filter only affects the options for display.
		 * It does NOT affect the options elsewhere, unless saved.
		 *
		 * @since 2.2.0
		 * @see $this->register_sanitization()
		 *
		 * @param array  $settings       The registered settings.
		 * @param string $extension_slug The extension slug that's registering settings.
		 */
		static::$settings[ $extension_slug ] = \apply_filters_ref_array(
			'tsf_extension_manager_register_extension_settings',
			[
				$settings,
				$extension_slug,
			]
		);
	}

	/**
	 * Registers the extension slug setting defaults.
	 *
	 * @since 2.2.0
	 * @uses static::$defaults
	 *
	 * @param string $extension_slug The extension slug to register settings for.
	 * @param array  $defaults       The extension default settings, will be forwarded to the FormGenerator instance.
	 */
	public static function register_defaults( $extension_slug, $defaults ) {
		/**
		 * This filter only affects the default option for display.
		 * It does NOT affect the default option elsewhere.
		 *
		 * @since 2.2.0
		 *
		 * @param array  $settings       The registered settings.
		 * @param string $extension_slug The extension slug that's registering settings.
		 */
		static::$defaults[ $extension_slug ] = \apply_filters_ref_array(
			'tsf_extension_manager_register_extension_settings_defaults',
			[
				$defaults,
				$extension_slug,
			]
		);
	}

	/**
	 * Registers the extension slug settings sanitization.
	 * Required.
	 *
	 * @since 2.2.0
	 * @uses static::$sanitize
	 *
	 * @param string $extension_slug The extension slug that's registering sanitization.
	 * @param array  $settings       The registered settings sanitization callbacks.
	 */
	public static function register_sanitization( $extension_slug, $settings ) {
		/**
		 * @since 2.2.0
		 *
		 * @param array  $settings       The registered settings sanitization callbacks.
		 * @param string $extension_slug The extension slug that's registering sanitization.
		 */
		static::$sanitize[ $extension_slug ] = \apply_filters_ref_array(
			'tsf_extension_manager_register_extension_settings_sanitization',
			[
				$settings,
				$extension_slug,
			]
		);
	}

	/**
	 * Prepares the settings page.
	 *
	 * @since 2.2.0
	 */
	private function prepare_settings() {

		/**
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_extension_settings_error_notice_option';

		// Nothing to do here if headless.
		if ( is_headless( 'settings' ) ) return;

		\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 12 );
		\add_action( 'admin_init', [ $this, '_load_admin_actions' ], 10 );
	}

	/**
	 * Adds WordPress menu entry for the settings page.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	public function _add_menu_link() {

		$es_i18n = \__( 'Extension Settings', 'the-seo-framework-extension-manager' );

		$this->ui_hook = \add_submenu_page(
			\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
				? \tsf()->admin()->menu()->get_top_menu_args()['menu_slug'] // parent_slug
				: \tsf()->seo_settings_page_slug,
			$es_i18n, // page_title
			$es_i18n, // menu_title
			\TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE,
			static::$settings_page_slug, // menu_slug
			[ $this, '_output_settings_page' ] // callback
		);
	}

	/**
	 * Loads admin-actions.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	public function _load_admin_actions() {

		if ( \wp_doing_ajax() ) {
			// Run after other extensions are done parsing. They must ignore empty indexes.
			\add_action( 'tsfem_form_do_ajax_save', [ $this, '_do_ajax_form_save' ], 20 );
		} else {
			\add_action( "load-{$this->ui_hook}", [ $this, '_do_settings_page_actions' ] );
		}
	}

	/**
	 * Processes form fields.
	 *
	 * Invoked by the FormGenerator output.
	 * Action is called in TSF_Extension_Manager\LoadAdmin::_wp_ajax_tsfemForm_save().
	 * It has already checked referrer and capability.
	 *
	 * @since 2.2.0
	 * @access private
	 * @see \TSF_Extension_Manager\FormGenerator
	 * @see \TSF_Extension_Manager\LoadAdmin
	 */
	public function _do_ajax_form_save() {

		/**
		 * Register your sanitization here for best performance.
		 *
		 * @since 3.2.0
		 * @param string The current class name.
		 */
		\do_action( 'tsfem_register_settings_sanitization', static::class );

		// phpcs:ignore, WordPress.Security.NonceVerification -- Already done at _wp_ajax_tsfemForm_save()
		$post_data = $_POST['data'] ?? '';
		parse_str( $post_data, $data );

		$store = [];

		// Only store sanitized data. Thank you.
		// This may leave stray options about. That should be resolved through the upgrader.
		foreach ( static::$sanitize as $slug => $sanitizations ) {
			if ( ! isset( $data[ \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $slug ] ) )
				continue;

			foreach ( $sanitizations as $_key => $_cb ) {
				$store[ $slug ][ $_key ] = \call_user_func(
					$_cb,
					$data[ \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $slug ][ $_key ] ?? null
				);
			}
		}

		if ( empty( $store ) )
			\wp_send_json_error( [ 'notice' => $this->get_ajax_notice( false, 18101 ) ] );

		$success = [];

		foreach ( $store as $slug => $data ) {
			$this->o_index = $slug;
			foreach ( $data as $key => $value ) {
				// Yea, update the same index over and over again. @TODO fixme -> update_option_multi?
				$success[ $slug ] = $this->update_option( $key, $value );

				// Break this loop on failure. Continue to next extension.
				if ( ! $success[ $slug ] ) break;
			}
		}

		$data = [
			'success' => array_keys( $success, true, true ),
			'failed'  => array_keys( $success, false, true ),
		];

		if ( \in_array( false, $success, true ) ) {
			if ( \in_array( true, $success, true ) ) {
				// Some data got saved.
				// TODO do something with the failures (when we implement a save-all button).
				\wp_send_json_error( [
					'notice' => $this->get_ajax_notice( false, 18102 ),
					'data'   => $data,
				] );
			} else {
				// No data got saved.
				\wp_send_json_error( [
					'notice' => $this->get_ajax_notice( false, 18103 ),
					'data'   => $data,
				] );
			}
		}

		\wp_send_json_success( [
			'notice' => $this->get_ajax_notice(
				false,
				\count( $success ) > 1 ? 18104 : 18105,
			),
			'data'   => $data,
		] );
	}

	/**
	 * Sets up the actions for the settings page.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	public function _do_settings_page_actions() {

		if (
			   empty( $this->ui_hook )
			|| ( $GLOBALS['page_hook'] ?? null ) !== $this->ui_hook
		) return;

		\add_filter( 'the_seo_framework_scripts', [ $this, '_register_scripts' ], 10, 3 );

		// Add something special for Vivaldi & Android.
		\add_action( 'admin_head', [ \tsfem(), '_output_theme_color_meta' ], 0 );

		/**
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->init_errors();

		$this->init_ui();
	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	public function _output_settings_page() {

		\do_action( 'tsfem_register_settings_fields', static::class );

		\add_action( 'tsfem_header', [ $this, '_output_settings_header' ] );
		\add_action( 'tsfem_content', [ $this, '_output_settings_content' ] );
		\add_action( 'tsfem_footer', [ $this, '_output_settings_footer' ] );

		$this->wrap_type = 'row';
		$this->ui_wrap( 'panes' );
	}

	/**
	 * Registers default TSFEM settings admin scripts.
	 * Also registers TSF scripts, for TT (tooltip) support.
	 *
	 * @since 2.2.0
	 * @access private
	 * @internal
	 *
	 * @param array  $scripts  The default CSS and JS loader settings.
	 * @param string $registry The \The_SEO_Framework\Admin\Script\Registry registry class name.
	 * @param string $loader   The \The_SEO_Framework\Admin\Script\Loader loader class name.
	 * @return array More CSS and JS loaders.
	 */
	public function _register_scripts( $scripts, $registry, $loader ) {

		$loader::prepare_media_scripts();

		$scripts[] = $this->get_form_scripts( $scripts );
		$scripts[] = $loader::get_media_scripts();

		return $scripts;
	}

	/**
	 * Outputs the settings page header.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	public function _output_settings_header() {
		$this->output_view( 'layout/extension/top' );
	}

	/**
	 * Outputs the settings page content container and fields.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	public function _output_settings_content() {
		$this->output_view( 'layout/extension/content' );
	}

	/**
	 * Outputs the settings page footer.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	public function _output_settings_footer() {
		$this->output_view( 'layout/extension/footer' );
	}

	/**
	 * Returns the settings form instance for the given index.
	 *
	 * @since 2.2.0
	 *
	 * @param string $index The options index.
	 */
	private static function get_settings_form( $index ) {

		static $form = [];

		if ( ! isset( $form[ $index ] ) ) {
			// The arguments are passed by reference. Thus, we need to create one.
			// TODO make this dynamic?
			$args = [
				'caller'       => __CLASS__,
				'o_index'      => $index,
				'o_defaults'   => static::$defaults[ $index ] ?? [],
				'o_key'        => '',
				'use_stale'    => false,
				'levels'       => 5,
				'architecture' => null,
			];

			$form[ $index ] = new \TSF_Extension_Manager\FormGenerator( $args );
		}

		return $form[ $index ];
	}

	/**
	 * Returns a save-all button.
	 *
	 * @since 2.2.0
	 */
	private function get_save_all_button() {
		return '';
		// phpcs:disable
		return sprintf(
			'<button type=submit name=tsf-extension-manager-extension-settings form=tsf-extension-manager-extension-settings class="tsfem-button-primary tsfem-button-upload" onclick="tsfemForm.saveAll()">%s</button>',
			\esc_html__( 'Save All', 'the-seo-framework-extension-manager' )
		);
		// phpcs:enable
	}

	/**
	 * Outputs the settings form instance for the given index.
	 *
	 * @since 2.2.0
	 * @access private
	 *
	 * @param string $index    The options index.
	 * @param array  $settings The settings fields compatible with the FormGenerator instance.
	 */
	public static function _output_pane_settings( $index, $settings ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis -- includes.
		static::get_instance()->output_view( 'layout/extension/pane', get_defined_vars() );
	}

	/**
	 * Outputs the settings form instance for the given index.
	 *
	 * @since 2.2.0
	 * @access private
	 *
	 * @param string $index The options index.
	 */
	public static function _output_pane_settings_footer( $index ) {
		// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- _form_button escapes.
		echo static::get_settings_form( $index )->_form_button(
			'submit',
			\__( 'Save', 'the-seo-framework-extension-manager' ),
			'get'
		);
		// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Verifies template view inclusion secret.
	 *
	 * @since 2.2.0
	 * @see static::output_view()
	 * @uses static::$include_secret
	 *
	 * @example template file header:
	 * `\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and \TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;`
	 *
	 * @param string $secret The passed secret.
	 * @return bool True on success, false on failure.
	 */
	public static function verify( $secret ) {
		return isset( $secret ) && static::$include_secret === $secret;
	}

	/**
	 * Outputs view, whilst trying to prevent 3rd party interference on views.
	 *
	 * There's a secret key generated on each file load. This key can be accessed
	 * in the view through `$_secret`, and be sent back to this class.
	 *
	 * @see static::verify( $secret )
	 *
	 * @since 2.2.0
	 * @uses static::$include_secret
	 *
	 * @param string $file The file location, relative to TSFEM base view folder.
	 * @param array  $args The registered view arguments.
	 */
	private function output_view( $file, $args = [] ) {

		foreach ( $args as $_key => $_val )
			$$_key = $_val;
		unset( $_key, $_val, $args );

		// Prevents private-includes hijacking.
		// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis -- Read the include?
		static::$include_secret = $_secret = mt_rand() . uniqid( '', true );
		include \tsfem()->get_view_location( $file );
		static::$include_secret = null;
	}
}
