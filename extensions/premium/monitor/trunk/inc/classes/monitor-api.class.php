<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Api
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use TSF_Extension_Manager\Construct_Sub_Once_Interface as Construct_Sub_Once_Interface;

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
 * Class TSF_Extension_Manager_Extension\Monitor_Api
 *
 * Holds extension api functionality.
 *
 * @since 1.0.0
 * @access private
 * @errorval 101xxxx
 */
class Monitor_Api extends Monitor_Data {
	use Enclose_Stray_Private, Construct_Sub_Once_Interface;

	/**
	 * Constructor. Verifies integrity.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . '\\Monitor_Admin';
		$this instanceof $that or wp_die( -1 );

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

	/**
	 * Registers setup transient.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 *
	 * @param bool $val Whether the site is installing Monitor.
	 */
	protected function set_installing_site( $val = true ) {
		$this->update_option( 'monitor_installing', (bool) $val );
	}

	protected function api_register_site( $delete = true ) {

		$this->set_installing_site();

		$response = $this->get_monitor_api_response( 'register_site' );

		if ( 'failure' === $response['status'] ) {
			$this->set_error_notice( array( 1010301 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		$success = $this->update_option( 'connected', 'yes' );

		if ( false === $success ) {
			$delete and $this->get_monitor_api_response( 'remove_site' );
			$this->set_error_notice( array( 1010302 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		$success = $this->update_option( 'crawl_requested', time() );

		if ( false === $success ) {
			$this->set_error_notice( array( 1010303 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		$this->set_error_notice( array( 1010304 => '' ) );
		the_seo_framework()->admin_redirect( $this->monitor_page_slug );
		exit;
	}

	protected function api_disconnect_site() {

		$response = $this->get_monitor_api_response( 'remove_site' );

		if ( 'failure' === $response['status'] ) {
			$this->set_error_notice( array( 1010501 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		$success = $this->delete_option( 'connected' );

		if ( false === $success ) {
			$this->set_error_notice( array( 1010502 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		$this->set_error_notice( array( 1010503 => '' ) );
		the_seo_framework()->admin_redirect( $this->monitor_page_slug );
		exit;
	}

	protected function api_request_crawl( $ajax = false ) {

		$response = $this->get_monitor_api_response( 'request_crawl' );

		if ( 'failure' === $response['status'] ) {
			$this->set_error_notice( array( 1010401 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		if ( 'site expired' === $response['status'] ) {
			$this->set_error_notice( array( 1010402 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		if ( 'site inactive' === $response['status'] ) {
			$this->set_error_notice( array( 1010403 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		$success = $this->update_option( 'crawl_requested', time() );

		if ( false === $success ) {
			$this->set_error_notice( array( 1010404 => '' ) );
			the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		$this->set_error_notice( array( 1010405 => '' ) );
		the_seo_framework()->admin_redirect( $this->monitor_page_slug );
		exit;
	}
}
