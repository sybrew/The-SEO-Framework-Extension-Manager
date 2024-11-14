<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
final class AJAX {
	use Error;

	/**
	 * Loads actions.
	 *
	 * @since 2.1.0
	 * @access private
	 */
	public static function load_actions() {

		// Ajax listener for error notice catching.
		\add_action( 'wp_ajax_tsfem_get_dismissible_notice', [ static::class, '_wp_ajax_get_dismissible_notice' ] );
		\add_action( 'wp_ajax_tsfem_inpost_get_dismissible_notice', [ static::class, '_wp_ajax_inpost_get_dismissible_notice' ] );

		// AJAX listener for form iterations.
		\add_action( 'wp_ajax_tsfemForm_iterate', [ static::class, '_wp_ajax_tsfemForm_iterate' ], 11 );

		// AJAX listener for form saving.
		\add_action( 'wp_ajax_tsfemForm_save', [ static::class, '_wp_ajax_tsfemForm_save' ], 11 );

		// AJAX listener for Geocoding.
		\add_action( 'wp_ajax_tsfemForm_get_geocode', [ static::class, '_wp_ajax_tsfemForm_get_geocode' ], 11 );

		// AJAX listener for image cropping.
		\add_action( 'wp_ajax_tsfem_crop_image', [ static::class, '_wp_ajax_crop_image' ] );

		/**
		 * @since 2.1.0
		 */
		\do_action( 'tsf_extension_manager_ajax_loaded' );
	}

	/**
	 * Send AJAX notices. If any.
	 *
	 * WARNING: This method has WEAK access control. Do not store data!
	 *
	 * @since 1.3.0
	 * @since 2.4.0 The access level is now controlled via an extra constant.
	 * @see static::build_ajax_dismissible_notice()
	 * @access private
	 */
	public static function _wp_ajax_get_dismissible_notice() {

		\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
			? \tsf()->headers()->clean_response_header()
			: \tsf()->clean_response_header();

		if ( ! ( \TSF_Extension_Manager\can_do_manager_settings() || \TSF_Extension_Manager\can_do_extension_settings() ) )
			\wp_send_json_error();

		if ( ! \check_ajax_referer( 'tsfem-ajax-insecure-nonce', 'nonce', false ) )
			\wp_send_json_error();

		// Though we say success, it may still result in a "failure" notice!
		\wp_send_json_success( static::build_ajax_dismissible_notice(), 200 );
	}

	/**
	 * Send AJAX notices for inpost. If any.
	 *
	 * @since 1.5.0
	 * @see static::build_ajax_dismissible_notice()
	 * @package TSF_Extension_Manager\InpostGUI
	 * @uses class InpostGUI
	 * @access private
	 */
	public static function _wp_ajax_inpost_get_dismissible_notice() {

		\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
			? \tsf()->headers()->clean_response_header()
			: \tsf()->clean_response_header();

		$post_id = filter_input( \INPUT_POST, 'post_ID', \FILTER_VALIDATE_INT );

		if ( ! $post_id || ! InpostGUI::current_user_can_edit_post( \absint( $post_id ) ) )
			\wp_send_json_error();

		if ( ! \check_ajax_referer( InpostGUI::JS_NONCE_ACTION, InpostGUI::JS_NONCE_NAME, false ) )
			\wp_send_json_error();

		// Though we say success, it may still result in a "failure" notice!
		\wp_send_json_success( static::build_ajax_dismissible_notice(), 200 );
	}

