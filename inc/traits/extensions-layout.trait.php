<?php
/**
 * @package TSF_Extension_Manager\Traits
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
* Holds i18n data functions for class TSF_Extension_Manager\Extensions.
*
* @since 1.0.0
* @access private
*/
trait Extensions_i18n {

	/**
	 * Initializes i18n.
	 *
	 * @since 1.0.0
	 * @staticvar array $i18n
	 *
	 * @return array $i18n The internationalization data.
	 */
	private static function obtain_i18n() {

		static $i18n = null;

		if ( isset( $i18n ) )
			return $i18n;

		return $i18n = array(
			'free'       => __( 'Free', 'the-seo-framework-extension-manager' ),
			'premium'    => __( 'Premium', 'the-seo-framework-extension-manager' ),
			'activate'   => __( 'Activate', 'the-seo-framework-extension-manager' ),
			'deactivate' => __( 'Deactivate', 'the-seo-framework-extension-manager' ),
		);
	}

	/**
	 * Returns i18n value from key.
	 *
	 * @since 1.0.0
	 *
	 * @return string The i18n data.
	 */
	private static function get_i18n( $key = '' ) {

		$i18n = static::obtain_i18n();

		return isset( $i18n[ $key ] ) ? $i18n[ $key ] : '';
	}
}

/**
 * Holds Layout functions for class TSF_Extension_Manager\Extensions.
 *
 * @since 1.0.0
 * @access private
 */
trait Extensions_Layout {

	/**
	 * Holds the class header contents.
	 *
	 * @since 1.0.0
	 *
	 * @var array $header
	 */
	private static $header = array();

	/**
	 * Outputs extensions overview header.
	 *
	 * @since 1.0.0
	 * @todo all of it
	 * @todo add refresh AJAX button with transient 5 min.
	 * @todo add filter tabs based on extension tags
	 *
	 * @return string The extensions overview header.
	 */
	private static function get_layout_header() {

		$output = '';

		if ( 'overview' === self::get_property( '_type' ) ) {
			foreach ( static::$header as $id => $item ) {
				$output .= '';
			}
		}

		return $output;
	}

	/**
	 * Outputs extensions overview content.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions overview content.
	 */
	private static function get_layout_content() {

		$output = '';

		if ( 'overview' === self::get_property( '_type' ) ) {
			$output = static::get_extensions_list();
		}

		return $output;
	}

	/**
	 * Generates a list of the available extensions.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions list in HTML.
	 */
	private static function get_extensions_list() {

		$extensions = static::filter_extensions( static::$extensions, 'maybe_network' );

		$output = '';

		foreach ( $extensions as $id => $extension ) {

			if ( ! ( isset( $extension['slug'] ) && isset( $extension['type'] ) && isset( $extension['title'] ) ) )
				continue;

			$wrap = '<div class="tsfem-extension-icon-wrap tsfem-flex-nogrowshrink">' . static::make_extension_list_icon( $extension ) . '</div>';
			$wrap .= '<div class="tsfem-extension-about-wrap">' . static::make_extension_list_about( $extension ) . '</div>';
			$wrap .= '<div class="tsfem-extension-description-wrap">' . static::make_extension_list_description( $extension ) . '</div>';

			$class = static::is_extension_active( $extension ) ? 'tsfem-extension-activated' : 'tsfem-extension-deactivated';

			$output .= sprintf( '<div class="tsfem-extension-entry tsfem-flex tsfem-flex-row %s" id="%s">%s</div>', $class, esc_attr( $id ), $wrap );
		}

		return $output;
	}

	/**
	 * Builds extension image icon tag. Images must be square.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extension The extension to make icon from.
	 * @param array $size The icon height and width.
	 * @return string HTML image.
	 */
	private static function make_extension_list_icon( $extension, $size = '120' ) {

		//* @TODO set default image.
		$fallback = array( 'svg' => '', '2x' => '', '1x' => '', 'default' => '' );
		$items = null;

		if ( ! empty( $extension['icons'] ) ) {
			$default = isset( $extension['icons']['default'] ) ? static::get_extension_asset_url( $extension['slug'], $extension['icons']['default'] ) : '';

			if ( $default ) {
				$svg = isset( $extension['icons']['svg'] ) ? static::get_extension_asset_url( $extension['slug'], $extension['icons']['svg'] ) : '';
				$one = isset( $extension['icons']['1x'] ) ? static::get_extension_asset_url( $extension['slug'], $extension['icons']['1x'] ) : '';
				$two = isset( $extension['icons']['2x'] ) ? static::get_extension_asset_url( $extension['slug'], $extension['icons']['2x'] ) : '';

				$items = array(
					'svg' => $svg,
					'2x' => $two,
					'1x' => $one,
					'default' => $default,
				);
			}
		}

		$items = isset( $items ) ? $items : $fallback;
		$size = esc_attr( $size );

		if ( $items['svg'] ) {
			$image = '<img src="' . esc_url( $items['svg'] ) . '" alt="extension-icon" height="' . $size . '" width="' . $size . '">';
		} else {
			if ( $items['2x'] ) {
				$image = '<img src="' . esc_url( $items['default'] ) . '" srcset="' . esc_url( $items['1x'] ) . ' 1x, ' . esc_url( $items['2x'] ) . ' 2x" alt="extension-icon" height="' . $size . '" width="' . $size . '">';
			} else {
				$image = '<img src="' . esc_url( $items['default'] ) . '" alt="extension-icon" height="' . $size . '" width="' . $size . '">';
			}
		}

		return $image;
	}

