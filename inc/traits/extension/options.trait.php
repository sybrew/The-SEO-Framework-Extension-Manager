<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extensions_Options_Cache.
 *
 * Caches the extension options. Used for updating and managing options.
 *
 * @since 1.0.0
 * @access private
 * @final
 */
final class Extensions_Options_Cache {
	use Construct_Core_Static_Final,
		Enclose_Core_Final;

	/**
	 * Holds the extension options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 */
	private static $options = null;

	/**
	 * Initializes the options cache.
	 *
	 * @since 1.0.0
	 */
	private static function init_options_cache() {
		static::$options = (array) \get_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, [] );
	}

	/**
	 * Returns all the extension options from cache.
	 * Used internally to stack multiple extension options stacks.
	 *
	 * Also initializes the options cache, if not already.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array All extension options.
	 */
	public static function _get_options_cache() {

		if ( is_null( static::$options ) )
			static::init_options_cache();

		return static::$options;
	}

	/**
	 * Overrides current option stack with the new one.
	 * Note: you can get the previous set through `_get_options_cache()`.
	 *
	 * Also initializes the options cache, if not already.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string|int $index The option index that has to be changed.
	 * @param null|array $new_options The new options to set.
	 *        Should not have changed options from outside the current extension's scope.
	 * @param bool $delete If $new_options aren't set, but this is true, then
	 *        it will delete the current options $index from cache.
	 * @return array The current extension options.
	 */
	public static function _set_options_cache( $index = '', $new_options = null, $delete = false ) {

		if ( is_null( static::$options ) )
			static::init_options_cache();

		if ( isset( $new_options ) && $index ) {
			static::$options[ $index ] = $new_options;
		} elseif ( $delete ) {
			unset( static::$options[ $index ] );
		}

		return static::$options;
	}
}

/**
 * Class TSF_Extension_Manager\Stale_Extensions_Options_Cache.
 *
 * Caches the stale extension options. Used for updating and managing options.
 *
 * @since 1.3.0
 * @access private
 * @final
 */
final class Stale_Extensions_Options_Cache {
	use Construct_Core_Static_Final,
		Enclose_Core_Final;

	/**
	 * Holds the extension options.
	 *
	 * @since 1.3.0
	 *
	 * @param array $options
	 */
	private static $options = null;

	/**
	 * Initializes the options cache.
	 *
	 * @since 1.3.0
	 */
	private static function init_options_cache() {
		static::$options = (array) \get_option( TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS, [] );
	}

	/**
	 * Returns all the extension options from cache.
	 * Used internally to stack multiple extension options stacks.
	 *
	 * Also initializes the options cache, if not already.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @return array All extension options.
	 */
	public static function _get_options_cache() {

		if ( is_null( static::$options ) )
			static::init_options_cache();

		return static::$options;
	}

	/**
	 * Overrides current option stack with the new one.
	 * Note: you can get the previous set through `_get_options_cache()`.
	 *
	 * Also initializes the options cache, if not already.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @param string|int $index The option index that has to be changed.
	 * @param null|array $new_options The new options to set.
	 *        Should not have changed options from outside the current extension's scope.
	 * @param bool $delete If $new_options aren't set, but this is true, then
	 *        it will delete the current options $index from cache.
	 * @return array The current extension options.
	 */
	public static function _set_options_cache( $index = '', $new_options = null, $delete = false ) {

		if ( is_null( static::$options ) )
			static::init_options_cache();

		if ( isset( $new_options ) && $index ) {
			static::$options[ $index ] = $new_options;
		} elseif ( $delete ) {
			unset( static::$options[ $index ] );
		}

		return static::$options;
	}
}

/**
 * Holds options functionality for package TSF_Extension_Manager\Extension.
 *
 * @since 1.0.0
 * @access private
 */
trait Extension_Options {

	/**
	 * Current Extension index field. Likely equal to extension slug.
	 *
	 * @NOTE: Always set this directly in the constructor of the class.
	 *        Traits do not share class properties and thus properties hold their
	 *        value as if it were its user's class.
	 * @since 1.0.0
	 *
	 * @param string $o_index The current extension settings base index field.
	 */
	protected $o_index = '';

