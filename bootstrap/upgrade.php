<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
		case $version < 2730:
			$upgrader->_register_upgrade(
				'core',
				'2730',
				__NAMESPACE__ . '\\_upgrade_2730_register_troy_notice',
			);
			// no break, do moar upgrades;
		default:
			// TODO add "thank you for upgrading" notice?
			$upgrader->_register_upgrade( 'core', \TSF_EXTENSION_MANAGER_DB_VERSION, '__return_true' );
	}
}

/**
 * Registers the Troy Client migration notice.
 *
 * @since 2.7.3
 *
 * @param int $version The current database version.
 * @return bool True on success.
 */
function _upgrade_2730_register_troy_notice( $version ) {

	// Troy Client already active -- no notice needed.
	if ( \defined( 'Troy\Client\ABSPATH' ) )
		return true;

	// TSF v5.0+ required for the persistent notice API.
	if ( ! version_compare( \THE_SEO_FRAMEWORK_VERSION, '5.0.0', '>=' ) )
		return true;

	\The_SEO_Framework\Admin\Notice\Persistent::register_notice(
		\sprintf(
			/* translators: 1 = Extension Manager version, 2 = Link to KB article */
			\esc_html__(
				'Starting from Extension Manager %1$s, updates will be handled by Troy Client. This improves privacy (no domain tracking) and reliability (updates work even if Extension Manager is inactive). Troy Client will be installed automatically when you update. %2$s',
				'the-seo-framework-extension-manager'
			),
			'<strong>3.0.0</strong>',
			\sprintf(
				'<a href="%s" target=_blank rel="noreferrer noopener">%s</a>',
				'https://kb.theseoframework.com/kb/what-is-troy-client/',
				\esc_html__( 'Learn more', 'the-seo-framework-extension-manager' )
			),
		),
		'tsfem-troy-migration',
		[
			'type'   => 'info',
			'escape' => false,
		],
		[
			'excl_screens' => [ 'update', 'update-core', 'plugins', 'plugin-install' ],
			'capability'   => 'update_plugins',
			'count'        => 42,
		],
	);

	return true;
}
