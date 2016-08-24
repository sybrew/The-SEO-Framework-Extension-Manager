<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Core
 *
 * Holds plugin core functions.
 *
 * @since 1.0.0
 */
class Core {
	use Enclose, Construct_Final;

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
	protected $request_name = array();
	protected $nonce_action = array();

	/**
	 * The POST request status code option name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The POST request status code option name.
	 */
	protected $error_notice_option;

	/**
	 * Constructor, initializes actions and sets up variables.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->nonce_name = 'tsf_extension_manager_nonce_name';
		$this->request_name = array(
			//* Reference convenience.
			'default'           => 'default',

			//* Account activation and more.
			'activate-key'      => 'activate-key',
			'activate-external' => 'activate-external',
			'activate-free'     => 'activate-free',
			'deactivate'        => 'deactivate',
			'enable-feed'       => 'enable-feed',

			//* Extensions.
			'activate-ext'      => 'activate-ext',
			'deactivate-ext'    => 'deactivate-ext',
		);
		$this->nonce_action = array(
			'default'           => 'tsfem_nonce_action',

			//* Account activation and more.
			'activate-free'     => 'tsfem_nonce_action_free_account',
			'activate-key'      => 'tsfem_nonce_action_key_account',
			'activate-external' => 'tsfem_nonce_action_external_account',
			'deactivate'        => 'tsfem_nonce_action_deactivate_account',
			'enable-feed'       => 'tsfem_nonce_action_feed',

			//* Extensions.
			'activate-ext'      => 'tsfem_nonce_action_activate_ext',
			'deactivate-ext'    => 'tsfem_nonce_action_deactivate_ext',
		);

		$this->error_notice_option = 'tsfem_error_notice_option';

		add_action( 'admin_init', array( $this, 'handle_update_post' ) );
		add_action( 'admin_notices', array( $this, 'do_error_notices' ) );
	}

	/**
	 * Handles plugin POST requests.
	 *
	 * @since 1.0.0
	 *
	 * @return bool False if nonce failed.
	 */
	public function handle_update_post() {

		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ]['action'] ) )
			return;

		$options = $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ];

		if ( false === $this->handle_update_nonce( $options['action'], false ) )
			return;

		switch ( $options['action'] ) :
			case $this->request_name['activate-key'] :
				$args = array(
					'licence_key' => trim( $options['key'] ),
					'activation_email' => sanitize_email( $options['email'] ),
				);

				$this->handle_request( 'activation', $args );
				break;

			case $this->request_name['activate-free'] :
				$this->do_free_activation();
				break;

			case $this->request_name['activate-external'] :
				$this->get_remote_activation_listener_response();
				break;

			case $this->request_name['deactivate'] :
				if ( false === $this->is_plugin_activated() ) {
					$this->set_error_notice( array( 701 => '' ) );
					break;
				} elseif ( false === $this->is_premium_user() ) {
					$this->do_free_deactivation();
					break;
				}

				$args = array(
					'licence_key' => trim( $this->get_option( 'api_key' ) ),
					'activation_email' => sanitize_email( $this->get_option( 'activation_email' ) ),
				);

				$this->handle_request( 'deactivation', $args );
				break;

			case $this->request_name['enable-feed'] :
				$success = $this->update_option( '_enable_feed', true, 'regular', false );
				$code = $success ? 702 : 703;
				$this->set_error_notice( array( $code => '' ) );
				break;

			case $this->request_name['activate-ext'] :
				$success = $this->activate_extension( $options );
				break;

			case $this->request_name['deactivate-ext'] :
				$success = $this->deactivate_extension( $options );
				break;

			default:
				$this->set_error_notice( array( 708 => '' ) );
				break;
		endswitch;

		//* Adds action to the URI. It's only used to visualize what has happened.
		$args = WP_DEBUG ? array( 'did-' . $options['action'] => 'true' ) : array();
		the_seo_framework()->admin_redirect( $this->seo_extensions_page_slug, $args );
		exit;
	}

	/**
	 * Checks the Activation page nonce. Returns false if nonce can't be found
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
	 * @param bool $check_post Whether to check for POST variables.
	 * @return bool True if verified and matches. False if can't verify.
	 */
	public function handle_update_nonce( $key = 'default', $check_post = true ) {

		static $validated = array();

		if ( isset( $validated[ $key ] ) )
			return $validated[ $key ];

		if ( false === $this->is_tsf_extension_manager_page() && false === $this->can_do_settings() )
			return $validated[ $key ] = false;

		if ( $check_post ) {
			/**
			 * If this page doesn't parse the site options,
			 * There's no need to filter them on each request.
			 * Nonce is handled elsewhere. This function merely injects filters to the $_POST data.
			 *
			 * @since 1.0.0
			 */
			if ( empty( $_POST ) || ! isset( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] ) || ! is_array( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] ) )
				return $validated[ $key ] = false;
		}

		check_admin_referer( $this->nonce_action[ $key ], $this->nonce_name );

		return $validated[ $key ] = true;
	}

	/**
	 * Outputs activation notice. If any.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function do_error_notices() {

		if ( $option = get_option( $this->error_notice_option, false ) ) {

			$notice = $this->get_error_notice( $option );

			if ( empty( $notice ) ) {
				$this->unset_error_notice();
				return;
			}

			//* Already escaped.
			the_seo_framework()->do_dismissible_notice( $notice['message'], $notice['type'], true, false );
			$this->unset_error_notice();
		}
	}

	/**
	 * Sets activation notice option.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The activation notice.
	 */
	protected function set_error_notice( $notice = array() ) {
		update_option( $this->error_notice_option, $notice );
	}

	/**
	 * Removes activation notice option.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The activation notice.
	 */
	protected function unset_error_notice() {
		delete_option( $this->error_notice_option );
	}

	/**
	 * Fetches activation notices by option and returns type.
	 *
	 * @since 1.0.0
	 *
	 * @return array|string The escaped activation notice. Empty string when no array key is set.
	 */
	protected function get_error_notice( $option ) {

		if ( is_array( $option ) )
			$key = key( $option );

		if ( empty( $key ) )
			return '';

		switch ( $key ) :
			case 101 :
				$message = esc_html__( 'No valid license key was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 102 :
				$message = esc_html__( 'No valid license email was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 103 :
			case 104 :
			case 701 :
			case 708 :
				$message = esc_html__( 'Invalid API request type.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 201 :
				$message = esc_html__( 'An empty API request was supplied.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 202 :
			case 301 :
			case 302 :
			case 401 :
			case 403 :
			case 404 :
			case 503 :
			case 10003 :
				$message = esc_html__( 'An error occurred while contacting the API server. Please try again later.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 303 :
			case 307 :
				/* translators: %s = My Account */
				$message = sprintf( esc_html__( 'Invalid API License Key. Login to the %s page to find a valid API License Key.', 'the-seo-framework-extension-manager' ), $this->get_my_account_link() );
				$type = 'error';
				break;

			case 304 :
				$message = esc_html__( 'Remote Software API error.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 305 :
				/* translators: %s = My Account */
				$message = sprintf( esc_html__( 'Exceeded maximum number of activations. Login to the %s page to manage your sites.', 'the-seo-framework-extension-manager' ), $this->get_my_account_link() );
				$type = 'error';
				break;

			case 306 :
				$message = esc_html__( 'Invalid Instance ID. Please try again. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 308 :
				$message = esc_html__( 'Subscription is not active or has expired.', 'the-seo-framework-extension-manager' );
				$type = 'warning';
				break;

			case 402 :
				$message = esc_html__( 'Your account has been successfully authorized to be used on this website.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 501 :
			case 502 :
				$message = esc_html__( 'Your account has been successfully deauthorized from this website.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 601 :
				$message = esc_html__( 'Enjoy your free extensions!', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 702 :
				$message = esc_html__( 'The feed has been enabled.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 801 :
				$message = esc_html__( 'Successfully deactivated.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 7001 :
			case 7002 :
				$message = esc_html__( 'An error occured while verifying the options. If this error keeps coming back, please deactivate your account and try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 9001 :
				$message = esc_html__( 'Nonce verification failed. Please try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 10001 :
			case 10002 :
				$message = esc_html__( 'Extension list has been tampered with. Please reinstall this plugin.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 10006 :
			case 10008 :
				$message = esc_html__( 'Extension has been succesfully activated.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 10007 :
				$message = esc_html__( 'Extension is not valid.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 11001 :
				$message = esc_html__( 'Extension has been succesfully deactivated.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 602 :
			case 703 :
			case 802 :
			case 10004 :
			case 10005 :
			case 10009 :
			case 11002 :
			default :
				$message = esc_html__( 'An unknown error occurred. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;
		endswitch;

		switch ( $type ) :
			case 'error' :
			case 'warning' :
				$status_i18n = esc_html__( 'Error code:', 'the-seo-framework-extension-manager' );
				break;

			case 'updated' :
			default :
				$status_i18n = esc_html__( 'Status code:', 'the-seo-framework-extension-manager' );
				break;
		endswitch;

		/* translators: 1: 'Error code:', 2: The error code */
		$status = sprintf( esc_html__( '%1$s %2$s', 'the-seo-framework-extension-manager' ), $status_i18n, $key );
		$additional_info = $option[ $key ];

		/* translators: 1: Error code, 2: Error message, 3: Additional info */
		$output = sprintf( esc_html__( '%1$s &mdash; %2$s %3$s', 'the-seo-framework-extension-manager' ), $status, $message, $additional_info );

		return array(
			'message' => $output,
			'type' => $type,
		);
	}

	/**
	 * Verifies views instances.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance The instance key.
	 * @param int $bit The instance bit.
	 * @return bool True if verified.
	 */
	public function verify_instance( $instance, $bit ) {
		return $instance === $this->get_verification_instance( $bit );
	}

	/**
	 * Generates view instance through bittype and hash comparison.
	 * It's a two-factor verification.
	 *
	 * @since 1.0.0
	 * @staticvar string $instance
	 *
	 * @param int|null $bit The instance bit.
	 * @return string $instance The instance key.
	 */
	protected function get_verification_instance( $bit = null ) {

		static $instance = array();

		$bits = $this->get_bits( true );
		$_bit = $bits[0];

		if ( isset( $instance[ $_bit ] ) ) {
			if ( empty( $instance[ $bit ] ) || $instance[ $_bit ] !== $instance[ $bit ] )
				wp_die( 'Instance verification failed.' );

			return $instance[ $bit ];
		}

		$hash = wp_hash( $_bit . '\\' . __METHOD__ . '\\' . $bit, 'tsfem-instance-' . $bit );

		return $instance[ $bit ] = $instance[ $_bit ] = $hash;
	}

	/**
	 * Generates verification bits based on time.
	 * It's crack-able, but you'll need to know exactly when to intercept. You'll
	 * also need to know the random number. One bit mistake and the plugin stops :).
	 *
	 * @since 1.0.0
	 * @staticvar int $_bit : $bits[0]
	 * @staticvar int $bit  : $bits[1]
	 *
	 * @param bool $previous Whether to fetch the previous set of bits.
	 * @return array The verification bits.
	 */
	protected function get_bits( $previous = false ) {

		static $_bit = null;
		static $bit = null;

		if ( $previous )
			return array( $_bit, $bit );

		if ( null === $bit ) {
			$bit = $_bit = mt_rand( 0, 1073741824 );
			$_bit++;
		}

		$bit | $_bit && $bit++ ^ ~ $_bit-- && $bit ^ $_bit++ && $bit | $_bit++;

		return array( $_bit, $bit );
	}

	/**
	 * Returns the minimum role required to adjust and access settings.
	 *
	 * @since 1.0.0
	 *
	 * @return string The minimum required capability for extensions Settings.
	 */
	public function can_do_settings() {
		return can_do_tsf_extension_manager_settings();
	}

	/**
	 * Determines whether the plugin is network activated.
	 *
	 * @since 1.0.0
	 * @staticvar bool $network_mode
	 *
	 * @return bool Whether the plugin is active in network mode.
	 */
	public function is_plugin_in_network_mode() {

		static $network_mode = null;

		if ( isset( $network_mode ) )
			return $network_mode;

		if ( ! is_multisite() )
			return $network_mode = false;

		$plugins = get_site_option( 'active_sitewide_plugins' );

		return $network_mode = isset( $plugins[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
	}

	/**
	 * Returns admin page URL.
	 * Defaults to the Extension Manager page ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page The admin menu page slug. Defaults to TSF Extension Manager's.
	 * @param array $args Other query arguments.
	 * @return string Admin Page URL.
	 */
	public function get_admin_page_url( $page = '', $args = array() ) {

		$page = $page ? $page : $this->seo_extensions_page_slug;

		$url = add_query_arg( $args, menu_page_url( $page, 0 ) );

		return $url;
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 *        Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, array $args = array() ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		$file = TSF_EXTENSION_MANAGER_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

		include( $file );
	}

	/**
	 * Creates a link and returns it.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The link arguments : {
	 *		'url'     => string The URL. Required.
	 *		'target'  => string The target. Default '_self'.
	 *		'class'   => string The link class. Default ''.
	 *		'title'   => string The link title. Default ''.
	 *		'content' => string The link content. Default ''.
	 * }
	 * @return string escaped link.
	 */
	public function get_link( array $args = array() ) {

		if ( empty( $args ) )
			return '';

		$defaults = array(
			'url'     => '',
			'target'  => '_self',
			'class'   => '',
			'title'   => '',
			'content' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$url = $args['url'] ? esc_url( $args['url'] ) : '';

		if ( empty( $url ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, esc_html__( 'No valid URL was supplied.', 'the-seo-framework-extension-manager' ), null );
			return '';
		}

		$url = ' href="' . $url . '"';
		$class = $args['class'] ? ' class="' . esc_attr( $args['class'] ) . '"' : '';
		$target = ' target="' . esc_attr( $args['target'] ) . '"';
		$title = $args['title'] ? ' title="' . esc_attr( $args['title'] ) . '"' : '';

		return '<a' . $url . $class . $target . $title . '>' . esc_html( $args['content'] ) . '</a>';
	}

	/**
	 * Generates software API My Account page HTML link.
	 *
	 * @since 1.0.0
	 *
	 * @return string The My Account API URL.
	 */
	protected function get_my_account_link() {
		return $this->get_link( array(
			'url' => $this->get_activation_url( 'my-account/' ),
			'target' => '_blank',
			'class' => '',
			'title' => esc_attr__( 'Go to My Account', 'the-seo-framework-extension-manager' ),
			'content' => esc_html__( 'My Account', 'the-seo-framework-extension-manager' ),
		) );
	}

	/**
	 * Generates support link for both Free and Premium.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The support link type. Accepts 'premium' or anything else for free.
	 * @param bool $love Whether to show a heart after the button text.
	 * @return string The Support Link.
	 */
	public function get_support_link( $type = 'free', $love = true ) {

		if ( 'premium' === $type ) {
			$url = $this->get_activation_url( 'get-support' );

			$title = __( 'Get support for premium extensions', 'the-seo-framework-extension-manager' );
			$text = __( 'Premium Support', 'the-seo-framework-extension-manager' );

			$class = $love ? 'tsfem-button-primary tsfem-button-love tsfem-button-premium' : 'tsfem-button tsfem-button-premium';
		} else {
			$url = 'https://wordpress.org/support/plugin/the-seo-framework-extension-manager';

			$title = __( 'Get support for free extensions', 'the-seo-framework-extension-manager' );
			$text = __( 'Free Support', 'the-seo-framework-extension-manager' );

			$class = $love ? 'tsfem-button-primary tsfem-button-love' : 'tsfem-button';
		}

		return $this->get_link( array(
			'url' => $url,
			'target' => '_blank',
			'class' => $class,
			'title' => $title,
			'content' => $text,
		) );
	}

	/**
	 * Activates extension based on form input.
	 *
	 * @since 1.0.0
	 * @TODO bind error notices.
	 *
	 * @param array $options The form input options.
	 * @return bool False on invalid input or on activation failure.
	 */
	protected function activate_extension( $options ) {

		if ( empty( $options['extension'] ) )
			return false;

		$slug = $options['extension'];

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Extensions::initialize( 'activation', $_instance, $bits );
		Extensions::set_account( $this->get_subscription_status() );
		Extensions::set_instance_extension_slug( $slug );

		$checksum = Extensions::get( 'extensions-checksum' );

		if ( empty( $checksum['hash'] ) || empty( $checksum['matches'] ) || empty( $checksum['type'] ) ) {
			$this->set_error_notice( array( 10001 => '' ) );
			return false;
		} elseif ( ! hash_equals( $checksum['matches'][ $checksum['type'] ], $checksum['hash'] ) ) {
			$this->set_error_notice( array( 10002 => '' ) );
			return false;
		}

		$status = Extensions::validate_extension_activation();
		Extensions::reset();

		if ( $status['success'] ) :
			if ( 2 === $status['case'] ) {
				if ( false === $this->validate_remote_subscription_license() ) {
					$this->set_error_notice( array( 10003 => '' ) );
					return false;
				}
			}

			$success = $this->enable_extension( $slug );

			if ( false === $success ) {
				$this->set_error_notice( array( 10004 => '' ) );
				return false;
			}
		endif;

		switch ( $status['case'] ) :
			case 1 :
				$code = 10005;
				break;

			case 2 :
				$code = 10006;
				break;

			case 3 :
				$code = 10007;
				break;

			case 4 :
				$code = 10008;
				break;

			default :
				$code = 10009;
				break;
		endswitch;

		$this->set_error_notice( array( $code => '' ) );

		return $status['success'];
	}

	/**
	 * Deactivates extension based on form input.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The form input options.
	 * @return bool False on invalid input.
	 */
	protected function deactivate_extension( $options ) {

		if ( empty( $options['extension'] ) )
			return false;

		$slug = $options['extension'];
		$success = $this->disable_extension( $slug );

		$code = $success ? 11001 : 11002;
		$this->set_error_notice( array( $code => '' ) );

		return $success;
	}

	/**
	 * Enables extension through options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @return bool False if extension enabling fails.
	 */
	protected function enable_extension( $slug ) {
		return $this->update_extension( $slug, true );
	}

	/**
	 * Disables extension through options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @return bool False if extension disabling fails.
	 */
	protected function disable_extension( $slug ) {
		return $this->update_extension( $slug, false );
	}

	/**
	 * Disables or enables an extension through options.
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @param bool $enable Whether to enable or disable the extension.
	 * @return bool False if extension enabling or disabling fails.
	 */
	protected function update_extension( $slug, $enable = false ) {

		$extensions = $this->get_option( 'active_extensions', array() );
		$extensions[ $slug ] = (bool) $enable;

		return $this->update_option( 'active_extensions', $extensions );
	}

	/**
	 * Returns TSF Extension Manager options array.
	 *
	 * @since 1.0.0
	 *
	 * @return array TSF Extension Manager options.
	 */
	protected function get_all_options() {
		return get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, array() );
	}

	/**
	 * Fetches TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The Option name.
	 * @param mixed $default The fallback value if the option doesn't exist.
	 * @param bool $use_cache Whether to store and use options from cache.
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	protected function get_option( $option, $default = null, $use_cache = true ) {

		if ( ! $option )
			return null;

		if ( false === $use_cache ) {
			$options = $this->get_all_options();

			return isset( $options[ $option ] ) ? $options[ $option ] : $default;
		}

		static $options_cache = array();

		if ( isset( $options_cache[ $option ] ) )
			return $options_cache[ $option ];

		$options = $this->get_all_options();

		return $options_cache[ $option ] = isset( $options[ $option ] ) ? $options[ $option ] : $default;
	}

	/**
	 * Determines whether update_option has already run. Always returns false on
	 * first call. Always returns true on second or later call.
	 *
	 * @since 1.0.0
	 * @staticvar $run Whether update_option has run.
	 *
	 * @return bool True if run, false otherwise.
	 */
	protected function has_run_update_option() {

		static $run = false;

		if ( false === $run ) {
			$run = true;
			return false;
		}

		return true;
	}

	/**
	 * Updates TSF Extension Manager option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed $value The option value.
	 * @param string $type The option update type, accepts 'instance' and 'regular'.
	 * @param bool $kill Whether to kill the plugin on invalid instance.
	 * @return bool True on success or the option is unchanged, false on failure.
	 */
	protected function update_option( $option, $value, $type = 'instance', $kill = false ) {

		if ( ! $option )
			return false;

		$_options = $this->get_all_options();
		$options = $_options;

		//* If option is unchanged, return true.
		if ( isset( $options[ $option ] ) && $value === $options[ $option ] )
			return true;

		$options[ $option ] = $value;

		$this->has_run_update_option();

		$this->initialize_option_update_instance( $type );

	 	if ( empty( $options['_instance'] ) && '_instance' !== $option )
			wp_die( 'Error 7008: Supply an instance key before updating other options.' );

		$success = update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );

		$key = '_instance' === $option ? $value : $options['_instance'];
		$this->set_options_instance( $options, $key );

		if ( false === $this->verify_option_update_instance( $kill ) ) {
			$this->set_error_notice( array( 7001 => '' ) );

			//* Revert option.
			if ( false === $kill )
				update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $_options );

			return false;
		}

		return $success;
	}

	/**
	 * Updates multiple TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options : {
	 *		$option_name => $value,
	 * }
	 * @param string $type The option update type, accepts 'instance' and 'regular'.
	 * @param bool $kill Whether to kill the plugin on invalid instance.
	 * @return bool True on success, false on failure or when options haven't changed.
	 */
	protected function update_option_multi( array $options = array(), $type = 'instance', $kill = false ) {

		static $run = false;

		if ( empty( $options ) )
			return false;

		$_options = $this->get_all_options();

		//* If options are unchanged, return true.
		if ( serialize( $options ) === serialize( $_options ) )
			return true;

		if ( $run ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You may only run this method once per request. Doing so multiple times will result in data deletion.' );
			wp_die();
		}

		if ( $this->has_run_update_option() ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, __CLASS__ . '::update_option() has already run in the current request. Running this function will lead to data deletion.' );
			wp_die();
		}

		$options = wp_parse_args( $options, $_options );
		$run = true;

		$this->initialize_option_update_instance( $type );

	 	if ( empty( $options['_instance'] ) )
			wp_die( 'Error 7009: Supply an instance key before updating other options.' );

		$this->set_options_instance( $options, $options['_instance'] );

		$success = update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );

		if ( false === $this->verify_option_update_instance( $kill ) ) {
			$this->set_error_notice( array( 7002 => '' ) );

			//* Revert option.
			if ( false === $kill )
				update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $_options );

			return false;
		}

		return true;
	}

	/**
	 * Returns verification instance option.
	 *
	 * @since 1.0.0
	 *
	 * @return string The hashed option.
	 */
	protected function get_options_instance() {
		return get_option( 'tsfem_i_' . $this->get_option( '_instance' ) );
	}

	/**
	 * Updates verification instance option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value The option value.
	 * @param string $key Optional. The options key. Must be supplied when activating account.
	 * @return bool True on success, false on failure.
	 */
	protected function update_options_instance( $value, $key = '' ) {

		$key = $key ? $key : $this->get_option( '_instance' );

		return update_option( 'tsfem_i_' . $key, $value );
	}

	/**
	 * Deletes option instance on account deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function delete_options_instance() {

		delete_option( 'tsfem_i_' . $this->get_option( '_instance' ) );

		return true;
	}

	/**
	 * Binds options to an unique hash and saves it in a comparison option.
	 * This prevents users from altering the options from outside this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The options to hash.
	 * @param string $key The instance key, needs to be supplied on plugin activation.
	 * @return bool True on success, false on failure.
	 */
	protected function set_options_instance( $options, $key = '' ) {

		if ( empty( $options['_instance'] ) )
			return false;

		$_options = serialize( $options );
		$hash = $this->make_hash( $_options );

		if ( $hash ) {
			$update = $this->update_options_instance( $hash, $key );

			if ( false === $update ) {
				$this->set_error_notice( array( 7001 => '' ) );
				return false;
			}
			return true;
		} else {
			$this->set_error_notice( array( 7002 => '' ) );
			return false;
		}
	}

	/**
	 * Returns hash key based on sha256 if available. Otherwise it will fall back
	 * to md5 (wp_hash()).
	 *
	 * @since 1.0.0
	 * @see @link https://developer.wordpress.org/reference/functions/wp_hash/
	 *
	 * @param string $data The data to hash.
	 * @return string The hash key.
	 */
	protected function make_hash( $data ) {

		if ( in_array( 'sha256', hash_algos(), true ) ) {
			$salt = wp_salt( 'auth' );
			$hash = hash_hmac( 'sha256', $data, $salt );
		} else {
			$hash = wp_hash( $data, 'auth' );
		}

		return $hash;
	}

	/**
	 * Verifies options hash.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data The data to compare hash with.
	 * @return bool True when hash passes, false on failure.
	 */
	public function verify_options_hash( $data ) {
		return hash_equals( $this->make_hash( $data ), $this->get_options_instance() );
	}

	/**
	 * Initializes option update instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type What type of update this is, accepts 'instance' and 'regular'.
	 */
	protected function initialize_option_update_instance( $type = 'regular' ) {

		if ( 'instance' === $type ) {
			$type = 'update_option_instance';
		} elseif ( 'regular' === $type ) {
			$type = 'update_option';
		}

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		SecureOption::initialize( $type, $_instance, $bits );

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );
		SecureOption::set_update_instance( $_instance, $bits );

	}

	/**
	 * Verifies if update went as expected.
	 * Deletes plugin options if data has been adjusted.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $kill Whether to kill plugin options.
	 * @return bool True on success, false on failure.
	 */
	protected function verify_option_update_instance( $kill = false ) {

		$verify = SecureOption::verified_option_update();

		if ( $kill && false === $verify )
			$this->kill_options();

		SecureOption::reset();

		return $verify;
	}

	/**
	 * Deletes all plugin options when an options breach has been spotted.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	protected function kill_options() {

		$success = array();
		$success[] = $this->delete_options_instance();
		$success[] = delete_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS );

		return ! in_array( false, $success, true );
	}
}
