<?php
/**
* @package TSF_Extension_Manager
*/
namespace TSF_Extension_Manager;

defined( 'TSF_EXTENSION_MANAGER_DIR_PATH' ) or die;

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

\add_action( 'activate_' . TSF_EXTENSION_MANAGER_PLUGIN_BASENAME, __NAMESPACE__ . '\\_test_sever' );
/**
 * Checks whether the server can run this plugin on activation.
 * If not, it will deactivate this plugin.
 *
 * This function will create a parse error on PHP < 5.3 (use of goto wrappers).
 * Which makes a knowledge database entry easier to make as it won't change anytime soon.
 * Otherwise, it will crash in the next called file because of the "use" keyword.
 *
 * @since 1.0.0
 * @see register_activation_hook():
 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
 * @access private
 *
 * @param bool $network_wide Whether the plugin is activated on a multisite network.
 * @return void Early if tests pass.
 */
function _test_sever( $network_wide = false ) {

	evaluate : {
		   PHP_VERSION_ID < 50521 and $test = 1
		or PHP_VERSION_ID >= 50600 && PHP_VERSION_ID < 50605 and $test = 2
		or $GLOBALS['wp_db_version'] < 35700 and $test = 3
		or $test = true;
	}

	//* All good.
	if ( true === $test )
		return;

	deactivate : {
		//* Not good. Deactivate plugin.
		\deactivate_plugins( TSF_EXTENSION_MANAGER_PLUGIN_BASENAME, false, $network_wide );
	}

	switch ( $test ) :
		case 1 :
		case 2 :
			//* PHP requirements not met, always count up to encourage best standards.
			$requirement = 1 === $test ? 'PHP 5.5.21 or later' : 'PHP 5.6.5 or later';
			$issue = 'PHP version';
			$version = phpversion();
			$subtitle = 'Server Requirements';
			break;

		case 3 :
			//* WordPress requirements not met.
			$requirement = 'WordPress 4.4 or later';
			$issue = 'WordPress version';
			$version = $GLOBALS['wp_version'];
			$subtitle = 'WordPress Requirements';
			break;

		default :
			\wp_die();
	endswitch;

	//* network_admin_url() falls back to admin_url() on single. But networks can enable single too.
	$pluginspage = $network_wide ? \network_admin_url( 'plugins.php' ) : \admin_url( 'plugins.php' );

	//* Let's have some fun with teapots.
	$response = floor( time() / DAY_IN_SECONDS ) === floor( strtotime( 'first day of April ' . date( 'Y' ) ) / DAY_IN_SECONDS ) ? 418 : 500;

	\wp_die(
		sprintf(
			'<p><strong>The SEO Framework - Extension Manager</strong> requires <em>%s</em>. Sorry about that!<br>Your %s is: <code>%s</code></p>
			<p>Do you want to <strong><a onclick="window.history.back()" href="%s">go back</a></strong>?</p>',
			\esc_html( $requirement ), \esc_html( $issue ), \esc_html( $version ), \esc_url( $pluginspage )
		),
		sprintf( 'The SEO Framework - Extension Manager &laquo; %s', \esc_attr( $subtitle ) ),
		array( 'response' => intval( $response ) )
	);
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_locale', 4 );
/**
 * Loads plugin locale: 'the-seo-framework-extension-manager'
 * Locale folder: the-seo-framework-extension-manager/language/
 *
 * @since 1.0.0
 * @staticvar $loaded Determines if the textdomain has already been loaded.
 * @access private
 *
 * @param bool $ignore Whether to load locale outside of the admin area.
 * @return void Early if already loaded.
 */
function _init_locale( $ignore = false ) {
	if ( \is_admin() || $ignore ) {
		static $loaded = false;

		if ( $loaded = $loaded ? false : $loaded = true ? false : true )
			return;

		\load_plugin_textdomain(
			'the-seo-framework-extension-manager',
			false,
			basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR
		);
	}
}

/**
 * Returns the minimum role required to adjust and access settings.
 *
 * @since 1.0.0
 * @staticvar bool $cache
 *
 * @return string The minimum required capability for extension installation.
 */
function can_do_settings() {

	static $cache = null;

	return isset( $cache ) ? $cache : $cache = \current_user_can( 'manage_options' );
}

\TSF_Extension_Manager\_protect_options();
/**
 * Prevents option handling outside of the plugin's scope.
 * Warning: When you remove these filters or actions, the plugin will delete all its options on first sight.
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
function _protect_options() {

	defined( 'PHP_INT_MIN' ) or define( 'PHP_INT_MIN', ~ PHP_INT_MAX );

	$current_options = (array) \get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, array() );

	\add_action( 'pre_update_option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS, __NAMESPACE__ . '\\_pre_execute_protect_option', PHP_INT_MIN, 3 );
	if ( isset( $current_options['_instance'] ) )
		\add_action( 'pre_update_option_tsfem_i_' . $current_options['_instance'], __NAMESPACE__ . '\\_pre_execute_protect_option', PHP_INT_MIN, 3 );
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
function _pre_execute_protect_option( $new_value, $old_value, $option ) {

	if ( false === class_exists( 'TSF_Extension_Manager\SecureOption', true ) )
		\wp_die( '<code>' . \esc_html( $option ) . '</code> is a protected option.' );

	/**
	 * Load class overloading traits.
	 */
	\TSF_Extension_Manager\_load_trait( 'overload' );

	return \TSF_Extension_Manager\SecureOption::verify_option_instance( $new_value, $old_value, $option );
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_tsf_extension_manager', 6 );
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

	if ( false === \doing_action( 'plugins_loaded' ) )
		\wp_die( 'Use tsf_extension_manager() after action `plugins_loaded` priority 7.' );

	if ( \TSF_Extension_Manager\can_load_class() ) {

		/**
		 * Load class overloading traits.
		 */
		\TSF_Extension_Manager\_load_trait( 'overload' );

		/**
		 * @package TSF_Extension_Manager
		 */
		if ( \is_admin() ) {
			$tsf_extension_manager = new \TSF_Extension_Manager\LoadAdmin;
		} else {
			$tsf_extension_manager = new \TSF_Extension_Manager\LoadFrontend;
		}

		//* Initialize extensions.
		$tsf_extension_manager->_init_extensions();
	}

	return $tsf_extension_manager;
}

