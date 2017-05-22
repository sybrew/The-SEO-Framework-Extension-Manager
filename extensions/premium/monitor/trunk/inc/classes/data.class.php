<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Monitor\Data
 */
namespace TSF_Extension_Manager\Extension\Monitor;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Monitor extension for The SEO Framework
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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use \TSF_Extension_Manager\Construct_Core_Once_Interface as Construct_Core_Once_Interface;

/**
 * Class TSF_Extension_Manager\Extension\Monitor\Data
 *
 * Holds extension data methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 101xxxx
 */
class Data {
	use Enclose_Stray_Private, Construct_Core_Once_Interface;

	/**
	 * Constructor. Verifies integrity.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . ( \is_admin() ? '\\Admin' : '\\Front' );
		$this instanceof $that or \wp_die( -1 );

	}

	/**
	 * Returns Monitor Data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The monitor data type. Accepts 'issue' and 'stats'.
	 * @param mixed $default The fallback data to return if no data is found.
	 * @return array|mixed The found data.
	 */
	protected function get_data( $type, $default = null ) {

		/**
		 * Return null if this is the first run; to eliminate duplicated calls
		 * to the API server. Which would otherwise return "not found" data anyway.
		 */
		if ( $this->get_option( 'monitor_installing', false ) ) {
			$this->set_installing_site( false );
			return null;
		}
		/**
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$data = $this->get_option( $type, [] );

		if ( empty( $data ) )
			$data = $this->get_remote_data( $type );

		return empty( $data ) ? $default : $data;
	}

	/**
	 * Returns Monitor Data fetched externally from the API server.
	 * If no locally stored data is found, new data gets fetched.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The monitor data type. Accepts 'issue' and 'stats'.
	 * @return array|boolean The found data. False on failure.
	 */
	protected function get_remote_data( $type = '' ) {

		if ( ! $type )
			return false;

		$this->is_remote_data_expired() and $this->api_get_remote_data();

		/**
		 * Option cache should be updated.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		return $this->get_option( $type, [] );
	}

	/**
	 * Deletes data fetched remotely.
	 *
	 * @since 1.0.0
	 */
	protected function delete_data() {
		$this->delete_option( 'issues' );
		$this->delete_option( 'stats' );
	}
}
