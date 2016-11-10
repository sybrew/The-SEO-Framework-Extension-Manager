<?php
/**
 * Plugin Name: The SEO Framework - Extension Manager
 * Plugin URI: https://wordpress.org/plugins/the-seo-framework-extension-manager/
 * Description: Add more powerful SEO features to The SEO Framework right from your WordPress Dashboard.
 * Version: 1.0.0
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv3
 * Text Domain: the-seo-framework-extension-manager
 * Domain Path: /language
 */

/**
 * Developers note:
 * This plugin is essentially written the same as The SEO Framework.
 *
 * Please bear in mind that not always all classes are loaded. This is because it's
 * more likely to have this plugin deactivated (although present) in a multisite
 * environment. This also saves resources.
 *
 * Not all states are the same for each site at all times, making it a vibrant plugin.
 * Use isset/function_exists/method_exists as much as possible if you wish to apply alterations.
 * Alternatively, look at The SEO Framework's can_i_use() method.
 */

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
 * CDN Cache buster. 3 point.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_VERSION', '0.9.0' );

/**
 * The plugin map URL. Used for calling browser files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * The plugin map absolute path. Used for calling php files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The plugin file relative to the plugins dir.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The plugin file, absolute unix path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE', __FILE__ );

/**
 * The plugin class map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_CLASS', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * The plugin class map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'traits' . DIRECTORY_SEPARATOR );

/**
 * The plugin function map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR );

/**
 * The plugin extensions base path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSIONS_PATH', TSF_EXTENSION_MANAGER_DIR_PATH . 'extensions' . DIRECTORY_SEPARATOR );

/**
 * The plugin options base name.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_SITE_OPTIONS', 'tsf-extension-manager-settings' );

/**
 * The extension options base name.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS', 'tsf-extension-manager-extension-settings' );

add_action( 'activate_' . TSF_EXTENSION_MANAGER_PLUGIN_BASENAME, '_tsf_extension_manager_test_server' );
/**
 * Checks whether the server can run this plugin on activation.
 * If not, it will deactivate this plugin.
 *
 * This function will create a parse error on PHP < 5.3 (use of goto wrappers).
 * Which makes a knowledge database entry easier to make as it won't change anytime soon.
 * Otherwise, it will crash in the first called file because of the "use" keyword.
 *
 * @since 1.0.0
 * @see register_activation_hook():
 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
 *
 * @param bool $network_wide Whether the plugin is activated on a multisite network.
 * @return void Early if tests pass.
 */
function _tsf_extension_manager_test_server( $network_wide = false ) {

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
		deactivate_plugins( plugin_basename( __FILE__ ) );
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
			return;
	endswitch;

	$network = $network_wide ? 'network/' : '';
	$pluginspage = admin_url( $network . 'plugins.php' );

	//* Let's have some fun with teapots.
	$response = floor( time() / DAY_IN_SECONDS ) === floor( strtotime( 'first day of April ' . date( 'Y' ) ) / DAY_IN_SECONDS ) ? 418 : 500;

	wp_die(
		sprintf(
			'<p><strong>The SEO Framework - Extension Manager</strong> requires <em>%s</em>. Sorry about that!<br>Your %s is: <code>%s</code></p>
			<p>Do you want to <strong><a onclick="window.history.back()" href="%s">go back</a></strong>?</p>',
			esc_html( $requirement ), esc_html( $issue ), esc_html( $version ), esc_url( $pluginspage )
		),
		sprintf( 'The SEO Framework - Extension Manager &laquo; %s', esc_attr( $subtitle ) ),
		array( 'response' => intval( $response ) )
	);
}

add_action( 'plugins_loaded', 'init_tsf_extension_manager_locale', 4 );
/**
 * Loads plugin locale: 'the-seo-framework-extension-manager'
 * Locale folder: the-seo-framework-extension-manager/language/
 *
 * @since 1.0.0
 * @staticvar $loaded Determines if the textdomain has already been loaded.
 *
 * @param bool $ignore Whether to load locale outside of the admin area.
 * @return void Early if already loaded.
 */
function init_tsf_extension_manager_locale( $ignore = false ) {
	if ( is_admin() || $ignore ) {
		static $loaded = false;

		if ( $loaded = $loaded ? false : $loaded = true ? false : true )
			return;

		load_plugin_textdomain(
			'the-seo-framework-extension-manager',
			false,
			basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR
		);
	}
}

/**
 * Load plugin files.
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH
 */
require_once( TSF_EXTENSION_MANAGER_DIR_PATH . 'load.php' );

/**
 * Load functions file.
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION
 */
require_once( TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'functions.php' );
