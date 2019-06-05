<?php
/**
 * @package TSF_Extension_Manager\Classes
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
 * Class TSF_Extension_Manager\AJAX.
 *
 * Handles AJAX requests for the main plugin.
 *
 * @since 2.1.0
 * @internal
 * @access private
 * @final
 */
final class AJAX extends Secure_Abstract {
	use Error;

	/**
	 * We don't rely fully on Secure_Abstract, such as action verification;
	 * this is in conflict of the abstract nature of WordPress' AJAX.
	 *
	 * As such, we use this.
	 *
	 * @var bool Whether the instance is validated.
	 */
	private static $_validated = false;

	/**
	 * @var null|AJAX The class instance.
	 */
	private static $instance = null;

	/**
	 * @var null|object TSF Extension Manager class object.
	 */
	private static $tsfem;

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 2.1.0
	 *
	 * @param string $type     Unused. The instance type.
	 * @param string $instance Required. The instance key. Passed by reference.
	 * @param array  $bits     Required. The instance bits. Passed by reference.
	 */
	public static function initialize( $type = '', &$instance = '', &$bits = [] ) {

		self::reset();

		// REVIEW me, always. Bypasses internal security checks.
		self::set( '_wpaction' );
		self::set( '_type', 'generic' );

		static::$tsfem = \tsf_extension_manager();
		static::$tsfem->_verify_instance( $instance, $bits[1] ) or die;

		static::$_validated = true;
		static::$instance   = new static;

		static::load_actions();
	}

	/**
	 * Returns false, unused.
	 *
	 * @since 2.1.0
	 *
	 * @param string $type Determines what to get.
	 * @return bool false
	 */
	public static function get( $type = '' ) {
		return false;
	}

	/**
	 * Determines if the current user can access settings.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	private static function can_do_settings() {
		return \tsf_extension_manager()->can_do_settings();
	}

	/**
	 * Loads actions.
	 *
	 * @since 2.1.0
	 * @access private
	 */
	private static function load_actions() {

		//* Ajax listener for error notice catching.
		\add_action( 'wp_ajax_tsfem_get_dismissible_notice', static::class . '::_wp_ajax_get_dismissible_notice' );
		\add_action( 'wp_ajax_tsfem_inpost_get_dismissible_notice', static::class . '::_wp_ajax_inpost_get_dismissible_notice' );

		//* AJAX listener for form iterations.
		\add_action( 'wp_ajax_tsfemForm_iterate', static::class . '::_wp_ajax_tsfemForm_iterate', 11 );

		//* AJAX listener for form saving.
		\add_action( 'wp_ajax_tsfemForm_save', static::class . '::_wp_ajax_tsfemForm_save', 11 );

		//* AJAX listener for Geocoding.
		\add_action( 'wp_ajax_tsfemForm_get_geocode', static::class . '::_wp_ajax_tsfemForm_get_geocode', 11 );

		//* AJAX listener for image cropping.
		\add_action( 'wp_ajax_tsfem_crop_image', static::class . '::_wp_ajax_crop_image' );

		/**
		 * @since 2.1.0
		 */
		\do_action( 'tsf_extension_manager_ajax_loaded' );
	}

	/**
	 * Send AJAX notices. If any.
	 *
	 * @since 1.3.0
	 * @see static:;build_ajax_dismissible_notice()
	 * @access private
	 */
	public static function _wp_ajax_get_dismissible_notice() {

		if ( ! static::$_validated ) return;
		if ( ! static::can_do_settings() ) return;

		if ( \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) ) {
			$notice_data = static::build_ajax_dismissible_notice();
		}

