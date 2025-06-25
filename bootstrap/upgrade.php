<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Note to self: We use "critical" here because it runs before extensions are loaded.
// We should not attach to other hooks because we must upgrade in sequence.
// We shouldn't have called it "critical", but "plugin" vs "extension" instead. TODO Fixme?
// We should also abandon the "admin" vs "always" upgrader? Not loading some parts of the admin might make some migrations difficult, however.

// Hook into upgrader first.
\add_action( 'tsfem_prepare_critical_upgrade', __NAMESPACE__ . '\\_do_critical_core_upgrade', 0, 1 );

// The load upgrader.
\TSF_Extension_Manager\load_upgrader();

/**
 * Upgrades the core plugin database before the plugin runs.
 *
 * Does an iteration of upgrades in order of version number.
 * Each called function will upgrade the plugin's database version by its iteration.
 *
 * @since 1.5.0
 * @param Upgrader $upgrader The TSF_Extension_Manager\Upgrader instance.
 */
function _do_critical_core_upgrade( $upgrader ) {

	// phpcs:disable -- Example with unused variable.
	$version = $upgrader->get_current_version( 'core' );

	switch ( true ) {
		case $version < 2500:
			$upgrader->_register_upgrade(
				'core',
				'2500',
				function ( $version ) {
					// Declare success when the option doesn't exist or is successfully deleted.
					return ! \get_option( 'tsfem_tested_environment_version' ) || \delete_option( 'tsfem_tested_environment_version' );
				}
			);
			// no break, do moar upgrades;
		case $version < 2700:
			$upgrader->_register_upgrade(
				'core',
				'2700',
				function ( $version ) {
					// Declare success when the option doesn't exist or is successfully deleted.
					return \update_option(
						\TSF_EXTENSION_MANAGER_ACTIVE_EXTENSIONS_OPTIONS,
						\get_option( \TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] )['active_extensions'] ?? [],
					);
				}
			);
			// no break, do moar upgrades;
		default:
			// TODO add "thank you for upgrading" notice?
			$upgrader->_register_upgrade( 'core', \TSF_EXTENSION_MANAGER_DB_VERSION, '__return_true' );
	}
}
