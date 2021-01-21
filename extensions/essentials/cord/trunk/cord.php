<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord
 */

namespace TSF_Extension_Manager\Extension\Cord;

/**
 * Extension Name: Cord
 * Extension URI: https://theseoframework.com/extensions/cord/
 * Extension Description: The Cord extension helps you connect your website to third-party services, like Google Analytics and Facebook pixel.
 * Extension Version: 1.0.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 * Extension Menu Slug: theseoframework-extension-settings
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Cord extension for The SEO Framework
 * Copyright (C) 2019-2021 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 *
 * NOTE: The presence does NOT guarantee the extension is loaded!!!
 */
\define( 'TSFEM_E_CORD_VERSION', '1.0.0' );

/**
 * The extension database version.
 *
 * @since ?.?.?
 * @ignore
 */
// phpcs:ignore
// \define( 'TSFEM_E_CORD_DB_VERSION', '1000' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_CORD_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_CORD_DIR_URL', \TSF_Extension_Manager\extension_dir_url( TSFEM_E_CORD_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_CORD_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( TSFEM_E_CORD_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_CORD_PATH_CLASS', TSFEM_E_CORD_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * Verify integrity and sets up autoloader.
 *
 * @since 1.0.0
 */
if ( false === \tsf_extension_manager()->_init_early_extension_autoloader( TSFEM_E_CORD_PATH_CLASS, 'Cord', $_instance, $bits ) )
	return;

// phpcs:disable -- @ignore
// if ( TSFEM_E_CORD_DB_VERSION > \tsf_extension_manager_db_version( 'cord' ) ) {
// 	require TSFEM_E_CORD_DIR_PATH . 'upgrade.php';
// }
// phpcs:enable

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_cord_init', 11 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 * @staticvar bool $loaded True when loaded.
 * @action 'plugins_loaded'
 * @priority 11
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _cord_init() {

	static $loaded;

	if ( isset( $loaded ) )
		return $loaded;

	if ( \is_admin() ) {
		new Admin;
	} else {
		new Front;
	}

	return $loaded = true;
}
