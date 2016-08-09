<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

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
 * Holds i18n data functions for class TSF_Extension_Manager\Extensions.
 *
 * @since 1.0.0
 * @access private
 */
trait Extensions_i18n {

	/**
	* Initializes class i18n.
	*
	* @since 1.0.0
	* @staticvar array $i18n
	*
	* @return array $i18n The internationalization data.
	*/
	private static function obtain_i18n() {

		static $i18n = null;

		if ( isset( $i18n ) )
			return $i18n;

		return $i18n = array(
			'free'		 => __( 'Free', 'the-seo-framework-extension-manager' ),
			'premium'	 => __( 'Premium', 'the-seo-framework-extension-manager' ),
			'download'	=> __( 'Download', 'the-seo-framework-extension-manager' ),
			'update'	  => __( 'Update', 'the-seo-framework-extension-manager' ),
			'activate'	=> __( 'Activate', 'the-seo-framework-extension-manager' ),
			'deactivate' => __( 'Deactivate', 'the-seo-framework-extension-manager' ),
			'delete'	  => __( 'Delete', 'the-seo-framework-extension-manager' ),
		);
	}

	/**
	* Returns i18n value from key.
	*
	* @since 1.0.0
	*
	* @return string The i18n data.
	*/
	private static function get_i18n( $key = '' ) {

		$i18n = static::obtain_i18n();

		return isset( $i18n[ $key ] ) ? $i18n[ $key ] : '';
	}
}

/**
 * Holds plugin data check functions for class TSF_Extension_Manager\Extensions.
 *
 * @since 1.0.0
 * @access private
 */
trait Extensions_Properties {

	/**
	* Determines whether the input plugin is premium.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $plugin The plugin to check.
	* @return bool Whether the plugin is premium.
	*/
	private static function is_plugin_premium( $plugin ) {
		return 'premium' === $plugin['type'];
	}

	/**
	* Determines whether the input plugin is downloaded and available.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $plugin The plugin to check.
	* @return bool Whether the plugin is downloaded and available.
	*/
	private static function is_plugin_downloaded( $plugin ) {
		return false;
	}

	/**
	* Determines whether the input plugin has been modified from its source.
	* It performs a simple ZIP package MD5 sum comparison check.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $plugin The plugin to check.
	* @return bool Whether the plugin is modified.
	*/
	private static function is_plugin_modified( $plugin ) {
		return false;
	}

	/**
	* Determines whether the input plugin is downloaded and requires an update.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $plugin The plugin to check.
	* @return bool Whether the plugin requires an update.
	*/
	private static function is_plugin_out_of_date( $plugin ) {
		return false;
	}

	/**
	* Determines whether the input plugin is premium.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $plugin The plugin to check.
	* @return bool Whether the plugin is premium.
	*/
	private static function is_plugin_active( $plugin ) {
		return false;
	}
}
