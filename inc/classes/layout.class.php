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
 * Require time factory trait.
 *
 * @since 2.5.2
 */
_load_trait( 'factory/time' );

/**
 * Class TSF_Extension_Manager\Layout.
 *
 * Outputs layout based on instance.
 *
 * @since 1.0.0
 * @access private
 *         You'll need to invoke the TSF_Extension_Manager\Core verification handler. Which is impossible.
 * @final
 */
final class Layout extends Secure_Abstract {
	use Time;

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type     Required. The instance type.
	 * @param string $instance Required. The verification instance key. Passed by reference.
	 * @param array  $bits     Required. The verification instance bits. Passed by reference.
	 */
	public static function initialize( $type, &$instance = '', &$bits = [] ) {

		self::reset();

		if ( ! $type ) {
			\tsf()->_doing_it_wrong( __METHOD__, 'You must specify an initialization type.' );
		} else {

			self::set( '_wpaction' );

			switch ( $type ) :
				case 'form':
				case 'link':
				case 'list':
					\tsfem()->_verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', $type );
					break;

				default:
					self::reset();
					self::invoke_invalid_type( __METHOD__ );
					break;
			endswitch;
		}
	}

	/**
	 * Returns the layout call.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Required. Determines what to get.
	 * @return string|bool|void
	 */
	public static function get( $type ) {

		if ( ! self::verify_instance() ) return;

		if ( ! $type ) {
			\tsf()->_doing_it_wrong( __METHOD__, 'You must specify an get type.' );
			return false;
		}

		switch ( $type ) :
			case 'disconnect-button':
				return static::get_disconnect_button();

			case 'public-support-button':
				return static::get_public_support_button();

			case 'private-support-button':
				return static::get_private_support_button();

			case 'account-information':
				return static::get_account_info();

			case 'account-upgrade':
				return static::get_account_upgrade_form();

			default:
				\tsf()->_doing_it_wrong( __METHOD__, 'You must specify a correct get type.' );
				break;
		endswitch;

		return false;
	}

	/**
	 * Outputs disconnect button.
	 *
	 * @since 1.5.0
	 *
	 * @return string The disconnect button.
	 */
	private static function get_disconnect_button() {

		$output = '';

		if ( 'form' === self::get_property( '_type' ) ) {
			$tsfem = \tsfem();

			$nonce_action = $tsfem->_get_nonce_action_field( self::$request_name['deactivate'] );
			$nonce        = \wp_nonce_field( self::$nonce_action['deactivate'], self::$nonce_name, true, false );

			$deactivate_i18n = \__( 'Disconnect', 'the-seo-framework-extension-manager' );
			$ays_i18n        = \__( 'Are you sure?', 'the-seo-framework-extension-manager' );
			$da_i18n         = \__( 'Disconnect account?', 'the-seo-framework-extension-manager' );

			$button_class = 'tsfem-switcher-button tsfem-button tsfem-button-red tsfem-button-warning';

			$button = vsprintf(
				'<button type=submit title="%s" class="%s">%s</button>',
				[
					\esc_attr( $ays_i18n ),
					$button_class,
					\esc_html( $deactivate_i18n ),
				]
			);

			$switcher_class = 'tsfem-button-flag tsfem-button';
			// phpcs:ignore -- maybe later var_dump() fixme?
			// $switcher_class .= $tsfem->are_options_valid() ? '' : ' tsfem-button-pulse';

			$switcher = '<div class=tsfem-switch-button-container-wrap><div class=tsfem-switch-button-container>'
							. '<input type=checkbox id=disconnect-switcher-action value=1 />'
							. '<label for=disconnect-switcher-action title="' . \esc_attr( $da_i18n ) . '" class="' . $switcher_class . '">' . \esc_html( $deactivate_i18n ) . '</label>'
							. $button
						. '</div></div>';

			$output = sprintf(
				'<form name=deactivate action="%s" method=post id=tsfem-deactivation-form  autocomplete=off data-form-type=other>%s</form>',
				\esc_url( $tsfem->get_admin_page_url() ),
				$nonce_action . $nonce . $switcher
			);
		} else {
			\tsf()->_doing_it_wrong( __METHOD__, 'The disconnect button only supports the form type.' );
		}

		return $output;
	}

