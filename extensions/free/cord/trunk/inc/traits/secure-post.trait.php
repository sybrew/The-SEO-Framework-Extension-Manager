<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Traits
 */
namespace TSF_Extension_Manager\Extension\Cord;

defined( 'ABSPATH' ) or die;

/**
 * Cord extension for The SEO Framework
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
 * Holds secure POST functions for @package TSF_Extension_Manager\Extension\Cord\Settings.
 *
 * Note: This trait has dependencies!
 *
 * @since 1.0.0
 * @uses trait \TSF_Extension_Manager\Extension_Options
 * @uses trait \TSF_Extension_Manager\Error
 * @access private
 * @errorval 109xxxx
 */
trait Secure_Post {

	/**
	 * The POST nonce validation name, action and name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 * @var string The validation request name.
	 * @var string The validation nonce action.
	 */
	protected $nonce_name;
	protected $request_name = [];
	protected $nonce_action = [];

	/**
	 * Sets extension nonces.
	 *
	 * @since 1.0.0
	 */
	protected function set_nonces() {

		$this->nonce_name = 'tsfem_e_cord_nonce_name';

		$this->request_name = [
			//* Reference convenience.
			'default' => 'default',

			//* Update options.
			'update'  => 'update',
		];

		$this->nonce_action = [
			//* Reference convenience.
			'default' => 'tsfem_e_cord_nonce_action',

			//* Update options.
			'update'  => 'tsfem_e_cord_nonce_action_cord_update',
		];
	}

	/**
	 * Checks POST for data through admin actions.
	 *
	 * @since 1.0.0
	 */
	protected function init_post_checks() {

		// AJAX only, not registered. Also, this method fires AFTER admin_init, so it went by unnoticed.
		// \add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		if ( \wp_doing_ajax() ) {
			$this->init_ajax_post_checks();
		} else {
			$this->init_default_post_checks();
		}
	}

	/**
	 * Checks AJAX POST for data through admin actions.
	 *
	 * @since 1.0.0
	 */
	protected function init_ajax_post_checks() {}

	/**
	 * Checks POST for data through admin actions.
	 *
	 * @since 1.0.0
	 */
	protected function init_default_post_checks() {}

	/**
	 * Checks AJAX form POST for data through admin actions.
	 *
	 * @NOTE: Nonce and user capabilities MUST be validated before calling this.
	 *
	 * @since 1.0.0
	 * @uses trait \TSF_Extension_Manager\Extension_Options
	 * @uses trait \TSF_Extension_Manager\Error
	 * @uses class \TSF_Extension_Manager\Extension\Cord\Options
	 */
	public function _do_ajax_form_save() {

		$post_data = isset( $_POST['data'] ) ? $_POST['data'] : ''; // CSRF, sanitization, input var ok.

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
			$send['results'] = $this->get_ajax_notice( false, 1090100 );
		} else {

			$options = $data[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ];
			$success = $this->update_stale_options_array_by_key( $options );
			$this->process_all_stored_data();

			if ( ! $success ) {
				$type            = 'failure';
				$send['results'] = $this->get_ajax_notice( false, 1090101 );
			} else {
				$type            = 'success';
				$send['results'] = $this->get_ajax_notice( true, 1090102 );
				$send['sdata']   = $this->get_stale_option( key( $options ) );
			}
		}

		\tsf_extension_manager()->send_json( $send, \tsf_extension_manager()->coalesce_var( $type, 'failure' ) );
	}
}
