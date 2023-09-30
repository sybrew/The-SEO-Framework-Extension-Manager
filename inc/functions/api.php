<?php
/**
 * @package TSF_Extension_Manager
 */

namespace {
	defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;
}

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

namespace {
	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'init' priority 0 otherwise it will kill the plugin,
	 * or even other plugins.
	 *
	 * @since 1.0.0
	 * @see `tsfem()` alias.
	 * @api
	 *
	 * @return null|object The plugin class object.
	 */
	function tsf_extension_manager() {
		return \TSF_Extension_Manager\_init_tsf_extension_manager();
	}

	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'init' priority 0 otherwise it will kill the plugin,
	 * or even other plugins.
	 *
	 * @since 2.6.0
	 * @see `tsfem()` alias.
	 * @api
	 *
	 * @return null|object The plugin class object.
	 */
	function tsfem() {
		return \TSF_Extension_Manager\_init_tsf_extension_manager();
	}

	/**
	 * Returns the database version of TSFEM members.
	 * The 'core' member represents the main plugin.
	 *
	 * @since 1.5.0
	 * @TODO Add caching when calls (throughout the plugin) > 4.
	 *
	 * @param string $member The member to check. Defaults to 'core'.
	 * @return string The database version. '0' if version isn't found.
	 */
	function tsf_extension_manager_db_version( $member = 'core' ) {
		return ( get_option( 'tsfem_current_db_versions', [] )[ $member ] ?? null ) ?: '0';
	}
}

namespace TSF_Extension_Manager {
	/**
	 * Returns true when the user can control extension options.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added filter.
	 * @since 2.4.0 1: Removed filter.
	 *              2: Now uses constant `TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE`
	 *
	 * @return bool The minimum required capability for extension management.
	 */
	function can_do_extension_settings() {
		return \current_user_can( \TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE );
	}

	/**
	 * Returns true when the user can control manager (main) settings.
	 * - Extensions overview.
	 * - Extension (de)activation.
	 * - API connection management.
	 * - Feed activation.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	function can_do_manager_settings() {
		return \current_user_can( \TSF_EXTENSION_MANAGER_MAIN_ADMIN_ROLE );
	}

	/**
	 * Returns the minimum role required to adjust and access general extension settings.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added filter.
	 * @since 2.4.0 Deprecated without warning.
	 * @deprecated
	 *
	 * @return string The minimum required capability for extension installation.
	 */
	function can_do_settings() {
		return can_do_manager_settings();
	}

	/**
	 * Requires trait files once.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Now returns state in boolean rather than void.
	 * @access private
	 *
	 * @param string $file Where the trait is for. Must be lowercase.
	 * @return bool True if loaded, false otherwise.
	 */
	function _load_trait( $file ) {

		static $loaded = [];

		if ( isset( $loaded[ $file ] ) )
			return $loaded[ $file ];

		$_file = str_replace( '/', \DIRECTORY_SEPARATOR, $file );

		return $loaded[ $file ] = (bool) require \TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT . $_file . '.trait.php';
	}

	/**
	 * Requires WordPress compat files once.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $version The version where the WordPress compatibility is required for.
	 * @return bool True if loaded; false otherwise.
	 */
	function _load_wp_compat( $version = '' ) {

		static $loaded = [];

		if ( isset( $loaded[ $version ] ) )
			return $loaded[ $version ];

		if ( ! $version || 3 !== \strlen( $version ) ) {
			\tsf()->_doing_it_wrong( __FUNCTION__, 'You must tell the two-point required WordPress version.' );
			return $loaded[ $version ] = false;
		}

		/**
		 * @global string $wp_version
		 */
		if ( version_compare( $GLOBALS['wp_version'], $version, '>=' ) )
			return $loaded[ $version ] = true;

		return $loaded[ $version ] = (bool) require \TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT . 'wp-' . $version . '.php';
	}

	/**
	 * Adds and returns-to the bootstrap timer.
	 *
	 * @since 2.3.1
	 * @access private
	 *
	 * @param int $add The time to add.
	 * @return int The accumulated time, roughly.
	 */
	function _bootstrap_timer( $add = 0 ) {
		static $time  = 0;
		return $time += $add;
	}
}
