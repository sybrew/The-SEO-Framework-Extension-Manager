<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

// phpcs:disable, Generic.Files.OneObjectStructurePerFile.MultipleFound -- Class and trait intertwine for cache abstraction.

/**
 * Class TSF_Extension_Manager\Extensions_Post_Meta_Cache.
 *
 * Caches the extension meta. Used for updating and managing meta.
 *
 * @since 1.5.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Extensions_Post_Meta_Cache {
	use Construct_Core_Static_Final,
		Enclose_Core_Final;

	/**
	 * Holds the extension meta.
	 *
	 * @since 1.5.0
	 *
	 * @var array $meta : {
	 *    'id' => [ 'key' => 'value' ],
	 * }
	 */
	private static $meta = [];

	/**
	 * Initializes the meta cache.
	 *
	 * @since 1.5.0
	 *
	 * @param int $id The Post ID.
	 * @return void
	 */
	private static function init_meta_cache( $id ) {
		static::$meta[ $id ] = (array) unserialize( // phpcs:ignore -- Security check OK, full serialization happened prior, can't execute sub-items.
			\get_post_meta( $id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, true ) ?: serialize( [] ) // phpcs:ignore -- serializing simple array.
		);
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
	 * @param int $id The Post ID.
	 * @return array Every extension's meta for ID.
	 */
	public static function _get_meta_cache( $id ) {

		if ( ! isset( static::$meta[ $id ] ) )
			static::init_meta_cache( $id );

		return static::$meta[ $id ];
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
	 * @param int        $id       The Post ID.
	 * @param string     $index    The meta index that has to be changed.
	 * @param null|array $new_meta The new meta to set.
	 *                             Should not have changed meta from outside the current extension's scope.
	 * @param bool       $delete   If $new_meta aren't set, but this is true, then
	 *                             it will delete the current meta $index from cache.
	 * @return array The updated extension meta for every extension for ID.
	 */
	public static function _set_meta_cache( $id, $index, $new_meta = null, $delete = false ) {

		if ( ! isset( static::$meta[ $id ] ) )
			static::init_meta_cache( $id );

		if ( isset( $new_meta ) && $index ) {
			static::$meta[ $id ][ $index ] = $new_meta;
		} elseif ( $delete ) {
			unset( static::$meta[ $id ][ $index ] );
		}

		return static::$meta[ $id ];
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
	 *
	 * @since 1.5.0
	 * @var string $pm_index The current extension meta base index field.
	 */
	protected $pm_index = '';

	/**
	 * Holds the post ID.
	 *
	 * @since 1.5.0
	 * @var int|null $id
	 */
	protected $pm_id = null;

	/**
	 * Current Extension default meta.
	 *
	 * If meta key's value is not null, it will fall back to set meta when
	 * $this->get_post_meta()'s second parameter is not null either.
	 *
	 * @since 1.5.0
	 * @var array $pm_defaults The default meta.
	 */
	protected $pm_defaults = [];

	/**
	 * Flag for initialization.
	 *
	 * @since 1.5.0
	 * @var bool $pm_initialized Whether the meta is initialized.
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
		$this->pm_id          = $id;
		$this->pm_initialized = true;
	}

	/**
	 * Resets the active Post ID.
	 *
	 * @since 1.5.0
	 */
	final protected function reset_extension_post_meta_id() {
		$this->set_extension_post_meta_id( \the_seo_framework()->get_the_real_ID() );
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

		$meta = Extensions_Post_Meta_Cache::_get_meta_cache( $this->pm_id );

		if ( isset( $meta[ $this->pm_index ] ) ) {
			return $meta[ $this->pm_index ];
		} else {
			empty( $this->pm_index )
				and \the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to assign property <code>\TSF_Extension_Manager\Extension_Post_Meta::$pm_index</code>.' );
		}

		return [];
	}

	/**
	 * Fetches current extension meta.
	 *
	 * @since 1.5.0
	 *
	 * @param string $key     The meta name.
	 * @param mixed  $default The fallback value if the meta doesn't exist. Defaults to $this->pm_defaults[ $meta ].
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
	 * This data may not be JSON encoded; but if so, the quotes need to be escaped or slashed.
	 *
	 * @since 1.5.0
	 * @since 2.4.1 No longer causes data loss during deserialization with quotes.
	 *
	 * @param string $key   The meta name.
	 * @param mixed  $value The meta value.
	 * @return bool True on success or the meta is unchanged, false on failure.
	 */
	final protected function update_post_meta( $key, $value ) {

		if ( ! $key || ! $this->pm_index )
			return false;

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		$meta = $this->get_extension_post_meta();

		// If meta is unchanged, return true.
		if ( isset( $meta[ $key ] ) && $value === $meta[ $key ] )
			return true;

		$meta[ $key ] = $value;

		// Prepare meta cache.
		$c_meta                    = Extensions_Post_Meta_Cache::_get_meta_cache( $this->pm_id );
		$c_meta[ $this->pm_index ] = $meta;

		// Addslashes here, so WordPress doesn't unslash it, whereafter unserialization fails.
		// phpcs:ignore -- Security check OK, this is a serialization of an array, sub-unserialization can't happen.
		$success = \update_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, addslashes( serialize( $c_meta ) ) );

		if ( $success ) {
			// Update meta cache on success.
			Extensions_Post_Meta_Cache::_set_meta_cache( $this->pm_id, $this->pm_index, $meta );
		}

		return $success;
	}

	/**
	 * Deletes current extension meta.
	 *
	 * @since 1.5.0
	 * @since 2.4.1 No longer causes data loss during deserialization with quotes.
	 *
	 * @param string $key The meta name to delete.
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_post_meta( $key ) {

		if ( ! $key || ! $this->pm_index )
			return false;

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		$meta = $this->get_extension_post_meta();

		// If meta is non existent, return true.
		if ( ! isset( $meta[ $key ] ) )
			return true;

		unset( $meta[ $key ] );

		// Prepare meta cache.
		$c_meta                    = Extensions_Post_Meta_Cache::_get_meta_cache( $this->pm_id );
		$c_meta[ $this->pm_index ] = $meta;

		// Addslashes here, so WordPress doesn't unslash it, whereafter unserialization fails.
		// phpcs:ignore -- Security check OK, this is a serialization of an array, sub-unserialization can't happen.
		$success = \update_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, addslashes( serialize( $c_meta ) ) );

		if ( $success ) {
			// Update meta cache on success.
			Extensions_Post_Meta_Cache::_set_meta_cache( $this->pm_id, $this->pm_index, $meta );
		}

		return $success;
	}

	/**
	 * Deletes all of the current extension meta.
	 *
	 * @since 1.5.0
	 * @since 2.4.1 No longer causes data loss during deserialization with quotes.
	 *
	 * @return boolean True on success; false on failure.
	 */
	final protected function delete_post_meta_index() {

		if ( ! $this->pm_index )
			return false;

		if ( ! $this->pm_initialized ) $this->reset_extension_post_meta_id();

		// Prepare meta cache.
		$c_meta = Extensions_Post_Meta_Cache::_get_meta_cache( $this->pm_id );

		// If index is non existent, return true.
		if ( ! isset( $c_meta[ $this->pm_index ] ) )
			return true;

		unset( $c_meta[ $this->pm_index ] );

		if ( [] === $c_meta ) {
			$success = \delete_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META );
		} else {
			// phpcs:ignore -- Security check OK, this is a serialization of an array, sub-unserialization can't happen.
			$success = \update_post_meta( $this->pm_id, TSF_EXTENSION_MANAGER_EXTENSION_POST_META, addslashes( serialize( $c_meta ) ) );
		}

		if ( $success ) {
			// Update meta cache on success.
			Extensions_Post_Meta_Cache::_set_meta_cache( $this->pm_id, $this->pm_index, null, true );
		}

		return $success;
	}
}
