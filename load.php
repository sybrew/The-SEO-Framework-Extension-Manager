<?php

defined( 'TSF_EXTENSION_MANAGER_DIR_PATH' ) or die;

/**
 * @package TSF_Extension_Manager
 */
use TSF_Extension_Manager\LoadAdmin as LoadAdmin;
use TSF_Extension_Manager\LoadFrontend as LoadFrontend;
use TSF_Extension_Manager\SecureOption as SecureOption;

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
 * Loads the class from cache.
 * This is the recommended way of calling the class, if needed.
 * Call this after action 'init' priority 0 otherwise it will kill the plugin,
 * or even other plugins.
 *
 * @since 1.0.0
 *
 * @return null|object The plugin class object.
 */
function tsf_extension_manager() {
	return _init_tsf_extension_manager();
}

/**
 * Returns the minimum role required to adjust and access settings.
 *
 * @since 1.0.0
 * @staticvar bool $cache
 *
 * @return string The minimum required capability for extension installation.
 */
function can_do_tsf_extension_manager_settings() {

	static $cache = null;

	if ( isset( $cache ) )
		return $cache;

	return $cache = current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' );
}

_tsf_extension_manager_protect_options();
/**
 * Prevents option handling outside of the plugin's scope.
 * Warning: When you remove these filters or action, the plugin will delete all its options on first sight.
 *          This essentially means it will be reset to its initial state.
 *
 * Also Triggers fatal error when The SEO Framework extension manager has not been initialized yet.
 * This is because the required traits files aren't loaded yet. The autoloader treats traits
 * as classes.
 *
 * @since 1.0.0
 * @access private
 * @uses constant PHP_INT_MIN, available from PHP 7.0
 */
function _tsf_extension_manager_protect_options() {

	defined( 'PHP_INT_MIN' ) or define( 'PHP_INT_MIN', ~ PHP_INT_MAX );

	$current_options = (array) get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, array() );

	add_filter( 'pre_update_option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS, '_pre_execute_extension_manager_protect_option', PHP_INT_MIN, 3 );
	if ( isset( $current_options['_instance'] ) )
		add_filter( 'pre_update_option_tsfem_i_' . $current_options['_instance'], '_pre_execute_extension_manager_protect_option', PHP_INT_MIN, 3 );
}

/**
 * Determines if option protection can be loaded, if not, wp_die is performed.
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\SecureOption::verify_option_instance()
 *
 * @param mixed $value The new, unserialized option value.
 * @param mixed $old_value The old option value.
 * @param string $option The option name.
 * @return mixed $value on success.
 */
function _pre_execute_extension_manager_protect_option( $new_value, $old_value, $option ) {

	if ( false === class_exists( 'TSF_Extension_Manager\SecureOption' ) )
		wp_die( '<code>' . esc_html( $option ) . '</code> is a protected option.' );

	/**
	 * Load class overloading traits.
	 */
	_tsf_extension_manager_load_trait( 'overload' );

	return SecureOption::verify_option_instance( $new_value, $old_value, $option );
}

add_action( 'plugins_loaded', '_init_tsf_extension_manager', 6 );
/**
 * Loads TSF_Extension_Manager\LoadAdmin class when in admin.
 * Loads TSF_Extension_Manager\LoadFrontend class on the front-end.
 *
 * Also directly initializes extensions after the class constructors have run.
 * This will allow all extensions and functions to run exactly after The SEO Framework has been initialized.
 *
 * @action plugins_loaded
 * @priority 6 Use anything above 6, or any action later than plugins_loaded and
 * 		you can access the class and functions. Failing to do so will perform wp_die().
 *		This makes sure The SEO Framework has been initialized correctly as well.
 *		So you can use function `the_seo_framework()` at all times.
 *
 * Performs wp_die() when called prior to action `plugins_loaded`.
 *
 * @since 1.0.0
 * @staticvar object $tsf_extension_manager
 * @access private
 *
 * @return null|object TSF Extension Manager class object.
 */
