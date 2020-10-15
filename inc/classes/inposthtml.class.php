<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\InpostHTML.
 *
 * Puts elements in common Inpost HTML wraps.
 * All functions are publically accessible by default.
 *
 * Importing this class for clean code is recommended.
 *
 * @see <http://php.net/manual/en/language.namespaces.importing.php>
 *
 * @since 1.5.0
 * @access public
 * @final
 */
final class InpostHTML {
	use Enclose_Stray_Private,
		Construct_Core_Static_Final;

	/**
	 * Outputs notification area.
	 *
	 * @since 1.5.0
	 *
	 * @param string $id The notification area ID.
	 */
	public static function notification_area( $id ) {
		printf( '<div class=tsfem-flex-settings-notification-area id=%s></div>', \esc_attr( $id ) );
	}

	/**
	 * Wraps and outputs content in common flex wrap for tabs.
	 *
	 * @since 1.5.0
	 * @uses static::construct_flex_wrap();
	 * @see documentation static::construct_flex_wrap();
	 *
	 * @param string $what    The type of wrap to use.
	 * @param string $content The content to wrap. Should be escaped.
	 * @param string $id      The wrap ID.
	 * @param string $for     The input ID an input label is for. Should be escaped.
	 */
	public static function wrap_flex( $what, $content, $id = '', $for = '' ) {
		//= Input should already be escaped.
		echo static::construct_flex_wrap( $what, $content, $id, $for );
	}

	/**
	 * Wraps and outputs and array of content in common flex wrap for tabs.
	 *
	 * Mainly used to wrap blocks and checkboxes.
	 * Does not accept title labels directly.
	 *
	 * @since 1.5.0
	 * @uses static::construct_flex_wrap();
	 * @see documentation static::construct_flex_wrap();
	 *
	 * @param string $what     The type of wrap to use.
	 * @param array  $contents The contents to wrap. Should be escaped.
	 * @param string $id       The wrap ID.
	 */
	public static function wrap_flex_multi( $what, array $contents, $id = '' ) {
		//= Input should already be escaped.
		echo static::contruct_flex_wrap_multi( $what, $contents, $id );
	}

	/**
	 * Wraps an array content in common flex wrap for tabs.
	 *
	 * Mainly used to wrap blocks and checkboxes.
	 * Does not accept title labels directly.
	 *
	 * @since 1.5.0
	 * @uses static::construct_flex_wrap();
	 * @see documentation static::construct_flex_wrap();
	 *
	 * @param string $what     The type of wrap to use.
	 * @param array  $contents The contents to wrap. Should be escaped.
	 * @param string $id       The wrap ID.
	 */
	public static function contruct_flex_wrap_multi( $what, array $contents, $id = '' ) {
		return static::construct_flex_wrap( $what, implode( PHP_EOL, $contents ), $id );
	}

	/**
	 * Wraps content in common flex wrap for tabs.
	 *
	 * @since 1.5.0
	 * @see static::wrap_flex();
	 *
	 * @param string $what The type of wrap to use. Accepts:
	 *               'block'        : The main wrap. Wraps a label and input/content block.
	 *               'label'        : Wraps a label.
	 *                                Be sure to wrap parts in `<div>` for alignment.
	 *               'label-input'  : Wraps an input label.
	 *                                Be sure to assign the $for parameter.
	 *                                Be sure to wrap parts in `<div>` for alignment.
	 *               'input'        : Wraps input content fields, plainly.
	 *               'content'      : Same as 'input'.
	 *               'checkbox'     : Wraps a checkbox and its label.
	 * @param string $content The content to wrap. Should be escaped.
	 * @param string $id      The wrap ID. Should be escaped.
	 * @param string $for     The input ID an input label is for. Should be escaped.
	 */
	public static function construct_flex_wrap( $what, $content, $id = '', $for = '' ) {

		$id = $id ? "id=$id" : '';

		switch ( $what ) :
			case 'block':
				$content = sprintf( '<div class="tsf-flex-setting tsf-flex" %s>%s</div>', $id, $content );
				break;

			case 'label':
				$content = sprintf(
					'<div class="tsf-flex-setting-label tsf-flex" %s>
						<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
							<div class="tsf-flex-setting-label-item tsf-flex">
								%s
							</div>
						</div>
					</div>',
					$id,
					$content
				);
				break;

			case 'label-input':
				$for or \the_seo_framework()->_doing_it_wrong( __METHOD__, 'Set the <code>$for</code> (3rd) parameter.' );
				$content = sprintf(
					'<div class="tsf-flex-setting-label tsf-flex" %s>
						<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
							<label for="%s" class="tsf-flex-setting-label-item tsf-flex">
								%s
							</label>
						</div>
					</div>',
					$id,
					$for,
					$content
				);
				break;

			case 'input':
			case 'content':
				$content = sprintf( '<div class="tsf-flex-setting-input tsf-flex" %s>%s</div>', $id, $content );
				break;

			case 'block-open':
				$content = sprintf( '<div class="tsf-flex-setting tsf-flex" %s>%s', $id, $content );
				break;

			case 'input-open':
			case 'content-open':
				$content = sprintf( '<div class="tsf-flex-setting-input tsf-flex" %s>%s', $id, $content );
				break;

			case 'block-close':
			case 'input-close':
			case 'content-close':
				$content = '</div>';
				break;

			//! Not used.
			// case 'checkbox':
			// 	$content = sprintf( '<div class="tsf-checkbox-wrapper">%s</div>', $content );
			// 	break;

			default:
				break;
		endswitch;

		return $content;
	}
}
