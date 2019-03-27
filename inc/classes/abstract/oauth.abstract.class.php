<?php
/**
 * @package TSF_Extension_Manager\Classes\Abstract
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Handles oAuth for various abitrary and undefined applications.
 *
 * @since 2.1.0
 * @access protected
 * @abstract
 */
abstract class OAuth {
	use Enclose_Core_Final,
		Ignore_Properties_Core_Public_Final;

	public $response;
	public $last_response;
	public $auth_type;
	public $nonce;
	public $timestamp;
	public $token;
	public $version;

	public function __construct( $consumer_key, $consumer_secret, $signature_method = 'hmacsha1', $auth_type = 1 ) {
		$this->consumer_key     = $consumer_key;
		$this->consumer_secret  = $consumer_secret;
		$this->signature_method = $signature_method;
		$this->set_auth_type( $auth_type );
	}

	abstract public function generate_signature( $http_method, $url, $extra_parameters = null );

	abstract public function get_access_token( $access_token_url, $auth_session_handle = '', $verifier_token = '', $http_method = 'GET' );

	abstract public function get_request_token( $request_token_url, $url, $extra_parameters = null );

	/**
	 * @param string $url         The request URL.
	 * @param array  $query_args  The GET query arguments.
	 * @param string $http_method Accepts 'GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'OPTIONS', or 'PATCH'.
	 *                            Some transports technically allow others, but should not be assumed. Default 'GET'.
	 * @param array  $http_args   The HTTP request arguments, excluding the method. See $http_method.
	 */
	public function fetch( $url, $query_args = [], $http_method = 'GET', $http_args = [] ) {

		$this->last_response = $this->response;

		$url = \add_query_arg( $query_args, $url );

		$http_args = array_merge( $http_args, [
			'timeout' => 7,
			'headers' => [],
			'cookies' => [],
			'body'    => null,
			'headers' => [],   //! $http_method === 'POST' only.
		] );

		$args['method'] = strtoupper( $http_method );

		return $this->response = \wp_remote_request( $url, $http_args );
	}

	final public function get_response_body() {
		return \wp_remote_retrieve_body( $this->response );
	}

	final public function get_response_header() {
		return \wp_remote_retrieve_headers( $this->response );
	}

	final public function get_last_response() {
		return $this->last_response;
	}

	final public function get_last_response_headers() {
		return \wp_remote_retrieve_headers( $this->last_response );
	}

	final public function get_last_response_body() {
		return \wp_remote_retrieve_body( $this->last_response );
	}

	final public function set_auth_type( $auth_type = 0 ) {
		$this->auth_type = $auth_type;
	}

	final public function set_nonce( $nonce = '' ) {
		$this->nonce = $nonce;
	}

	final public function set_timestamp() {
		$this->timestamp = microtime( true );
	}

	final public function set_token( $token, $token_secret ) {
		$this->token = compact( 'token', 'token_secret' );
	}

	final public function set_version( $version ) {
		$this->version = $version;
	}
}
