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
 * Require option trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'options' );

/**
 * Class TSF_Extension_Manager\Core
 *
 * Holds plugin core functions.
 *
 * @since 1.0.0
 * @access private
 */
class Core {
	use Enclose_Stray_Private, Construct_Core_Interface, Destruct_Core_Public_Final, Options;

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
	 * Returns an array of active extensions real path.
	 *
	 * @since 1.0.0
	 *
	 * @var array List of active extensions real path.
	 */
	protected $active_extensions = array();

	/**
	 * Constructor, initializes actions and sets up variables.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . ( is_admin() ? '\\LoadAdmin' : '\\LoadFrontend' );
		$this instanceof $that or wp_die( -1 );

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
			//* Reference convenience.
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

	}

	/**
	 * Handles extensions. On both the front end and back-end.
	 *
	 * @since 1.0.0
	 * @staticvar bool $loaded True if extensions are loaded, false otherwise.
	 *
	 * @return true If loaded, false otherwise.
	 */
	public function init_extensions() {

		static $loaded = null;

		if ( isset( $loaded ) )
			return $loaded;

		if ( wp_installing() || false === $this->is_plugin_activated() )
			return $loaded = false;

		if ( false === $this->are_options_valid() ) {
			//* Failed options instance checksum.
			$this->set_error_notice( array( 2001 => '' ) );
			return $loaded = false;
		}

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Extensions::initialize( 'list', $_instance, $bits );
		Extensions::set_account( $this->get_subscription_status() );

		$checksum = Extensions::get( 'extensions_checksum' );
		$result = $this->validate_extensions_checksum( $checksum );

		if ( true !== $result ) :
			switch ( $result ) :
				case -1 :
					//* No extensions have ever been active...
					;

				case -2 :
					//* Failed checksum.
					$this->set_error_notice( array( 2002 => '' ) );
					;

				default :
					Extensions::reset();
					return $loaded = false;
					break;
			endswitch;
		endif;

		$extensions = Extensions::get( 'active_extensions_list' );

		Extensions::reset();

		if ( empty( $extensions ) )
			return $loaded = false;

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Extensions::initialize( 'load', $_instance, $bits );

		foreach ( $extensions as $slug => $active ) {
			$bits = $this->get_bits();
			$_instance = $this->get_verification_instance( $bits[1] );

			Extensions::load_extension( $slug, $_instance, $bits );
		}

		Extensions::reset();

		return $loaded = true;
	}

