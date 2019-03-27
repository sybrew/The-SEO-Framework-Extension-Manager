<?php
/**
 * Plugin Name: The SEO Framework - Extension Manager
 * Plugin URI: https://theseoframework.com/extension-manager/
 * Description: Add more powerful SEO features to The SEO Framework right from your WordPress Dashboard.
 * Version: 2.1.0-dev
 * Author: Sybre Waaijer
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 * Text Domain: the-seo-framework-extension-manager
 * Domain Path: /language
 */

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @NOTE This file MUST be written according to WordPress' minimum PHP requirements.
 *       Which is PHP 5.2.
 */

/**
 * The plugin version. Always 3 point.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_VERSION', '2.1.0' );

/**
 * The plugin's database version.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_DB_VERSION', '1600' );

/**
 * The plugin file, absolute unix path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE', __FILE__ );

/**
 * The plugin basename relative to the plugins directory.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_BASENAME', plugin_basename( TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE ) );

/**
 * The plugin's bootstrap folder location.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH', dirname( TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE ) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR );

/**
 * Checks whether to start plugin or test server first.
 * @since 1.5.0
 */
if ( get_option( 'tsfem_tested_environment_version' ) < TSF_EXTENSION_MANAGER_DB_VERSION ) {
	require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'envtest.php';

	//= This check is for the front-end. The back-end performs wp_die() when false to notify the admin.
	if ( get_option( 'tsfem_tested_environment_version' ) >= TSF_EXTENSION_MANAGER_DB_VERSION )
		tsf_extension_manager_boot();
} else {
	tsf_extension_manager_boot();
}

/**
 * Starts the plugin.
 *
 * @since 1.5.0
 * @access private
 */
function tsf_extension_manager_boot() {

	/**
	 * Defines environental constants.
	 * @since 1.5.0
	 */
	require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'define.php';

	/**
	 * Load plugin API file.
	 * @since 1.5.0
	 */
	require TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'api.php';

	/**
	 * Load internal functions file.
	 * @since 1.0.0
	 */
	require TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'internal.php';

	/**
	 * Prepare plugin upgrader before the plugin loads.
	 * @since 1.5.0
	 */
	if ( tsf_extension_manager_db_version() < TSF_EXTENSION_MANAGER_DB_VERSION ) {
		require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'upgrade.php';
	}

	if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'update.php';
	}

	/**
	 * Load plugin files.
	 * @since 1.0.0
	 */
	require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'load.php';
}
