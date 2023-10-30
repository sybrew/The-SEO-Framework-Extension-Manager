<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
 * Class TSF_Extension_Manager\API
 *
 * Holds plugin API functions.
 *
 * @since 1.0.0
 * @access private
 */
class API extends Core {
	use Construct_Child_Interface;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() {
		$this->set_api_endpoint_type();
	}

	/**
	 * Determines whether the plugin's set to be auto-activated.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	final public function is_auto_activated() {
		return (bool) \TSF_EXTENSION_MANAGER_API_INFORMATION;
	}

	/**
	 * Fetches status API request and returns response data.
	 *
	 * @since 1.0.0
	 * @since 2.6.1 Renamed to more accurately describe the function.
	 *
	 * @param string $type The request type.
	 * @param array  $args : {
	 *    'api_key'          => string The license key.
	 *    'activation_email' => string The activation email.
	 * }
	 * @return bool|array {
	 *    Always: False on failure.
	 *    Deactivation: True on successful deactivation.
	 *    Activation/Status: Reponse data.
	 * }
	 */
	final protected function handle_activation_request( $type = 'status', $args = [] ) {

		if ( empty( $args['api_key'] ) ) {
			$this->set_error_notice( [ 101 => '' ] );
			return false;
		}

		if ( empty( $args['activation_email'] ) ) {
			$this->set_error_notice( [ 102 => '' ] );
			return false;
		}

		switch ( $type ) :
			case 'status':
			case 'activation':
				break;

			case 'deactivation':
				if ( ! $this->is_plugin_activated() ) {
					$this->kill_options();
					$this->set_error_notice( [ 103 => '' ] );
					return false;
				}

				if ( ! $this->is_connected_user() )
					return $this->do_free_deactivation();

				// Premium/Essential deactivation propagates through API, so nothing happens here.
				break;

			default:
				$this->set_error_notice( [ 104 => '' ] );
				return false;
		endswitch;

		$key   = trim( $args['api_key'] );
		$email = \sanitize_email( $args['activation_email'] );

		$response = $this->handle_activation_response(
			[
				'request'          => $type,
				'api_key'          => $key,
				'activation_email' => $email,
			],
			$this->get_api_response( [
				'request' => $type,
				'api_key' => $key,
				'email'   => $email,
			] )
		);

		return $response;
	}

	/**
	 * Returns registered domain host of plugin holder.
	 *
	 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
	 * so only the host portion of the URL can be sent. For example the host portion might be
	 * www.example.com or example.com. http://www.example.com includes the scheme http,
	 * and the host www.example.com.
	 * Sending only the host also eliminates issues when a client site changes from http to https,
	 * but their activation still uses the original scheme.
	 *
	 * @since 2.6.1
	 *
	 * @return string Domain Host.
	 */
	final public function get_expected_site_domain() {
		return $this->get_option( 'activation_domain' );
	}

	/**
	 * Returns domain host of plugin holder.
	 *
	 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
	 * so only the host portion of the URL can be sent. For example the host portion might be
	 * www.example.com or example.com. http://www.example.com includes the scheme http,
	 * and the host www.example.com.
	 * Sending only the host also eliminates issues when a client site changes from http to https,
	 * but their activation still uses the original scheme.
	 *
	 * @since 2.6.1
	 *
	 * @return string Domain Host.
	 */
	final public function get_current_site_domain() {
		return preg_replace( '~^(https?:)?//~i', '', \esc_url( \get_home_url(), [ 'https', 'http' ] ) );
	}

	/**
	 * Determines the API key origin.
	 *
	 * @since 2.1.0
	 * @since 2.6.3
	 *
	 * @param string $key The API key.
	 * @return string The endpoint origin.
	 */
	final protected function get_key_endpoint_origin( $key ) {

		if ( preg_match( '/(?:^|_)tsfeu_/', $key ) )
			return 'eu';

		if ( preg_match( '/(?:^|_)tsfwm_/', $key ) )
			return 'wcm';

		return 'global';
	}

	/**
	 * Returns the API endpoint type.
	 *
	 * @since 2.1.0
	 *
	 * @return string $type The endpoint type. Passed by reference.
	 */
	final public function &get_api_endpoint_type() {
		static $type = 'global';
		return $type;
	}

	/**
	 * Sets the endpoint type.
	 *
	 * @since 2.1.0
	 * @since 2.6.3 Now considers WCM endpoint.
	 * @uses $this->get_api_endpoint_type()
	 *
	 * @param string|null $type Set to null to autodetermine. Accepts 'eu', 'wcm', and 'global'.
	 */
	final protected function set_api_endpoint_type( $type = null ) {

		$endpoint = &$this->get_api_endpoint_type();

		if ( $type ) {
			switch ( $type ) {
				case 'global':
				case 'eu':
				case 'wcm':
					$endpoint = $type;
					break;
				default:
					$endpoint = 'global';
			}
		} elseif ( $this->is_connected_user() ) {
			$endpoint = $this->get_key_endpoint_origin( $this->get_subscription_status()['key'] );
		} else {
			$endpoint = 'global';
		}
	}

