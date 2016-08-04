<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

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
 * Class TSF_Extension_Manager\Activation
 *
 * Holds plugin activation functions.
 *
 * @since 1.0.0
 */
class Activation extends Panes {

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
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Constructor. Loads parent constructor.
	 */
	protected function __construct() {
		parent::__construct();
	}

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
	 * @param array $args
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
			case 'deactivation' :
				break;

			default :
				$this->set_error_notice( array( 103 => '' ) );
				return false;
				break;
		endswitch;

		$request = array(
			'request'     => $type,
			'licence_key' => $this->activation_key,
			'email'       => $this->activation_email,
		);

		$response = $this->get_api_response( $request );
		$response = $this->handle_response( $type, $response );

		return $response;
	}

	/**
	 * Connects to the main plugin activation.
	 *
	 * @since 1.0.0
	 * @see $this->handle_request() The request validation wrapper.
	 *
	 * @param array $args
	 * @return string Response body. Empty string if no body or incorrect parameter given.
	 */
	protected function get_api_response( $args ) {

		$defaults = array(
			'request'          => '',
			'email'            => '',
			'licence_key'      => '',
			'product_id'       => $this->get_activation_product_title(),
			'instance'         => $this->get_activation_instance( false ),
			'platform'         => $this->get_activation_site_domain(),
			'software_version' => '1.0.0', // Always 1.0.0, as it's not software, but a "placeholder" for the subscription.
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['request'] ) ) {
			$this->set_error_notice( array( 201 => '' ) );
			return false;
		}

		$target_url = $this->get_api_url( $args );

		/**
		 * @since 1.0.0:
		 * Applies filters 'tsf_extension_manager_request_timeout' : int
		 *		7 seconds should be more than sufficient and equals the API server keep_alive_timeout. Default is 5.
		 * Applies filters 'tsf_extension_manager_http_request_version' : string
		 *		1.1 is used for improved performance. Default is '1.0'
		 */
		$http_args = array(
			'timeout' => apply_filters( 'tsf_extension_manager_request_timeout', 7 ),
			'httpversion' => apply_filters( 'tsf_extension_manager_http_request_version', '1.1' ),
		);

		$request = wp_safe_remote_get( $target_url, $http_args );

		if ( 200 !== (int) wp_remote_retrieve_response_code( $request ) ) {
			$this->set_error_notice( array( 202 => '' ) );
			return false;
		}

		$response = wp_remote_retrieve_body( $request );

		return $response;
	}

	/**
	 * Handles AME response and sets options.
	 *
	 * @since 1.0.0
	 * @see $this->handle_request() The request validation wrapper.
	 *
	 * @param string $type The request type.
	 * @param string $response The obtained response body.
	 * @return bool True on successful response, false on failure.
	 */
	protected function handle_response( $type = 'status', $response = '' ) {

		if ( empty( $response ) ) {
			$this->set_error_notice( array( 301 => '' ) );
			return false;
		}

		$results = json_decode( $response, true );

		$_response = '';

		if ( 'status' !== $type ) {
			if ( 'activation' === $type )
				$_response = $this->handle_premium_activation( $results );
			elseif ( 'deactivation' === $type )
				$_response = $this->handle_premium_deactivation( $results );
		} else {
			$_response = $results;
		}

		if ( isset( $results['code'] ) ) {
			switch ( $results['code'] ) :
				case '100' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 302 => $additional_info ) );
					$this->do_deactivation();
					break;
				case '101' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 303 => $additional_info ) );
					$this->do_deactivation();
					break;
				case '102' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 304 => $additional_info ) );
					$this->do_deactivation();
					break;
				case '103' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 305 => $additional_info ) );
					$this->do_deactivation();
					break;
				case '104' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 306 => $additional_info ) );
					$this->do_deactivation();
					break;
				case '105' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 307 => $additional_info ) );
					$this->do_deactivation();
					break;
				case '106' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 308 => $additional_info ) );
					$this->do_deactivation();
					break;
				default :
					break;
			endswitch;
		}

		return $_response;
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

		if ( isset( $results['activated'] ) && true === $results['activated'] && ! empty( $this->get_option( '_activated' ) ) ) {

			$args = array(
				'api_key' => $this->activation_key,
				'activation_email' => $this->activation_email,
				'_activation_level' => 'Premium',
			);

			$success = $this->do_activation( $args );

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
			//* Probably duplicated local activation request. Will be handled later in reponse.
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

				$this->do_deactivation();

				$message = esc_html__( 'API Key deactivated.', 'the-seo-framework-extension-manager' ) . ' ' . esc_html( $results['activations_remaining'] ) . '.';
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

		$success = $this->update_option_multi( array(
			'api_key'             => '',
			'activation_email'    => '',
			'_activation_level'   => 'Free',
			'_activation_expires' => '',
			'_activated'          => 'Activated',
			'_instance'           => false,
			'_data'               => array(),
		) );

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
	 * Handles premium activation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The activation arguments.
	 * @return bool True on success. False on failure.
	 */
	protected function do_activation( $args ) {

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
		//$success[] = $this->set_remote_subscription_status( true );
		$success[] = $this->update_extra_subscription_data( true );

		return ! in_array( false, $success, true );
	}

	/**
	 * Handles premium deactivation.
	 * Sets all options to empty or 'Deactivated'.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success. False on failure.
	 */
	protected function do_deactivation() {

		$success = array();

		$success[] = $this->update_option( 'api_key', '' );
		$success[] = $this->update_option( 'activation_email', '' );
		$success[] = $this->update_option( '_activation_level', '' );
		$success[] = $this->update_option( '_activation_expires', '' );
		$success[] = $this->update_option( '_activated', 'Deactivated' );
		$success[] = $this->update_option( '_instance', false );
		$success[] = $this->update_option( '_data', array() );

		return ! in_array( false, $success, true );
	}

	/**
	 * Determines whether the plugin's use has been verified.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the plugin is connected to the API handler.
	 */
	protected function is_plugin_connected() {
		return 'Activated' === $this->get_option( '_activated' );
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
	 * Sets remote subscription status cache.
	 *
	 * @since 1.0.0
	 * @see $this->get_remote_subscription_status()
	 *
	 * @param bool $doing_activation Whether the activation process is running.
	 * @return bool true on success, false on failure.
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
			//* Wait 0.0625 seconds as a second request is following up.
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

		return $response = $this->handle_request( 'status', $args );
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

		if ( false === $reponse )
			return false;

		if ( $doing_activation ) {
			$data = array();
		} else {
			$data = $this->get_option( '_data', array() );

			if ( false === is_array( $data ) )
				$data = array();
		}

		$data['expire'] = isset( $response['status_extra']['end_date'] ) ? $response['status_extra']['end_date'] : null;

		$success = $this->update_option( '_data', $data );

		return $success;
	}

	/**
	 * Returns domain host of plugin holder.
	 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
	 * so only the host portion of the URL can be sent. For example the host portion might be
	 * www.example.com or example.com. http://www.example.com includes the scheme http,
	 * and the host www.example.com.
	 * Sending only the host also eliminates issues when a client site changes from http to https,
	 * but their activation still uses the original scheme.
	 *
	 * @since 1.0.0
	 *
	 * @return string Domain Host.
	 */
	protected function get_activation_site_domain() {
		return str_ireplace( array( 'http://', 'https://' ), '', home_url() );
	}

	/**
	 * Returns activation domain URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path The URL Path.
	 * @return string
	 */
	protected function get_activation_url( $path = '' ) {
		return 'https://premium.theseoframework.com/' . ltrim( $path, ' \\/' );
	}

	/**
	 * Returns product title to activate.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_activation_product_title() {
		return 'The SEO Framework Premium';
	}

	/**
	 * Returns API option prefix.
	 *
	 * @since 1.0.0
	 *
	 * @return string.
	 */
	protected function get_activation_prefix() {

		static $prefix = null;

		if ( isset( $prefix ) )
			return $prefix;

		return $prefix = str_ireplace( array( ' ', '_', '&', '?' ), '_', strtolower( $this->get_activation_product_title() ) );
	}

	/**
	 * Returns website's instance key from option. Generates one if non-existent.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $save_option Whether to save the instance in an option. Useful
	 *             for when you're going to save it later.
	 * @return string Instance key.
	 */
	protected function get_activation_instance( $save_option = true ) {

		static $instance = null;

		if ( isset( $instance ) )
			return $instance;

		$instance = $this->get_option( '_instance' );

		if ( false === $instance ) {
			$instance = trim( wp_generate_password( 32, false ) );

			if ( $save_option )
				$this->update_option( '_instance', $instance );
		}

		return $instance;
	}

	/**
	 * Generates software API URL to connect to the API manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The API query parameters.
	 * @return string The escaped API URL with parameters.
	 */
	protected function get_api_url( $args = array() ) {

		$api_url = add_query_arg( 'wc-api', 'am-software-api', $this->get_activation_url() );

		return esc_url_raw( $api_url . '&' . http_build_query( $args ) );
	}

	/**
	 * Generates software API My Account page HTML link.
	 *
	 * @since 1.0.0
	 *
	 * @return string The My Account API URL.
	 */
	protected function get_my_account_link() {
		return $this->get_link( array(
			'url' => $this->get_activation_url( 'my-account/' ),
			'target' => '_blank',
			'class' => '',
			'title' => esc_attr__( 'Go to My Account', 'the-seo-framework-extension-manager' ),
			'content' => esc_html__( 'My Account', 'the-seo-framework-extension-manager' ),
		) );
	}
}
