<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extensions_Post_Meta_Cache.
 *
 * Caches the extension meta. Used for updating and managing meta.
 *
 * @since 1.5.0
 * @access private
 * @final
 */
final class Extensions_Post_Meta_Cache {
	use Construct_Core_Static_Final,
		Enclose_Core_Final;

	/**
	 * Hols the post ID.
	 *
	 * @since 1.5.0
	 *
	 * @param int|null $id
	 */
	private static $id = null;

	/**
	 * Holds the extension meta.
	 *
	 * @since 1.5.0
	 *
	 * @param array|null $meta
	 */
	private static $meta = null;

	/**
	 * Initializes the meta cache.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	private static function init_meta_cache() {

		if ( is_null( static::$id ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'No ID is set.' );
			return;
		}

		static::$meta = (array) \get_post_meta( static::$id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, [] );
	}

	/**
	 * Sets the active ID.
	 *
	 * @since 1.5.0
	 *
	 * @param int $id The ID.
	 */
	public static function set_id( $id ) {
		static::$id = (int) $id;
	}

	/**
	 * Returns all the extension meta from cache.
	 * Used internally to stack multiple extension meta stacks.
	 *
	 * Also initializes the meta cache, if not already.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @return array All extension meta.
	 */
	public static function _get_meta_cache() {

		if ( is_null( static::$meta ) )
			static::init_meta_cache();

		return static::$meta;
	}

	/**
	 * Overrides current meta stack with the new one.
	 * Note: you can get the previous set through `_get_meta_cache()`.
	 *
	 * Also initializes the meta cache, if not already.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param string|int $index The meta index that has to be changed.
	 * @param null|array $new_meta The new meta to set.
	 *        Should not have changed meta from outside the current extension's scope.
	 * @param bool $delete If $new_meta aren't set, but this is true, then
	 *        it will delete the current meta $index from cache.
	 * @return array The current extension meta.
	 */
	public static function _set_meta_cache( $index = '', $new_meta = null, $delete = false ) {

		if ( is_null( static::$meta ) )
			static::init_meta_cache();

		if ( isset( $new_meta ) && $index ) {
			static::$meta[ $index ] = $new_meta;
		} elseif ( $delete ) {
			unset( static::$meta[ $index ] );
		}

		return static::$meta;
	}
}

/**
 * Holds post meta functionality for package TSF_Extension_Manager\Extension.
 *
 * @since 1.5.0
 * @access private
 */
trait Extension_Post_Meta {

	/**
	 * Current Extension meta index field. Likely equal to extension slug.
	 *
	 * @NOTE: Always set this directly in the constructor of the class.
	 *        Traits do not share class properties and thus properties hold their
	 *        value as if it were its user's class.
	 * @since 1.5.0
	 *
	 * @param string $pm_index The current extension meta base index field.
	 */
	protected $pm_index = '';

	/**
	 * Current Extension default meta.
	 *
	 * If meta key's value is not null, it will fall back to set meta when
	 * $this->get_post_meta()'s second parameter is not null either.
	 * @since 1.5.0
	 *
	 * @param array $pm_defaults The default meta.
	 */
	protected $pm_defaults = [];

	/**
	 * Flag for initialization.
	 *
	 * @since 1.5.0
	 *
	 * @param bool $pm_initialized Whether the meta is initialized.
	 */
	protected $pm_initialized = false;

	/**
	 * Sets the active Post ID.
	 *
	 * @since 1.5.0
	 *
	 * @param int $id The ID.
	 */
	final protected function set_extension_post_meta_id( $id ) {
		\TSF_Extension_Manager\Extensions_Post_Meta_Cache::set_id( $id );
		$this->pm_initialized = true;
	}

	/**
	 * Resets the active Post ID.
	 *
	 * @since 1.5.0
	 */
	final protected function reset_extension_post_meta_id() {
		\TSF_Extension_Manager\Extensions_Post_Meta_Cache::set_id( \the_seo_framework()->get_the_real_ID() );
		$this->pm_initialized = true;
	}