	/**
	 * Outputs public support button.
	 *
	 * @since 2.0.0
	 *
	 * @return string The public support button link.
	 */
	private static function get_public_support_button() {

		if ( 'link' === self::get_property( '_type' ) ) {
			return \tsfem()->get_support_link( 'public' );
		} else {
			\tsf()->_doing_it_wrong( __METHOD__, 'The public support button only supports the link type.' );
			return '';
		}
	}

	/**
	 * Outputs private support button.
	 *
	 * @since 2.0.0
	 *
	 * @return string The private support button link.
	 */
	private static function get_private_support_button() {

		if ( 'link' === self::get_property( '_type' ) ) {
			return \tsfem()->get_support_link( 'private' );
		} else {
			\tsf()->_doing_it_wrong( __METHOD__, 'The private support button only supports the link type.' );
			return '';
		}
	}

	/**
	 * Outputs premium support button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The premium support button link.
	 */
	private static function get_account_info() {

		if ( 'list' !== self::get_property( '_type' ) ) {
			\tsf()->_doing_it_wrong( __METHOD__, 'The premium account information output only supports list type.' );
			return '';
		}

		$tsfem = \tsfem();

		$valid_options = \tsfem()->are_options_valid();

		$account = self::$account;

		$email              = $account['email'] ?? '';
		$key                = $account['key'] ?? '';
		$data               = $account['data'] ?? '';
		$level              = ! empty( $account['level'] ) ? $account['level'] : \__( 'Unknown', 'the-seo-framework-extension-manager' );
		$domain             = str_ireplace( [ 'https://', 'http://' ], '', \esc_url( \get_home_url(), [ 'https', 'http' ] ) );
		$end_date           = '';
		$payment_date       = '';
		$requests_remaining = '';

		if ( $data ) {
			if ( isset( $data['status']['status_check'] ) && 'inactive' === $data['status']['status_check'] ) {
				$level = \__( 'Decoupled', 'the-seo-framework-extension-manager' );
			} else {
				// UTC.
				$end_date           = $data['status']['end_date'] ?? '';
				$payment_date       = $data['status']['payment_date'] ?? '';
				$domain             = $data['status']['activation_domain'] ?? '';
				$requests_remaining = $data['status']['requests_remaining'] ?? '';
			}
		}

		$output = '';

		if ( $email ) {
			$output .= static::wrap_row_content(
				\__( 'Account email:', 'the-seo-framework-extension-manager' ),
				str_replace( '*', '&bull;', preg_replace( '/(^.{0,2})(.*?)(.{0,1}\@.)([^.]{0,}?)(.{0,2}\..*?$)/', '$1**$3**$5', $email ) )
			);
		}

		if ( $key ) {
			$output .= static::wrap_row_content(
				\__( 'License key:', 'the-seo-framework-extension-manager' ),
				str_repeat( '&bull;', 5 ) . substr( $key, -4 )
			);
		}

		$_class = [ 'tsfem-dashicon' ];

		switch ( $level ) :
			case 'Enterprise':
				$_level   = \__( 'Enterprise', 'the-seo-framework-extension-manager' );
				$_class[] = $valid_options ? 'tsfem-success' : 'tsfem-error';
				break;

			case 'Premium':
				$_level   = \__( 'Premium', 'the-seo-framework-extension-manager' );
				$_class[] = $valid_options ? 'tsfem-success' : 'tsfem-error';
				break;

			case 'Essentials':
				$_level   = \__( 'Essentials', 'the-seo-framework-extension-manager' );
				$_class[] = $valid_options ? 'tsfem-success' : 'tsfem-error';
				break;

			case 'Free':
				$_level   = \__( 'Free', 'the-seo-framework-extension-manager' );
				$_class[] = $valid_options ? 'tsfem-success' : 'tsfem-error';
				break;

			default:
				$_level   = $level;
				$_class[] = 'tsfem-error';
				break;
		endswitch;

		if ( isset( $data['timestamp'], $data['divider'] ) ) {
			/**
			 * @TODO bugfix/make consistent/put in function/put in action?
			 * It only refreshes when a premium extension is being activated.
			 * Otherwise, it will continue to count into negatives.
			 *
			 * This might prevent rechecking "decoupled" websites... which in that case is a bug.
			 */
			$next_check_min = round( ( floor( $data['timestamp'] * $data['divider'] ) - time() ) / 60 );

			if ( $next_check_min > 0 ) {
				$level_desc = sprintf(
					/* translators: %u = minutes number */
					\_n( 'Next check is scheduled in %u minute.', 'Next check is scheduled in %u minutes.', $next_check_min, 'the-seo-framework-extension-manager' ),
					$next_check_min
				);
			}
		}

		$_level = HTML::wrap_inline_tooltip( HTML::make_inline_tooltip(
			$level,
			$level_desc ?? '',
			'',
			$_class
		) );

		$output .= static::wrap_row_content( \esc_html__( 'Account level:', 'the-seo-framework-extension-manager' ), $_level, false );

		switch ( $tsfem->get_api_endpoint_type() ) :
			case 'eu':
				$_ep = \__( 'TSF Europe', 'the-seo-framework-extension-manager' );
				break;

			default:
			case 'global':
				$_ep = \__( 'TSF global', 'the-seo-framework-extension-manager' );
				break;
		endswitch;

		$_ep_html = HTML::wrap_inline_tooltip( HTML::make_inline_tooltip(
			$_ep,
			\esc_html__( 'Outbound API connections will prefer this endpoint.', 'the-seo-framework-extension-manager' ),
			'',
			[ 'tsfem-dashicon', 'tsfem-success' ]
		) );
		$output  .= static::wrap_row_content( \esc_html__( 'API endpoint:', 'the-seo-framework-extension-manager' ), $_ep_html, false );

		if ( \is_int( $requests_remaining ) ) {
			$_notice  = '';
			$_classes = [ 'tsfem-dashicon' ];

			if ( $requests_remaining > 100 ) {
				$_notice    = \esc_html__( 'Number of API requests left for this month.', 'the-seo-framework-extension-manager' );
				$_classes[] = 'tsfem-success';
			} elseif ( $requests_remaining > 0 ) {
				$_notice    = \esc_html__( 'Only a few requests left for this month. Consider upgrading your account.', 'the-seo-framework-extension-manager' );
				$_classes[] = 'tsfem-warning';
			} else {
				$_notice    = \esc_html__( 'No requests left for this month. Consider upgrading your account.', 'the-seo-framework-extension-manager' );
				$_classes[] = 'tsfem-error';
			}
			$output .= static::wrap_row_content(
				\esc_html__( 'Requests remaining:', 'the-seo-framework-extension-manager' ),
				HTML::wrap_inline_tooltip( HTML::make_inline_tooltip(
					(int) $requests_remaining,
					$_notice,
					'',
					$_classes
				) ),
				false
			);
		}

		if ( $valid_options && $domain ) {
			// Check for domain mismatch. If they don't match no premium extensions can be activated.
			$_domain  = str_ireplace( [ 'https://', 'http://' ], '', \esc_url( \get_home_url(), [ 'https', 'http' ] ) );
			$_warning = '';
			$_classes = [ 'tsfem-dashicon' ];

			if ( $_domain === $domain ) {
				$_classes[] = 'tsfem-success';
			} else {
				$_warning = \tsf()->convert_markdown(
					sprintf(
						/* translators: `%s` = domain with markdown backtics */
						\esc_html__( 'The domain `%s` does not match the registered domain. If your website is accessible on multiple domains, switch to the registered domain. Otherwise, deactivate the account and try again.', 'the-seo-framework-extension-manager' ),
						$_domain
					),
					[ 'code' ]
				);
				$_classes[] = 'tsfem-error';
			}

			$output .= static::wrap_row_content(
				\esc_html__( 'Valid for:', 'the-seo-framework-extension-manager' ),
				HTML::wrap_inline_tooltip( HTML::make_inline_tooltip(
					// Not necessarily this domain.
					$domain,
					$_warning,
					'',
					$_classes
				) ),
				false
			);
		}

		if ( $end_date ) :
			$date_until = strtotime( $end_date );
			$now        = time();

			$difference = $date_until - $now;
			$_class     = 'tsfem-success';
			$expires_in = '';

			// Move to time.trait?
			if ( $difference < 0 ) {
				// Expired.
				$expires_in = \__( 'Account expired', 'the-seo-framework-extension-manager' );
				$_class     = 'tsfem-error';
			} elseif ( $difference < WEEK_IN_SECONDS ) {
				$expires_in = \__( 'Less than a week', 'the-seo-framework-extension-manager' );
				$_class     = 'tsfem-warning';
			} elseif ( $difference < WEEK_IN_SECONDS * 2 ) {
				$expires_in = \__( 'Less than two weeks', 'the-seo-framework-extension-manager' );
				$_class     = 'tsfem-warning';
			} elseif ( $difference < WEEK_IN_SECONDS * 3 ) {
				$expires_in = \__( 'Less than three weeks', 'the-seo-framework-extension-manager' );
			} elseif ( $difference < MONTH_IN_SECONDS ) {
				$expires_in = \__( 'Less than a month', 'the-seo-framework-extension-manager' );
			} elseif ( $difference < MONTH_IN_SECONDS * 2 ) {
				$expires_in = \__( 'Less than two months', 'the-seo-framework-extension-manager' );
			} else {
				/* translators: %d = months number */
				$expires_in = sprintf( \__( 'About %d months', 'the-seo-framework-extension-manager' ), round( $difference / MONTH_IN_SECONDS ) );
			}

			// phpcs:ignore, WordPress.DateTime.RestrictedFunctions.date_date -- Date is fetched from another server.
			$end_date      = date( 'Y-m-d', $date_until );
			$end_date_i18n = static::get_rectified_date_i18n( 'F j, Y, g:i A (\G\M\TP)', $date_until );

			$output .= static::wrap_row_content(
				\esc_html__( 'Expires in:', 'the-seo-framework-extension-manager' ),
				HTML::wrap_inline_tooltip( vsprintf(
					'<time class="tsfem-dashicon tsf-tooltip-item %s" title="%s" datetime="%s">%s</time>',
					[
						\esc_attr( $_class ),
						\esc_attr( $end_date_i18n ),
						\esc_attr( $end_date ),
						\esc_html( $expires_in ),
					]
				) ),
				false
			);
		endif;

		if ( $payment_date ) :
			$date_until = strtotime( $payment_date );
			$now        = time();

			$difference = $date_until - $now;
			$_class     = 'tsfem-success';
			$payment_in = '';

			if ( $difference < -5184000 ) {
				// Probably a permanent subscription. Let's not bother my friends.
				goto end;
			} elseif ( $difference < 0 ) {
				// Processing.
				$payment_in = \__( 'Payment processing', 'the-seo-framework-extension-manager' );
				$_class     = 'tsfem-warning';
			} elseif ( $difference < WEEK_IN_SECONDS ) {
				$payment_in = \__( 'Less than a week', 'the-seo-framework-extension-manager' );
			} elseif ( $difference < WEEK_IN_SECONDS * 2 ) {
				$payment_in = \__( 'Less than two weeks', 'the-seo-framework-extension-manager' );
			} else {
				$n = round( $difference / MONTH_IN_SECONDS );
				/* translators: %d = months number */
				$payment_in = sprintf( \_n( 'About %d month', 'About %d months', $n, 'the-seo-framework-extension-manager' ), $n );
			}

			$end_date_i18n = $payment_date ? static::get_rectified_date_i18n( 'F j, Y, g:i A (\G\M\TP)', $date_until ) : '';

			$payment_in = HTML::wrap_inline_tooltip( vsprintf(
				'<time class="tsfem-dashicon tsf-tooltip-item %s" title="%s" datetime="%s">%s</time>',
				[
					\esc_attr( $_class ),
					\esc_attr( $end_date_i18n ),
					\esc_attr( gmdate( 'Y-m-d', $date_until ) ),
					\esc_html( $payment_in ),
				]
			) );

			$output .= static::wrap_row_content( \esc_html__( 'Payment due in:', 'the-seo-framework-extension-manager' ), $payment_in, false );
		endif;

		end:;

		// Wrap tooltips here.
		return sprintf( '<div class="tsfem-flex-account-info-rows tsfem-flex tsfem-flex-nogrowshrink">%s</div>', $output );
	}