	/**
	 * Returns activation domain URL.
	 *
	 * @since 1.0.0
	 * @since 2.1.0 Now considers EU endpoint.
	 * @since 2.6.3 Now considers WCM endpoint.
	 *
	 * @param string $path The URL Path.
	 * @return string
	 */
	final protected function get_activation_url( $path = '' ) {

		switch ( $this->get_api_endpoint_type() ) {
			case 'eu':
				$uri = \TSF_EXTENSION_MANAGER_PREMIUM_EU_URI;
				break;

			case 'wcm':
				$uri = \TSF_EXTENSION_MANAGER_PREMIUM_WCM_URI;
				break;

			case 'global':
			default:
				$uri = \TSF_EXTENSION_MANAGER_PREMIUM_URI;
				break;
		}

		return $uri . ltrim( $path, ' \\/' );
	}

	/**
	 * Generates software API URL to connect to the WooCommerce API manager.
	 *
	 * @since 1.0.0
	 * @since 1.3.2 Circumvented improper separator and encoding.
	 * @link http://php.net/arg-separator.output
	 *
	 * @param array $args The API query parameters.
	 * @return string The escaped API URL with parameters.
	 */
	final protected function get_api_url( $args = [] ) {

		$api_url = \add_query_arg( 'wc-api', 'tsfem-software-api', $this->get_activation_url() );

		return \sanitize_url(
			$api_url . '&' . http_build_query( $args, '', '&', \PHP_QUERY_RFC1738 ),
			[ 'https', 'http' ]
		);
	}

	/**
	 * Connects to the main plugin API handler.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 The security parameters are now passed by reference.
	 * @access private
	 * @see $this->get_api_response();
	 *
	 * @param array  $args      The API query parameters.
	 * @param string $_instance The verification instance key. Passed by reference.
	 * @param int    $bits      The verification instance bit. Passed by reference.
	 * @return string|boolean The escaped API URL with parameters. False on failed instance verification.
	 */
	final public function _get_api_response( $args, &$_instance, &$bits ) {

		if ( $this->_verify_instance( $_instance, $bits[1] ) )
			return $this->get_api_response( $args, false );

		return false;
	}

	/**
	 * Connects to the main plugin API handler.
	 *
	 * @since 1.0.0
	 * @since 2.1.0 Now listens to the `TSF_EXTENSION_MANAGER_API_VERSION` and `TSF_EXTENSION_MANAGER_DEV_API` constants.
	 * @see $this->handle_activation_request() The request validation wrapper.
	 *
	 * @param array $args     The API query parameters.
	 * @param bool  $internal Whether the API call is for $this object.
	 * @return string Response body. Empty string if no body or incorrect parameter given.
	 */
	final protected function get_api_response( $args, $internal = true ) {

		$defaults = [
			'request'  => '',
			'email'    => '',
			'api_key'  => '',
			'instance' => $this->get_options_instance_key(),
			'platform' => $this->get_current_site_domain(),
			'version'  => \TSF_EXTENSION_MANAGER_API_VERSION,
		];

		if ( \TSF_EXTENSION_MANAGER_DEV_API )
			$args['dev'] = \TSF_EXTENSION_MANAGER_DEV_API;

		$args = \wp_parse_args( $args, $defaults );

		if ( empty( $args['request'] ) ) {
			$internal && $this->set_error_notice( [ 201 => '' ] );
			return false;
		}

		$this->set_api_endpoint_type( $this->get_key_endpoint_origin( $args['api_key'] ) );

		$target_url = $this->get_api_url( $args );

		$http_args = [
			/**
			 * @since 1.0.0
			 * @param int $timeout 7 seconds should be more than sufficient and equals
			 *                     the API server keep_alive_timeout. WP default is 5.
			 */
			'timeout'     => \apply_filters( 'tsf_extension_manager_request_timeout', 7 ),
			/**
			 * @since 1.0.0
			 * @param string $httpversion HTTP 1.1 is used for improved performance.
			 *                            WP default is '1.0'
			 */
			'httpversion' => \apply_filters( 'tsf_extension_manager_http_request_version', '1.1' ),
		];

		$request = \wp_safe_remote_get( $target_url, $http_args );

		if ( 200 !== (int) \wp_remote_retrieve_response_code( $request ) ) {
			$internal && $this->set_error_notice( [ 202 => '' ] );
			return false;
		}

		return \wp_remote_retrieve_body( $request );
	}

