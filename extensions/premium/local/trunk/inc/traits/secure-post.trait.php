<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Traits
 */

namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds secure POST functions for package \TSF_Extension_Manager\Extension\Local\Settings.
 *
 * Note: This trait has dependencies!
 *
 * @since 1.0.0
 * @uses trait \TSF_Extension_Manager\Extension_Options
 * @uses trait \TSF_Extension_Manager\Error
 * @access private
 * @errorval 107xxxx
 */
trait Secure_Post {

	/**
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 */
	protected $nonce_name;

	/**
	 * @since 1.0.0
	 *
	 * @var string The validation request name.
	 */
	protected $request_name = [];

	/**
	 * @since 1.0.0
	 *
	 * @var string The validation nonce action.
	 */
	protected $nonce_action = [];

	/**
	 * Sets extension nonces.
	 *
	 * @since 1.0.0
	 */
	protected function set_nonces() {

		$this->nonce_name = 'tsfem_e_local_nonce_name';

		$this->request_name = [
			//* Reference convenience.
			'default' => 'default',

			//* Update options.
			'update'  => 'update',
		];

		$this->nonce_action = [
			//* Reference convenience.
			'default' => 'tsfem_e_local_nonce_action',

			//* Update options.
			'update'  => 'tsfem_e_local_nonce_action_local_update',
		];
	}

