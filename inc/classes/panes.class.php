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
 * Class TSF_Extension_Manager\Panes
 *
 * Holds plugin extensions overview functions.
 *
 * @since 1.0.0
 * @access private
 */
class Panes extends API {
	use Construct_Child_Interface;

	/**
	 * Constructor, initializes WordPress Admin actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		// Ajax listener for updating extension setting.
		\add_action( 'wp_ajax_tsfem_update_extension', [ $this, '_wp_ajax_tsfem_update_extension' ] );

		// Ajax listener for after updating the extension setting.
		\add_action( 'wp_ajax_tsfem_update_extension_desc_footer', [ $this, '_wp_ajax_tsfem_update_extension_desc_footer' ] );
	}

	/**
	 * Returns TSF Extension Manager account actions overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The escaped account actions overview.
	 */
	protected function get_extensions_actions_overview() {
		return sprintf(
			'<div class="tsfem-pane-inner-wrap tsfem-actions-wrap">%s</div>',
			vsprintf(
				'<div class="tsfem-actions tsfem-flex">%s%s%s%s%s</div>',
				[
					$this->get_account_information(),
					$this->get_transfer_domain_form(),
					$this->get_account_upgrade_form(),
					$this->get_support_buttons(),
					$this->get_disconnect_button(),
				]
			)
		);
	}

	/**
	 * Returns the extension overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions overview.
	 */
	protected function get_extension_overview() {

		$this->get_verification_codes( $_instance, $bits );

		Extensions::initialize( 'overview', $_instance, $bits );

		Extensions::set_nonces( 'nonce_name', $this->nonce_name );
		Extensions::set_nonces( 'request_name', $this->request_name );
		Extensions::set_nonces( 'nonce_action', $this->nonce_action );

		Extensions::set_account( $this->get_subscription_status() );

		$content = Extensions::get( 'layout_content' );
		$content = sprintf( '<div class=tsfem-extensions-overview-content>%s</div>', $content );

		Extensions::reset();

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-extensions-wrap">%s</div>', $content );
	}

	/**
	 * Updates extension through AJAX and returns AJAX response.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now uses the updated AJAX handler.
	 * @since 2.0.0 Now uses \TSF_Extension_Manager\can_do_manager_settings()
	 * @access private
	 */
	public function _wp_ajax_tsfem_update_extension() {

		if (
			   ! \TSF_Extension_Manager\can_do_manager_settings()
			|| \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false )
		) {
			\wp_send_json_error( [ 'notice' => $this->get_ajax_notice( false, 10102 ) ] );
		}

		$send = [];

		// As data is passed to UNIX/IIS for file existence, strip as much as possible.
		$slug = $this->s_ajax_string( $_POST['slug'] ?? '' );
		$case = $this->s_ajax_string( $_POST['case'] ?? '' );

		$send['slug'] = $slug;
		$send['case'] = $case;

		switch ( $slug ? $case : false ) {
			case 'activate':
				// Always send success due to asynchronicity (another tab could already have activated).
				$send['notice']  = $this->activate_extension( [ 'extension' => $slug ], true ) ?: null;
				$send['success'] = true;
				break;
			case 'deactivate':
				// Always send success due to asynchronicity (another tab could already have deactivated).
				$send['notice']  = $this->deactivate_extension( [ 'extension' => $slug ], true ) ?: null;
				$send['success'] = true;
				break;
			default:
				$send['notice'] = $this->get_ajax_notice( false, 10101 );
		}

		$send['success'] ??= false;

