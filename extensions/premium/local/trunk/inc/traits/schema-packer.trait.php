<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Traits
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

/**
 * Local extension for The SEO Framework
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
	 * Removes scheme from input URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The URL with scheme.
	 * @return string The URL without scheme.
	 */
	protected function remove_scheme( $url ) {
		return str_ireplace( [ 'http://', 'https://' ], '', \esc_url( $url, [ 'http', 'https' ] ) );
	}

	/**
	 * Returns the current Local SEO schema.
	 *
	 * @since 1.0.0
	 *
	 * @return object JSON schema from file.
	 */
	protected function get_schema() {

		$schema_file = TSFEM_E_LOCAL_DIR_PATH . 'lib' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'schema.json';
		$timeout = stream_context_create( [ 'http' => [ 'timeout' => 3 ] ] );

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
	 * @return string The JSON data.
	 */
	protected function pack_data( array $data, $pretty = false ) {

		$schema = $this->get_schema();

		if ( ! is_object( $schema ) )
			return '';

		$packer = new \TSF_Extension_Manager\SchemaPacker( $data, $schema );

		$count = isset( $data['department']['count'] ) ? $data['department']['count'] : 0;
		$main_url = isset( $data['department'][1]['url'] ) ? $data['department'][1]['url'] : null;

		if ( $count && $main_url ) {
			$_collection = &$packer->_collector();

			//= Get root/main department first.
			$packer->_iterate_base();
			$_collection = $packer->_pack();

			if ( $count > 1 ) {
				//= Get sub departments.
				$_collection->department = [];
				for ( $i = 2; $i <= $count; $i++ ) {
					$packer->_iterate_base();
					$url = isset( $data['department'][ $i ]['url'] ) ? $data['department'][ $i ]['url'] : null;
					if ( $url ) {
						/**
						 * Gets sub-department data, and store it inclusively.
						 * i.e. Inclusively for homepage for $_collection.
						 */
						$_data = $packer->_pack();
	 					if ( isset( $_data ) ) {
	 						$_collection->department[] = $_data;
	 					}
					}
				}
				if ( [] === $_collection->department )
					unset( $_collection->department );
			}
		}

		$_data = $packer->_get();
		if ( ! $_data )
			return null;

		$options = JSON_UNESCAPED_SLASHES;
		$options |= $pretty ? JSON_PRETTY_PRINT : 0;

		return json_encode( $_data, $options );
	}

	/**
	 * Parses and stores all packed data.
	 * It stores the data by URL.
	 *
	 * note: It uses stale options and saves new options. Therefore, it makes two
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

		$data = $this->get_stale_extension_options();
		$schema = $this->get_schema();

		$packer = new \TSF_Extension_Manager\SchemaPacker( $data, $schema );

		$count = isset( $data['department']['count'] ) ? $data['department']['count'] : 0;
		$main_url = isset( $data['department'][1]['url'] ) ? $data['department'][1]['url'] : 1;

		$json_options = JSON_UNESCAPED_SLASHES;

		if ( $count ) {
			$_collection = &$packer->_collector();

			//= Get root/main department first.
			$packer->_iterate_base();
			$_collection = $packer->_pack();
			/**
			 * Store root department for URL.
			 * Note, if it's the home URL, it will be overwritten out of this loop.
			 */
			$_data = $_collection;
			$this->store_packed_data( $main_url, json_encode( $_data, $json_options ) );

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
					$id = isset( $data['department'][ $i ]['url'] ) ? $data['department'][ $i ]['url'] : $i;

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
			$this->store_packed_data( \get_home_url(), json_encode( $_data, $json_options ) );

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
		return $this->store_packed_data( '', [], true );
	}

	/**
	 * Stores and saves packed data.
	 *
	 * @since 1.0.0
	 * @see $this->store_packed_data();
	 * @staticvar array $_d The stored data.
	 *
	 * @return bool|void : {
	 *   Saving:  True on success, false on failure.
	 *   Storing: void.
	 * }
	 */
	protected function store_packed_data( $url, $data, $save = false ) {

		static $_d = [];

		if ( $save )
			return $this->update_option( 'packed_data', $_d );

		$url = $this->remove_scheme( $url );
		$_d[ $url ] = $data;
	}
}
