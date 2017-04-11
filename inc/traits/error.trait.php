<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds Error handling functionality.
 *
 * @since 1.0.0
 * @access private
 */
trait Error {

	/**
	 * The Error hook where all scripts should be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var string The Error loader hook.
	 */
	private $error_hook = '';

	/**
	 * The POST request status code option name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The POST request status code option name.
	 */
	protected $error_notice_option;

	/**
	 * Initializes the UI traits.
	 *
	 * @since 1.0.0
	 */
	final protected function init_errors() {

		$this->error_notice_option or \the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to specify property <code>error_notice_option</code>' );

		\add_action( 'admin_notices', array( $this, '_do_error_notices' ) );
	}

	/**
	 * Outputs notices. If any, and only on the Extension manager page.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _do_error_notices() {

		if ( $option = \get_option( $this->error_notice_option, false ) ) {

			$notice = $this->get_error_notice( $option );

			if ( empty( $notice ) ) {
				$this->unset_error_notice();
				return;
			}

			//* Already escaped.
			\the_seo_framework()->do_dismissible_notice( $notice['message'], $notice['type'], true, false );
			$this->unset_error_notice();
		}
	}

	/**
	 * Sets notices option, only does so when in the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The notice.
	 */
	final protected function set_error_notice( $notice = array() ) {
		\is_admin() and $this->error_notice_option and \update_option( $this->error_notice_option, $notice );
	}

	/**
	 * Removes notices option.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The notice.
	 */
	final protected function unset_error_notice() {
		$this->error_notice_option and \delete_option( $this->error_notice_option );
	}

	/**
	 * Fetches notices by option and returns type.
	 *
	 * @since 1.0.0
	 *
	 * @param int|array $option The error notice key.
	 * @return array|string The escaped notice. Empty string when no array key is set.
	 */
	final protected function get_error_notice( $option ) {

		if ( is_array( $option ) )
			$key = key( $option );

		if ( empty( $key ) )
			return '';

		$notice = $this->get_error_notice_by_key( $key, true );

		$message = $notice['message'];
		$type = $notice['type'];

		switch ( $type ) :
			case 'error' :
			case 'warning' :
				$status_i18n = \esc_html__( 'Error code:', 'the-seo-framework-extension-manager' );
				break;

			case 'updated' :
			default :
				$status_i18n = \esc_html__( 'Status code:', 'the-seo-framework-extension-manager' );
				break;
		endswitch;

		/* translators: 1: 'Error code:', 2: The error code */
		$status = sprintf( \esc_html__( '%1$s %2$s', 'the-seo-framework-extension-manager' ), $status_i18n, $key );
		$additional_info = $option[ $key ];

		/* translators: 1: Error code, 2: Error message, 3: Additional info */
		$output = sprintf( \esc_html__( '%1$s &mdash; %2$s %3$s', 'the-seo-framework-extension-manager' ), $status, $message, $additional_info );

		return array(
			'message' => $output,
			'type' => $type,
		);
	}

