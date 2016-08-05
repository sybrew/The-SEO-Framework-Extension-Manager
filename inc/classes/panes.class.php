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
class Panes extends Core {

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Constructor. Loads parent constructor and initializes actions.
	 */
	protected function __construct() {
		parent::__construct();

		//* Ajax listener for updating feed option.
		add_action( 'wp_ajax_tsfem_enable_feeds', array( $this, 'wp_ajax_enable_feeds' ) );
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

		return sprintf( '<div class="tsfem-extensions-overview-wrap">%s</div>', $output );
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
	 *
	 * @return string.
	 */
	protected function get_feed_enabler_button() {

		$enable = __( 'Enable feed?', 'the-seo-framework-extension-manager' );

		$key = sprintf( '<input type="hidden" name="%s" value="validate-key">', esc_attr( $this->get_field_name( 'action' ) ) );
		$nonce_action = $this->get_nonce_action_field( $this->request_name['enable-feed'] );
		$nonce = wp_nonce_field( $this->nonce_action['enable-feed'], $this->nonce_name, true, false );
		$submit = sprintf( '<input type="submit" name="submit" id="submit" class="tsfem-button-primary" value="%s">', esc_attr( $enable ) );

		$form = $key . $nonce_action . $nonce . $submit;
		$nojs = sprintf( '<form action="%s" method="post" id="tsfem-enable-feeds-form" class="hide-if-js">%s</form>', esc_url( $this->get_admin_page_url() ), $form );

		$js = '<a id="tsfem-enable-feeds" class="tsfem-button-primary hide-if-no-js">' . esc_html( $enable ) . '</a>';

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
					$type = $this->update_option( '_enable_feed', true ) ? 'success' : 'error';

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
	 * Outputs actions for the premium user.
	 *
	 * @since 1.0.0
	 *
	 * @TODO Deactivation button + Subscription time.
	 * @return string The premium user actions.
	 */
	protected function get_premium_actions_output() {

		$output = '';
		$output .= $this->get_deactivation_button();

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

		$output = '';
		$output .= $this->get_deactivation_button();

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

		$header = Extensions::get( 'header' );
		$header = sprintf( '<div class="tsfem-extensions-overview-header">%s</div>', $header );

		$content = Extensions::get( 'content' );
		$content = sprintf( '<div class="tsfem-extensions-overview-header">%s</div>', $content );

		Extensions::reset();

		return $header . $content;
	}
}