	/**
	 * Checks POST for data through admin actions.
	 *
	 * @since 1.0.0
	 */
	protected function init_post_checks() {

		// AJAX only, not registered. Also, this method AFTER admin_init, so it went by unnoticed.
		// \add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		if ( \wp_doing_ajax() ) {
			$this->init_ajax_post_checks();
		}
	}

	/**
	 * Checks AJAX POST for data through admin actions.
	 *
	 * Registers iteration callback.
	 *
	 * @since 1.0.0
	 */
	protected function init_ajax_post_checks() {

		/**
		 * Registers and checks form AJAX iteration callback listeners.
		 *
		 * @see class TSF_Extension_Manager\FormGenerator
		 *
		 * Action is called in TSF_Extension_Manager\LoadAdmin::_wp_ajax_tsfemForm_iterate().
		 * It has already checked referrer and capability.
		 * @see \TSF_Extension_Manager\LoadAdmin
		 */
		\add_action( 'tsfem_form_prepare_ajax_iterations', [ $this, '_init_ajax_iteration_callback' ] );

		/**
		 * Listens to AJAX form save.
		 *
		 * @see class TSF_Extension_Manager\FormGenerator
		 *
		 * Action is called in TSF_Extension_Manager\LoadAdmin::_wp_ajax_tsfemForm_save().
		 * It has already checked referrer and capability.
		 * @see \TSF_Extension_Manager\LoadAdmin
		 */
		\add_action( 'tsfem_form_do_ajax_save', [ $this, '_do_ajax_form_save' ] );

		/**
		 * Listens to AJAX form validation
		 */
		\add_action( 'wp_ajax_tsfem_e_local_validateFormJson', [ $this, '_prepare_ajax_form_json_validation' ] );
	}

	/**
	 * Checks AJAX form POST for data through admin actions.
	 *
	 * @NOTE: Nonce and user capabilities MUST be validated before calling this.
	 *
	 * @since 1.0.0
	 * @since 1.1.4 Now strips slashes from POST.
	 * @uses trait \TSF_Extension_Manager\Extension_Options
	 * @uses trait \TSF_Extension_Manager\Error
	 * @uses class \TSF_Extension_Manager\Extension\Local\Options
	 */
	public function _do_ajax_form_save() {

		// phpcs:ignore, WordPress.Security.NonceVerification -- Already done at _wp_ajax_tsfemForm_save()
		$post_data = isset( $_POST['data'] ) ? $_POST['data'] : '';
		parse_str( $post_data, $data );

		$send = [];

		// Nothing to see here.
		if ( ! isset( $data[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) )
			return;

		/**
		 * If this page doesn't parse the site options,
		 * there's no need to check them on each request.
		 */
		if ( ! is_array( $data[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) ) {
			$type            = 'failure';
			$send['results'] = $this->get_ajax_notice( false, 1070100 );
		} else {
			$options = \wp_unslash( $data[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] );
			$success = $this->update_stale_options_array_by_key( $options );
			$this->process_all_stored_data();

			if ( ! $success ) {
				$type            = 'failure';
				$send['results'] = $this->get_ajax_notice( false, 1070101 );
			} else {
				$type            = 'success';
				$send['results'] = $this->get_ajax_notice( true, 1070102 );
				$send['sdata']   = $this->get_stale_option( key( $options ) );
			}
		}

		\tsf_extension_manager()->send_json( $send, \tsf_extension_manager()->coalesce_var( $type, 'failure' ) );
	}

	/**
	 * Registers and checks form AJAX cb listeners.
	 *
	 * @since 1.0.0
	 * @uses class \TSF_Extension_Manager\Extension\Local\Fields
	 * @uses class \TSF_Extension_Manager\FormGenerator
	 */
	public function _init_ajax_iteration_callback() {

		$key = \TSF_Extension_Manager\FormGenerator::_parse_ajax_its_listener( __CLASS__, $this->form_args );

		if ( $key ) {
			$method = $this->get_iterator_callback_by_key( $key );
			if ( $method ) {
				$fields = &\TSF_Extension_Manager\FormGenerator::_collect_ajax_its_fields();
				$fields = Fields::get_instance()->{$method}();
			}
		}
	}

	/**
	 * Fetches the iterator callback by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The valid iterator callback key.
	 * @return string The iteration function name for key.
	 */
	private function get_iterator_callback_by_key( $key ) {

		$items = $this->get_registered_iterator_callbacks();

		return isset( $items[ $key ] ) ? $items[ $key ] : '';
	}

	/**
	 * Returns a list of registered iterator callbacks.
	 *
	 * @since 1.0.0
	 *
	 * @return array The callbacks with their target key and attached method.
	 */
	private function get_registered_iterator_callbacks() {
		return [
			'department'   => 'get_departments_fields',
			'openingHours' => 'get_opening_hours_fields',
		];
	}

	/**
	 * Prepares AJAX form validation checks.
	 *
	 * @since 1.0.0
	 * @see $this->send_ajax_form_json_validation()
	 * @access private
	 */
	public function _prepare_ajax_form_json_validation() {

		if ( \wp_doing_ajax() ) :
			if ( \tsf_extension_manager()->can_do_settings() ) :
				if ( \check_ajax_referer( 'tsfem-e-local-ajax-nonce', 'nonce', false ) ) {
					$this->send_ajax_form_json_validation();
				}
			endif;

			\tsf_extension_manager()->send_json( [ 'results' => $this->get_ajax_notice( false, 1079001 ) ], 'failure' );
		endif;

		exit;
	}

	/**
	 * Packs and sends AJAX form validation that will be passed to Google's Structured
	 * Data validator.
	 * It does not store the packed data.
	 *
	 * @NOTE: Nonce and user capabilities MUST be validated before calling this.
	 *
	 * @since 1.0.0
	 * @since 1.1.4 Now strips slashes from POST.
	 * @see $this->send_ajax_form_json_validation()
	 * @access private
	 */
	private function send_ajax_form_json_validation() {

		// phpcs:disable, WordPress.Security.NonceVerification.Missing -- Caller must check for this.

		$post_data = isset( $_POST['data'] ) ? $_POST['data'] : '';

		parse_str( $post_data, $data );

		$send = [];

		/**
		 * If this page doesn't parse the site options,
		 * there's no need to check them on each request.
		 */
		if ( empty( $data )
		|| ( ! isset( $data[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) )
		|| ( ! is_array( $data[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) )
		) {
			$type            = 'failure';
			$send['results'] = $this->get_ajax_notice( false, 1070200 );
		} else {

			$options = \wp_unslash( $data[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] );
			$data    = $this->pack_data( $options, true );

			if ( ! $data ) {
				$type            = 'failure';
				$send['results'] = $this->get_ajax_notice( false, 1070201 );
			} else {
				$type            = 'success';
				$send['results'] = $this->get_ajax_notice( true, 1070202 );
				$send['tdata']   = '<script type="application/ld+json">' . PHP_EOL . $data . PHP_EOL . '</script>';
			}
		}

		\tsf_extension_manager()->send_json( $send, \tsf_extension_manager()->coalesce_var( $type, 'failure' ) );

		// phpcs:enable, WordPress.Security.NonceVerification.Missing
	}
}
