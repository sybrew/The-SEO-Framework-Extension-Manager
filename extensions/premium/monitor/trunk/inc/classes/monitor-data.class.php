<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Monitor_Data
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Require extension options trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'extension-options' );

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use TSF_Extension_Manager\Construct_Core_Once_Interface as Construct_Core_Once_Interface;
use TSF_Extension_Manager\Extension_Options as Extension_Options;

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
 */
class Monitor_Data {
	use Enclose_Stray_Private, Construct_Core_Once_Interface, Extension_Options;

	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . ( is_admin() ? '\\Monitor_Admin' : '\\Monitor_Frontend' );
		$this instanceof $that or wp_die( -1 );

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'monitor';
	}

	protected function is_api_connected() {
		return 'yes' === $this->get_option( 'connected' );
	}

	protected function get_data( $type, $default = null ) {

		$data = $this->get_remote_data( $type, false );

		return empty( $data ) ? $default : $data;
	}

	protected function get_remote_data( $type = '', $ajax = false ) {

		if ( ! $type )
			return false;

		$this->delete_option( $type );

		/**
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$data = $this->get_option( $type, array() );

		//* DEBUG.
		static $debug = true;
		$debug and $this->fetch_new_data( $ajax ) and $data = $this->get_session_data( $type ) and $debug = false;

		if ( empty( $data ) ) {
			$this->fetch_new_data( $ajax );
			$data = $this->get_session_data( $type );
		}

		return $data;
	}

	protected function fetch_new_data( $ajax = false ) {

		static $fetched = null;

		if ( isset( $fetched ) )
			return $fetched;

		$data = $this->api_get_remote_data();

		if ( is_array( $data ) ) {
			foreach ( $data as $type => $values ) {
				$this->store_session_data( $type, $values );
				/**
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
	 * Prevents API spam by setting 3 minute time limit.
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
			//* Remove 5 seconds server time as buffer.
			$expiration = ( MINUTE_IN_SECONDS * 3 ) - 5;
			set_transient( $transient, $response, $expiration );

			return $response;
		}

		return false;
	}

	protected function get_session_data( $type ) {
		return $this->store_session_data( $type );
	}

	protected function store_session_data( $type = '', $data = null ) {

		static $data_cache = array();

		if ( isset( $data_cache[ $type ] ) )
			return $data_cache[ $type ];

		if ( isset( $data ) )
			return $data_cache[ $type ] = $data;

		return false;
	}

	/**
	 * Retrieves API response for SEO Monitor data collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The request type.
	 * @return array The response body.
	 */
	protected function get_monitor_api_response( $type = '' ) {

		if ( empty( $type ) ) {
			$this->set_error_notice( array( 1010201 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		/**
		 * Request verification instances, variables are passed by reference :
		 * 0. Request by class. Pass to yield.
		 * 1. Yield first loop, get options.
		 * 2. Yield second loop. Use options to build API link.
		 */
		tsf_extension_manager()->_request_premium_extension_verification_instance( $this, $_instance, $bits );
		$count = 1;
		foreach ( tsf_extension_manager()->_yield_verification_instance( 2, $_instance, $bits ) as $verification ) :
			$bits = $verification['bits'];
			$_instance = $verification['instance'];

			switch ( $count ) :
				case 1 :
					$subscription = tsf_extension_manager()->_get_subscription_status( $_instance, $bits );
					break;

				case 2 :
					if ( is_array( $subscription ) ) {
						$args = array(
							'request'     => 'extension/monitor/' . $type,
							'email'       => $subscription['email'],
							'licence_key' => $subscription['key'],
						);
						$response = tsf_extension_manager()->_get_api_response( $args, $_instance, $bits );
					} else {
						$this->set_error_notice( array( 1010202 => '' ) );
						tsf_extension_manager()->_verify_instance( $instance, $bits );
						the_seo_framework()->admin_redirect( $this->monitor_page_slug );
						exit;
					}
					break;

				default :
					tsf_extension_manager()->_verify_instance( $instance, $bits );
					break;
			endswitch;
			$count++;
		endforeach;

		$response = json_decode( $response );

		if ( ! isset( $response->success ) ) {
			$this->set_error_notice( array( 1010203 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		if ( ! isset( $response->data ) ) {
			$this->set_error_notice( array( 1010204 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		return stripslashes_deep( json_decode( $response->data, true ) );
	}
}
