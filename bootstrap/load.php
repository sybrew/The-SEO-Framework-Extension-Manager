<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_locale', 4 );
\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_load_tsfem', 5 );

/**
 * Loads plugin locale: 'the-seo-framework-extension-manager'
 * Locale folder: the-seo-framework-extension-manager/language/
 *
 * @hook plugins_loaded 4
 * @since 1.0.0
 * @access private
 *
 * @param bool $ignore Whether to load locale outside of the admin area.
 * @return void Early if already loaded.
 */
function _init_locale( $ignore = false ) {

	static $has_loaded = false;

	if ( ! $has_loaded && ( \is_admin() || $ignore ) ) {
		\load_plugin_textdomain(
			'the-seo-framework-extension-manager',
			false,
			\dirname( \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ) . \DIRECTORY_SEPARATOR . 'language',
		);

		$has_loaded = true;
	}
}

/**
 * Loads the plugin.
 *
 * @hook plugins_loaded 5
 * @since 2.7.0
 * @access private
 * @uses constant \PHP_INT_MIN, available from PHP 7.0
 */
function _load_tsfem() {

	if ( ! \function_exists( 'tsf' ) ) {
		if ( \is_admin() )
			require \TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'install-tsf.php';
		return;
	}

	// Prepare plugin upgrader before the plugin loads.
	if ( \tsf_extension_manager_db_version() < \TSF_EXTENSION_MANAGER_DB_VERSION )
		require \TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'upgrade.php';

	\TSF_Extension_Manager\_protect_options();
	\TSF_Extension_Manager\_register_autoloader();

	_init_tsf_extension_manager();
	// TODO var_Dump() next pass:
	// \add_action( 'the_seo_framework_loaded', __NAMESPACE__ . '\\_init_tsf_extension_manager', 0 );
}

/**
 * Prevents option handling outside of the plugin's scope.
 * Warning: When you remove these filters or actions, the plugin will delete all its options on first sight.
 *          This essentially means it will be reset to its initial state.
 *
 * Also Triggers fatal error when The SEO Framework extension manager has not been initialized yet.
 * This is because the required traits files aren't loaded yet. The autoloader treats traits
 * as classes.
 *
 * Also defines \PHP_INT_MIN when not defined. This is used further internally.
 *
 * @since 1.0.0
 * @access private
 * @uses constant \PHP_INT_MIN, available from PHP 7.0
 */
function _protect_options() {

	$current_options = (array) \get_option( \TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] );

	\add_filter(
		'pre_update_option_' . \TSF_EXTENSION_MANAGER_SITE_OPTIONS,
		__NAMESPACE__ . '\\_pre_execute_protect_option',
		\PHP_INT_MIN,
		3
	);

	if ( isset( $current_options['_instance'] ) )
		\add_filter(
			"pre_update_option_tsfem_i_{$current_options['_instance']}",
			__NAMESPACE__ . '\\_pre_execute_protect_option',
			\PHP_INT_MIN,
			3
		);
}

/**
 * Determines if option protection can be loaded, if not, wp_die is performed.
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\SecureOption::verify_option_instance()
 *
 * @param mixed  $new_value The new, unserialized option value.
 * @param mixed  $old_value The old option value.
 * @param string $option    The option name.
 * @return mixed $value on success.
 */
function _pre_execute_protect_option( $new_value, $old_value, $option ) {

	if ( $new_value === $old_value ) return $old_value;

	// phpcs:ignore, TSF.Performance.Functions.PHP -- required
	if ( ! class_exists( 'TSF_Extension_Manager\SecureOption', true ) ) {
		\wp_die( '<code>' . \esc_html( $option ) . '</code> is a protected option.' );
		return $old_value;
	}

	\TSF_Extension_Manager\_load_trait( 'core/overload' );

	// Why do we return on an ACTION? What's happening here... how has it tested time?
	return SecureOption::verify_option_instance( $new_value, $old_value, $option );
}

/**
 * Loads TSF_Extension_Manager\LoadAdmin class when in admin.
 * Loads TSF_Extension_Manager\LoadFront class on the front-end.
 *
 * Also directly initializes extensions after the class constructors have run.
 * This will allow all extensions and functions to run exactly after The SEO Framework has been initialized.
 *
 * Priority 6: Use anything above 6, or any action later than plugins_loaded and
 *             you can access the class and functions. Failing to do so will perform wp_die().
 *             This makes sure The SEO Framework has been initialized correctly as well.
 *             So you can use function `tsf()` at all times.
 *
 * Performs wp_die() when called prior to action `plugins_loaded`.
 *
 * @hook the_seo_framework_loaded 0
 * @since 1.0.0
 * @since 2.7.0 Moved from plugins_loaded 6 to the_seo_framework_loaded (init 0).
 * @access private
 * @factory
 *
 * @return null|object TSF Extension Manager class object.
 */
