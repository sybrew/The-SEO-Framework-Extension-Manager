<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Monitor_Data
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use TSF_Extension_Manager\Construct_Core_Once_Interface as Construct_Core_Once_Interface;

/**
 * Monitor extension for The SEO Framework
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
 * Class TSF_Extension_Manager_Extension\Monitor_Data
 *
 * Holds extension data functions.
 *
 * @since 1.0.0
 * @access private
 * @errorval 101xxxx
 */
class Monitor_Data {
	use Enclose_Stray_Private, Construct_Core_Once_Interface;

	/**
	 * Constructor. Verifies integrity.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . ( is_admin() ? '\\Monitor_Admin' : '\\Monitor_Frontend' );
		$this instanceof $that or wp_die( -1 );

	}

	/**
	 * Determines if the site is connected.
	 * This does not inheritly tell if the connection is valid.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if connected, false otherwise.
	 */
	protected function is_api_connected() {
		return 'yes' === $this->get_option( 'connected' );
	}

	/**
	 * Determines when the next crawl is scheduled for the website.
	 * Remote server cron runs every minute, with a 63 second delay.
	 *
	 * @since 1.0.0
	 * @todo Fine-tune this, maybe get remote response on next cron?
	 * @todo The remote server can't run multiple cron-jobs at the same time. It locks for 30 seconds.
	 *
	 * @return string Unix timestring for next crawl.
	 */
	protected function next_crawl() {
		return $next = $this->get_option( 'crawl_requested', 0 ) > time() ? $next + 63 : false;
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

		$data = $this->get_remote_data( $type, false );

		return empty( $data ) ? $default : $data;
	}

	/**
	 * Returns Monitor Data fetched externally from the API server.
	 * If no locally stored data is found, new data gets fetched.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The monitor data type. Accepts 'issue' and 'stats'.
	 * @param bool $ajax Whether the call is made through AJAX.
	 * @return array|mixed The found data.
	 */
	protected function get_remote_data( $type = '', $ajax = false ) {

		if ( ! $type )
			return false;

		/**
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$data = $this->get_option( $type, array() );

		if ( empty( $data ) ) {
			$this->fetch_new_data( $ajax );
			/**
			 * Option cache should be updated.
			 * @see trait TSF_Extension_Manager\Extension_Options
			 */
			$data = $this->get_option( $type, array() );
		}

		return $data;
	}

	/**
	 * Returns Monitor Data fetched externally from the API server.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The monitor data type. Accepts 'issue' and 'stats'.
	 * @param bool $ajax Whether the call is made through AJAX.
	 * @return array|mixed The found data.
	 */
	protected function fetch_new_data( $ajax = false ) {

		static $fetched = null;

		if ( isset( $fetched ) )
			return $fetched;

		$data = $this->api_get_remote_data();

		if ( is_array( $data ) ) {
			foreach ( $data as $type => $values ) {
				/**
				 * Option cache should be updated as well.
				 * @see trait TSF_Extension_Manager\Extension_Options
				 */
				$this->update_option( $type, $values );
			}
			$fetched = true;
		} else {
			$fetched = false;
		}

		return $fetched;
	}

	/**
	 * Fetches remote monitor data to later be evaluated.
	 * Prevents API spam by setting 2 minute time limit.
	 *
	 * @since 1.0.0
	 * @global int $blog_id
	 *
	 * @return array The remote monitor data.
	 */
	protected function api_get_remote_data() {
		global $blog_id;

		$transient = 'tsfem_e_monitor_remote_data_' . $blog_id;

		$data = get_transient( $transient );

		if ( false !== $data )
			return $data;

		$response = $this->get_monitor_api_response( 'get_data' );

		if ( isset( $response ) ) {
			//* Remove 5 seconds server time as local to remote buffer.
			$expiration = ( MINUTE_IN_SECONDS * 2 ) - 5;
			set_transient( $transient, $response, $expiration );

			return $response;
		}

		return false;
	}
}
