<?php
/**
 * @package TSF_Extension_Manager\Classes
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
 * Class TSF_Extension_Manager\Panes
 *
 * Holds plugin extensions overview functions.
 *
 * @since 1.0.0
 * @access private
 */
class Panes extends API {
	use Enclose_Stray_Private, Construct_Child_Interface;

	/**
	 * Constructor, initializes WordPress Admin actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Ajax listener for updating feed option.
		\add_action( 'wp_ajax_tsfem_enable_feeds', array( $this, '_wp_ajax_enable_feeds' ) );

		//* Ajax listener for updating extension setting.
		\add_action( 'wp_ajax_tsfem_update_extension', array( $this, '_wp_ajax_tsfem_update_extension' ) );

		//* Ajax listener for after updating the extension setting.
		\add_action( 'wp_ajax_tsfem_update_extension_desc_footer', array( $this, '_wp_ajax_tsfem_update_extension_desc_footer' ) );
	}

	/**
	 * Returns the SEO trends and updates overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The escaped SEO Trends and Updates overview.
	 */
	protected function get_seo_trends_and_updates_overview() {

		$output = '';

		$feed_enabled = $this->get_option( '_enable_feed', false );

		if ( $feed_enabled ) {
			$output = $this->get_trends_output();
		} else {
			$output = $this->get_trends_activation_output();
		}

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-trends-wrap tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Returns TSF Extension Manager account actions overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The escaped account actions overview.
	 */
	protected function get_extensions_actions_overview() {

		$output = $this->get_actions_output();

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-actions-wrap tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Returns the extension overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions overview.
	 */
	protected function get_extension_overview() {

		$output = $this->get_extensions_output();

		return sprintf( '<div class="tsfem-pane-inner-wrap tsfem-extensions-wrap tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Returns wrapped Google Feed with status notices.
	 *
	 * @since 1.0.0
	 *
	 * @return string The wrapped Google Webmasters feed output.
	 */
	protected function get_trends_output() {

		$feed = $this->get_trends_feed();

		$output = '';

		if ( -1 === $feed ) {
			$feed_error = \esc_html__( "Unfortunately, your server can't process this request as of yet.", 'the-seo-framework-extension-manager' );
			$output .= sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
		} elseif ( empty( $feed ) ) {
			$feed_error = \esc_html__( 'There are no trends and updates to report yet.', 'the-seo-framework-extension-manager' );
			$output .= sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
		} else {
			$output .= sprintf( '<div class="tsfem-feed-wrap tsfem-flex tsfem-flex-row">%s</div>', $feed );
		}

		return sprintf( '<div class="tsfem-trends tsfem-ltr tsfem-flex tsfem-flex-row">%s</div>', $output );
	}

	/**
	 * Returns wrapped Google Feed with status notices.
	 *
	 * @since 1.0.0
	 *
	 * @param bool @ajax Whether the return value is queried through AJAX.
	 * @return array {
	 *    'data' => array The feed items.
	 *    'wrap' => string The feed items wrap.
	 *    'error_output' => string If feed has an error, this is sent too.
	 * }
	 */
	protected function ajax_get_trends_output() {

		$data = $this->get_trends_feed( true );

		if ( -1 === $data ) {
			$feed_error = \esc_html__( "Unfortunately, your server can't process this request as of yet.", 'the-seo-framework-extension-manager' );
			$output = sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
			$error_output = sprintf( '<div class="tsfem-trends tsfem-ltr tsfem-flex tsfem-flex-row">%s</div>', $output );
			$status = 'parse_error';
		} elseif ( empty( $data ) ) {
			$feed_error = \esc_html__( 'There are no trends and updates to report yet.', 'the-seo-framework-extension-manager' );
			$output = sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
			$error_output = sprintf( '<div class="tsfem-trends tsfem-ltr tsfem-flex tsfem-flex-row">%s</div>', $output );
			$status = 'unknown_error';
		} else {
			$status = 'success';
			$wrap = '<div class="tsfem-trends tsfem-ltr tsfem-flex tsfem-flex-row"><div class="tsfem-feed-wrap tsfem-flex tsfem-flex-row"></div></div>';
		}

		return compact( 'status', 'error_output', 'data', 'wrap' );
	}

	/**
	 * Returns Google Webmaster's blog Feed.
	 *
	 * @since 1.0.0
	 * @uses TSF_Extension_Manager\Trends::get()
	 * @todo Consider loading via AJAX if transient has expired (set transient ->
	 * auto-load through ajax (calculate difference with local UNIX time -> might error?) & delete transient -> if failed run anyway).
	 *
	 * @param bool $ajax Whether the trends are fetched through AJAX.
	 * @return string The sanitized Google Webmasters feed output.
	 */
	protected function get_trends_feed( $ajax = false ) {

		$this->get_verification_codes( $_instance, $bits );

		if ( $ajax ) {
			return \TSF_Extension_Manager\Trends::get( 'ajax_feed', $_instance, $bits );
		}

		return \TSF_Extension_Manager\Trends::get( 'feed', $_instance, $bits );
	}

	/**
	 * Returns trends activation introduction.
	 *
	 * @since 1.0.0
	 * @todo convert to secure Trends instance? Trends isn't "super-secure" (no bit verification).
	 *       Do so when converting to own blog entries (auto-enabled?). Relucatance towards backwards compat.
	 *
	 * @return string Trends activation buttons.
	 */
	protected function get_trends_activation_output() {

		$output = '';

		$googleblog = sprintf( '<a href="%s" target="_blank">Google Webmaster Central Blog</a>', \esc_url( 'https://webmasters.googleblog.com/' ) );
		$acquiredfrom = sprintf( \esc_html__( 'The feed is acquired from %s.', 'the-seo-framework-extension-manager' ), $googleblog );
		$googleprivacy = sprintf( '<a href="%s" target="_blank">Google\'s Privacy Policy</a>', \esc_url( 'https://www.google.com/policies/privacy/' ) );
		/* translators: %s = "Google's Privacy Policy" */
		$privacystatement = sprintf( \esc_html__( 'Read %s.', 'the-seo-framework-extension-manager' ), $googleprivacy );

		//* The feed is totally optional until it pulls from The SEO Framework premium. I.e. privacy.
		$title = \esc_html__( 'The feed has been disabled to protect your privacy.', 'the-seo-framework-extension-manager' );
		$title = sprintf( '<h4 class="tsfem-status-title">%s</h4>', $title );
		$output .= '<p>' . \esc_html__( 'You may choose to enable the feed. Once enabled, it can not be disabled.', 'the-seo-framework-extension-manager' ) . '</p>';
		$output .= '<p>' . $acquiredfrom . ' ' . $privacystatement . '</p>';
		$output .= $this->get_feed_enabler_button();

		return sprintf( '<div class="tsfem-trends-activation">%s</div>', $title . $output );
	}

	/**
	 * Returns a button that implements an AJAX request for Feed enabling.
	 *
	 * @since 1.0.0
	 * @todo @see $this->get_trends_activation_output().
	 *
	 * @return string.
	 */
	protected function get_feed_enabler_button() {

		$enable = \__( 'Enable feed?', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( $this->request_name['enable-feed'] );
		$nonce = \wp_nonce_field( $this->nonce_action['enable-feed'], $this->nonce_name, true, false );
		$submit = sprintf( '<input type="submit" name="submit" id="submit" class="tsfem-button tsfem-button-primary" value="%s">', \esc_attr( $enable ) );
		$form = $nonce_action . $nonce . $submit;

		$nojs = sprintf( '<form action="%s" method="post" id="tsfem-enable-feeds-form" class="hide-if-js">%s</form>', \esc_url( $this->get_admin_page_url() ), $form );
		$js = '<a id="tsfem-enable-feeds" class="tsfem-button tsfem-button-primary hide-if-no-js">' . \esc_html( $enable ) . '</a>';

		return sprintf( '<div class="tsfem-flex tsfem-flex-no-wrap tsfem-enable-feed-button">%s</div>', $js . $nojs );
	}

	/**
	 * Enables feed through AJAX and echos the feed output through AJAX response.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _wp_ajax_enable_feeds() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :

				\check_ajax_referer( 'tsfem-ajax-nonce', 'nonce' );

				$data = '';
				$type = 'unknown';

				if ( $this->get_option( '_enable_feed' ) ) {
					//* Another admin has initialized this after the last page load.
					$type = 'success';
					$data = array(
						'content' => $this->ajax_get_trends_output(),
						'type' => $type,
					);
				} else {
					$type = $this->update_option( '_enable_feed', true, 'regular', false ) ? 'success' : 'error';

					if ( 'success' === $type ) {
						$data = array(
							'content' => $this->ajax_get_trends_output(),
							'type' => $type,
						);
					} else {
						$data = array(
							'content' => '',
							'type' => $type,
						);
					}
				}

				$this->send_json( $data, $type );
			endif;
		endif;

		exit;
	}

	/**
	 * Updates extension through AJAX and returns AJAX response.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _wp_ajax_tsfem_update_extension() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :

				$case = '';
				$slug = '';

				if ( \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) ) {
					$data = $_POST;

					//* As data is passed to UNIX/IIS for file existence, strip as much as possible.
					$slug = isset( $data['slug'] ) ? $this->s_ajax_string( $data['slug'] ) : '';
					$case = isset( $data['case'] ) ? $this->s_ajax_string( $data['case'] ) : '';
				}

				if ( $case && $slug ) {
					$options = array(
						'extension' => $slug,
					);

					if ( 'activate' === $case ) {
						$status = $this->activate_extension( $options, true );
					} elseif ( 'deactivate' === $case ) {
						$status = $this->deactivate_extension( $options, true );
					}
				} else {
					$status = array(
						'success' => -1,
						'notice' => \esc_html__( 'Something went wrong. Please reload the page.', 'the-seo-framework-extension-manager' ),
					);
				}

				$type = ! empty( $status['success'] ) ? 'success' : 'error';

				//* Send back input when WP_DEBUG is on.
				$response = WP_DEBUG ? array( 'status' => $status, 'slug' => $slug, 'case' => $case ) : array( 'status' => $status );

				$this->send_json( $response, $type );
			endif;
		endif;

		exit;
	}

	/**
	 * Returns updated footer fields for the activated extension.
	 *
	 * Generates a rogue menu entry item.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _wp_ajax_tsfem_update_extension_desc_footer() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :

				$slug = '';
				$case = '';

				if ( \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) ) {
					$data = $_POST;

					//* As data is passed to UNIX/IIS for file existence, strip as much as possible.
					$slug = isset( $data['slug'] ) ? $this->s_ajax_string( $data['slug'] ) : '';
					$case = isset( $data['case'] ) ? $this->s_ajax_string( $data['case'] ) : '';
				}

				if ( $slug && $case ) :
					//* Tell the plugin we're on the correct page.
					$this->ajax_is_tsf_extension_manager_page( true );

					$this->get_verification_codes( $_instance, $bits );

					\TSF_Extension_Manager\Extensions::initialize( 'ajax_layout', $_instance, $bits );

					if ( 'activate' === $case ) :
						//* Check for menu slug in order to add it.
						$header = \TSF_Extension_Manager\Extensions::get( 'ajax_get_extension_header', $slug );

						if ( ! empty( $header['MenuSlug'] ) ) {
							$this->_set_ajax_menu_link( $header['MenuSlug'] );
						}
					endif;

					$html = \TSF_Extension_Manager\Extensions::get( 'ajax_get_extension_desc_footer', $slug );

					\TSF_Extension_Manager\Extensions::reset();
				endif;

				if ( isset( $html ) ) {
					$data = $html;
					$type = 'success';
				} else {
					$data = '';
					$type = 'error';
				}

				$this->send_json( $data, $type );
			endif;
		endif;

		exit;
	}

	/**
	 * Renders and returns actions pane output content.
	 *
	 * @since 1.0.0
	 *
	 * @return string The actions pane output.
	 */
	protected function get_actions_output() {

		$left = $this->get_actions_left_output();
		$right = $this->get_actions_right_output();

		return sprintf( '<div class="tsfem-actions tsfem-flex tsfem-flex-row">%s</div>', $left . $right );
	}

	/**
	 * Wraps and outputs the left side of the Actions pane.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Actions pane left side output.
	 */
	protected function get_actions_left_output() {

		$output = '';

		$output .= $this->get_support_buttons();

		return sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $output );
	}

	/**
	 * Wraps and outputs the right side of the Actions pane.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Actions pane right side output.
	 */
	protected function get_actions_right_output() {

		$output = '';

		if ( $this->is_premium_user() && $this->are_options_valid() ) {
			$output .= $this->get_account_information();
			//* TODO make this happen (on request/modal?).
			//	$output .= $this->get_account_extend_form();
		} else {
			$output .= $this->get_account_upgrade_form();
		}

		$output .= $this->get_deactivation_button();

		return sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $output );
	}

	/**
	 * Wraps and returns the account information.
	 *
	 * @since 1.0.0
	 *
	 * @return string The account information wrap.
	 */
	protected function get_account_information() {

		$this->get_verification_codes( $_instance, $bits );

		\TSF_Extension_Manager\Layout::initialize( 'list', $_instance, $bits );

		\TSF_Extension_Manager\Layout::set_nonces( 'nonce_name', $this->nonce_name );
		\TSF_Extension_Manager\Layout::set_nonces( 'request_name', $this->request_name );
		\TSF_Extension_Manager\Layout::set_nonces( 'nonce_action', $this->nonce_action );

		\TSF_Extension_Manager\Layout::set_account( $this->get_subscription_status() );

		$output = \TSF_Extension_Manager\Layout::get( 'account-information' );

		\TSF_Extension_Manager\Layout::reset();

		$title = sprintf( '<h4 class="tsfem-info-title">%s</h4>', \esc_html__( 'Account information', 'the-seo-framework-extension-manager' ) );

		return sprintf( '<div class="tsfem-account-info">%s%s</div>', $title, $output );
	}

	/**
	 * @TODO make this happen.
	 */
	protected function get_account_extend_form() { }

	/**
	 * Wraps and returns the account upgrade form.
	 *
	 * @since 1.0.0
	 *
	 * @return string The account upgrade form wrap.
	 */
	protected function get_account_upgrade_form() {

		$this->get_verification_codes( $_instance, $bits );

		\TSF_Extension_Manager\Layout::initialize( 'form', $_instance, $bits );

		\TSF_Extension_Manager\Layout::set_account( $this->get_subscription_status() );

		\TSF_Extension_Manager\Layout::set_nonces( 'nonce_name', $this->nonce_name );
		\TSF_Extension_Manager\Layout::set_nonces( 'request_name', $this->request_name );
		\TSF_Extension_Manager\Layout::set_nonces( 'nonce_action', $this->nonce_action );

		$form = \TSF_Extension_Manager\Layout::get( 'account-upgrade' );

		\TSF_Extension_Manager\Layout::reset();

		$title = sprintf( '<h4 class="tsfem-form-title">%s</h4>', \esc_html__( 'Upgrade your account', 'the-seo-framework-extension-manager' ) );

		return sprintf( '<div class="tsfem-account-upgrade">%s%s</div>', $title, $form );
	}

	/**
	 * Renders and returns deactivation button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The deactivation button.
	 */
	protected function get_deactivation_button() {

		$this->get_verification_codes( $_instance, $bits );

		\TSF_Extension_Manager\Layout::initialize( 'form', $_instance, $bits );

		\TSF_Extension_Manager\Layout::set_nonces( 'nonce_name', $this->nonce_name );
		\TSF_Extension_Manager\Layout::set_nonces( 'request_name', $this->request_name );
		\TSF_Extension_Manager\Layout::set_nonces( 'nonce_action', $this->nonce_action );

		$button = \TSF_Extension_Manager\Layout::get( 'deactivation-button' );

		\TSF_Extension_Manager\Layout::reset();

		$title = sprintf( '<h4 class="tsfem-info-title">%s</h4>', \esc_html__( 'Deactivate account', 'the-seo-framework-extension-manager' ) );
		$content = \esc_html__( 'This also deactivates all extensions.', 'the-seo-framework-extension-manager' );

		$extras = array();
		$_extra = '';

		$extras[] = \esc_html__( 'This will deactivate all extensions. All extension options are held intact.', 'the-seo-framework-extension-manager' );
		$extras[] = $this->is_premium_user() ? \esc_html__( 'Your key can be used on another website after deactivation.', 'the-seo-framework-extension-manager' ) : '';

		foreach ( $extras as $extra ) {
			if ( $extra )
				$_extra .= sprintf( '<div class="tsfem-description">%s</div>', $extra );
		}

		$extra = sprintf( '<div class="tsfem-flex tsfem-flex-nowrap">%s</div>', $_extra );

		return sprintf( '<div class="tsfem-account-deactivate">%s%s%s</div>', $title, $button, $extra );
	}

	/**
	 * Renders and returns support buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return string The support buttons.
	 */
	protected function get_support_buttons() {

		$this->get_verification_codes( $_instance, $bits );

		\TSF_Extension_Manager\Layout::initialize( 'link', $_instance, $bits );

		$buttons = array();
		$description = array();

		$buttons[1] = \TSF_Extension_Manager\Layout::get( 'free-support-button' );
		$description[1] = \__( 'Questions about all free extensions and using the Extension Manager can be asked through Free Support.', 'the-seo-framework-extension-manager' );

		$buttons[2] = \TSF_Extension_Manager\Layout::get( 'premium-support-button' );
		$description[2] = \__( 'Any question about a premium extensions or your account should be asked through Premium Support.', 'the-seo-framework-extension-manager' );

		\TSF_Extension_Manager\Layout::reset();

		$title = sprintf( '<h4 class="tsfem-support-title">%s</h4>', \esc_html__( 'Get support', 'the-seo-framework-extension-manager' ) );

		$content = '';
		foreach ( $buttons as $key => $button ) {
			$extra = sprintf( '<span class="tsfem-description">%s</span>', \esc_html( $description[ $key ] ) );
			$content .= sprintf( '<div class="tsfem-support-buttons tsfem-flex tsfem-flex-nogrow tsfem-flex-nowrap">%s%s</div>', $button, $extra );
		}

		return sprintf( '<div class="tsfem-account-support">%s%s</div>', $title, $content );
	}

	/**
	 * Outputs the extensions to be activated.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions overview.
	 */
	protected function get_extensions_output() {

		$this->get_verification_codes( $_instance, $bits );

		\TSF_Extension_Manager\Extensions::initialize( 'overview', $_instance, $bits );

		\TSF_Extension_Manager\Extensions::set_nonces( 'nonce_name', $this->nonce_name );
		\TSF_Extension_Manager\Extensions::set_nonces( 'request_name', $this->request_name );
		\TSF_Extension_Manager\Extensions::set_nonces( 'nonce_action', $this->nonce_action );

		\TSF_Extension_Manager\Extensions::set_account( $this->get_subscription_status() );

		$content = \TSF_Extension_Manager\Extensions::get( 'layout_content' );
		$content = sprintf( '<div class="tsfem-extensions-overview-content tsfem-flex tsfem-flex-row tsfem-flex-space">%s</div>', $content );

		\TSF_Extension_Manager\Extensions::reset();

		return $content;
	}
}
