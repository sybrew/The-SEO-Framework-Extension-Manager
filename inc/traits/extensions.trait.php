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
 * Holds extension data check functions for class TSF_Extension_Manager\Extensions.
 *
 * @since 1.0.0
 * @access private
 */
trait Extensions_Properties {

	/**
	 * Holds the class extension list contents.
	 *
	 * @since 1.0.0
	 *
	 * @var array $extensions
	 */
	private static $extensions = array();

	/**
	 * Holds the instance extension slug to handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string $current_slug
	 */
	private static $current_slug = array();

	/**
	 * Fetches all extensions.
	 *
	 * @since 1.0.0
	 *
	 * @return array The extensions list.
	 */
	private static function get_extensions() {
		/**
		 * @access private
		 * Please note, if I catch any prominent tutorial on how to alter this list,
		 * for better or for worse: I'll add an external checksum validator with TLS
		 * and HMAC together with large blocks of code to be altered henceforth.
		 *
		 * Please stay on a moral highground and let everyone keep and have the
		 * best of the best. I (Sybre Waaijer) try my hardest to keep everything up
		 * to date! This is state of the art software, for everyone.
		 * The extensions are open source, so feel free to use and learn from that
		 * code :). Benefit from that personally; SEO is after all an active war
		 * between websites (...sell it to your clients?; nevermind: GPLv3).
		 *
		 * This plugin provides a portal for people who do not have the time to code
		 * everything (or don't have the know-how). The extensions are built with <3
		 * and have cost me literally thousands of hours to get where they are now.
		 *
		 * Coding is extremely difficult, as you might know (why else are you reading
		 * this?), so build something positive from those skills! Become an awesome
		 * part of this awesome WordPress.org community :).
		 */
		return array(
			'title-fix' => array(
				'slug' => 'title-fix',
				'title' => __( 'Title Fix', 'the-seo-framework-extension-manager' ),
				'network' => '0',
				'type' => 'free',
				'auth' => '0',
				'short_description' => __( 'The Title Fix extension makes sure your title output is as configured. Even if your theme is doing it wrong.', 'the-seo-framework-extension-manager' ),
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'party' => 'first',
				'last_updated' => '1454785229',
				'requires' => '3.9.0',
				'tested' => '4.6.0',
				'icons' => array(
					'default' => 'icon-100x100.jpg',
					'svg' => '',
					'1x' => 'icon-100x100.jpg',
					'2x' => 'icon-200x200.jpg',
				),
			),
		);
	}

	/**
	 * Returns checksums from the extensions list.
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *		'sha256' => string sha256 checksum key,
	 *		'sha1'   => string sha1 checksum key,
	 *		'md5'    => string md5 checksum key,
	 * }
	 */
	private static function get_external_extensions_checksum() {
		return array(
			'sha256' => 'e3182ba4c9c3f75a5d7b1206d41f99c0acdabce8a386e004079411ab5ec4ce03',
			'sha1'   => 'a878530568a960759c20f24c4d41dfb5d89af582',
			'md5'    => 'ab61f8c932a408f8b6880a08583dc13f',
		);
	}

	/**
	 * Returns the extension properties from slug.
	 * This can also be used to determine if the given extension exists in the extensions array.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $slug The extension slug or the extension.
	 * @return array The extension, if found.
	 */
	private static function get_extension( $slug ) {

		if ( is_array( $slug ) )
			$slug = key( $slug );

		if ( $slug ) {
			$extensions = static::$extensions;

			if ( isset( $extensions[ $slug ] ) ) {
				return $extensions[ $slug ];
			} else {
				the_seo_framework()->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'You must specify an existing extension slug.' );
				return array();
			}
		} else {
			the_seo_framework()->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'You must specify a slug.' );
			return array();
		}
	}

	/**
	 * Generates asset URL for extensions. If they're found.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @param string $file The file to generate URL from.
	 * @return string The extension asset URL.
	 */
	private static function get_extension_asset_url( $slug, $file ) {

		if ( empty( $slug ) || empty( $file ) )
			return '';

		$path = static::get_extension_relative_path( $slug );

		return $url = TSF_EXTENSION_MANAGER_DIR_URL . 'extensions/' . $path . 'assets/' . $file;
	}

	/**
	 * Generates extension directory path relative to the plugin directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @return string The extension relative path.
	 */
	private static function get_extension_relative_path( $slug ) {

		static $path = array();

		if ( isset( $path[ $slug ] ) )
			return $path[ $slug ];

		$extension = static::get_extension( $slug );

		if ( empty( $extension ) )
			return '';

		$network = static::is_extension_network( $extension );
		$premium = static::is_extension_premium( $extension );

		$path[ $slug ] = '';

		$path[ $slug ] .= $network ? 'network/' : '';
		$path[ $slug ] .= $premium ? 'premium/' : 'free/';
		$path[ $slug ] .= $slug . '/';

		return $path[ $slug ];
	}

	/**
	 * Returns extension header file location.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @return string The extension header file location.
	 */
	private static function get_extension_header_file_location( $slug ) {

		if ( $path = static::get_extension_relative_path( $slug ) )
			return TSF_EXTENSION_MANAGER_DIR_URL . $path . $slug . '.php';

		return '';
	}

	/**
	 * Determines whether the input extension is premium.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $extension The extension to check.
	 * @return bool Whether the extension is premium.
	 */
	private static function is_extension_premium( $extension ) {

		if ( is_string( $extension ) )
			$extension = static::get_extension( $extension );

		return 'premium' === $extension['type'];
	}

	/**
	 * Determines whether the input extension is a network extensions.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $extension The extension to check.
	 * @return bool Whether the extension is a network extension.
	 */
	private static function is_extension_network( $extension ) {

		if ( is_string( $extension ) )
			$extension = static::get_extension( $extension );

		return '1' === $extension['network'];
	}

	/**
	 * Determines whether the input extension is active.
	 *
	 * @since 1.0.0
	 * @TODO
	 *
	 * @param array $extension The extension to check.
	 * @return bool Whether the extension is active.
	 */
	private static function is_extension_active( $extension ) {
		return false;
	}
}


