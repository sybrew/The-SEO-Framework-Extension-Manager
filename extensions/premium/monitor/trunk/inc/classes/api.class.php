<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Api
 */

namespace TSF_Extension_Manager\Extension\Monitor;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	use \TSF_Extension_Manager\Construct_Sub_Once_Interface;

	/**
	 * Constructor. Verifies integrity.
	 *
	 * @since 1.0.0
	 */
	private function construct() {
		// Verify integrity.
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
	 * @param bool   $ajax Whether the request call is from AJAX.
	 * @param array  $args Additional arguments to send.
	 * @return array The response body. Or error notice on AJAX.
	 */
	protected function get_monitor_api_response( $type = '', $ajax = false, $args = [] ) {

		if ( ! $type ) {
			$ajax or $this->set_error_notice( [ 1010201 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010201 ) : false;
		}

		$response = \tsfem()->_get_protected_api_response(
			$this,
			\TSFEM_E_MONITOR_API_ACCESS_KEY,
			array_merge(
				$args,
				[
					'request' => "extension/monitor/$type",
				]
			)
		);
		$response = json_decode( $response );

		if ( isset( $response->status_check ) && 'inactive' === $response->status_check ) {
			$this->update_option( 'site_marked_inactive', true );
			$this->delete_data();
		}

		if ( ! isset( $response->data ) ) {
			$ajax or $this->set_error_notice( [ 1010204 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010204 ) : false;
		}

		$data = \is_string( $response->data ) ? json_decode( $response->data, true ) : (array) $response->data;

		return [
			'success' => $response->success ?? false,
			'data'    => $data,
		];
	}

	/**
	 * Requests Monitor to register or fix the site's instance.
	 *
	 * @since 1.0.0
	 * @since 1.2.9 Added check & error 1010306, 1010307.
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param bool $delete Whether to remove the site from the server on failure.
	 * @return bool False on invalid input or on deactivation failure. True otherwise.
	 */
	protected function api_register_site( $delete = true ) {

		$this->set_installing_site();

		$response = $this->get_monitor_api_response( 'register_site' );

		if ( empty( $response ) ) {
			// Notice has already been set. No AJAX conformation here.
			return false;
		}

		$response = $response['data'];

		switch ( $response['status'] ) {
			case 'REQUEST_LIMIT_REACHED':
				$this->set_error_notice( [ 1010306 => '' ] );
				return false;

			case 'LICENSE_TOO_LOW':
				$this->set_error_notice( [ 1010307 => '' ] );
				return false;

			case 'failure':
				$this->set_error_notice( [ 1010301 => '' ] );
				return false;
		}

		$success   = [];
		$success[] = $this->update_option(
			'monitor_expected_domain',
			\tsfem()->get_current_site_domain()
		);
		$success[] = $this->update_option( 'connected', 'yes' );

		if ( \in_array( false, $success, true ) ) {
			$delete and $this->get_monitor_api_response( 'remove_site' );
			$this->set_error_notice( [ 1010302 => '' ] );
			return false;
		}

		$success   = [];
		$success[] = $this->set_remote_crawl_timeout();
		$success[] = $this->update_option( 'site_requires_fix', false );
		$success[] = $this->update_option( 'site_marked_inactive', false );

		if ( \in_array( false, $success, true ) ) {
			$this->set_error_notice( [ 1010303 => '' ] );
			return false;
		}

		$this->set_error_notice( [ 1010304 => '' ] );
		$this->set_error_notice( [ 1010305 => '' ] );
		return true;
	}

	/**
	 * Requests Monitor to remove the site.
	 *
	 * @since 1.0.0
	 * @since 1.2.9 Added check & error 1010404, 1010405.
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @return bool False on invalid input or on deactivation failure. True otherwise.
	 */
	protected function api_disconnect_site() {

		$response = $this->get_monitor_api_response( 'remove_site' );

		if ( empty( $response ) ) {
			// Notice has already been set. No AJAX conformation here.
			return false;
		}

		$response = $response['data'];

		// NOTE: Do not delete data on failure -- the user won't get new data anyway;
		// this bypasses timeouts for "instant data" and lags their site.
		switch ( $response['status'] ) {
			case 'REQUEST_LIMIT_REACHED':
				$this->set_error_notice( [ 1010404 => '' ] );
				return false;

			case 'LICENSE_TOO_LOW':
				$this->set_error_notice( [ 1010405 => '' ] );
				return false;

			case 'failure':
				$this->set_error_notice( [ 1010401 => '' ] );
				return false;
		}

		// Still delete the option index.
		$success = $this->delete_option_index();

		if ( ! $success ) {
			$this->set_error_notice( [ 1010402 => '' ], true );
			return false;
		}

		$this->set_error_notice( [ 1010403 => '' ], true );
		return true;
	}

	/**
	 * Requests Monitor to crawl the website.
	 * Prevents API spam by setting monitor_crawl_requested option.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Added check & error 1010507.
	 * @since 1.2.9 Added check & error 1010508, 1010509.
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param bool $ajax Whether to request is done through AJAX.
	 * @return bool|array False on invalid input or on activation failure. True otherwise.
	 *         Array The status notice on AJAX.
	 */
	protected function api_request_crawl( $ajax = false ) {

		if ( $this->get_option( 'site_marked_inactive' ) || $this->get_option( 'site_requires_fix' ) ) {
			// Notified through Control Panel. AJAX will elaborate on this issue as it can be asynchronously updated.
			if ( $this->get_option( 'site_requires_fix' ) ) {
				return $ajax ? $this->get_ajax_notice( false, 1010502 ) : false;
			} else {
				return $ajax ? $this->get_ajax_notice( false, 1010503 ) : false;
			}
		}

		if ( ! $this->can_request_next_crawl() ) {
			// AJAX shouldn't get this far.
			$ajax or $this->set_error_notice( [ 1010507 => $this->get_try_again_notice( $this->get_remote_crawl_timeout_remainder() ) ] );
			return false;
		}

		$response = $this->get_monitor_api_response( 'request_crawl', $ajax );

		if ( empty( $response ) ) {
			// Notice has already been set for ajax.
			return $ajax ? $response : false;
		}

		$response = $response['data'];

		switch ( $response['status'] ) {
			case 'REQUEST_LIMIT_REACHED':
				$this->set_remote_crawl_timeout();
				$ajax or $this->set_error_notice( [ 1010508 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010508 ) : false;

			case 'LICENSE_TOO_LOW':
				$ajax or $this->set_error_notice( [ 1010509 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010509 ) : false;

			case 'failure':
				$ajax or $this->set_error_notice( [ 1010501 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010501 ) : false;

			case 'site expired':
				$this->update_option( 'site_requires_fix', true );
				$ajax or $this->set_error_notice( [ 1010502 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010502 ) : false;

			case 'site inactive':
				$this->update_option( 'site_marked_inactive', true );
				$ajax or $this->set_error_notice( [ 1010503 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010503 ) : false;

			case 'queued':
				$this->set_remote_crawl_timeout();
				$ajax or $this->set_error_notice( [ 1010504 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010504 ) : false;
		}

		$success = $this->set_remote_crawl_timeout();

		if ( ! $success ) {
			$ajax or $this->set_error_notice( [ 1010505 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010505 ) : false;
		}

		// Success.
		$ajax or $this->set_error_notice( [ 1010506 => '' ] );
		return $ajax ? $this->get_ajax_notice( true, 1010506 ) : true;
	}

	/**
	 * Fetches remote monitor data to later be evaluated.
	 * Prevents API spam by setting monitor_data_requested option with two minute delay.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Added check & error 1010607.
	 * @since 1.2.9 Added check & error 1010608, 1010609.
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param bool $ajax Whether this request is done through AJAX.
	 * @return bool|array False on invalid input or on activation failure. True otherwise.
	 *         Array The status notice on AJAX.
	 */
	protected function api_get_remote_data( $ajax = false ) {

		if ( $this->get_option( 'site_marked_inactive' ) || $this->get_option( 'site_requires_fix' ) ) {
			// Notified through Control Panel. AJAX will elaborate on this issue as it can be asynchronously updated.
			if ( $this->get_option( 'site_requires_fix' ) ) {
				return $ajax ? $this->get_ajax_notice( false, 1010602 ) : false;
			} else {
				return $ajax ? $this->get_ajax_notice( false, 1010603 ) : false;
			}
		}

		if ( ! $this->is_remote_data_expired() ) {
			// AJAX shouldn't get this far... But it does, after the page is just loaded whilst data is fetched.
			$ajax or $this->set_error_notice( [ 1010607 => $this->get_try_again_notice( $this->get_remote_data_timeout_remainder() ) ] );
			return $ajax ? $this->get_ajax_notice( false, 1010607 ) : false;
		}

		$response = $this->get_monitor_api_response( 'get_data', $ajax );

		if ( empty( $response ) ) {
			// Notice has already been set for ajax.
			return $ajax ? $response : false;
		}

		$response = $response['data'];

		switch ( $response['status'] ) {
			case 'REQUEST_LIMIT_REACHED':
				$this->set_remote_crawl_timeout();
				$ajax or $this->set_error_notice( [ 1010608 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010608 ) : false;

			case 'LICENSE_TOO_LOW':
				$ajax or $this->set_error_notice( [ 1010609 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010609 ) : false;

			case 'failure':
				$ajax or $this->set_error_notice( [ 1010601 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010601 ) : false;

			case 'site expired':
				$this->update_option( 'site_requires_fix', true );
				$ajax or $this->set_error_notice( [ 1010602 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010602 ) : false;

			case 'site inactive':
				$this->update_option( 'site_marked_inactive', true );
				$ajax or $this->set_error_notice( [ 1010603 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010603 ) : false;
		}

		/**
		 * Updates timeout to prevent DDoS.
		 *
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$success = $this->set_remote_data_timeout();

		if ( ! $success ) {
			$ajax or $this->set_error_notice( [ 1010604 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010604 ) : false;
		}

		$success = [];

		foreach ( $response as $type => $values ) {
			if ( \in_array( $type, [ 'issues', 'issues_lc', 'uptime_setting', 'performance_setting' ], true ) ) {
				/**
				 * @see trait TSF_Extension_Manager\Extension_Options
				 */
				$success[] = $this->update_option( $type, $values );
			}
		}

		if ( \in_array( false, $success, true ) ) {
			$ajax or $this->set_error_notice( [ 1010605 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010605 ) : false;
		}

		$ajax or $this->set_error_notice( [ 1010606 => '' ] );
		return $ajax ? $this->get_ajax_notice( true, 1010606 ) : true;
	}

	/**
	 * Updates monitor site settings.
	 *
	 * @since 1.1.0
	 * @since 1.2.9 Added check & error 1010806, 1010807.
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param array $settings The new settings.
	 * @param bool  $ajax     Whether this request is done through AJAX.
	 * @return bool|array False on invalid input or on activation failure. True otherwise.
	 *         Array The status notice on AJAX.
	 */
	protected function api_update_remote_settings( $settings, $ajax = false ) {

		if ( $this->get_option( 'site_marked_inactive' ) || $this->get_option( 'site_requires_fix' ) ) {
			// Notified through Control Panel. AJAX will elaborate on this issue as it can be asynchronously updated.
			if ( $this->get_option( 'site_requires_fix' ) ) {
				return $ajax ? $this->get_ajax_notice( false, 1010802 ) : false;
			} else {
				return $ajax ? $this->get_ajax_notice( false, 1010803 ) : false;
			}
		}

		$old_settings = [
			'uptime_setting'      => $this->get_option( 'uptime_setting', 0 ),
			'performance_setting' => $this->get_option( 'performance_setting', 0 ),
		];
		// Filters and merges old and new settings. Magic.
		$settings = array_intersect_key(
			array_merge( $old_settings, $settings ),
			$old_settings
		);

		$response = $this->get_monitor_api_response( 'update_site', $ajax, compact( 'settings' ) );

		if ( empty( $response ) ) {
			// Notice has already been set for ajax.
			return $ajax ? $response : false;
		}

		$response = $response['data'];

		switch ( $response['status'] ) {
			case 'REQUEST_LIMIT_REACHED':
				$this->set_remote_crawl_timeout();
				$ajax or $this->set_error_notice( [ 1010806 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010806 ) : false;

			case 'LICENSE_TOO_LOW':
				$ajax or $this->set_error_notice( [ 1010807 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010807 ) : false;

			case 'failure':
				$ajax or $this->set_error_notice( [ 1010801 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010801 ) : false;

			case 'site expired':
				$this->update_option( 'site_requires_fix', true );
				$ajax or $this->set_error_notice( [ 1010802 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010802 ) : false;

			case 'site inactive':
				$this->update_option( 'site_marked_inactive', true );
				$ajax or $this->set_error_notice( [ 1010803 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 1010803 ) : false;
		}

		$success = [];
		foreach ( [ 'uptime_setting', 'performance_setting' ] as $type ) {
			if ( isset( $response[ $type ] ) ) {
				/**
				 * @see trait TSF_Extension_Manager\Extension_Options
				 */
				$success[] = $this->update_option( $type, $response[ $type ] );
			}
		}
		if ( \in_array( false, $success, true ) ) {
			$ajax or $this->set_error_notice( [ 1010804 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1010804 ) : false;
		}

		$ajax or $this->set_error_notice( [ 1010805 => '' ] );
		return $ajax ? $this->get_ajax_notice( true, 1010805 ) : true;
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
	 * Returns try again notice.
	 *
	 * @since 1.1.0
	 *
	 * @param int $seconds The seconds to try again in.
	 */
	protected function get_try_again_notice( $seconds ) {
		return sprintf(
			/* translators: %s = numeric seconds. */
			\esc_html( \_n( 'Try again in %s second.', 'Try again in %s seconds.', $seconds, 'the-seo-framework-extension-manager' ) ),
			(int) $seconds
		);
	}

	/**
	 * Returns remaining time in seconds of data fetch.
	 *
	 * @since 1.1.0
	 *
	 * @return int
	 */
	protected function get_remote_data_timeout_remainder() {
		return $this->get_remote_data_timeout() + $this->get_remote_data_buffer() - time();
	}

	/**
	 * Returns remaining time in seconds crawl request.
	 *
	 * @since 1.1.0
	 *
	 * @return int
	 */
	protected function get_remote_crawl_timeout_remainder() {
		return $this->get_remote_crawl_timeout() + $this->get_request_next_crawl_buffer() - time();
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
