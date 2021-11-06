<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Traits
 */

namespace TSF_Extension_Manager\Extension\Local;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017-2021 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds schema packing callbacks for \TSF_Extension_Manager\Extension\Local\Settings.
 *
 * Note: This trait has dependencies!
 *
 * @since 1.0.0
 * @uses trait \TSF_Extension_Manager\Extension_Options
 * @uses class \TSF_Extension_Manager\SchemaPacker
 * @access private
 */
trait Schema_Packer {

	/**
	 * Determines whether php.ini 'serialize_precision' should be changed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function should_change_precision() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		$precision = \function_exists( 'ini_get' ) ? ini_get( 'serialize_precision' ) : null;

		//= -1 means it's optimized correctly. 7 to 14 would also do, actually.
		if ( isset( $precision ) && -1 !== (int) $precision )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Determines whether php.ini 'serialize_precision' is changeable.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function can_change_precision() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		if ( ! \function_exists( 'ini_get_all' ) )
			return $cache = false;

		$ini_all = ini_get_all();

		if ( empty( $ini_all['serialize_precision']['access'] ) )
			return $cache = false;

		$access = &$ini_all['serialize_precision']['access'];

		if ( INI_USER & $access || INI_ALL & $access )
			return $cache = true;

		return $cache = false;
	}

	/**
	 * Sets php.ini serialize_precision to correct value, i.e. -1.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $reset Whether to reset to previous serialize_precision value.
	 */
	private function correct_precision( $reset = false ) {

		static $prev = null;

		// phpcs:disable, WordPress.PHP.IniSet.Risky -- cPanel needs to fix this...
		// @TODO make case for feature change.

		if ( $this->should_change_precision() && $this->can_change_precision() ) {
			if ( $reset ) {
				isset( $prev ) and ini_set( 'serialize_precision', $prev );
			} else {
				$prev = ini_get( 'serialize_precision' );
				ini_set( 'serialize_precision', '-1' );
			}
		}

		// phpcs:enable, WordPress.PHP.IniSet.Risky -- cPanel needs to fix this...
	}

	/**
	 * Resets php.ini serialize_precision to previous value.
	 *
	 * @since 1.0.0
	 */
	private function reset_precision() {
		$this->correct_precision( true );
	}

	/**
	 * Removes scheme from input URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The URL with scheme.
	 * @return string The URL without scheme.
	 */
	protected function remove_scheme( $url ) {
		return str_ireplace( [ 'https://', 'http://' ], '', \esc_url_raw( $url, [ 'https', 'http' ] ) );
	}

	/**
	 * Returns the current Local schema.
	 *
	 * @since 1.0.0
	 *
	 * @return object JSON schema from file.
	 */
	protected function get_schema() {

		$schema_file = TSFEM_E_LOCAL_DIR_PATH . 'lib' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'schema.json';
		$timeout     = stream_context_create( [ 'http' => [ 'timeout' => 3 ] ] );

		// phpcs:ignore, WordPress.WP.AlternativeFunctions, TSF.Performance.Functions -- This isn't a remote call; required.
		return json_decode( file_get_contents( $schema_file, false, $timeout ) );
	}

	/**
	 * Packs and parses data through the Schema packer.
	 *
	 * @since 1.0.0
	 * @see $this->get_schema()
	 * @uses \TSF_Extension_Manager\SchemaPacker
	 *
	 * @param array $data   The data to pack.
	 * @param bool  $pretty Whether to output prettified JSON
	 * @return string|null The JSON data. Null on failure.
	 */
	protected function pack_data( array $data, $pretty = false ) {

		$schema = $this->get_schema();

		if ( ! \is_object( $schema ) )
			return '';

		$this->correct_precision();

		$packer = new \TSF_Extension_Manager\SchemaPacker( $data, $schema );

		$count = isset( $data['department']['count'] ) ? $data['department']['count'] : 0;

		if ( $count ) {
			$_collection = &$packer->_collector();

			//= Get root/main department first.
			$packer->_iterate_base();
			$_collection = $packer->_pack();

			// Main department was disabled...
			if ( ! $_collection )
				$_collection = (object) [];

			if ( $count > 1 ) {
				//= Get sub departments.
				$_collection->department = [];
				for ( $i = 2; $i <= $count; $i++ ) {
					$packer->_iterate_base();

					/**
					 * Gets sub-department data, and store it inclusively.
					 * i.e. Inclusively for homepage for $_collection.
					 */
					$_data = $packer->_pack();

					if ( isset( $_data ) )
						$_collection->department[] = $_data;
				}
				if ( [] === $_collection->department )
					unset( $_collection->department );
			}
		}

		$_data = $packer->_get();
		if ( ! $_data ) {
			$output = null;
			goto reset;
		}

		$options  = JSON_UNESCAPED_SLASHES;
		$options |= $pretty ? JSON_PRETTY_PRINT : 0;

		$output = json_encode( $_data, $options );

		reset:;
		$this->reset_precision();

		return $output;
	}

