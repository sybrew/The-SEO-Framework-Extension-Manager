<?php
/**
 * @package TSF_Extension_Manager
 */

namespace {
	defined( 'TSF_EXTENSION_MANAGER_DIR_PATH' ) or die;
}

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

namespace {
	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'init' priority 0 otherwise it will kill the plugin,
	 * or even other plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return null|object The plugin class object.
	 */
	function tsf_extension_manager() {
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
		$versions = \get_option( 'tsfem_current_db_versions', [] );
		return ! empty( $versions[ $member ] ) ? $versions[ $member ] : '0';
	}
}

namespace TSF_Extension_Manager {
	/**
	 * Returns the minimum role required to adjust and access settings.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added filter.
	 * @staticvar bool $cache
	 *
	 * @return string The minimum required capability for extension installation.
	 */
	function can_do_settings() {

		static $cache = null;

		/**
		 * Allows for conditionally adjusting the "can do settings" role.
		 *
		 * @NOTE:
		 * We don't recommend conditioning this lower, as some functionality may appear broken.
		 * This should be set to a role that also implies 'manage_options'.
		 * @NOTE WANRING:
		 * Conditioning this higher might impose 'security' risks, where admins, still with
		 * 'manage_options' capabilities, may perform certain actions, regardless of this state.
		 *
		 * We could alleviate these issues by dynamically fetching roles when this is true,
		 * but that'll create unpredictable behavior, which we won't allow.
		 *
		 * @NOTE: Don't try to act smart by always returning true. This function is used
		 * where can_do_manager_settings() isn't.
		 *
		 * @since 1.5.0
		 * @param bool $can_do_settings Whether the user can access and modify settings.
		 */
		return isset( $cache )
			? $cache
			: $cache = \apply_filters( 'tsf_extension_manager_can_manage_options', \current_user_can( 'manage_options' ) );
	}

	/**
	 * Returns the minimum role required to adjust and access the main settings.
	 *
	 * @since 2.0.0
	 * @uses \TSF_Extension_Manager\can_do_settings() This must pass, too.
	 *
	 * @return bool
	 */
	function can_do_manager_settings() {
		return can_do_settings() && \current_user_can( TSF_EXTENSION_MANAGER_MAIN_ADMIN_ROLE );
	}

	/**
	 * Requires trait files once.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Now returns state in boolean rather than void.
	 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT
	 * @access private
	 * @staticvar array $loaded
	 *
	 * @param string $file Where the trait is for. Must be lowercase.
	 * @return bool True if loaded, false otherwise.
	 */
	function _load_trait( $file ) {

		static $loaded = [];

		if ( isset( $loaded[ $file ] ) )
			return $loaded[ $file ];

		$_file = str_replace( '/', DIRECTORY_SEPARATOR, $file );

		return $loaded[ $file ] = (bool) require TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT . $_file . '.trait.php';
	}

	/**
	 * Requires WordPress compat files once.
	 *
	 * @since 1.0.0
	 * @uses TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT
	 * @access private
	 * @staticvar array $loaded
	 *
	 * @param string $version The version where the WordPress compatibility is required for.
	 * @return bool True if loaded; false otherwise.
	 */
	function _load_wp_compat( $version = '' ) {

		static $loaded = [];

		if ( isset( $loaded[ $version ] ) )
			return $loaded[ $version ];

		if ( empty( $version ) || 3 !== strlen( $version ) ) {
			\the_seo_framework()->_doing_it_wrong( __FUNCTION__, 'You must tell the two-point required WordPress version.' );
			return $loaded[ $version ] = false;
		}

		/**
		 * @global string $wp_version
		 */
		if ( version_compare( $GLOBALS['wp_version'], $version, '>=' ) )
			return $loaded[ $version ] = true;

		return $loaded[ $version ] = (bool) require TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT . 'wp-' . $version . '.php';
	}

	/**
	 * Adds and returns-to the bootstrap timer.
	 *
	 * @since 2.3.1
	 * @access private
	 * @staticvar $time The estimated total time for bootstrapping.
	 *
	 * @param int $add The time to add.
	 * @return int The accumulated time, roughly.
	 */
	function _bootstrap_timer( $add = 0 ) {
		static $time  = 0;
		return $time += $add;
	}
}
