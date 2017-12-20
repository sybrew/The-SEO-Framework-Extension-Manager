<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Api
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
 * Class TSF_Extension_Manager\Extension\Monitor\Api
 *
 * Holds extension api methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 101xxxx
 * @uses TSF_Extension_Manager\Traits
 */
class Api extends Data {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Sub_Once_Interface;

	/**
	 * Constructor. Verifies integrity.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . '\\Admin';
		$this instanceof $that or \wp_die( -1 );

	}

	/**
	 * Retrieves API response for SEO Monitor data collection.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param string $type The request type.
	 * @param bool $ajax Whether the request call is from AJAX.
	 * @return array The response body. Or error notice on AJAX.
	 */
	protected function get_monitor_api_response( $type = '', $ajax = false ) {

		if ( empty( $type ) ) {
			$ajax or $this->set_error_notice( [ 1010201 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010201 ) : false;
		}

		/**
		 * Request verification instances, variables are passed by reference :
		 * 0. Request by class. Pass to yield.
		 * 1. Yield first loop, get options.
		 * 2. Yield second loop. Use options to build API link.
		 */
		\tsf_extension_manager()->_request_premium_extension_verification_instance( $this, $_instance, $bits );
		$count = 1;
		foreach ( \tsf_extension_manager()->_yield_verification_instance( 2, $_instance, $bits ) as $verification ) :
			$bits = $verification['bits'];
			$_instance = $verification['instance'];

			switch ( $count ) :
				case 1 :
					$subscription = \tsf_extension_manager()->_get_subscription_status( $_instance, $bits );
					break;

				case 2 :
					if ( is_array( $subscription ) ) {
						$args = [
							'request'     => 'extension/monitor/' . $type,
							'email'       => $subscription['email'],
							'licence_key' => $subscription['key'],
						];
						$response = \tsf_extension_manager()->_get_api_response( $args, $_instance, $bits );
					} else {
						\tsf_extension_manager()->_verify_instance( $instance, $bits );
						$ajax or $this->set_error_notice( [ 1010202 => '' ] );
						return $ajax ? $this->get_ajax_notice( false, 1010202 ) : false;
					}
					break;

				default :
					\tsf_extension_manager()->_verify_instance( $instance, $bits );
					break;
			endswitch;
			$count++;
		endforeach;

		$response = json_decode( $response );

		if ( isset( $response->status_check ) && 'inactive' === $response->status_check ) {
			$this->update_option( 'site_marked_inactive', true );
			$this->delete_data();
		}

		if ( ! isset( $response->success ) ) {
			$ajax or $this->set_error_notice( [ 1010203 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010203 ) : false;
		}

		if ( ! isset( $response->data ) ) {
			$ajax or $this->set_error_notice( [ 1010204 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010204 ) : false;
		}

		$data = is_string( $response->data ) ? json_decode( $response->data, true ) : (array) $response->data;

		return [
			'success' => true,
			'data' => $data,
		];
	}

	/**
	 * Requests Monitor to register or fix the site's instance.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param bool $delete Whether to remove the site from the server on failure.
	 * @return bool False on invalid input or on deactivation failure. True otherwise.
	 */
	protected function api_register_site( $delete = true ) {

		$this->set_installing_site();

		$response = $this->get_monitor_api_response( 'register_site' );

		if ( empty( $response['success'] ) ) {
			//* Notice has already been set. No AJAX conformation here.
			return false;
		}

		$response = $response['data'];

		if ( 'failure' === $response['status'] ) {
			$this->set_error_notice( [ 1010301 => '' ] );
			return false;
		}

		$success = [];
		$success[] = $this->update_option( 'monitor_expected_domain', str_ireplace( [ 'http://', 'https://' ], '', \esc_url( \get_home_url(), [ 'http', 'https' ] ) ) );
		$success[] = $this->update_option( 'connected', 'yes' );

		if ( in_array( false, $success, true ) ) {
			$delete and $this->get_monitor_api_response( 'remove_site' );
			$this->set_error_notice( [ 1010302 => '' ] );
			return false;
		}

		$success = [];
		$success[] = $this->set_remote_crawl_timeout();
		$success[] = $this->update_option( 'site_requires_fix', false );
		$success[] = $this->update_option( 'site_marked_inactive', false );

		if ( in_array( false, $success, true ) ) {
			$this->set_error_notice( [ 1010303 => '' ] );
			return false;
		}

		$this->set_error_notice( [ 1010304 => '' ] );
		return true;
	}

	/**
	 * Requests Monitor to remove the site.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @return bool False on invalid input or on deactivation failure. True otherwise.
	 */
	protected function api_disconnect_site() {

		$response = $this->get_monitor_api_response( 'remove_site' );

		if ( empty( $response['success'] ) ) {
			//* Notice has already been set. No AJAX conformation here.
			return false;
		}

		$response = $response['data'];

		if ( 'failure' === $response['status'] ) {
			$this->set_error_notice( [ 1010401 => '' ] );
			return false;
		}

		$success = $this->delete_option_index();

		if ( false === $success ) {
			$this->set_error_notice( [ 1010402 => '' ] );
			return false;
		}

		$this->set_error_notice( [ 1010403 => '' ] );
		return true;
	}

	/**
	 * Requests Monitor to crawl the website.
	 * Prevents API spam by setting monitor_crawl_requested option.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param bool $ajax Whether to request is done through AJAX.
	 * @return bool|array False on invalid input or on activation failure. True otherwise.
	 *         Array The status notice on AJAX.
	 */
	protected function api_request_crawl( $ajax = false ) {

		if ( $this->get_option( 'site_marked_inactive' ) || $this->get_option( 'site_requires_fix' ) ) {
			//* Notified through Control Panel. AJAX will elaborate on this issue as it can be asynchronously updated.
			if ( $this->get_option( 'site_requires_fix' ) ) {
				return $ajax ? $this->get_ajax_notice( false, 1010502 ) : false;
			} else {
				return $ajax ? $this->get_ajax_notice( false, 1010503 ) : false;
			}
		}

		$response = $this->get_monitor_api_response( 'request_crawl', $ajax );

		if ( empty( $response['success'] ) ) {
			//* Notice have already been set.
			return $ajax ? $response : false;
		}

		$response = $response['data'];

		if ( 'failure' === $response['status'] ) {
			$ajax or $this->set_error_notice( [ 1010501 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010501 ) : false;
		}

		if ( 'site expired' === $response['status'] ) {
			$this->update_option( 'site_requires_fix', true );
			$ajax or $this->set_error_notice( [ 1010502 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010502 ) : false;
		}

		if ( 'site inactive' === $response['status'] ) {
			$this->update_option( 'site_marked_inactive', true );
			$ajax or $this->set_error_notice( [ 1010503 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010503 ) : false;
		}

		if ( 'queued' === $response['status'] ) {
			$this->set_remote_crawl_timeout();
			$ajax or $this->set_error_notice( [ 1010504 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010504 ) : false;
		}

		$success = $this->set_remote_crawl_timeout();

		if ( false === $success ) {
			$ajax or $this->set_error_notice( [ 1010505 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010505 ) : false;
		}

		//* Success.
		$ajax or $this->set_error_notice( [ 1010506 => '' ] );
		return $ajax ? $this->get_ajax_notice( true, 1010506 ) : true;
	}

	/**
	 * Fetches remote monitor data to later be evaluated.
	 * Prevents API spam by setting monitor_data_requested option with two minute delay.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param bool $ajax Whether this request is done through AJAX.
	 * @return bool|array False on invalid input or on activation failure. True otherwise.
	 *         Array The status notice on AJAX.
	 */
	protected function api_get_remote_data( $ajax = false ) {

		if ( $this->get_option( 'site_marked_inactive' ) || $this->get_option( 'site_requires_fix' ) ) {
			//* Notified through Control Panel. AJAX will elaborate on this issue as it can be asynchronously updated.
			if ( $this->get_option( 'site_requires_fix' ) ) {
				return $ajax ? $this->get_ajax_notice( false, 1010602 ) : false;
			} else {
				return $ajax ? $this->get_ajax_notice( false, 1010603 ) : false;
			}
		}

		$response = $this->get_monitor_api_response( 'get_data', $ajax );

		if ( empty( $response['success'] ) ) {
			//* Notice has already been set.
			return $ajax ? $response : false;
		}

		$response = $response['data'];

		if ( 'failure' === $response['status'] ) {
			$ajax or $this->set_error_notice( [ 1010601 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010601 ) : false;
		}

		if ( 'site expired' === $response['status'] ) {
			$this->update_option( 'site_requires_fix', true );
			$ajax or $this->set_error_notice( [ 1010602 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010602 ) : false;
		}

		if ( 'site inactive' === $response['status'] ) {
			$this->update_option( 'site_marked_inactive', true );
			$ajax or $this->set_error_notice( [ 1010603 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010603 ) : false;
		}

		/**
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$success = $this->set_remote_data_timeout();

		if ( false === $success ) {
			$ajax or $this->set_error_notice( [ 1010604 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010604 ) : false;
		}

		$success = [];

		foreach ( $response as $type => $values ) {
			if ( in_array( $type, [ 'issues', 'stats', 'issues_lc' ], true ) ) {
				/**
				 * @see trait TSF_Extension_Manager\Extension_Options
				 */
				$success[] = $this->update_option( $type, $values );
			}
		}

		if ( in_array( false, $success, true ) ) {
			$ajax or $this->set_error_notice( [ 1010605 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010605 ) : false;
		}

		$ajax or $this->set_error_notice( [ 1010606 => '' ] );
		return $ajax ? $this->get_ajax_notice( true, 1010606 ) : true;
	}

	/**
	 * Determines if the site is connected.
	 * This does not inheritly tell if the connection is valid.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 *
	 * @return boolean True if connected, false otherwise.
	 */
	protected function is_api_connected() {
		return 'yes' === $this->get_option( 'connected' );
	}

	/**
	 * Registers Monitor setup option in order to prevent propagation of requests
	 * that would most likely yield empty results.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 *
	 * @param bool $val Whether the site is installing Monitor.
	 */
	protected function set_installing_site( $val = true ) {
		$this->update_option( 'monitor_installing', (bool) $val );
	}

	/**
	 * Determines if the remote data is expired.
	 * Currently yields two minutes timeout.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if expired, false otherwise.
	 */
	protected function is_remote_data_expired() {
		return ( $this->get_remote_data_timeout() + $this->get_remote_data_buffer() ) < time();
	}

	/**
	 * Returns the remote data fetch timeout buffer.
	 * Currently yields 93 seconds timeout.
	 *
	 * @since 1.0.0
	 *
	 * @return int The timeout buffer.
	 */
	protected function get_remote_data_buffer() {
		return 93;
	}

	/**
	 * Updates the timeout of Monitor remote data fetching.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 *
	 * @return bool True on success, false on failure.
	 */
	protected function set_remote_data_timeout() {
		return $this->update_option( 'monitor_data_requested', time() );
	}

	/**
	 * Returns the timeout of remote data fetching.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 *
	 * @return int The remote data fetching timeout in UNIX time.
	 *             Can be 0 if timeout is non-existent.
	 */
	protected function get_remote_data_timeout() {
		return (int) $this->get_option( 'monitor_data_requested' );
	}

	/**
	 * Determines if the remote crawl timeout request is expired.
	 * Currently yields 63 seconds timeout.
	 *
	 * @since 1.0.0
	 * @todo Fine-tune this, maybe get remote response on next cron?
	 * @todo The remote server can't run multiple cron-jobs at the same time. It locks for 30 seconds.
	 *
	 * @return bool True if crawl can be requested, false otherwise.
	 */
	protected function can_request_next_crawl() {
		return ( $this->get_remote_crawl_timeout() + $this->get_request_next_crawl_buffer() ) < time();
	}

	/**
	 * Returns the remote data crawl timeout buffer.
	 * Currently yields 63 seconds timeout.
	 *
	 * @since 1.0.0
	 * @todo Fine-tune this, maybe get remote response on next cron?
	 * @todo The remote server can't run multiple cron-jobs at the same time. It locks for 30 seconds.
	 *
	 * @return int The timeout buffer.
	 */
	protected function get_request_next_crawl_buffer() {
		return 63;
	}

	/**
	 * Updates the timeout of Monitor remote crawl requests.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 *
	 * @return bool True on success, false on failure.
	 */
	protected function set_remote_crawl_timeout() {
		return $this->update_option( 'monitor_crawl_requested', time() );
	}

	/**
	 * Determines the last crawl request of the website.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 * @todo Fine-tune this, maybe get remote response on next cron?
	 * @todo The remote server can't run multiple cron-jobs at the same time. It locks for 30 seconds.
	 *
	 * @return int The remote data request timeout in UNIX time.
	 *             Can be 0 if timeout is non-existent.
	 */
	protected function get_remote_crawl_timeout() {
		return (int) $this->get_option( 'monitor_crawl_requested' );
	}

	/**
	 * Returns the last recorded issue crawl.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Extension_Options
	 *
	 * @return int The remote last crawl time in UNIX time.
	 *             Can be 0 if time isn't ever recorded.
	 */
	protected function get_last_issues_crawl() {
		return (int) $this->get_option( 'issues_lc' );
	}

	/**
	 * Returns the expected Monitor connection domain.
	 *
	 * @since 1.0.0
	 *
	 * @return string The expected matching domain.
	 */
	protected function get_expected_domain() {
		return $this->get_option( 'monitor_expected_domain' );
	}
}
