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

		return $software_title = $this->get_activation_product_title();

		return $prefix = str_ireplace( array( ' ', '_', '&', '?' ), '_', strtolower( $software_title ) );
	}

	protected function get_activation_instance() {

		$prefix = $this->get_activation_prefix();
		$instance = get_option( $prefix . '_instance', false );

		if ( false === $instance ) {
			update_option( $prefix . '_instance', wp_generate_password( 12, false ) );
			$instance = get_option( $prefix . '_instance' );
		}

		return $instance;
	}

	protected function get_activation_product_id() {
		return $this->get_activation_product_title();
		return $this->get_activation_prefix() . '_product_id';
	}

	protected function get_api_url( $args = array() ) {

		$api_url = add_query_arg( 'wc-api', 'am-software-api', $this->get_activation_url() );

		return esc_url_raw( $api_url . '&' . http_build_query( $args ) );
	}

	/**
	 * Fetches activation API request and returns response data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return bool true on success.
	 */
	protected function handle_activation_request( $args = array() ) {

		if ( ! isset( $args['activation_email'] ) )
			return false;

		if ( ! isset( $args['licence_key'] ) )
			return false;

		$request = array(
			'request'          => 'activation',
			'email'            => $args['activation_email'],
			'licence_key'      => $args['licence_key'],
		);

		$response = $this->get_api_response( $request );

		return $response;
	}

	/**
	 * Fetches deactivation API request and returns response data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return bool true on success.
	 */
	protected function handle_deactivation_request( $args = array() ) {

		if ( ! isset( $args['activation_email'] ) )
			return false;

		if ( ! isset( $args['licence_key'] ) )
			return false;

		$request = array(
			'request'          => 'deactivation',
			'email'            => $args['activation_email'],
			'licence_key'      => $args['licence_key'],
		);

		$response = $this->get_api_response( $request );

		return $response;
	}

	/**
	 * Fetches status API request and returns response data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return bool true on success.
	 */
	protected function handle_status_request( $args = array() ) {

		if ( ! isset( $args['activation_email'] ) )
			return false;

		if ( ! isset( $args['licence_key'] ) )
			return false;

		$request = array(
			'request'          => 'status',
			'email'            => $args['activation_email'],
			'licence_key'      => $args['licence_key'],
		);

		$response = $this->get_api_response( $request );

		return $response;
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
			'product_id'       => $this->get_activation_product_id(),
			'instance'         => $this->get_activation_instance(),
			'platform'         => $this->get_activation_site_domain(),
			'software_version' => '1.0.0' // Always 1.0.0, as it's not software, but a "placeholder".
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['request'] ) )
			return false;

		var_dump( $args );

		$target_url = $this->get_api_url( $args );
		$request = wp_safe_remote_get( $target_url );

		// $request = wp_remote_post( $this->api_url . 'wc-api/am-software-api/', array( 'body' => $args ) );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			// Request failed
			return false;
		}

		$response = wp_remote_retrieve_body( $request );
		return $response;
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