	/**
	 * Propagate FormGenerator class AJAX iteration calls.
	 * Exits when done.
	 *
	 * @since 1.3.0
	 * @since 2.4.0 The extension access level is now controlled via another constant.
	 * @uses class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	public static function _wp_ajax_tsfemForm_iterate() {

		\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
			? \tsf()->headers()->clean_response_header()
			: \tsf()->clean_response_header();

		if (
			   ! \TSF_Extension_Manager\can_do_extension_settings()
			|| ! \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false )
		) {
			\wp_send_json_error( [ 'notice' => static::$instance->get_ajax_notice( false, 9002 ) ] );
		}

		/**
		 * Allows callers to prepare iteration class.
		 *
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
	 * @since 2.2.0 Now handles basic invalid POST data checks, so extensions don't have to.
	 * @since 2.4.0 The extension access level is now controlled via another constant.
	 * @uses class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	public static function _wp_ajax_tsfemForm_save() {

		\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
			? \tsf()->headers()->clean_response_header()
			: \tsf()->clean_response_header();

		if (
			   ! \TSF_Extension_Manager\can_do_extension_settings()
			|| ! \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false )
		) {
			\wp_send_json_error( [ 'notice' => static::$instance->get_ajax_notice( false, 9003 ) ] );
		}

		if ( empty( $_POST['data'] ) )
			\wp_send_json_error( [ 'notice' => static::$instance->get_ajax_notice( false, 17100 ) ] );

		/**
		 * Allows callers to save POST data.
		 *
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
	 * @since 2.4.0 The extension access level is now controlled via another constant.
	 * @see class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	public static function _wp_ajax_tsfemForm_get_geocode() {

		\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
			? \tsf()->headers()->clean_response_header()
			: \tsf()->clean_response_header();

		if (
			   ! \TSF_Extension_Manager\can_do_extension_settings()
			|| ! \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false )
		) {
			\wp_send_json_error( [ 'notice' => static::$instance->get_ajax_notice( false, 9004 ) ] );
		}

		$send = [];

		// Input gets forwarded to secure location. Sanitization happens externally.
		$input = isset( $_POST['input'] ) ? json_decode( \wp_unslash( $_POST['input'] ) ) : '';

		if ( ! $input || ! \is_object( $input ) ) {
			$send['notice'] = static::$instance->get_ajax_notice( false, 17000 );
		} else {
			$args = [
				'request' => 'geocoding/get',
				'data'    => [
					'geodata' => json_encode( $input ),
					'locale'  => \get_user_locale(),
				],
			];

			$response = \tsfem()->_get_protected_api_response( static::$instance, self::get_property( 'secret_api_key' ), $args );
			$response = json_decode( $response );

			if ( ! isset( $response->success ) ) {
				$send['notice'] = static::$instance->get_ajax_notice( false, 17001 );
			} else {
				if ( ! isset( $response->data ) ) {
					$send['notice'] = static::$instance->get_ajax_notice( false, 17002 );
				} else {
					$data = json_decode( $response->data, true );

					if ( ! $data ) {
						$send['notice'] = static::$instance->get_ajax_notice( false, 17003 );
					} else {
						$data['status'] ??= null;

						if ( 'OK' !== $data['status'] ) {
							switch ( $data['status'] ) {
								// @link https://developers.google.com/maps/documentation/geocoding/overview#reverse-response
								case 'ZERO_RESULTS':
									$send['notice'] = static::$instance->get_ajax_notice( false, 17004 );
									break;

								case 'OVER_QUERY_LIMIT':
									// This should never be invoked.
									$send['notice'] = static::$instance->get_ajax_notice( false, 17005 );
									break;

								case 'REQUEST_DENIED':
									// This should never be invoked.
									$send['notice'] = static::$instance->get_ajax_notice( false, 17006 );
									break;

								case 'INVALID_REQUEST':
									// Data is missing.
									$send['notice'] = static::$instance->get_ajax_notice( false, 17007 );
									break;

								case 'UNKNOWN_ERROR':
									// Remote Geocoding API error. Try again...
									$send['notice'] = static::$instance->get_ajax_notice( false, 17008 );
									break;

								case 'TIMEOUT':
									// Too many consecutive requests.
									$send['notice'] = static::$instance->get_ajax_notice( false, 17009 );
									break;

								case 'RATE_LIMIT':
									// Too many requests in the last period.
									$send['notice'] = static::$instance->get_ajax_notice( false, 17010 );
									break;

								case 'REQUEST_LIMIT_REACHED':
									// License request limit reached.
									$send['notice'] = static::$instance->get_ajax_notice( false, 17013 );
									break;

								case 'LICENSE_TOO_LOW':
									$send['notice'] = static::$instance->get_ajax_notice( false, 17011 );
									break;

								default:
									// Undefined error.
									$send['notice'] = static::$instance->get_ajax_notice( false, 17011 );
							}
						} else {
							$send['notice']  = static::$instance->get_ajax_notice( false, 17012 );
							$send['geodata'] = $data;
							$send['success'] = true;
						}
					}
				}
			}
		}

		$send['notice']  ??= $send['notice'] ?? static::$instance->get_ajax_notice( false, 17100 );
		$send['success'] ??= false;

		\wp_send_json( $send );
	}

	/**
	 * Handles cropping of images on AJAX request.
	 *
	 * Copied from WordPress Core wp_ajax_crop_image.
	 * Adjusted: 1. It accepts capability 'upload_files', instead of 'customize'.
	 *           2. It now only accepts TSF own AJAX nonces.
	 *           3. It now only accepts context 'tsf-image'
	 *           4. It no longer accepts a default context.
	 *
	 * @since 1.3.0
	 * @access private
	 * @see The SEO Framework's companion method `wp_ajax_crop_image()`.
	 */
	public static function _wp_ajax_crop_image() {

		\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
			? \tsf()->headers()->clean_response_header()
			: \tsf()->clean_response_header();

		if ( ! \current_user_can( 'upload_files' ) || ! isset( $_POST['id'], $_POST['context'], $_POST['cropDetails'] ) )
			\wp_send_json_error();

		if ( ! \check_ajax_referer( 'tsfem-media-nonce', 'nonce', false ) )
			\wp_send_json_error();

		$attachment_id = \absint( $_POST['id'] );

		if ( ! $attachment_id || 'attachment' !== \get_post_type( $attachment_id ) || ! \wp_attachment_is_image( $attachment_id ) )
			\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'default' ) ) ] );

		$context = str_replace( '_', '-', \sanitize_key( $_POST['context'] ) );
		$data    = array_map( 'absint', $_POST['cropDetails'] );
		$cropped = \wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

		if ( ! $cropped || \is_wp_error( $cropped ) )
			\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'the-seo-framework-extension-manager' ) ) ] );

		switch ( $context ) {
			case 'tsfem-image':
				/**
				 * Fires before a cropped image is saved.
				 *
				 * Allows to add filters to modify the way a cropped image is saved.
				 *
				 * @since 4.3.0 WordPress Core
				 *
				 * @param string $context       The Customizer control requesting the cropped image.
				 * @param int    $attachment_id The attachment ID of the original image.
				 * @param string $cropped       Path to the cropped image file.
				 */
				\do_action( 'wp_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

				/** This filter is documented in wp-admin/includes/class-custom-image-header.php */
				$cropped = \apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

				$parent_url       = \wp_get_attachment_url( $attachment_id );
				$parent_basename  = \wp_basename( $parent_url );
				$cropped_basename = \wp_basename( $cropped );
				$url              = str_replace( $parent_basename, $cropped_basename, $parent_url );

				// phpcs:ignore, WordPress.PHP.NoSilencedErrors -- See https://core.trac.wordpress.org/ticket/42480
				$size       = \function_exists( 'wp_getimagesize' ) ? \wp_getimagesize( $cropped ) : @getimagesize( $cropped );
				$image_type = $size['mime'] ?? 'image/jpeg';

				// Get the original image's post to pre-populate the cropped image.
				$original_attachment  = \get_post( $attachment_id );
				$sanitized_post_title = \sanitize_file_name( $original_attachment->post_title );
				$use_original_title   = (
					\strlen( trim( $original_attachment->post_title ) ) &&
					/**
					 * Check if the original image has a title other than the "filename" default,
					 * meaning the image had a title when originally uploaded or its title was edited.
					 */
					( $parent_basename !== $sanitized_post_title ) &&
					( pathinfo( $parent_basename, \PATHINFO_FILENAME ) !== $sanitized_post_title )
				);
				$use_original_description = \strlen( trim( $original_attachment->post_content ) );

				$attachment = [
					'post_title'     => $use_original_title ? $original_attachment->post_title : $cropped_basename,
					'post_content'   => $use_original_description ? $original_attachment->post_content : $url,
					'post_mime_type' => $image_type,
					'guid'           => $url,
					'context'        => $context,
				];

				// Copy the image caption attribute (post_excerpt field) from the original image.
				if ( \strlen( trim( $original_attachment->post_excerpt ) ) )
					$attachment['post_excerpt'] = $original_attachment->post_excerpt;

				// Copy the image alt text attribute from the original image.
				if ( \strlen( trim( $original_attachment->_wp_attachment_image_alt ) ) )
					$attachment['meta_input'] = [
						'_wp_attachment_image_alt' => \wp_slash( $original_attachment->_wp_attachment_image_alt ),
					];

				$attachment_id = \wp_insert_attachment( $attachment, $cropped );
				$metadata      = \wp_generate_attachment_metadata( $attachment_id, $cropped );

				/**
				 * @since 4.3.0 WordPress Core
				 * @see wp_generate_attachment_metadata()
				 * @param array $metadata Attachment metadata.
				 */
				$metadata = \apply_filters( 'wp_ajax_cropped_attachment_metadata', $metadata );
				\wp_update_attachment_metadata( $attachment_id, $metadata );

				/**
				 * @since 4.3.0 WordPress Core
				 * @param int    $attachment_id The attachment ID of the cropped image.
				 * @param string $context       The Customizer control requesting the cropped image.
				 */
				$attachment_id = \apply_filters( 'wp_ajax_cropped_attachment_id', $attachment_id, $context );
				break;

			default:
				\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'the-seo-framework-extension-manager' ) ) ] );
		}

		\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );
	}

	/**
	 * Builds AJAX notices.
	 *
	 * WARNING: This method has WEAK access control prior being called. Do not store data!
	 *
	 * @since 1.5.0
	 * @uses trait TSF_Extension_Manager\Error
	 * @access private
	 */
	private static function build_ajax_dismissible_notice() {

		// phpcs:disable, WordPress.Security.NonceVerification -- Caller must check for this.
		$data = [];

		$data['key'] = (int) ( $_POST['tsfem-notice-key'] ?? 0 );

		if ( $data['key'] ) {
			$notice = Error::get_error_notice( $data['key'] ); // var_dump() decouple the error?

			if ( \is_array( $notice ) ) {
				// If it has a custom message (already stored in browser), then don't output the notice message.
				$msg = ! empty( $_POST['tsfem-notice-has-msg'] ) ? $notice['before'] : $notice['message'];

				$data['noticeElement'] = \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
					? \tsf()->admin()->notice()->generate_notice(
						$msg,
						[
							'type'   => $notice['type'],
							'escape' => false,
							'inline' => true,
						]
					)
					: \tsf()->generate_dismissible_notice( $msg, $notice['type'], true, false, true );

				$data['noticeType'] = $notice['noticeType'];
			}
		}
		// phpcs:enable, WordPress.Security.NonceVerification

		return $data;
	}
}
