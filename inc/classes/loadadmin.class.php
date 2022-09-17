<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	use Construct_Master_Once_Interface;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		// Load activation notices.
		\add_action( 'admin_notices', [ $this, 'do_activation_notice' ] );

		// Check API blocking.
		\add_action( 'tsfem_notices', [ $this, 'check_external_blocking' ] );

		$this->is_auto_activated()
			and \add_action( 'admin_init', [ $this, '_check_constant_activation' ] );

		// Listener for updates.
		\add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		// Listener for AJAX.
		\add_action( 'admin_init', [ $this, '_prepare_admin_ajax' ] );
	}

	/**
	 * Tries activating the account via a contstant.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	public function _check_constant_activation() {

		// Store in $var for PHP<7 compiling compat.
		$data = TSF_EXTENSION_MANAGER_API_INFORMATION;
		$data = [
			'email' => \sanitize_email( $data['email'] ),
			'key'   => trim( $data['key'] ),
		];

		if ( $this->is_connected_user() ) {
			$current = $this->get_subscription_status();
			$equals  = array_intersect_assoc( $current, $data );

			if ( isset( $equals['email'], $equals['key'] ) ) return;

			$args = [
				'activation_email' => $current['email'],
				'licence_key'      => $current['key'],
			];
			$this->handle_request( 'deactivation', $args );

			if ( $this->is_tsf_extension_manager_page( false ) ) {
				// Reload dashboard.
				\tsf()->admin_redirect( $this->seo_extensions_page_slug );
				exit;
			}
		} else {
			$timeout = \get_transient( 'tsf-extension-manager-auto-activate-timeout' );
			if ( $timeout ) return;
			\set_transient( 'tsf-extension-manager-auto-activate-timeout', 1, MINUTE_IN_SECONDS * 5 );

			$args = [
				'activation_email' => $data['email'],
				'licence_key'      => $data['key'],
			];
			$this->handle_request( 'activation', $args );

			if ( $this->is_tsf_extension_manager_page( false ) ) {
				// Reload dashboard.
				\tsf()->admin_redirect( $this->seo_extensions_page_slug );
				exit;
			}
		}
	}

	/**
	 * Checks whether the WP installation blocks external requests.
	 * Shows notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
	 *
	 * @since 1.0.0
	 * @since 2.1.0 Now checks on both our API endpoints.
	 * @see WP Core WP_Site_Health()->get_test_dotorg_communication(), we might want to use that markup instead.
	 */
	public function check_external_blocking() {

		if ( ! $this->is_tsf_extension_manager_page() || ! \TSF_Extension_Manager\can_do_manager_settings() )
			return;

		if ( ! \defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL )
			return;

		$show_notice = ! \defined( 'WP_ACCESSIBLE_HOSTS' );
		if ( ! $show_notice ) {
			$wildcard_host = '*.theseoframework.com';
			/**
			 * This is an inconsiderate check, may we ever wish to change it.
			 *
			 * @TODO maintain this well, and don't recommend it to our users.
			 */
			if ( false !== stristr( WP_ACCESSIBLE_HOSTS, $wildcard_host ) )
				return;
		}

		$hosts     = [];
		$endpoints = [
			TSF_EXTENSION_MANAGER_PREMIUM_URI,
			TSF_EXTENSION_MANAGER_PREMIUM_EU_URI,
		];

		foreach ( $endpoints as $endpoint ) {
			$hosts[] = parse_url( $endpoint, PHP_URL_HOST );
		}

		if ( ! $show_notice ) {
			foreach ( $hosts as $_host ) {
				if ( false === stristr( WP_ACCESSIBLE_HOSTS, $_host ) ) {
					/**
					 * Users won't connect to the EU endpoint if they enter a global key.
					 * Nevertheless, still nudge, for they might.
					 */
					$show_notice = true;
					break;
				}
			}
		}

		if ( ! $show_notice ) return;

		$tsf = \tsf();

		// Already escaped.
		$tsf->do_dismissible_notice(
			$tsf->convert_markdown(
				sprintf(
					/* translators: Markdown. %s = API URL */
					\esc_html__(
						'This website is blocking external requests, this means it will not be able to connect to the API services. Please add `%s` to `WP_ACCESSIBLE_HOSTS`.',
						'the-seo-framework-extension-manager'
					),
					\esc_html( implode( ',', $hosts ) )
				),
				[ 'code' ]
			),
			'error',
			true,
			false,
			true
		);
	}

	/**
	 * Handles plugin POST requests.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void If nonce failed.
	 */
	public function _handle_update_post() {

		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- handle_update_nonce does this.
		if ( empty( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ]['nonce-action'] ) )
			return;

		// Post is taken and will be validated directly below.
		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- handle_update_nonce does this.
		$options = $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ];

		// Options exist. There's no need to check again them.
		if ( false === $this->handle_update_nonce( $options['nonce-action'], false ) )
			return;

		switch ( $options['nonce-action'] ) :
			case $this->request_name['activate-key']:
				if ( $this->is_auto_activated() ) break;
				$args = [
					'licence_key'      => trim( $options['key'] ),
					'activation_email' => \sanitize_email( $options['email'] ),
				];

				$this->handle_request( 'activation', $args );
				break;

			case $this->request_name['activate-free']:
				$this->do_free_activation();
				break;

			case $this->request_name['activate-external']:
				if ( $this->is_auto_activated() ) break;
				$this->get_remote_activation_listener_response();
				break;

			case $this->request_name['deactivate']:
				if ( $this->is_auto_activated() ) break;
				if ( false === $this->is_plugin_activated() ) {
					$this->set_error_notice( [ 701 => '' ] );
					break;
				} elseif ( false === $this->is_connected_user() || false === $this->are_options_valid() ) {
					$this->do_free_deactivation();
					break;
				}

				$args = [
					'licence_key'      => trim( $this->get_option( 'api_key' ) ),
					'activation_email' => \sanitize_email( $this->get_option( 'activation_email' ) ),
				];

				if ( ! $this->handle_request( 'deactivation', $args ) ) {
					// Deactivate regardless, without requesting.
					$this->kill_options();
				}
				break;

			case $this->request_name['enable-feed']:
				$success = $this->update_option( '_enable_feed', true, 'regular', false );
				$this->set_error_notice( [ $success ? 702 : 703 => '' ] );
				break;

			case $this->request_name['activate-ext']:
				$success = $this->activate_extension( $options );
				break;

			case $this->request_name['deactivate-ext']:
				$success = $this->deactivate_extension( $options );
				break;

			default:
				$this->set_error_notice( [ 708 => '' ] );
				break;
		endswitch;

		// Adds action to the URI. It's only used to visualize what has happened.
		$args = WP_DEBUG ? [ 'did-' . $options['nonce-action'] => 'true' ] : [];
		\tsf()->admin_redirect( $this->seo_extensions_page_slug, $args );
		exit;
	}

	/**
	 * Loads AJAX actions.
	 *
	 * @since 2.1.0
	 */
	public function _prepare_admin_ajax() {
		if ( \wp_doing_ajax() ) {
			$this->get_verification_codes( $_instance, $bits );
			AJAX::initialize( '', $_instance, $bits );
			AJAX::set_secret_api_key( $this->_create_protected_api_access_key( AJAX::class ) );
			AJAX::set_account( $this->get_subscription_status() );
		}
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
	 *
	 * @param string $key The nonce action used for caching.
	 * @param bool   $check_post Whether to check for POST variables containing TSFEM settings.
	 * @return bool True if verified and matches. False if can't verify.
	 */
	protected function handle_update_nonce( $key = 'default', $check_post = true ) {

		static $validated = [];

		if ( isset( $validated[ $key ] ) )
			return $validated[ $key ];

		if ( ! \TSF_Extension_Manager\can_do_manager_settings() )
			return $validated[ $key ] = false;

		if ( $check_post ) {
			// If this page doesn't parse the site options, there's no need to check them on each request.
			if ( empty( $_POST ) // input var ok
			|| ! isset( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] )
			|| ! \is_array( $_POST[ TSF_EXTENSION_MANAGER_SITE_OPTIONS ] ) )
				return $validated[ $key ] = false;
		}

		$result = isset( $_POST[ $this->nonce_name ] )
				? \wp_verify_nonce( \wp_unslash( $_POST[ $this->nonce_name ] ), $this->nonce_action[ $key ] )
				: false;

		if ( false === $result ) {
			// Nonce failed. Set error notice and reload.
			$this->set_error_notice( [ 9001 => '' ] );
			\tsf()->admin_redirect( $this->seo_extensions_page_slug );
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

		if ( $this->is_plugin_activated() || ! \TSF_Extension_Manager\can_do_manager_settings() || $this->is_tsf_extension_manager_page() )
			return;

		$text  = \__( 'Your extensions are only three clicks away', 'the-seo-framework-extension-manager' );
		$url   = $this->get_admin_page_url();
		$title = \__( 'Activate the SEO Extension Manager', 'the-seo-framework-extension-manager' );

		$notice_link = '<a href="' . \esc_url( $url ) . '" title="' . \esc_attr( $title ) . '" target=_self>' . \esc_html( $title ) . '</a>';

		$notice = \esc_html( $text ) . ' &mdash; ' . $notice_link;

		// No a11y icon. Already escaped. Use TSF as it loads styles.
		\tsf()->do_dismissible_notice( $notice, 'updated', false, false, false );
	}

	/**
	 * Returns admin page URL.
	 * Defaults to the Extension Manager page ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page The admin menu page slug. Defaults to TSF Extension Manager's.
	 * @param array  $args Other query arguments.
	 * @return string Admin Page URL.
	 */
	public function get_admin_page_url( $page = '', $args = [] ) {

		$page = $page ? $page : $this->seo_extensions_page_slug;

		return \add_query_arg( $args, \menu_page_url( $page, false ) );
	}

	/**
	 * Tests whether we're on the right administrative page.
	 *
	 * @since 2.5.0
	 * @global string $pagenow
	 *
	 * @param string|array $pagenow A list of pagenow values, or a single one.
	 * @return bool True if we're on that page, false if called too early or when we aren't.
	 */
	public function is_pagenow( $pagenow = '' ) {
		return isset( $GLOBALS['pagenow'] ) && \in_array( $GLOBALS['pagenow'], (array) $pagenow, true );
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view The file name.
	 * @param array  $args The arguments to be supplied within the file name.
	 *                     Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, array $args = [] ) {

		foreach ( $args as $key => $val ) {
			$$key = $val;
		}

		$this->get_verification_codes( $_instance, $bits );

		include $this->get_view_location( $view );
	}

	/**
	 * Includes templates for JS.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template The template file name.
	 */
	public function _include_template( $template ) {

		$this->get_verification_codes( $_instance, $bits );

		include $this->get_template_location( $template );
	}

	/**
	 * Returns view location.
	 *
	 * @since 1.5.0
	 *
	 * @param string $view The view file name.
	 */
	public function get_view_location( $view ) {
		return TSF_EXTENSION_MANAGER_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
	}

	/**
	 * Returns template location.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template The template file name.
	 */
	public function get_template_location( $template ) {
		return $this->get_view_location( 'template' . DIRECTORY_SEPARATOR . $template );
	}

	/**
	 * Creates a link and returns it.
	 *
	 * If URL is '#', then it no href will be set.
	 * If URL is empty, a doing it wrong notice will be output.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added download, filename, id and data.
	 * @since 1.5.0 Now always adds `rel="nofollow noopener noreferrer"`
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
	public function get_link( array $args = [] ) {

		if ( empty( $args ) )
			return '';

		$defaults = [
			'url'      => '',
			'target'   => '_self',
			'rel'      => 'nofollow noreferrer',
			'class'    => '',
			'id'       => '',
			'title'    => '',
			'content'  => '',
			'download' => false,
			'filename' => '',
			'data'     => [],
		];

		$args = array_filter( array_merge( $defaults, $args ) );

		if ( '_blank' === $args['target'] ) {
			$args['rel'] .= ' noopener';
		}

		if ( empty( $args['url'] ) ) {
			\tsf()->_doing_it_wrong( __METHOD__, \esc_html__( 'No valid URL was supplied.', 'the-seo-framework-extension-manager' ), null );
			return '';
		}

		$content = ! empty( $args['content'] ) ? $args['content'] : '';
		unset( $args['content'] );
		$parts = [];

		foreach ( $args as $type => $value ) :
			switch ( $type ) :
				case 'class':
				case 'title':
				case 'rel':
					$parts[] = $type . '="' . \esc_attr( $value ) . '"';
					break;

				case 'id':
				case 'target':
					$parts[] = $type . '=' . \esc_attr( $value );
					break;

				case 'url':
					if ( '#' !== $value )
						$parts[] = 'href="' . \esc_attr( \esc_url_raw( $value ) ) . '"';
					break;

				case 'download':
					if ( isset( $args['filename'] ) ) {
						$parts[] = 'download="' . \esc_attr( $args['filename'] ) . '"';
					} else {
						$parts[] = 'download';
					}
					unset( $args['filename'] );
					break;

				case 'data':
					foreach ( $value as $k => $v ) {
						$parts[] = sprintf( 'data-%s="%s"', \esc_attr( $k ), \esc_attr( $v ) );
					}
					break;

				default:
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
	public function get_download_link( array $args = [] ) {

		$defaults = [
			'url'      => '',
			'target'   => '_self',
			'class'    => '',
			'title'    => '',
			'content'  => '',
			'download' => true,
			'filename' => '',
			'data'     => [],
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
	protected function get_my_account_link() {
		return $this->get_link( [
			'url'     => $this->get_activation_url( 'my-account/' ),
			'target'  => '_blank',
			'class'   => '',
			'title'   => \esc_attr__( 'Go to My Account', 'the-seo-framework-extension-manager' ),
			'content' => \esc_html__( 'My Account', 'the-seo-framework-extension-manager' ),
		] );
	}

	/**
	 * Generates support link for both Public and Private.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Now goes by Private/Public
	 *
	 * @param string $type The support link type. Accepts 'privte' or anything else for public.
	 * @param bool   $icon Whether to show a heart/star after the button text.
	 * @return string The Support Link.
	 */
	public function get_support_link( $type = 'public', $icon = true ) {

		if ( 'private' === $type ) {
			$url = 'https://premium.theseoframework.com/support/';

			$title = \__( 'Get support via mail', 'the-seo-framework-extension-manager' );
			$text  = \__( 'Private Support', 'the-seo-framework-extension-manager' );

			$class  = 'tsfem-button';
			$class .= $icon ? ' tsfem-button-star' : '';
		} else {
			$url = 'https://github.com/sybrew/The-SEO-Framework-Extension-Manager/issues/new/choose';

			$title = \__( 'File an issue with us', 'the-seo-framework-extension-manager' );
			$text  = \__( 'Public Support', 'the-seo-framework-extension-manager' );

			$class  = 'tsfem-button';
			$class .= $icon ? ' tsfem-button-love' : '';
		}

		return $this->get_link( [
			'url'     => $url,
			'target'  => '_blank',
			'class'   => $class,
			'title'   => $title,
			'content' => $text,
		] );
	}

	/**
	 * Sets admin menu links so the pages can be safely used within AJAX.
	 *
	 * Does not forge a callback function, instead, the callback returns an empty string.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @param string $slug       The menu slug. Required.
	 * @param string $capability The menu's required access capability.
	 * @return bool True on success, false on failure.
	 */
	public function _set_ajax_menu_link( $slug, $capability = 'manage_options' ) {

		$slug       = \sanitize_key( $slug );
		$capability = \sanitize_key( $capability );

		if ( ! $slug || ! \current_user_can( $capability ) )
			return false;

		static $parent_set = false;
		static $set        = [];

		if ( false === $parent_set ) {
			// Set parent slug.
			\tsf()->add_menu_link();
			$parent_set = true;
		}

		if ( isset( $set[ $slug ] ) )
			return $set[ $slug ];

		// Add arbitrary menu contents to known menu slug.
		$menu = [
			'parent_slug' => \tsf()->seo_settings_page_slug,
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
	 * @NOTE Warning: Only set after valid nonce verification pass.
	 *
	 * @param bool $set If true, it registers the AJAX page.
	 * @return bool True if set, false otherwise.
	 */
	protected function ajax_is_tsf_extension_manager_page( $set = false ) {

		static $cache = false;

		return $set ? $cache = true : $cache;
	}

	/**
	 * Activates extension based on form input.
	 *
	 * @since 1.0.0
	 * @since 1.5.1 Added "already activated" tests to prevent "x was already defined" errors.
	 * @since 2.0.0 Now checks for the TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS constant.
	 *
	 * @param array $options The form/request input options.
	 * @param bool  $ajax    Whether this is an AJAX request.
	 * @return bool|string False on invalid input or on activation failure.
	 *         String on success or AJAX.
	 */
	protected function activate_extension( $options, $ajax = false ) {

		if ( empty( $options['extension'] ) )
			return false;

		$slug = \sanitize_key( $options['extension'] );

		// PHP 7 please.
		if ( \array_key_exists( $slug, (array) TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS ) ) {
			$ajax or $this->register_extension_state_change_notice( 10013, $slug );
			return $ajax ? $this->get_ajax_notice( false, 10013 ) : false;
		}
		if ( \in_array( $slug, (array) TSF_EXTENSION_MANAGER_HIDDEN_EXTENSIONS, true ) ) {
			$ajax or $this->register_extension_state_change_notice( 10014, $slug );
			return $ajax ? $this->get_ajax_notice( false, 10014 ) : false;
		}

		$this->get_verification_codes( $_instance, $bits );

		Extensions::initialize( 'activation', $_instance, $bits );
		Extensions::set_account( $this->get_subscription_status() );
		Extensions::set_instance_extension_slug( $slug );

		$checksum = Extensions::get( 'extensions_checksum' );
		$result   = $this->validate_extensions_checksum( $checksum );

		if ( true !== $result ) :
			switch ( $result ) :
				case -1:
					// No checksum found.
					$ajax or $this->set_error_notice( [ 10001 => '' ] );
					return $ajax ? $this->get_ajax_notice( false, 10001 ) : false;

				case -2:
					// Checksum mismatch.
					$ajax or $this->set_error_notice( [ 10002 => '' ] );
					return $ajax ? $this->get_ajax_notice( false, 10002 ) : false;

				default:
					// Method mismatch error. Unknown error.
					$ajax or $this->set_error_notice( [ 10003 => '' ] );
					return $ajax ? $this->get_ajax_notice( false, 10003 ) : false;
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

			// 5 means it's already activated. 4 means it passed all tests.
			if ( ! \in_array( $test, [ 4, 5 ], true ) || $this->_has_died() ) {
				$ajax or $this->set_error_notice( [ 10005 => $test ] );
				return $ajax ? $this->get_ajax_notice( false, 10005 ) : false;
			}

			// 5 means it's already activated. Enable it otherwise.
			$success = 5 === $test || $this->enable_extension( $slug );

			if ( false === $success ) {
				$ajax or $this->set_error_notice( [ 10006 => '' ] );
				return $ajax ? $this->get_ajax_notice( false, 10006 ) : false;
			}
		endif;

		switch ( $status['case'] ) :
			case 1:
				// No slug set.
				$code = 10007;
				break;

			case 2:
				// Premium/Essentials activated.
				$code = 10008;
				break;

			case 3:
				// Premium/Essentials failed: User not connected.
				$code = 10009;
				break;

			case 4:
				// Free activated.
				$code = 10010;
				break;

			case 5:
				// Was already active.
				$code = 10012;
				break;

			default:
				// Unknown case.
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
	 * @since 2.0.0 Now checks for the TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS constant.
	 *
	 * @param array $options The form input options.
	 * @param bool  $ajax Whether this is an AJAX request.
	 * @return bool False on invalid input.
	 */
	protected function deactivate_extension( $options, $ajax = false ) {

		if ( empty( $options['extension'] ) )
			return false;

		/**
		 * We don't check for its previous activation state; we just deactivate regardless.
		 * This is because we can't check (via our API) whether it was active without
		 * introducing further failure points.
		 * Users whom deactivate an extension might do so because of contraints, like PHP errors;
		 * adding additional inconsequential actions isn't beneficial for the user.
		 * Checking it after this point will result in inconsitent data.
		 */
		$slug = \sanitize_key( $options['extension'] );

		// PHP 7 please.
		if ( \array_key_exists( $slug, (array) TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS ) ) {
			$ajax or $this->register_extension_state_change_notice( 11003, $slug );
			return $ajax ? $this->get_ajax_notice( false, 11003 ) : false;
		}
		if ( \in_array( $slug, (array) TSF_EXTENSION_MANAGER_HIDDEN_EXTENSIONS, true ) ) {
			$ajax or $this->register_extension_state_change_notice( 11004, $slug );
			return $ajax ? $this->get_ajax_notice( false, 11004 ) : false;
		}

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
	protected function register_extension_state_change_notice( $code, $slug ) {
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
	 * @since 2.2.0 1. Now bypasses the WP 5.2 fatal error handler on AJAX.
	 *              2. Now clears the WP 5.2 fatal error hanlder message on non-AJAX.
	 *
	 * @param string $slug The extension slug to load.
	 * @param bool   $ajax Whether this is an AJAX request.
	 * @return int|void {
	 *    -1 : No check has been performed.
	 *    1  : No file header path can be created. (Invalid extension)
	 *    2  : Extension header file is invalid. (Invalid extension)
	 *    3  : Inclusion failed.
	 *    4  : Success.
	 *    void : Fatal error.
	 * }
	 */
	protected function test_extension( $slug, $ajax = false ) {

		if ( $ajax ) {
			\add_filter( 'wp_die_ajax_handler', [ $this, '_disable_wp_fatal_error_handler_cb' ], 9999 );
		} else {
			\add_filter( 'wp_php_error_message', [ $this, '_disable_wp_fatal_error_handler' ], 9999 );
		}

		$this->get_verification_codes( $_instance, $bits );
		Extensions::initialize( 'load', $_instance, $bits );

		$this->get_verification_codes( $_instance, $bits );
		$result = Extensions::test_extension( $slug, $ajax, $_instance, $bits );

		Extensions::reset();

		if ( $ajax ) {
			\remove_filter( 'wp_die_ajax_handler', [ $this, '_disable_wp_fatal_error_handler_cb' ], 9999 );
		} else {
			\remove_filter( 'wp_php_error_message', [ $this, '_disable_wp_fatal_error_handler' ], 9999 );
		}

		return $result;
	}

	/**
	 * Disables the WP fatal error handler callback, so ours works again; making error notices legible.
	 * This is a separated private function because we must reset it after the extension activation failed.
	 *
	 * @since 2.2.0
	 * @access private
	 *
	 * @return string
	 */
	public function _disable_wp_fatal_error_handler_cb() {
		return '__return_empty_string';
	}

	/**
	 * Disables the WP fatal error handler, so ours works again; making error notices legible.
	 * This is a separated private function because we must reset it after the extension activation failed.
	 *
	 * @since 2.2.0
	 * @access private
	 *
	 * @return void
	 */
	public function _disable_wp_fatal_error_handler() { }

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
	 * @param bool   $enable Whether to enable or disable the extension.
	 * @return bool False if extension enabling or disabling fails.
	 */
	protected function update_extension( $slug, $enable = false ) {

		$extensions = $this->get_option( 'active_extensions', [] );

		$extensions[ $slug ] = (bool) $enable;

		// Kill options on failure when enabling.
		$kill = $enable;

		return $this->update_option( 'active_extensions', $extensions, 'regular', $kill );
	}
}
