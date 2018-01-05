<?php
/**
 * Plugin Name: The SEO Framework - Extension Manager
 * Plugin URI: https://wordpress.org/plugins/the-seo-framework-extension-manager/
 * Description: Add more powerful SEO features to The SEO Framework right from your WordPress Dashboard.
 * Version: 1.5.0-dev2018.01.05.1
 * Author: Sybre Waaijer
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 * Text Domain: the-seo-framework-extension-manager
 * Domain Path: /language
 */

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * The plugin version. Always 3 point.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_VERSION', '1.5.0-dev' );

/**
 * The plugin's database version.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_DB_VERSION', '1500' );

/**
 * The plugin basename relative to the plugins directory.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_BASENAME', \plugin_basename( __FILE__ ) );

/**
 * The plugin's bootstrap folder location.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR );

/**
 * Checks whether to start plugin or test server.
 *
 * @since 1.5.0
 */
if ( get_option( 'tsfem_tested_upgrade_version' ) >= TSF_EXTENSION_MANAGER_DB_VERSION ) {
	tsf_extension_manager_boot();
} else {
	require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'test-environment.php';

	if ( get_option( 'tsfem_tested_upgrade_version' ) >= TSF_EXTENSION_MANAGER_DB_VERSION )
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
	$__file = __FILE__;
	require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'define.php';
	unset( $__file );

	/**
	 * Load plugin API file.
	 * @since 1.5.0
	 * @uses TSF_EXTENSION_MANAGER_DIR_PATH
	 */
	require TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'api.php';

	/**
	 * Load functions file.
	 * @since 1.0.0
	 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION
	 */
	require TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'functions.php';

	/**
	 * Load plugin files.
	 * @since 1.0.0
	 * @uses TSF_EXTENSION_MANAGER_DIR_PATH
	 */
	require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'load.php';
}
