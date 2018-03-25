<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Classes
 */
namespace TSF_Extension_Manager\Extension\Focus;

defined( 'ABSPATH' ) or die;

/**
 * Verify integrity and sets up API secret.
 * @since 1.0.0
 */
define(
	'TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY',
	\tsf_extension_manager()->_init_final_static_extension_api_access( __NAMESPACE__ . '\\Ajax', $_instance, $bits ) ?: false
);
if ( false === TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY )
	return;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/error' );

/**
 * Class TSF_Extension_Manager\Extension\Focus\Ajax
 *
 * @api extension/focus/%
 * @apikey protected TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY
 * @access protected
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @errorval 110xxxx
 * @final
 */
final class Ajax {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Core_Static_Final,
		\TSF_Extension_Manager\Error;

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Focus\Admin $_admin Used for integrity.
	 */
	public static function _init( Admin $_admin ) {

		$instance = new static;

		/**
		 * Set error notice option.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$instance->error_notice_option = 'tsfem_e_focus_ajax_error_notice_option';

		//* AJAX definition getter listener.
		\add_action( 'wp_ajax_tsfem_e_focus_get_lexicalforms', [ $instance, '_get_lexicalforms' ] );
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
		return \tsf_extension_manager()->_get_extension_api_response(
			$this,
			TSFEM_E_FOCUS_AJAX_API_ACCESS_KEY,
			[
				'request' => 'extension/focus/' . $type,
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

		$tsfem = \tsf_extension_manager();
		$post_id = filter_input( INPUT_POST, 'post_ID', FILTER_VALIDATE_INT );

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

		$this->verify_api_access();

		$tsfem = \tsf_extension_manager();
		$_args = ! empty( $_POST['args'] ) ? $_POST['args'] : [];

		$keyword = isset( $_args['keyword'] ) ? $tsfem->s_ajax_string( $_args['keyword'] ) : '';
		$language = isset( $_args['language'] ) ? $tsfem->s_ajax_string( $_args['language'] ) : '';

		if ( ! strlen( $keyword ) || ! $language ) {
			//= How in the...
			$results = $this->get_ajax_notice( false, 1100101 );
		} else {
			$response = $this->get_api_response( 'lexicalform', compact( 'keyword', 'language' ) );
			$response = json_decode( $response );

			if ( empty( $response->success ) ) {
				switch ( isset( $response->data->error ) ? $response->data->error : '' ) :
					case 'WORD_NOT_FOUND' :
						$results = $this->get_ajax_notice( false, 1100102 );
						break;

					default :
					case 'REMOTE_API_BODY_ERROR' :
					case 'REMOTE_API_ERROR' :
						$results = $this->get_ajax_notice( false, 1100103 );
						break;
				endswitch;
			} elseif ( ! isset( $response->data ) ) {
				$results = $this->get_ajax_notice( false, 1100104 );
			} else {
				$data = is_string( $response->data ) ? json_decode( $response->data ) : (object) $response->data;

				if ( isset( $data->forms ) ) {
					$type = 'success';
					$forms = $data->forms ?: [];
					if ( empty( $forms ) ) {
						$results = $this->get_ajax_notice( false, 1100105 );
					} else {
						$results = $this->get_ajax_notice( true, 1100106 );
					}
				} else {
					if ( isset( $data->error ) )
						$error = $data->error;

					$results = $this->get_ajax_notice( false, 1100107 );
				}
			}
		}

		$data = compact( 'forms', 'error' );

		$tsfem->send_json( compact( 'results', 'data' ), $tsfem->coalesce_var( $type, 'failure' ) );
	}

	/**
	 * Gets lexical form's synonyms on AJAX request.
	 *
	 * @since 1.0.0
	 * @uses $this->verify_api_access()
	 * @uses $this->get_api_response()
	 */
	public function _get_synonyms() {

		$this->verify_api_access();

		$tsfem = \tsf_extension_manager();
		$_args = ! empty( $_POST['args'] ) ? $_POST['args'] : [];

		$form_keys = [ 'category', 'value' ];
		$form = isset( $_args['form'] ) ? \map_deep( $_args['form'], [ $tsfem, 's_ajax_string' ] ) : '';
		$language = isset( $_args['language'] ) ? $tsfem->s_ajax_string( $_args['language'] ) : '';

		if ( ! $tsfem->has_required_array_keys( $form, $form_keys ) || ! $language ) {
			//= How in the...
			$results = $this->get_ajax_notice( false, 1100201 );
		} else {
			$form = json_encode( $tsfem->filter_keys( $form, $form_keys ) );

			$response = $this->get_api_response( 'synonyms', compact( 'form', 'language' ) );
			$response = json_decode( $response );

			if ( empty( $response->success ) ) {
				switch ( isset( $response->data->error ) ? $response->data->error : '' ) :
					case 'WORD_NOT_FOUND' :
						$results = $this->get_ajax_notice( false, 1100202 );
						break;

					default :
					case 'REMOTE_API_BODY_ERROR' :
					case 'REMOTE_API_ERROR' :
						$results = $this->get_ajax_notice( false, 1100203 );
						break;
				endswitch;
			} elseif ( ! isset( $response->data ) ) {
				$results = $this->get_ajax_notice( false, 1100204 );
			} else {
				$data = is_string( $response->data ) ? json_decode( $response->data ) : (object) $response->data;

				if ( isset( $data->synonyms ) ) {
					$type = 'success';
					$synonyms = $data->synonyms ?: [];
					if ( empty( $synonyms ) ) {
						$results = $this->get_ajax_notice( false, 1100205 );
					} else {
						$results = $this->get_ajax_notice( true, 1100206 );
					}
				} else {
					if ( isset( $data->error ) )
						$error = $data->error;

					$results = $this->get_ajax_notice( false, 1100207 );
				}
			}
		}

		$data = compact( 'synonyms', 'error' );

		$tsfem->send_json( compact( 'results', 'data' ), $tsfem->coalesce_var( $type, 'failure' ) );
	}
}
