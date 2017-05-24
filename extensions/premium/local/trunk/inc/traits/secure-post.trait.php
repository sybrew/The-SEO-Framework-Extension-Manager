<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Traits
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

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
 * Holds secure POST functions for package TSF_Extension_Manager\Extension\Local.
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

		$this->nonce_name = 'tsfem_e_local_nonce_name';

		$this->request_name = [
			//* Reference convenience.
			'default' => 'default',

			//* Update options.
			'update' => 'update',
		];

		$this->nonce_action = [
			//* Reference convenience.
			'default' => 'tsfem_e_local_nonce_action',

			//* Update options.
			'update' => 'tsfem_e_monitor_nonce_action_local_update',
		];
	}

	/**
	 * Checks POST for data through admin actions.
	 *
	 * @since 1.0.0
	 */
	protected function init_post_checks() {

		//* Update POST listener.
		\add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		//* AJAX update listener.
		\add_action( 'wp_ajax_tsfem_e_local_update', [ $this, '_wp_ajax_update_data' ] );

		//* AJAX API listener.
		\add_action( 'wp_ajax_tsfem_e_local_api_request', [ $this, '_wp_ajax_do_api' ] );
	}

	/**
	 * Handles Monitor POST requests.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void If nonce failed.
	 */
	public function _handle_update_post() {

		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ]['nonce-action'] ) )
			return;

		$options = $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ];

		if ( false === $this->handle_update_nonce( $options['nonce-action'], false ) )
			return;

		switch ( $options['nonce-action'] ) :
			default :
				$this->set_error_notice( [ 1070101 => '' ] );
				break;
		endswitch;

		$args = WP_DEBUG ? [ 'did-' . $options['nonce-action'] => 'true' ] : [];
		\the_seo_framework()->admin_redirect( $this->monitor_page_slug, $args );
		exit;
	}

	/**
	 * Checks the Extension's page nonce. Returns false if nonce can't be found
	 * or if user isn't allowed to perform nonce.
	 * Performs wp_die() when nonce verification fails.
	 *
	 * Never run a sensitive function when it's returning false. This means no
	 * nonce can or has been been verified.
	 *
	 * @since 1.0.0
	 * @staticvar bool $validated Determines whether the nonce has already been verified.
	 *
	 * @param string $key The nonce action used for caching.
	 * @param bool $check_post Whether to check for POST variables containing TSFEM settings.
	 * @return bool True if verified and matches. False if can't verify.
	 */
	final protected function handle_update_nonce( $key = 'default', $check_post = true ) {

		static $validated = [];

		if ( isset( $validated[ $key ] ) )
			return $validated[ $key ];

		if ( false === $this->is_local_page() && false === \tsf_extension_manager()->can_do_settings() )
			return $validated[ $key ] = false;

		if ( $check_post ) {
			/**
			 * If this page doesn't parse the site options,
			 * there's no need to check them on each request.
			 */
			if ( empty( $_POST )
			|| ( ! isset( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) )
			|| ( ! is_array( $_POST[ TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS ][ $this->o_index ] ) )
			) {
				return $validated[ $key ] = false;
			}
		}

		$result = isset( $_POST[ $this->nonce_name ] ) ? \wp_verify_nonce( \wp_unslash( $_POST[ $this->nonce_name ] ), $this->nonce_action[ $key ] ) : false;

		if ( false === $result ) {
			//* Nonce failed. Set error notice and reload.
			$this->set_error_notice( [ 1079001 => '' ] );
			\the_seo_framework()->admin_redirect( $this->monitor_page_slug );
			exit;
		}

		return $validated[ $key ] = (bool) $result;
	}
}
