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
 * Class TSF_Extension_Manager\Extensions_Options_Cache.
 *
 * Caches the extension options. Used for updating and managing options.
 *
 * @since 1.0.0
 * @access private
 *
 * @final Please don't extend this.
 */
final class Extensions_Options_Cache {
	use Construct_Core_Static_Final, Enclose_Core_Final;

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
		static::$options = (array) get_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, array() );
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
	 * @return array The current extension options.
	 */
	public static function _get_options_cache() {

		if ( is_null( static::$options ) )
			static::init_options_cache();

		return static::$options;
	}

	/**
	 * Overrides current option stack with the new one.
	 * Note: you can get the previous set through `TSF_Extension_Manager\_get_e_options_cache()`.
	 *
	 * Also initializes the options cache, if not already.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string|int $index The option index that has to be changed.
	 *        When int -1, it will override all options.
	 * @param null|array $new_options The new options to set.
	 *        Should not have changed options from outside the current extension's scope.
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
 * Holds option functions for package TSF_Extension_Manager_Extension.
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
	 * Returns current extension options array based upon $o_index;
	 *
	 * @since 1.0.0
	 * @see $this->o_index The current options index.
	 *
	 * @return array Current extension options.
	 */
	final protected function get_all_options() {

		$options = Extensions_Options_Cache::_get_options_cache();

		if ( isset( $options[ $this->o_index ] ) ) {
			return $options[ $this->o_index ];
		} else {
			empty( $this->o_index ) and the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to assign property TSF_Extension_Manager\Extension_Options->o_index.' );
		}

		return array();
	}

	/**
	 * Fetches current extension options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The Option name.
	 * @param mixed $default The fallback value if the option doesn't exist.
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	final protected function get_option( $option, $default = null ) {

		if ( ! $option )
			return null;

		$options = $this->get_all_options();

		return isset( $options[ $option ] ) ? $options[ $option ] : $default;
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

		$options = $this->get_all_options();

		//* If option is unchanged, return true.
		if ( isset( $options[ $option ] ) && $value === $options[ $option ] )
			return true;

		$options[ $option ] = $value;

		//* Prepare options cache.
		$c_options = Extensions_Options_Cache::_get_options_cache();
		$c_options[ $this->o_index ] = $options;

		$success = update_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $c_options );

		if ( $success ) {
			//* Update options cache on success.
			Extensions_Options_Cache::_set_options_cache( $this->o_index, $options );
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

		$options = $this->get_all_options();

		//* If option is non existent, return true.
		if ( ! isset( $options[ $option ] ) )
			return true;

		unset( $options[ $option ] );

		//* Prepare options cache.
		$c_options = Extensions_Options_Cache::_get_options_cache();
		$c_options[ $this->o_index ] = $options;

		$success = update_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $c_options );

		if ( $success ) {
			//* Update options cache on success.
			Extensions_Options_Cache::_set_options_cache( $this->o_index, $options );
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
		$c_options = Extensions_Options_Cache::_get_options_cache();

		//* If index is non existent, return true.
		if ( ! isset( $c_options[ $this->o_index ] ) )
			return true;

		unset( $c_options[ $this->o_index ] );

		$success = update_option( TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $c_options );

		if ( $success ) {
			//* Update options cache on success.
			Extensions_Options_Cache::_set_options_cache( $this->o_index, null, true );
		}

		return $success;
	}
}
