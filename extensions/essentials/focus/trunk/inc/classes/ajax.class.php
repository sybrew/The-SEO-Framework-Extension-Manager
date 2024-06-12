<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Classes
 */

namespace TSF_Extension_Manager\Extension\Focus;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Verify integrity and sets up API secret.
 *
 * @since 1.0.0
 */
\define(
	'TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY',
	\tsfem()->_init_final_static_extension_api_access( __NAMESPACE__ . '\\Ajax', $_instance, $bits ) ?: false
);

if ( false === \TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY )
	return;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Require error trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/error' );

/**
 * Class TSF_Extension_Manager\Extension\Focus\Ajax
 *
 * @api extension/focus/%
 * @apikey protected \TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY
 * @access protected
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @errorval 110xxxx
 * @final
 */
final class Ajax {
	use \TSF_Extension_Manager\Construct_Core_Static_Final,
		\TSF_Extension_Manager\Error;

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added inflection-getter callback.
	 * @access private
	 *
	 * @param Admin $_admin Used for integrity.
	 */
	public static function _init( Admin $_admin ) {

		$instance = new static;

		/**
		 * Set error notice option.
		 *
		 * @see trait TSF_Extension_Manager\Error
		 */
		$instance->error_notice_option = 'tsfem_e_focus_ajax_error_notice_option';

		// AJAX definition getter listener.
		\add_action( 'wp_ajax_tsfem_e_focus_get_lexicalforms', [ $instance, '_get_lexicalforms' ] );
		\add_action( 'wp_ajax_tsfem_e_focus_get_inflections', [ $instance, '_get_inflections' ] );
		\add_action( 'wp_ajax_tsfem_e_focus_get_synonyms', [ $instance, '_get_synonyms' ] );
	}

	/**
	 * Returns API response for Focus.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The API request type.
	 * @param mixed  $data The attached API data.
	 * @return bool|void True on success. Void and exit on failure.
	 */
	private function get_api_response( $type, $data ) {
		return \tsfem()->_get_protected_api_response(
			$this,
			\TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY,
			[
				'request' => "extension/focus/$type",
				'data'    => $data,
			]
		);
	}

	/**
	 * Verifies premium status, user access, and user nonce.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|void True on success. Void and exit on failure.
	 */
	private function verify_api_access() {

		$tsfem   = \tsfem();
		$post_id = filter_input( \INPUT_POST, 'post_ID', \FILTER_VALIDATE_INT );

		if ( $post_id && \TSF_Extension_Manager\InpostGUI::current_user_can_edit_post( \absint( $post_id ) ) ) {
			if ( $tsfem->is_premium_user() ) {
				if ( \check_ajax_referer( 'tsfem-e-focus-inpost-nonce', 'nonce', false ) ) {
					return true;
				}
			}
		}

		$results = $this->get_ajax_notice( false, 1109001 );
		$tsfem->send_json( compact( 'results' ), 'failure' );
		exit;
	}