function _init_tsf_extension_manager() {

	// Memoize the class object. Do not run everything more than once.
	static $tsfem;

	if ( $tsfem )
		return $tsfem;

	if ( version_compare( \THE_SEO_FRAMEWORK_VERSION, '4.2.8', '<' ) )
		return null;

	/**
	 * @since 1.0.0
	 * @param bool $can_load
	 */
	if ( \apply_filters( 'tsf_extension_manager_enabled', true ) ) {
		/**
		 * @since 2.6.3
		 * @internal
		 */
		\define( 'TSF_EXTENSION_MANAGER_USE_MODERN_TSF', version_compare( \THE_SEO_FRAMEWORK_VERSION, '4.3.0', '>=' ) );

		// Load TSF v5.0 transition functions file.
		require TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'transition.php';

		/**
		 * Load class overloading traits.
		 */
		\TSF_Extension_Manager\_load_trait( 'core/overload' );

		/**
		 * @package TSF_Extension_Manager
		 */
		if ( \is_admin() ) {
			$tsfem = new LoadAdmin;
		} else {
			$tsfem = new LoadFront;
		}

		// Initialize extensions.
		$tsfem->_init_extensions();

		/**
		 * Runs after extensions are initialized
		 *
		 * @since 1.5.0
		 */
		\do_action( 'tsfem_extensions_initialized' );
	}

	return $tsfem;
}

/**
 * Registers The SEO Framework extension manager's autoloader.
 *
 * @since 1.0.0
 * @access private
 */
function _register_autoloader() {

	// Prevent overriding of security classes by checking their existence.
	$integrity_classes = [
		'\TSF_Extension_Manager\Core',
		'\TSF_Extension_Manager\Secure_Abstract',
		'\TSF_Extension_Manager\SecureOption',
		'\TSF_Extension_Manager\LoadAdmin',
		'\TSF_Extension_Manager\LoadFront',
	];

	foreach ( $integrity_classes as $_class ) {
		// phpcs:ignore, TSF.Performance.Functions.PHP -- no other method exists.
		class_exists( $_class, false ) and die;
	}

	/**
	 * Register class autoload here.
	 * This will make sure the website crashes when extensions try to bypass WordPress's loop.
	 */
	spl_autoload_register( __NAMESPACE__ . '\\_autoload_classes', true, true );
}

/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 1.0.0
 * @since 2.5.1 Now handles mixed-case class names.
 * @since 2.6.0 Now uses `hrtime()` instead of `microtime()`.
 * @uses \TSF_EXTENSION_MANAGER_DIR_PATH_CLASS
 * @access private
 *
 * @NOTE 'TSF_Extension_Manager\' is a reserved namespace. Using it outside of this
 *       plugin's scope could result in an error.
 *
 * @param string $class The class name.
 * @return void Early if the class is not within the current namespace.
 */
function _autoload_classes( $class ) {

	$class = strtolower( $class );

	// NB It's TSF_Extension_Manager, not tsf_extension_manager!
	if ( ! str_starts_with( $class, 'tsf_extension_manager\\' ) ) return;

	if ( \WP_DEBUG ) {
		/**
		 * Prevent loading sub-namespaces when they're not initiated correctly.
		 *
		 * Only on a fatal error within autoloaded files, this check will yield true.
		 * Prevent this function to then show an unrelated fatal error because
		 * it's not meant load that file. Then it will propagate the error
		 * towards the actual error within the previously and already loaded file.
		 */
		if ( substr_count( $class, '\\', 2 ) >= 2 )
			return;
	}

	static $_timer;

	$_timer ??= hrtime( true );

	$file = str_replace( 'tsf_extension_manager\\', '', $class );

	if ( str_contains( $file, '_abstract' ) ) {
		$file    = str_replace( '_abstract', '.abstract', $file );
		$rel_dir = 'abstract' . \DIRECTORY_SEPARATOR;
	} else {
		$rel_dir = '';
	}

	require \TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . "{$rel_dir}{$file}.class.php";

	if ( isset( $_timer ) ) {
		// When the class extends, the last class in the stack will reach this first.
		// All classes before cannot reach this any more.
		$_t = ( hrtime( true ) - $_timer ) / 1e9;
		\The_SEO_Framework\_bootstrap_timer( $_t );
		\TSF_Extension_Manager\_bootstrap_timer( $_t );
		$_timer = null;
	}
}
