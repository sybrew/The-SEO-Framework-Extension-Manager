<?php
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
 * Class TSF_Extension_Manager_Activation
 *
 * Holds plugin activation functions.
 *
 * @since 1.0.0
 */
class TSF_Extension_Manager_Activation extends TSF_Extension_Manager_Core {

	/**
	 * Holds activation input.
	 *
	 * @since 1.0.0
	 * @var string
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
	 * Constructor. Loads parent constructor and initializes actions.
	 */
	public function __construct() {
		parent::__construct();
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
	public function get_activation_site_domain() {
		return str_ireplace( array( 'http://', 'https://' ), '', home_url() );
	}

	/**
	 * Returns activation domain URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_activation_url() {
		return 'https://premium.theseoframework.com/';
	}

	/**
	 * Returns product title to activate.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_activation_product_title() {
		return 'The SEO Framework Premium';
	}

	public function get_activation_prefix() {

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

		$prefix = $this->get_activation_prefix();
		$instance = get_option( $prefix . '_instance', false );

		if ( false === $instance ) {
			update_option( $prefix . '_instance', wp_generate_password( 32, false ) );
			$instance = get_option( $prefix . '_instance' );
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
	 * Initializes AME Software API keys and options.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on set, false on fetch.
	 */
	protected function set_activation_option_keys() {

		static $set = null;

		if ( isset( $set ) )
			return false;

		$prefix = $this->get_activation_prefix();

		/**
		 * Set all AME data defaults.
		 */
		$this->ame_data_key                = $prefix . '_data';
		$this->ame_api_key                 = 'api_key';
		$this->ame_activation_email        = 'activation_email';
		$this->ame_product_id_key          = $prefix . '_product_id';
		$this->ame_instance_key            = $prefix . '_instance';
		$this->ame_activated_key           = $prefix . '_activated';

		/**
		 * Set all AME software update data.
		 */
		$this->ame_options           = get_option( $this->ame_data_key );
		$this->ame_plugin_name       = untrailingslashit( TSF_EXTENSION_MANAGER_PLUGIN_BASENAME );
		$this->ame_product_id        = get_option( $this->ame_product_id_key );
		$this->ame_renew_license_url = $this->get_activation_url() . 'my-account';
		$this->ame_instance_id       = get_option( $this->ame_instance_key );

		return $set = true;
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

		if ( ! isset( $args['licence_key'] ) )
			return false;

		if ( ! isset( $args['activation_email'] ) )
			return false;

		$this->activation_key = $args['licence_key'];
		$this->activation_email = $args['activation_email'];

		$request = array(
			'request'          => $type,
			'email'            => $args['activation_email'],
			'licence_key'      => $args['licence_key'],
		);

		$response = $this->get_api_response( $request );
		$options = $this->handle_response( $type, $response );

		return $options;
	}

	/**
	 * Connects to the main plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return
	 */
	protected function get_api_response( $args ) {

		$defaults = array(
			'request'          => '',
			'product_id'       => $this->get_activation_product_title(),
			'instance'         => $this->get_activation_instance(),
			'platform'         => $this->get_activation_site_domain(),
			'software_version' => '1.0.0' // Always 1.0.0, as it's not software, but a "placeholder".
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['request'] ) )
			return false;

		$target_url = $this->get_api_url( $args );
				var_dump( $target_url );
		$request = wp_safe_remote_get( $target_url );

		// $request = wp_remote_post( $this->get_api_url() . 'wc-api/am-software-api/', array( 'body' => $args ) );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			// Request failed
			return false;
		}

