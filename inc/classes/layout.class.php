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
 * Class TSF_Extension_Manager\Layout.
 *
 * Outputs layout based on instance.
 *
 * @since 1.0.0
 * @access private
 * 		You'll need to invoke the TSF_Extension_Manager\Core verification handler. Which is impossible.
 * @final Please don't extend this.
 */
final class Layout extends Secure_Abstract {

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Required. The instance type.
	 * @param string $instance Required. The instance key.
	 * @param int $bit Required. The instance bit.
	 */
	public static function initialize( $type = '', $instance = '', $bits = null ) {

		self::reset();

		if ( empty( $type ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an initialization type.' );
		} else {

			self::set( '_wpaction' );

			switch ( $type ) :
				case 'form' :
					tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', 'form' );
					break;

				case 'link' :
					tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', 'link' );
					break;

				default :
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
	 * @return string
	 */
	public static function get( $type = '' ) {

		self::verify_instance() or die;

		if ( empty( $type ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an get type.' );
			return false;
		}

		switch ( $type ) :
			case 'deactivation-button' :
				return static::get_deactivation_button();
				break;

			case 'free-support-button' :
				return static::get_free_support_button();
				break;

			case 'premium-support-button' :
				return static::get_premium_support_button();
				break;

			default :
				the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct get type.' );
				break;
		endswitch;

		return false;
	}

	/**
	 * Outputs deactivation button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The deactivation button.
	 */
	private static function get_deactivation_button() {

		$output = '';

		if ( 'form' === self::get_property( '_type' ) ) {
			$nonce_action = tsf_extension_manager()->get_nonce_action_field( self::$request_name['deactivate'] );
			$nonce = wp_nonce_field( self::$nonce_action['deactivate'], self::$nonce_name, true, false );

			$field_id = 'deactivation-switcher';
			$deactivate_i18n = __( 'Deactivate', 'the-seo-framework-extension-manager' );
			$ays_i18n = __( 'Are you sure?', 'the-seo-framework-extension-manager' );
			$da_i18n = __( 'Deactivate account?', 'the-seo-framework-extension-manager' );

			$button = '<input type="submit" id="' . $field_id . '-validator">'
					. '<label for="' . $field_id . '-validator" title="' . esc_attr( $ays_i18n ) . '" class="tsfem-button-primary tsfem-switcher-button tsfem-button-warning">' . esc_html( $deactivate_i18n ) . '</label>';

			$switcher = '<div class="tsfem-switch-button-container-wrap"><div class="tsfem-switch-button-container">'
							. '<input type="checkbox" id="' . $field_id . '-action" value="1" />'
							. '<label for="' . $field_id . '-action" title="' . esc_attr( $da_i18n ) . '" class="tsfem-button tsfem-button-flag">' . esc_html( $deactivate_i18n ) . '</label>'
							. $button
						. '</div></div>';

			$output = sprintf( '<form name="deactivate" action="%s" method="post" id="tsfem-deactivation-form">%s</form>',
				esc_url( tsf_extension_manager()->get_admin_page_url() ),
				$nonce_action . $nonce . $switcher
			);
		} else {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'The deactivation button only supports the form instance.' );
		}

		return $output;
	}

	/**
	 * Outputs free support button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The free support button link.
	 */
	private static function get_free_support_button() {

		if ( 'link' === self::get_property( '_type' ) ) {
			return tsf_extension_manager()->get_support_link( 'free' );
		} elseif ( 'form' === self::get_property( 'type' ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'The free support will most likely never support forms.' );
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
	private static function get_premium_support_button() {

		if ( 'link' === self::get_property( '_type' ) ) {
			return tsf_extension_manager()->get_support_link( 'premium' );
		} elseif ( 'form' === self::get_property( 'type' ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'The premium support button does not yet support forms.' );
			return '';
		}
	}
}
