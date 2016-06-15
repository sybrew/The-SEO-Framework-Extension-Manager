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

/**
 * CDN Cache buster. 3 to 4 point.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_VERSION', '1.0.0' );

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
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_CLASS', TSF_EXTENSION_MANAGER_DIR_PATH . '/inc/classes/' );

add_action( 'plugins_loaded', 'init_tsf_extension_manager_locale', 10 );
/**
 * Plugin locale 'the-seo-framework-extension-manager'
 * File located in plugin folder the-seo-framework-extension-manager/language/
 * @since 1.0.0
 */
function init_tsf_extension_manager_locale() {
	load_plugin_textdomain( 'the-seo-framework-extension-manager', false, basename( dirname( __FILE__ ) ) . '/language/' );
}

/**
 * Load plugin files.
 * @since 1.0.0
 * @uses TSF_EXTENSION_MANAGER_DIR_PATH
 */
require_once( TSF_EXTENSION_MANAGER_DIR_PATH . '/load.php' );
