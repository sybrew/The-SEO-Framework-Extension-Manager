<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
 * Holds settings generator functions for package TSF_Extension_Manager\Extension.
 *
 * Not according to DRY standards for improved performance.
 *
 * @since 1.3.0
 * @access private
 * @uses trait TSF_Extension_Manager\Extension_Options
 * @see TSF_Extension_Manager\Traits\Extension_Options
 */
class FormGenerator {
	use \TSF_Extension_Manager\Extension_Options;

	/**
	 * Holds the bits and maximum iterations thereof.
	 *
	 * @since 1.3.0
	 *
	 * @var int $bits
	 * @var int $max_it
	 */
	public $bits = 12,
	       $max_it = 0;

	/**
	 * Maintains the reiteration level, the name thereof, and the iteration within.
	 *
	 * NOTE: $it should not ever exceed $max_it.
	 * JavaScript should enforce values. POST even more so, actually.
	 *
	 * @since 1.3.0
	 *
	 * @var int   $level
	 * @var array $level_names
	 * @var int   $it
	 */
	private $level = 0,
	        $level_names = [],
	        $it = 0;

	/**
	 * Maintains the option key, and the boolean value thereof.
	 *
	 * @since 1.3.0
	 *
	 * @var int $bits
	 * @var int $max_it
	 */
	private $o_key = '',
	        $has_o_key = false;

