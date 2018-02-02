<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Facade Class TSF_Extension_Manager\LoadAdmin.
 *
 * Initializes plugin classes.
 *
 * @since 1.0.0
 * @access private
 * @final
 */
final class LoadAdmin extends AdminPages {
	use Construct_Master_Once_Interface,
		Enclose_Stray_Private;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Load activation notices.
		\add_action( 'admin_notices', [ $this, 'do_activation_notice' ] );

		//* Check API blocking.
		\add_action( 'tsfem_notices', [ $this, 'check_external_blocking' ] );

		//* Ajax listener for error notice catching.
		\add_action( 'wp_ajax_tsfem_get_dismissible_notice', [ $this, '_wp_ajax_get_dismissible_notice' ] );

		//* AJAX listener for form iterations.
		\add_action( 'wp_ajax_tsfemForm_iterate', [ $this, '_wp_ajax_tsfemForm_iterate' ], 11 );

		//* AJAX listener for form saving.
		\add_action( 'wp_ajax_tsfemForm_save', [ $this, '_wp_ajax_tsfemForm_save' ], 11 );

		//* AJAX listener for Geocoding.
		\add_action( 'wp_ajax_tsfemForm_get_geocode', [ $this, '_wp_ajax_tsfemForm_get_geocode' ], 11 );

		//* AJAX listener for image cropping.
		\add_action( 'wp_ajax_tsfem_crop_image', [ $this, '_wp_ajax_crop_image' ] );