function _init_tsf_extension_manager() {

	//* Cache the class object. Do not run everything more than once.
	static $tsf_extension_manager = null;

	if ( $tsf_extension_manager )
		return $tsf_extension_manager;

	if ( false === doing_action( 'plugins_loaded' ) )
		wp_die( 'Use tsf_extension_manager() after action `plugins_loaded` priority 7.' );

	if ( can_load_tsf_extension_manager() ) {

		/**
		 * Load class overloading traits.
		 */
		_tsf_extension_manager_load_trait( 'overload' );

		/**
		 * @package TSF_Extension_Manager
		 */
		if ( is_admin() ) {
			$tsf_extension_manager = new LoadAdmin;
		} else {
			$tsf_extension_manager = new LoadFrontend;
		}

		//* Initialize extensions.
		$tsf_extension_manager->_init_extensions();

	}

	return $tsf_extension_manager;
}

_register_tsf_extension_manager();
/**
 * Registers The SEO Framework extension manager's autoloader.
 *
 * @since 1.0.0
 * @access private
 */
function _register_tsf_extension_manager() {

	//* Prevent overriding of security classes.
	! ( class_exists( 'TSF_Extension_Manager\Core' ) || class_exists( 'TSF_Extension_Manager\Secure_Abstract' ) || class_exists( 'TSF_Extension_Manager\SecureOption' ) )
	and ! ( class_exists( 'TSF_Extension_Manager\LoadAdmin' ) || class_exists( 'TSF_Extension_Manager\LoadFrontend' ) )
		or wp_die( -1 );

	/**
	 * Register class autoload here.
	 * This will make sure the website crashes when extensions try to bypass WordPress' loop.
	 */
	spl_autoload_register( '_autoload_tsf_extension_manager_classes', true, true );

}

/**
 * Determines whether we can load the the plugin.
 *
 * Applies filters 'tsf_extension_manager_enabled' : boolean
 *
 * @since 1.0.0
 * @staticvar bool $can_load
 * @uses the_seo_framework_version() which returns null if plugin is inactive.
 *
 * @return bool Whether the plugin can load. Always returns false on the front-end.
 */
function can_load_tsf_extension_manager() {

	static $can_load = null;

	if ( isset( $can_load ) )
		return $can_load;

	if ( function_exists( 'the_seo_framework_version' ) && version_compare( the_seo_framework_version(), '2.7', '>=' ) )
		return $can_load = (bool) apply_filters( 'tsf_extension_manager_enabled', true );

	return $can_load = false;
}

/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_CLASS
 * @access private
 *
 * @NOTE 'TSF_Extension_Manager\' is a reserved namespace. Using it outside of this
 * 		plugin's scope could result in an error.
 *
 * @param string $class The class name.
 * @return bool False if file hasn't yet been included, otherwise true.
 */
function _autoload_tsf_extension_manager_classes( $class ) {

	if ( 0 !== strpos( $class, 'TSF_Extension_Manager\\', 0 ) )
		return;

	if ( strpos( $class, '_Abstract' ) ) {
		$path = TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'abstract' . DIRECTORY_SEPARATOR;
	} else {
		$path = TSF_EXTENSION_MANAGER_DIR_PATH_CLASS;
	}

	$class = strtolower( str_replace( 'TSF_Extension_Manager\\', '', $class ) );
	$class = str_replace( '_abstract', '.abstract', $class );

	require_once( $path . $class . '.class.php' );
}

/**
 * Requires trait files once.
 *
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT
 * @access private
 * @staticvar array $loaded
 *
 * @param string $file Where the trait is for.
 * @return void; Early if file is already loaded.
 */
function _tsf_extension_manager_load_trait( $file ) {

	static $loaded = array();

	if ( isset( $loaded[ $file ] ) )
		return;

	$loaded[ $file ] = require_once( TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT . $file . '.trait.php' );

	return;
}

/**
 * Requires WordPress compat files once.
 *
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT
 * @access private
 * @staticvar array $loaded
 * @global string $wp_version
 *
 * @param string $version The version where the WordPress compatibility is required for.
 * @return void; Early if file is already loaded or when compatibility has already been determined.
 */
function _tsf_extension_manager_load_wp_compat( $version = '' ) {

	static $loaded = array();

	if ( isset( $loaded[ $version ] ) )
		return;

	if ( empty( $version ) || strlen( $version ) > 3 ) {
		the_seo_framework()->_doing_it_wrong( __FUNCTION__, 'You must tell the two-point required WordPress version.' );
		return $loaded[ $version ] = false;
	}

	global $wp_version;

	$_wp_version = $wp_version;

	if ( version_compare( $_wp_version, $version, '>=' ) )
		return $loaded[ $version ] = true;

	$loaded[ $version ] = require_once( TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT . 'wp-' . $version . '.php' );

	return;
}
