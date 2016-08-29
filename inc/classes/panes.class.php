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
 * Class TSF_Extension_Manager\Panes
 *
 * Holds plugin extensions overview functions.
 *
 * @since 1.0.0
 */
class Panes extends API {
	use Enclose, Construct_Sub;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {
		//* Ajax listener for updating feed option.
		add_action( 'wp_ajax_tsfem_enable_feeds', array( $this, 'wp_ajax_enable_feeds' ) );
		//* Ajax listener for updating extension setting.
		add_action( 'wp_ajax_tsfem_update_extension', array( $this, 'wp_ajax_tsfem_update_extension' ) );
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

		return sprintf( '<div class="tsfem-trends-wrap">%s</div>', $output );
	}

	/**
	 * Returns TSF Extension Manager account actions overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The escaped account actions overview.
	 */
	protected function get_extensions_actions_overview() {

		if ( $this->is_premium_user() ) {
			$output = $this->get_premium_actions_output();
		} else {
			$output = $this->get_free_actions_output();
		}

		return sprintf( '<div class="tsfem-actions-wrap">%s</div>', $output );
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

		return sprintf( '<div class="tsfem-extensions-wrap">%s</div>', $output );
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
			$feed_error = esc_html__( "Unfortunately, your server can't process this request as of yet.", 'the-seo-framework-extension-manager' );
			$output .= sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
		} elseif ( empty( $feed ) ) {
			$feed_error = esc_html__( 'There are no trends and updates to report yet.', 'the-seo-framework-extension-manager' );
			$output .= sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
		} else {
			$output .= sprintf( '<div class="tsfem-feed-wrap">%s</div>', $feed );
		}

