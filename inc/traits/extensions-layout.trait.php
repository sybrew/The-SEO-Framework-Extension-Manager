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
	 * @todo add filter tabs based on plugin tags
	 *
	 * @return string The extensions overview header.
	 */
	private static function get_header() {

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
	private static function get_content() {

		$output = '';

		if ( 'overview' === self::get_property( '_type' ) ) {
			$output = static::get_plugins_list();
		}

		return $output;
	}

	/**
	 * Generates a list of the available plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugins list in HTML.
	 */
	private static function get_plugins_list() {

		$plugins = static::filter_plugins( static::$plugins, 'maybe_network' );

		$output = '';

		foreach ( $plugins as $id => $plugin ) {

			if ( ! ( isset( $plugin['slug'] ) && isset( $plugin['type'] ) && isset( $plugin['name'] ) ) )
				continue;

			$wrap = '<div class="tsfem-plugin-icon-wrap">' . static::make_plugin_list_icon( $plugin ) . '</div>';
			$wrap .= '<div class="tsfem-plugin-about-wrap">' . static::make_plugin_list_about( $plugin ) . '</div>';
			$wrap .= '<div class="tsfem-plugin-description-wrap">' . static::make_plugin_list_description( $plugin ) . '</div>';

			$output .= sprintf( '<div class="tsfem-plugin-entry" id="%s">%s</div>', esc_attr( $id ), $wrap );
		}

		return $output;
	}

	/**
	 * Builds plugin image icon tag. Images must be square.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The plugin to make icon from.
	 * @param array $size The icon height and width.
	 * @return string HTML image.
	 */
	private static function make_plugin_list_icon( $plugin, $size = '100' ) {

		//* @TODO set default image.
		$fallback = array( 'svg' => '', '2x' => '', '1x' => '', 'default' => '' );
		$items = null;

		if ( ! empty( $plugin['icons'] ) ) {
			$default = isset( $plugin['icons']['default'] ) ? $plugin['icons']['default'] : '';

			if ( $default ) {
				$svg = isset( $plugin['icons']['svg'] ) ? $plugin['icons']['svg'] : '';
				$one = isset( $plugin['icons']['1x'] ) ? $plugin['icons']['1x'] : '';
				$two = isset( $plugin['icons']['2x'] ) ? $plugin['icons']['2x'] : '';

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
			$image = '<img src="' . esc_url( $items['svg'] ) . '" alt="plugin-icon" height="' . $size . '" width="' . $size . '">';
		} else {
			if ( $items['2x'] ) {
				$image = '<img src="' . esc_url( $items['default'] ) . '" srcset="' . esc_url( $items['1x'] ) . ' 1x, ' . esc_url( $items['2x'] ) . ' 2x" alt="plugin-icon" height="' . $size . '" width="' . $size . '">';
			} else {
				$image = '<img src="' . esc_url( $items['default'] ) . '" alt="plugin-icon" height="' . $size . '" width="' . $size . '">';
			}
		}

		return $image;
	}

	/**
	 * Builds plugin about section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The plugin to make section from.
	 * @return string HTML plugin section with actions and nonce.
	 */
	private static function make_plugin_list_about( $plugin ) {

		$header = static::make_plugin_header( $plugin );
		$buttons = static::make_plugin_buttons( $plugin );

		return $header . $buttons;
	}

	/**
	 * Makes plugin header.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The plugin to make header from.
	 * @return string HTML plugin header.
	 */
	private static function make_plugin_header( $plugin ) {

		$title = '<h4 class="tsfem-extension-title">' . esc_html( $plugin['name'] ) . '</h4>';

		$type = 'free' === $plugin['type'] ? static::get_i18n( 'free' ) : static::get_i18n( 'premium' );
		$type = '<h5 class="tsfem-extension-type">' . esc_html( $type ) . '</h5>';

		return  '<div class="tsfem-extension-header">' . $title . $type . '</div>';
	}

	/**
	 * Builds plugin activation/update/deactivate buttons based on plugin and
	 * account type. Also initializes nonces for those buttons.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The plugin to make button from.
	 * @return string HTML plugin button with nonce.
	 */
	private static function make_plugin_buttons( $plugin ) {

		$buttons = array();

		if ( static::is_plugin_active( $plugin ) ) {
			$buttons[] = array(
				'type' => 'deactivate',
				'disabled' => false,
			);
		} else {
			$disabled = self::is_premium_account() || ! static::is_plugin_premium( $plugin ) ? false : true;
			$buttons[] = array(
				'type' => 'activate',
				'disabled' => $disabled,
			);
		}

		$output = '';

		foreach ( $buttons as $button ) :
			$output .= static::get_plugin_button_form( $plugin['slug'], $button['type'], $button['disabled'] );
		endforeach;

		return sprintf( '<div class="tsfem-extension-actions-wrap">%s</div>', $output );
	}

	/**
	 * Builds extension button form and builds nonce. Supports both JS and no-JS.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The plugin/extension slug.
	 * @param string $type The button type.
	 * @param bool $disabled Whether the button is disabled and holds no action.
	 * @return string The button form.
	 */
	private static function get_plugin_button_form( $slug = '', $type = '', $disabled = false ) {

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
			$button = sprintf( '<span class="tsfem-button-primary hide-if-no-js %s tsfem-button-disabled ">%s</span>', esc_attr( $class ), esc_html( $text ) );
		} else {
			$nonce_action = tsf_extension_manager()->get_nonce_action_field( self::$request_name[ $nonce_key ] );
			$nonce = wp_nonce_field( self::$nonce_action[ $nonce_key ], self::$nonce_name, true, false );
			$submit = sprintf( '<input type="submit" name="submit" id="submit" class="tsfem-button-primary %s" value="%s">', esc_attr( $class ), esc_attr( $text ) );
			$form = $nonce_action . $nonce . $submit;

			$nojs = sprintf( '<form action="%s" method="post" id="tsfem-activate-form[%s]" class="hide-if-js">%s</form>', esc_url( tsf_extension_manager()->get_admin_page_url() ), esc_attr( $slug ), $form );
			$js = sprintf( '<a id="tsfem-activate[%s]" class="tsfem-button-primary hide-if-no-js %s">%s</a>', esc_attr( $slug ), esc_attr( $class ), esc_html( $text ) );

			$button = $nojs . $js;
		}

		return sprintf( '<div class="tsfem-extension-action">%s</div>', $button );
	}

	private static function make_plugin_list_description( $plugin ) {

	}
}