	/**
	 * Fetches notices by option and returns type.
	 *
	 * @since 1.0.0
	 *
	 * @param int $key The error key.
	 * @param bool $get_type Whether to fetch the error type as well.
	 * @return array|string The escaped notice. When $get_type is true, an array is returned.
	 */
	protected function get_error_notice_by_key( $key, $get_type = true ) {

		switch ( $key ) :
			case 101 :
				$message = \esc_html__( 'No valid license key was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 102 :
				$message = \esc_html__( 'No valid license email was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 103 :
			case 104 :
			case 701 :
			case 708 :
				$message = \esc_html__( 'Invalid API request type.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 201 :
			case 1010201 :
				$message = \esc_html__( 'An empty API request was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 202 :
			case 301 :
			case 302 :
			case 403 :
			case 404 :
			case 405 :
			case 503 :
			case 10004 :
			case 1010101 :
				$message = \esc_html__( 'An error occurred while contacting the API server. Please try again later.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 401 :
				/* translators: %s = My Account */
				$message = sprintf( \esc_html__( 'An error occured while validating settings. Login to the %s page to manage your keys and try again.', 'the-seo-framework-extension-manager' ), $this->get_my_account_link() );
				$type = 'error';
				break;

			case 303 :
			case 307 :
				/* translators: %s = My Account */
				$message = sprintf( \esc_html__( 'Invalid API License Key. Login to the %s page to find a valid API License Key.', 'the-seo-framework-extension-manager' ), $this->get_my_account_link() );
				$type = 'error';
				break;

			case 304 :
			case 1010203 :
			case 1010204 :
				$message = \esc_html__( 'Remote Software API error.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 1010301 :
			case 1010401 :
			case 1010501 :
			case 1010601 :
				$message = \esc_html__( 'Remote Software API error. Please try again. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 305 :
				/* translators: %s = My Account */
				$message = sprintf( \esc_html__( 'Exceeded maximum number of activations. Login to the %s page to manage your sites.', 'the-seo-framework-extension-manager' ), $this->get_my_account_link() );
				$type = 'error';
				break;

			case 306 :
				$message = \esc_html__( 'Invalid Instance ID. Please try again. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 308 :
			case 1010202 :
				$message = \esc_html__( 'Your subscription is not active or has expired.', 'the-seo-framework-extension-manager' );
				$type = 'warning';
				break;

			case 402 :
				$message = \esc_html__( 'Your account has been successfully authorized to be used on this website.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 501 :
			case 502 :
				$message = \esc_html__( 'Your account has been successfully deauthorized from this website.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 601 :
				$message = \esc_html__( 'Enjoy your free extensions!', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 702 :
				$message = \esc_html__( 'The feed has been enabled.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 801 :
				$message = \esc_html__( 'Successfully deactivated.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 2001 :
			case 7001 :
			case 7002 :
				$message = \esc_html__( 'An error occured while verifying the options. If this error keeps coming back, please deactivate your account and try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			//* IT'S OVER NINE THOUSAAAAAAAAAAAAAAAAAAAAAAND!!one!1!!
			case 9001 :
			case 1019001 :
			case 1069001 :
				$message = \esc_html__( 'Nonce verification failed. Please try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 2002 :
			case 10001 :
			case 10002 :
				$message = \esc_html__( 'Extension list has been tampered with. Please reinstall this plugin and try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 10005 :
				$message = \esc_html__( 'Extension is not compatible with your server configuration.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 10008 :
			case 10010 :
				$message = \esc_html__( 'Extension has been successfully activated.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 10009 :
				$message = \esc_html__( 'Extension is not valid.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 11001 :
				$message = \esc_html__( 'Extension has been successfully deactivated.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 1010304 :
				$message = \esc_html__( 'Your website has been successfully connected to the Monitor API server.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 1010403 :
				$message = \esc_html__( 'Your site has been succesfully disconnected from the Monitor API server.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 1010502 :
			case 1010602 :
				$message = \esc_html__( 'The Monitor API server does not recognize your instance. Request a fix.', 'the-seo-framework-extension-manager' );
				$type = 'warning';
				break;

			case 1010503 :
			case 1010603 :
				$message = \esc_html__( 'Your website has been marked as inactive by the Monitor API server.', 'the-seo-framework-extension-manager' );
				$type = 'warning';
				break;

			case 1010504 :
				$message = \esc_html__( 'Crawl request is still in queue. Please try again later.', 'the-seo-framework-extension-manager' );
				$type = 'warning';
				break;

			case 1010506 :
				$message = \esc_html__( 'Crawl has been requested succesfully. It can take up to three minutes to be processed.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 1010606 :
				$message = \esc_html__( 'The latest Monitor data has been recieved.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 1060301 :
				$message = \esc_html__( "The SEO Settings couldn't be converted to file.", 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 1060302 :
				$message = \esc_html__( 'An unknown source outputted data before sending the file. Therefore, Transporter is unable to complete your request.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			//* These errors shouldn't occur. Most likely WordPress Database/Option issues.
			case 602 :
			case 703 :
			case 802 :
			case 10003 :
			case 10006 :
			case 10007 :
			case 10011 :
			case 11002 :
			case 1010302 :
			case 1010303 :
			case 1010402 :
			case 1010505 :
			case 1010604 :
			case 1010605 :
			case 1060101 :
			default :
				$message = \esc_html__( 'An unknown error occurred. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;
		endswitch;

		return $get_type ? array( 'message' => $message, 'type' => $type ) : $message;
	}

	/**
	 * Returns Ajax notice from $code.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $success The success status, either boolean, int, or other.
	 * @param int $code The error code.
	 * @return array {
	 *		'success' => mixed $success,
	 *		'notice'  => string $notice,
	 *		'code'    => int $code,
	 * }
	 */
	protected function get_ajax_notice( $success, $code ) {
		return array(
			'success' => $success,
			'notice' => $this->get_error_notice_by_key( $code, false ),
			'code' => intval( $code ),
		);
	}
}