	/**
	 * Gets keyword's lexical forms on AJAX request.
	 *
	 * @since 1.0.0
	 * @uses $this->verify_api_access()
	 * @uses $this->get_api_response()
	 */
	public function _get_lexicalforms() {

		// phpcs:disable, WordPress.Security.NonceVerification -- this is the verification.
		$this->verify_api_access();

		$tsfem = \tsfem();
		$_args = ( $_POST['args'] ?? null ) ?: [];

		$keyword  = isset( $_args['keyword'] ) ? $tsfem->s_ajax_string( $_args['keyword'] ) : '';
		$language = isset( $_args['language'] ) ? $tsfem->s_ajax_string( $_args['language'] ) : '';

		$send = [];

		if ( ! \strlen( $keyword ) || ! $language ) {
			// How in the...
			$send['results'] = $this->get_ajax_notice( false, 1100101 );
		} else {
			$response = $this->get_api_response( 'lexicalform', compact( 'keyword', 'language' ) );
			$response = json_decode( $response );

			if ( empty( $response->success ) ) {
				switch ( $response->data->error ?? '' ) {
					case 'WORD_NOT_FOUND':
						// $keyword is trimmed via `s_ajax_string`
						// Note that JS converts Unicode spacing to ASCII, so if the dictionary API includes more languages, we may need to adjust.
						if ( preg_match( '/[\p{Z}\h\v]/', $keyword ) ) {
							// Suggest not using spaces.
							$send['results'] = $this->get_ajax_notice( false, 1100111 );
						} else {
							$send['results'] = $this->get_ajax_notice( false, 1100102 );
						}
						break;

					case 'REQUEST_LIMIT_REACHED':
						$send['results'] = $this->get_ajax_notice( false, 1100108 );
						break;

					case 'LANGUAGE_SUPPORT_ERROR':
						$send['results'] = $this->get_ajax_notice( false, 1100109 );
						break;

					case 'LICENSE_TOO_LOW':
						$send['results'] = $this->get_ajax_notice( false, 1100110 );
						break;

					default:
					case 'REMOTE_API_BODY_ERROR':
					case 'REMOTE_API_ERROR':
						$send['results'] = $this->get_ajax_notice( false, 1100103 );
				}
			} elseif ( ! isset( $response->data ) ) {
				$send['results'] = $this->get_ajax_notice( false, 1100104 );
			} else {
				$_data = \is_string( $response->data ) ? json_decode( $response->data ) : (object) $response->data;

				if ( isset( $_data->forms ) ) {

					$type = 'success'; // The API responded as intended, although the data may not be useful.

					$send['data']['forms'] = $_data->forms ?: [];

					if ( ! $send['data']['forms'] ) {
						$send['results'] = $this->get_ajax_notice( false, 1100105 );
					} else {
						$send['results'] = $this->get_ajax_notice( true, 1100106 );
					}
				} else {
					if ( isset( $_data->error ) )
						$send['data']['error'] = $_data->error;

					$send['results'] = $this->get_ajax_notice( false, 1100107 );
				}
			}
		}

		$tsfem->send_json( $send, $type ?? 'failure' );

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Gets lexical form's inflections on AJAX request.
	 *
	 * @since 1.4.0
	 * @uses $this->verify_api_access()
	 * @uses $this->get_api_response()
	 */
	public function _get_inflections() {

		// phpcs:disable, WordPress.Security.NonceVerification -- this is the verification.
		$this->verify_api_access();

		$tsfem = \tsfem();
		$_args = ( $_POST['args'] ?? null ) ?: [];

		$form_keys = [ 'category', 'value' ];

		$form     = isset( $_args['form'] ) ? \map_deep( $_args['form'], [ $tsfem, 's_ajax_string' ] ) : '';
		$language = isset( $_args['language'] ) ? $tsfem->s_ajax_string( $_args['language'] ) : '';

		$send = [];

		if ( ! $tsfem->has_required_array_keys( $form, $form_keys ) || ! $language ) {
			// How in the...
			$send['results'] = $this->get_ajax_notice( false, 1100301 );
		} else {
			$keyword = $form['value'];
			$form    = json_encode( $tsfem->filter_keys( $form, $form_keys ) );

			$response = $this->get_api_response( 'inflections', compact( 'form', 'language' ) );
			$response = json_decode( $response );

			if ( empty( $response->success ) ) {
				switch ( $response->data->error ?? '' ) {
					case 'WORD_NOT_FOUND':
						$send['results'] = $this->get_ajax_notice( false, 1100302 );
						break;

					case 'REQUEST_LIMIT_REACHED':
						$send['results'] = $this->get_ajax_notice( false, 1100303 );
						break;

					case 'LANGUAGE_SUPPORT_ERROR':
						$send['results'] = $this->get_ajax_notice( false, 1100309 );
						break;

					case 'LICENSE_TOO_LOW':
						$send['results'] = $this->get_ajax_notice( false, 1100310 );
						break;

					default:
					case 'REMOTE_API_BODY_ERROR':
					case 'REMOTE_API_ERROR':
						$send['results'] = $this->get_ajax_notice( false, 1100304 );
				}
			} elseif ( ! isset( $response->data ) ) {
				$send['results'] = $this->get_ajax_notice( false, 1100305 );
			} else {
				$_data = \is_string( $response->data ) ? json_decode( $response->data ) : (object) $response->data;

				if ( isset( $_data->inflections ) ) {
					$send['data']['inflections'] = $_data->inflections ?: [];

					// When no inflections are returned, or if the one returned is only of the same kind as the keyword, fail.
					// NOTE: Uses weak non-UTF8 strtolower. Users are smart enough to ignore useless data.
					if (
						   ! $send['data']['inflections']
						|| (
							   \count( $send['data']['inflections'] ) < 2
							&& strtolower( $send['data']['inflections'][0] ) === strtolower( $keyword )
						)
					) {
						$send['results'] = $this->get_ajax_notice( false, 1100306 );
					} else {
						$type            = 'success';
						$send['results'] = $this->get_ajax_notice( true, 1100307 );
					}
				} else {
					if ( isset( $_data->error ) )
						$send['data']['error'] = $_data->error;

					$send['results'] = $this->get_ajax_notice( false, 1100308 );
				}
			}
		}

		$tsfem->send_json( $send, $type ?? 'failure' );

		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Gets lexical form's synonyms on AJAX request.
	 *
	 * @since 1.0.0
	 * @uses $this->verify_api_access()
	 * @uses $this->get_api_response()
	 */
	public function _get_synonyms() {

		// phpcs:disable, WordPress.Security.NonceVerification -- this is the verification.
		$this->verify_api_access();

		$tsfem = \tsfem();
		$_args = ( $_POST['args'] ?? null ) ?: [];

		$form_keys = [ 'category', 'value' ];

		$form     = isset( $_args['form'] ) ? \map_deep( $_args['form'], [ $tsfem, 's_ajax_string' ] ) : '';
		$language = isset( $_args['language'] ) ? $tsfem->s_ajax_string( $_args['language'] ) : '';

		$send = [];

		if ( ! $tsfem->has_required_array_keys( $form, $form_keys ) || ! $language ) {
			// How in the...
			$send['results'] = $this->get_ajax_notice( false, 1100201 );
		} else {
			$form = json_encode( $tsfem->filter_keys( $form, $form_keys ) );

			$response = $this->get_api_response( 'synonyms', compact( 'form', 'language' ) );
			$response = json_decode( $response );

			if ( empty( $response->success ) ) {
				switch ( $response->data->error ?? '' ) {
					case 'WORD_NOT_FOUND':
						$send['results'] = $this->get_ajax_notice( false, 1100202 );
						break;

					case 'REQUEST_LIMIT_REACHED':
						$send['results'] = $this->get_ajax_notice( false, 1100208 );
						break;

					case 'LANGUAGE_SUPPORT_ERROR':
						$send['results'] = $this->get_ajax_notice( false, 1100209 );
						break;

					case 'LICENSE_TOO_LOW':
						$send['results'] = $this->get_ajax_notice( false, 1100210 );
						break;

					default:
					case 'REMOTE_API_BODY_ERROR':
					case 'REMOTE_API_ERROR':
						$send['results'] = $this->get_ajax_notice( false, 1100203 );
				}
			} elseif ( ! isset( $response->data ) ) {
				$send['results'] = $this->get_ajax_notice( false, 1100204 );
			} else {
				$_data = \is_string( $response->data ) ? json_decode( $response->data ) : (object) $response->data;

				if ( isset( $_data->synonyms ) ) {
					$type = 'success'; // The API responded as intended, although the data may not be useful.

					$send['data']['synonyms'] = $_data->synonyms ?: [];

					if ( ! $send['data']['synonyms'] ) {
						$send['results'] = $this->get_ajax_notice( false, 1100205 );
					} else {
						$send['results'] = $this->get_ajax_notice( true, 1100206 );
					}
				} else {
					if ( isset( $_data->error ) )
						$send['data']['error'] = $_data->error;

					$send['results'] = $this->get_ajax_notice( false, 1100207 );
				}
			}
		}

		$tsfem->send_json( $send, $type ?? 'failure' );

		// phpcs:enable, WordPress.Security.NonceVerification
	}
}
