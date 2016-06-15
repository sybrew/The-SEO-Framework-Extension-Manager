<?php
/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Loads the class from cache.
 * This is the recommended way of calling the class, if needed.
 * Call this after action 'plugins_loaded' priority 6 otherwise it will kill the plugin.
 *
 * @since 1.0.0
 *
 * @return null|object The plugin class object.
 */
function tsf_extension_manager() {
	return init_tsf_extension_manager();
}

add_action( 'plugins_loaded', 'init_tsf_extension_manager', 6 );
/**
 * Loads TSF_Extension_Manager_Load class when in admin.
 *
 * @action plugins_loaded
 * @priority 6 Use anything above 6, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @since 1.0.0
 * @staticvar object $tsf_extension_manager
 * @access private
 *
 * @return null|object The SEO Framework class.
 */
function init_tsf_extension_manager() {

	//* Cache the class. Do not run everything more than once.
	static $tsf_extension_manager = null;

	if ( $tsf_extension_manager )
		return $tsf_extension_manager;

	if ( can_load_tsf_extension_manager() ) {
		require_tsf_extension_manager_files();
		$tsf_extension_manager = new TSF_Extension_Manager_Load();
	}

	return $tsf_extension_manager;
}

/**
 * Determines whether we can load the the plugin.
 * Call this after action 'plugins_loaded' priority 5 or it will kill the plugin.
 *
 * Applies filters 'tsf_extension_manager_network_enabled' : boolean
 * Applies filters 'tsf_extension_manager_enabled' : boolean
 *
 * @since 1.0.0
 * @staticvar bool $can_load
 * @uses the_seo_framework_version() Returns null if inactive.
 *
 * @return bool Whether the plugin can load. Always returns false on the front-end.
 */
function can_load_tsf_extension_manager() {

	static $can_load = null;

	if ( isset( $can_load ) )
		return $can_load;

	if ( is_admin() && function_exists( 'the_seo_framework_version' ) )
		if ( version_compare( the_seo_framework_version(), '2.7', '>=' ) )
			return $can_load = (bool) apply_filters( 'tsf_extension_manager_enabled', true );

	return $can_load = false;
}

/**
 * Loads all class files. To be used when requiring access to all or any of the
 * plugin classes.
 *
 * @since 1.0.0
 * @staticvar bool $loaded
 *
 * @return bool True when loaded.
 */
function require_tsf_extension_manager_files() {

	static $loaded = null;

	if ( isset( $loaded ) )
		return $loaded;

	require_once( TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'core.class.php' );
	require_once( TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'activation.class.php' );
	require_once( TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'adminpages.class.php' );

	require_once( TSF_EXTENSION_MANAGER_DIR_PATH . 'load.class.php' );

	return $loaded = true;
}
