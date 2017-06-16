<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Traits
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds settings generator functions for package TSF_Extension_Manager\Extension\Local.
 *
 * Not according to DRY standards for improved performance.
 *
 * This trait has dependencies!
 *
 * @since 1.0.0
 * @access private
 * @uses trait TSF_Extension_Manager\Extension_Options
 * @see TSF_Extension_Manager\Traits\Extension_Options
 * @uses trait TSF_Extension_Manager\Traits\UI
 * @see TSF_Extension_Manager\Traits\UI
 */
 // impements \Generator ??
trait Settings_Generator {
	// Load Instance type.. set as class...?

	private $bits = 12, // Should be and changeable depending on "!!pre-set" depth (i.e. floor( $os_bits / $depth ))
	        $level = 0,
	        $it = 0,
	        $level_names = [];

	private $o_key = '',
	        $has_o_key = false;

	/**
	 * Sanitizeses ID. Mainly removing spaces and coding characters.
	 *
	 * Unlike sanitize_key(), it doens't alter the case nor applies filters.
	 *
	 * @see WordPress Core sanitize_key()
	 * @since 1.0.0
	 *
	 * @param string $id The unsanitized ID.
	 * @return string The sanitized ID.
	 */
	public function sanitize_id( $id ) {
		return preg_replace( '/[^a-zA-Z0-9_\-]/', '', $id );
	}

