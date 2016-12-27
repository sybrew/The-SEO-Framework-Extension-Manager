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
 * Class TSF_Extension_Manager\API
 *
 * Holds plugin API functions.
 *
 * @since 1.0.0
 * @access private
 */
class API extends Core {
	use Enclose_Stray_Private, Construct_Child_Interface;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() { }

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
		return str_ireplace( array( 'http://', 'https://' ), '', \esc_url( home_url() ) );
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

		if ( empty( $instance ) ) {
			$instance = trim( \wp_generate_password( 32, false ) );

			if ( $save_option )
				$this->update_option( '_instance', $instance );
		}

		return $instance;
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
	 * Generates software API URL to connect to the WooCommerce API manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The API query parameters.
	 * @return string The escaped API URL with parameters.
	 */
	protected function get_api_url( $args = array() ) {

		$api_url = \add_query_arg( 'wc-api', 'tsfem-software-api', $this->get_activation_url() );

		return \esc_url_raw( $api_url . '&' . http_build_query( $args ) );
	}

	/**
	 * Connects to the main plugin API handler.
	 *
	 * @since 1.0.0
	 * @access private
	 * @see $this->get_api_response();
	 *
	 * @param array $args The API query parameters.
	 * @param string $instance The verification instance key.
	 * @param int $bit The verification instance bit.
	 * @return string|boolean The escaped API URL with parameters. False on failed instance verification.
	 */
	public function _get_api_response( $args = array(), $_instance, $bits ) {

		if ( $this->_verify_instance( $_instance, $bits[1] ) ) {
			return $this->get_api_response( $args );
		}

		return false;
	}

	/**
	 * Connects to the main plugin API handler.
	 *
	 * @since 1.0.0
	 * @see $this->handle_request() The request validation wrapper.
	 *
	 * @param array $args The API query parameters.
	 * @return string Response body. Empty string if no body or incorrect parameter given.
	 */
	protected function get_api_response( $args ) {

		$defaults = array(
			'request'          => '',
			'email'            => '',
			'licence_key'      => '',
			'instance'         => $this->get_activation_instance( false ),
			'platform'         => $this->get_activation_site_domain(),
		);

		$args = \wp_parse_args( $args, $defaults );

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
			'timeout' => \apply_filters( 'tsf_extension_manager_request_timeout', 7 ),
			'httpversion' => \apply_filters( 'tsf_extension_manager_http_request_version', '1.1' ),
		);

		$request = \wp_safe_remote_get( $target_url, $http_args );

		if ( 200 !== (int) \wp_remote_retrieve_response_code( $request ) ) {
			$this->set_error_notice( array( 202 => '' ) );
			return false;
		}

		$response = \wp_remote_retrieve_body( $request );

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
	 * @param bool $explain Whether to show additional info in error messages.
	 * @return bool True on successful response, false on failure.
	 */
	protected function handle_response( $type = 'status', $response = '', $explain = false ) {

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

		$additional_info = '';

		//* If the user's already using a free account, don't deactivate.
		$free = $this->is_plugin_activated() && false === $this->is_premium_user();

		if ( isset( $results['code'] ) ) :
			switch ( $results['code'] ) :
				case '100' :
					$additional_info = $explain && ! empty( $results['additional info'] ) ? \esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 302 => $additional_info ) );
					$free or $this->do_deactivation( true );
					break;
				case '101' :
					$additional_info = $explain && ! empty( $results['additional info'] ) ? \esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 303 => $additional_info ) );
					$free or $this->do_deactivation();
					break;
				case '102' :
					$additional_info = $explain && ! empty( $results['additional info'] ) ? \esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 304 => $additional_info ) );
					$free or $this->do_deactivation();
					break;
				case '103' :
					$additional_info = $explain && ! empty( $results['additional info'] ) ? \esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 305 => $additional_info ) );
					$free or $this->do_deactivation();
					break;
				case '104' :
					$additional_info = $explain && ! empty( $results['additional info'] ) ? \esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 306 => $additional_info ) );
					$free or $this->do_deactivation();
					break;
				case '105' :
					$additional_info = $explain && ! empty( $results['additional info'] ) ? \esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 307 => $additional_info ) );
					$free or $this->do_deactivation();
					break;
				case '106' :
					$additional_info = $explain && ! empty( $results['additional info'] ) ? \esc_attr( $results['additional info'] ) : '';
					$this->set_error_notice( array( 308 => $additional_info ) );
					$free or $this->do_deactivation( true );
					break;
				default :
					break;
			endswitch;
		endif;

		return $_response;
	}
}
