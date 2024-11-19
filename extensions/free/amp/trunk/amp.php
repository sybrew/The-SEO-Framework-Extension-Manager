<?php
/**
 * @package TSF_Extension_Manager\Extension\AMP
 */

namespace TSF_Extension_Manager\Extension\AMP;

/**
 * Extension Name: AMP
 * Extension URI: https://theseoframework.com/extensions/amp/
 * Extension Description: The AMP extension binds The SEO Framework to the [AMP plugin](https://wordpress.org/plugins/amp/) for [AMP](https://www.ampproject.org/) supported articles and pages.
 * Extension Version: 1.3.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * AMP extension for The SEO Framework
 * Copyright (C) 2017 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * The AMP extension version.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_AMP_VERSION', '1.3.0' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.3.0
 */
\define( 'TSFEM_E_AMP_BASE_FILE', __FILE__ );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.3.0
 */
\define( 'TSFEM_E_AMP_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( \TSFEM_E_AMP_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.3.0
 */
\define( 'TSFEM_E_AMP_PATH_CLASS', \TSFEM_E_AMP_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'classes' . \DIRECTORY_SEPARATOR );

/**
 * Verify integrity and sets up autoloader.
 *
 * @since 1.3.0
 */
if ( ! \tsfem()->_init_early_extension_autoloader( \TSFEM_E_AMP_PATH_CLASS, 'AMP', $_instance, $bits ) )
	return;

\add_action( 'wp', __NAMESPACE__ . '\\_amp_init', 11 );
/**
 * Initializes the extension. Runs after AMP plugin action 'amp_init'.
 *
 * @since 1.0.0
 * @since 1.1.0 Now uses WP-AMP's new API.
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _amp_init() {

	if ( \is_admin() ) {
		// Bail on admin. No admin dashboard yet.
		return false;
	} else {
		$is_amp = false;

		if ( \function_exists( 'is_amp_endpoint' ) ) {
			$is_amp = \is_amp_endpoint();
		} elseif ( \defined( 'AMP_QUERY_VAR' ) ) {
			$is_amp = \get_query_var( \AMP_QUERY_VAR, false ) !== false;
		}

		if ( $is_amp ) {
			new Front;
			return true;
		}
	}
	return false;
}
