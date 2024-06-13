<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\AccountActivation
 *
 * Holds plugin activation functions.
 *
 * @since 1.0.0
 * @access private
 * @TODO Convert to instance? It's only required once...
 *       Instancing does expand complexity massively as it handles options.
 */
class AccountActivation extends Panes {
	use Construct_Child_Interface;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() { }

	/**
	 * Handles remote activation request.
	 * Has to validate nonce prior to activating.
	 * Validation is done two-fold (local and activation server).
	 *
	 * @since 1.0.0
	 * @todo use this
	 */
	protected function get_remote_activation_listener() {

		if ( ! $this->handle_update_nonce( $this->request_name['activate-external'] ) )
			return;

		return $this->get_remote_activation_listener_response();
	}

	/**
	 * Fetches external activation response, periodically.
	 *
	 * @since 1.0.0
	 * @ignore
	 * @collector
	 * @todo everything.
	 *
	 * @return bool|array False if data has not yet been set. Array if data has been set.
	 */
	protected function &get_remote_activation_listener_response() {
		static $response = false;
		return $response;
	}

	/**
	 * Sets external activation response.
	 *
	 * @since 1.0.0
	 * @ignore
	 * @todo use this
	 *
	 * @param array $value The data that needs to be set.
	 * @return bool True
	 */
	protected function set_remote_activation_listener_response( $value = [] ) {

		if ( ! $value || \is_wp_error( $value ) )
			return false;

		$store = &$this->get_remote_activation_listener_response();
		$store = $value;

		return true;
	}

	/**
	 * Handles activation and returns status.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Added multilevel support.
	 * @since 2.1.0 Added the $args parameter.
	 *
	 * @param array $args : {
	 *    'request'          => string The request type.
	 *    'api_key'          => string The license key used.
	 *    'activation_email' => string The activation email used.
	 * }
	 * @param array $results The activation response.
	 * @return bool|null True on success, false on failure. Null on invalid request.
	 */
	protected function handle_premium_activation( $args, $results ) {

		if ( ! empty( $results['activated'] ) && ! empty( $results['_activation_level'] ) ) {

			$success = $this->do_premium_activation( [
				'api_key'           => $args['api_key'],
				'activation_email'  => $args['activation_email'],
				'_activation_level' => $results['_activation_level'],
			] );

			if ( ! $success ) {
				$this->do_deactivation( false, true );
				$this->set_error_notice( [ 401 => '' ] );
				return false;
			}

			$this->set_error_notice( [ 402 => '' ] );
			return true;
		} elseif ( ! $results ) {
			if ( $this->get_option( '_activated' ) ) {
				// Upgrade request.
				$this->set_error_notice( [ 403 => '' ] );
			} else {
				// Activation request.
				$this->do_deactivation( false, true );
				$this->set_error_notice( [ 404 => '' ] );
			}

			return false;
		} elseif ( isset( $results['code'] ) ) {
			// Probably duplicated local activation request. Will be handled later in response.
			return false;
		}

		$this->set_error_notice( [ 405 => '' ] );
		return null;
	}

	/**
	 * Handles disconnection and returns status.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Can now also disconnect decoupled websites.
	 * @since 2.1.0 Added the $args parameter.
	 * @since 2.6.2 Rewritten to ease disconnecting and remove dependency on our API services on error.
	 *
	 * @param array $args : {
	 *    'request'          => string The request type.
	 *    'api_key'          => string The license key used.
	 *    'activation_email' => string The activation email used.
	 * }
	 * @param array $results The disconnection response.
	 * @return bool True on success, false on failure.
	 */
	protected function handle_premium_disconnection( $args, $results ) {

		$message = '';

		// Remove deactivation was successful. Don't notify user otherwise; they couldn't care less.
		if ( ! empty( $results['deactivated'] ) )
			$message .= \esc_html__( 'API Key disconnected.', 'the-seo-framework-extension-manager' );

		if ( $this->get_option( '_activated' ) ) {
			if ( $this->do_deactivation( false, true ) ) {
				$this->set_error_notice( [ 501 => $message ] );
				return true;
			}

			// TODO $this->kill_options() on failure?
			// Or set a flag to allow the user to directly kill_options()?
			// It can only fail when the instance is borked. TODO test?

			$this->set_error_notice( [ 502 => $message ] );
			return false;
		}

		if ( empty( $results['timestamp'] ) ) {
			// API server down and local disconnect failed.
			$this->set_error_notice( [ 503 => '' ] );
		}

		return null;
	}

