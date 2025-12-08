<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	do_dismissible_notice,
};

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// @TODO create error legend/index for codes.

/**
 * Holds Error handling functionality.
 *
 * @since 1.0.0
 * @access private
 */
trait Error {

	/**
	 * @since 1.0.0
	 * @var string The Error loader hook.
	 */
	private $error_hook = '';

	/**
	 * @since 1.0.0
	 * @var string The POST request status code option name.
	 */
	protected $error_notice_option;

	/**
	 * Initializes the UI traits.
	 *
	 * @since 1.0.0
	 */
	final protected function init_errors() {

		$this->error_notice_option or \tsf()->_doing_it_wrong( __METHOD__, 'You need to specify property <code>error_notice_option</code>' );

		// Can this be applied in-post too, when $this->error_notice_option is known? Otherwise, supply parameter?
		\add_action( 'tsfem_notices', [ $this, '_do_error_notices' ] );
	}

	/**
	 * Outputs notices. If any, and only on the Extension manager pages.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now outputs multiple notices.
	 * @uses $this->error_notice_option
	 * @access private
	 */
	final public function _do_error_notices() {

		$options = \get_option( $this->error_notice_option, false );

		if ( ! $options ) return;

		$notices = $this->get_error_notices( $options );

		if ( ! $notices ) {
			$this->unset_error_notice_option();
			return;
		}

		foreach ( $notices as $notice )
			do_dismissible_notice(
				$notice['message'],
				[
					'type'   => $notice['type'],
					'escape' => false,
					'inline' => true,
				]
			);

		$this->unset_error_notice_option();
	}

	/**
	 * Sets notices option, only does so when in the admin area.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 : 1. Now stores multiple notices.
	 *                2. Added a new parameter to clear previous notices.
	 * @since 1.5.1 Added an exact-match check to prevent duplicated entries.
	 *
	 * @param array $notice    The notice. : {
	 *    0 => int    key,
	 *    1 => string additional message
	 * }
	 * @param bool  $clear_old When true, it removes all previous notices.
	 * @return void
	 */
	final protected function set_error_notice( $notice = [], $clear_old = false ) {

		if ( ! \is_admin() || ! $this->error_notice_option )
			return;

		$notices = ( $clear_old ? null : \get_option( $this->error_notice_option ) ) ?: [];

		if ( ! $notices ) {
			$notices = [ $notice ];
		} else {
			// This checks if the notice is already stored.
			//# This prevents adding timestamps preemptively in the future.
			// We could form a timestamp collection per notice, separately.
			//# But, that would cause performance issues.
			if ( \in_array( $notice, $notices, true ) ) {
				// We already have the notice stored in cache.
				return;
			} else {
				array_push( $notices, $notice );
			}
		}

		\update_option( $this->error_notice_option, $notices, 'yes' );
	}

	/**
	 * Removes notices option.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 1. No longer deletes option, but instead overwrites it.
	 *              2. Now removes the option from autoload.
	 * @since 1.5.0 Renamed from `unset_error_notice()`
	 */
	final protected function unset_error_notice_option() {
		$this->error_notice_option and \update_option( $this->error_notice_option, null, 'no' );
	}

	/**
	 * Fetches notices by option and returns type.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 @see $this->get_error_notices(), the processing callback.
	 *
	 * @param int|array $option The error notice key.
	 * @return array|string The escaped notice. Empty string when no array key is set.
	 */
	final protected function get_error_notice( $option ) {

		if ( \is_array( $option ) ) {
			$key = key( $option );
		} elseif ( \is_scalar( $option ) ) {
			$key = $option;
		}

		if ( empty( $key ) )
			return '';

		$notice = $this->get_error_notice_by_key( $key, true );

		$args = [
			'type'            => $notice['type'],
			'message'         => $notice['message'],
			'additional_info' => ( $option[ $key ] ?? null ) ?: '',
		];

		return $this->format_error_notice( $key, $args );
	}

	/**
	 * Parses error notices from options.
	 *
	 * @since 1.5.0
	 *
	 * @param array $options The error notice keys.
	 * @return array $notices
	 */
	final protected function get_error_notices( $options = [] ) {

		$notices = [];

		foreach ( $options as $option )
			$notices[] = $this->get_error_notice( $option );

		return $notices;
	}