	/**
	 * Current Extension default options.
	 *
	 * If option key's value is not null, it will fall back to set option when
	 * $this->get_option()'s second parameter is not null either.
	 * @since 1.3.0
	 *
	 * @param array $o_defaults The default options.
	 */
	protected $o_defaults = [];

	/**
	 * Stale Extension default options.
	 *
	 * If option key's value is not null, it will fall back to set option when
	 * $this->get_option()'s second parameter is not null either.
	 * @since 1.3.0
	 *
	 * @param array $o_stale_defaults The default options.
	 */
	protected $o_stale_defaults = [];

	/**
	 * Loops through multidimensional keys and values to find the corresponding one.
	 *
	 * Expected not to go beyond 10 key depth.
	 * CAUTION: 2nd parameter is passed by reference and it will be annihilated.
	 *
	 * @since 1.3.0
	 *
	 * @param array|string $keys  The keys that collapse with $value. For performance
	 *                            benefits, the last value should be a string.
	 * @param array|string $value The values that might contain $keys' value.
	 *                            Passed by reference for huge performance improvement.
	 * @return mixed|null Null if not found. Value otherwise.
	 */
	final protected function get_mda_value( $keys, &$value ) {

		//= Because it's cast to array, the return will always be inside this loop.
		foreach ( (array) $keys as $k => $v ) {
			if ( is_array( $v ) ) {
				return isset( $value[ $k ] ) ? $this->get_mda_value( $v, $value[ $k ] ) : null;
			} else {
				if ( $k ) {
					return isset( $value[ $k ][ $v ] ) ? $value[ $k ][ $v ] : null;
				}

				return isset( $value[ $v ] ) ? $value[ $v ] : null;
			}
		}
	}

	/**
	 * Converts a single or sequential|associative array into a multidimensional array.
	 *
	 * satoma: "Single Array to Multidimensional Array"
	 *
	 * Example: '[ 0 => a, 1 => b, 3 => c ]';
	 * Becomes: [ a => [ b => [ c ] ];
	 *
	 * This function can also be found in class \TSF_Extension_Manager\Core.
	 *
	 * @NOTE Do not pass multidimensional arrays, as they will cause PHP errors.
	 *       Their values will be used as keys. Arrays can't be keys.
	 *
	 * @since 1.3.0
	 * @staticvar array $_b Maintains iteration and depth.
	 *
	 * @param array $a The single dimensional array.
	 * @return array Multidimensional array, where the values are the dimensional keys.
	 */
	final protected function satoma( array $a ) {

		static $_b;

		$_b = $a;

		if ( $_b ) {
			$last = array_shift( $a );

			if ( $a ) {
				$r = [];
				$r[ $last ] = $this->satoma( $a );
			} else {
				$r = $last;
			}
		}

		return $r;
	}

	/**
	 * Returns current extension options array based upon $o_index;
	 *
	 * @since 1.0.0
	 * @see $this->o_index The current options index.
	 *
	 * @return array Current extension options.
	 */
	final protected function get_extension_options() {

		$options = \TSF_Extension_Manager\Extensions_Options_Cache::_get_options_cache();

		if ( isset( $options[ $this->o_index ] ) ) {
			return $options[ $this->o_index ];
		} else {
			empty( $this->o_index ) and \the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to assign property TSF_Extension_Manager\Extension_Options->o_index.' );
		}

		return [];
	}

