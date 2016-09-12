<?php
/**
 * @package TSF_Extension_Manager_Extension
 */

/**
 * Extension Name: Google Analytics
 * Extension URI: https://premium.theseoframework.com/extensions/incognito/
 * Description: The Google Analytics extension allows you to set up and interact with Google Analytics right from your dashboard.
 * Version: 1.0.0
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv3
 */

/**
 * @package TSF_Extension_Manager
 */
namespace {

	defined( 'ABSPATH' ) or die;

	if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
		return;
}

/**
 * Google Analytics extension for The SEO Framework
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
 * @package TSF_Extension_Manager
 */
namespace TSF_Extension_Manager {

	/**
	 * The extension map URL. Used for calling browser files.
	 * @since 1.0.0
	 */
	define( 'TSFEM_E_GOOGLE_ANALYTICS_DIR_URL', extension_dir_url( __FILE__ ) );

	/**
	 * The extension file relative to the plugins dir.
	 * @since 1.0.0
	 */
	define( 'TSFEM_E_GOOGLE_ANALYTICS_DIR_PATH', extension_dir_path( __FILE__ ) );

	/**
	 * The plugin class map absolute path.
	 * @since 1.0.0
	 */
	define( 'TSFEM_E_GOOGLE_ANALYTICS_PATH_CLASS', TSFEM_E_GOOGLE_ANALYTICS_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

	/**
	 * The extension file, absolute unix path.
	 * @since 1.0.0
	 */
	define( 'TSFEM_E_GOOGLE_ANALYTICS_BASE_FILE', __FILE__ );
}

/**
 * @package TSF_Extension_Manager_Extension
 */
namespace TSF_Extension_Manager_Extension {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\google_analytics_init', 11 );
	/**
	 * Initialize the extension.
	 *
	 * @since 1.0.0
	 * @action 'plugins_loaded'
	 * @priority 11
	 *
	 * @return bool True if class is loaded.
	 */
	function google_analytics_init() {

		static $loaded = null;

		//* Don't init the class twice.
		if ( isset( $loaded ) )
			return $loaded;

		tsf_extension_manager()->register_premium_extension_autoload_path( TSFEM_E_GOOGLE_ANALYTICS_PATH_CLASS, 'Google_Analytics' );

		if ( is_admin() ) {
			new Google_Analytics_Admin();
		} else {
			new Google_Analytics();
		}

		return $loaded = true;
	}
}