	/**
	 * Returns field name and ID attributes for form fields.
	 *
	 * @since 1.0.0
	 * @uses TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @uses $this->o_key
	 * @access private
	 *
	 * @param string $option The option the field is for.
	 * @return string Full field ID/name attribute.
	 */
	private function create_field_id( $option ) {

		$option = $this->sanitize_id( $option );

		if ( $this->has_o_key ) {
			$k = sprintf( '%s[%s][%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $this->o_key );
		} else {
			$k = sprintf( '%s[%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index );
		}

		//* Correct the length of bits, split them and put them in the right order.
		$_f = sprintf( '%%0%db', ( $this->level * $this->bits ) );
		$levels = array_reverse( str_split( sprintf( $_f, $this->it ), $this->bits ) );

		$i = 0;
		foreach ( $levels as $b ) {
			$k = sprintf( '%s[%s]', $k, $this->level_names[ $i ] );
			//= Only grab iterators.
			if ( $b > 1 ) {
				$k = sprintf( '%s[%d]', $k, bindec( $b ) - 1 );
			}
			$i++;
		}

		return $k;
	}

	/**
	 * @param string $o_key The key given to the option. For when you want to prevent option collision.
	 */
	public function _fields( array $fields, $type = 'echo', $o_key = '' ) {

		$this->o_key = $this->sanitize_id( $o_key );
		$this->has_o_key = (bool) $this->o_key;

		// forclass :;
		// $this->o_index = $o_index;
		// $this->is_64 = \the_seo_framework()->is_64();
		// IF NOT 64, max IT is 64 (bits = 6)... otherwise max IT is 4096 (bits = 12)... save as option?

		if ( 'get' === $type )
			return $this->get_fields( $fields );

		$this->output_fields( $fields );
	}

	/**
	 * Gets fields by references.
	 *
	 * @since 1.0.0
	 * @see http://php.net/manual/en/language.references.return.php
	 * @uses $this->generate_fields()
	 *
	 * @param array $fields. Passed by reference for performance.
	 * @return string $_fields; Passed by reference to allow field and sequence stacking.
	 */
	private function &get_fields( array &$fields ) {

		$_fields = '';

		foreach ( $this->generate_fields( $fields ) as $field ) {
			//* Already escaped.
			$_fields .= $field;
		}

		return $_fields;
	}

	/**
	 *
	 *
	 * @param array $fields. Passed by reference for performance.
	 */
	private function output_fields( array &$fields ) {
		foreach ( $this->generate_fields( $fields ) as $field ) {
			//* Already escaped.
			echo $field;
		}
	}

	/**
	 * Generates fields.
	 *
	 * @since 1.0.0
	 * @uses $this->create_field()
	 * @generator
	 *
	 * @param array $fields The fields with sequence [ option => args ].
	 * @yields Field based on input.
	 */
	private function generate_fields( array $fields ) {

		//= Store first key, to be caught later when iterating.
		$this->level_names[ $this->level ] = key( $fields );

		/**
		 * Pass down option level as main level.
		 * Because it allows for 6 bits setting, each loop can iterate at 64
		 * options for each depth (ie hex).
		 * Maximum of depth of 5 @ 32 bit. 10 @ 64 bits.
		 */
		++$this->level;
		$this->iterate(); // This works.

		foreach ( $fields as $option => $_args ) {
			//= Overwrite later keys, to be caught when generating IDs
			$this->level_names[ $this->level - 1 ] = $option;

			//$this->iterate(); // This is more robust, but we already check the options by name.
			yield $this->create_field( $option, $_args );
		}

		$this->deiterate();
	}

	private function iterate() {
		//* Add 1 to current level.
		$this->it += ( 1 << ( ( $this->level - 1 ) * $this->bits ) );
	}

	private function deiterate() {
		//* Unset last level.
		$this->it &= ~( ( pow( 2, $this->bits ) - 1 ) << ( $this->bits * ( $this->level - 1 ) ) );
		unset( $this->level_names[ $this->level ] );
		--$this->level;
	}

	private function create_field( $option, array $args ) {

		if ( empty( $args['_edit'] ) )
			return '';

		$this->clean_list_index( $args );
		$this->clean_desc_index( $args['_desc'] );

		switch ( $args['_type'] ) :
			case 'multi' :
				return $this->create_fields_multi( $option, $args );
				break;

			case 'iterate_main' :
				//= Can only be used on main output field. Will echo. Will try to defer.
				return $this->fields_iterator( $option, $args, 'echo' );
				break;

			case 'iterate' :
				return $this->fields_iterator( $option, $args, 'return' );
				break;

			case 'select' :
				return $this->create_select_field( $option, $args );
				break;

			case 'text' :
			case 'password' :
			case 'tel' :
			case 'url' :
			case 'search' :
			case 'time' :
			case 'week' :
			case 'month' :
			case 'datetime-local' :
			case 'date' :
			case 'number' :
			case 'range' :
			case 'color' :
			case 'hidden' :
				return $this->create_input_field_by_type( $option, $args );
				break;

			case 'textarea' :
				return $this->create_textarea_field( $option, $args );
				break;

			case 'checkbox' :
				return $this->create_checkbox_field( $option, $args );
				break;

			case 'radio' :
				return $this->create_radio_field( $option, $args );
				break;

			case 'image' :
				return $this->create_image_field( $option, $args );
				break;

			default :
				break;
		endswitch;

		return '';
	}

	/**
	 * @see $this->create_field()
	 */
	private function create_fields_multi( $option, array $args ) {

		$this->clean_desc_index( $args['_desc'] );
		$title = $args['_desc'][0];
		$desc  = $args['_desc'][1];

		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-e-local-multi-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-e-local-multi-setting-label tsfem-flex" id="%s">%s</div>',
					$this->create_field_id( $option ),
					vsprintf(
						'<div class="tsfem-e-local-multi-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<div class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></div>',
								[
									sprintf( 'tsfem-e-local-option-title%s', ( $s_desc ? ' tsfem-e-local-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-e-local-multi-setting-input tsfem-flex">%s</div>',
					$this->get_fields( $args['_fields'] )
				),
			]
		);
	}

	/**
	 * @see $this->create_field()
	 */
	private function fields_iterator( $option, array $args, $type = 'echo' ) {

		$o = '';

		if ( 'echo' === $type ) {
			$this->output_fields_iterator( $option, $args );
		} else {
			$o = $this->get_fields_iterator( $option, $args );
		}

		return $o;
	}

	private function output_fields_iterator( $option, array $args ) {

		echo '<div class="tsfem-e-local-iterator-setting tsfem-flex">';

		$it_option_key = key( $args['_iterate_selector'] );
		//* The selector.
		echo $this->create_field( $it_option_key, $args['_iterate_selector'][ $it_option_key ] );

		//* 3 === TEMPORARILY var_dump() remove 3...
		$count = $this->get_field_value( $it_option_key, $args['_iterate_selector'][ $it_option_key ]['_default'] | 7 );

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = isset( $args['_iterator_title'][1] ) ? $args['_iterator_title'][1] : $_it_title_main;

		$defer = $count > 6; // Set to 5 when we add a save-menu?
		$_id = $this->create_field_id( $it_option_key );

		//* Already escaped.
		$defer and printf( '<div class="tsfem-flex-status-loading tsfem-flex tsfem-flex-center" id="%s-loader" style=padding-top:4vh><span></span></div>', $_id );

		//* Already escaped.
		printf(
			'<div class="tsfem-e-local-collapse-wrap tsfem-e-local-collapse-sub-wrap" id="%s-wrapper"%s>',
			$_id,
			( $defer ? ' style=display:none' : '' )
		);

		for ( $it = 0; $it < $count; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? sprintf( $_it_title, $it + 1 ) : sprintf( $_it_title_main, $it + 1 );

			$this->iterate();
			//* Already escaped.
			echo $this->get_collapse_wrap( 'start', $_title, $this->create_field_id( $it_option_key ) );
			$this->output_fields( $args['_fields'], $_title );
			//* Already escaped.
			echo $this->get_collapse_wrap( 'end' );
		}

		echo '</div>';

		//* Already escaped.
		$defer and printf(
			'<script>window.onload=function(){var a=document.getElementById("%1$s-loader");a.parentNode.removeChild(a);document.getElementById("%1$s-wrapper").style=null;};</script>',
			$_id
		);
	}

	private function get_fields_iterator( $option, array $args ) {

		$it_option_key = key( $args['_iterate_selector'] );
		$selector = $this->create_field( $it_option_key, $args['_iterate_selector'][ $it_option_key ] );

		//* 2 === TEMPORARILY var_dump() remove 2...
		$count = $this->get_field_value( $it_option_key, $args['_iterate_selector'][ $it_option_key ]['_default'] | 2 );

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = isset( $args['_iterator_title'][1] ) ? $args['_iterator_title'][1] : $_it_title_main;

		$_fields = '';
		for ( $it = 0; $it < $count; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? sprintf( $_it_title, $it + 1 ) : sprintf( $_it_title_main, $it + 1 );

			$this->iterate();
			$_fields .= $this->get_collapse_wrap( 'start', $_title, $this->create_field_id( $it_option_key ) );
			$_fields .= $this->get_fields( $args['_fields'] );
			$_fields .= $this->get_collapse_wrap( 'end' );
		}

		return vsprintf(
			'<div class="tsfem-e-local-iterator-setting tsfem-flex">%s%s</div>',
			[
				$selector,
				sprintf(
					'<div class="tsfem-e-local-collapse-wrap tsfem-e-local-collapse-sub-wrap" id="%s">%s</div>',
					$this->create_field_id( $it_option_key ),
					$_fields
				),
			]
		);
	}

	private function get_collapse_wrap( $what, $title = '', $id = '' ) {

		if ( 'start' === $what ) {
			$s_id = $id ? sprintf( 'id="tsfem-e-local-collapse-%s"', $id ) : '';

			$checkbox_id = sprintf( 'tsfem-e-local-collapse-checkbox-%s', $id );
			$checkbox = sprintf( '<input type="checkbox" id="%s" checked>', $checkbox_id );

			//* Requires JS to edit. Always start with warning.
			$state = 'warning';
			$title = $this->get_state_icon( $state ) . \esc_html( $title );

			$title = sprintf( '<h3 class="tsfem-e-local-collapse-title">%s</h3>', $title );
			$icon = sprintf( '<span class="tsfem-e-local-collapse-icon tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-e-local-icon-%s"></span>', $state );

			$header = vsprintf( '<label class="tsfem-e-local-collapse-header tsfem-flex tsfem-flex-row tsfem-flex-nowrap tsfem-flex-nogrow tsfem-flex-space" for="%s">%s%s</label>',
				[
					$checkbox_id,
					$title,
					$icon,
				]
			);

			$content_start = '<div class="tsfem-e-local-collapse-content">';

			return sprintf( '<div class="tsfem-e-local-collapse" %s>%s%s%s', $s_id, $checkbox, $header, $content_start );
		} elseif ( 'end' === $what ) {
			//* ok.
			return '</div></div>';
		}

		return '';
	}


	/**
	 * Creates a JS and no-JS compatible description mark.
	 *
	 * @since 1.0.0
	 *
	 * @param string $description The description.
	 * @return string The escaped inline HTML description output.
	 */
	private function create_fields_sub_description( $description ) {
		return vsprintf(
			'<span class="tsfem-has-hover-balloon" title="%s" data-desc="%s"><span>%s</span></span>',
			[
				\esc_attr( $description ),
				\esc_html( $description ),
				'<span class="tsfem-extension-description-icon tsfem-dashicon tsfem-unknown"></span>',
			]
		);
	}

	/**
	 * Creates a description block.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $description The description field(s).
	 * @return string The escaped flex HTML description output.
	 */
	private function create_fields_description( $description ) {

		if ( is_scalar( $description ) ) {
			return sprintf(
				'<span class="tsfem-e-local-option-description">%s</span>',
				\esc_html( $description )
			);
		} else {
			$ret = '';
			foreach ( $description as $desc ) {
				$ret .= $this->create_fields_description( $desc );
			}
			return $ret;
		}
	}

	private function get_field_value( $option, $default = null ) {

		if ( $this->has_o_key ) {
			$_options = $this->get_option( $this->o_key );
			return isset( $_options[ $option ] ) ? $_options[ $option ] : $default;
		}

		return $this->get_option( $option, $default );
	}

	//* TEMP. Simply put, all args need to be filled in correctly prior to running this to improve performance.
	private function clean_list_index( array &$args ) {
		$args['_type']    = isset( $args['_type'] )    ? (string) $args['_type']    : '';
		$args['_default'] = isset( $args['_default'] ) ? (string) $args['_default'] : '';
		$args['_ph']      = isset( $args['_ph'] )      ? (string) $args['_ph']      : '';
		$args['_ret']     = isset( $args['_ret'] )     ? (string) $args['_ret']     : '';
		$args['_req']     = isset( $args['_req'] )     ? (bool) $args['_req']       : false;
		$args['_edit']    = isset( $args['_edit'] )    ? (bool) $args['_edit']      : false;
		$args['_desc']    = isset( $args['_desc'] )    ? (array) $args['_desc']     : [];
		$args['_range']   = isset( $args['_range'] )   ? (array) $args['_range']    : [];
		$args['_fields']  = isset( $args['_fields'] )  ? (array) $args['_fields']   : [];
		$args['_dd']      = isset( $args['_dd'] )      ? (array) $args['_dd']       : [];
	}

	private function clean_desc_index( array &$desc ) {
		$desc[0] = isset( $desc[0] ) ? $desc[0] : '';
		$desc[1] = isset( $desc[1] ) ? $desc[1] : '';
		$desc[2] = isset( $desc[2] ) ? $desc[2] : '';
	}

	//* Cleans range, including steps @ 1e-10
	private function clean_range_index( array &$range ) {
		$range[0] = isset( $range[0] ) ? (string) $range[0] : '';
		$range[1] = isset( $range[1] ) ? (string) $range[1] : '';
		$range[2] = isset( $range[2] ) ? (string) rtrim( sprintf( '%.10F', $range[2] ), '.0' ) : '';
	}

	/**
	 * Accepted types... TODO
	 */
	private function create_input_field_by_type( $option, array $args ) {

		switch ( $args['_type'] ) :

			case 'date' :
			case 'number' :
			case 'range' :
				$this->clean_range_index( $args['_range'] );

				$s_range = '';
				$s_range .= '' !== $args['_range'][0] ? sprintf( 'min=%s', $args['_range'][0] ) : '';
				$s_range .= '' !== $args['_range'][1] ? sprintf( ' max=%s', $args['_range'][1] ) : '';
				$s_range .= '' !== $args['_range'][2] ? sprintf( ' step=%s', $args['_range'][2] ) : '';
				break;

			case 'color' :
				// TODO
				break;

			default :
			case 'text' :
			case 'password' :
			case 'tel' :
			case 'url' :
			case 'search' :
			case 'time' :
			case 'week' :
			case 'month' :
			case 'datetime-local' :
			case 'hidden' :
				//= Look behind you.
				break;
		endswitch;

		//* Not escaped.
		$title = $args['_desc'][0];

		// Escaped.
		$s_type = \esc_attr( $args['_type'] );
		$s_name = $s_id = $this->create_field_id( $option );
		$s_ph   = $args['_ph'] ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';
		$s_range = isset( $s_range ) ? $s_range : '';

		return vsprintf(
			'<div class="tsfem-%s-field-wrapper tsfem-e-local-flex-setting tsfem-flex">%s%s</div>',
			[
				$s_type,
				sprintf(
					'<div class="tsfem-e-local-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-e-local-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf( 'tsfem-e-local-option-title%s', ( $s_desc ? ' tsfem-e-local-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-e-local-flex-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<input type=%s id="%s" name=%s value="%s" %s %s>',
						[
							$s_type,
							$s_id,
							$s_name,
							\esc_attr( $args['_default'] ),
							$s_range,
							$s_ph,
						]
					)
				),
			]
		);
	}

	private function create_textarea_field( $option, array $args ) {}
	private function create_select_field( $option, array $args ) {

		//* Not escaped.
		$title = $args['_desc'][0];

		$s_name = $s_id = $this->create_field_id( $option );
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-select-field-wrapper tsfem-e-local-flex-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-e-local-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-e-local-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf( 'tsfem-e-local-option-title%s', ( $s_desc ? ' tsfem-e-local-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-e-local-flex-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<select id="%s" name=%s>%s</select>',
						[
							$s_id,
							$s_name,
							$this->get_select_options( $args['_select'], $args['_default'] ),
						]
					)
				),
			]
		);
	}