	/**
	 * Formats notice through input.
	 *
	 * @since 1.5.0
	 *
	 * @param int|string $code The error code or formatting placeholder.
	 * @param array      $args : {
	 *    'type'            : string The type,
	 *    'message'         : string The message,
	 *    'additional_info' : string Additional info, like HTML,
	 * }
	 * @return array|string The escaped notice. Empty string when no array key is set.
	 */
	final public function format_error_notice( $code, $args ) {

		$defaults = [
			'type'            => 'updated',
			'message'         => '',
			'additional_info' => '',
		];

		$args = array_merge( $defaults, $args );

		switch ( $args['type'] ) {
			case 'error':
				$status_i18n = \esc_html__( 'Error code:', 'the-seo-framework-extension-manager' );
				break;

			case 'warning':
				$status_i18n = \esc_html__( 'Notice code:', 'the-seo-framework-extension-manager' );
				break;

			default:
			case 'info':
			case 'updated':
				$status_i18n = \esc_html__( 'Status code:', 'the-seo-framework-extension-manager' );
		}

		/* translators: 1: 'Error code:', 2: The error code. */
		$status = \sprintf( \esc_html__( '%1$s %2$s', 'the-seo-framework-extension-manager' ), $status_i18n, $code );

		/* translators: %s = Error code */
		$before = \sprintf( \__( '<strong>%s</strong> &mdash;', 'the-seo-framework-extension-manager' ), $status );

		/* translators: 1: Error code, 2: Error message, 3: Additional info */
		$output = vsprintf( \esc_html__( '%1$s %2$s %3$s', 'the-seo-framework-extension-manager' ),
			[
				$before,
				$args['message'],
				$args['additional_info'],
			]
		);

		return [
			'message' => $output,
			'before'  => $before, // To be used when adding a personal message.
			'type'    => $args['type'],
		];
	}