	/**
	 * Returns the default activation options.
	 *
	 * @since 2.6.1
	 * @since 2.6.2 Now listens to constant \TSF_EXTENSION_MANAGER_INSTANCE_VERSION.
	 *
	 * @return array The default activation options.
	 */
	protected function get_default_activation_options() {

		switch (
			/**
			 * This forces the plugin option verification instance version, which prevents users
			 * from sharing API information between sites.
			 *
			 * @since 2.6.2
			 * @param string The instance version to use.
			 *               '1.0' relies on wp-config.php's AUTH_KEY and AUTH_SALT values.
			 *               '2.0' relies on Extension Manager's folder location.
			 *               '3.0' relies on the site URL.
			 */
			\defined( 'TSF_EXTENSION_MANAGER_INSTANCE_VERSION' ) ? \TSF_EXTENSION_MANAGER_INSTANCE_VERSION : false
		) {
			case '1.0': // wp-config.php AUTH_KEY and AUTH_SALT values.
			case '2.0': // Extension Manager's folder location.
			case '3.0': // Site URL.
				$instance_version = \TSF_EXTENSION_MANAGER_INSTANCE_VERSION;
				break;

			default:
				$instance_version = '3.0';
		}

		return [
			'api_key'           => '',
			'activation_email'  => '',
			'_activation_level' => 'Free',
			'_activated'        => 'Activated',
			'_instance'         => $this->get_options_instance_key(),
			'_instance_version' => $instance_version, // If not set in database, assume 1.0
		];
	}

	/**
	 * Handles free activation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success. False on failure.
	 */
	protected function do_free_activation() {

		if ( $this->update_option_multi( $this->get_default_activation_options() ) ) {
			$this->set_error_notice( [ 601 => '' ] );
			return true;
		}

		$this->set_error_notice( [ 602 => '' ] );
		$this->do_deactivation( false, false );
		return false;
	}

	/**
	 * Handles free deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success. False on failure.
	 */
	protected function do_free_deactivation() {

		$success = $this->do_deactivation( false, false );

		if ( $success ) {
			$this->set_error_notice( [ 801 => '' ] );
			return true;
		} else {
			$this->set_error_notice( [ 802 => '' ] );
			return false;
		}
	}

	/**
	 * Handles premium activation.
	 * Unlike the free activation, we do not update in bulk here, for it may be an upgrade.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args : {
	 *    'api_key'           => string The license key used.
	 *    'activation_email'  => string The activation email used.
	 *    '_activation_level' => string The activation email used.
	 * }
	 * @return bool True on success. False on failure.
	 */
	protected function do_premium_activation( $args ) {

		$options = array_merge(
			// Get existing options before filling with defaults;
			// this allows for both an upgrade of free, and a new instant premium setup.
			$this->get_all_options() ?: $this->get_default_activation_options(),
			$args
		);

		if ( $this->update_option_multi( $options, true ) ) {
			// Wait 0.0625 seconds as a second request is now quickly following up (1~20x load time server).
			usleep( 62500 );
			return $this->update_remote_subscription_status( $args );
		}

		return false;
	}

	/**
	 * Handles account deactivation.
	 * Sets all options to empty i.e. 'Deactivated'.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Added margin of error handling.
	 * @since 2.6.1 Now ignores margin of error on instance failure to prevent a soft lockout.
	 *
	 * @param bool $grace     Whether to allow a grace period of 7 days.
	 * @param bool $downgrade Whether to downgrade to Free, or disconnect completely.
	 * @return bool True on success. False on failure.
	 */
	protected function do_deactivation( $grace = false, $downgrade = false ) {

		if ( $grace && $this->get_option( '_instance' ) ) {

			$this->update_option( '_activation_level', 'Free' );

			$expire = $this->get_option( 'license_grace' );

			if ( ! $expire ) {
				$this->update_option( 'license_grace', time() + \DAY_IN_SECONDS * 7 );
				return false;
			} elseif ( $expire > time() ) {
				return false;
			}
		}

		// Grace expired or not permitted.
		$this->delete_option( 'license_grace' );

		if ( $downgrade ) {
			$options = $this->get_all_options();
			// Activation failed, and no instance is available.
			if ( ! $options ) return true;

			// Downgrade.
			return $this->update_option_multi(
				array_merge(
					$options,
					[
						'api_key'                     => '',
						'activation_email'            => '',
						'_activation_level'           => 'Free',
						'_remote_subscription_status' => [],
					]
				),
				true
			);
		}

		return $this->kill_options();
	}

