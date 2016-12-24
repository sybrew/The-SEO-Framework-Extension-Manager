<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\Panes as Panes;

/**
 * Class TSF_Extension_Manager\AccountActivation
 *
 * Holds plugin activation functions.
 *
 * @since 1.0.0
 * @access private
 * @TODO Convert to instance? It's only required once...
 *       Instancing does expand complexity massively as it handles options.
 */
class AccountActivation extends Panes {
	use Enclose_Stray_Private, Construct_Child_Interface;

	/**
	 * Holds activation input key and email.
	 *
	 * @since 1.0.0
	 *
	 * @var string The activation key.
	 * @var string The activation email.
	 */
	protected $activation_key = '';
	protected $activation_email = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() { }

	/**
	 * Handles remote activation request.
	 * Has to validate nonce prior to activating.
	 * Validation is done two-fold (local and activation server).
	 *
	 * @since 1.0.0
	 * @todo use this
	 */
	protected function get_remote_activation_listener() {

		if ( false === $this->handle_update_nonce( $this->request_name['activate-external'] ) )
			return;

		return $response = $this->get_remote_activation_listener_response();
	}

	/**
	 * Fetches external activation response, periodically.
	 *
	 * @since 1.0.0
	 * @todo everything.
	 *
	 * @return bool|array False if data has not yet been set. Array if data has been set.
	 */
	protected function get_remote_activation_listener_response( $store = false ) {

		static $response = false;

		if ( false !== $store ) {
			return $response = $store;
		} else {
			return $response;
		}
	}

	/**
	 * Sets external activation response.
	 *
	 * @since 1.0.0
	 * @todo use this
	 *
	 * @param array $value The data that needs to be set.
	 * @return bool True
	 */
	protected function set_remote_activation_listener_response( $value = array() ) {

		if ( empty( $value ) || is_wp_error( $value ) )
			return false;

		$this->get_remote_activation_listener_response( $value );

		return true;
	}

	/**
	 * Fetches status API request and returns response data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args : {
	 *		'licence_key'      => string The license key.
	 *		'activation_email' => string The activation email.
	 * }
	 * @return bool|array {
	 *		Always: False on failure.
	 *		Deactivation: True on succesful deactivation.
	 *		Activation/Status: Reponse data.
	 * }
	 */
	protected function handle_request( $type = 'status', $args = array() ) {

		if ( empty( $args['licence_key'] ) ) {
			$this->set_error_notice( array( 101 => '' ) );
			return false;
		}

		if ( empty( $args['activation_email'] ) ) {
			$this->set_error_notice( array( 102 => '' ) );
			return false;
		}

		$this->activation_key = trim( $args['licence_key'] );
		$this->activation_email = sanitize_email( $args['activation_email'] );

		switch ( $type ) :
			case 'status' :
			case 'activation' :
				break;

			case 'deactivation' :
				if ( false === $this->is_plugin_activated() ) {
					$this->kill_options();
					$this->set_error_notice( array( 103 => '' ) );
					return false;
				}

				if ( false === $this->is_premium_user() ) {
					return $this->do_free_deactivation();
				}
				//* Premium deactivation propagates through API, so nothing happens here.
				break;

			default :
				$this->set_error_notice( array( 104 => '' ) );
				return false;
				break;
		endswitch;

		$request = array(
			'request'     => $type,
			'licence_key' => $this->activation_key,
			'email'       => $this->activation_email,
		);

		$response = $this->get_api_response( $request );
		$response = $this->handle_response( $type, $response, WP_DEBUG );

		return $response;
	}