	/**
	 * Wraps columnized Title/Content output.
	 * Escapes input prior to outputting when $escape is true.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title   The title.
	 * @param string $content The content.
	 * @param bool   $escape  Whether to escape the output.
	 * @return string The Title/Content wrap.
	 */
	public static function wrap_row_content( $title, $content, $escape = true ) {

		if ( $escape ) {
			$title   = \esc_html( $title );
			$content = \esc_html( $content );
		}

		$output = sprintf( '<div class=tsfem-row-info-title>%s</div><div class=tsfem-row-info-value>%s</div>', $title, $content );

		return sprintf( '<div class="tsfem-row-info tsfem-flex tsfem-flex-row tsfem-flex-space tsfem-flex-noshrink">%s</div>', $output );
	}

	/**
	 * Outputs premium support button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The premium support button link.
	 */
	private static function get_account_upgrade_form() {

		if ( 'form' === self::get_property( '_type' ) ) {
			$tsfem = \tsfem();

			$input = sprintf(
				'<input id="%s" name=%s type=text size=15 class="regular-text code tsfem-flex tsfem-flex-row" placeholder="%s">',
				$tsfem->_get_field_id( 'key' ),
				$tsfem->_get_field_name( 'key' ),
				\esc_attr__( 'License key', 'the-seo-framework-extension-manager' )
			);

			$input .= sprintf(
				'<input id="%s" name=%s type=text size=15 class="regular-text code tsfem-flex tsfem-flex-row" placeholder="%s">',
				$tsfem->_get_field_id( 'email' ),
				$tsfem->_get_field_name( 'email' ),
				\esc_attr__( 'License email', 'the-seo-framework-extension-manager' )
			);

			$nonce_action = $tsfem->_get_nonce_action_field( self::$request_name['activate-key'] );
			$nonce        = \wp_nonce_field( self::$nonce_action['activate-key'], self::$nonce_name, true, false );

			$submit = sprintf(
				'<input type=submit name=submit id=tsfem-account-upgrade-submit class=tsfem-button-primary value="%s">',
				\esc_attr__( 'Use this key', 'the-seo-framework-extension-manager' )
			);

			$form = $input . $nonce_action . $nonce . $submit;

			return sprintf(
				'<form class="tsfem-flex tsfem-flex-nowrap" name="%s" action="%s" method=post id="%s" class="%s" autocomplete=off data-form-type=other>%s</form>',
				\esc_attr( self::$request_name['activate-key'] ),
				\esc_url( $tsfem->get_admin_page_url() ),
				'input-activation',
				'',
				$form
			);
		} else {
			\tsf()->_doing_it_wrong( __METHOD__, 'The upgrade form only supports the form type.' );
			return '';
		}
	}
}