	/**
	 * Revalidate account status.
	 *
	 * @since 1.3.0
	 * @see $this->validate_remote_subscription_license();
	 *
	 * @param bool $silent Whether to emit errors.
	 * @return int $this->validate_remote_subscription_license();
	 */
	protected function revalidate_subscription( $silent = false ) {

		$response = $this->update_remote_subscription_status();

		$status = 0;
		/**
		 * 0 : Not subscribed / API failure.
		 * 1 : Local connected user.
		 * 2 : Local connected user. Remote connected User.
		 * 3 : Local connected user. Remote connected User. Instance verified.
		 * 4 : Local connected user. Remote connected User. Instance verified. Domain verified.
		 * 5 : Local connected user. Remote connected User. Instance verified. Domain verified. Premium verified.
		 * 6 : Local connected user. Remote connected User. Instance verified. Domain verified. Enterprise verified.
		 */
		while ( true ) {
			if ( ! isset( $response['status_check'] ) ) break;
			++$status; // 1
			if ( 'active' !== $response['status_check'] ) break;
			++$status; // 2
			if ( $this->get_options_instance_key() !== ( $response['_instance'] ?? null ) ) break;
			++$status; // 3
			if ( $this->get_current_site_domain() !== ( $response['activation_domain'] ?? -1 ) ) break;
			++$status; // 4
			if ( ! \in_array( $response['_activation_level'] ?? '', [ 'Premium', 'Enterprise' ], true ) ) break;
			++$status; // 5
			if ( 'Enterprise' !== $response['_activation_level'] ) break;
			++$status; // 6

			break;
		}

		switch ( $status ) {
			case 0:
				// Already free or couldn't reach API.
				break;

			case 1:
				// Used to be premium. Bummer.
				$silent or $this->set_error_notice( [ 901 => '' ] );
				$this->do_deactivation( true, true );
				break;

			case 2:
				$silent or $this->set_error_notice( [ 902 => '' ] );
				// Remote instance failed. Allow grace, but no API.
				// Administrator has already been notified to fix this ASAP.
				$this->do_deactivation( true, true );
				break;

			case 3:
				// Domain mismatch. Everything else is OK. Don't downgrade to Free.
				// User gets notified via the CP pane on certain actions and can not perform API actions.
				$this->update_option( '_requires_domain_transfer', true );
				break;

			case 4:
				// Everything's superb. Remote upgrade/downgrade.
				if ( $this->get_option( '_activation_level' ) !== 'Essentials' ) {
					if ( $this->update_option( '_activation_level', 'Essentials' ) )
						$silent or $this->set_error_notice( [ 904 => '' ] );

					$this->delete_option( 'license_grace' );
				}
				break;

			case 5:
				// Everything's Premium. Remote upgrade/downgrade.
				if ( $this->get_option( '_activation_level' ) !== 'Premium' ) {
					if ( $this->update_option( '_activation_level', 'Premium' ) )
						$silent or $this->set_error_notice( [ 905 => '' ] );

					$this->delete_option( 'license_grace' );
				}
				break;

			case 6:
				// Everything's Enterprise. Remote upgrade/downgrade.
				if ( $this->get_option( '_activation_level' ) !== 'Enterprise' ) {
					if ( $this->update_option( '_activation_level', 'Enterprise' ) )
						$silent or $this->set_error_notice( [ 906 => '' ] );

					$this->delete_option( 'license_grace' );
				}
		}

		return $status;
	}

	/**
	 * Fetches remote subscription status. Use this scarcely.
	 *
	 * @since 1.0.0
	 * @since 2.1.0 The first parameter now accepts arguments.
	 * @since 2.6.1 Removed memoization.
	 *
	 * @param array|null $args Should only be set during activation : {
	 *    'api_key'          => string The license key used.
	 *    'activation_email' => string The activation email used.
	 * }
	 * @return bool|array False on failure. Array subscription status on success.
	 */
	protected function update_remote_subscription_status( $args = null ) {

		// When in license grace period, try to reconnect if not deemed connected.
		if ( null === $args && ! $this->is_connected_user() && ! $this->get_option( 'license_grace' ) )
			return false;

		$status = $this->get_option( '_remote_subscription_status' ) ?: [
			'timestamp' => 0,
			'status'    => [],
		];

		if ( isset( $status['status']['status_check'] ) && 'active' !== $status['status']['status_check'] ) {
			// Updates at most every 1 minute.
			$divider = \MINUTE_IN_SECONDS * 2;
		} else {
			// Updates at most every 5 minutes.
			$divider = \MINUTE_IN_SECONDS * 5;
		}

		// In-house transient cache.
		$timestamp = (int) ceil( time() / $divider );

		// If timeout hasn't passed, return registered data.
		if ( $timestamp === $status['timestamp'] )
			return $status['status'];

		if ( null !== $args ) {
			// $args should only be supplied when doing activation.
			// So, wait 0.0625 seconds as a second request is following up (1~20x load time server).
			usleep( 62500 );
		} else {
			$args = [
				'api_key'          => $this->get_option( 'api_key' ),
				'activation_email' => $this->get_option( 'activation_email' ),
			];
		}

		$response = $this->handle_activation_request( 'status', $args );
		$success  = false;

		if ( $response )
			$success = $this->update_option(
				'_remote_subscription_status',
				[
					'timestamp' => $timestamp,
					'status'    => $response,
					'divider'   => $divider,
				],
				false
			);

		return $success ? $response : false;
	}
}
