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
	* Initializes i18n.
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
			'free'       => __( 'Free', 'the-seo-framework-extension-manager' ),
			'premium'    => __( 'Premium', 'the-seo-framework-extension-manager' ),
			'activate'   => __( 'Activate', 'the-seo-framework-extension-manager' ),
			'deactivate' => __( 'Deactivate', 'the-seo-framework-extension-manager' ),
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
	 * Holds the class plugin list contents.
	 *
	 * @since 1.0.0
	 *
	 * @var array $plugins
	 */
	private static $plugins = array();

	/**
	 * Fetches all plugins remotely.
	 *
	 * @since 1.0.0
	 */
	private static function get_plugins() {
		//* EXAMPLE. @TODO FETCH LIST EXTERNALLY?
		//* @TODO SET LINKS BEHIND FIREWALL LINK THROUGH WC API [account validation] (even if free?).
		//* @TODO use transient cache (expire 1 hour + refresh cache button (with transient 5 minutes)?)
		return array(
			'test-plugin-free' => array(
				'slug' => 'test-plugin-free',
				'name' => 'Free Plugin',
				'network' => '0',
				'type' => 'free',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/0.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/1.jpg',
				'auth' => '0',
				'dl-type' => 'worg',
				'dl-url' => 'https://downloads.wordpress.org/plugin/the-seo-framework-title-fix.zip',
				'short_description' => 'This is a free testing plugin.',
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-3 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '80',
				'num_ratings' => '30',
				'requires' => '4.5.2',
				'active_installs' => '0',
			),
			'test-plugin-free2' => array(
				'slug' => 'test-plugin-free2',
				'name' => 'Free Plugin 2',
				'network' => '0',
				'type' => 'free',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/2.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/3.jpg',
				'auth' => '0',
				'dl-type' => 'worg',
				'dl-url' => 'https://downloads.wordpress.org/plugin/the-seo-framework-title-fix.zip',
				'short_description' => 'This is the seconds free testing plugin.',
				'version' => '1.0.0beta',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-5 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '100',
				'num_ratings' => '40',
				'requires' => '4.5.6',
				'active_installs' => '50',
			),
			'test-plugin-premium' => array(
				'slug' => 'test-plugin-premium',
				'name' => 'Premium Plugin',
				'network' => '0',
				'type' => 'premium',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/0.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/1.jpg',
				'auth' => '1',
				'dl-type' => 's3',
				'dl-url' => 'https://theseoframework.com/share/promimetypes.zip',
				'short_description' => 'This is a premium testing plugin.',
				'version' => '1-20160504',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-8 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '90',
				'num_ratings' => '50',
				'requires' => '4.5.0',
				'active_installs' => '500',
			),
			'test-plugin-premium2' => array(
				'slug' => 'test-plugin-premium2',
				'name' => 'Premium Plugin 2',
				'network' => '0',
				'type' => 'premium',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/2.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/3.jpg',
				'auth' => '1',
				'dl-type' => 's3',
				'dl-url' => 'https://theseoframework.com/share/pro-sites-extras.zip',
				'short_description' => 'This is the second premium testing plugin.',
				'version' => '2.4.4',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-50 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '60',
				'num_ratings' => '20',
				'requires' => '4.4.8',
				'active_installs' => '5000',
			),
			'test-network' => array(
				'slug' => 'test-network',
				'name' => 'Network Plugin',
				'network' => '1',
				'type' => 'free',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/0.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/1.jpg',
				'auth' => '0',
				'dl-type' => 's3',
				'dl-url' => 'https://theseoframework.com/share/custom-css.zip',
				'short_description' => 'This is a free newtwork testing plugin.',
				'version' => '1.0.5',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "+50 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '40',
				'num_ratings' => '80',
				'requires' => '4.3.8',
				'active_installs' => '4200',
			),
		);
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
	private static function is_plugin_premium( $plugin ) {
		return 'premium' === $plugin['type'];
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