		static::$tsfem->send_json(
			static::$tsfem->coalesce_var( $notice_data, [] ),
			static::$tsfem->coalesce_var( $notice_data['type'], 'failure' )
		);
		exit;
	}

	/**
	 * Send AJAX notices for inpost. If any.
	 *
	 * @since 1.5.0
	 * @see static:;build_ajax_dismissible_notice()
	 * @package TSF_Extension_Manager\InpostGUI
	 * @uses class InpostGUI
	 * @access private
	 */
	public static function _wp_ajax_inpost_get_dismissible_notice() {

		if ( ! static::$_validated ) return;

		$post_id = filter_input( INPUT_POST, 'post_ID', FILTER_VALIDATE_INT );
		if ( ! $post_id || ! InpostGUI::current_user_can_edit_post( \absint( $post_id ) ) ) return;

		if ( \check_ajax_referer( InpostGUI::JS_NONCE_ACTION, InpostGUI::JS_NONCE_NAME, false ) ) {
			$notice_data = static::build_ajax_dismissible_notice();
		}

		static::$tsfem->send_json(
			static::$tsfem->coalesce_var( $notice_data, [] ),
			static::$tsfem->coalesce_var( $notice_data['type'], 'failure' )
		);
		exit;
	}

	/**
	 * Propagate FormGenerator class AJAX iteration calls.
	 * Exits when done.
	 *
	 * @since 1.3.0
	 * @uses class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	public static function _wp_ajax_tsfemForm_iterate() {

		if ( ! static::$_validated ) return;
		if ( ! static::can_do_settings() || ! \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false ) ) {
			static::$tsfem->send_json( [ 'results' => static::$instance->get_ajax_notice( false, 9002 ) ], 'failure' );
			exit;
		}

		/**
		 * Allows callers to prepare iteration class.
		 * @see class TSF_Extension_Manager\FormGenerator
		 * @access protected
		 */
		\do_action( 'tsfem_form_prepare_ajax_iterations' );

		/**
		 * Outputs the iteration items when properly prepared and when matched.
		 *
		 * @NOTE: This action shouldn't be hooked into by extensions.
		 *
		 * @see class TSF_Extension_Manager\FormGenerator
		 * @access private
		 */
		\do_action( 'tsfem_form_do_ajax_iterations' );
		exit;
	}

	/**
	 * Propagate FormGenerator class AJAX save calls.
	 *
	 * @since 1.3.0
	 * @uses class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	public static function _wp_ajax_tsfemForm_save() {

		if ( ! static::$_validated ) return;
		if ( ! static::can_do_settings() || ! \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false ) ) {
			static::$tsfem->send_json( [ 'results' => static::$instance->get_ajax_notice( false, 9003 ) ], 'failure' );
			exit;
		}
		/**
		 * Allows callers to save POST data.
		 * @see class TSF_Extension_Manager\FormGenerator
		 * @access protected
		 */
		\do_action( 'tsfem_form_do_ajax_save' );
		exit;
	}

	/**
	 * Returns Geocoding data form FormGenerator's address fields.
	 * On failure, it returns an AJAX error code.
	 *
	 * @since 1.3.0
	 * @see class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	public static function _wp_ajax_tsfemForm_get_geocode() {

		if ( ! static::$_validated ) return;

		if ( ! static::can_do_settings() ) {
			static::$tsfem->send_json( [ 'results' => static::$instance->get_ajax_notice( false, 9004 ) ], 'failure' );
			exit;
		}

		if ( ! \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false ) ) {
			static::$tsfem->send_json( [], 'failure' );
			exit;
		}

		$send = [];

		//= Input gets forwarded to secure location. Sanitization happens externally.
		$input = isset( $_POST['input'] ) ? json_decode( \wp_unslash( $_POST['input'] ) ) : ''; // CSRF, sanitization & input var ok

		if ( ! $input || ! is_object( $input ) ) {
			$send['results'] = static::$instance->get_ajax_notice( false, 17000 );
		} else {
			$account = self::get_property( 'account' );

			$args = [
				'request'     => 'geocoding/get',
				'email'       => $account['email'],
				'licence_key' => $account['key'],
				'data'        => [
					'geodata' => json_encode( $input ),
					//= get_user_locale() is WP 4.7+
					'locale'  => function_exists( '\\get_user_locale' ) ? \get_user_locale() : \get_locale(),
				],
			];

			$response = static::$tsfem->_get_protected_api_response( static::$instance, self::get_property( 'secret_api_key' ), $args );
			$response = json_decode( $response );

			if ( ! isset( $response->success ) ) {
				$send['results'] = static::$instance->get_ajax_notice( false, 17001 );
			} else {
				if ( ! isset( $response->data ) ) {
					$send['results'] = static::$instance->get_ajax_notice( false, 17002 );
				} else {
					$data = json_decode( $response->data, true );

					if ( ! $data ) {
						$send['results'] = static::$instance->get_ajax_notice( false, 17003 );
					} else {
						static::$tsfem->coalesce_var( $data['status'] );

						if ( 'OK' !== $data['status'] ) {
							switch ( $data['status'] ) :
								//* @link https://developers.google.com/maps/documentation/geocoding/intro#reverse-response
								case 'ZERO_RESULTS':
									$send['results'] = static::$instance->get_ajax_notice( false, 17004 );
									break;

								case 'OVER_QUERY_LIMIT':
									// This should never be invoked.
									$send['results'] = static::$instance->get_ajax_notice( false, 17005 );
									break;

								case 'REQUEST_DENIED':
									// This should never be invoked.
									$send['results'] = static::$instance->get_ajax_notice( false, 17006 );
									break;

								case 'INVALID_REQUEST':
									//= Data is missing.
									$send['results'] = static::$instance->get_ajax_notice( false, 17007 );
									break;

								case 'UNKNOWN_ERROR':
									//= Remote Geocoding API error. Try again...
									$send['results'] = static::$instance->get_ajax_notice( false, 17008 );
									break;

								case 'TIMEOUT':
									//= Too many consecutive requests.
									$send['results'] = static::$instance->get_ajax_notice( false, 17009 );
									break;

								case 'RATE_LIMIT':
									//= Too many requests in the last period.
									$send['results'] = static::$instance->get_ajax_notice( false, 17010 );
									break;

								case 'REQUEST_LIMIT_REACHED':
									//= License request limit reached.
									$send['results'] = static::$instance->get_ajax_notice( false, 17013 );
									break;

								case 'LICENSE_TOO_LOW':
								default:
									//= Undefined error.
									$send['results'] = static::$instance->get_ajax_notice( false, 17011 );
									break;
							endswitch;
						} else {
							$send['results'] = static::$instance->get_ajax_notice( false, 17012 );
							$send['geodata'] = $data;
							$_type           = 'success';
						}
					}
				}
			}
		}

		static::$tsfem->send_json( $send, static::$tsfem->coalesce_var( $_type, 'failure' ) );
		exit;
	}

	/**
	 * Builds AJAX notices.
	 *
	 * @since 1.5.0
	 * @uses trait TSF_Extension_Manager\Error
	 * @access private
	 */
	private static function build_ajax_dismissible_notice() {

		$data['key'] = (int) static::$tsfem->coalesce_var( $_POST['tsfem-notice-key'], false ); // phpcs:ignore -- Sanitization, input var OK.

		if ( $data['key'] ) {
			$notice = static::$instance->get_error_notice( $data['key'] );

			if ( is_array( $notice ) ) {
				//= If it has a custom message (already stored in browser), then don't output the notice message.
				$msg  = ! empty( $_POST['tsfem-notice-has-msg'] ) ? $notice['before'] : $notice['message']; // CSRF, input var ok

				$data['notice'] = static::$tsfem->get_dismissible_notice( $msg, $notice['type'], true, false );
				$data['type']   = $notice['type'];
				// $_type  = $data['notice'] ? 'success' : 'failure';
			}
		}

		return $data;
	}
}