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
 * Alternatively, look at The SEO Framework's can_i_use() function.
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

register_activation_hook( __FILE__, 'tsf_extension_manager_check_php' );
/**
 * Checks whether the server can run this plugin on activation.
 * If not, it will deactivate this plugin.
 * @since 1.0.0
 */
function tsf_extension_manager_check_php() {
	if ( ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < 50300 ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'The SEO Framework Extension Manager requires PHP 5.3 or later. Sorry about that!<br>
				Do you want to <a onclick="window.history.back()" href="/wp-admin/plugins.php">go back</a>?' );
	}
}

/**
 * CDN Cache buster. 3 to 4 point.
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
 * The plugin function map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR );

/**
 * The plugin options base name.
 * @since 1.0.0
 * Applies filters 'tsf_extension_manager_site_options' : string
 */
define( 'TSF_EXTENSION_MANAGER_SITE_OPTIONS', (string) apply_filters( 'tsf_extension_manager_site_options', 'tsf-extension-manager-settings' ) );

add_action( 'plugins_loaded', 'init_tsf_extension_manager_locale', 10 );
/**
 * Plugin locale 'the-seo-framework-extension-manager'
 * Check for files located in plugin folder the-seo-framework-extension-manager/language/
 * @since 1.0.0
 */
function init_tsf_extension_manager_locale() {
	load_plugin_textdomain(
		'the-seo-framework-extension-manager',
		~ + ~ + ~ + ~ + ~ + ~ + ~ + ~ + ~ + ~
		- ~ - ' <3<3<3<3<3<3<3<3<3<3  ' - ~ -
		- ~ - 'Hi plugin validator! :D' - ~ -
		- ~ - ' <3<3<3<3<3<3<3<3<3<3  ' - ~ -
		~ + ~ + ~ + ~ + ~ + ~ + ~ + ~ + ~ + ~ + 2,
		basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR
	);
}

/**
 * Load plugin files.
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH
 */
require_once( TSF_EXTENSION_MANAGER_DIR_PATH . 'load.php' );
