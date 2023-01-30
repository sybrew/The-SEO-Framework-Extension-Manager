<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport
 */

namespace TSF_Extension_Manager\Extension\Transport;

/**
 * Extension Name: Transport *&beta;eta*
 * Extension URI: https://theseoframework.com/extensions/transport/
 * Extension Description: The Transport extension migrates SEO metadata from other plugins to The SEO Framework.
 * Extension Version: 1.1.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 * Extension Menu Slug: theseoframework-transport
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Transport extension for The SEO Framework
 * copyright (C) 2022-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
\define( 'TSFEM_E_TRANSPORT_VERSION', '1.1.0' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_TRANSPORT_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_TRANSPORT_DIR_URL', \TSF_Extension_Manager\extension_dir_url( TSFEM_E_TRANSPORT_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_TRANSPORT_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( TSFEM_E_TRANSPORT_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_TRANSPORT_PATH_CLASS', TSFEM_E_TRANSPORT_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * The logserver store option index.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_TRANSPORT_LOGSERVER_STORE', 'tsfem_e_transport_logserver_store' );

/**
 * Verify integrity and set up autoloader.
 *
 * @since 1.0.0
 */
if ( ! \tsfem()->_init_early_extension_autoloader( TSFEM_E_TRANSPORT_PATH_CLASS, 'Transport', $_instance, $bits ) )
	return;

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\transport_init', 11 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 *
 * @return bool True if class is loaded.
 */
function transport_init() {

	static $loaded;

	// Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	if ( \is_admin() ) {
		new Admin;
		return $loaded = true;
	}

	return $loaded = false;
}