		//* Listener for updates.
		\add_action( 'admin_init', [ $this, '_handle_update_post' ] );
	}

	/**
	 * Checks whether the WP installation blocks external requests.
	 * Shows notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
	 *
	 * @since 1.0.0
	 */
	public function check_external_blocking() {

		if ( false === $this->is_tsf_extension_manager_page() || false === $this->can_do_settings() )
			return;

		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && true === WP_HTTP_BLOCK_EXTERNAL ) {

			$parsed_url = \wp_parse_url( $this->get_activation_url() );
			$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

			if ( false === defined( 'WP_ACCESSIBLE_HOSTS' ) || false === stristr( WP_ACCESSIBLE_HOSTS, $host ) ) {
				$notice = $this->convert_markdown(
					sprintf(
						/* translators: Markdown. %s = API URL */
						\esc_html__(
							'This website is blocking external requests, this means it will not be able to connect to the API services. Please add `%s` to `WP_ACCESSIBLE_HOSTS`.',
							'the-seo-framework-extension-manager'
						),
						\esc_html( $host )
					),
					[ 'code' ]
				);
				//* Already escaped.
				$this->do_dismissible_notice( $notice, 'error', true, false );
			}
		}
	}

	/**
	 * Send AJAX notices. If any.
	 *
	 * @since 1.3.0
	 * @uses trait TSF_Extension_Manager\Error
	 * @access private
	 */
	final public function _wp_ajax_get_dismissible_notice() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :
				if ( \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) ) {

					$key = \tsf_extension_manager()->coalesce_var( $_POST['tsfem-notice-key'], false );
					$key = intval( $key );

					if ( $key ) {
						$_notice = $this->get_error_notice( $key );

						if ( is_array( $_notice ) ) {
							//= If it has a custom message (already stored in browser), then don't output the notice message.
							$msg = ! empty( $_POST['tsfem-notice-has-msg'] ) ? $_notice['before'] : $_notice['message'];
							$type = $_notice['type'];

							$notice = $this->get_dismissible_notice( $msg, $type, true, false );
							$_type = $notice ? 'success' : 'failure';
						}
					}
				}

				$this->send_json( compact( 'type', 'notice', 'key' ), \tsf_extension_manager()->coalesce_var( $_type, 'failure' ) );
			endif;
		endif;

		exit;
	}

	/**
	 * Propagate FormGenerator class AJAX iteration calls.
	 *
	 * @since 1.3.0
	 * @uses class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	final public function _wp_ajax_tsfemForm_iterate() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :
				if ( \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false ) ) {

					/**
					 * Allows callers to prepare iteration class.
					 * @see class TSF_Extension_Manager\FormGenerator
					 * @access protected
					 */
					\do_action( 'tsfem_form_prepare_ajax_iterations' );

					/**
					 * Outputs the iteration items when properly prepared and when matched.
					 *
					 * This action shouldn't be called upon by extensions.
					 *
					 * @see class TSF_Extension_Manager\FormGenerator
					 * @access private
					 */
					\do_action( 'tsfem_form_do_ajax_iterations' );
				}
			endif;

			$this->send_json( [ 'results' => $this->get_ajax_notice( false, 9002 ) ], 'failure' );
		endif;

		exit;
	}

	/**
	 * Propagate FormGenerator class AJAX save calls.
	 *
	 * @since 1.3.0
	 * @uses class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	final public function _wp_ajax_tsfemForm_save() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :
				if ( \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false ) ) {
					/**
					 * Allows callers to save POST data.
					 * @see class TSF_Extension_Manager\FormGenerator
					 * @access protected
					 */
					\do_action( 'tsfem_form_do_ajax_save' );
				}
			endif;

			$this->send_json( [ 'results' => $this->get_ajax_notice( false, 9003 ) ], 'failure' );
		endif;

		exit;
	}

	/**
	 * Returns Geocoding data form FormGenerator's address fields.
	 * On failure, it returns an AJAX error code.
	 *
	 * @since 1.3.0
	 * @see class TSF_Extension_Manager\FormGenerator
	 * @access private
	 */
	final public function _wp_ajax_tsfemForm_get_geocode() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :
				if ( \check_ajax_referer( 'tsfem-form-nonce', 'nonce', false ) ) {

					//= Input gets forwarded to secure location. Sanitation happens externally.
					$input = isset( $_POST['input'] ) ? json_decode( \wp_unslash( $_POST['input'] ) ) : '';

					if ( ! $input || ! is_object( $input ) ) {
						$results = $this->get_ajax_notice( false, 17000 );
					} else {
						$subscription = $this->get_subscription_status();
						$args = [
							'request'     => 'geocoding/get',
							'email'       => $subscription['email'],
							'licence_key' => $subscription['key'],
							'data' => [
								'geodata' => json_encode( $input ),
								//= get_user_locale() is WP 4.7+
								'locale' => function_exists( '\get_user_locale' ) ? \get_user_locale() : \get_locale(),
							],
						];

						$response = $this->get_api_response( $args );
						$response = json_decode( $response );

						if ( ! isset( $response->success ) ) {
							$results = $this->get_ajax_notice( false, 17001 );
						} else {
							if ( ! isset( $response->data ) ) {
								$results = $this->get_ajax_notice( false, 17002 );
							} else {
								$data = json_decode( $response->data, true );

								if ( ! $data ) {
									$results = $this->get_ajax_notice( false, 17003 );
								} else {
									$this->coalesce_var( $data['status'] );

									if ( 'OK' !== $data['status'] ) {
										switch ( $data['status'] ) :
											//* @link https://developers.google.com/maps/documentation/geocoding/intro#reverse-response
											case 'ZERO_RESULTS' :
												$results = $this->get_ajax_notice( false, 17004 );
												break;

											case 'OVER_QUERY_LIMIT' :
												// This should never be invoked.
												$results = $this->get_ajax_notice( false, 17005 );
												break;

											case 'REQUEST_DENIED' :
												// This should never be invoked.
												$results = $this->get_ajax_notice( false, 17006 );
												break;

											case 'INVALID_REQUEST' :
												//= Data is missing.
												$results = $this->get_ajax_notice( false, 17007 );
												break;

											case 'UNKNOWN_ERROR' :
												//= Remote Geocoding API error. Try again...
												$results = $this->get_ajax_notice( false, 17008 );
												break;

											case 'TIMEOUT' :
												//= Too many consecutive requests.
												$results = $this->get_ajax_notice( false, 17009 );
												break;

											case 'RATE_LIMIT' :
												//= Too many requests in the last period.
												$results = $this->get_ajax_notice( false, 17010 );
												break;

											default :
												//= ??
												$results = $this->get_ajax_notice( false, 17011 );
												break;
										endswitch;
									} else {
										$results = $this->get_ajax_notice( false, 17012 );
										$geodata =& $data;
										$_type = 'success';
									}
								}
							}
						}
					}

					$this->send_json( compact( 'results', 'geodata' ), \tsf_extension_manager()->coalesce_var( $_type, 'failure' ) );
					exit;
				}
			endif;

			$this->send_json( [ 'results' => $this->get_ajax_notice( false, 9004 ) ], 'failure' );
		endif;

		exit;
	}

	/**
	 * Handles plugin POST requests.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void If nonce failed.
	 */
	final public function _handle_update_post() {

		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ]['nonce-action'] ) )
			return;

		//* Post is taken and will be validated directly below.
		$options = $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ];

		//* Options exist. There's no need to check again them.
		if ( false === $this->handle_update_nonce( $options['nonce-action'], false ) )
			return;

		switch ( $options['nonce-action'] ) :
			case $this->request_name['activate-key'] :
				$args = [
					'licence_key' => trim( $options['key'] ),
					'activation_email' => \sanitize_email( $options['email'] ),
				];

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
					$this->set_error_notice( [ 701 => '' ] );
					break;
				} elseif ( false === $this->is_premium_user() || false === $this->are_options_valid() ) {
					$this->do_free_deactivation();
					break;
				}

				$args = [
					'licence_key' => trim( $this->get_option( 'api_key' ) ),
					'activation_email' => sanitize_email( $this->get_option( 'activation_email' ) ),
				];

				$this->handle_request( 'deactivation', $args );
				break;

			case $this->request_name['enable-feed'] :
				$success = $this->update_option( '_enable_feed', true, 'regular', false );
				$code = $success ? 702 : 703;
				$this->set_error_notice( [ $code => '' ] );
				break;

			case $this->request_name['activate-ext'] :
				$success = $this->activate_extension( $options );
				break;

			case $this->request_name['deactivate-ext'] :
				$success = $this->deactivate_extension( $options );
				break;

			default :
				$this->set_error_notice( [ 708 => '' ] );
				break;
		endswitch;

		//* Adds action to the URI. It's only used to visualize what has happened.
		$args = WP_DEBUG ? [ 'did-' . $options['nonce-action'] => 'true' ] : [];
		\the_seo_framework()->admin_redirect( $this->seo_extensions_page_slug, $args );
		exit;
	}

	/**
	 * Checks the Extension Manager page's nonce. Returns false if nonce can't be found
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

		$result = isset( $_POST[ $this->nonce_name ] ) ? \wp_verify_nonce( stripslashes( $_POST[ $this->nonce_name ] ), $this->nonce_action[ $key ] ) : false;

		if ( false === $result ) {
			//* Nonce failed. Set error notice and reload.
			$this->set_error_notice( [ 9001 => '' ] );
			\the_seo_framework()->admin_redirect( $this->seo_extensions_page_slug );
			exit;
		}

		return $validated[ $key ] = (bool) $result;
	}

	/**
	 * Adds dashboard notice for when the user still needs to choose a license type.
	 * The goal is to eliminate confusion, although slightly annoying.
	 *
	 * @since 1.0.0
	 */
	public function do_activation_notice() {

		if ( $this->is_plugin_activated() || false === $this->can_do_settings() || $this->is_tsf_extension_manager_page() )
			return;

		$text = \__( 'Your extensions are only three clicks away', 'the-seo-framework-extension-manager' );
		$url = $this->get_admin_page_url();
		$title = \__( 'Activate the SEO Extension Manager', 'the-seo-framework-extension-manager' );

		$notice_link = '<a href="' . \esc_url( $url ) . '" title="' . \esc_attr( $title ) . '" target=_self>' . \esc_html( $title ) . '</a>';
		$notice = \esc_html( $text ) . ' &mdash; ' . $notice_link;

		//* No a11y icon. Already escaped. Use TSF as it loads styles.
		\the_seo_framework()->do_dismissible_notice( $notice, 'updated', false, false );
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
	final public function get_admin_page_url( $page = '', $args = [] ) {

		$page = $page ? $page : $this->seo_extensions_page_slug;

		$url = \add_query_arg( $args, \menu_page_url( $page, false ) );

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
	final protected function get_view( $view, array $args = [] ) {

		foreach ( $args as $key => $val ) {
			$$key = $val;
		}

		$this->get_verification_codes( $_instance, $bits );

		$file = TSF_EXTENSION_MANAGER_DIR_PATH . 'views'
		      . DIRECTORY_SEPARATOR . $view . '.php';

		include $file;
	}

	/**
	 * Includes templates for JS.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template The template file name.
	 */
	final public function _include_template( $template ) {

		$this->get_verification_codes( $_instance, $bits );

		$file = TSF_EXTENSION_MANAGER_DIR_PATH . 'views'
		      . DIRECTORY_SEPARATOR . 'template'
		      . DIRECTORY_SEPARATOR . $template . '.php';

		include $file;
	}

	/**
	 * Creates a link and returns it.
	 *
	 * If URL is '#', then it no href will be set.
	 * If URL is empty, a doing it wrong notice will be output.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 : Added download, filename, id and data.
	 * @since 1.5.0 : Now always adds `rel="nofollow noopener noreferrer"`
	 *
	 * @param array $args The link arguments : {
	 *   'url'      => string The URL. Required.
	 *   'target'   => string The target. Default '_self'.
	 *   'class'    => string The link class. Default ''.
	 *   'id'       => string The link id. Default ''.
	 *   'title'    => string The link title. Default ''.
	 *   'content'  => string The link content. Default ''.
	 *   'download' => bool Whether to download. Default false.
	 *   'filename' => string The optional download filename. Default ''.
	 *   'data'     => array Array of data-$keys and $values.
	 * }
	 * @return string escaped link.
	 */
	final public function get_link( array $args = [] ) {

		if ( empty( $args ) )
			return '';

		$defaults = [
			'url'     => '',
			'target'  => '_self',
			'rel' => 'nofollow noopener noreferrer',
			'class'   => '',
			'id'      => '',
			'title'   => '',
			'content' => '',
			'download' => false,
			'filename' => '',
			'data' => [],
		];
		$args = array_filter( array_merge( $defaults, $args ) );

		if ( empty( $args['url'] ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, \esc_html__( 'No valid URL was supplied.', 'the-seo-framework-extension-manager' ), null );
			return '';
		}

		$content = ! empty( $args['content'] ) ? $args['content'] : '';
		unset( $args['content'] );
		$parts = [];

		foreach ( $args as $type => $value ) :
			switch ( $type ) :
				case 'class' :
				case 'title' :
				case 'rel' :
					$parts[] = $type . '="' . \esc_attr( $value ) . '"';
					break;

				case 'id' :
				case 'target' :
					$parts[] = $type . '=' . \esc_attr( $value );
					break;

				case 'url' :
					if ( '#' !== $value )
						$parts[] = 'href="' . $value . '"';
					break;

				case 'download' :
					if ( isset( $args['filename'] ) ) {
						$parts[] = 'download="' . \esc_attr( $args['filename'] ) . '"';
					} else {
						$parts[] = 'download';
					}
					unset( $args['filename'] );
					break;

				case 'data' :
					foreach ( $value as $k => $v ) {
						$parts[] = sprintf( 'data-%s="%s"', \esc_attr( $k ), \esc_attr( $v ) );
					}
					break;

				default :
					break;
			endswitch;
		endforeach;

		return sprintf( '<a %s>%s</a>', implode( ' ', $parts ), \esc_html( $content ) );
	}

	/**
	 * Creates a download button link from input arguments.
	 *
	 * @since 1.2.0
	 *
	 * @param array $args The button arguments.
	 * @return string The download button.
	 */
	final public function get_download_link( array $args = [] ) {

		$defaults = [
			'url'     => '',
			'target'  => '_self',
			'class'   => '',
			'title'   => '',
			'content' => '',
			'download' => true,
			'filename' => '',
			'data' => [],
		];

		return $this->get_link( \wp_parse_args( $args, $defaults ) );
	}

	/**
	 * Generates software API My Account page HTML link.
	 *
	 * @since 1.0.0
	 *
	 * @return string The My Account API URL.
	 */
	final protected function get_my_account_link() {
		return $this->get_link( [
			'url' => $this->get_activation_url( 'my-account/' ),
			'target' => '_blank',
			'class' => '',
			'title' => \esc_attr__( 'Go to My Account', 'the-seo-framework-extension-manager' ),
			'content' => \esc_html__( 'My Account', 'the-seo-framework-extension-manager' ),
		] );
	}

	/**
	 * Generates support link for both Free and Premium.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The support link type. Accepts 'premium' or anything else for free.
	 * @param bool $icon Whether to show a heart/star after the button text.
	 * @return string The Support Link.
	 */
	final public function get_support_link( $type = 'free', $icon = true ) {

		if ( 'premium' === $type ) {
			$url = 'https://premium.theseoframework.com/support/';

			$title = \__( 'Get support for premium extensions', 'the-seo-framework-extension-manager' );
			$text = \__( 'Premium Support', 'the-seo-framework-extension-manager' );

			$class = 'tsfem-button-primary tsfem-button-primary-bright';
			$class .= $icon ? ' tsfem-button-star' : '';
		} else {
			$url = 'https://wordpress.org/support/plugin/the-seo-framework-extension-manager';

			$title = \__( 'Get support for free extensions', 'the-seo-framework-extension-manager' );
			$text = \__( 'Free Support', 'the-seo-framework-extension-manager' );

			$class = 'tsfem-button-primary';
			$class .= $icon ? ' tsfem-button-love' : '';
		}

		return $this->get_link( [
			'url' => $url,
			'target' => '_blank',
			'class' => $class,
			'title' => $title,
			'content' => $text,
		] );
	}

	/**
	 * Generates dismissible notice.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 1.3.0
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type The notice type : 'updated', 'success', 'error', 'warning'.
	 * @param bool $a11y Whether to add an accessibility icon.
	 * @param bool $escape Whether to escape the whole output.
	 * @return string The dismissible error notice.
	 */
	final public function get_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {

		switch ( $type ) :
			case 'success' :
			case 'updated' :
				$type = 'tsfem-notice-success';
				break;

			case 'warning' :
				$type = 'tsfem-notice-warning';
				break;

			case 'error' :
				$type = 'tsfem-notice-error';
				break;

			default :
				$type = '';
				break;
		endswitch;

		$a11y = $a11y ? ' tsfem-show-icon' : '';

		$notice = '<div class="tsfem-notice ' . \esc_attr( $type ) . $a11y . '"><p>';
		$notice .= '<a class="hide-if-no-js tsfem-dismiss" title="' . \esc_attr__( 'Dismiss', 'the-seo-framework-extension-manager' ) . '"></a>';
		$notice .= $escape ? \esc_html( $message ) : $message;
		$notice .= '</p></div>';

		return $notice;
	}

	/**
	 * Echos generated dismissible notice.
	 *
	 * @since 1.3.0
	 *
	 * @param $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type The notice type : 'updated', 'success', 'error', 'warning'.
	 * @param bool $a11y Whether to add an accessibility icon.
	 * @param bool $escape Whether to escape the whole output.
	 */
	final public function do_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {
		echo $this->get_dismissible_notice( $message, $type, (bool) $a11y, (bool) $escape );
	}

	/**
	 * Sets admin menu links so the pages can be safely used within AJAX.
	 *
	 * Does not forge a callback function, instead, the callback returns an empty string.
	 *
	 * @since 1.2.0
	 * @access private
	 * @staticvar bool $parent_set
	 * @staticvar array $slug_set
	 *
	 * @param string $slug The menu slug. Required.
	 * @param string $capability The menu's required access capability.
	 * @return bool True on success, false on failure.
	 */
	final public function _set_ajax_menu_link( $slug, $capability = 'manage_options' ) {

		if ( ( ! $slug = \sanitize_key( $slug ) )
		|| ( ! $capability = \sanitize_key( $capability ) )
		|| ! \current_user_can( $capability )
		) {
			return false;
		}

		static $parent_set = false;
		static $set = [];

		if ( false === $parent_set && ( $parent_set = true ) ) {
			//* Set parent slug.
			\the_seo_framework()->add_menu_link();
		}

		if ( isset( $set[ $slug ] ) )
			return $set[ $slug ];

		//* Add arbitrary menu contents to known menu slug.
		$menu = [
			'parent_slug' => \the_seo_framework_options_page_slug(),
			'page_title'  => '1',
			'menu_title'  => '1',
			'capability'  => $capability,
			'menu_slug'   => $slug,
			'callback'    => '\\__return_empty_string',
		];

		return $set[ $slug ] = (bool) \add_submenu_page(
			$menu['parent_slug'],
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);
	}

	/**
	 * Determines if TSFEM AJAX has determined the correct page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 * @NOTE Warning: Only set after valid nonce verification pass.
	 *
	 * @param bool $set If true, it registers the AJAX page.
	 * @return bool True if set, false otherwise.
	 */
	final protected function ajax_is_tsf_extension_manager_page( $set = false ) {

		static $cache = false;

		return $set ? $cache = true : $cache;
	}

	/**
	 * Activates extension based on form input.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The form/request input options.
	 * @param bool $ajax Whether this is an AJAX request.
	 * @return bool|string False on invalid input or on activation failure.
	 *         String on success or AJAX.
	 */
	final protected function activate_extension( $options, $ajax = false ) {

		if ( empty( $options['extension'] ) )
			return false;

		$slug = \sanitize_key( $options['extension'] );

		$this->get_verification_codes( $_instance, $bits );

		Extensions::initialize( 'activation', $_instance, $bits );
		Extensions::set_account( $this->get_subscription_status() );
		Extensions::set_instance_extension_slug( $slug );

		$checksum = Extensions::get( 'extensions_checksum' );
		$result = $this->validate_extensions_checksum( $checksum );

		if ( true !== $result ) :
			switch ( $result ) :
				case -1 :
					//* No checksum found.
					$ajax or $this->set_error_notice( [ 10001 => '' ] );
					return $ajax ? $this->get_ajax_notice( false, 10001 ) : false;
					break;

				case -2 :
					//* Checksum mismatch.
					$ajax or $this->set_error_notice( [ 10002 => '' ] );
					return $ajax ? $this->get_ajax_notice( false, 10002 ) : false;
					break;

				default :
					//* Method mismatch error. Unknown error.
					$ajax or $this->set_error_notice( [ 10003 => '' ] );
					return $ajax ? $this->get_ajax_notice( false, 10003 ) : false;
					break;
			endswitch;
		endif;

		$status = Extensions::validate_extension_activation();

		Extensions::reset();

		if ( $status['success'] ) :
			if ( 2 === $status['case'] ) {
				if ( 0 === $this->validate_remote_subscription_license() ) {
					$ajax or $this->set_error_notice( [ 10004 => '' ] );
					return $ajax ? $this->get_ajax_notice( false, 10004 ) : false;
				}
			}

			$test = $this->test_extension( $slug, $ajax );

			if ( 4 !== $test || $this->_has_died() ) {
				$ajax or $this->set_error_notice( [ 10005 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 10005 ) : false;
			}

			$success = $this->enable_extension( $slug );

			if ( false === $success ) {
				$ajax or $this->set_error_notice( [ 10006 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 10006 ) : false;
			}
		endif;

		switch ( $status['case'] ) :
			case 1 :
				//* No slug set.
				$code = 10007;
				break;

			case 2 :
				//* Premium activated.
				$code = 10008;
				break;

			case 3 :
				//* Premium failed: User not premium.
				$code = 10009;
				break;

			case 4 :
				//* Free activated.
				$code = 10010;
				break;

			default :
				//* Unknown case.
				$code = 10011;
				break;
		endswitch;

		$ajax or $this->register_extension_state_change_notice( $code, $slug );

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
	final protected function deactivate_extension( $options, $ajax = false ) {

		if ( empty( $options['extension'] ) )
			return false;

		$slug = \sanitize_key( $options['extension'] );
		$success = $this->disable_extension( $slug );

		$code = $success ? 11001 : 11002;
		$ajax or $this->register_extension_state_change_notice( $code, $slug );

		return $ajax ? $this->get_ajax_notice( $success, $code ) : $success;
	}

	/**
	 * Registers extension state change code including slug message.
	 *
	 * @since 1.5.0
	 *
	 * @param int    $code The error/success code.
	 * @param string $slug The extension slug. Must be escaped.
	 */
	final protected function register_extension_state_change_notice( $code, $slug ) {
		$this->set_error_notice( [
			$code => sprintf(
				'<strong><em>(%s)</em></strong>',
				sprintf(
					/* translators: %s = extension slug */
					\esc_html__( 'Extension slug: %s', 'the-seo-framework-extension-manager' ),
					$slug
				)
			),
		] );
	}

	/**
	 * Test drives extension to see if an error occurs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug to load.
	 * @param bool $ajax Whether this is an AJAX request.
	 * @return int|void {
	 *    -1 : No check has been performed.
	 *    1  : No file header path can be created. (Invalid extension)
	 *    2  : Extension header file is invalid. (Invalid extension)
	 *    3  : Inclusion failed.
	 *    4  : Success.
	 *    void : Fatal error.
	 * }
	 */
	final protected function test_extension( $slug, $ajax = false ) {

		$this->get_verification_codes( $_instance, $bits );
		Extensions::initialize( 'load', $_instance, $bits );

		$this->get_verification_codes( $_instance, $bits );
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
	final protected function enable_extension( $slug ) {
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
	final protected function disable_extension( $slug ) {
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
	final protected function update_extension( $slug, $enable = false ) {

		$extensions = $this->get_option( 'active_extensions', [] );
		$extensions[ $slug ] = (bool) $enable;

		//* Kill options on failure when enabling.
		$kill = $enable;

		return $this->update_option( 'active_extensions', $extensions, 'regular', $kill );
	}
}