	/**
	 * Handles activation and returns status.
	 *
	 * @since 1.0.0
	 *
	 * @param array $results The activation response.
	 * @return bool|null True on success, false on failure. Null on invalid request.
	 */
	protected function handle_premium_activation( $results ) {

		if ( isset( $results['activated'] ) && true === $results['activated'] ) {

			$args = array(
				'api_key' => $this->activation_key,
				'activation_email' => $this->activation_email,
				'_activation_level' => 'Premium',
			);

			$success = $this->do_premium_activation( $args );

			if ( ! $success ) {
				$this->do_deactivation();
				$this->set_error_notice( array( 401 => '' ) );
				return false;
			}

			$this->set_error_notice( array( 402 => '' ) );
			return true;
		} elseif ( ! $results ) {
			if ( $this->get_option( '_activated' ) ) {
				//* Upgrade request.
				$this->set_error_notice( array( 403 => '' ) );
			} else {
				//* Activation request.
				$this->do_deactivation();
				$this->set_error_notice( array( 404 => '' ) );
			}

			return false;
		} elseif ( isset( $results['code'] ) ) {
			//* Probably duplicated local activation request. Will be handled later in response.
			return false;
		}

		$this->set_error_notice( array( 405 => '' ) );
		return null;
	}

	/**
	 * Handles deactivation and returns status.
	 *
	 * @since 1.0.0
	 *
	 * @param array $results The deactivation response.
	 * @return bool|null True on success, false on failure. Null on invalid request.
	 */
	protected function handle_premium_deactivation( $results ) {

		if ( ! empty( $results['deactivated'] ) ) {
			//* If option has once been registered, deregister options and return activation status.
			if ( $this->get_option( '_activated' ) ) {

				$success = $this->do_deactivation();

				$message = esc_html__( 'API Key deactivated.', 'the-seo-framework-extension-manager' ) . ' ' . esc_html( $results['activations_remaining'] ) . '.';
				$message .= $success ? '' : ' ' . esc_html__( 'However, something went wrong with the deactivation on this website.', 'the-seo-framework-extension-manager' );

				$this->set_error_notice( array( 501 => $message ) );
				return true;
			}

			$this->set_error_notice( array( 502 => '' ) );
			return false;
		}

		//* API server down... TODO consider still handling deactivation?
		$this->set_error_notice( array( 503 => '' ) );
		return null;
	}

	/**
	 * Handles free activation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success. False on failure.
	 */
	protected function do_free_activation() {

		$options = array(
			'api_key'             => '',
			'activation_email'    => '',
			'_activation_level'   => 'Free',
			'_activation_expires' => '',
			'_activated'          => 'Activated',
			'_instance'           => $this->get_activation_instance( false ),
		);

		$success = $this->update_option_multi( $options );

		if ( $success ) {
			$this->set_error_notice( array( 601 => '' ) );
			return true;
		} else {
			$this->set_error_notice( array( 602 => '' ) );
			$this->do_deactivation();
			return false;
		}
	}

	/**
	 * Handles free deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success. False on failure.
	 */
	protected function do_free_deactivation() {

		$success = $this->do_deactivation();

		if ( $success ) {
			$this->set_error_notice( array( 801 => '' ) );
			return true;
		} else {
			$this->set_error_notice( array( 802 => '' ) );
			return false;
		}
	}

	/**
	 * Handles premium activation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The activation arguments.
	 * @return bool True on success. False on failure.
	 */
	protected function do_premium_activation( $args ) {

		$success = array();

		$success[] = $this->update_option( '_instance', $this->get_activation_instance( false ), 'instance', true );
		$success[] = $this->update_option( 'api_key', $args['api_key'], 'instance', true );
		$success[] = $this->update_option( 'activation_email', $args['activation_email'], 'instance', true );
		$success[] = $this->update_option( '_activation_level', $args['_activation_level'], 'instance', true );
		$success[] = $this->update_option( '_activated', 'Activated', 'instance', true );

		//* Fetches and saves extra subscription status data. i.e. '_remote_subscription_status'
		$success[] = $this->set_remote_subscription_status( true );

		return ! in_array( false, $success, true );
	}

	/**
	 * Handles account deactivation.
	 * Sets all options to empty i.e. 'Deactivated'.
	 *
	 * @since 1.0.0
	 * @TODO lower margin of error if server maintains stable.
	 *
	 * @param bool $moe Whether to allow a margin of error.
	 *             May happen once every 60 days for 3 days.
	 * @return bool True on success. False on failure.
	 */
	protected function do_deactivation( $moe = false ) {

		if ( $moe ) {
			$expire = $this->get_option( 'moe', $nt = ( time() + DAY_IN_SECONDS * 3 ) );
			if ( $expire >= time() || $expire < ( time() - DAY_IN_SECONDS * 60 ) ) {
				$this->update_option( 'moe', $nt );
				return false;
			}
		}
		return $this->kill_options();
	}

