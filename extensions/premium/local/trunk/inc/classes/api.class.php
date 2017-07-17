<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Local\Api
 *
 * Holds extension api functionality.
 *
 * @since 1.0.0
 * @access private
 * @errorval 107xxxx
 * @uses TSF_Extension_Manager\Traits
 */
class Api extends Core {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Sub_Once_Interface;

	/**
	 * Constructor. Verifies integrity.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . '\\Admin';
		$this instanceof $that or \wp_die( -1 );

	}

	/**
	 * Retrieves API response for Local SEO data collection.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param string $type The request type.
	 * @param bool $ajax Whether the request call is from AJAX.
	 * @return array The response body. Or error notice on AJAX.
	 */
	protected function get_local_api_response( $type = '', $ajax = false ) {

		if ( empty( $type ) ) {
			$ajax or $this->set_error_notice( [ 1070201 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1070201 ) : false;
		}

		/**
		 * Request verification instances, variables are passed by reference :
		 * 0. Request by class. Pass to yield.
		 * 1. Yield first loop, get options.
		 * 2. Yield second loop. Use options to build API link.
		 */
		\tsf_extension_manager()->_request_premium_extension_verification_instance( $this, $_instance, $bits );
		$count = 1;
		foreach ( \tsf_extension_manager()->_yield_verification_instance( 2, $_instance, $bits ) as $verification ) :
			$bits = $verification['bits'];
			$_instance = $verification['instance'];

			switch ( $count ) :
				case 1 :
					$subscription = \tsf_extension_manager()->_get_subscription_status( $_instance, $bits );
					break;

				case 2 :
					if ( is_array( $subscription ) ) {
						$args = [
							'request'     => 'extension/local/' . $type,
							'email'       => $subscription['email'],
							'licence_key' => $subscription['key'],
						];
						$response = \tsf_extension_manager()->_get_api_response( $args, $_instance, $bits );
					} else {
						\tsf_extension_manager()->_verify_instance( $instance, $bits );
						$ajax or $this->set_error_notice( [ 1070202 => '' ] );
						return $ajax ? $this->get_ajax_notice( false, 1070202 ) : false;
					}
					break;

				default :
					\tsf_extension_manager()->_verify_instance( $instance, $bits );
					break;
			endswitch;
			$count++;
		endforeach;

		$response = json_decode( $response );

		if ( isset( $response->status_check ) && 'inactive' === $response->status_check ) {
			$this->update_option( 'site_marked_inactive', true );
		}

		if ( ! isset( $response->success ) ) {
			$ajax or $this->set_error_notice( [ 1070203 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1070203 ) : false;
		}

		if ( ! isset( $response->data ) ) {
			$ajax or $this->set_error_notice( [ 1070204 => '' ] );
			return $ajax ? $this->get_ajax_notice( false, 1070204 ) : false;
		}

		$data = is_string( $response->data ) ? json_decode( $response->data, true ) : (array) $response->data;

		return [
			'success' => true,
			'data' => $data,
		];
	}

	/**
	 * Fetches remote Local API data to be evaluated.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\Error
	 *
	 * @param bool $ajax Whether this request is done through AJAX.
	 * @return bool|array False on invalid input or on activation failure. True otherwise.
	 *         Array The status notice on AJAX.
	 */
	protected function api_get_remote_data( $ajax = false ) {
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
	 * Renders and returns update button.
	 *
	 * @since 1.0.0
	 * @uses trait \TSF_Extension_Manager\Extension_Forms
	 *
	 * @return string The update button.
	 */
	protected function get_update_button() {

		$class = 'tsfem-button-primary tsfem-button-green tsfem-button-store';
		$name = \__( 'Update Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Save your current settings', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'update' );
		$nonce = $this->_get_nonce_field( 'update' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'         => 'tsfem-e-local-update-form',
			'input'      => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'       => true,
			'ajax-id'    => 'tsfem-e-local-update-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		];

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->local_page_slug ), $args );
	}
}