/**
 * Holds extensions activation functions for class TSF_Extension_Manager\Extensions.
 * This trait holds front-end PHP security risks when mistreated.
 *
 * @since 1.0.0
 * @uses trait TSF_Extension_Manager\Extensions_Properties
 * @access private
 */
trait Extensions_Actions {

	/**
	 * Determines extensions list checksum to be compared to against the API server.
	 *
	 * @since 1.0.0
	 * @return array : {
	 * 		'hash'    => string The generated hash,
	 * 		'type'    => string The hash type used,
	 *		'matches' => array The pre-calculated hash matches.
	 * }
	 */
	private static function get_extensions_checksum() {

		static $checksum = null;

		if ( isset( $checksum ) )
			return $checksum;

		$extensions = static::$extensions;

		//* Unset i18n.
		foreach ( $extensions as $slug => $values ) {
			unset( $extensions[ $slug ]['title'] );
			unset( $extensions[ $slug ]['short_description'] );
		}

		$extensions = serialize( $extensions );
		$algos = hash_algos();

		if ( in_array( 'sha256', $algos, true ) ) {
			$hash = hash( 'sha256', $extensions );
			$type = 'sha256';
		} elseif ( in_array( 'sha1', $algos, true ) ) {
			$hash = hash( 'sha1', $extensions );
			$type = 'sha1';
		} else {
			$hash = hash( 'md5', $extensions );
			$type = 'md5';
		}

		return $checksum = array(
			'hash' => $hash,
			'type' => $type,
			'matches' => static::get_external_extensions_checksum(),
		);
	}

	/**
	 * Determines extensions list checksum to be compared to against the API server.
	 *
	 * @since 1.0.0
	 * @todo Use this if people are being hackish.
	 *
	 * @param string $slug The extension slug.
	 * @param string $instance The verification instance.
	 * @param array $bits The verification bits.
	 * @return array : {
	 * 		'hash' => string The generated hash,
	 * 		'type' => string The hash type used,
	 * }
	 */
	private static function get_extension_checksum() {

		static $checksum = null;

		if ( isset( $checksum ) )
			return $checksum;

		$slug = static::$current_slug;

		$file = static::get_extension_header_file_location( $slug );

		if ( in_array( 'sha256', hash_algos(), true ) ) {
			$hash = hash_file( 'sha256', $file );
			$type = 'sha256';
		} elseif ( in_array( 'sha1', hash_algos(), true ) ) {
			$hash = hash_file( 'sha1', $file );
			$type = 'sha1';
		} else {
			$hash = hash_file( 'md5', $file );
			$type = 'md5';
		}

		return $checksum = array(
			'hash' => $hash,
			'type' => $type,
		);
	}

	private static function get_active_extensions( $placeholder = array(), $instance, $bits ) {

		tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;

	}

	/**
	 * Validates extension activation.
	 *
	 * @since 1.0.0
	 *
	 * @return array : {
	 * 		'success' => bool Whether the activation can proceed.
	 *		'case'    => int The status key.
	 * }
	 */
	public static function validate_extension_activation() {

		self::verify_instance() or die;

		if ( 'activation' !== self::get_property( '_type' ) ) {
			self::reset();
			self::invoke_invalid_type( __METHOD__ );
		}

		$slug = static::$current_slug;
		$extension = static::get_extension( $slug );

		if ( empty( $extension ) )
			return array( 'success' => false, 'case' => 1 );

		if ( static::is_extension_premium( $extension ) ) {
			if ( self::is_premium_account() ) {
				return array( 'success' => true, 'case' => 2 );
			} else {
				return array( 'success' => false, 'case' => 3 );
			}
		} else {
			return array( 'success' => true, 'case' => 4 );
		}
	}
}
