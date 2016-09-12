<?php
/**
 * @package TSF_Extension_Manager
 */
use TSF_Extension_Manager\Load as Load;

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
	return init_tsf_extension_manager();
}

/**
 * Returns the minimum role required to adjust and access settings.
 *
 * @since 1.0.0
 *
 * @return string The minimum required capability for extension installation.
 */
function can_do_tsf_extension_manager_settings() {

	static $cache = null;

	if ( isset( $cache ) )
		return $cache;

	return $cache = current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' );
}

/**
 * Prevent option handling outside of the plugin's scope.
 * Warning: When you remove these filters, the plugin will delete all its options on first sight.
 *          This essentially means it will be reset to its initial state.
 *
 * @access private
 * @since 1.0.0
 */
add_filter( 'pre_update_option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS, array( 'TSF_Extension_Manager\SecureOption', 'verify_option_instance' ), PHP_INT_MIN, 3 );
if ( isset( TSF_EXTENSION_MANAGER_CURRENT_OPTIONS['_instance'] ) )
	add_filter( 'pre_update_option_tsfem_i_' . TSF_EXTENSION_MANAGER_CURRENT_OPTIONS['_instance'], array( 'TSF_Extension_Manager\SecureOption', 'verify_option_instance' ), PHP_INT_MIN, 3 );

add_action( 'plugins_loaded', 'init_tsf_extension_manager', 6 );
/**
 * Loads TSF_Extension_Manager_Load class when in admin.
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
function init_tsf_extension_manager() {

	//* Cache the class. Do not run everything more than once.
	static $tsf_extension_manager = null;

	if ( $tsf_extension_manager )
		return $tsf_extension_manager;

	if ( false === doing_action( 'plugins_loaded' ) )
		wp_die( 'Use tsf_extension_manager() on action `plugins_loaded` priority 7 or later.' );

	if ( can_load_tsf_extension_manager() ) {

		/**
		 * Load class overloading traits.
		 */
		_tsf_extension_manager_load_trait( 'overload' );

		/**
		 * Register class autoload here.
		 * This will make sure the website crashes when extensions try to bypass WordPress' loop.
		 */
		spl_autoload_register( '_autoload_tsf_extension_manager_classes' );

		/**
		 * @package TSF_Extension_Manager
		 */
		$tsf_extension_manager = new Load;
	}

	return $tsf_extension_manager;
}

/**
 * Determines whether we can load the the plugin.
 *
 * Applies filters 'tsf_extension_manager_enabled' : boolean
 *
 * @since 1.0.0
 * @staticvar bool $can_load
 * @uses the_seo_framework_version() Returns null if inactive.
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
 * @staticvar array $loaded Whether $class has been loaded.
 * @NOTE 'TSF_Extension_Manager\' is a reserved namespace. Using it outside of this plugin's scope will result in an error.
 *
 * @return bool False if file hasn't yet been included, otherwise true.
 */
function _autoload_tsf_extension_manager_classes( $class ) {

	if ( 0 !== strpos( $class, 'TSF_Extension_Manager\\', 0 ) )
		return;

	static $loaded = array();

	if ( isset( $loaded[ $class ] ) )
		return true;

	if ( false !== strpos( $class, '_Abstract' ) ) {
		$path = TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'abstract' . DIRECTORY_SEPARATOR;
	} else {
		$path = TSF_EXTENSION_MANAGER_DIR_PATH_CLASS;
	}

	$_class = strtolower( str_replace( 'TSF_Extension_Manager\\', '', $class ) );
	$_class = str_replace( '_abstract', '.abstract', $_class );

	return $loaded[ $class ] = require_once( $path . $_class . '.class.php' );
}

/**
 * Requires trait files once.
 *
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT
 * @access private
 * @staticvar bool $loaded
 *
 * @param string $file Where the trait is for.
 */
function _tsf_extension_manager_load_trait( $file ) {

	static $loaded;

	if ( isset( $loaded[ $file ] ) )
		return;

	require_once( TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT . $file . '.trait.php' );

	$loaded[ $file ] = true;

}