\TSF_Extension_Manager\_register_autoloader();
/**
 * Registers The SEO Framework extension manager's autoloader.
 *
 * @since 1.0.0
 * @access private
 */
function _register_autoloader() {

	//* Prevent overriding of security classes by checking their existence.
	! ( class_exists( 'TSF_Extension_Manager\Core', false ) || class_exists( 'TSF_Extension_Manager\Secure_Abstract', false ) || class_exists( 'TSF_Extension_Manager\SecureOption', false ) )
	and ! ( class_exists( 'TSF_Extension_Manager\LoadAdmin', false ) || class_exists( 'TSF_Extension_Manager\LoadFrontend', false ) )
		or \wp_die( -1 );

	/**
	 * Register class autoload here.
	 * This will make sure the website crashes when extensions try to bypass WordPress' loop.
	 */
	spl_autoload_register( __NAMESPACE__ . '\\_autoload_classes', true, true );
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
function can_load_class() {

	static $can_load = null;

	if ( isset( $can_load ) )
		return $can_load;

	if ( function_exists( 'the_seo_framework_version' ) && version_compare( \the_seo_framework_version(), '2.7', '>=' ) )
		return $can_load = (bool) \apply_filters( 'tsf_extension_manager_enabled', true );

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
 *       plugin's scope could result in an error.
 *
 * @param string $class The class name.
 * @return void Early if the class is not within the current namespace.
 */
function _autoload_classes( $class ) {

	if ( 0 !== strpos( $class, __NAMESPACE__ . '\\', 0 ) )
		return;

	if ( WP_DEBUG ) {
		/**
		 * Prevent loading sub-namespaces.
		 *
		 * Only on a fatal error within autoloaded files, this function will run.
		 * Prevent this function to then show an unrelated fatal error because
		 * it's not meant load that file. Then it will propagate the error
		 * towards the actual error within the previously and already loaded file.
		 */
		if ( substr_count( $class, '\\', 2 ) >= 2 )
			return;
	}

	if ( strpos( $class, '_Abstract' ) ) {
		$path = TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'abstract' . DIRECTORY_SEPARATOR;
	} else {
		$path = TSF_EXTENSION_MANAGER_DIR_PATH_CLASS;
	}

	$class = strtolower( str_replace( __NAMESPACE__ . '\\', '', $class ) );
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
function _load_trait( $file ) {

	static $loaded = array();

	if ( isset( $loaded[ $file ] ) )
		return;

	$loaded[ $file ] = (bool) require_once( TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT . $file . '.trait.php' );

	return;
}

/**
 * Requires WordPress compat files once.
 *
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT
 * @access private
 * @staticvar array $loaded
 *
 * @param string $version The version where the WordPress compatibility is required for.
 * @return bool True if loaded; false otherwise.
 */
function _load_wp_compat( $version = '' ) {

	static $loaded = array();

	if ( isset( $loaded[ $version ] ) )
		return $loaded[ $version ];

	if ( empty( $version ) || strlen( $version ) > 3 ) {
		\the_seo_framework()->_doing_it_wrong( __FUNCTION__, 'You must tell the two-point required WordPress version.' );
		return $loaded[ $version ] = false;
	}

	/**
	 * @global string $wp_version
	 */
	if ( version_compare( $GLOBALS['wp_version'], $version, '>=' ) )
		return $loaded[ $version ] = true;

	return $loaded[ $version ] = (bool) require_once( TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT . 'wp-' . $version . '.php' );
}