		return $output;
	}

	/**
	 * Returns Google Feed.
	 *
	 * @since 1.0.0
	 * @uses TSF_Extension_Manager\Trends::get()
	 * @todo Consider loading via AJAX if transient has expired (register_shutdown_function + hide-if-js (reload...)?).
	 *
	 * @return string The sanitized Google Webmasters feed output.
	 */
	protected function get_trends_feed() {

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		return Trends::get( $_instance, $bits );
	}

	/**
	 * Returns trends activation introduction.
	 *
	 * @since 1.0.0
	 * @todo convert to secure instance.
	 *
	 * @return string Trends activation buttons.
	 */
	protected function get_trends_activation_output() {

		$output = '';

		//* The feed is totally optional until it pulls from The SEO Framework premium. I.e. privacy.
		$title = esc_html__( 'The feed has been disabled to protect your privacy.', 'the-seo-framework-extension-manager' );
		$title = sprintf( '<h4 class="tsfem-status-title">%s</h4>', $title );
		$output .= '<p>' . esc_html__( 'You may choose to enable the feed. Once enabled, it can not be disabled.', 'the-seo-framework-title-fix' ) . '</p>';
		$output .= $this->get_feed_enabler_button();

		return $title . $output;
	}

	/**
	 * Returns a button that implements an AJAX request for Feed enabling.
	 *
	 * @since 1.0.0
	 * @todo convert to secure instance.
	 *
	 * @return string.
	 */
	protected function get_feed_enabler_button() {

		$enable = __( 'Enable feed?', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->get_nonce_action_field( $this->request_name['enable-feed'] );
		$nonce = wp_nonce_field( $this->nonce_action['enable-feed'], $this->nonce_name, true, false );
		$submit = sprintf( '<input type="submit" name="submit" id="submit" class="tsfem-button tsfem-button-primary" value="%s">', esc_attr( $enable ) );
		$form = $nonce_action . $nonce . $submit;

		$nojs = sprintf( '<form action="%s" method="post" id="tsfem-enable-feeds-form" class="hide-if-js">%s</form>', esc_url( $this->get_admin_page_url() ), $form );
		$js = '<a id="tsfem-enable-feeds" class="tsfem-button tsfem-button-primary hide-if-no-js">' . esc_html( $enable ) . '</a>';

		return $js . $nojs;
	}

	/**
	 * Enables feed through AJAX and echos the feed output through AJAX response.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function wp_ajax_enable_feeds() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( $this->can_do_settings() ) {

				check_ajax_referer( 'tsfem-ajax-nonce', 'nonce' );

				if ( $this->get_option( '_enable_feed' ) ) {
					//* Another admin has initialized this after the last page load.
					$results = array(
						'content' => $this->get_trends_output(),
						'type' => 'unknown',
					);
				} else {
					$type = $this->update_option( '_enable_feed', true, 'regular', false ) ? 'success' : 'error';

					if ( 'success' === $type ) {
						$results = array(
							'content' => $this->get_trends_output(),
							'type' => $type,
						);
					} else {
						$results = array(
							'content' => '',
							'type' => $type,
						);
					}
				}

				echo json_encode( $results );

				exit;
			}
		}
	}

	/**
	 * Updates extension through AJAX and returns AJAX response.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function wp_ajax_tsfem_update_extension() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( $this->can_do_settings() ) {

				$case = '';
				$slug = '';

				if ( check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) ) {
					$data = $_POST;

					//* As data is passed to UNIX/IIS for file existence, strip as much as possible.
					$slug = isset( $data['slug'] ) ? $this->s_ajax_string( $data['slug'] ) : '';
					$case = isset( $data['case'] ) ? $this->s_ajax_string( $data['case'] ) : '';
				}

				if ( $case && $slug ) {
					$options = array(
						'extension' => $slug,
					);

					if ( 'activate' === $case )
						$status = $this->activate_extension( $options, true );
					elseif ( 'deactivate' === $case )
						$status = $this->deactivate_extension( $options, true );
				} else {
					$status = array(
						'success' => -1,
						'notice' => esc_html__( 'Something went wrong. Please reload the page.', 'the-seo-framework-extension-manager' ),
					);
				}

				//* Send back input when WP_DEBUG is on.
				$response = WP_DEBUG ? array( 'status' => $status, 'slug' => $slug, 'case' => $case ) : array( 'status' => $status );

				echo json_encode( $response );

				exit;
			}
		}
	}

	/**
	 * Outputs actions for the premium user.
	 *
	 * @since 1.0.0
	 *
	 * @TODO Deactivation button + Subscription time.
	 * @return string The premium user actions.
	 */
	protected function get_premium_actions_output() {

		$output = '<h4>This is still under construction.</h4>';
		$output .= $this->get_deactivation_button();
		$output .= $this->get_support_buttons();

		return $output;
	}

	/**
	 * Outputs actions for the free user.
	 *
	 * @since 1.0.0
	 *
	 * TODO: Basically the activation page, but without free and with deactivation.
	 * @return string The free user actions.
	 */
	protected function get_free_actions_output() {

		$output = '<h4>This is still under construction.</h4>';
		$output .= $this->get_deactivation_button();
		$output .= $this->get_support_buttons();

		return $output;
	}

	/**
	 * Renders and returns deactivation button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The deactivation button.
	 */
	protected function get_deactivation_button() {

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Layout::initialize( 'form', $_instance, $bits );

		Layout::set_nonces( 'nonce_name', $this->nonce_name );
		Layout::set_nonces( 'request_name', $this->request_name );
		Layout::set_nonces( 'nonce_action', $this->nonce_action );

		$button = Layout::get( 'deactivation-button' );

		Layout::reset();

		return $button;
	}

	/**
	 * Renders and returns support buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return string The support buttons.
	 */
	protected function get_support_buttons() {

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Layout::initialize( 'link', $_instance, $bits );

		$buttons = '';

		$buttons = Layout::get( 'free-support-button' );
		if ( $this->is_premium_user() )
			$buttons .= Layout::get( 'premium-support-button' );

		Layout::reset();

		return $buttons;
	}

	/**
	 * Outputs the extensions to be activated.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions overview.
	 */
	protected function get_extensions_output() {

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		Extensions::initialize( 'overview', $_instance, $bits );

		Extensions::set_nonces( 'nonce_name', $this->nonce_name );
		Extensions::set_nonces( 'request_name', $this->request_name );
		Extensions::set_nonces( 'nonce_action', $this->nonce_action );

		Extensions::set_account( $this->get_subscription_status() );

		$header = Extensions::get( 'layout_header' );
		$header = sprintf( '<div class="tsfem-extensions-overview-header">%s</div>', $header );

		$content = Extensions::get( 'layout_content' );
		$content = sprintf( '<div class="tsfem-extensions-overview-content tsfem-flex tsfem-flex-row">%s</div>', $content );

		Extensions::reset();

		return $header . $content;
	}
}
