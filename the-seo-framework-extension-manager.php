<?php
/**
 * Plugin Name: The SEO Framework - Extension Manager
 * Plugin URI: https://wordpress.org/plugins/the-seo-framework-extension-manager/
 * Description: Add more powerful SEO features to The SEO Framework right from your WordPress Dashboard.
 * Version: 1.3.0
 * Author: Sybre Waaijer
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 * Text Domain: the-seo-framework-extension-manager
 * Domain Path: /language
 */

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
 * The plugin version. Always 3 point.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_VERSION', '1.3.0' );

/**
 * The plugin map URL. Used for calling browser files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_URL', \plugin_dir_url( __FILE__ ) );

/**
 * The plugin map absolute path. Used for calling php files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH', \untrailingslashit( __DIR__ ) . DIRECTORY_SEPARATOR );

/**
 * The plugin file relative to the plugins dir.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_BASENAME', \plugin_basename( __FILE__ ) );

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
 * The plugin function map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'compat' . DIRECTORY_SEPARATOR );

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

/**
 * The extension options stale base name.
 * @since 1.3.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS', 'tsf-extension-manager-extension-s-settings' );

/**
 * Load plugin files.
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH
 */
require( TSF_EXTENSION_MANAGER_DIR_PATH . 'load.php' );

/**
 * Load functions file.
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION
 */
require( TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'functions.php' );

/**
 * Loads the class from cache.
 *
 * This is the recommended way of calling the class, if needed.
 * Call this after action 'init' priority 0 otherwise it will kill the plugin,
 * or even other plugins.
 *
 * @since 1.0.0
 *
 * @return null|object The plugin class object.
 */
function tsf_extension_manager() {
	return \TSF_Extension_Manager\_init_tsf_extension_manager();
}