	/**
	 * Validates local subscription status against remote through an API request.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	protected function validate_remote_subscription_license() {

		$response = $this->get_remote_subscription_status();

		if ( isset( $response['status_check'] ) && 'active' === $response['status_check'] )
			if ( isset( $response['status_extra']['instance'] ) && $this->get_activation_instance() === $response['status_extra']['instance'] )
				if ( isset( $response['status_extra']['activation_domain'] ) && $this->get_activation_site_domain() === $response['status_extra']['activation_domain'] )
					return true;

		return false;
	}

	/**
	 * Sets remote subscription status cache.
	 *
	 * @since 1.0.0
	 * @see $this->get_remote_subscription_status()
	 *
	 * @param bool $doing_activation Whether the activation process is running.
	 * @return bool True on success, false on failure.
	 */
	protected function set_remote_subscription_status( $doing_activation = false ) {
		return false !== $this->get_remote_subscription_status( $doing_activation );
	}

	/**
	 * Fetches remote subscription status. Use this scarcely.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $doing_activation Whether the activation process is running.
	 * @return bool|array False on failure. Array subscription status on success.
	 */
	protected function get_remote_subscription_status( $doing_activation = false ) {

		if ( false === $doing_activation && 'Premium' !== $this->get_option( '_activation_level' ) )
			return false;

		static $response = null;

		if ( isset( $response ) )
			return $response;

		$status = $this->get_option( '_remote_subscription_status', array( 'timestamp' => 0, 'status' => array() ) );

		if ( isset( $status['status']['status_check'] ) && 'active' !== $status['status']['status_check'] ) {
			//* Updates at most every 10 minutes.
			$divider = HOUR_IN_SECONDS / 6;
		} else {
			//* Updates at most every two hours.
			$divider = HOUR_IN_SECONDS * 2;
		}

		//* In-house transient cache.
		$timestamp = (int) ceil( time() / $divider );

		//* Return cached status within 2 hours.
		if ( ! $doing_activation && $timestamp === $status['timestamp'] )
			return $response = $status['status'];

		if ( $doing_activation ) {
			//* Wait 0.0625 seconds as a second request is following up (1~2x load time server).
			usleep( 62500 );

			//* Fetch data from POST variables. As the options are cached and tainted already at this point.
			$args = array(
				'licence_key' => $this->activation_key,
				'activation_email' => $this->activation_email,
			);
		} else {
			$args = array(
				'licence_key' => $this->get_option( 'api_key' ),
				'activation_email' => $this->get_option( 'activation_email' ),
			);
		}

		$response = $this->handle_request( 'status', $args );

		if ( ! empty( $response ) )
			$this->update_option( '_remote_subscription_status', array( 'timestamp' => $timestamp, 'status' => $response, 'divider' => $divider ), 'regular', false );

		return $response;
	}

	/**
	 * Fetches extra remote subscription information and stores it in the data option.
	 * Should happen only run once after subscription has been updated to Premium.
	 *
	 * @since 1.0.0
	 * @ignore
	 *
	 * @param bool $doing_activation Whether an activation process is active.
	 * @return bool True on success, false on failure or when activation level isn't Premium.
	 */
	protected function update_extra_subscription_data( $doing_activation = false ) {

		$response = $this->get_remote_subscription_status( $doing_activation );

		if ( false === $response )
			return false;

		if ( $doing_activation ) {
			$data = array();
		} else {
			$data = $this->get_option( '_remote_subscription_status', array() );

			if ( false === is_array( $data ) )
				$data = array();
		}

		$data['expire'] = isset( $response['status_extra']['end_date'] ) ? $response['status_extra']['end_date'] : null;

		$success = $this->update_option( '_remote_subscription_status', $data, 'instance', false );

		return $success;
	}
}
