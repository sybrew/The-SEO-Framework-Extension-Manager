<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor
 */

namespace TSF_Extension_Manager\Extension\Monitor;

/**
 * Extension Name: Monitor
 * Extension URI: https://theseoframework.com/extensions/monitor/
 * Extension Description: The Monitor extension keeps track of your website's SEO optimizations and statistics.
 * Extension Version: 1.2.10
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 * Extension Menu Slug: theseoframework-monitor
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 *
 * @since 1.0.0
 * NOTE: The presence does NOT guarantee the extension is loaded!!!
 */
\define( 'TSFEM_E_MONITOR_VERSION', '1.2.10' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_MONITOR_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_MONITOR_DIR_URL', \TSF_Extension_Manager\extension_dir_url( \TSFEM_E_MONITOR_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_MONITOR_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( \TSFEM_E_MONITOR_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_MONITOR_PATH_CLASS', \TSFEM_E_MONITOR_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'classes' . \DIRECTORY_SEPARATOR );

/**
 * Verify integrity and set up autoloader.
 *
 * @since 1.0.0
 */
if ( ! \tsfem()->_init_early_extension_autoloader( \TSFEM_E_MONITOR_PATH_CLASS, 'Monitor', $_instance, $bits ) )
	return;

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\monitor_init', 11 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 *
 * @return bool True if class is loaded.
 */
function monitor_init() {

	static $loaded;

	// Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	if ( \is_admin() ) {
		new Admin;
	} else {
		// Statistical data. TODO.
		// new Front;
		return $loaded = false;
	}

	return $loaded = true;
}
