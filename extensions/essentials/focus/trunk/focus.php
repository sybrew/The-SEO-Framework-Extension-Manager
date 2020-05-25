<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus
 */

namespace TSF_Extension_Manager\Extension\Focus;

/**
 * Extension Name: Focus
 * Extension URI: https://theseoframework.com/extensions/focus/
 * Extension Description: The Focus extension guides you through the process of writing targeted content that ranks with focus keywords, and for Premium users also their inflections and synonyms.
 * Extension Version: 1.4.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

defined( 'ABSPATH' ) or die;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
define( 'TSFEM_E_FOCUS_VERSION', '1.4.0' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.0.0
 */
define( 'TSFEM_E_FOCUS_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 *
 * @since 1.0.0
 */
define( 'TSFEM_E_FOCUS_DIR_URL', \TSF_Extension_Manager\extension_dir_url( TSFEM_E_FOCUS_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.0.0
 */
define( 'TSFEM_E_FOCUS_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( TSFEM_E_FOCUS_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.0.0
 */
define( 'TSFEM_E_FOCUS_PATH_CLASS', TSFEM_E_FOCUS_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * Verify integrity and sets up autoloader.
 *
 * @since 1.0.0
 */
if ( false === \tsf_extension_manager()->_init_early_extension_autoloader( TSFEM_E_FOCUS_PATH_CLASS, 'Focus', $_instance, $bits ) )
	return;

\add_action( 'admin_init', __NAMESPACE__ . '\\_focus_init', 10 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 * @action 'admin_init'
 * @priority 10
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _focus_init() {

	static $loaded;

	if ( isset( $loaded ) )
		return $loaded;

	if ( \is_admin() ) {
		new Admin;
		$loaded = true;
	}

	return $loaded = (bool) $loaded;
}

/**
 * Returns the active base class.
 *
 * @since 1.0.0
 *
 * @return string The active class name.
 */
function get_active_class() {
	if ( \is_admin() ) {
		return __NAMESPACE__ . '\\Admin';
	}
	return '';
}