		$response = wp_remote_retrieve_body( $request );
		return $response;
	}

	/**
	 * Handles AME response and sets options.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on successful response, false on failure.
	 */
	protected function handle_response( $type = 'status', $response = '' ) {

		if ( empty( $response ) )
			return false;

		$results = json_decode( $response, true );

		//* Setup keys.
		$this->set_activation_option_keys();

		if ( 'status' !== $type ) {
			if ( 'activation' === $type )
				$this->handle_activation( $results );
			else if ( 'deactivation' === $type )
				$this->handle_deactivation( $results );
		}

		$options = $this->ame_options;

		if ( isset( $results[ 'code' ] ) && ! empty( $options ) && ! empty( $this->ame_activated_key ) ) {
			switch ( $results[ 'code' ] ) {
				case '100':
					$additional_info = ! empty( $results[ 'additional info' ] ) ? esc_attr( $results[ 'additional info' ] ) : '';
					add_settings_error( 'api_email_text', 'api_email_error', "{$results['error']}. {$additional_info}", 'error' );
					$options[ $this->ame_activation_email ] = '';
					$options[ $this->ame_api_key ]          = '';
					update_option( $options[ $this->ame_activated_key ], 'Deactivated' );
					break;
				case '101':
					$additional_info = ! empty( $results[ 'additional info' ] ) ? esc_attr( $results[ 'additional info' ] ) : '';
					add_settings_error( 'api_key_text', 'api_key_error', "{$results['error']}. {$additional_info}", 'error' );
					$options[ $this->ame_api_key ]          = '';
					$options[ $this->ame_activation_email ] = '';
					update_option( $options[ $this->ame_activated_key ], 'Deactivated' );
					break;
				case '102':
					$additional_info = ! empty( $results[ 'additional info' ] ) ? esc_attr( $results[ 'additional info' ] ) : '';
					add_settings_error( 'api_key_purchase_incomplete_text', 'api_key_purchase_incomplete_error', "{$results['error']}. {$additional_info}", 'error' );
					$options[ $this->ame_api_key ]          = '';
					$options[ $this->ame_activation_email ] = '';
					update_option( $options[ $this->ame_activated_key ], 'Deactivated' );
					break;
				case '103':
					$additional_info = ! empty( $results[ 'additional info' ] ) ? esc_attr( $results[ 'additional info' ] ) : '';
					add_settings_error( 'api_key_exceeded_text', 'api_key_exceeded_error', "{$results['error']}. {$additional_info}", 'error' );
					$options[ $this->ame_api_key ]          = '';
					$options[ $this->ame_activation_email ] = '';
					update_option( $options[ $this->ame_activated_key ], 'Deactivated' );
					break;
				case '104':
					$additional_info = ! empty( $results[ 'additional info' ] ) ? esc_attr( $results[ 'additional info' ] ) : '';
					add_settings_error( 'api_key_not_activated_text', 'api_key_not_activated_error', "{$results['error']}. {$additional_info}", 'error' );
					$options[ $this->ame_api_key ]          = '';
					$options[ $this->ame_activation_email ] = '';
					update_option( $options[ $this->ame_activated_key ], 'Deactivated' );
					break;
				case '105':
					$additional_info = ! empty( $results[ 'additional info' ] ) ? esc_attr( $results[ 'additional info' ] ) : '';
					add_settings_error( 'api_key_invalid_text', 'api_key_invalid_error', "{$results['error']}. {$additional_info}", 'error' );
					$options[ $this->ame_api_key ]          = '';
					$options[ $this->ame_activation_email ] = '';
					update_option( $options[ $this->ame_activated_key ], 'Deactivated' );
					break;
				case '106':
					$additional_info = ! empty( $results[ 'additional info' ] ) ? esc_attr( $results[ 'additional info' ] ) : '';
					add_settings_error( 'sub_not_active_text', 'sub_not_active_error', "{$results['error']}. {$additional_info}", 'error' );
					$options[ $this->ame_api_key ]          = '';
					$options[ $this->ame_activation_email ] = '';
					update_option( $options[ $this->ame_activated_key ], 'Deactivated' );
					break;
			}
		}

		return $options;
	}

	protected function handle_activation( $results ) {

		if ( $results[ 'activated' ] === true && ! empty( $this->ame_activated_key ) ) {
			add_settings_error( 'activate_text', 'activate_msg', sprintf( __( '%s is activated. ', $this->text_domain ), $this->get_activation_product_title() ) . "{$results['message']}.", 'updated' );
			update_option( $this->ame_activated_key, 'Activated' );
			update_option( $this->ame_options[ $this->ame_activated_key ], 'Activated' );
			// TODO decide which option to pick.
		}

		if ( $results == false && ! empty( $this->ame_options ) && ! empty( $this->ame_activated_key ) ) {
			add_settings_error( 'api_key_check_text', 'api_key_check_error', __( 'Connection failed to the License Key API server. Please try again later.', $this->text_domain ), 'error' );
			$this->ame_options[ $this->ame_api_key ] = '';
			$this->ame_options[ $this->ame_activation_email ] = '';
			update_option( $this->ame_options[ $this->ame_activated_key ], 'Deactivated' );
		}

	}

	protected function handle_deactivation( $results ) {

		if ( $results[ 'deactivated' ] === true ) {
			$update = array(
				$this->ame_api_key			=> '',
				$this->ame_activation_email	=> ''
			);
			$merge_options = array_merge( $this->ame_options, $update );

			if ( ! empty( $this->ame_activated_key ) ) {
				update_option( $this->ame_data_key, $merge_options );
				update_option( $this->ame_activated_key, 'Deactivated' );
				add_settings_error( 'wc_am_deactivate_text', 'deactivate_msg', __( 'API Key deactivated. ', $this->text_domain ) . "{$results['activations_remaining']}.", 'updated' );
			}
		}

	}

	/**
	 * Determines whether the plugin's use has been verified.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the plugin is connected to the API handler.
	 */
	protected function is_plugin_connected() {
	//	return true;
		return false;
	}

	/**
	 * Determines subscription status.
	 *
	 * @since 1.0.0
	 *
	 * @return array Current subscription status.
	 */
	protected function get_subscription_status() {
		return array( 'active', 'no-sub' );
		return array( 'active', 'used-key' );
		return array( 'active', 'account' );
		return array( 'active', 'expires-soon' );
		return array( 'inactive', 'cancelled' );
		return array( 'inactive', 'suspended' );
	}

	/**
	 * Returns subscription authentication values.
	 *
	 * @since 1.0.0
	 *
	 * @return array Authentication key, type and expire unix time.
	 */
	protected function get_subscription_auth() {
		// TODO
		return array(
			'key' => '83abcf1cc0019755577ec04e6301e8e1',
			'type' => 'key',
			'expire' => date( 'U', strtotime( "+3 days" ) ) // @TODO get and cache unix time.
		);
	}

}
