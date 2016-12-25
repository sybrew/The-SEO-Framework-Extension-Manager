<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor
 */
namespace TSF_Extension_Manager_Extension;

/**
 * Extension Name: Monitor *beta*
 * Extension URI: https://premium.theseoframework.com/extensions/monitor/
 * Extension Description: The Monitor extension keeps track of your website's SEO, optimization, uptime and statistics.
 * Extension Version: 1.0.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 * Extension Menu Slug: theseoframework-monitor
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Monitor extension for The SEO Framework
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
 * The extension version.
 * @since 1.0.0
 */
define( 'TSFEM_E_MONITOR_VERSION', '1.0.0' );

/**
 * The extension file, absolute unix path.
 * @since 1.0.0
 */
define( 'TSFEM_E_MONITOR_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 * @since 1.0.0
 */
define( 'TSFEM_E_MONITOR_DIR_URL', \TSF_Extension_Manager\extension_dir_url( TSFEM_E_MONITOR_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 * @since 1.0.0
 */
define( 'TSFEM_E_MONITOR_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( TSFEM_E_MONITOR_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 * @since 1.0.0
 */
define( 'TSFEM_E_MONITOR_PATH_CLASS', TSFEM_E_MONITOR_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

\add_action( 'plugins_loaded', __NAMESPACE__ . '\monitor_init', 11 );
/**
 * Initialize the extension.
 *
 * @since 1.0.0
 * @action 'plugins_loaded'
 * @priority 11
 *
 * @return bool True if class is loaded.
 */
function monitor_init() {

	static $loaded = null;

	//* Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	\tsf_extension_manager()->_register_premium_extension_autoload_path( TSFEM_E_MONITOR_PATH_CLASS, 'Monitor' );

	if ( is_admin() ) {
		new \TSF_Extension_Manager_Extension\Monitor_Admin();
	} else {
		//* Statistical data. TODO.
		// new Monitor_Frontend();
	}

	return $loaded = true;
}

/**
 * Returns the active monitor base class.
 *
 * @since 1.0.0
 *
 * @return string The active monitor class name.
 */
function monitor_class() {
	if ( \is_admin() ) {
		return __NAMESPACE__ . '\\Monitor_Admin';
	} else {
		return __NAMESPACE__ . '\\Monitor_Front_End';
	}
}
