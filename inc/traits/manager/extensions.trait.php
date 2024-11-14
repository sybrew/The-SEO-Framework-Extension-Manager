<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, Generic.Files.OneObjectStructurePerFile.MultipleFound -- Traits intertwine.

/**
 * Holds extension data check functions for class TSF_Extension_Manager\Extensions.
 *
 * @since 1.0.0
 * @access private
 */
trait Extensions_Properties {

	/**
	 * @since 1.0.0
	 * @var array Map of extensions.
	 */
	private static $extensions = [];

	/**
	 * @since 1.0.0
	 * @var string The current extension slug to handle (activate/check/etc.).
	 */
	private static $current_slug = '';

	/**
	 * Fetches all extensions.
	 *
	 * @since 1.0.0
	 * @TODO Once we deprecate extensions, we'd want to do that here 'deprecated'=>true,
	 *       and hide those from view when deactivated. Constant activation is still possible.
	 *
	 * @return array The extensions list.
	 */
	private static function get_extensions() {
		/**
		 * @access private
		 * Don't hijack this <3
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
		 * this?), so build something positive with those skills! Become an awesome
		 * part of this awesome WordPress.org community :). Go build your own :D.
		 */
		return [
			'focus'     => [
				'slug'         => 'focus',
				'network'      => '0',
				'type'         => 'essentials+',
				'area'         => 'audit, content, keywords',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1716207115',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'articles'  => [
				'slug'         => 'articles',
				'network'      => '0',
				'type'         => 'essentials',
				'area'         => 'blogging, news',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1716196485',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'transport' => [
				'slug'         => 'transport',
				'network'      => '0',
				'type'         => 'free',
				'area'         => 'setup',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1687363584',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'honeypot'  => [
				'slug'         => 'honeypot',
				'network'      => '0',
				'type'         => 'essentials',
				'area'         => 'anti-spam',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1664173453',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'cord'      => [
				'slug'         => 'cord',
				'network'      => '0',
				'type'         => 'essentials',
				'area'         => 'analytics',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1698798295',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'local'     => [
				'slug'         => 'local',
				'network'      => '0',
				'type'         => 'premium',
				'area'         => 'business',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1675716878',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'amp'       => [
				'slug'         => 'amp',
				'network'      => '0',
				'type'         => 'free',
				'area'         => 'general',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1675763035',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'monitor'   => [
				'slug'         => 'monitor',
				'network'      => '0',
				'type'         => 'premium',
				'area'         => 'syntax',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1718279710',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'incognito' => [
				'slug'         => 'incognito',
				'network'      => '0',
				'type'         => 'free',
				'area'         => 'general',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1515109560',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'origin'    => [
				'slug'         => 'origin',
				'network'      => '0',
				'type'         => 'free',
				'area'         => 'media',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1541601833',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
			'title-fix' => [
				'slug'         => 'title-fix',
				'network'      => '0',
				'type'         => 'free',
				'area'         => 'theme',
				'author'       => 'Sybre Waaijer',
				'party'        => 'first',
				'last_updated' => '1716208521',
				'requires'     => '5.9',
				'tested'       => '6.6',
				'requires_tsf' => '4.2.8',
				'tested_tsf'   => '5.0.6',
			],
		];
	}

	/**
	 * Returns checksums from the extensions list.
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *    'sha256' => string sha256 checksum key,
	 *    'sha1'   => string sha1 checksum key,
	 *    'md5'    => string md5 checksum key,
	 * }
	 */
	private static function get_external_extensions_checksum() {
		return [
			'sha256' => '1ef444849d43ac023f1998d19cb45c1704b8dfe905c92f4a7da2a165530937c7',
			'sha1'   => 'df9fbe5b96e00df1f54b871fb2a1cf5297eec5f4',
			'md5'    => '3b5f08d1dcc08cecd490de7f1c39aa20',
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

		if ( \is_array( $slug ) )
			$slug = key( $slug );

		if ( $slug ) {
			$extensions = static::$extensions;

			if ( isset( $extensions[ $slug ] ) ) {
				return $extensions[ $slug ];
			} else {
				// Extension that doesn't exist is registered as activated.
				// The user can't remove this notice without disconnecting the account. TODO Purge it? --> Not here!
				// TODO run $this->disable_extension( $slug ) and register a notice: "${slug} no longer exists"
				// TODO Forward this to the upgrader for whenever an extension's removed.
				return [];
			}
		} else {
			\tsf()->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'You must specify a slug.' );
			return [];
		}
	}

	/**
	 * Generates expected asset URL or path for extensions.
	 *
	 * @since 1.0.0
	 * @todo Reintroduce using this?
	 *
	 * @param string $slug The extension slug.
	 * @param string $file The file to generate URL or path from.
	 * @param bool   $url  Whether to return an URL or path.
	 * @return string The extension asset URL or path.
	 */
	private static function get_extension_asset_location( $slug, $file, $url = false ) {

		if ( ! $slug || ! $file ) return '';

		$path = static::get_extension_relative_path( $slug );

		if ( $url ) {
			$path = str_replace( \DIRECTORY_SEPARATOR, '/', $path );

			return \TSF_EXTENSION_MANAGER_DIR_URL . "{$path}assets/$file";
		} else {
			return \TSF_EXTENSION_MANAGER_DIR_PATH . "{$path}assets" . \DIRECTORY_SEPARATOR . $file;
		}
	}

	/**
	 * Generates expected trunk path for extensions. If they're found.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @return string The extension trunk file path.
	 */
	private static function get_extension_trunk_path( $slug ) {
		if ( ! $slug ) return '';
		return \TSF_EXTENSION_MANAGER_DIR_PATH . static::get_extension_relative_path( $slug ) . 'trunk' . \DIRECTORY_SEPARATOR;
	}

	/**
	 * Generates expected extension directory path relative to the plugin home directory.
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

		if ( ! $extension )
			return '';

		// phpcs:ignore -- No network support like this, yet.
		// $network = static::is_extension_network( $extension );
		$premium    = static::is_extension_premium( $extension );
		$essentials = static::is_extension_essentials( $extension );

		$path[ $slug ] = '';

		$path[ $slug ] .= 'extensions/';
		// phpcs:ignore -- No network support like this, yet.
		// $path[ $slug ] .= $network ? 'network/' : '';
		$path[ $slug ] .= $premium ? 'premium/' : ( $essentials ? 'essentials/' : 'free/' );
		$path[ $slug ] .= $slug . '/';

		$path[ $slug ] = str_replace( '/', \DIRECTORY_SEPARATOR, $path[ $slug ] );

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

		$path = static::get_extension_trunk_path( $slug );

		if ( ! $path ) return '';

		return "$path$slug.php";
	}

	/**
	 * Returns extension file headers.
	 *
	 * @since 1.0.0
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

		$file = static::get_extension_header_file_location( $slug );

		// phpcs:ignore, TSF.Performance.Functions.PHP -- file_exists is required.
		return $data[ $slug ] = file_exists( $file )
			? \get_file_data( $file, $default_headers, 'tsfem-extension' )
			: [];
	}
}

/**
 * Holds extensions activation functions for class TSF_Extension_Manager\Extensions.
 *
 * Warning: This trait holds front-end PHP security risks when mistreated. Always use
 * trait TSF_Extension_Manager\Enclose(_*) in pair with this trait.
 *
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
	 *    'hash'    => string The generated hash,
	 *    'type'    => string The hash type used,
	 *    'matches' => array The pre-calculated hash matches.
	 * }
	 */
	private static function get_extensions_checksum() {

		static $checksum;

		if ( isset( $checksum ) )
			return $checksum;

		$type = \tsfem()->get_hash_type();
		// phpcs:ignore -- No objects are inserted, nor is this ever unserialized.
		$hash = hash( $type, serialize( static::$extensions ) );

		return $checksum = [
			'hash'    => $hash,
			'type'    => $type,
			'matches' => static::get_external_extensions_checksum(),
		];
	}

	/**
	 * Determines extensions list checksum to be compared to against the API server.
	 *
	 * @since 1.0.0
	 * @todo Use this if people are being hackish.
	 *
	 * @return array : {
	 *    'hash' => string The generated hash,
	 *    'type' => string The hash type used,
	 * }
	 */
	private static function get_extension_checksum() {

		static $checksum;

		if ( isset( $checksum ) )
			return $checksum;

		$file = static::get_extension_header_file_location( static::$current_slug );

		$type = \tsfem()->get_hash_type();
		$hash = hash_file( $type, $file );

		return $checksum = [
			'hash' => $hash,
			'type' => $type,
		];
	}

	/**
	 * Returns a list of active extension slugs.
	 *
	 * TODO Enforce extension order, instead of activation order?
	 *      This could prevent some racing-bugs.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Now listens to the \TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS constant.
	 *
	 * @return array : {
	 *    string The extension slug => bool True if active
	 * }
	 */
	private static function get_active_extensions() {

		static $memo;

		if ( isset( $memo ) )
			return $memo;

		$extensions = \get_option( \TSF_EXTENSION_MANAGER_ACTIVE_EXTENSIONS_OPTIONS, [] );

		if ( \TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS )
			$extensions = array_merge( $extensions, \TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS );

		// Assume an extension would be active -- why else would they use this plugin?
		// Don't add a defence clause. That should only make these calls useless right after activation.
		$is_premium_user   = self::is_premium_user();
		$is_connected_user = self::is_connected_user();

		foreach ( $extensions as $_extension => $_active ) {
			if (
				   ! $_active
				|| ! $is_premium_user && static::is_extension_premium( $_extension )
				|| ( ! $is_connected_user && static::is_extension_essentials( $_extension ) )
				|| ( ! static::is_extension_compatible( $_extension ) )
			) {
				unset( $extensions[ $_extension ] );
			}
		}

		return $memo = $extensions;
	}

	/**
	 * Validates extension activation.
	 *
	 * @since 1.0.0
	 * @since 1.5.1 Now tests for extension prior-activation.
	 * @since 2.0.0 Now tests essentials.
	 *
	 * @return array : {
	 *    'success' => bool Whether the activation can proceed.
	 *    'case'    => int The status key. {
	 *       0: Invalid instance.
	 *       1: Invalid extension.
	 *       2: Premium/Essentials OK.
	 *       3: Not enough privileges.
	 *       4: Free OK.
	 *       5: Race condition on activation (send OK for "activated").
	 *    }
	 * }
	 */
	public static function validate_extension_activation() {
		// phpcs:disable, WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- condensed code

		if ( ! self::verify_instance() )
			return [ 'success' => false, 'case' => 0 ];

		if ( 'activation' !== self::get_property( '_type' ) ) {
			self::reset();
			self::invoke_invalid_type( __METHOD__ );
		}

		$extension = static::get_extension( static::$current_slug );

		if ( ! $extension )
			return [ 'success' => false, 'case' => 1 ];

		if ( static::is_extension_active( $extension ) )
			return [ 'success' => true, 'case' => 5 ];

		if ( static::is_extension_premium( $extension ) ) {
			if ( self::is_premium_user() ) {
				return [ 'success' => true, 'case' => 2 ];
			} else {
				return [ 'success' => false, 'case' => 3 ];
			}
		} elseif ( static::is_extension_essentials( $extension ) ) {
			if ( self::is_connected_user() ) {
				return [ 'success' => true, 'case' => 2 ];
			} else {
				return [ 'success' => false, 'case' => 3 ];
			}
		} else {
			return [ 'success' => true, 'case' => 4 ];
		}
		// phpcs:enable, WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
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

		if ( \is_string( $extension ) )
			$extension = static::get_extension( $extension );

		return 'premium' === $extension['type'];
	}

	/**
	 * Determines whether the input extension is essentials.
	 *
	 * @since 2.0.0
	 *
	 * @param array|string $extension The extension to check.
	 * @return bool Whether the extension is essentials.
	 */
	private static function is_extension_essentials( $extension ) {

		if ( \is_string( $extension ) )
			$extension = static::get_extension( $extension );

		return false !== strpos( $extension['type'], 'essentials' );
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

		if ( \is_string( $extension ) )
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

		if ( \is_string( $extension ) )
			$extension = static::get_extension( $extension );

		return isset( static::get_active_extensions()[ $extension['slug'] ] );
	}

	/**
	 * Determines whether the input extension is compatible with the current WordPress
	 * and The SEO Framework version.
	 *
	 * @since 1.0.0
	 * @since 2.1.0 1. Now reworked to return only a boolean value.
	 *              2. Moved cache to deeper method.
	 *
	 * @param array|string $extension The extension to check.
	 * @return bool True if compatible, false otherwise.
	 */
	private static function is_extension_compatible( $extension ) {

		if ( \is_string( $extension ) )
			$extension = static::get_extension( $extension );

		return $extension && ! (
			static::determine_extension_incompatibility( $extension )
			& ( \TSFEM_EXTENSION_TSF_INCOMPATIBLE | \TSFEM_EXTENSION_WP_INCOMPATIBLE )
		);
	}

	/**
	 * Determines whether the input extension is compatible with the current WordPress
	 * and The SEO Framework versions.
	 *
	 * @since 2.1.0
	 * @since 2.3.1 Now compares leniently.
	 * @since 2.7.0 Now includes an unmodified $wp_version parameter.
	 * @uses static::version_compare_lenient()
	 * @global string $wp_version
	 *
	 * @param array|string $extension The extension to check.
	 * @return int|null The extension compatibility bitwise integer. Null on faiure.
	 */
	private static function determine_extension_incompatibility( $extension ) {

		if ( \is_string( $extension ) )
			$extension = static::get_extension( $extension );

		if ( ! $extension ) return null;

		static $cache = [];

		if ( isset( $cache[ $extension['slug'] ] ) )
			return $cache[ $extension['slug'] ];

		$compatibility = 0;

		static $version_cache;

		if ( empty( $version_cache ) ) {
			// include an unmodified $wp_version
			include \ABSPATH . \WPINC . '/version.php';

			$version_cache = [
				'tsf' => \THE_SEO_FRAMEWORK_VERSION,
				'wp'  => $wp_version, // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- included
			];
		}

		if ( static::version_compare_lenient( $version_cache['tsf'], $extension['requires_tsf'], '<' ) ) {
			$compatibility |= \TSFEM_EXTENSION_TSF_INCOMPATIBLE;
		} elseif ( static::version_compare_lenient( $version_cache['tsf'], $extension['tested_tsf'], '>' ) ) {
			$compatibility |= \TSFEM_EXTENSION_TSF_UNTESTED;
		}

		if ( static::version_compare_lenient( $version_cache['wp'], $extension['requires'], '<' ) ) {
			$compatibility |= \TSFEM_EXTENSION_WP_INCOMPATIBLE;
		} elseif ( static::version_compare_lenient( $version_cache['wp'], $extension['tested'], '>' ) ) {
			$compatibility |= \TSFEM_EXTENSION_WP_UNTESTED;
		}

		$cache[ $extension['slug'] ] = $compatibility;

		return $cache[ $extension['slug'] ] = $compatibility;
	}

	/**
	 * Compares the versions leniently by restricting $v1 to $v2's length in a WP's major-release fashion.
	 *
	 * Major/minor/branch: M[...M].M.m-b
	 * The second "Major" branch may only be of length 1. M.MM is therefore not allowed.
	 *
	 * @since 2.3.1
	 * @link <https://www.php.net/manual/en/function.version-compare.php>
	 *
	 * @param string $v1 First version number.
	 * @param string $v2 Second version number.
	 * @param string $op Optional. The comparison operator.
	 * @return mixed When no operator is defined, -1 if the first version is lower than the second, 0 if they are equal,
	 *               and 1 if the second is lower.
	 *               When using the operator argument, return true if the relationship is the one specified by the
	 *               operator, false otherwise. When an unsupported operator is given, NULL is returned.
	 */
	private static function version_compare_lenient( $v1, $v2, $op = null ) {

		static $memo = [];

		if ( isset( $memo[ $v1 ][ $v2 ][ $op ] ) )
			return $memo[ $v1 ][ $v2 ][ $op ];

		// 1: major, 2: minor, 3: branch
		// $regex = '/^(\d+\.\d)(\.\d+)?(-.*)?$/';
		$regex = '/(\d+\.\d)(\.\d+)?(-.*)?/'; // 2 out of 10~14 steps fewer...

		preg_match( $regex, $v1, $m_v1 );
		preg_match( $regex, $v2, $m_v2 );

		switch ( \count( $m_v2 ) ) {
			case 4: // branch
			case 3: // minor
				if ( isset( $op ) )
					return $memo[ $v1 ][ $v2 ][ $op ] = version_compare( $v1, $v2, $op );
				return $memo[ $v1 ][ $v2 ][ $op ] = version_compare( $v1, $v2 );

			default: // major
				if ( isset( $op ) )
					return $memo[ $v1 ][ $v2 ][ $op ] = version_compare( $m_v1[1], $m_v2[1], $op );
				return $memo[ $v1 ][ $v2 ][ $op ] = version_compare( $m_v1[1], $m_v2[1] );
		}
	}

	/**
	 * Test drives extension to see if an error occurs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug to load.
	 * @param bool   $ajax Whether AJAX is active.
	 * @param string $_instance The verification instance. Propagates to inclusion file if possible.
	 * @param array  $bits The verification instance bits. Propagates to inclusion file if possible.
	 * @return int|void {
	 *   -1 => No check has been performed.
	 *    1 => No file header path can be created. (Invalid extension)
	 *    2 => Extension header file is invalid. (Invalid extension)
	 *    3 => Inclusion failed.
	 *    4 => Success.
	 *    5 => Extension's already active.
	 *    void => Fatal error.
	 * }
	 */
	public static function test_extension( $slug, $ajax, $_instance, $bits ) {

		if ( ! self::verify_instance() ) {
			self::reset();

			$val = -1;
			goto tick;
		}

		if ( 'load' !== self::get_property( '_type' ) ) {
			self::reset();
			self::invoke_invalid_type( __METHOD__ );

			$val = -1;
			goto tick;
		}

		if ( static::is_extension_active( $slug ) ) {
			$val = 5;
			goto tick;
		}

		$file = static::get_extension_header_file_location( $slug );

		if ( ! $file ) {
			$val = 1;
			goto tick;
		}

		if ( ! static::validate_file( $file ) ) {
			$val = 2;
			goto tick;
		}

		// Goto tick is now forbidden. Use goto clean.
		unclean: {
			ob_start();

			\define( '_TSFEM_TESTING_EXTENSION', true );
			\define( '_TSFEM_TEST_EXT_IS_AJAX', $ajax );

			// We only want to catch a fatal/parse error.
			static::set_error_reporting( \E_ERROR | \E_PARSE | \E_CORE_ERROR | \E_COMPILE_ERROR | \E_USER_ERROR );

			register_shutdown_function( __CLASS__ . '::_shutdown_handle_test_extension_fatal_error' );
		}

		basetest: {
			// Test base file.
			$success = static::persist_include_extension( $file, $_instance, $bits );
		}

		if ( $success ) {
			jsontest: {
				// Test json file and contents.
				$success = static::perform_extension_json_tests( $slug, $_instance, $bits );
			}
		}

		$val = $success ? 4 : 3;

		clean: {
			ob_clean();

			static::reset_error_reporting();

			// No fatal error has occurred, pass and therefore nullify shutdown function.
			\define( '_TSFEM_TEST_EXT_PASS', true );
		}

		tick: {
			// Tick the instance.
			\tsfem()->_verify_instance( $_instance, $bits[1] );
		}

		end:;

		return $val;
	}

	/**
	 * Performs extension file tests based on json file input.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 : 1. Now tests extension file validity.
	 *                2. Increased extension file timeout to 3 seconds, from 2.
	 * @throws \Exception On JSON test file parsing failure.
	 *
	 * @param string $slug      The extension slug.
	 * @param string $_instance The verification instance. Passed by reference.
	 * @param array  $bits      The verification instance bits. Passed by reference.
	 * @return true on success, false on failure.
	 */
	private static function perform_extension_json_tests( $slug, &$_instance, &$bits ) {

		$base_path = static::get_extension_trunk_path( $slug );
		$json_file = "{$base_path}test.json";

		$success = [];

		if ( ! static::validate_file( $json_file, 'json' ) )
			goto end;

		$timeout = stream_context_create( [ 'http' => [ 'timeout' => 3 ] ] );
		// phpcs:ignore, WordPress.WP.AlternativeFunctions, TSF.Performance.Functions -- this is a local file call, required file get.
		$json = json_decode( file_get_contents( $json_file, false, $timeout ) );

		if ( ! $json ) {
			// json file contents are invalid.
			throw new \Exception( 'Extension test file is invalid', \E_USER_ERROR );

			// phpcs:disable, Squiz.PHP.NonExecutableCode.Unreachable -- This won't run, and that's OK.
			$success[] = false;
			goto end;
			// phpcs:enable, Squiz.PHP.NonExecutableCode.Unreachable
		}

		$namespace = empty( $json->namespace ) ? '' : $json->namespace;
		$tests     = empty( $json->test ) ? [] : (array) $json->test;

		foreach ( $tests as $_class => $_file ) {
			// Base file is already tested.
			if ( '_base' === $_class )
				continue;

			if ( \is_array( $_file ) ) {
				// Facade.
				foreach ( $_file as $f_file ) {
					$success[] = static::persist_include_extension( $base_path . $f_file, $_instance, $bits );
				}
			} else {
				$success[] = static::persist_include_extension( $base_path . $_file, $_instance, $bits );
			}

			if ( $_class ) {
				$class     = "$namespace\\$_class";
				$success[] = (bool) new $class;
			}
		}

		end:;
		return ! \in_array( false, $success, true );
	}

	/**
	 * Includes extension file and returns persisting instance and bits.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file      The file to test.
	 * @param string $_instance The verification instance. Propagates to inclusion file. Passed by reference.
	 * @param array  $bits      The verification instance bits. Propagates to inclusion file. Passed by reference.
	 * @return bool Whether the file inclusion(s) succeeded.
	 */
	private static function persist_include_extension( $file, &$_instance, &$bits ) {

		$yield_count = 0;
		$success     = [];

		// Get follow-up verification instance.
		foreach ( \tsfem()->_yield_verification_instance( 2, $_instance, $bits ) as $verification ) {

			$bits      = $verification['bits'];
			$_instance = $verification['instance'];

			switch ( $yield_count ) {
				case 0:
					$success[] = static::include_extension( $file, $_instance, $bits );
					// Continue to default for counting.
				default:
					$yield_count++;
			}
		}

		return ! \in_array( false, $success, true );
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
	 * Also disables commong \WP_DEBUG functionality that are prone to interfere.
	 * The \WP_DEBUG functionality can not be re-enabled, currently.
	 * Nevertheless, this is handled on POST as one of its final action.
	 *
	 * @see http://php.net/manual/en/function.error-reporting.php
	 * @since 1.0.0
	 * @todo Reset \WP_DEBUG functionality? i.e. by caching the filters current input.
	 *       Probably not necessary, since we crash right after this.
	 *
	 * @param null|int $val The error reporting level. If null, it will reset
	 *        error_reporting to its previous value.
	 * @return void Early if $val is null.
	 */
	private static function set_error_reporting( $val = null ) {
		// phpcs:disable, WordPress.PHP.DevelopmentFunctions, WordPress.PHP.DiscouragedPHPFunctions -- Required catchables.

		static $_prev_error_reporting;

		if ( null === $val ) {
			// Reset error reporting, if set.
			if ( isset( $_prev_error_reporting ) )
				error_reporting( $_prev_error_reporting );

			return;
		}

		// Cache previous error reporting.
		if ( null === $_prev_error_reporting )
			$_prev_error_reporting = error_reporting();

		if ( isset( $val ) )
			error_reporting( $val );

		if ( 0 === $val ) {
			// Also disable \WP_DEBUG functions used by The SEO Framework.
			\add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
			\add_filter( 'deprecated_function_trigger_error', '__return_false' );
			\add_filter( 'the_seo_framework_inaccessible_p_or_m_trigger_error', '__return_false' );
		}

		// phpcs:enable, WordPress.PHP.DevelopmentFunctions, WordPress.PHP.DiscouragedPHPFunctions
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

		if ( \defined( '_TSFEM_TEST_EXT_PASS' ) )
			return;

		$level = ob_get_level();
		if ( $level )
			while ( $level-- ) ob_end_clean();

		$error      = error_get_last();
		$error_type = '';

		switch ( $error['type'] ) {
			case \E_ERROR:
			case \E_CORE_ERROR:
			case \E_COMPILE_ERROR:
			case \E_USER_ERROR:
				$error_type = 'Fatal error.';
				break;

			case \E_PARSE:
				$error_type = 'Parse error.';
				break;

			default:
				$error_type = "Type {$error['type']} error.";
		}

		$error['message'] = static::clean_error_message( $error['message'], $error );

		$error_notice = $error_type . ' ' . \esc_html__( 'Extension is not compatible with your server configuration.', 'the-seo-framework-extension-manager' );

		$advanced_error_notice = \esc_html( $error['message'] ) . ' in file <strong>' . \esc_html( $error['file'] ) . '</strong> on line <strong>' . \esc_html( $error['line'] ) . '</strong>.';

		if ( \wp_doing_ajax() ) {
			// TODO send slug?
			\wp_send_json_error( [
				'notice'      => \TSF_Extension_Manager\get_ajax_notice( false, $error_notice, 10005 ),
				'fatal_error' => sprintf( '<strong>Error message:</strong> %s', $advanced_error_notice ),
			] );
		} else {
			$error_notice .= '<br>' . \esc_html__( 'Extension has not been activated.', 'the-seo-framework-extension-manager' );
			$error_notice .= "<p><strong>Error message:</strong> <br>$advanced_error_notice</p>";

			\wp_die(
				$error_notice, // phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- it's escaped.
				'Extension error',
				[
					'back_link'      => true,
					'text_direction' => 'ltr',
				]
			);
		}
	}

	/**
	 * Removes redundant data from $message.
	 *
	 * @since 1.0.0
	 * @NOTE Output is not escaped.
	 *
	 * @param string $message The current error message.
	 * @param array  $error   The PHP triggered error.
	 * @return string The cleaned error message.
	 */
	private static function clean_error_message( $message = '', $error = [] ) {

		// phpcs:disable, WordPress.CodeAnalysis.AssignmentInCondition

		// Remove stack trace.
		if ( false !== ( $stack_pos = stripos( $message, 'Stack trace:' ) ) )
			$message = substr( $message, 0, $stack_pos );

		// Remove error location and line from message.
		if ( ( $loc = stripos( $message, ' in /' ) ) ) {
			$additions = '.php:' . $error['line'];
			$loc_line  = stripos( $message, $additions, $loc );
			$offset    = $loc_line - $loc + \strlen( $additions );

			if ( $loc_line && ( $rem = substr( $message, $loc, $offset ) ) ) {
				// Continue only if there are no spaces.
				$without_in = substr( $rem, 4 );
				if ( false === strpos( $without_in, ' ' ) ) {
					$message = trim( str_replace( $rem, '', $message ) );
				}
			}
		}

		// phpcs:enable, WordPress.CodeAnalysis.AssignmentInCondition

		return $message;
	}

	/**
	 * Loads extension from input.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug      The extension slug to load.
	 * @param string $_instance The verification instance. Propagates to inclusion file. Passed by reference.
	 * @param array  $bits      The verification instance bits. Propagates to inclusion file. Passed by reference.
	 * @return bool Whether the extension is loaded.
	 */
	public static function load_extension( $slug, &$_instance, &$bits ) {

		if ( ! self::verify_instance() ) return false;

		if ( 'load' !== self::get_property( '_type' ) ) {
			self::reset();
			self::invoke_invalid_type( __METHOD__ );
		}

		$file = static::get_extension_header_file_location( $slug );

		if ( $file && static::validate_file( $file ) )
			return static::include_extension( $file, $_instance, $bits );

		// Tick the instance on failure.
		\tsfem()->_verify_instance( $_instance, $bits[1] );

		return false;
	}

	/**
	 * Includes extension from input.
	 * Also registers that the extension has been loaded.
	 *
	 * @since 1.0.0
	 * @since 2.2.0 Now allows for persistent shared-class tests (thanks to _once).
	 * @since 2.5.1 Added memoization, speeds up loading.
	 *
	 * @param string $file      The extension file to include.
	 * @param string $_instance The verification instance. Propagates to inclusion file. Passed by reference.
	 * @param array  $bits      The verification instance bits. Propagates to inclusion file. Passed by reference.
	 * @return bool True on success, false on failure.
	 */
	private static function include_extension( $file, &$_instance, &$bits ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis -- includes

		static $loaded = [];

		if ( isset( $loaded[ $file ] ) ) {
			// Tick the instance for we bypass inclusion.
			\tsfem()->_verify_instance( $_instance, $bits[1] );

			return $loaded[ $file ];
		}

		return $loaded[ $file ] = (bool) include $file;
	}

	/**
	 * Validates extension PHP file. Should not be used for anything else.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The extension file, already normalized.
	 * @param string $type The file (extension) type.
	 * @return bool True on success, false on failure.
	 */
	private static function validate_file( $file, $type = 'php' ) {

		if ( ( ".$type" ) === substr( $file, - ( \strlen( $type ) + 1 ) ) ) {
			switch ( \validate_file( $file ) ) {
				case 2:
					if ( 'WIN' !== strtoupper( substr( \PHP_OS, 0, 3 ) ) ) {
						break;
					}
					// Fall through to test 0
				case 0:
					// phpcs:ignore, TSF.Performance.Functions.PHP -- necessary call.
					return file_exists( $file );
			}
		}

		return false;
	}
}
