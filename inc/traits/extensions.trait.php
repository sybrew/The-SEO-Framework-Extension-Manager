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
		 * between websites (...sell it as a service to your clients?).
		 *
		 * This plugin provides a portal for people who do not have the time to code
		 * everything (or don't have the know-how). The extensions are built with <3
		 * and have cost me literally thousands of hours to get where they are now.
		 *
		 * Coding is extremely difficult, as you might know (why else are you reading
		 * this?), so build something positive from those skills! Become an awesome
		 * part of this awesome WordPress.org community :). Or build your own :D.
		 */
		return array(
			'title-fix' => array(
				'slug' => 'title-fix',
				'network' => '0',
				'type' => 'free',
				'area' => 'general',
				'version' => '1.0.2',
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
			'sha256' => '0c78c432da447ec7646f3836b87da33f8e46c252dc75851fdd94c6ea9959ca79',
			'sha1'   => '093d53e226ffb48b4cdf0d8489549e9468b8b082',
			'md5'    => 'f7f5aced9700d8b1c4d29811cedc6a59',
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
		$path = str_replace( DIRECTORY_SEPARATOR, '/', $path );

		return $url = TSF_EXTENSION_MANAGER_DIR_URL . $path . 'assets/' . $file;
	}

	/**
	 * Generates trunk path for extensions. If they're found.
	 *
	 * @since 1.0.0
	 * @staticvar array $cache The trunk paths cache.
	 *
	 * @param string $slug The extension slug.
	 * @return string The extension trunk file path.
	 */
	private static function get_extension_trunk_path( $slug ) {

		if ( empty( $slug ) )
			return '';

		$path = static::get_extension_relative_path( $slug );

		return $path = TSF_EXTENSION_MANAGER_DIR_PATH . $path . 'trunk' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Generates extension directory path relative to the plugin home directory.
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

		$path[ $slug ] .= 'extensions/';
		$path[ $slug ] .= $network ? 'network/' : '';
		$path[ $slug ] .= $premium ? 'premium/' : 'free/';
		$path[ $slug ] .= $slug . '/';

		$path[ $slug ] = str_replace( '/', DIRECTORY_SEPARATOR, $path[ $slug ] );

		return $path[ $slug ];
	}

	/**
	 * Returns extension header file location absolute path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @return string The extension header file location absolute path.
	 */
	private static function get_extension_header_file_path( $slug ) {

		if ( $path = static::get_extension_trunk_path( $slug ) )
			return $path . $slug . '.php';

		return '';
	}

	/**
	 * Returns extension file headers.
	 *
	 * @since 1.0.0
	 * @staticvar array $data The fetched header data.
	 *
	 * @param string $slug The extension slug.
	 * @return array The extension header data.
	 */
	private static function get_extension_header( $slug ) {

		static $data = array();

		if ( isset( $data[ $slug ] ) )
			return $data[ $slug ];

		$default_headers = array(
			'Name'         => 'Extension Name',
			'ExtensionURI' => 'Extension URI',
			'Version'      => 'Version',
			'Description'  => 'Description',
			'Author'       => 'Author',
			'AuthorURI'    => 'Author URI',
			'License'      => 'License',
			'Network'      => 'Network',
			'TextDomain'   => 'TextDomain',
		);

		$data[ $slug ] = false;

		if ( $file = static::get_extension_header_file_path( $slug ) ) {
			$data[ $slug ] = get_file_data( $file, $default_headers, 'tsfem-extension' );
		}

		return $data[ $slug ];
	}
}


/**
 * Holds extensions activation functions for class TSF_Extension_Manager\Extensions.
 *
 * Warning: This trait holds front-end PHP security risks when mistreated. Always use
 * trait TSF_Extension_Manager\Enclose(_*) in pair with this trait.
 * @see /inc/traits/overload.trait.php
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

		$file = static::get_extension_header_file_path( $slug );

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

	/**
	 * Returns a list of active extension slugs.
	 *
	 * @since 1.0.0
	 * @staticvar array $cache
	 *
	 * @param array $placeholder Unused.
	 * @return array : {
	 * 		string The extension slug => bool True if active
	 * }
	 */
	private static function get_active_extensions( $placeholder = array() ) {

		static $cache = false;

		if ( false !== $cache )
			return $cache;

		$options = TSF_EXTENSION_MANAGER_CURRENT_OPTIONS;

		return $cache = isset( $options['active_extensions'] ) ? array_filter( $options['active_extensions'] ) : array();
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
			if ( self::is_premium_user() ) {
				return array( 'success' => true, 'case' => 2 );
			} else {
				return array( 'success' => false, 'case' => 3 );
			}
		} else {
			return array( 'success' => true, 'case' => 4 );
		}
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
	 *
	 * @param array|string $extension The extension to check.
	 * @return bool Whether the extension is active.
	 */
	private static function is_extension_active( $extension ) {

		if ( is_string( $extension ) )
			$extension = static::get_extension( $extension );

		$active = static::get_active_extensions();

		if ( isset( $active[ $extension['slug'] ] ) )
			return true;

		return false;
	}

	/**
	 * Loads extension from input.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug to load.
	 * @return bool Whether the extension is loaded.
	 */
	public static function load_extension( $slug, $_instance, $bits ) {

		self::verify_instance() or die;

		if ( 'load' !== self::get_property( '_type' ) ) {
			self::reset();
			self::invoke_invalid_type( __METHOD__ );
		}

		if ( $file = static::get_extension_header_file_path( $slug ) ) {
			if ( static::validate_file( $file ) ) {
				return static::include_extension( $file, $_instance, $bits );
			}
		}

		return false;
	}

	/**
	 * Includes extension from input.
	 * Also registers that the extension has been loaded.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The extension file to include.
	 * @param string $instance The verification instance.
	 * @param array $bits The verification instance bits.
	 * @return bool True on success, false on failure.
	 */
	private static function include_extension( $file, $_instance, $bits ) {
		return (bool) include_once( $file );
	}

	/**
	 * Validates extension file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The extension file, already normalized.
	 * @return bool True on success, false on failure.
	 */
	private static function validate_file( $file ) {

		if ( 0 === validate_file( $file ) && '.php' === substr( $file, -4 ) && file_exists( $file ) )
			return true;

		return false;
	}
}
