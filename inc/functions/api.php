<?php
/**
 * @package TSF_Extension_Manager
 */

namespace {
	defined( 'TSF_EXTENSION_MANAGER_DIR_PATH' ) or die;
}

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
		 * Applies filters 'tsf_extension_manager_can_manage_options'
		 * @since 1.5.0
		 * @param bool $capability Current user's administrative capability.
		 */
		return isset( $cache )
		     ? $cache
		     : $cache = \apply_filters( 'tsf_extension_manager_can_manage_options', \current_user_can( 'manage_options' ) );
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

		return $loaded[ $file ] = (bool) require( TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT . $_file . '.trait.php' );
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

		return $loaded[ $version ] = (bool) require( TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT . 'wp-' . $version . '.php' );
	}
}