	/**
	 * Fetches notices by option and returns type.
	 *
	 * Not final. Can be overwritten.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $key The error key.
	 * @param bool $get_type Whether to fetch the error type as well.
	 * @return array|string The escaped notice. When $get_type is true, an array is returned.
	 */
	protected function get_error_notice_by_key( $key, $get_type = true ) {

		switch ( $key ) {
			case -1:
				// Placeholder error. See TSF_Extension_Manager\_wp_ajax_get_dismissible_notice()
				$message = 'Undefined error. Check other messages.';
				$type    = 'error';
				break;

			case 101:
				$message = \esc_html__( 'No valid license key was supplied.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 102:
				$message = \esc_html__( 'No valid license email was supplied.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 103:
			case 104:
			case 701:
			case 708:
			case 1010702:
			case 1060106:
				$message = \esc_html__( 'Invalid API request type.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 201:
			case 1010201:
			case 1100101:
			case 1100201:
			case 1100301:
				$message = \esc_html__( 'An incomplete API request was supplied.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 301:
				$message = \esc_html__( 'No response received from the API server. Please try again later. If this error keeps coming back, contact your hosting provider.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 202:
			case 302:
			case 403:
			case 404:
			case 405:
			case 503:
			case 10004:
			case 1010101:
				$message = \esc_html__( 'An error occurred while contacting the API server. Please try again later.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 401:
				$message = \esc_html__( 'An error occured while validating the settings. Please try again.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 303: // license not found
			case 307: // email/license mismatch
				$message = \sprintf(
					/* translators: %s = My Account */
					\esc_html__( 'Invalid API license key. Login to the %s page to find a valid API License Key.', 'the-seo-framework-extension-manager' ),
					$this->get_my_account_link() // can never be WCM; user isn't connected yet.
				);
				$type = 'error';
				break;

			case 304:
			case 17001:
			case 17002:
			case 1010204:
			case 1100104:
			case 1100204:
			case 1100305:
				$message = \esc_html__( 'Remote software API error.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 17008:
			case 1010301:
			case 1010401:
			case 1010501:
			case 1010601:
			case 1010801:
			case 1100103:
			case 1100107:
			case 1100207:
			case 1100304:
			case 1100308:
				$message = \esc_html__( 'Remote Software API error. Please try again. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 17013:
			case 1010306:
			case 1010404:
			case 1010508:
			case 1010608:
			case 1010806:
			case 1100108:
			case 1100208:
			case 1100303:
			case 1010205:
				$message = \esc_html__( 'Exceeded maximum number of monthly request. Upgrade your license or check back in next month.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1100109:
			case 1100209:
			case 1100309:
				$message = \esc_html__( 'Site language is not supported for lexical lookup.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 305:
				$message = \sprintf(
					/* translators: %s = My Account */
					\esc_html__( 'Exceeded maximum number of activations. Login to the %s page to manage your sites.', 'the-seo-framework-extension-manager' ),
					$this->get_my_account_link() // can never be WCM; user isn't connected yet.
				);
				$type = 'error';
				break;

			case 306:
				$message = \esc_html__( 'Invalid instance ID. Please try again. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 308:
				$message = \esc_html__( 'Your subscription is not active or has expired.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 402:
				$message = \esc_html__( 'Your account has been successfully authorized to be used on this website.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 501:
				$message = \esc_html__( 'Your account has been successfully deauthorized from this website.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 502:
				$message = \esc_html__( 'Failed to disconnect locally, please try again.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 504:
				$message = \esc_html__( 'Failed to deauthorize your account from the API server, but still disconnected locally.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 601:
				$message = \esc_html__( 'Enjoy your free extensions!', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 801:
				$message = \esc_html__( 'Successfully deactivated.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 901:
				$message = \esc_html__( 'Your subscription has expired or has been deactivated remotely.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 902:
				if ( 'wcm' === $this->get_api_endpoint_type() ) {
					// Headless. User cannot inspect key. Edge case -- user gets disconnected right before this error.
					$message = \esc_html__( "Your subscription instance couldn't be verified.", 'the-seo-framework-extension-manager' );
				} else {
					$message = \sprintf(
						/* translators: %s = My Account */
						\esc_html__( "Your subscription instance couldn't be verified. Login to the %s page and verify if this site is still connected.", 'the-seo-framework-extension-manager' ),
						$this->get_my_account_link() // this can be WCM, which is troubling.
					);
				}
				$type = 'warning';
				break;

			case 904:
				$message = \esc_html__( "Your account level has been set to Essentials. Reload the page if it didn't take effect.", 'the-seo-framework-extension-manager' );
				$type    = 'info';
				break;

			case 905:
				$message = \esc_html__( "Your account level has been set to Premium. Reload the page if it didn't take effect.", 'the-seo-framework-extension-manager' );
				$type    = 'info';
				break;

			case 906:
				$message = \esc_html__( "Your account level has been set to Enterprise. Reload the page if it didn't take effect.", 'the-seo-framework-extension-manager' );
				$type    = 'info';
				break;

			case 2001:
			case 6001:
			case 6002:
			case 7001:
			case 7002:
			case 7101:
			case 7102:
				if ( \TSF_EXTENSION_MANAGER_API_INFORMATION ) {
					$message = \esc_html__( 'An error occured while verifying the options. The local instance is out of sync and enabled extensions are now inactive.', 'the-seo-framework-extension-manager' );
				} else {
					$message = \esc_html__( 'An error occured while verifying the options. The local instance is out of sync and enabled extensions are now inactive. If this error keeps coming back, please disconnect your account at "Account and Actions" and try again.', 'the-seo-framework-extension-manager' );
				}
				$type = 'error';
				break;

			// IT'S OVER NINE THOUSAAAAAAAAAAAAAAAAAAAAAAND!!one!1!!
			case 9001:
			case 9002:
			case 9003:
			case 9004:
			case 1019001:
			case 1019002:
			case 1069001:
			case 1079001:
			case 1109001:
				$message = \esc_html__( 'User verification failed. Please try again.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 2002:
			case 10001:
			case 10002:
				$message = \esc_html__( 'Extension list has been tampered with. Please reinstall this plugin and try again.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 10005:
				$message = \esc_html__( 'Extension is not compatible with your server configuration.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 10008:
			case 10010:
				$message = \esc_html__( 'Extension has been successfully activated.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 10012:
				$message = \esc_html__( 'Extension was already activated in another browser instance.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 10009:
			case 10102:
				$message = \esc_html__( "Can't touch this.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 10015:
			case 10016:
				$message = \esc_html__( "This domain isn't connected to the API. Transfer the license and try again.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 17014:
			case 1010307:
			case 1010405:
			case 1010509:
			case 1010609:
			case 1010807:
			case 1100110:
			case 1100210:
			case 1100310:
			case 1010206:
				$message = \esc_html__( "This domain isn't connected to the API. Reconnect via Extension Manager.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 11001:
				$message = \esc_html__( 'Extension has been successfully deactivated.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1010304:
				$message = \esc_html__( 'Your website has been successfully connected to the Monitor API server.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1010403:
				$message = \esc_html__( 'Your site has been successfully disconnected from the Monitor API server.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1010502:
			case 1010602:
			case 1010802:
				$message = \esc_html__( "The Monitor API server does not recognize your website's instance. Request reactivation.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1010503:
			case 1010603:
			case 1010803:
				$message = \esc_html__( 'Your website has been marked as inactive by the Monitor API server.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1010504:
				$message = \esc_html__( 'Crawl request is still in queue. Please try again later.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1010507:
				$message = \esc_html__( 'Crawl request has just been submitted.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1060201:
				$message = \esc_html__( "Importer does't exist.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1010607:
				$message = \esc_html__( 'Data has just been updated.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1010305:
			case 1010506:
				$message = \esc_html__( 'Crawl has been requested successfully. It can take up to two minutes to be processed.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1010606:
				$message = \esc_html__( 'The latest Monitor data has been recieved.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1010804:
				$message = \esc_html__( 'Updated site settings, but your site is now out of sync. You should fetch data.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1010805:
				$message = \esc_html__( 'Updated site settings.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 17100:
			case 18101:
			case 1060200:
			case 1070100:
			case 1090100:
				$message = \esc_html__( 'Invalid data was sent to the server.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 18102:
				$message = \esc_html__( "A database error occurred. Some changes aren't saved.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 18103:
			case 1070101:
			case 1090101:
				$message = \esc_html__( "A database error occurred. Changes aren't saved.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 18104:
				$message = \esc_html__( 'All changes are saved.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 18105:
				$message = \esc_html__( 'Changes are saved for one extension.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1060202:
				$message = \esc_html__( 'Transporting in session, please wait...', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1070102:
			case 1090102:
				$message = \esc_html__( 'Changes are saved.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1060203:
				$message = \esc_html__( 'Timeout', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1060204:
				$message = \esc_html__( 'Crash', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1060205:
				$message = \esc_html__( 'Done!', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1060206:
				$message = \esc_html__( 'Memory exhaustion', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1011700:
			case 1071100:
			case 1071101:
				$message = \esc_html__( 'Unable to verify if changes are saved. Refresh this page to manually verify.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1011800:
				$message = \esc_html__( 'Unable to propagate request. Are you running the latest version?', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1072100:
				$message = \esc_html__( "Couldn't fetch data.", 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 1070201:
				$message = \esc_html__( 'Unable to create markup. Inspect your fields for errors or contact support.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 17000:
			case 17003:
			case 17200:
				$message = \esc_html__( 'Unable to fetch geocoding data.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 17004:
			case 17007:
				$message = \esc_html__( 'No results found. Inspect your current input.', 'the-seo-framework-extension-manager' );
				switch ( $key ) {
					case 17004:
						$type = 'warning';
						break 2;

					default:
					case 17007:
						$type = 'error';
						break 2;
				}
				break;

			case 17009:
				$message = \esc_html__( 'Please wait a few seconds before making another request.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 17010:
				$message = \esc_html__( 'Too many requests in the last period. Limit will be lifted soon.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
				break;

			case 17012:
				$message = \esc_html__( 'Geocoding data received.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1100102:
			case 1100105:
				$message = \esc_html__( 'No definitions found. Check your spelling.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1100111:
				$message = \esc_html__( 'No definitions found. Check your spelling and consider using simple or compound words only.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1100302:
			case 1100306:
				$message = \esc_html__( 'No inflections found.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1100202:
			case 1100205:
				$message = \esc_html__( 'No synonyms found.', 'the-seo-framework-extension-manager' );
				$type    = 'warning';
				break;

			case 1100106:
				$message = \esc_html__( 'Lexical forms received.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1100206:
				$message = \esc_html__( 'Synonyms received.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			case 1100307:
				$message = \esc_html__( 'Inflections received.', 'the-seo-framework-extension-manager' );
				$type    = 'updated';
				break;

			// These errors shouldn't occur. Most likely WordPress Database/Option issues,
			// or some doofus spread erroneous files to the public.
			default:
			case 602:
			case 703:
			case 802:
			case 10003:
			case 10006:
			case 10007:
			case 10011:
			case 10013:
			case 10014:
			case 10101:
			case 11002:
			case 11003:
			case 11004:
			case 17005:
			case 17006:
			case 17011:
			case 1010302:
			case 1010303:
			case 1010402:
			case 1010505:
			case 1010604:
			case 1010605:
				$message = \esc_html__( 'An unknown error occurred. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type    = 'error';
		}

		return $get_type ? compact( 'message', 'type' ) : $message;
	}

	/**
	 * Returns Ajax notice from $code.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now appends notice type.
	 *
	 * @param mixed $success The success status, either boolean, int, or other.
	 * @param int   $code    The error code.
	 * @return array {
	 *    'success' => mixed $success,
	 *    'notice'  => string $notice,
	 *    'code'    => int $code,
	 * }
	 */
	final protected function get_ajax_notice( $success, $code ) {

		$notice = $this->get_error_notice_by_key( $code );

		return \TSF_Extension_Manager\get_ajax_notice(
			$success,
			$notice['message'],
			$code,
			$notice['type']
		);
	}
}
