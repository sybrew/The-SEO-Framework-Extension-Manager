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
				'checksum' => '',
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
				'checksum' => '',
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
				'checksum' => '',
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
				'checksum' => '',
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
				'checksum' => '',
			),
		);
	}

	/**
	* Determines whether the input extension is premium.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $extension The extension to check.
	* @return bool Whether the extension is premium.
	*/
	private static function is_plugin_premium( $extension ) {
		return 'premium' === $extension['type'];
	}

	/**
	* Determines whether the input extension has been modified from its source.
	* It performs a simple ZIP package MD5 sum comparison check.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $extension The extension to check.
	* @return bool Whether the extension is modified.
	*/
	private static function is_plugin_modified( $extension ) {
		return false;
	}

	/**
	* Determines whether the input extension is premium.
	*
	* @since 1.0.0
	* @TODO
	*
	* @param array $extension The extension to check.
	* @return bool Whether the extension is premium.
	*/
	private static function is_plugin_active( $extension ) {
		return false;
	}
}


/**
 * Holds extensions activation functions for class TSF_Extension_Manager\Extensions.
 *
 * @since 1.0.0
 * @uses trait TSF_Extension_Manager\Extensions_Properties
 * @access private
 */
trait Extensions_Actions {

	private static function do_plugin_checksum( $plugin, $instance, $bits ) {

		tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;

	}

	private static function do_plugin_checksum_verification( $plugin, $instance, $bits ) {

		tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;

	}

	private static function get_active_plugins( $placeholder = array(), $instance, $bits ) {

		tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;

	}

	private static function do_plugin_activation( $plugin, $instance, $bits ) {

		tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;

		$nonce_action = tsf_extension_manager()->get_nonce_action_field( self::$request_name['activate-ext'] );
		$nonce = wp_nonce_field( self::$nonce_action['activate-ext'], self::$nonce_name, true, false );

	}

	private static function do_plugin_deactivation( $plugin, $instance, $bits ) {

		tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;

		$nonce_action = tsf_extension_manager()->get_nonce_action_field( self::$request_name['deactivate-ext'] );
		$nonce = wp_nonce_field( self::$nonce_action['deactivate-ext'], self::$nonce_name, true, false );

	}
}
