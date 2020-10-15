<?php
/**
 * @package TSF_Extension_Manager\Extension\Local
 */

namespace TSF_Extension_Manager\Extension\Local;

\defined( 'TSFEM_E_LOCAL_DB_VERSION' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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

\TSF_Extension_Manager\load_upgrader();

\add_action( 'tsfem_prepare_admin_upgrade', __NAMESPACE__ . '\\_do_admin_upgrade', 0, 1 );
/**
 * Upgrades the Local database on the admin screens.
 *
 * Does an iteration of upgrades in order of version number.
 * Each called function will upgrade the plugin's database version by its iteration.
 *
 * @since 1.5.0
 * @param \TSF_Extension_Manager\Upgrader $upgrader The \TSF_Extension_Manager\Upgrader instance.
 */
function _do_admin_upgrade( \TSF_Extension_Manager\Upgrader $upgrader ) {

	$version = $upgrader->get_current_version( 'local' );

	if ( $version < '1100' ) {
		$upgrader->_register_upgrade( 'local', '1100', function( $version ) {
			// Defer: If it crashes, nothing happens.
			\add_action( 'shutdown', function() {
				\TSF_Extension_Manager\Extension\Local\Settings::get_instance()->_reprocess_all_stored_data();
			} );
			// Register it's done, always.
			return true;
		} );
	}

	$upgrader->_register_upgrade( 'local', TSFEM_E_LOCAL_DB_VERSION, '\\__return_true' );
}
