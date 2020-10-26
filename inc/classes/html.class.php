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
 * Class TSF_Extension_Manager\HTML.
 *
 * Puts elements in common HTML wraps.
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
final class HTML {
	use Construct_Core_Static_Final;

	/**
	 * Wraps tooltip item in wrapper.
	 *
	 * @since 1.5.0
	 * @since 2.0.2 Now uses tsfTT compatible classes.
	 *
	 * @param string $content The content to wrap.
	 * @param array  $classes The classes for the tooltip to have.
	 */
	public static function wrap_inline_tooltip( $content, array $classes = [] ) {
		$classes[] = 'tsf-tooltip-wrap';
		return vsprintf(
			'<span class="%s">%s</span>',
			[
				implode( ' ', $classes ),
				$content,
			]
		);
	}

	/**
	 * Makes working tooltip item when titles exists in a question mark.
	 * Otherwise, it simply makes a question mark.
	 *
	 * @since 1.5.0
	 *
	 * @param string $title      The title displayed when JS is disabled.
	 *                           Also functions as tooltip (without HTML) if $title_html
	 *                           is omitted.
	 * @param string $title_html The definite tooltip, may contain HTML. Optional.
	 */
	public static function make_inline_question_tooltip( $title, $title_html = '' ) {
		return static::wrap_inline_tooltip(
			static::make_inline_tooltip( '', $title, $title_html, [ 'tsfem-dashicon', 'tsfem-unknown' ] )
		);
	}

	/**
	 * Makes tooltip item when titles exists.
	 *
	 * @since 1.5.0
	 * @since 2.0.2 Now uses tsfTT compatible classes.
	 * @since 2.1.0 Now added a tabindex for keyboard navigation.
	 *
	 * @param string $content    The content within the wrap. Must be escaped.
	 * @param string $title      The title displayed when JS is disabled.
	 *                           Also functions as tooltip (without HTML) if $title_html
	 *                           is omitted.
	 * @param string $title_html The definite tooltip, may contain HTML. Optional.
	 * @param array  $classes    The additional tooltip classes.
	 */
	public static function make_inline_tooltip( $content, $title, $title_html = '', array $classes = [] ) {

		$title      = \esc_attr( \wp_strip_all_tags( $title ) );
		$title_html = $title_html ? sprintf( 'data-desc="%s"', \esc_attr( \esc_html( $title_html ) ) ) : '';

		$tabindex = false;

		if ( \strlen( $title . $title_html ) ) {
			$classes[] = 'tsf-tooltip-item';
			$tabindex  = true;
		}

		return vsprintf(
			'<span class="%s" title="%s" %s %s>%s</span>',
			[
				implode( ' ', $classes ),
				$title,
				$title_html,
				$tabindex ? 'tabindex=0' : '',
				$content,
			]
		);
	}

	/**
	 * Makes a dropdown options list from input.
	 *
	 * @since 1.5.0
	 *
	 * @param array  $options : {
	 *    'value' (string) => The option value
	 *    'name' (string)  => The option name,
	 * }
	 * @param string $selected The currently selected value.
	 * @return string The formatted options list.
	 */
	public static function make_dropdown_option_list( array $options, $selected = '' ) {

		$out = '';

		$selected = (string) $selected;
		foreach ( $options as $entry ) {
			$value = \esc_attr( $entry['value'] );
			$out  .= sprintf(
				'<option value="%s"%s>%s</option>',
				$value,
				$value === $selected ? ' selected' : '',
				\esc_html( $entry['name'] )
			);
		}

		return $out;
	}

	/**
	 * Makes a sequential dropdown options list from input.
	 *
	 * @since 1.5.0
	 *
	 * @param array $options : sequential {
	 *    'name' (string)  => The option name,
	 * }
	 * @param int   $selected The currently selected value.
	 * @return string The formatted options list.
	 */
	public static function make_sequential_dropdown_option_list( array $options, $selected = 0 ) {

		$_options = [];

		$i = 0;
		foreach ( $options as $key => $entry ) {
			$_options[ $key ]['value'] = $i;
			$_options[ $key ]['name']  = $entry['name'];
			$i++;
		}

		return static::make_dropdown_option_list( $_options, $selected ?: 0 );
	}

	/**
	 * Makes either simple or JSON-encoded data-* attributes for HTML elements.
	 *
	 * Converts CamelCase to dash-case when needed.
	 * Data value may be anything, and is JSON encoded. Use jQuery.data() to extract.
	 *
	 * @since 1.5.0
	 *
	 * @param array $data : {
	 *    string $k => mixed $v
	 * }
	 * @return string The HTML data attributes, with added space to the start.
	 */
	public static function make_data_attributes( array $data ) {

		$ret = [];

		foreach ( $data as $k => $v ) {
			if ( ! is_scalar( $v ) ) {
				$ret[] = sprintf(
					'data-%s="%s"',
					strtolower( preg_replace(
						'/([A-Z])/',
						'-$1',
						preg_replace( '/[^a-z0-9_\-]/i', '', $k )
					) ), // dash case.
					htmlspecialchars( json_encode( $v, JSON_UNESCAPED_SLASHES ), ENT_COMPAT, 'UTF-8' )
				);
			} else {
				$ret[] = sprintf(
					'data-%s="%s"',
					strtolower( preg_replace(
						'/([A-Z])/',
						'-$1',
						preg_replace( '/[^a-z0-9_\-]/i', '', $k )
					) ), // dash case.
					\esc_attr( $v )
				);
			}
		}

		return ' ' . implode( ' ', $ret );
	}
}
