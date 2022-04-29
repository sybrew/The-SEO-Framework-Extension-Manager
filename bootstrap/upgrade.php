<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_DB_VERSION' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

\TSF_Extension_Manager\load_upgrader();

\add_action( 'tsfem_prepare_critical_upgrade', __NAMESPACE__ . '\\_do_critical_core_upgrade', 0, 1 );
/**
 * Upgrades the core plugin database before the plugin runs.
 *
 * Does an iteration of upgrades in order of version number.
 * Each called function will upgrade the plugin's database version by its iteration.
 *
 * @since 1.5.0
 * @param Upgrader $upgrader The TSF_Extension_Manager\Upgrader instance.
 */
function _do_critical_core_upgrade( Upgrader $upgrader ) {

	// phpcs:disable -- Example with unused variable.
	$version = $upgrader->get_current_version( 'core' );

	// Example:
	// if ( $version < 1500 ) {
	// 	$upgrader->_register_upgrade( 'core', '1500', function( $version ) { return (bool) $success; } );
	// }

	// phpcs:enable

	$upgrader->_register_upgrade( 'core', TSF_EXTENSION_MANAGER_DB_VERSION, '\\__return_true' );
}
