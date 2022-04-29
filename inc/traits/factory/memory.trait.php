<?php
/**
 * @package TSF_Extension_Manager\Traits\Factory
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds memory cache for the Memory trait.
 * Ironically.
 *
 * @since 1.5.0
 * @access private
 */
class Memory_Cache {

	/**
	 * Determines if the memory limit has been increased.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	final public static function increased_available_memory() {
		return \TSF_Extension_Manager\has_run( __METHOD__ );
	}

	/**
	 * Returns the memory limit in bytes.
	 *
	 * @since 1.5.0
	 * @source http://php.net/manual/en/function.ini-get.php
	 *
	 * @return int <bytes> memory limit.
	 */
	final public static function get_memory_limit_in_bytes() {

		static $limit;

		if ( $limit )
			return $limit;

		$_limit     = trim( ini_get( 'memory_limit' ) );
		$quantifier = strtolower( $_limit[ \strlen( $_limit ) - 1 ] );
		$val        = filter_var( $_limit, FILTER_SANITIZE_NUMBER_INT );

		switch ( $quantifier ) {
			case 'g':
				$val *= 1024;
				// No break. Run next calculation.
			case 'm':
				$val *= 1024;
				// No break. Run next calculation.
			case 'k':
				$val *= 1024;
				break;
		}

		return $limit = $val;
	}
}

/**
 * Holds memory methods.
 *
 * @since 1.5.0
 * @access private
 */
trait Memory {

	/**
	 * Increases the memory limit to maximum allowed size.
	 *
	 * @since 1.5.0
	 * @since 2.3.1 Now uses wp_raise_memory_limit()
	 * @uses Memory_Cache::increased_available_memory()
	 */
	final protected function increase_available_memory() {
		Memory_Cache::increased_available_memory()
			or \wp_raise_memory_limit( 'tsfem' );
	}

	/**
	 * Returns the memory limit in bytes.
	 *
	 * @since 1.5.0
	 * @uses Memory_Cache::get_memory_limit_in_bytes()
	 *
	 * @return int <bytes> memory limit.
	 */
	final protected function get_memory_limit_in_bytes() {
		return Memory_Cache::get_memory_limit_in_bytes();
	}

	/**
	 * Determines if at least $bytes is available.
	 *
	 * @since 1.5.0
	 *
	 * @param int <bytes> $bytes The number of bytes that should be available.
	 * @return bool
	 */
	final protected function has_free_memory( $bytes ) {
		return $this->get_memory_limit_in_bytes() - memory_get_usage( true ) > $bytes;
	}
}
