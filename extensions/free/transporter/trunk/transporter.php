<?php
/**
 * @package TSF_Extension_Manager\Extension\Transporter
 */
namespace TSF_Extension_Manager\Extension;

/**
 * Extension Name: Transporter
 * Extension URI: https://premium.theseoframework.com/extensions/transporter/
 * Extension Description: The Transporter extensions allows you to export and import your SEO settings from site to site.
 * Extension Version: 1.0.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 * Extension Menu Slug: theseoframework-transporter
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Transporter extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
define( 'TSFEM_E_TRANSPORTER_VERSION', '1.0.0' );

/**
 * The extension file, absolute unix path.
 * @since 1.0.0
 */
define( 'TSFEM_E_TRANSPORTER_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 * @since 1.0.0
 */
define( 'TSFEM_E_TRANSPORTER_DIR_URL', \TSF_Extension_Manager\extension_dir_url( TSFEM_E_TRANSPORTER_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 * @since 1.0.0
 */
define( 'TSFEM_E_TRANSPORTER_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( TSFEM_E_TRANSPORTER_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 * @since 1.0.0
 */
define( 'TSFEM_E_TRANSPORTER_PATH_CLASS', TSFEM_E_TRANSPORTER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\transporter_init', 11 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 * @staticvar bool $loaded
 * @action 'plugins_loaded'
 * @priority 11
 *
 * @return bool True if class is loaded.
 */
function transporter_init() {

	static $loaded;

	//* Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	\tsf_extension_manager()->_register_free_extension_autoload_path( TSFEM_E_TRANSPORTER_PATH_CLASS, 'Transporter' );

	if ( \is_admin() ) {
		new \TSF_Extension_Manager\Extension\Transporter_Admin;
	} else {
		return $loaded = false;
	}

	return $loaded = true;
}

/**
 * Returns the active transporter base class.
 *
 * @since 1.0.0
 *
 * @return string The active transporter class name.
 */
function transporter_class() {
	if ( \is_admin() ) {
		return __NAMESPACE__ . '\\Transporter_Admin';
	} else {
		return '';
	}
}