	/**
	 * Verifies integrity of the options.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool True if options are valid, false if not.
	 */
	protected function are_options_valid() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->verify_options_hash( serialize( $this->get_all_options() ) );
	}

	/**
	 * Handles plugin POST requests.
	 *
	 * @since 1.0.0
	 *
	 * @return bool False if nonce failed.
	 */
	public function handle_update_post() {

		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ]['nonce-action'] ) )
			return;

		$options = $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ];

		if ( false === $this->handle_update_nonce( $options['nonce-action'], false ) )
			return;

		switch ( $options['nonce-action'] ) :
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
				} elseif ( false === $this->is_premium_user() || false === $this->are_options_valid() ) {
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
		$args = WP_DEBUG ? array( 'did-' . $options['nonce-action'] => 'true' ) : array();
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
	 * @param bool $check_post Whether to check for POST variables containing TSFEM settings.
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
			 * there's no need to check them on each request.
			 */
			if ( empty( $_POST ) || ! isset( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] ) || ! is_array( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] ) )
				return $validated[ $key ] = false;
		}

		$result = isset( $_POST[ $this->nonce_name ] ) ? wp_verify_nonce( $_POST[ $this->nonce_name ], $this->nonce_action[ $key ] ) : false;

		if ( false === $result ) {
			//* Nonce failed. Set error notice and reload.
			$this->set_error_notice( array( 9001 => '' ) );
			the_seo_framework()->admin_redirect( $this->seo_extensions_page_slug );
			exit;
		}

		return $validated[ $key ] = (bool) $result;
	}

	/**
	 * Outputs notices. If any, and only on the Extension manager page.
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
	 * Sets notices option, only does so when in the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The notice.
	 */
	protected function set_error_notice( $notice = array() ) {
		is_admin() and update_option( $this->error_notice_option, $notice );
	}

	/**
	 * Removes notices option.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The notice.
	 */
	protected function unset_error_notice() {
		delete_option( $this->error_notice_option );
	}

	/**
	 * Fetches notices by option and returns type.
	 *
	 * @since 1.0.0
	 *
	 * @param int|array $option The error notice key.
	 * @return array|string The escaped notice. Empty string when no array key is set.
	 */
	protected function get_error_notice( $option ) {

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
			case 403 :
			case 404 :
			case 405 :
			case 503 :
			case 10003 :
				$message = esc_html__( 'An error occurred while contacting the API server. Please try again later.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 401 :
				/* translators: %s = My Account */
				$message = sprintf( esc_html__( 'An error occured while validating settings. Login to the %s page to manage your keys and try again.', 'the-seo-framework-extension-manager' ), $this->get_my_account_link() );
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

			case 2001 :
			case 7001 :
			case 7002 :
				$message = esc_html__( 'An error occured while verifying the options. If this error keeps coming back, please deactivate your account and try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			//* IT'S OVER NINE THOUSAAAAAAAAAAAAAAAAAAAAAAND!!one!1!!
			case 9001 :
				$message = esc_html__( 'Nonce verification failed. Please try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 2002 :
			case 10001 :
			case 10002 :
				$message = esc_html__( 'Extension list has been tampered with. Please reinstall this plugin and try again.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 10004 :
				$message = esc_html__( 'Extension is not compatible with your server configuration.', 'the-seo-framework-extension-manager' );
				$type = 'error';
				break;

			case 10007 :
			case 10009 :
				$message = esc_html__( 'Extension has been succesfully activated.', 'the-seo-framework-extension-manager' );
				$type = 'updated';
				break;

			case 10008 :
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
			case 10005 :
			case 10006 :
			case 10010 :
			case 11002 :
			default :
				$message = esc_html__( 'An unknown error occurred. Contact the plugin author if this error keeps coming back.', 'the-seo-framework-extension-manager' );
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
	 * }
	 */
	protected function get_ajax_notice( $success, $code ) {

		$notice = array( 'success' => $success, 'notice' => $this->get_error_notice_by_key( $code, false ) );

		if ( WP_DEBUG )
			$notice = array_merge( $notice, array( 'code' => intval( $code ) ) );

		return $notice;
	}

	/**
	 * Destroys output buffer, if any. To be used with AJAX to clear any PHP errors or dumps.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on clear. False otherwise.
	 */
	protected function clean_ajax_reponse_header() {

		if ( ob_get_level() && ob_get_contents() ) {
			ob_clean();
			return true;
		}

		return false;
	}

	/**
	 * Performs wp_die on TSF Extension Manager Page.
	 * Descructs class otherwise.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $message The error message.
	 * @return bool false If no wp_die has been performed.
	 */
	final public function _maybe_die( $message = '' ) {

		if ( $this->is_tsf_extension_manager_page( false ) ) {
			//* wp_die() can be filtered. Remove filters JIT.
			remove_all_filters( 'wp_die_ajax_handler' );
			remove_all_filters( 'wp_die_xmlrpc_handler' );
			remove_all_filters( 'wp_die_handler' );

			wp_die( esc_html( $message ) );
	 	}

		//* Don't spam error log.
		if ( false === $this->_has_died() ) {
			if ( $message ) {
				the_seo_framework()->_doing_it_wrong( __CLASS__, 'Class execution stopped with message: <strong>' . esc_html( $message ) . '</strong>' );
			} else {
				the_seo_framework()->_doing_it_wrong( __CLASS__, 'Class execution stopped because of an error.' );
			}
		}

		$this->stop_class();
		$this->_has_died( true );

		return false;
	}

	/**
	 * Stops class from executing. A true destructor.
	 * Removes all instance properties, and removes instance from global $wp_filter.
	 *
	 * @since 1.0.0
	 *
	 * @return bool true
	 */
	final protected function stop_class() {

		$class_vars = get_class_vars( __CLASS__ );
		$other_vars = get_class_vars( get_called_class() );

		$properties = array_merge( $class_vars, $other_vars );

		foreach ( $properties as $property => $value ) :
			if ( isset( $this->$property ) )
				$this->$property = is_array( $this->$property ) ? array() : null;
		endforeach;

		array_walk( $GLOBALS['wp_filter'], array( $this, 'stop_class_filters' ) );
		$this->__destruct();

		return true;
	}

	/**
	 * Forces wp_filter removal. It's quite heavy, used in "oh dear God" circumstances.
	 *
	 * Searches current filter, and if the namespace of this namespace is found,
	 * it will destroy it from globals $wp_filter.
	 *
	 * @since 1.0.0
	 *
	 * @param array $current_filter The filter to walk.
	 * @param string $key The current array key.
	 * @return bool true
	 */
	final protected function stop_class_filters( $current_filter, $key ) {

		$_key = key( $current_filter );
		$filter = isset( $current_filter[ $_key ] ) and reset( $current_filter[ $_key ] );

		static $_this = null;

		if ( null === $_this )
			$_this = get_class( $this );

		if ( isset( $filter['function'] ) ) {
			if ( is_array( $filter['function'] ) ) :
				foreach ( $filter['function'] as $k => $function ) :
					if ( is_object( $function ) && get_class( $function ) === $_this )
						unset( $GLOBALS['wp_filter'][ $key ][ $_key ] );
				endforeach;
			endif;
		}

		return true;
	}

	/**
	 * Verifies views instances.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $instance The verification instance key.
	 * @param int $bit The verification instance bit.
	 * @return bool True if verified.
	 */
	final public function _verify_instance( $instance, $bit ) {
		return $instance === $this->get_verification_instance( $bit );
	}

	/**
	 * Loops through instance verification in order to fetch multiple instance keys.
	 *
	 * Must be used within a foreach loop. Instance must be verified within each loop iteration.
	 * Must be able to validate usage first with the 2nd and 3rd parameter.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $count The amount of instances to loop for.
	 * @param string $instance The verification instance key.
	 * @param array $bits The verification instance bits.
	 * @yield array Generator : {
	 *		$instance string The verification instance key
	 *		$bits array The verification instance bits
	 * }
	 */
	final public function _yield_verification_instance( $count, $instance, $bits ) {

		if ( $this->_verify_instance( $instance, $bits[1] ) ) :
			for ( $i = 0; $i < $count; $i++ ) :
				yield array(
					'bits'     => $bits = $this->get_bits(),
					'instance' => $this->get_verification_instance( $bits[1] ),
				);
			endfor;
		endif;

	}

	/**
	 * Generates view instance through bittype and hash comparison.
	 * It's a two-factor verification.
	 *
	 * Performs wp_die() on TSF Extension Manager's admin page. Otherwise it
	 * will silently fail and destruct class.
	 *
	 * @since 1.0.0
	 * @staticvar string $instance
	 *
	 * @param int|null $bit The instance bit.
	 * @return string $instance The instance key.
	 */
	final protected function get_verification_instance( $bit = null ) {

		static $instance = array();

		$bits = $this->get_bits();
		$_bit = $bits[0];

		if ( isset( $instance[ ~ $_bit ] ) ) {
			if ( empty( $instance[ $bit ] ) || $instance[ ~ $_bit ] !== $instance[ $bit ] ) {
				//* Only die on plugin settings page upon failure. Otherwise kill instance and all bindings.
				$this->_maybe_die( 'Error -1: The SEO Framework Extension Manager instance verification failed.' ) xor $instance = array();
				return '';
			}

			//* Prevent another way of timing attacks.
			$val = $instance[ $bit ] and $instance = array();

			return $val;
		}

		//* This won't save to database, but does create a unique salt for each bit.
		$hash = $this->hash( $_bit . '\\' . mt_rand( 0, time() ) . '\\' . $bit, 'instance' );

		return $instance[ $bit ] = $instance[ ~ $_bit ] = $hash;
	}

	/**
	 * Generates verification bits based on time.
	 *
	 * The bit generation is 4 dimensional, this makes it time-attack secure.
	 * It other words: Previous bits can't be re-used as the match will be
	 * subequal in the upcoming check.
	 *
	 * @since 1.0.0
	 * @staticvar int $_bit : $bits[0]
	 * @staticvar int $bit  : $bits[1]
	 *
	 * @return array The verification bits.
	 */
	final protected function get_bits() {

		static $_bit, $bit;

		if ( isset( $bit ) )
			goto generate;

		/**
		 * Create new bits on first run.
		 * Prevents random abstract collision by filtering odd numbers.
		 */
		set : {
			    $time = time()
			and $bit = $_bit = mt_rand( - $time, $time )
			and $bit % 2
			and $bit = $_bit++;
		}

		if ( 0 === $bit ) {
			goto set;
		}

		generate : {
			/**
			 * Count to create an irregular bit verification.
			 * This can jump multiple sequences while maintaining the previous.
			 * It traverses in three (actually two, but get_verification_instance makes it
			 * three) dimensions: up (positive), down (negative) and right (new sequence).
			 */
			    $bit  = $_bit <= 0 ? ~$bit-- | ~$_bit-- : ~$bit-- | ~$_bit++
			and $bit  = $bit++ & $_bit--
			and $bit  = $bit < 0 ? $bit++ : $bit--
			and $_bit = $_bit < 0 ? $_bit : ~$_bit
			and $bit  = ~$_bit++
			and $_bit = $_bit < 0 ? $_bit : ~$_bit
			and $bit++;
		}

		return array( $_bit, $bit );
	}

	/**
	 * Hashes input $data with the best hash type available while also using hmac.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data The data to hash.
	 * @param string $scheme Authentication scheme ( 'instance', 'auth', 'secure_auth', 'nonce' ).
	 *                       Default 'instance'.
	 * @return string Hash of $data.
	 */
	final protected function hash( $data, $scheme = 'instance' ) {

		$salt = $this->get_salt( $scheme );

		return hash_hmac( $this->get_hash_type(), $data, $salt );
	}

	/**
	 * Generates salt from WordPress defined constants.
	 *
	 * Taken from WordPress core function `wp_salt()` and adjusted accordingly.
	 * @link https://developer.wordpress.org/reference/functions/wp_salt/
	 *
	 * @since 1.0.0
	 * @staticvar array $cached_salts Contains cached salts based on $scheme input.
	 * @staticvar string $instance_scheme Random scheme for instance verification. Determined at runtime.
	 *
	 * @param string $scheme Authentication scheme. ( 'instance', 'auth', 'secure_auth', 'nonce' ).
	 *                       Default 'instance'.
	 * @return string Salt value.
	 */
	final protected function get_salt( $scheme = 'instance' ) {

		static $cached_salts = array();

		if ( isset( $cached_salts[ $scheme ] ) )
			return $cached_salts[ $scheme ];

		$values = array(
			'key'  => '',
			'salt' => '',
		);

		$schemes = array( 'auth', 'secure_auth', 'logged_in', 'nonce' );

		//* 'instance' picks a random key.
		static $instance_scheme = null;
		if ( null === $instance_scheme ) {
			$_key = mt_rand( 0, count( $schemes ) - 1 );
			$instance_scheme = $schemes[ $_key ];
		}
		$scheme = 'instance' === $scheme ? $instance_scheme : $scheme;

		if ( in_array( $scheme, $schemes, true ) ) {
			foreach ( array( 'key', 'salt' ) as $type ) :
				$const = strtoupper( "{$scheme}_{$type}" );
				if ( defined( $const ) && constant( $const ) ) {
					$values[ $type ] = constant( $const );
				} elseif ( empty( $values[ $type ] ) ) {
					$values[ $type ] = get_site_option( "{$scheme}_{$type}" );
					if ( ! $values[ $type ] ) {
						/**
						 * Hash keys not defined in wp-config.php nor in database.
						 * Let wp_salt() handle this. This should run at most once per site per scheme.
						 */
						$values[ $type ] = wp_salt( $scheme );
					}
				}
			endforeach;
		} else {
			wp_die( 'Invalid scheme supplied for <code>' . __METHOD__ . '</code>.' );
		}

		$cached_salts[ $scheme ] = $values['key'] . $values['salt'];

		return $cached_salts[ $scheme ];
	}

	/**
	 * Returns working hash type.
	 *
	 * @since 1.0.0
	 * @staticvar string $type
	 *
	 * @return string The working hash type to be used within hash() functions.
	 */
	final public function get_hash_type() {

		static $type = null;

		if ( isset( $type ) )
			return $type;

		$algos = hash_algos();

		if ( in_array( 'sha256', $algos, true ) ) {
			$type = 'sha256';
		} elseif ( in_array( 'sha1', $algos, true ) ) {
			$type = 'sha1';
		} else {
			$type = 'md5';
		}

		return $type;
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

			$class = $love ? 'tsfem-button-primary tsfem-button-star tsfem-button-premium' : 'tsfem-button tsfem-button-premium';
		} else {
			$url = 'https://wordpress.org/support/plugin/the-seo-framework-extension-manager';

			$title = __( 'Get support for free extensions', 'the-seo-framework-extension-manager' );
			$text = __( 'Free Support', 'the-seo-framework-extension-manager' );

			$class = $love ? 'tsfem-button-primary tsfem-button-love' : 'tsfem-button';
		}

		//* TODO: TEMPORARILY. REMOVE. var_dump().
		$class .= ' tsfem-button-disabled';

		return $this->get_link( array(
			'url' => $url,
			'target' => '_blank',
			'class' => $class,
			'title' => $title,
			'content' => $text,
		) );
	}

	/**
	 * Registers autoloading classes for extensions and activates autoloader.
	 * If the account isn't premium, it will not be loaded.
	 *
	 * @since 1.0.0
	 * @access private
	 * @staticvar bool $autoload_inactive Whether the autoloader is active.
	 *
	 * @param string $path The extension path to look for.
	 * @param string $class_base Class base words.
	 * @return bool True on success, false on failure.
	 */
	public function _register_premium_extension_autoload_path( $path, $class_base ) {

		if ( false === $this->is_premium_user() || false === $this->are_options_valid() )
			return false;

		static $autoload_inactive = true;

		if ( $autoload_inactive ) {
			spl_autoload_register( array( $this, 'autoload_premium_extension_class' ) );
			$autoload_inactive = false;
		}

		return $this->set_premium_extension_autoload_path( $path, $class_base );
	}

	/**
	 * Registers autoloading classes for extensions.
	 * Maintains a cache. So this can be fetched later.
	 *
	 * @since 1.0.0
	 * @staticvar array $registered The registered classes.
	 *
	 * @param string|null $path The extension path to look for.
	 * @param string|null $class_base Class base words.
	 * @param string|null $class The classname to fetch from cache.
	 * @return void|bool|array : {
	 * 		void  : $class if not set. Default behavior.
	 * 		false : $class isn't found in $locations.
	 *		array : $class is found in locations.
	 * }
	 */
	protected function set_premium_extension_autoload_path( $path, $class_base, $class = null ) {

		static $locations = array();

		if ( $class ) {
			$class = str_replace( 'TSF_Extension_Manager_Extension\\', '', $class );

			//* Singular class names. Recommended as its much faster.
			if ( isset( $locations[ $class ] ) )
				return $locations[ $class ];

			//* Extended class names. Slower but feasible.
			$class_bases = explode( '_', $class );

			$_class_base = '';
			foreach ( $class_bases as $_class_base_part ) :
				$_class_base .= $_class_base ? '_' . $_class_base_part : $_class_base_part;

				if ( isset( $locations[ $_class_base ] ) )
					return $locations[ $_class_base ];

				continue;
			endforeach;

			return false;
		}

		$locations[ $class_base ] = $path;

		return;
	}

	/**
	 * Returns the registered $class name base path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The classname path to fetch.
	 * @return string|bool The path if found. False otherwise.
	 */
	protected function get_premium_extension_autload_path( $class ) {
		return $this->set_premium_extension_autoload_path( null, null, $class );
	}

	/**
	 * Autoloads all class files. To be used when requiring access to all or any of
	 * the plugin classes.
	 *
	 * @since 1.0.0
	 * @staticvar array $loaded Whether $class has been loaded.
	 *
	 * @param string $class The extension classname.
	 * @return bool False if file hasn't yet been included, otherwise true.
	 */
	protected function autoload_premium_extension_class( $class ) {

		if ( 0 !== strpos( $class, 'TSF_Extension_Manager_Extension\\', 0 ) )
			return;

		static $loaded = array();

		if ( isset( $loaded[ $class ] ) )
			return $loaded[ $class ];

		$path = $this->get_premium_extension_autload_path( $class );

		if ( $path ) {
			$_class = strtolower( str_replace( 'TSF_Extension_Manager_Extension\\', '', $class ) );
			$_class = str_replace( '_', '-', $_class );

			$bits = $this->get_bits();
			$_instance = $this->get_verification_instance( $bits[1] );

			return $loaded[ $class ] = require_once( $path . $_class . '.class.php' );
		} else {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'Class <code>' . esc_html( $class ) . '</code> could not be registered.' );

			//* Most likely, a fatal error will now occur.
			return $loaded[ $class ] = false;
		}
	}

	/**
	 * Validates extensions option checksum.
	 *
	 * @since 1.0.0
	 *
	 * @param array $checksum The extensions checksum.
	 * @return int|bool, Negative int on failure, true on success.
	 */
	protected function validate_extensions_checksum( $checksum ) {

		if ( empty( $checksum['hash'] ) || empty( $checksum['matches'] ) || empty( $checksum['type'] ) ) {
			return -1;
		} elseif ( ! hash_equals( $checksum['matches'][ $checksum['type'] ], $checksum['hash'] ) ) {
			return -2;
		}

		return true;
	}

	/**
	 * Activates extension based on form input.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The form/request input options.
	 * @param bool $ajax Whether this is an AJAX request.
	 * @return bool False on invalid input or on activation failure.
	 */
	protected function activate_extension( $options, $ajax = false ) {

		if ( empty( $options['extension'] ) )
			return false;

		$slug = $options['extension'];

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Extensions::initialize( 'activation', $_instance, $bits );
		Extensions::set_account( $this->get_subscription_status() );
		Extensions::set_instance_extension_slug( $slug );

		$checksum = Extensions::get( 'extensions_checksum' );
		$result = $this->validate_extensions_checksum( $checksum );

		if ( true !== $result ) :
			switch ( $result ) :
				case -1 :
					//* No checksum found.
					$ajax or $this->set_error_notice( array( 10001 => '' ) );
					return $ajax ? $this->get_ajax_notice( false, 10001 ) : false;
					break;

				case -2 :
					//* Checksum mismatch.
					$ajax or $this->set_error_notice( array( 10002 => '' ) );
					return $ajax ? $this->get_ajax_notice( false, 10002 ) : false;
					break;

				default :
					//* @TODO consider return unknown error.
					break;
			endswitch;
		endif;

		$status = Extensions::validate_extension_activation();
		Extensions::reset();

		if ( $status['success'] ) :
			if ( 2 === $status['case'] ) {
				if ( false === $this->validate_remote_subscription_license() ) {
					$ajax or $this->set_error_notice( array( 10003 => '' ) );
					return $ajax ? $this->get_ajax_notice( false, 10003 ) : false;
				}
			}

			$test = $this->test_extension( $slug, $ajax );

			if ( 4 !== $test || $this->_has_died() ) {
				$ajax or $this->set_error_notice( array( 10004 => '' ) );
				return $ajax ? $this->get_ajax_notice( false, 10004 ) : false;
			}

			$success = $this->enable_extension( $slug );

			if ( false === $success ) {
				$ajax or $this->set_error_notice( array( 10005 => '' ) );
				return $ajax ? $this->get_ajax_notice( false, 10005 ) : false;
			}
		endif;

		switch ( $status['case'] ) :
			case 1 :
				//* No slug set.
				$code = 10006;
				break;

			case 2 :
				//* Premium activated.
				$code = 10007;
				break;

			case 3 :
				//* Premium failed: User not premium.
				$code = 10008;
				break;

			case 4 :
				//* Free activated.
				$code = 10009;
				break;

			default :
				//* Unknown case.
				$code = 10010;
				break;
		endswitch;

		$ajax or $this->set_error_notice( array( $code => '' ) );

		return $ajax ? $this->get_ajax_notice( $status['success'], $code ) : $status['success'];
	}

	/**
	 * Deactivates extension based on form input.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The form input options.
	 * @param bool $ajax Whether this is an AJAX request.
	 * @return bool False on invalid input.
	 */
	protected function deactivate_extension( $options, $ajax = false ) {

		if ( empty( $options['extension'] ) )
			return false;

		$slug = $options['extension'];
		$success = $this->disable_extension( $slug );

		$code = $success ? 11001 : 11002;
		$ajax or $this->set_error_notice( array( $code => '' ) );

		return $ajax ? $this->get_ajax_notice( $success, $code ) : $success;
	}

	/**
	 * Test drives extension to see if an error occurs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug to load.
	 * @param bool $ajax Whether this is an AJAX request.
	 * @return int|void {
	 * 		-1 => No check has been performed.
	 * 		1 => No file header path can be created. (Invalid extension)
	 * 		2 => Extension header file is invalid. (Invalid extension)
	 * 		3 => Inclusion failed.
	 * 		4 => Success.
	 * 		void => Fatal error.
	 * }
	 */
	protected function test_extension( $slug, $ajax = false ) {

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Extensions::initialize( 'load', $_instance, $bits );

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		$result = Extensions::test_extension( $slug, $ajax, $_instance, $bits );
		Extensions::reset();

		return $result;
	}

	/**
	 * Enables extension through options.
	 *
	 * Kills options when activation fails.
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
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @param bool $enable Whether to enable or disable the extension.
	 * @return bool False if extension enabling or disabling fails.
	 */
	protected function update_extension( $slug, $enable = false ) {

		$extensions = $this->get_option( 'active_extensions', array() );
		$extensions[ $slug ] = (bool) $enable;

		//* Kill options on failure when enabling.
		$kill = $enable;

		return $this->update_option( 'active_extensions', $extensions, 'regular', $kill );
	}

	/**
	 * Sanitizes AJAX input string.
	 * Removes NULL, converts to string, normalizes entities and escapes attributes.
	 * Also prevents regex execution.
	 *
	 * @since 1.0.0
	 *
	 * @param string $input The AJAX input string.
	 * @return string $output The cleaned AJAX input string.
	 */
	protected function s_ajax_string( $input ) {
		return trim( esc_attr( wp_kses_normalize_entities( strval( wp_kses_no_null( $input ) ) ) ), ' \\/#' );
	}

	/**
	 * Returns font file location.
	 * To be used for testing font-pixels.
	 *
	 * @since 1.0.0
	 *
	 * @param string $font The font name, should include .ttf.
	 * @param bool $url Whether to return a path or URL.
	 * @return string The font URL or path. Not escaped.
	 */
	public function get_font_file_location( $font = '', $url = false ) {
		if ( $url ) {
			return TSF_EXTENSION_MANAGER_DIR_URL . 'lib/fonts/' . $font;
		} else {
			return TSF_EXTENSION_MANAGER_DIR_PATH . 'lib' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $font;
		}
	}

	/**
	 * Returns image file location.
	 *
	 * @since 1.0.0
	 *
	 * @param string $image The image name, should include .jpg, .png, etc..
	 * @param bool $url Whether to return a path or URL.
	 * @return string The image URL or path. Not escaped.
	 */
	public function get_image_file_location( $image = '', $url = false ) {
		if ( $url ) {
			return TSF_EXTENSION_MANAGER_DIR_URL . 'lib/images/' . $image;
		} else {
			return TSF_EXTENSION_MANAGER_DIR_PATH . 'lib' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $image;
		}
	}

	/**
	 * Converts pixels to points.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $px The pixels amount. Accepts 42 as well as '42px'.
	 * @return int Points.
	 */
	public function pixels_to_points( $px = 0 ) {
		return intval( $px ) * 0.75;
	}

	/**
	 * Determines whether we're on the SEO extension manager settings page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @param bool $secure Whether to prevent insecure checks.
	 * @return bool
	 */
	public function is_tsf_extension_manager_page( $secure = true ) {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( false === is_admin() )
			return $cache = false;

		if ( $secure ) {
			//* Don't load from $_GET request if secure.
			return $cache = the_seo_framework()->is_menu_page( $this->seo_extensions_menu_page_hook );
		} else {
			//* Don't cache if insecure.
			return the_seo_framework()->is_menu_page( $this->seo_extensions_menu_page_hook, $this->seo_extensions_page_slug );
		}
	}

	/**
	 * Determines whether the plugin's activated. Either free or premium.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool True if the plugin is activated.
	 */
	protected function is_plugin_activated() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = 'Activated' === $this->get_option( '_activated' );
	}

	/**
	 * Determines whether the plugin's use is premium.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool True if the plugin is connected to the API handler.
	 */
	protected function is_premium_user() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = 'Premium' === $this->get_option( '_activation_level' );
	}

	/**
	 * Returns subscription status from local options.
	 *
	 * @since 1.0.0
	 * @staticvar array $status
	 *
	 * @return array Current subscription status.
	 */
	protected function get_subscription_status() {

		static $status = null;

		if ( null !== $status )
			return $status;

		return $status = array(
			'key'     => $this->get_option( 'api_key' ),
			'email'   => $this->get_option( 'activation_email' ),
			'active'  => $this->get_option( '_activated' ),
			'level'   => $this->get_option( '_activation_level' ),
			'data'    => $this->get_option( '_remote_subscription_status' ),
		);
	}


	/**
	 * Converts markdown text into HMTL.
	 *
	 * Does not support list or block elements. Only inline statements.
	 *
	 * @since 1.0.0
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text The text that might contain markdown. Expected to be escaped.
	 * @param array $convert The markdown style types wished to be converted.
	 * 				If left empty, it will convert all.
	 * @return string The markdown converted text.
	 */
	public function convert_markdown( $text, $convert = array() ) {

		preprocess : {
			$text = str_replace( "\r\n", "\n", $text );
			$text = str_replace( "\t", ' ', $text );
		}

		if ( '' === $text )
			return '';

		/**
		 * The conversion list's keys are per reference only.
		 */
		$conversions = array(
			'**'   => 'strong',
			'*'    => 'em',
			'`'    => 'code',
			'[]()' => 'a',
			'======'  => 'h6',
			'====='  => 'h5',
			'===='  => 'h4',
			'==='  => 'h3',
			'=='   => 'h2',
			'='    => 'h1',
		);

		$md_types = empty( $convert ) ? $conversions : array_intersect( $conversions, $convert );

		foreach ( $md_types as $type ) :
			switch ( $type ) :
				case 'strong' :
					//* Considers word boundary. @TODO consider removing this?
					$text = preg_replace( '/(?:\*{2})\b([^\*{2}]+)(?:\*{2})/', '<strong>${1}</strong>', $text );
					break;

				case 'em' :
					$text = preg_replace( '/(?:\*{1})([^\*{1}]+)(?:\*{1})/', '<em>${1}</em>', $text );
					break;

				case 'code' :
					$text = preg_replace( '/(?:`{1})([^`{1}]+)(?:`{1})/', '<code>${1}</code>', $text );
					break;

				case 'h6' :
				case 'h5' :
				case 'h4' :
				case 'h3' :
				case 'h2' :
				case 'h1' :
					//* Considers word non-boundary. @TODO consider removing this?
					$amount = filter_var( $type, FILTER_SANITIZE_NUMBER_INT );
					$expression = "/(?:={{$amount}})\B([^={{$amount}}]+?)\B(?:={{$amount}})/";
					$replacement = "<{$type}>${1}</{$type}>";
					$text = preg_replace( $expression, $replacement, $text );
					break;

				case 'a' :
					getmatches : {
						$count = preg_match_all( '/(?:(?:\[{1})([^\]{1}]+)(?:\]{1})(?:\({1})([^\)\(]+)(?:\){1}))/', $text, $matches, PREG_PATTERN_ORDER );
					}
					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<a href="%s" rel="nofollow">%s</a>', esc_url( $matches[2][ $i ] ), esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				default :
					break;
			endswitch;
		endforeach;

		return $text;
	}
}