	private function get_select_options( array $options, $default = '' ) {

		$_fields = '';

		foreach ( $this->generate_select_fields( $options, $default ) as $field ) {
			//* Already escaped.
			$_fields .= $field;
		}

		return $_fields;
	}

	/**
	 * Heavily optimized for performance.
	 *
	 * @generator
	 */
	private function generate_select_fields( array $fields, $default = '' ) {

		static $_level = 0;

		if ( '' !== $default ) :
			foreach ( $fields as $args ) :

				if ( $_level ) {
					//* Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '― ' . $args[1] : str_repeat( '― ', $_level ) . $args[1];

					//= `&8213; `... gets escaped otherwise.
					$args[1] = '― ' . $args[1];
				}

				if ( isset( $args[2] ) ) {
					yield sprintf( '<optgroup label=%s>', $args[1] );
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					$_level++;
					yield $this->get_select_options( $args[2] );
					$_level--;
					yield '</optgroup>';
				} else {
					if ( $args[0] === $default ) {
						yield sprintf( '<option value="%s" selected>%s</option>', $args[0], $args[1] );
					} else {
						yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					}
				}
			endforeach;
		else :
			foreach ( $fields as $args ) :

				if ( $_level ) {
					//* Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '― ' . $args[1] : str_repeat( '― ', $_level ) . $args[1];

					//= `&8213; `... gets escaped otherwise.
					$args[1] = '― ' . $args[1];
				}

				if ( isset( $args[2] ) ) {
					yield sprintf( '<optgroup label="%s">', $args[1] );
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					$_level++;
					yield $this->get_select_options( $args[2] );
					$_level--;
					yield '</optgroup>';
				} else {
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
				}
			endforeach;
		endif;

		$level = 0;
	}

