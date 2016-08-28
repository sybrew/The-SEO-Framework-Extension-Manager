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
 * Require activation traits.
 * @since 1.0.0
 */
tsf_extension_manager_load_trait( 'activation' );

/**
 * Class TSF_Extension_Manager\AccountActivation
 *
 * Holds plugin activation functions.
 *
 * @since 1.0.0
 * @TODO Convert to instance? It's only required once...
 *       Instancing does expand complexity massively as it handles options.
 */
class AccountActivation extends Panes {
	use Enclose, Construct_Sub, Activation_Data;

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
	 * @todo
	 */
	protected function get_remote_activation_listener() {

		if ( false === $this->handle_update_nonce( $this->request_name['activate-external'] ) )
			return;

		$response = $this->get_remote_activation_listener_response();
	}

	/**
	 * Fetches external activation response, periodically.
	 *
	 * @since 1.0.0
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
	 * @return array Request Data option details.
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
		} elseif ( ! $results && $this->get_option( '_activated' ) ) {
			$this->do_deactivation();
			$this->set_error_notice( array( 403 => '' ) );
			return false;
		} elseif ( isset( $results['code'] ) ) {
			//* Probably duplicated local activation request. Will be handled later in response.
			return false;
		}

		$this->set_error_notice( array( 404 => '' ) );
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
				$message .= $success ? '' : ' ' . esc_html__( 'Something went wrong with the deactivation.', 'the-seo-framework-extension-manager' );

				$this->set_error_notice( array( 501 => $message ) );
				return true;
			}

			$this->set_error_notice( array( 502 => '' ) );
			return false;
		}

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
			'_data'               => array(),
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

		$success[] = $this->update_option_multi( array(
			'api_key'             => $args['api_key'],
			'activation_email'    => $args['activation_email'],
			'_activation_level'   => $args['_activation_level'],
			'_activated'          => 'Activated',
			'_instance'           => $this->get_activation_instance( false ),
			'_data'               => array(),
		) );

		//* Fetches and saves extra subscription status data. i.e. '_data'
		$success[] = $this->set_remote_subscription_status( true );

		return ! in_array( false, $success, true );
	}

	/**
	 * Handles premium deactivation.
	 * Sets all options to empty i.e. 'Deactivated'.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success. False on failure.
	 */
	protected function do_deactivation() {
		return $this->kill_options();
	}

	/**
	 * Determines whether the plugin's activated. Either free or premium.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the plugin is activated.
	 */
	protected function is_plugin_activated() {
		return 'Activated' === $this->get_option( '_activated' );
	}

	/**
	 * Determines whether the plugin's use is premium.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the plugin is connected to the API handler.
	 */
	protected function is_premium_user() {
		return 'Premium' === $this->get_option( '_activation_level' );
	}

	/**
	 * Returns subscription status from local options.
	 *
	 * @since 1.0.0
	 * @staticvar array $status.
	 *
	 * @return array Current subscription status.
	 */
	protected function get_subscription_status() {

		static $status = null;

		if ( null !== $status )
			return $status;

		return $status = array(
			'key'     => $this->get_option( 'api_key' ),
			'email'   => $this->get_option( 'activation_email' ),
			'active'  => $this->get_option( '_activated' ),
			'level'   => $this->get_option( '_activation_level' ),
			'data'    => $this->get_option( '_data' ),
		);
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

		if ( $doing_activation ) {
			//* Wait 0.0625 seconds as a second request is following up (1~2x load time server).
			usleep( 62500 );

			//* Fetch data from POST variables. As the options are cached and tainted already at this point.
			$args = array(
				'licence_key' => $this->activation_key,
				'activation_email' => $this->activation_email,
			);

			return $response = $this->handle_request( 'status', $args );
		} else {
			//* Updates at most every 2 hours.
			$timestamp = ceil( time() / ( DAY_IN_SECONDS / 12 ) );

			$status = $this->get_option( '_remote_subscription_status', array( 'timestamp' => 0, 'status' => array() ) );

			//* Cache status for 2 hours.
			if ( $timestamp === $status['timestamp'] ) {
				return $status['status'];
			}

			$args = array(
				'licence_key' => $this->get_option( 'api_key' ),
				'activation_email' => $this->get_option( 'activation_email' ),
			);

			$response = $this->handle_request( 'status', $args );

			if ( ! empty( $response ) )
				$this->update_option( '_remote_subscription_status', array( 'timestamp' => $timestamp, 'status' => $response ) );

			return $response;
		}
	}

	/**
	 * Fetches extra remote subscription information and stores it in the data option.
	 * Should happen only run once after subscription has been updated to Premium.
	 *
	 * @since 1.0.0
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
			$data = $this->get_option( '_data', array() );

			if ( false === is_array( $data ) )
				$data = array();
		}

		$data['expire'] = isset( $response['status_extra']['end_date'] ) ? $response['status_extra']['end_date'] : null;

		$success = $this->update_option( '_data', $data, 'instance', false );

		return $success;
	}
}
