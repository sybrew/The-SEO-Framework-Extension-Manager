<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Logger
 */

namespace TSF_Extension_Manager\Extension\Transport\Logger;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * Copyright (C) 2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Transport\Logger\Server
 *
 * Holds extension logging functionality.
 * Instantly sets up server when called!
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 * @final
 */
final class Store {

	/**
	 * @since 1.0.0
	 * @var string $id The unique store identifier.
	 */
	public $id;
	// public immutable $id; // :(

	/**
	 * @since 1.0.0
	 * @var array $store The current store. Shared across instances.
	 */
	private static $store = [];

	/**
	 * @since 1.0.0
	 * @param string $id The unique store identifier.
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Stores data.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data The data to store
	 */
	public function store( $data ) {
		static::$store[ $this->id ][ uniqid( hrtime( true ), true ) ] = $data;
	}

	/**
	 * Retrieves store data.
	 *
	 * @since 1.0.0
	 *
	 * @return array The current store.
	 */
	public function get_store() {
		return static::$store[ $this->id ] ?? [];
	}

	/**
	 * Clears current store and returns what's been cleared.
	 *
	 * @since 1.0.0
	 *
	 * @return array The current store.
	 */
	public function get_flush_store() {
		$store = $this->get_store();
		$this->clear_store();
		return $store;
	}

	/**
	 * Clears store.
	 *
	 * @param int $length The allowed array length of the store. -1 is unlimited.
	 */
	public function clear_store( $length = -1 ) {
		if ( -1 === $length ) {
			unset( static::$store[ $this->id ] );
		} else {
			array_splice( static::$store[ $this->id ], 0, -$length );
		}
	}
}