	/**
	 * Returns current extension meta array based upon $pm_index;
	 *
	 * @since 1.5.0
	 * @see $this->pm_index The current meta index.
	 *
	 * @return array Current extension meta.
	 */
	final protected function get_extension_post_meta() {

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		$meta = \TSF_Extension_Manager\Extensions_Post_Meta_Cache::_get_meta_cache();

		if ( isset( $meta[ $this->pm_index ] ) ) {
			return $meta[ $this->pm_index ];
		} else {
			empty( $this->pm_index ) and \the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to assign property TSF_Extension_Manager\Extension_Post_Meta->pm_index.' );
		}

		return [];
	}

	/**
	 * Fetches current extension meta.
	 *
	 * @since 1.5.0
	 * @since 1.2.0 : Now listens to $this->pm_defaults.
	 *
	 * @param string $key The meta name.
	 * @param mixed $default The fallback value if the meta doesn't exist. Defaults to $this->pm_defaults[ $meta ].
	 * @return mixed The meta value if exists. Otherwise $default.
	 */
	final protected function get_post_meta( $key, $default = null ) {

		if ( ! $key )
			return null;

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		$meta = $this->get_extension_post_meta();

		if ( isset( $meta[ $key ] ) )
			return $meta[ $key ];

		if ( isset( $default ) )
			return $default;

		if ( isset( $this->pm_defaults[ $key ] ) )
			return $this->pm_defaults[ $key ];

		return null;
	}

	/**
	 * Updates TSFEM Extensions meta.
	 *
	 * @since 1.5.0
	 *
	 * @param string $key The meta name.
	 * @param mixed $value The meta value.
	 * @return bool True on success or the meta is unchanged, false on failure.
	 */
	final protected function update_post_meta( $key, $value ) {

		if ( ! $key || ! $this->pm_index )
			return false;

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		$meta = $this->get_extension_post_meta();

		//* If meta is unchanged, return true.
		if ( isset( $meta[ $key ] ) && $value === $meta[ $key ] )
			return true;

		$meta[ $key ] = $value;

		//* Prepare meta cache.
		$c_meta = \TSF_Extension_Manager\Extensions_Post_Meta_Cache::_get_meta_cache();
		$c_meta[ $this->pm_index ] = $meta;

		$success = \update_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, $c_meta );

		if ( $success ) {
			//* Update meta cache on success.
			\TSF_Extension_Manager\Extensions_Post_Meta_Cache::_set_meta_cache( $this->pm_index, $meta );
		}

		return $success;
	}

	/**
	 * Deletes current extension meta.
	 *
	 * @since 1.5.0
	 *
	 * @param string $key The meta name to delete.
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_post_meta( $key ) {

		if ( ! $key || ! $this->pm_index )
			return false;

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		$meta = $this->get_extension_post_meta();

		//* If meta is non existent, return true.
		if ( ! isset( $meta[ $key ] ) )
			return true;

		unset( $meta[ $key ] );

		//* Prepare meta cache.
		$c_meta = \TSF_Extension_Manager\Extensions_Post_Meta_Cache::_get_meta_cache();
		$c_meta[ $this->pm_index ] = $meta;

		$success = \update_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, $c_meta );

		if ( $success ) {
			//* Update meta cache on success.
			\TSF_Extension_Manager\Extensions_Post_Meta_Cache::_set_meta_cache( $this->pm_index, $meta );
		}

		return $success;
	}

	/**
	 * Deletes all of the current extension meta.
	 *
	 * @since 1.5.0
	 *
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_post_meta_index() {

		if ( ! $this->pm_index )
			return false;

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		//* Prepare meta cache.
		$c_meta = \TSF_Extension_Manager\Extensions_Post_Meta_Cache::_get_meta_cache();

		//* If index is non existent, return true.
		if ( ! isset( $c_meta[ $this->pm_index ] ) )
			return true;

		unset( $c_meta[ $this->pm_index ] );

		if ( [] === $c_meta ) {
			$success = \delete_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META );
		} else {
			$success = \update_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, $c_meta );
		}

		if ( $success ) {
			//* Update meta cache on success.
			\TSF_Extension_Manager\Extensions_Post_Meta_Cache::_set_meta_cache( $this->pm_index, null, true );
		}

		return $success;
	}
}
