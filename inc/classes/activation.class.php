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
	 * The activation nonce name and field.
	 *
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 * @var string The validation nonce field.
	 */
	protected $activation_nonce_name;
	protected $activation_nonce_action;

	/**
	 * The activation request status code option name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The activation request status code option name.
	 */
	protected $activation_notice_option;

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Constructor. Loads parent constructor, initializes actions and sets up variables.
	 */
	protected function __construct() {
		parent::__construct();

		$this->activation_nonce_name = 'tsf_extension_manager_activation_nonce_name';
		$this->activation_nonce_action = 'tsf_extension_manager_activation_nonce_action';
		$this->activation_notice_option = 'tsf_extension_manager_activation_notice_option';

		$this->activation_type = array(
			'input'    => 'validate-key',
			'external' => 'external',
			'free'     => 'free',
		);

		add_action( 'admin_init', array( $this, 'handle_activation_post' ) );
		add_action( 'admin_notices', array( $this, 'do_activation_notices' ) );
	//	add_action( 'shutdown', array( $this, 'dump' ) );
	}

	function dump() {
		var_dump( $this->get_all_options() );
		//	var_dump( $this->do_deactivation() );
		//global $wp_actions;var_dump( $wp_actions );
	}

	/**
	 * Handles (de-)activation POST requests.
	 *
	 * @since 1.0.0
	 *
	 * @return bool False if nonce failed.
	 */
	public function handle_activation_post() {

		if ( false === $this->handle_activation_nonce() )
			return;

		$options = $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ];

		switch ( $options['action'] ) :
			case $this->activation_type['input'] :
				//* Sanitation is handled on the request server as well. We're simply making sure all gets through right.
				$args = array(
					'licence_key' => trim( $options['key'] ),
					'activation_email' => sanitize_email( $options['email'] ),
				);

				$response = $this->handle_request( 'activation', $args );
			break;

			case $this->activation_type['free'] :
				$response = $this->activate_free();
			break;

			case $this->activation_type['external'] :
				$response = $this->get_remote_activation_listener_response();
			break;

			default:
				wp_die();
			break;
		endswitch;

		//* Fetches and saves extra subscription status data.
		$this->update_extra_subscription_data();

		the_seo_framework()->admin_redirect( $this->seo_extensions_page_slug, array( 'did-' . $options['action'] => 'true' ) );
		exit;
	}

	/**
	 * Checks the Activation page nonce. Returns false if nonce can't be found or if user isn't allowed to perform nonce.
	 * Performs wp_die() when nonce verification fails.
	 *
	 * Never run a sensitive function when it's returning false. This means no nonce can be verified.
	 *
	 * @since 1.0.0
	 * @staticvar bool $validated Determines whether the nonce has already been verified.
	 *
	 * @return bool True if verified and matches. False if can't verify.
	 */
	public function handle_activation_nonce() {

		static $validated = null;

		if ( isset( $validated ) )
			return $validated;

		if ( ! $this->is_tsf_extension_manager_page() || ! $this->can_do_settings() )
			return $validated = false;

		/**
		 * If this page doesn't parse the site options,
		 * There's no need to filter them on each request.
		 * Nonce is handled elsewhere. This function merely injects filters to the $_POST data.
		 *
		 * @since 1.0.0
		 */
		if ( empty( $_POST ) || ! isset( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] ) || ! is_array( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] ) )
			return $validated = false;

		check_admin_referer( $this->activation_nonce_action, $this->activation_nonce_name );

		return $validated = true;
	}

	/**
	 * Outputs activation notice. If any.
	 *
	 * @since 1.0.0
	 */
	public function do_activation_notices() {

		if ( $option = get_option( $this->activation_notice_option, false ) ) {

			$notice = $this->get_activation_notice( $option );

			if ( empty( $notice ) ) {
				$this->unset_activation_notice();
				return;
			}

			echo the_seo_framework()->generate_dismissible_notice( $notice['message'], $notice['type'] );
			$this->unset_activation_notice();
		}
	}

	/**
	 * Sets activation notice option.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The activation notice.
	 */
	protected function set_activation_notice( $notice = array() ) {
		update_option( $this->activation_notice_option, $notice );
	}

	/**
	 * Removes activation notice option.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The activation notice.
	 */
	protected function unset_activation_notice() {
		delete_option( $this->activation_notice_option );
	}

	/**
	 * Fetches activation notices by option and returns type.
	 *
	 * @since 1.0.0
	 *
	 * @return array The activation notice.
	 */
	protected function get_activation_notice( $option ) {

		if ( is_array( $option ) )
			$key = key( $option );

		if ( empty( $key ) )
			return '';

		switch ( $key ) :
			case 101 :
				$message = esc_html__( 'No valid license key was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 102 :
				$message = esc_html__( 'No valid license email was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 201 :
				$message = esc_html__( 'An empty API request was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 202 :
			case 301 :
			case 401 :
			case 403 :
			case 404 :
			case 503 :
				$message = esc_html__( 'An error occurred while contacting the API server. Please try again later.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 303 :
			case 307 :
				$message = esc_html__( 'Invalid API License Key. Login to your My Account page to find a valid API License Key.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 304 :
				$message = esc_html__( 'Software API error.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 305 :
				$message = esc_html__( 'Exceeded maximum number of activations.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 306 :
				$message = esc_html__( 'Invalid Instance ID. Contact the plugin author.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;

			case 308 :
				$message = esc_html__( 'Subscription is not active or has expired.', 'the-seo-framework-extension-manager' );
				$type = 'warning';
			break;

			case 402 :
				$message = esc_html__( 'Your account has been successfully authorized to be used on this website.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
			break;

			case 501 :
			case 502 :
				$message = esc_html__( 'Your account has been successfully deauthorized from this website.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
			break;

			default:
				$message = esc_html__( 'An unknown error occurred.', 'the-seo-framework-extension-manager' );
				$type = 'error';
			break;
		endswitch;

		switch ( $type ) :
			case 'error' :
			case 'warning' :
				$status_i18n = esc_html__( 'Error code:', 'the-seo-framework-extension-manager' );
			break;

			default :
				$status_i18n = esc_html__( 'Status code:', 'the-seo-framework-extension-manager' );
			break;
		endswitch;

		/* translators: 1: 'Error code:', 2: The error code */
		$status_i18n = sprintf( esc_html__( '%1$s %2$s', 'the-seo-framework-extension-manager' ), $status_i18n, $key );
		$additional_info = $option[ $key ];

		/* translators: 1: Error code, 2: Error message, 3: Additional info */
		$message = sprintf( esc_html__( '%1$s &mdash; %2$s %3$s', 'the-seo-framework-extension-manager' ), $status_i18n, $message, $additional_info );

		return array(
			'message' => $message,
			'type' => $type,
		);
	}

	/**
	 * Handles remote activation request.
	 * Has to validate nonce prior to activating.
	 * Validation is done two-fold (local and activation server).
	 *
	 * @since 1.0.0
	 */
	protected function get_remote_activation_listener() {

		if ( false === $this->handle_activation_nonce() )
			return;

	}

	protected function get_remote_activation_listener_response( $store = false ) {

		static $response = false;

		if ( false !== $store ) {
			$response = $store;
		} else {
			return $response;
		}
	}

	protected function set_remote_activation_listener_response( $value = false ) {

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
			$this->set_activation_notice( array( 101 => '' ) );
			return false;
		}

		if ( empty( $args['activation_email'] ) ) {
			$this->set_activation_notice( array( 102 => '' ) );
			return false;
		}

		$this->activation_key = $args['licence_key'];
		$this->activation_email = $args['activation_email'];

		$request = array(
			'request'     => $type,
			'email'       => $args['activation_email'],
			'licence_key' => $args['licence_key'],
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
			'instance'         => $this->get_activation_instance(),
			'platform'         => $this->get_activation_site_domain(),
			'software_version' => '1.0.0', // Always 1.0.0, as it's not software, but a "placeholder" for the subscription.
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['request'] ) ) {
			$this->set_activation_notice( array( 201 => '' ) );
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
			$this->set_activation_notice( array( 202 => '' ) );
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
			$this->set_activation_notice( array( 301 => '' ) );
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
					$this->set_activation_notice( array( 302 => $additional_info ) );
					$this->do_deactivation();
				break;
				case '101' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_activation_notice( array( 303 => $additional_info ) );
					$this->do_deactivation();
				break;
				case '102' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_activation_notice( array( 304 => $additional_info ) );
					$this->do_deactivation();
				break;
				case '103' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_activation_notice( array( 305 => $additional_info ) );
					$this->do_deactivation();
				break;
				case '104' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_activation_notice( array( 306 => $additional_info ) );
					$this->do_deactivation();
				break;
				case '105' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_activation_notice( array( 307 => $additional_info ) );
					$this->do_deactivation();
				break;
				case '106' :
					$additional_info = ! empty( $results['additional info'] ) ? esc_attr( $results['additional info'] ) : '';
					$this->set_activation_notice( array( 308 => $additional_info ) );
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
				'_activation_expires' => '', // TODO
			);

			$success = $this->do_activation( $args );

			if ( ! $success ) {
				$this->do_deactivation();
				$this->set_activation_notice( array( 401 => '' ) );
				return false;
			}

			$this->set_activation_notice( array( 402 => '' ) );
			return true;
		} elseif ( ! $results && $this->get_option( '_activated' ) ) {
			$this->do_deactivation();
			$this->set_activation_notice( array( 403 => '' ) );
			return false;
		} elseif ( isset( $results['code'] ) ) {
			//* Probably duplicated local activation request. Will be handled later in reponse.
			return false;
		}

		$this->set_activation_notice( array( 404 => '' ) );
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
				$this->set_activation_notice( array( 501 => $message ) );
				return true;
			}

			$this->set_activation_notice( array( 502 => '' ) );
			return false;
		}

		$this->set_activation_notice( array( 503 => '' ) );
		return null;
	}

	/**
	 * Handles product activation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The activation arguments.
	 * @return bool True on success. False on failure.
	 */
	protected function do_activation( $args ) {

		$success = array();

		$success[] = $this->update_option( 'api_key', $args['api_key'] );
		$success[] = $this->update_option( 'activation_email', $args['activation_email'] );
		$success[] = $this->update_option( '_activation_level', $args['_activation_level'] );
		$success[] = $this->update_option( '_activation_expires', $args['_activation_expires'] );
		$success[] = $this->update_option( '_activated', 'Activated' );

		return in_array( false, $success, true );
	}

	/**
	 * Handles product deactivation.
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

		return in_array( false, $success, true );
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
	 * Determines subscription status.
	 *
	 * @since 1.0.0
	 *
	 * @return array Current subscription status.
	 */
	protected function get_subscription_status() {
		return array(
			'key'     => $this->get_option( 'api_key' ),
			'email'   => $this->get_option( 'activation_email' ),
			'active'  => $this->get_option( '_activated' ),
			'level'   => $this->get_option( '_activation_level' ),
			'data'    => $this->get_option( '_data' ),
		);
	}

	/**
	 * Fetches extra remote subscription information and stores it in the data option.
	 * Should happen only run once after subscription has been updated to Premium.
	 *
	 * @since 1.0.0
	 *
	 * @return array The extra subscription information.
	 */
	protected function update_extra_subscription_data() {

		static $response = null;

		if ( isset( $response ) )
			return $response;

		if ( 'Premium' !== $this->get_option( '_activation_level' ) )
			return $response = false;

		$args = array(
			'licence_key' => $this->get_option( 'api_key' ),
			'activation_email' => $this->get_option( 'activation_email' ),
		);

		$response = $this->handle_request( 'status', $args );

		$data = $this->get_option( '_data', array() );
		if ( ! is_array( $data ) )
			$data = array();

		$data['expire'] = isset( $response['status_extra']['end_date'] ) ? $response['status_extra']['end_date'] : null;

		$this->update_option( '_data', $data );

		return $data;
	}

	/**
	 * Updates subscription status in the loop (or at the end of).
	 * Resets option cache afterwards.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The subscription arguments.
	 * @param bool $reset Whether to reset the options cache.
	 */
	protected function update_subscription_status( $args, $reset = true ) {

		$this->do_activation( $args );

		if ( $reset )
			$this->reset_option_cache();

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
	 * @return string Instance key.
	 */
	protected function get_activation_instance() {

		$instance = $this->get_option( '_instance', false );

		if ( false === $instance ) {
			$instance = trim( wp_generate_password( 32, false ) );
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
}