		\wp_send_json( $send );
	}

	/**
	 * Returns updated footer fields for the activated extension.
	 *
	 * Generates a rogue menu entry item.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Now uses \TSF_Extension_Manager\can_do_manager_settings()
	 * @access private
	 */
	final public function _wp_ajax_tsfem_update_extension_desc_footer() {

		if (
			   ! \TSF_Extension_Manager\can_do_manager_settings()
			|| \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false )
		) {
			\wp_send_json_error();
		}

		$send = [];

		// As data is passed to UNIX/IIS for file existence, strip as much as possible.
		$slug = $this->s_ajax_string( $_POST['slug'] ?? '' );
		$case = $this->s_ajax_string( $_POST['case'] ?? '' );

		if ( $slug && $case ) {
			// Tell the plugin we're on the correct page. We require this to conditionally load certain traits.
			$this->ajax_is_tsf_extension_manager_page( true ); // TODO figure how to comment me and clean is_tsf_extension_manager_page

			$this->get_verification_codes( $_instance, $bits );

			Extensions::initialize( 'ajax_layout', $_instance, $bits );

			// Check for menu slug in extension header, so we can add a link to the footer.
			if ( 'activate' === $case ) {
				$header = Extensions::get( 'ajax_get_extension_header', $slug );

				if ( ! empty( $header['MenuSlug'] ) ) {
					$menu_slug = \sanitize_key( $header['MenuSlug'] );

					if ( $menu_slug && \current_user_can( \TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE ) ) {
						// (untested) If the extension is already activated, these will add duplicated menu callbacks:
						// one from the plugin/extension, one from this. This is inconsequential.

						// Register parent slug.
						\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
							? \tsf()->admin()->menu()->register_top_menu_page()
							: \tsf()->add_menu_link();

						// Add arbitrary menu contents to known menu slug.
						\add_submenu_page(
							\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
								? \tsf()->admin()->menu()->get_top_menu_args()['menu_slug'] // parent_slug
								: \tsf()->seo_settings_page_slug,
							'1', // page_title
							'1', // menu_title
							\TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE, // capability
							$menu_slug, // slug
							'__return_empty_string', // callback
						);
					}
				}
			}

			$send['html']    = Extensions::get( 'ajax_get_extension_desc_footer', $slug );
			$send['success'] = true;

			Extensions::reset();
		}

		$send['success'] ??= false;

		\wp_send_json( $send );
	}

	/**
	 * Wraps and returns the account information.
	 *
	 * @since 1.0.0
	 *
	 * @return string The account information wrap.
	 */
	protected function get_account_information() {

		$infos = [];

		if ( $this->is_connected_user() )
			$infos[] = \esc_html__( 'This information is updated every few minutes, infrequently.', 'the-seo-framework-extension-manager' );

		$title = sprintf(
			'<h4 class=tsfem-info-title>%s %s</h4>',
			\esc_html__( 'Account information', 'the-seo-framework-extension-manager' ),
			( $infos
				? HTML::make_inline_question_tooltip( implode( ' ', $infos ), implode( '<br>', $infos ) )
				: ''
			)
		);

		$this->get_verification_codes( $_instance, $bits );

		Layout::initialize( 'list', $_instance, $bits );

		Layout::set_nonces( 'nonce_name', $this->nonce_name );
		Layout::set_nonces( 'request_name', $this->request_name );
		Layout::set_nonces( 'nonce_action', $this->nonce_action );

		$options_instance = $this->get_options_instance_key();
		$options_valid    = $this->are_options_valid();
		$options_hash     = substr( \get_option( "tsfem_i_$options_instance" ), -4 );

		Layout::set_account( $this->get_subscription_status() );
		Layout::set_misc( [
			'options' => [
				'valid'    => $options_valid,
				'instance' => substr( $options_instance, -4 ),
				'hash'     => [
					'expected' => $options_hash,
					'actual'   => $options_valid
						? $options_hash
						: substr( $this->hash_options( \get_option( \TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] ) ), -4 ),
				],
			],
		] );

		$output = Layout::get( 'account-information' );

		Layout::reset();

		return sprintf( '<div class="tsfem-account-info tsfem-pane-section">%s%s</div>', $title, $output );
	}

	/**
	 * @TODO make this happen.
	 */
	// phpcs:ignore
	// protected function get_account_extend_form() { }

	/**
	 * Wraps and returns the account upgrade form.
	 *
	 * @since 1.0.0
	 *
	 * @return string The account upgrade form wrap.
	 */
	protected function get_account_upgrade_form() {

		if ( $this->is_auto_activated() ) return '';
		if ( $this->is_connected_user() || ! $this->are_options_valid() ) return '';

		$this->get_verification_codes( $_instance, $bits );

		Layout::initialize( 'form', $_instance, $bits );

		Layout::set_account( $this->get_subscription_status() );

		Layout::set_nonces( 'nonce_name', $this->nonce_name );
		Layout::set_nonces( 'request_name', $this->request_name );
		Layout::set_nonces( 'nonce_action', $this->nonce_action );

		$form = Layout::get( 'account-upgrade' );

		Layout::reset();

		$title = sprintf( '<h4 class=tsfem-form-title>%s</h4>', \esc_html__( 'Upgrade your account', 'the-seo-framework-extension-manager' ) );

		return sprintf( '<div class="tsfem-cp-buttons tsfem-cp-buttons tsfem-pane-section">%s%s</div>', $title, $form );
	}

	/**
	 * Wraps and returns the domain transfer form.
	 *
	 * @since 2.6.1
	 *
	 * @return string The account upgrade form wrap.
	 */
	protected function get_transfer_domain_form() {

		if ( ! $this->get_option( '_requires_domain_transfer' )
		  || ! $this->is_connected_user()
		  || ! $this->are_options_valid()
		) return '';

		$title = sprintf(
			'<h4 class=tsfem-form-title>%s</h4>',
			\esc_html__( 'Transfer domain', 'the-seo-framework-extension-manager' )
		);

		$this->get_verification_codes( $_instance, $bits );
		Layout::initialize( 'form', $_instance, $bits );
		Layout::set_account( $this->get_subscription_status() );
		Layout::set_nonces( 'nonce_name', $this->nonce_name );
		Layout::set_nonces( 'request_name', $this->request_name );
		Layout::set_nonces( 'nonce_action', $this->nonce_action );
		$form = Layout::get( 'transfer-domain-button' );
		Layout::reset();

		return sprintf(
			'<div class="tsfem-domain-transfer tsfem-pane-section tsfem-cp-buttons">%s%s</div>',
			$title,
			$form
		);
	}

	/**
	 * Renders and returns disconnect button.
	 *
	 * @since 1.5.0
	 *
	 * @return string The disconnect button.
	 */
	protected function get_disconnect_button() {

		if ( $this->is_auto_activated() ) return '';

		$infos = [];

		if ( $this->is_connected_user() ) {
			$infos[] = \esc_html__( 'This will free up your site limit.', 'the-seo-framework-extension-manager' );
		}
		$infos[] = \esc_html__( 'Extension options will not be deleted.', 'the-seo-framework-extension-manager' );

		$title = sprintf(
			'<h4 class=tsfem-info-title>%s %s</h4>',
			\esc_html__( 'Disconnect account', 'the-seo-framework-extension-manager' ),
			HTML::make_inline_question_tooltip( implode( ' ', $infos ), implode( '<br>', $infos ) )
		);

		$this->get_verification_codes( $_instance, $bits );
		Layout::initialize( 'form', $_instance, $bits );
		Layout::set_nonces( 'nonce_name', $this->nonce_name );
		Layout::set_nonces( 'request_name', $this->request_name );
		Layout::set_nonces( 'nonce_action', $this->nonce_action );
		$button = Layout::get( 'disconnect-button' );
		Layout::reset();

		return sprintf(
			'<div class="tsfem-account-disconnect tsfem-pane-section">%s%s</div>',
			$title,
			$button
		);
	}

	/**
	 * Renders and returns support buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return string The support buttons.
	 */
	protected function get_support_buttons() {

		$title   = '';
		$content = '';

		if ( 'wcm' === $this->get_api_endpoint_type() ) {
			$title = sprintf(
				'<h4 class=tsfem-support-title>%s %s</h4>',
				\esc_html__( 'Your WooCommerce.com subscription', 'the-seo-framework-extension-manager' ),
				HTML::make_inline_question_tooltip( \__( 'Get support for The SEO Framework and manage your subscription via WooCommerce.com.', 'the-seo-framework-extension-manager' ) )
			);

			$buttons = [
				$this->get_support_link( 'wcm' ),
				$this->get_support_link( 'wcm-manage' ),
			];

			foreach ( $buttons as $button )
				$content .= sprintf( '<div class=tsfem-cp-buttons>%s</div>', $button );
		} else {
			$buttons = [
				[
					'button' => $this->get_support_link( 'public' ),
					'tt'     => \__( 'Inquire your question publicly so more people will benefit from our support.', 'the-seo-framework-extension-manager' ),
				],
				[
					'button' => $this->get_support_link( 'private' ),
					'tt'     => \__( 'Questions about your account should be inquired via Private Support.', 'the-seo-framework-extension-manager' ),
				],
			];
			foreach ( $buttons as $button ) {
				$content .= sprintf(
					'<div class=tsfem-cp-buttons>%s %s</div>',
					$button['button'],
					HTML::make_inline_question_tooltip( $button['tt'] )
				);
			}
		}

		return sprintf(
			'<div class="tsfem-account-support tsfem-pane-section">%s%s</div>',
			$title,
			$content
		);
	}
}