	private function create_checkbox_field( $option, array $args ) {}
	private function create_radio_field( $option, array $args ) {}

	/**
	 *
	 * @see _wp_ajax_crop_image() The AJAX cropper callback.
	 */
	private function create_image_field( $option, array $args ) {

		/**
		 * @uses trait TSF_Extension_Manager\UI
		 * @package TSF_Extension_Manager\Traits
		 */
		$this->register_media_scripts();

		//* Not escaped.
		$title = $args['_desc'][0];

		// Escaped.
		$s_type = \esc_attr( $args['_type'] );
		$s_name = $s_id = $this->create_field_id( $option );
		$s_ph   = $args['_ph'] ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-image-field-wrapper tsfem-e-local-flex-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-e-local-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-e-local-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf(
										'tsfem-e-local-option-title%s',
										( $s_desc ? ' tsfem-e-local-option-has-description' : '' )
									),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				vsprintf(
					'<div class="tsfem-e-local-flex-setting-input tsfem-flex">%s<div class="tsfem-e-local-image-buttons-wrap tsfem-flex tsfem-flex-row tsfem-hide-if-no-js">%s</div></div>',
					[
						vsprintf(
							'<input type=url id="%s" name=%s value="%s" %s>',
							[
								$s_id,
								$s_name,
								\esc_attr( $args['_default'] ),
								$s_ph,
							]
						),
						vsprintf(
							'<button class="tsfem-set-image-button tsfem-button-primary tsfem-button-primary-bright tsfem-button-small" data-href="%1$s" title="%2$s" id="%3$s-select" data-inputid="%3$s">%4$s</button>',
							[
								\esc_url( \get_upload_iframe_src( 'image', -1, null ) ),
								\esc_attr_x( 'Select image', 'Button hover title', '' ),
								$s_id,
								\esc_html__( 'Select Image', '' ),
							]
						),
					]
				),
			]
		);
	}
}
