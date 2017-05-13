<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	private static $extensions = [];

	/**
	 * Holds the instance extension slug to handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string $current_slug
	 */
	private static $current_slug = [];

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
		return [
			'amp' => [
				'slug' => 'amp',
				'network' => '0',
				'type' => 'free',
				'area' => 'general',
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'party' => 'first',
				'last_updated' => '1494391221',
				'requires' => '4.4.0',
				'tested' => '4.8.0',
				'requires_tsf' => '2.8.2',
				'tested_tsf' => '2.9.2',
			],
			'articles' => [
				'slug' => 'articles',
				'network' => '0',
				'type' => 'premium',
				'area' => 'news',
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'party' => 'first',
				'last_updated' => '1494391221',
				'requires' => '4.4.0',
				'tested' => '4.8.0',
				'requires_tsf' => '2.8.2',
				'tested_tsf' => '2.9.2',
			],
			'monitor' => [
				'slug' => 'monitor',
				'network' => '0',
				'type' => 'premium',
				'area' => 'general',
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'party' => 'first',
				'last_updated' => '1494391221',
				'requires' => '4.4.0',
				'tested' => '4.8.0',
				'requires_tsf' => '2.7.0',
				'tested_tsf' => '2.9.2',
			],
			'incognito' => [
				'slug' => 'incognito',
				'network' => '0',
				'type' => 'free',
				'area' => 'general',
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'party' => 'first',
				'last_updated' => '1494391221',
				'requires' => '3.9.0',
				'tested' => '4.8.0',
				'requires_tsf' => '2.2.0',
				'tested_tsf' => '2.9.2',
			],
			'title-fix' => [
				'slug' => 'title-fix',
				'network' => '0',
				'type' => 'free',
				'area' => 'general',
				'version' => '1.0.2',
				'author' => 'Sybre Waaijer',
				'party' => 'first',
				'last_updated' => '1494391221',
				'requires' => '3.9.0',
				'tested' => '4.8.0',
				'requires_tsf' => '2.7.0',
				'tested_tsf' => '2.9.2',
			],
			'honeypot' => [
				'slug' => 'honeypot',
				'network' => '0',
				'type' => 'premium',
				'area' => 'spam',
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'party' => 'first',
				'last_updated' => '1494567223',
				'requires' => '4.4.0',
				'tested' => '4.8.0',
				'requires_tsf' => '2.7.0',
				'tested_tsf' => '2.9.2',
			],
		];
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
		return [
			'sha256' => '347be7eb842ad3596e108108388e0d3a4f084e7efd2d5ebdbee1704f026f7226',
			'sha1'   => 'f88708cdfc79e980cf931f89143e61b4f4a17076',
			'md5'    => '7219ecebe327a439f25a7e0c118ee744',
		];
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
				\the_seo_framework()->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'You must specify an existing extension slug.' );
				return [];
			}
		} else {
			\the_seo_framework()->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'You must specify a slug.' );
			return [];
		}
	}

	/**
	 * Generates asset URL or path for extensions.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @param string $file The file to generate URL or path from.
	 * @param bool $url Whether to return an URL or path.
	 * @return string The extension asset URL or path.
	 */
	private static function get_extension_asset_location( $slug, $file, $url = false ) {

		if ( empty( $slug ) || empty( $file ) )
			return '';

		$path = static::get_extension_relative_path( $slug );

		if ( $url ) {
			$path = str_replace( DIRECTORY_SEPARATOR, '/', $path );

			return TSF_EXTENSION_MANAGER_DIR_URL . $path . 'assets/' . $file;
		} else {
			return TSF_EXTENSION_MANAGER_DIR_PATH . $path . 'assets' . DIRECTORY_SEPARATOR . $file;
		}
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

		static $path = [];

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
	private static function get_extension_header_file_location( $slug ) {

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

		static $data = [];

		if ( isset( $data[ $slug ] ) )
			return $data[ $slug ];

		$default_headers = [
			'Name'         => 'Extension Name',
			'ExtensionURI' => 'Extension URI',
			'Version'      => 'Extension Version',
			'Description'  => 'Extension Description',
			'Author'       => 'Extension Author',
			'AuthorURI'    => 'Extension Author URI',
			'License'      => 'Extension License',
			'Network'      => 'Extension Network',
			'TextDomain'   => 'Extension TextDomain',
			'MenuSlug'     => 'Extension Menu Slug',
		];

		$data[ $slug ] = false;

		$file = static::get_extension_header_file_location( $slug );

		if ( file_exists( $file ) )
			$data[ $slug ] = \get_file_data( $file, $default_headers, 'tsfem-extension' );

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
	 * 		'matches' => array The pre-calculated hash matches.
	 * }
	 */
	private static function get_extensions_checksum() {

		static $checksum = null;

		if ( isset( $checksum ) )
			return $checksum;

		$type = \tsf_extension_manager()->get_hash_type();
		$hash = hash( $type, serialize( static::$extensions ) );

		return $checksum = [
			'hash' => $hash,
			'type' => $type,
			'matches' => static::get_external_extensions_checksum(),
		];
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

		$type = \tsf_extension_manager()->get_hash_type();
		$hash = hash_file( $type, $file );

		return $checksum = [
			'hash' => $hash,
			'type' => $type,
		];
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
	private static function get_active_extensions( $placeholder = [] ) {

		static $cache = false;

		if ( false !== $cache )
			return $cache;

		$options = (array) \get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] );

		return $cache = isset( $options['active_extensions'] ) ? array_filter( $options['active_extensions'] ) : [];
	}

	/**
	 * Validates extension activation.
	 *
	 * @since 1.0.0
	 *
	 * @return array : {
	 *		'success' => bool Whether the activation can proceed.
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
			return [ 'success' => false, 'case' => 1 ];

		if ( static::is_extension_premium( $extension ) ) {
			if ( self::is_premium_user() ) {
				return [ 'success' => true, 'case' => 2 ];
			} else {
				return [ 'success' => false, 'case' => 3 ];
			}
		} else {
			return [ 'success' => true, 'case' => 4 ];
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
	 * Determines whether the input extension is compatible with the current WordPress
	 * and The SEO Framework version.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $extension The extension to check.
	 * @return int Compatibility : {
	 *		0 is compatible.
	 *		1, 2 and 3 is okay but might require update of either WP or TSF. {
	 *			1 : TSF version is greater than tested against.
	 * 			2 : TSF is compatible. WP Version is greater than tested against.
	 * 			3 : TSF and WP versions are both greater than tested against.
	 * 		}
	 *		-1 is not compatible.
	 */
	private static function is_extension_compatible( $extension ) {

		if ( is_string( $extension ) )
			$extension = static::get_extension( $extension );

		static $cache = [];

		if ( isset( $cache[ $extension['slug'] ] ) )
			return $cache[ $extension['slug'] ];

		$compatibility = static::determine_extension_compatibility( $extension );

		switch ( $compatibility ) :
			case 0 :
				$_compatibility = 0;
				break;

			case 1 :
				$_compatibility = 1;
				break;

			case 4 :
				$_compatibility = 2;
				break;

			case 5 :
				$_compatibility = 3;
				break;

			default :
				$_compatibility = -1;
				break;
		endswitch;

		return $cache[ $extension['slug'] ] = $_compatibility;
	}

	/**
	 * Determines whether the input extension is compatible with the current WordPress
	 * and The SEO Framework version.
	 *
	 * The uneven bits (left to right) always need to be followed by an active bit.
	 * So 1010 isn't possible. 0101 and 1101 are.
	 *
	 * I could've used concatenation for bit additions, but shifting bit series is more difficult; ergo cooler.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $extension The extension to check.
	 * @param bool $get_bits Whether to get bits or int.
	 * @return int|string Either 4 bits or an integer that determine compatibility : {
	 *		0  | '0000' = good => Completely compatible.
	 *		1  | '0001' = okay => TSF version is greater than tested against.
	 *		3  | '0011' = bad  => TSF is not compatible.
	 *		4  | '0100' = okay => TSF is compatible. WP Version is greater than tested against.
	 *		5  | '0101' = okay => TSF and WP versions are both greater than tested against.
	 *		7  | '0111' = bad  => TSF is not compatible. WP version is greater than tested against.
	 *		12 | '1100' = bad  => WP is not compatible.
	 *		13 | '1101' = bad  => WP is not compatible. TSF version is greater than testest against.
	 *		15 | '1111' = bad  => Not compatible.
	 * }
	 */
	private static function determine_extension_compatibility( $extension, $get_bits = false ) {

		$compatibility = static::get_extension_compatibility( $extension );

		//* bindec( '1111' )
		$bit = 15;

		//* Set first two bits ( 1100 );
		$first_two_bit = ~ $bit >> $compatibility['wp'];
		//* Set last two bits ( 0011 );
		$last_two_bit = ~ $bit >> 2 ^ $bit << 2 - $compatibility['tsf'];

		//* Add bits up and invert bits so 0 is best case scenario.
		$bit = $first_two_bit | $last_two_bit;
		$bit = ~ $bit;

		//* Convert bits to string and extract last 4 bits.
		$bit = substr( decbin( $bit ), -4 );

		return $get_bits ? $bit : bindec( $bit );
	}

	/**
	 * Determines whether the input extension is compatible with the current WordPress
	 * and The SEO Framework version.
	 *
	 * @since 1.0.0
	 * @global string $wp_version
	 *
	 * @param array|string $extension The extension to check.
	 * @return array Whether the extension is compatible. : {
	 *		'tsf' => int (0-2),
	 *		'wp' => int Compatibility (0-2),
	 * }
	 */
	private static function get_extension_compatibility( $extension ) {
		global $wp_version;

		if ( is_string( $extension ) )
			$extension = static::get_extension( $extension );

		/**
		 * @param array $compatibility : {
		 *		key => int : {
		 *			0: Not compatible.
		 *			1: Version exceeeds tested check. Likely compatible.
		 *			2: Compatible.
		 *		}
		 * }
		 */
		$compatibility = [
			'tsf' => 0,
			'wp' => 0,
		];

		$tsf_version = \the_seo_framework_version();
		$_wp_version = $wp_version;

		if ( version_compare( $tsf_version, $extension['tested_tsf'], '>' ) ) {
			$compatibility['tsf'] = 1;
		} elseif ( version_compare( $tsf_version, $extension['requires_tsf'], '>=' ) ) {
			$compatibility['tsf'] = 2;
		}

		if ( version_compare( $_wp_version, $extension['tested'], '>' ) ) {
			$compatibility['wp'] = 1;
		} elseif ( version_compare( $_wp_version, $extension['requires'], '>=' ) ) {
			$compatibility['wp'] = 2;
		}

		return $compatibility;
	}

	/**
	 * Test drives extension to see if an error occurs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug to load.
	 * @param bool $ajax Whether AJAX is active.
	 * @param string $_instance The verification instance. Propagates to inclusion file if possible.
	 * @param array $bits The verification instance bits. Propagates to inclusion file if possible.
	 * @return int|void {
	 * 		-1 => No check has been performed.
	 * 		1 => No file header path can be created. (Invalid extension)
	 * 		2 => Extension header file is invalid. (Invalid extension)
	 * 		3 => Inclusion failed.
	 *		4 => Success.
	 *		void => Fatal error.
	 * }
	 */
	public static function test_extension( $slug, $ajax, $_instance, $bits ) {

		self::verify_instance() or die;

		if ( 'load' !== self::get_property( '_type' ) ) {
			self::reset();
			self::invoke_invalid_type( __METHOD__ );

			$val = -1;
			goto tick;
		}

		$file = static::get_extension_header_file_location( $slug );

		if ( empty( $file ) ) {
			$val = 1;
			goto tick;
		}

		if ( ! static::validate_file( $file ) ) {
			$val = 2;
			goto tick;
		}

		//* Goto tick is now forbidden. Use goto clean.
		unclean : {
			ob_start();

			define( '_TSFEM_TESTING_EXTENSION', true );
			define( '_TSFEM_TEST_EXT_IS_AJAX', $ajax );

			//* We only want to catch a fatal/parse error.
			static::set_error_reporting( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR );

			register_shutdown_function( __CLASS__ . '::_shutdown_handle_test_extension_fatal_error' );
		}

		basetest : {
			//* Test base file.
			$success = static::persist_include_extension( $file, $_instance, $bits );
		}

		if ( $success ) {
			jsontest : {
				//* Test json file and contents.
				$success = static::perform_extension_json_tests( $slug, $_instance, $bits );
			}
		}

		$val = $success ? 4 : 3;

		clean : {
			ob_clean();

			static::reset_error_reporting();

			//* No fatal error has occurred, pass and therefore nullify shutdown function.
			define( '_TSFEM_TEST_EXT_PASS', true );
		}

		tick : {
			//* Tick the instance.
			\tsf_extension_manager()->_verify_instance( $_instance, $bits[1] );
		}

		end :;

		return $val;
	}

	/**
	 * Performs extension file tests based on json file input.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 : 1. Now tests extension file validity.
	 *                2. Increased extension file timeout to 3 seconds, from 2.
	 *
	 * @param string $slug The extension slug.
	 * @param string $_instance The verification instance. Passed by reference.
	 * @param array $bits The verification instance bits. Passed by reference.
	 * @return true on success, false on failure.
	 */
	private static function perform_extension_json_tests( $slug, &$_instance, &$bits ) {

		$base_path = static::get_extension_trunk_path( $slug );
		$json_file = $base_path . 'test.json';

		$success = [];

		if ( 0 !== \validate_file( $json_file ) || ! file_exists( $json_file ) )
			goto end;

		$timeout = stream_context_create( [ 'http' => [ 'timeout' => 3 ] ] );
		$json = json_decode( file_get_contents( $json_file, false, $timeout ) );

		if ( empty( $json ) ) {
			//* json file contents are invalid.
			throw new \Exception( 'Extension test file is invalid.', E_USER_ERROR );
			$success[] = false;
			goto end;
		}

		$namespace = empty( $json->namespace ) ? '' : $json->namespace;
		$tests = empty( $json->test ) ? [] : (array) $json->test;

		foreach ( $tests as $_class => $_file ) {
			//* Base file is already tested.
			if ( '_base' === $_class )
				continue;

			if ( is_array( $_file ) ) {
				//* Facade.
				foreach ( $_file as $f_file ) :
					$success[] = static::persist_include_extension( $base_path . $f_file, $_instance, $bits );
				endforeach;
			} else {
				$success[] = static::persist_include_extension( $base_path . $_file, $_instance, $bits );
			}

			if ( $_class ) {
				$class = $namespace . '\\' . $_class;
				$success[] = new $class;
			}
		}

		end : {
			return ! in_array( false, $success, true );
		}
	}

	/**
	 * Includes extension file and returns persisting instance and bits.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The file to test.
	 * @param string $_instance The verification instance. Propagates to inclusion file. Passed by reference.
	 * @param array $bits The verification instance bits. Propagates to inclusion file. Passed by reference.
	 * @return bool Whether the file inclusion(s) succeeded.
	 */
	private static function persist_include_extension( $file, &$_instance, &$bits ) {

		$yield_count = 0;
		$success = [];

		//* Get follow-up verification instance.
		foreach ( \tsf_extension_manager()->_yield_verification_instance( 2, $_instance, $bits ) as $verification ) :

			$bits = $verification['bits'];
			$_instance = $verification['instance'];

			switch ( $yield_count ) :
				case 0 :
					$success[] = static::include_extension( $file, $_instance, $bits );

				default :
					$yield_count++;
					break;
			endswitch;
		endforeach;

		return ! in_array( false, $success, true );
	}

	/**
	 * Resets error reporting to initial value.
	 *
	 * @see set_error_reporting();
	 * @since 1.0.0
	 */
	private static function reset_error_reporting() {
		static::set_error_reporting();
	}

	/**
	 * Sets error reporting to input $val.
	 *
	 * Also disables commong WP_DEBUG functionality that are prone to interfere.
	 * The WP_DEBUG functionality can not be re-enabled, currently.
	 * Nevertheless, this is handled on POST as one of its final action.
	 *
	 * @see http://php.net/manual/en/function.error-reporting.php
	 * @since 1.0.0
	 * @staticvar int $_prev_error_reporting
	 * @todo Reset WP_DEBUG functionality? i.e. by caching the filters current input.
	 *
	 * @param null|int $val The error reporting level. If null, it will reset
	 *			error_reporting to its previous value.
	 * @return void Early if $val is null.
	 */
	private static function set_error_reporting( $val = null ) {

		static $_prev_error_reporting = null;

		if ( null === $val ) {
			//* Reset error reporting, if set.
			if ( isset( $_prev_error_reporting ) )
				error_reporting( $_prev_error_reporting );

			return;
		}

		//* Cache previous error reporting.
		if ( null === $_prev_error_reporting )
			$_prev_error_reporting = error_reporting();

		if ( isset( $val ) )
			error_reporting( $val );

		if ( 0 === $val ) {
			//* Also disable WP_DEBUG functions used by The SEO Framework.
			\add_action( 'doing_it_wrong_trigger_error', '__return_false' );
			\add_action( 'deprecated_function_trigger_error', '__return_false' );
			\add_action( 'the_seo_framework_inaccessible_p_or_m_trigger_error', '__return_false' );
		}
	}

	/**
	 * Handles fatal error on extension activation for both AJAX and form activation.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void Early when all tests have passed.
	 */
	public static function _shutdown_handle_test_extension_fatal_error() {

		if ( defined( '_TSFEM_TEST_EXT_PASS' ) )
			return;

		if ( $level = ob_get_level() ) {
			while ( $level ) {
				ob_end_clean();
				$level--;
			}
		}

		$error = error_get_last();
		$error_type = '';

		switch ( $error['type'] ) :
			case E_ERROR :
			case E_CORE_ERROR :
			case E_COMPILE_ERROR :
			case E_USER_ERROR :
				$error_type = 'Fatal error.';
				break;

			case E_PARSE :
				$error_type = 'Parse error.';
				break;

			default :
				$error_type = 'Type ' . $error['type'] . ' error.';
				break;
		endswitch;

		$error['message'] = static::clean_error_message( $error['message'], $error );

		$error_notice = $error_type . ' ' . \esc_html__( 'Extension is not compatible with your server configuration.', 'the-seo-framework-extension-manager' );
		$advanced_error_notice = '<strong>Error message:</strong> <br>' . \esc_html( $error['message'] ) . ' in file <strong>' . \esc_html( $error['file'] ) . '</strong> on line <strong>' . \esc_html( $error['line'] ) . '</strong>.';

		if ( defined( 'DOING_AJAX' ) ) {
			$notice = sprintf( '<span class="tsfem-has-hover-balloon" title="%s" data-desc="%s">%s</span>', \wp_strip_all_tags( $advanced_error_notice ), \esc_attr( $advanced_error_notice ), $error_notice );

			$status = [
				'success' => 10005,
				'notice' => $notice,
			];

			/**
			 * @TODO make notice copy-able by clicking.
			 * @TODO set slug.
			 */
			$response = WP_DEBUG ? [ 'status' => $status, 'slug' => '', 'case' => 'activate' ] : [ 'status' => $status ];
			\tsf_extension_manager()->send_json( $response, 'failure' );
			exit;
		} else {
			$error_notice .= '<br>' . \esc_html__( 'Extension has not been activated.', 'the-seo-framework-extension-manager' );

			//* Already escaped.
			\wp_die( $error_notice . '<p>' . $advanced_error_notice . '</p>', 'Extension error', [ 'back_link' => true, 'text_direction' => 'ltr' ] );
		}
	}

	/**
	 * Removes redundant data from $message.
	 *
	 * @since 1.0.0
	 * @NOTE Output is not sanitized.
	 *
	 * @param string $message The current error message.
	 * @param array $error The PHP triggered error.
	 * @return string The cleaned error message.
	 */
	private static function clean_error_message( $message = '', $error = [] ) {

		//* Remove stack trace.
		if ( false !== ( $stack_pos = stripos( $message, 'Stack trace:' ) ) )
			$message = substr( $message, 0, $stack_pos );

		//* Remove error location and line from message.
		if ( ( $loc = stripos( $message, ' in /' ) ) ) {
			$additions = '.php:' . $error['line'];
			$loc_line = stripos( $message, $additions, $loc );
			$offset = $loc_line - $loc + strlen( $additions );

			if ( $loc_line && ( $rem = substr( $message, $loc, $offset ) ) ) {
				//* Continue only if there are no spaces.
				$without_in = substr( $rem, 4 );
				if ( false === strpos( $without_in, ' ' ) ) {
					$message = trim( str_replace( $rem, '', $message ) );
				}
			}
		}

		return $message;
	}

	/**
	 * Loads extension from input.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug to load.
	 * @param string $instance The verification instance. Propagates to inclusion file.
	 * @param array $bits The verification instance bits. Propagates to inclusion file.
	 * @return bool Whether the extension is loaded.
	 */
	public static function load_extension( $slug, $_instance, $bits ) {

		self::verify_instance() or die;

		if ( 'load' !== self::get_property( '_type' ) ) {
			self::reset();
			self::invoke_invalid_type( __METHOD__ );
		}

		if ( ( $file = static::get_extension_header_file_location( $slug ) ) ) {
			if ( static::validate_file( $file ) ) {
				return static::include_extension( $file, $_instance, $bits );
			}
		}

		//* Tick the instance on failure.
		\tsf_extension_manager()->_verify_instance( $_instance, $bits[1] );

		return false;
	}

	/**
	 * Includes extension from input.
	 * Also registers that the extension has been loaded.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The extension file to include.
	 * @param string $instance The verification instance. Propagates to inclusion file.
	 * @param array $bits The verification instance bits. Propagates to inclusion file.
	 * @return bool True on success, false on failure.
	 */
	private static function include_extension( $file, $_instance, $bits ) {
		return (bool) include_once( $file );
	}

	/**
	 * Validates extension PHP file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The extension file, already normalized.
	 * @return bool True on success, false on failure.
	 */
	private static function validate_file( $file ) {

		if ( '.php' === substr( $file, -4 ) && 0 === \validate_file( $file ) && file_exists( $file ) )
			return true;

		return false;
	}
}
