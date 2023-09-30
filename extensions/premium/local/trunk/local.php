<?php
/**
 * @package TSF_Extension_Manager\Extension\Local
 */

namespace TSF_Extension_Manager\Extension\Local;

/**
 * Extension Name: Local
 * Extension URI: https://theseoframework.com/extensions/local/
 * Extension Description: The Local extension lets you set up important local business information for search engines to consume.
 * Extension Version: 1.2.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 * Extension Menu Slug: theseoframework-local
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
\define( 'TSFEM_E_LOCAL_VERSION', '1.2.0' );

/**
 * The extension database version.
 *
 * @since 1.1.2
 */
\define( 'TSFEM_E_LOCAL_DB_VERSION', '1100' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_LOCAL_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_LOCAL_DIR_URL', \TSF_Extension_Manager\extension_dir_url( \TSFEM_E_LOCAL_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_LOCAL_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( \TSFEM_E_LOCAL_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_LOCAL_PATH_CLASS', \TSFEM_E_LOCAL_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'classes' . \DIRECTORY_SEPARATOR );

/**
 * The plugin trait map absolute path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_LOCAL_PATH_TRAIT', \TSFEM_E_LOCAL_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'traits' . \DIRECTORY_SEPARATOR );

/**
 * Verify integrity and sets up autoloader.
 *
 * @since 1.0.0
 */
if ( ! \tsfem()->_init_early_extension_autoloader( \TSFEM_E_LOCAL_PATH_CLASS, 'Local', $_instance, $bits ) )
	return;

if ( \TSFEM_E_LOCAL_DB_VERSION > \tsf_extension_manager_db_version( 'local' ) ) {
	require \TSFEM_E_LOCAL_DIR_PATH . 'upgrade.php';
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_local_init', 11 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _local_init() {

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

/**
 * Requires trait files once.
 *
 * @since 1.0.0
 * @uses \TSFEM_E_LOCAL_PATH_TRAIT
 * @access private
 *
 * @param string $file Trait file name.
 * @return bool True if loaded, false otherwise.
 */
function _load_trait( $file ) {

	static $loaded = [];

	if ( isset( $loaded[ $file ] ) )
		return $loaded[ $file ];

	return $loaded[ $file ] = (bool) require \TSFEM_E_LOCAL_PATH_TRAIT . $file . '.trait.php';
}