	/**
	 * Handles AME response and sets options.
	 *
	 * @since 1.0.0
	 * @since 2.6.1 Renamed to more accurately describe the function.
	 * @see $this->handle_activation_request() The request validation wrapper.
	 *
	 * @param array  $args : {
	 *    'request'          => string The request type.
	 *    'api_key'          => string The license key used.
	 *    'activation_email' => string The activation email used.
	 * }
	 * @param string $response The obtained response body.
	 * @return ?bool True on successful response, false on failure, null on invalid response.
	 */
	final protected function handle_activation_response( $args, $response ) {

		if ( ! $response ) {
			$this->set_error_notice( [ 301 => '' ] );
			return false;
		}

		$results = json_decode( $response, true );

		$_response = '';

		// If the user's already using a free account, don't deactivate.
		$registered_free = $this->is_plugin_activated() && ! $this->is_connected_user();

		if ( 'status' !== $args['request'] ) {
			if ( 'activation' === $args['request'] ) :
				$_response = $this->handle_premium_activation( $args, $results );
			elseif ( 'deactivation' === $args['request'] ) :
				$_response = $this->handle_premium_disconnection( $args, $results );
			endif;
		} else {
			$_response = $results;
		}

		if ( ! empty( $results['code'] ) ) :
			switch ( $results['code'] ) :
				case 106: // Inactive/expired subscription.
					$this->set_error_notice( [ 308 => '' ] );
					$registered_free or $this->do_deactivation( false, true );
					break;
				case 105: // Mismatching license key from other data.
					$this->set_error_notice( [ 307 => '' ] );
					$registered_free or $this->do_deactivation( false, true );
					break;
				case 104: // Invalid instance ID.
					$this->set_error_notice( [ 306 => '' ] );
					$registered_free or $this->do_deactivation( false, true );
					break;
				case 103: // Exceeded number of activations.
					$this->set_error_notice( [ 305 => '' ] );
					$registered_free or $this->do_deactivation( false, true );
					break;
				case 102: // Miscellaneous software error on TSF servers.
					$this->set_error_notice( [ 304 => '' ] );
					$registered_free or $this->do_deactivation( false, true );
					break;
				case 101: // Invalid license key inputted.
					$this->set_error_notice( [ 303 => '' ] );
					$registered_free or $this->do_deactivation( false, true );
					break;
				case 100: // Unspecified error. Set $moe to true.
					$this->set_error_notice( [ 302 => '' ] );
					$registered_free or $this->do_deactivation( true, true );
					break;
			endswitch;
		endif;

		return $_response;
	}

	/**
	 * Returns API secret for a final static class.
	 *
	 * @REVIEW: This key MUST only given to objects that can make API calls privately.
	 * The final static class mustn't be instanced.
	 *
	 * @since 1.5.0
	 * @internal
	 * @access private
	 *
	 * @param string $class     The class instance name.
	 * @param string $_instance The verification instance. Passed by reference.
	 * @param array  $bits      The verification bits. Passed by reference.
	 * @return string|false The storage key, false on failure.
	 */
	final public function _init_final_static_extension_api_access( $class, &$_instance, &$bits ) {

		if ( $this->_blocked_extension_file( $_instance, $bits[1] ) )
			return false;

		return $this->_create_protected_api_access_key( $class );
	}

	/**
	 * Generates an internal API access key.
	 *
	 * @REVIEW: This key MUST only given to objects that can make API calls privately.
	 * The final static class mustn't be instanced, or it must be bound to Secure_Abstract.
	 *
	 * @since 2.1.0
	 * @internal
	 *
	 * @param string $class The class instance name.
	 * @return string The storage key.
	 */
	final protected function _create_protected_api_access_key( $class ) {
		return $this->generate_api_access_key( $class )[ $class ];
	}

	/**
	 * Gets API response for extension.
	 *
	 * @since 1.5.0
	 * @internal
	 * @access private
	 *
	 * @param object $object The caller object. The object must match the key.
	 * @param string $key    The API access key for $object.
	 * @param array  $args : {
	 *   'request' => string The request type.
	 *    ...      => mixed  Additional parameters.
	 * }
	 * @return bool|string False on failure. JSON/HTML response otherwise.
	 */
	final public function _get_protected_api_response( $object, $key, $args ) {

		if ( ! $this->_verify_api_access( $object, $key ) )
			return false;

		$subscription = $this->get_subscription_status();

		$args = array_merge(
			$args,
			[
				'email'   => $subscription['email'],
				'api_key' => $subscription['key'],
			]
		);

		return $this->get_api_response( $args, false );
	}

	/**
	 * Verifies API access.
	 *
	 * @since 1.5.0
	 *
	 * @param object $object The class object to verify.
	 * @param string $key    The attached object key.
	 * @return bool True if verified, false otherwise.
	 */
	private function _verify_api_access( $object, $key ) {
		return (
			$this->generate_api_access_key()[ \get_class( $object ) ] ?? null
		) === (string) $key;
	}

	/**
	 * Generates API access keys.
	 *
	 * @since 1.5.0
	 * @since 2.1.0 Enabled entropy to prevent system sleep.
	 *
	 * @param string|bool $class The class name. If false, no key is generated.
	 * @return array $keys, the storage keys. Passed by reference.
	 */
	private function &generate_api_access_key( $class = false ) {

		static $keys = [];

		if ( false !== $class )
			$keys[ $class ] = mt_rand() . uniqid( '', true );

		return $keys;
	}
}