	/**
	 * Builds extension about section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extension The extension to make section from.
	 * @return string HTML extension section with actions and nonce.
	 */
	private static function make_extension_list_about( $extension ) {

		$header = static::make_extension_header( $extension );
		$buttons = static::make_extension_buttons( $extension );

		return $header . $buttons;
	}

	/**
	 * Makes extension header.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extension The extension to make header from.
	 * @return string HTML extension header.
	 */
	private static function make_extension_header( $extension ) {

		$title = '<h4 class="tsfem-extension-title">' . esc_html( $extension['title'] ) . '</h4>';

		$type = 'free' === $extension['type'] ? static::get_i18n( 'free' ) : static::get_i18n( 'premium' );
		$type = '<h5 class="tsfem-extension-type">' . esc_html( $type ) . '</h5>';

		return  '<div class="tsfem-extension-header tsfem-flex tsfem-flex-row tsfem-flex-noshrink">' . $title . $type . '</div>';
	}

	/**
	 * Builds extension activation/update/deactivate buttons based on extension and
	 * account type. Also initializes nonces for those buttons.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extension The extension to make button from.
	 * @return string HTML extension button with nonce.
	 */
	private static function make_extension_buttons( $extension ) {

		$buttons = array();

		if ( static::is_extension_active( $extension ) ) {
			$buttons[] = array(
				'type' => 'deactivate',
				'disabled' => false,
			);
		} else {
			$disabled = self::is_premium_account() || ! static::is_extension_premium( $extension ) ? false : true;
			$buttons[] = array(
				'type' => 'activate',
				'disabled' => $disabled,
			);
		}

		$output = '';

		foreach ( $buttons as $button ) :
			$output .= static::get_extension_button_form( $extension['slug'], $button['type'], $button['disabled'] );
		endforeach;

		return sprintf( '<div class="tsfem-extension-actions-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>', $output );
	}

	/**
	 * Builds extension button form and builds nonce. Supports both JS and no-JS.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 * @param string $type The button type.
	 * @param bool $disabled Whether the button is disabled and holds no action.
	 * @return string The button form.
	 */
	private static function get_extension_button_form( $slug = '', $type = '', $disabled = false ) {

		$key = '';

		//* This pattern can't be unseen. Let's just keep it this way until further notice.
		switch ( $type ) :
			case 'activate' :
				$nonce_key = 'activate-ext';
				$text = static::get_i18n( 'activate' );
				$class = 'tsfem-button-extension-activate';
				break;
			case 'deactivate' :
				$nonce_key = 'deactivate-ext';
				$text = static::get_i18n( 'deactivate' );
				$class = 'tsfem-button-extension-deactivate';
				break;
			default :
				return '';
				break;
		endswitch;

		if ( $disabled ) {
			$button = sprintf( '<span class="tsfem-button-primary %s tsfem-button-disabled ">%s</span>', esc_attr( $class ), esc_html( $text ) );
		} else {
			$nonce_action = tsf_extension_manager()->get_nonce_action_field( self::$request_name[ $nonce_key ] );
			$nonce = wp_nonce_field( self::$nonce_action[ $nonce_key ], self::$nonce_name, true, false );
			$extension = '<input type="hidden" name="' . esc_attr( tsf_extension_manager()->get_field_name( 'extension' ) ) . '" value="' . esc_attr( $slug ) . '">';
			$submit = sprintf( '<input type="submit" name="submit" id="submit" class="tsfem-button-primary %s" value="%s">', esc_attr( $class ), esc_attr( $text ) );
			$form = $nonce_action . $nonce . $extension . $submit;

			$nojs = sprintf( '<form action="%s" method="post" id="tsfem-activate-form[%s]" class="hide-if-js">%s</form>', esc_url( tsf_extension_manager()->get_admin_page_url() ), esc_attr( $slug ), $form );
			$js = sprintf( '<a id="tsfem-activate[%s]" class="tsfem-button-primary hide-if-no-js %s" data-slug="%s" data-case="%s">%s</a>', esc_attr( $slug ), esc_attr( $class ), esc_attr( $slug ), esc_attr( $type ), esc_html( $text ) );

			$button = $nojs . $js;
		}

		return sprintf( '<div class="tsfem-extension-action">%s</div>', $button );
	}

	private static function make_extension_list_description( $extension ) {

	}
}