	/**
	 * Constructor. Sets up class.
	 *
	 * @since 1.3.0
	 *
	 * We could expect users to upgrade from 32 bits to 64 bits. That is fine.
	 * But a downgrade will be very unlikely. We're not going to defensively program
	 * for it and it will be and stay a "wontfix bug".
	 *
	 * @param array $args Passed by reference : {
	 *   string 'o_index'  : Required. The option index field for storing extension options.
	 *   string 'o_key'    : The pre-assigned option key. Great for when working
	 *                       with multiple option fields.
	 *   int 'level_depth' : Set how many levels the options can traverse.
	 *                       e.g. 5 depth @ 64 bits => 12 bits =>> 12 bits === 4096 iterations.
	 *                       e.g. 5 depth @ 32 bits =>  6 bits =>>  6 bits ===   64 iterations.
	 *   int 'architecture' : The amount of bits to work with. If unassigned, it will autodetermine.
	 * }
	 * @return \TSF_Extension_Manager\Settings_Generator $this
	 */
	public function __construct( &$args ) {

		empty( $args['o_index'] ) and \wp_die( __METHOD__ . ' is very angry: Assign o_index.' );

		/**
		 * @see trait \TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = $args['o_index'];

		$defaults = [
			'o_key' => '',
			'levels' => 5,
			'architecture' => null,
		];
		$args = array_merge( $defaults, $args );

		$args['architecture'] = $args['architecture'] ?: ( \tsf_extension_manager()->is_64() ? 64 : 32 );

		$this->bits = floor( $args['architecture'] / $args['levels'] );
		$this->max_it = pow( 2, $this->bits );

		$this->o_key = $args['o_key'] = $this->sanitize_id( $args['o_key'] );
		$this->has_o_key = (bool) $this->o_key;

	}

	public function _form_wrap( $what, $url = '', $type = 'echo' ) {

		if ( 'get' === $type )
			return $this->get_form_wrap( $what, $url );

		//* Already escaped.
		echo $this->get_form_wrap( $what, $url );
	}

	private function get_form_wrap( $what, $url ) {

		switch ( $what ) :
			case 'start' :
				return vsprintf(
					'<form action="%s" method=post id="%s" class="tsfem-form">',
					[
						\esc_url( $url ),
						$this->get_form_id(),
					]
				);
				break;

			case 'end' :
				return '</form>';
				break;

			default;
		endswitch;

		return '';
	}

	/**
	 * @param string $o_key The key given to the option. For when you want to prevent option collision.
	 */
	public function _fields( array $fields, $type = 'echo', $o_key = '' ) {

		if ( 'get' === $type )
			return $this->get_fields( $fields );

		$this->output_fields( $fields );
	}

	/**
	 * Sanitizeses ID. Mainly removing spaces and coding characters.
	 *
	 * Unlike sanitize_key(), it doens't alter the case nor applies filters.
	 *
	 * @see WordPress Core sanitize_key()
	 * @since 1.3.0
	 *
	 * @param string $id The unsanitized ID.
	 * @return string The sanitized ID.
	 */
	private function sanitize_id( $id ) {
		return preg_replace( '/[^a-zA-Z0-9_\-]/', '', $id );
	}

	/**
	 * Returns form ID attribute for form wrap.
	 *
	 * @since 1.3.0
	 * @uses TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @uses $this->o_key
	 * @access private
	 *
	 * @return string Full form ID attribute.
	 */
	private function get_form_id() {

		if ( $this->has_o_key ) {
			$k = sprintf( '%s[%s][%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $this->o_key );
		} else {
			$k = sprintf( '%s[%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index );
		}

		return $k;
	}

	/**
	 * Returns field name and ID attributes for form fields.
	 *
	 * @since 1.3.0
	 * @uses TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @uses $this->o_key
	 * @access private
	 *
	 * @return string Full field ID/name attribute.
	 */
	private function get_field_id() {

		if ( $this->has_o_key ) {
			$k = sprintf( '%s[%s][%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $this->o_key );
		} else {
			$k = sprintf( '%s[%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index );
		}

		//= Correct the length of bits, split them and put them in the right order.
		$_f = sprintf( '%%0%db', ( $this->level * $this->bits ) );
		$levels = array_reverse( str_split( sprintf( $_f, $this->it ), $this->bits ) );

		$i = 0;
		foreach ( $levels as $b ) {
			$k = sprintf( '%s[%s]', $k, $this->sanitize_id( $this->level_names[ $i ] ) );
			//= Only grab iterators, they start at 2.
			if ( $b > 1 ) {
				$k = sprintf( '%s[%d]', $k, bindec( $b ) - 1 );
			}
			$i++;
		}

		return $k;
	}

	/**
	 * Gets fields by references.
	 *
	 * @since 1.3.0
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
	 * @since 1.3.0
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
		$this->iterate();

		foreach ( $fields as $option => $_args ) {
			//= Overwrite later keys, to be caught when generating IDs
			$this->level_names[ $this->level - 1 ] = $option;

			yield $this->create_field( $_args );
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

	private function create_field( array $args ) {

		if ( empty( $args['_edit'] ) )
			return '';

		$this->clean_list_index( $args );
		$this->clean_desc_index( $args['_desc'] );

		switch ( $args['_type'] ) :
			case 'multi' :
				return $this->create_fields_multi( $args );
				break;

			case 'iterate_main' :
				//= Can only be used on main output field. Will echo. Will try to defer.
				return $this->fields_iterator( $args, 'echo' );
				break;

			case 'iterate' :
				return $this->fields_iterator( $args, 'return' );
				break;

			case 'select' :
			case 'selectmulti' :
				return $this->create_select_field( $args );
				break;

			case 'selectmultia11y' :
				//= Select field, but then through checkboxes.
				return $this->create_select_multi_a11y_field( $args );
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
				return $this->create_input_field_by_type( $args );
				break;

			case 'textarea' :
				return $this->create_textarea_field( $args );
				break;

			case 'checkbox' :
				return $this->create_checkbox_field( $args );
				break;

			case 'radio' :
				return $this->create_radio_field( $args );
				break;

			case 'image' :
				return $this->create_image_field( $args );
				break;

			default;
		endswitch;

		return '';
	}

	/**
	 * @see $this->create_field()
	 */
	private function create_fields_multi( array $args ) {

		$this->clean_desc_index( $args['_desc'] );
		$title = $args['_desc'][0];
		$desc  = $args['_desc'][1];

		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-form-multi-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-form-multi-setting-label tsfem-flex" id="%s">%s</div>',
					$this->get_field_id(),
					vsprintf(
						'<div class="tsfem-form-multi-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<div class="tsfem-form-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></div>',
								[
									sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-form-multi-setting-input tsfem-flex">%s</div>',
					$this->get_fields( $args['_fields'] )
				),
			]
		);
	}

	/**
	 * @see $this->create_field()
	 */
	private function fields_iterator( array $args, $type = 'echo' ) {

		$o = '';

		if ( 'echo' === $type ) {
			$this->output_fields_iterator( $args );
		} else {
			$o = $this->get_fields_iterator( $args );
		}

		return $o;
	}

	/**
	 * Sets max iterations based on bits and current value.
	 *
	 * <del>Allows 0. However, '' and null will be converted.</del>
	 * Empty values will be converted to max it.
	 *
	 * @since 1.3.0
	 * @param int $max The maximum value. Passed by reference.
	 */
	private function set_max_iterations( &$max ) {

		// if ( 0 !== $max ) {
			if ( ! $max || $max > $this->max_it ) {
				$max = $this->max_it;
			}
		// }
	}

	private function output_fields_iterator( array $args ) {

		echo '<div class="tsfem-form-iterator-setting tsfem-flex">';

		$it_option_key = key( $args['_iterate_selector'] );
		//* Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		//= The selector. Already escaped.
		echo $this->create_field( $args['_iterate_selector'][ $it_option_key ] );

		//* 3 === TEMPORARILY var_dump() remove 3...
		$count = $this->get_field_value( $args['_iterate_selector'][ $it_option_key ]['_default'] | 4 );

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = isset( $args['_iterator_title'][1] ) ? $args['_iterator_title'][1] : $_it_title_main;

		$defer = $count > 6; // Set to 5 when we add a save-menu?
		$_id = $this->get_field_id();

		//* Already escaped.
		$defer and printf( '<div class="tsfem-flex-status-loading tsfem-flex tsfem-flex-center" id="%s-loader" style=padding-top:4vh><span></span></div>', $_id );

		//* Already escaped.
		printf(
			'<div class="tsfem-form-collapse-wrap tsfem-form-collapse-sub-wrap" id="%s-wrapper"%s>',
			$_id,
			( $defer ? ' style=display:none' : '' )
		);

		for ( $it = 0; $it < $count; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? sprintf( $_it_title, $it + 1 ) : sprintf( $_it_title_main, $it + 1 );

			$this->iterate();
			//* Already escaped.
			echo $this->get_collapse_wrap( 'start', $_title, $this->get_field_id() );
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

	private function get_fields_iterator( array $args ) {

		$it_option_key = key( $args['_iterate_selector'] );

		//* Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		$selector = $this->create_field( $args['_iterate_selector'][ $it_option_key ] );

		//* 2 === TEMPORARILY var_dump() remove 2...
		$count = $this->get_field_value( $args['_iterate_selector'][ $it_option_key ]['_default'] | 2 );

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = isset( $args['_iterator_title'][1] ) ? $args['_iterator_title'][1] : $_it_title_main;

		$_fields = '';
		for ( $it = 0; $it < $count; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? sprintf( $_it_title, $it + 1 ) : sprintf( $_it_title_main, $it + 1 );

			$this->iterate();
			$_fields .= $this->get_collapse_wrap( 'start', $_title, $this->get_field_id() );
			$_fields .= $this->get_fields( $args['_fields'] );
			$_fields .= $this->get_collapse_wrap( 'end' );
		}

		return vsprintf(
			'<div class="tsfem-form-iterator-setting tsfem-flex">%s%s</div>',
			[
				$selector,
				sprintf(
					'<div class="tsfem-form-collapse-wrap tsfem-form-collapse-sub-wrap" id="%s">%s</div>',
					$this->get_field_id(),
					$_fields
				),
			]
		);
	}

	private function get_collapse_wrap( $what, $title = '', $id = '' ) {

		if ( 'start' === $what ) {
			$s_id = $id ? sprintf( 'id="tsfem-form-collapse-%s"', $id ) : '';

			$checkbox_id = sprintf( 'tsfem-form-collapse-checkbox-%s', $id );
			$checkbox = sprintf( '<input type="checkbox" id="%s" checked>', $checkbox_id );

			//* Requires JS to edit. Always start with warning.
			$state = 'warning';
			$title = $this->get_state_icon( $state ) . \esc_html( $title );

			$title = sprintf( '<h3 class="tsfem-form-collapse-title">%s</h3>', $title );
			$icon = sprintf( '<span class="tsfem-form-collapse-icon tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-form-icon-%s"></span>', $state );

			$header = vsprintf( '<label class="tsfem-form-collapse-header tsfem-flex tsfem-flex-row tsfem-flex-nowrap tsfem-flex-nogrow tsfem-flex-space" for="%s">%s%s</label>',
				[
					$checkbox_id,
					$title,
					$icon,
				]
			);

			$content_start = '<div class="tsfem-form-collapse-content">';

			return sprintf( '<div class="tsfem-form-collapse" %s>%s%s%s', $s_id, $checkbox, $header, $content_start );
		} elseif ( 'end' === $what ) {
			//* ok.
			return '</div></div>';
		}

		return '';
	}

	/**
	 * Parses entry state HTMl icon.
	 *
	 * @since 1.3.0
	 *
	 * @param string $state The icon state.
	 * @return string The HTML formed entry state icon.
	 */
	protected function get_state_icon( $state = '' ) {
		return sprintf( '<span class="tsfem-e-local-title-icon tsfem-e-local-icon-%1$s tsfem-e-local-title-icon-%s"></span>', $this->parse_defined_icon_state( $state ) );
	}

	/**
	 * Parses known CSS icon states.
	 *
	 * @since 1.3.0
	 *
	 * @param string $state The could-be unknown state.
	 * @return string The known state.
	 */
	protected function parse_defined_icon_state( $state = '' ) {

		switch ( $state ) :
			case 'good' :
			case 'okay' :
			case 'warning' :
			case 'bad' :
			case 'error' :
				break;

			default :
				$state = 'unknown';
				break;
		endswitch;

		return $state;
	}

	/**
	 * Creates a JS and no-JS compatible description mark.
	 *
	 * @since 1.3.0
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
	 * @since 1.3.0
	 *
	 * @param mixed $description The description field(s).
	 * @return string The escaped flex HTML description output.
	 */
	private function create_fields_description( $description ) {

		if ( is_scalar( $description ) ) {
			return sprintf(
				'<span class="tsfem-form-option-description">%s</span>',
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

	private function get_field_value( $default = null ) {

		$option = $this->sanitize_id( $this->level_names[ $this->level - 1 ] );

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
	 * Text inputs only
	 */
	private function create_input_field_by_type( array $args ) {

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
		$s_name = $s_id = $this->get_field_id();
		$s_ph   = $args['_ph'] ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';
		$s_range = isset( $s_range ) ? $s_range : '';

		return vsprintf(
			'<div class="tsfem-%s-field-wrapper tsfem-form-flex-setting tsfem-flex">%s%s</div>',
			[
				$s_type,
				sprintf(
					'<div class="tsfem-form-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-form-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-form-flex-setting-input tsfem-flex">%s</div>',
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

	private function create_textarea_field( array $args ) {}
	private function create_select_field( array $args ) {

		//* Not escaped.
		$title = $args['_desc'][0];

		$s_name = $s_id = $this->get_field_id();
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		$multiple = 'selectmulti' === $args['_type'];

		return vsprintf(
			'<div class="tsfem-%s-field-wrapper tsfem-form-flex-setting tsfem-flex">%s%s</div>',
			[
				$args['_type'], //= Doesn't need escaping.
				sprintf(
					'<div class="tsfem-form-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-form-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-form-flex-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<select id="%s" name=%s %s>%s</select>',
						[
							$s_id,
							$s_name,
							( $multiple ? 'multiple' : '' ),
							$this->get_select_options( $args['_select'], $this->get_field_value( $args['_default'] ) ),
						]
					)
				),
			]
		);
	}

	private function get_select_options( array $select, $selected = '' ) {

		$_fields = '';

		foreach ( $this->generate_select_fields( $select, $selected ) as $field ) {
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
	private function generate_select_fields( array $select, $selected = '' ) {

		static $_level = 0;

		if ( '' !== $selected ) :
			foreach ( $select as $args ) :

				if ( $_level ) {
					//* Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '― ' . $args[1] : str_repeat( '― ', $_level ) . $args[1];

					//= `&8213; `... gets escaped otherwise.
					$args[1] = '― ' . $args[1];
				}

				if ( isset( $args[2] ) ) {
					//* Level up.
					yield sprintf( '<optgroup label=%s>', $args[1] );
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					++$_level;
					yield $this->get_select_options( $args[2], $selected );
					--$_level;
					yield '</optgroup>';
				} else {
					if ( in_array( $args[0], [ $selected ], true ) ) {
						yield sprintf( '<option value="%s" selected>%s</option>', $args[0], $args[1] );
					} else {
						yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					}
				}
			endforeach;
		else :
			foreach ( $select as $args ) :

				if ( $_level ) {
					//* Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '― ' . $args[1] : str_repeat( '― ', $_level ) . $args[1];

					//= `&8213; `... gets escaped otherwise.
					$args[1] = '― ' . $args[1];
				}

				if ( isset( $args[2] ) ) {
					//* Level up.
					yield sprintf( '<optgroup label="%s">', $args[1] );
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					++$_level;
					yield $this->get_select_options( $args[2], $selected );
					--$_level;
					yield '</optgroup>';
				} else {
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
				}
			endforeach;
		endif;
	}

	private function create_select_multi_a11y_field( array $args ) {

		//* Not escaped.
		$title = $args['_desc'][0];

		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-selectmulti-a11y-field-wrapper tsfem-form-flex-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-form-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<div class="tsfem-form-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></div>',
								[
									sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-form-flex-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-multi-select-wrap" id="%s">%s</div>',
						[
							$this->get_field_id(),
							$this->get_select_multi_a11y_options( $args['_select'], $this->get_field_value( $args['_default'] ) ),
						]
					)
				),
			]
		);
	}

	private function get_select_multi_a11y_options( array $select, $selected = '' ) {

		$_fields = '';

		foreach ( $this->generate_select_multi_a11y_fields( $select, $selected ) as $field ) {
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
	private function generate_select_multi_a11y_fields( array $select, $selected = '' ) {

		yield '<ul class="tsfem-form-multi-a11y-wrap">';

		if ( '' !== $selected ) :
			foreach ( $select as $args ) :
				$this->iterate();
				if ( isset( $args[2] ) ) {
					//* Level up.
					yield sprintf( '<li><strong>%s</strong></li>', $args[1] );
					yield sprintf( '<li><label><input type=checkbox name="%s" value="%s">%s</label></li>', $this->get_field_id(), $args[0], $args[1] );
					yield '<li>';
					yield $this->get_select_multi_a11y_options( $args[2], $selected );
					yield '</li>';
				} else {
					if ( in_array( $args[0], [ $selected ], true ) ) {
						yield sprintf( '<li><label><input type=checkbox name="%s" value="%s" checked>%s</label></li>', $this->get_field_id(), $args[0], $args[1] );
					} else {
						yield sprintf( '<li><label><input type=checkbox name="%s" value="%s">%s</label></li>', $this->get_field_id(), $args[0], $args[1] );
					}
				}
			endforeach;
		else :
			foreach ( $select as $args ) :
				$this->iterate();
				if ( isset( $args[2] ) ) {
					//* Level up.
					yield sprintf( '<li><strong>%s</strong></li>', $args[1] );
					yield sprintf( '<li><label><input type=checkbox name="%s" value="%s">%s</label></li>', $this->get_field_id(), $args[0], $args[1] );
					yield '<li>';
					yield $this->get_select_multi_a11y_options( $args[2], $selected );
					yield '</li>';
				} else {
					yield sprintf( '<li><label><input type=checkbox name="%s" value="%s">%s</label></li>', $this->get_field_id(), $args[0], $args[1] );
				}
			endforeach;
		endif;

		yield '</ul>';
	}


	private function create_checkbox_field( array $args ) {}
	private function create_radio_field( array $args ) {}

	/**
	 *
	 * Requires media scripts to be registered.
	 * @see TSF_Extension_Manager\Traits\UI
	 * @see TSF_Extension_Manager\Traits\UI\register_media_scripts()
	 *
	 * @see _wp_ajax_crop_image() The AJAX cropper callback.
	 */
	private function create_image_field( array $args ) {

		//* Not escaped.
		$title = $args['_desc'][0];

		// Escaped.
		$s_name = $s_id = $this->get_field_id();
		$s_ph   = $args['_ph'] ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-image-field-wrapper tsfem-form-flex-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-form-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-form-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf(
										'tsfem-form-option-title%s',
										( $s_desc ? ' tsfem-form-option-has-description' : '' )
									),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				vsprintf(
					'<div class="tsfem-form-flex-setting-input tsfem-flex">%s<div class="tsfem-form-image-buttons-wrap tsfem-flex tsfem-flex-row tsfem-hide-if-no-js">%s</div></div>',
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
