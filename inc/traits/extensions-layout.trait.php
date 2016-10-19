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
			'free'            => __( 'Free', 'the-seo-framework-extension-manager' ),
			'premium'         => __( 'Premium', 'the-seo-framework-extension-manager' ),
			'activate'        => __( 'Activate', 'the-seo-framework-extension-manager' ),
			'deactivate'      => __( 'Deactivate', 'the-seo-framework-extension-manager' ),
			'version'         => __( 'Version', 'the-seo-framework-extension-manager' ),
			'first-party'     => __( 'First party', 'the-seo-framework-extension-manager' ),
			'third-party'     => __( 'Third party', 'the-seo-framework-extension-manager' ),
			'view-details'    => __( 'View detais', 'the-seo-framework-extension-manager' ),
			'visit-author'    => __( 'Go to the author homepage', 'the-seo-framework-extension-manager' ),
			'visit-extension' => __( 'Go to the extension homepage', 'the-seo-framework-extension-manager' ),
			'extension-home'  => __( 'Extension home', 'the-seo-framework-extension-manager' ),
			'compatible'      => __( 'Compatible', 'the-seo-framework-extension-manager' ),
			'incompatible'    => __( 'Incompatible', 'the-seo-framework-extension-manager' ),
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
			// foreach ( static::$header as $id => $item ) {
			// 	$output .= '';
			// }
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

			if ( false === ( isset( $extension['slug'] ) && isset( $extension['type'] ) && isset( $extension['area'] ) ) )
				continue;

			$wrap = '<div class="tsfem-extension-icon-wrap tsfem-flex-nogrowshrink tsfem-flex-wrap">' . static::make_extension_list_icon( $extension ) . '</div>';
			$wrap .= '<div class="tsfem-extension-about-wrap tsfem-flex tsfem-flex-grow">' . static::make_extension_list_about( $extension ) . '</div>';
			$wrap .= '<div class="tsfem-extension-description-wrap tsfem-flex tsfem-flex-space">' . static::make_extension_list_description( $extension ) . '</div>';

			$class = static::is_extension_active( $extension ) ? 'tsfem-extension-activated' : 'tsfem-extension-deactivated';

			$entry = sprintf( '<div class="tsfem-extension-entry tsfem-flex tsfem-flex-noshrink tsfem-flex-row %s" id="%s">%s</div>', $class, esc_attr( $id ), $wrap );

			$output .= sprintf( '<div class="tsfem-extension-entry-wrap tsfem-flex tsfem-flex-space">%s</div>', $entry );
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

		$one = $two = $svg = null;

		if ( ! empty( $extension['slug'] ) ) {
			$svg = static::get_extension_asset_location( $extension['slug'], 'icon.svg' );
			$two = static::get_extension_asset_location( $extension['slug'], 'icon-240x240px.png' );
			$one = static::get_extension_asset_location( $extension['slug'], 'icon-120x120px.png' );

			$svg = file_exists( $svg ) ? static::get_extension_asset_location( $extension['slug'], 'icon.svg', true ) : '';
			$two = file_exists( $two ) ? static::get_extension_asset_location( $extension['slug'], 'icon-240x240px.png', true ) : '';
			$one = file_exists( $one ) ? static::get_extension_asset_location( $extension['slug'], 'icon-120x120px.png', true ) : '';
		}

		if ( empty( $svg | $two | $one ) ) {
			$svg = tsf_extension_manager()->get_image_file_location( 'exticon-fallback.svg', true );
			$two = tsf_extension_manager()->get_image_file_location( 'exticon-fallback-240x240px.png', true );
			$one = tsf_extension_manager()->get_image_file_location( 'exticon-fallback-120x120px.png', true );
		}

		$items = array(
			'svg' => $svg,
			'2x' => $two,
			'1x' => $one,
		);

		if ( $items['svg'] ) {
			$image = sprintf( '<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" src="%2$s" width="%3$s" height="%3$s" alt="extension-icon"></image>', esc_url( $items['svg'] ), esc_url( $items['1x'] ), esc_attr( $size ) );
			$image = sprintf( '<svg width="%1$s" height="%1$s">%2$s</svg>', esc_attr( $size ), $image );
		} elseif ( $items['2x'] ) {
			$image = sprintf( '<img src="%1$s" srcset="%1$s 1x, %2$s 2x" alt="extension-icon" height="%3$s" width="%3$s">', esc_url( $items['1x'] ), esc_url( $items['2x'] ), esc_attr( $size ) );
		} elseif ( $items['1x'] ) {
			$image = sprintf( '<img src="%1$s" alt="extension-icon" height="%2$s" width="%2$s">', esc_url( $items['1x'] ), esc_attr( $size ) );
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
		$subheader = static::make_extension_subheader( $extension );
		$buttons = static::make_extension_buttons( $extension );

		return $header . $subheader . $buttons;
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

		$data = static::get_extension_header( $extension['slug'] );

		$title = '<h4 class="tsfem-extension-title">' . esc_html( $data['Name'] ) . '</h4>';

		$type = 'free' === $extension['type'] ? static::get_i18n( 'free' ) : static::get_i18n( 'premium' );
		$type = '<h5 class="tsfem-extension-type">' . esc_html( $type ) . '</h5>';

		return  '<div class="tsfem-extension-header tsfem-flex tsfem-flex-row tsfem-flex-space tsfem-flex-noshrink">' . $title . $type . '</div>';
	}

	/**
	 * Makes extension subheader.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extension The extension to make subheader from.
	 * @return string HTML extension subheader.
	 */
	private static function make_extension_subheader( $extension ) {

		$data = static::get_extension_header( $extension['slug'] );

		$party_class = 'first' === $extension['party'] ? 'tsfem-extension-first-party-icon' : 'tsfem-extension-third-party-icon';
		$party_title = 'first' === $extension['party'] ? static::get_i18n( 'first-party' ) : static::get_i18n( 'third-party' );

		$party = sprintf( '<span class="tsfem-extension-party %s" title="%s"></span>', $party_class, esc_attr( $party_title ) );
		$author = '<span class="tsfem-extension-author">' . esc_html( $data['Author'] ) . '</span>';

		return  '<div class="tsfem-extension-subheader tsfem-flex tsfem-flex-row tsfem-flex-noshrink">' . $party . $author . '</div>';
	}

	/**
	 * Builds extension activation/update/deactivate buttons based on extension and
	 * account type. Also initializes nonces for those buttons.
	 *
	 * @since 1.0.0
	 * @uses trait TSF_Extension_Manager\Extensions_i18n
	 * @uses trait TSF_Extension_Manager\Extensions_Actions
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
			//* Disable if: Extension is not compatible || User isn't premium and extension is.
			$disabled = static::is_extension_compatible( $extension ) === -1 || ( ! self::is_premium_user() && static::is_extension_premium( $extension ) );
			$buttons[] = array(
				'type' => 'activate',
				'disabled' => $disabled,
			);
		}

		$output = '';

		foreach ( $buttons as $button ) {
			$output .= static::get_extension_button_form( $extension['slug'], $button['type'], $button['disabled'] );
		}

		return sprintf( '<div class="tsfem-extension-actions-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>', $output );
	}

	/**
	 * Builds extension button form and builds nonce. Supports both JS and no-JS.
	 *
	 * @since 1.0.0
	 * @uses trait TSF_Extension_Manager\Extensions_i18n
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

		return sprintf( '<div class="tsfem-extension-action tsfem-flex tsfem-flex-row">%s</div>', $button );
	}

	/**
	 * Outputs the extension description wrap and content.
	 *
	 * @since 1.0.0
	 * @uses trait TSF_Extension_Manager\Extensions_i18n
	 * @uses trait TSF_Extension_Manager\Extensions_Actions
	 *
	 * @param array $extension The extension to fetch the description wrap from.
	 * @return string The extension description output wrap.
	 */
	private static function make_extension_list_description( $extension ) {

		$data = static::get_extension_header( $extension['slug'] );

		$description = $data['Description'];

		//* Make extension author element.
		//	$author = $data['Author'];
		//	$author_url = $data['AuthorURI'];
		//	$author = sprintf( '<a href="%s" target="_blank" class="tsfem-extension-description-author" title="%s">%s</a>', esc_url( $author_url ), esc_attr( static::get_i18n( 'visit-author' ) ), esc_html( $author ) );

		//* Make extension version element.
		$version = $data['Version'];
		$version = sprintf( '<span class="tsfem-extension-description-version">%s %s</span>', esc_html( static::get_i18n( 'version' ) ), esc_html( $version ) );

		//* Make extension home element.
		$url = $data['ExtensionURI'];
		$home = sprintf( '<a href="%s" target="_blank" class="tsfem-extension-description-home" title="%s">%s</a>', esc_url( $url ), esc_attr( static::get_i18n( 'visit-extension' ) ), esc_html( static::get_i18n( 'extension-home' ) ) );

		//* Make extension compatibility element.
		$is_compatible = static::is_extension_compatible( $extension );

		switch ( $is_compatible ) :
			case 0 :
				$compat_class = 'tsfem-success';
				$compat_notice = __( 'Compatible with the current versions of WordPress and The SEO Framework.', 'the-seo-framework-extension-manager' );
				$compat_name = static::get_i18n( 'compatible' );
				break;

			case 1 :
			case 2 :
			case 3 :
				$compat_class = 'tsfem-unknown';
				$compat_name = static::get_i18n( 'compatible' );
				switch ( $is_compatible ) :
					case 1 :
						$compat_notice = __( 'The SEO Framework version is higher than tested against.', 'the-seo-framework-extension-manager' );
						break;
					case 2 :
						$compat_notice = __( 'WordPress version is higher than tested against.', 'the-seo-framework-extension-manager' );
						break;
					case 3 :
						$compat_notice = __( 'WordPress and The SEO Framework versions are higher than tested against.', 'the-seo-framework-extension-manager' );
						break;
				endswitch;
				break;

			case -1 :
			default :
				$compat_class = 'tsfem-error';
				/* translators: 1: Version number, 2: Version number */
				$compat_notice = sprintf( __( 'WordPress %1$s and The SEO Framework %2$s are required.', 'the-seo-framework-extension-manager' ), $extension['requires'], $extension['requires_tsf'] );
				$compat_name = static::get_i18n( 'incompatible' );
				break;
		endswitch;

		$compat_icon = sprintf( '<span class="tsfem-extension-description-icon tsfem-dashicon %s"></span>', $compat_class );
		$compatible = sprintf( '<span class="tsfem-extension-description-compat tsfem-has-hover-balloon" title="%s" data-desc="%s"><span>%s%s</span></span>', esc_attr( $compat_notice ), esc_html( $compat_notice ), esc_html( $compat_name ), $compat_icon );

		//* Put it all together.
		$content = sprintf( '<div class="tsfem-extension-description-header">%s</div>', esc_html( $description ) );
		$content .= sprintf( '<div class="tsfem-extension-description-footer">%s</div>', implode( ' | ', array( $version, $compatible, $home ) ) );
	 	$output = sprintf( '<div class="tsfem-extension-description tsfem-flex tsfem-flex-space">%s</div>', $content );

		return $output;
	}
}