	/**
	 * Fetches current extension options.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 : Now listens to $this->o_defaults.
	 *
	 * @param string $option The Option name.
	 * @param mixed $default The fallback value if the option doesn't exist. Defaults to $this->o_defaults[ $option ].
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	final protected function get_option( $option, $default = null ) {

		if ( ! $option )
			return null;

		$options = $this->get_extension_options();

		if ( isset( $options[ $option ] ) )
			return $options[ $option ];

		if ( isset( $default ) )
			return $default;

		if ( isset( $this->o_defaults[ $option ] ) )
			return $this->o_defaults[ $option ];

		return null;
	}

	/**
	 * Fetches current extension options from multidimensional array.
	 *
	 * @since 1.3.0
	 *
	 * @param array $key The key that should collapse with the option.
	 * @param mixed $default The fallback value if the option doesn't exist.
	 *                       Defaults to the corrolated $this->o_defaults.
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	final protected function get_option_by_mda_key( array $key, $default = null ) {

		//= If the array is sequential, convert it to a multidimensional array.
		if ( array_values( $key ) === $key ) {
			$key = $this->satoma( $key );
		}

		$_ = $this->get_extension_options();
		$value = $this->get_mda_value( $key, $_ ) ?: $default;

		if ( isset( $value ) )
			return $value;

		if ( isset( $this->o_defaults ) ) {
			$_ = $this->o_defaults;
			return $this->get_mda_value( $key, $_ );
		}

		return null;
	}

	/**
	 * Updates TSFEM Extensions option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed $value The option value.
	 * @return bool True on success or the option is unchanged, false on failure.
	 */
	final protected function update_option( $option, $value ) {

		if ( ! $option || ! $this->o_index )
			return false;

		$options = $this->get_extension_options();

		//* If option is unchanged, return true.
		if ( isset( $options[ $option ] ) && $value === $options[ $option ] )
			return true;

		$options[ $option ] = $value;

		//* Prepare options cache.
		$c_options = \TSF_Extension_Manager\Extensions_Options_Cache::_get_options_cache();
		$c_options[ $this->o_index ] = $options;

		$success = \update_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $c_options );

		if ( $success ) {
			//* Update options cache on success.
			\TSF_Extension_Manager\Extensions_Options_Cache::_set_options_cache( $this->o_index, $options );
		}