	/**
	 * Parses and stores all packed data.
	 * It stores the data by URL.
	 *
	 * Note: It uses stale options and saves new options. Therefore, it makes two
	 *       consecutive database calls.
	 *
	 * @since 1.0.0
	 *
	 * @see $this->save_packed_data();
	 * @see $this->store_packed_data();
	 * @uses \TSF_Extension_Manager\SchemaPacker
	 * @uses \TSF_Extension_Manager\Extension_Options
	 */
	protected function process_all_stored_data() {

		$data   = $this->get_stale_extension_options();
		$schema = $this->get_schema();

		$this->correct_precision();

		$packer = new \TSF_Extension_Manager\SchemaPacker( $data, $schema );

		$count    = isset( $data['department']['count'] ) ? $data['department']['count'] : 0;
		$main_url = isset( $data['department'][1]['url'] ) ? $this->remove_scheme( $data['department'][1]['url'] ) : 1;

		$json_options = JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION;

		if ( $count ) {
			$_collection = &$packer->_collector();

			//= Get root/main department first.
			$packer->_iterate_base();
			$_collection = $packer->_pack();

			// Main department was disabled...
			if ( ! $_collection ) {
				$_collection = (object) [];
			} else {
				/**
				 * Store root department for URL.
				 * Note, if it's the home URL, it will be overwritten out of this loop.
				 */
				$_data = $_collection;
				$this->store_packed_data( $main_url, json_encode( $_data, $json_options ) );
			}

			if ( $count > 1 ) {
				//= Get sub departments.
				$_collection->department = [];
				for ( $i = 2; $i <= $count; $i++ ) {
					$packer->_iterate_base();

					/**
					 * Generates ID out of URL if set, so it can be extracted at
					 * run-time to test matched URLs.
					 *
					 * Alternatively, it creates it from the iteration.
					 */
					$id = isset( $data['department'][ $i ]['url'] ) ? $this->remove_scheme( $data['department'][ $i ]['url'] ) : $i;

					/**
					 * Gets sub-department data, and store it separately and inclusively.
					 * Separately in database through store_packed_data().
					 * Inclusively for homepage in $_collection.
					 */
					$_data = $packer->_pack();
					if ( isset( $_data ) ) {
						$this->store_packed_data( $id, json_encode( $_data, $json_options ) );
						$_collection->department[] = $_data;
					}
				}
				if ( [] === $_collection->department )
					unset( $_collection->department );
			}
		}

		$_data = $packer->_get();
		if ( $_data )
			$this->store_packed_data( $this->remove_scheme( \get_home_url() ), json_encode( $_data, $json_options ) );

		$this->reset_precision();

		$this->save_packed_data();
	}

	/**
	 * Saves packed data.
	 *
	 * @since 1.0.0
	 * @see $this->store_packed_data();
	 *
	 * @return bool True on success, false on failure.
	 */
	protected function save_packed_data() {
		return $this->store_packed_data( '', '', true );
	}

	/**
	 * Stores and saves packed data.
	 *
	 * @since 1.0.0
	 * @see $this->store_packed_data();
	 *
	 * @param string|int $id   Either the URL or ID.
	 * @param string     $data The data to store.
	 * @param bool       $save Whether to store save the stored output.
	 * @return bool|void : {
	 *   Saving:  True on success, false on failure.
	 *   Storing: void.
	 * }
	 */
	protected function store_packed_data( $id, $data, $save = false ) {

		static $_d = [];

		if ( $save )
			return $this->update_option( 'packed_data', $_d );

		if ( $data )
			$_d[ $id ] = $data;
	}
}