		return $success;
	}

	/**
	 * Deletes current extension option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The Option name to delete.
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_option( $option ) {

		if ( ! $option || ! $this->o_index )
			return false;

		$options = $this->get_extension_options();

		//* If option is non existent, return true.
		if ( ! isset( $options[ $option ] ) )
			return true;

		unset( $options[ $option ] );

		//* Prepare options cache.
		$c_options = \TSF_Extension_Manager\Extensions_Options_Cache::_get_options_cache();
		$c_options[ $this->o_index ] = $options;

		$success = \update_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $c_options );

		if ( $success ) {
			//* Update options cache on success.
			\TSF_Extension_Manager\Extensions_Options_Cache::_set_options_cache( $this->o_index, $options );
		}

		return $success;
	}

	/**
	 * Deletes all of the current extension options.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_option_index() {

		if ( ! $this->o_index )
			return false;

		//* Prepare options cache.
		$c_options = \TSF_Extension_Manager\Extensions_Options_Cache::_get_options_cache();

		//* If index is non existent, return true.
		if ( ! isset( $c_options[ $this->o_index ] ) )
			return true;

		unset( $c_options[ $this->o_index ] );

		if ( [] === $c_options ) {
			$success = \delete_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS );
		} else {
			$success = \update_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $c_options );
		}

		if ( $success ) {
			//* Update options cache on success.
			\TSF_Extension_Manager\Extensions_Options_Cache::_set_options_cache( $this->o_index, null, true );
		}

		return $success;
	}

	/**
	 * Returns current stale extension options array based upon $o_index;
	 *
	 * @since 1.3.0
	 * @see $this->o_index The current stale options index.
	 *
	 * @return array Current extension options.
	 */
	final protected function get_stale_extension_options() {

		$options = \TSF_Extension_Manager\Stale_Extensions_Options_Cache::_get_options_cache();

		if ( isset( $options[ $this->o_index ] ) ) {
			return $options[ $this->o_index ];
		} else {
			empty( $this->o_index ) and \the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to assign property TSF_Extension_Manager\Extension_Options->o_index.' );
		}

		return [];
	}

	/**
	 * Fetches current stale extension options.
	 *
	 * @since 1.3.0
	 *
	 * @param string $option The Option name.
	 * @param mixed $default The fallback value if the option doesn't exist. Defaults to $this->o_stale_defaults[ $option ].
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	final protected function get_stale_option( $option, $default = null ) {

		if ( ! $option )
			return null;

		$options = $this->get_stale_extension_options();

		if ( isset( $options[ $option ] ) )
			return $options[ $option ];

		if ( isset( $default ) )
			return $default;

		if ( isset( $this->o_stale_defaults[ $option ] ) )
			return $this->o_stale_defaults[ $option ];

		return null;
	}

	/**
	 * Fetches current stale extension options from multidimensional array.
	 *
	 * @since 1.3.0
	 *
	 * @param array $key The key that should collapse with the option.
	 * @param mixed $default The fallback value if the option doesn't exist.
	 *                       Defaults to the corrolated $this->o_stale_defaults.
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	final protected function get_stale_option_by_mda_key( array $key, $default = null ) {

		//= If the array is sequential, convert it to a multidimensional array.
		if ( array_values( $key ) === $key ) {
			$key = $this->satoma( $key );
		}

		$_ = $this->get_stale_extension_options();
		$value = $this->get_mda_value( $key, $_ ) ?: $default;

		if ( isset( $value ) )
			return $value;

		if ( isset( $this->o_stale_defaults ) ) {
			$_ = $this->o_stale_defaults;
			return $this->get_mda_value( $key, $_ );
		}

		return null;
	}

	/**
	 * Updates stale extension options based on array by key.
	 *
	 * @since 1.3.0
	 *
	 * @param array $options The Single Dimensional options array with key.
	 * @return bool True on success or the stale option is unchanged, false on failure.
	 */
	final protected function update_stale_options_array_by_key( array $options ) {

		$k = key( $options );

		return $this->update_stale_option( $k, $options[ $k ] );
	}

	/**
	 * Updates TSFEM stale Extensions option.
	 *
	 * @since 1.3.0
	 *
	 * @param string $option The option name.
	 * @param mixed $value The option value.
	 * @return bool True on success or the option is unchanged, false on failure.
	 */
	final protected function update_stale_option( $option, $value ) {

		if ( ! $option || ! $this->o_index )
			return false;

		$options = $this->get_stale_extension_options();

		//* If option is unchanged, return true.
		if ( isset( $options[ $option ] ) && $value === $options[ $option ] )
			return true;

		$options[ $option ] = $value;

		//* Prepare options cache.
		$c_options = \TSF_Extension_Manager\Stale_Extensions_Options_Cache::_get_options_cache();
		$c_options[ $this->o_index ] = $options;

		$success = \update_option( TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS, $c_options, 'no' );

		if ( $success ) {
			//* Update options cache on success.
			\TSF_Extension_Manager\Stale_Extensions_Options_Cache::_set_options_cache( $this->o_index, $options );
		}

		return $success;
	}

	/**
	 * Deletes current stale extension option.
	 *
	 * @since 1.3.0
	 *
	 * @param string $option The Option name to delete.
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_stale_option( $option ) {

		if ( ! $option || ! $this->o_index )
			return false;

		$options = $this->get_stale_extension_options();

		//* If option is non existent, return true.
		if ( ! isset( $options[ $option ] ) )
			return true;

		unset( $options[ $option ] );

		//* Prepare options cache.
		$c_options = \TSF_Extension_Manager\Stale_Extensions_Options_Cache::_get_options_cache();
		$c_options[ $this->o_index ] = $options;

		if ( [] === $c_options ) {
			$success = \delete_option( TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS );
		} else {
			$success = \update_option( TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS, $c_options, 'no' );
		}

		if ( $success ) {
			//* Update options cache on success.
			\TSF_Extension_Manager\Stale_Extensions_Options_Cache::_set_options_cache( $this->o_index, $options );
		}

		return $success;
	}

	/**
	 * Deletes all of the current stale extension options.
	 *
	 * @since 1.3.0
	 *
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_stale_option_index() {

		if ( ! $this->o_index )
			return false;

		//* Prepare options cache.
		$c_options = \TSF_Extension_Manager\Stale_Extensions_Options_Cache::_get_options_cache();

		//* If index is non existent, return true.
		if ( ! isset( $c_options[ $this->o_index ] ) )
			return true;

		unset( $c_options[ $this->o_index ] );

		$success = \update_option( TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS, $c_options, 'no' );

		if ( $success ) {
			//* Update options cache on success.
			\TSF_Extension_Manager\Stale_Extensions_Options_Cache::_set_options_cache( $this->o_index, null, true );
		}

		return $success;
	}
}
