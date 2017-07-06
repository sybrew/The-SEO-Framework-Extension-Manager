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
	 * Holds the bits and maximum iterations thereof.
	 *
	 * @since 1.3.0
	 *
	 * @var int $bits
	 * @var int $max_it
	 */
	private $bits = 12,
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

	private static $cur_ajax_caller = '',
	               $ajax_it_fields = [],
	               $ajax_it_args = [];

	/**
	 * Determines and initializes AJAX iteration listener.
	 *
	 * @since 1.3.0
	 * @static
	 * @staticvar bool $found Prevents further callback matching to improve performance.
	 *
	 * @param string $class The caller class.
	 * @param array $args : The form arguments {
	 *   string 'caller'   : Required. The calling class. Checks for "doing it right" iteration listeners.
	 *   string 'o_index'  : Required. The option index field for storing extension options.
	 *   string 'o_key'    : The pre-assigned option key. Great for when working
	 *                       with multiple option fields.
	 *   int 'level_depth' : Set how many levels the options can traverse.
	 *                       e.g. 5 depth @ 64 bits => 12 bits =>> 12 bits === 4096 iterations.
	 *                       e.g. 5 depth @ 32 bits =>  6 bits =>>  6 bits ===   64 iterations.
	 *   int 'architecture' : The amount of bits to work with. If unassigned, it will autodetermine.
	 * }
	 * @return string|bool The called iterator name. False otherwise.
	 */
	public static function _parse_ajax_its_listener( $class, $args ) {

		static $found = false;

		if ( $found )
			return false;

		if ( static::is_ajax_callee( $class ) ) {
			$found = true;
			static::$cur_ajax_caller = $class;
			static::$ajax_it_args = $args;

			/**
			 * Action is called in TSF_Extension_Manager\LoadAdmin::_wp_ajax_tsfemForm_iterate().
			 * It has already checked referrer and capability.
			 * @see \TSF_Extension_Manager\LoadAdmin
			 */
			\add_action( 'tsfem_form_do_ajax_iterations', __CLASS__ . '::_output_ajax_form_its', PHP_INT_MIN );

			return static::get_ajax_target_id();
		}

		return false;
	}

	/**
	 * Verifies if the current AJAX callback caller is made for the callee class.
	 *
	 * @since 1.3.0
	 * @static
	 * @global object $_POST
	 *
	 * @param string $caller The caller.
	 * @return bool True if matched, false otherwise.
	 */
	private static function is_ajax_callee( $caller ) {
		return isset( $_POST['args']['callee'] ) && $caller === stripslashes( $_POST['args']['callee'] );
	}

	/**
	 * Returns the AJAX callback iterator name (aka target ID).
	 *
	 * @since 1.3.0
	 * @static
	 * @global object $_POST
	 *
	 * @return string|bool The called iterator name. False otherwise.
	 */
	private static function get_ajax_target_id() {

		if ( isset( $_POST['args']['caller'] ) )
			return \tsf_extension_manager()->get_last_value( \tsf_extension_manager()->satoma( stripslashes( $_POST['args']['caller'] ) ) );

		return false;
	}

	/**
	 * Returns the iteration start for AJAX callback.
	 *
	 * @since 1.3.0
	 * @static
	 * @global object $_POST
	 *
	 * @return unsigned int (R>0) $i The previous iteration value. 1 if $_POST value not set.
	 */
	private static function get_ajax_iteration_start() {
		//= Careful, smart logic. Will return 1 if not set.
		return \absint( ! isset( $_POST['args']['previousIt'] ) ?: $_POST['args']['previousIt'] );
	}

	/**
	 * Returns the iteration start for AJAX callback.
	 *
	 * @since 1.3.0
	 * @static
	 * @global object $_POST
	 *
	 * @return unsigned int (R>=0) $i The new iteration value. 0 if $_POST is not set.
	 */
	private static function get_ajax_iteration_amount() {
		return isset( $_POST['args']['newIt'] ) ? $_POST['args']['newIt'] : 0;
	}

	/**
	 */
	public static function _output_ajax_form_its() {

		$fields = static::$ajax_it_fields;

		$o = new static( static::$ajax_it_args );
		$o->prepare_ajax_iteration();
		$o->prepare_ajax_iteration_fields();
		$o->_fields( static::$ajax_it_fields );

		exit;
	}

	/**
	 * Prepares the current iteration and option levels and names.
	 *
	 * @since 1.3.0
	 * @iterator
	 */
	private function prepare_ajax_iteration() {

		$caller = stripslashes( $_POST['args']['caller'] );
		$items = preg_split( '/[\[\]]+/', $caller, -1, PREG_SPLIT_NO_EMPTY );

		//* Unset the option indexes.
		$unset_count = $this->has_o_key ? 3 : 2;
		while ( $unset_count-- ) {
			array_shift( $items );
		}

		//* Remove current item, as the iterator reintroduces it.
		array_pop( $items );

		foreach ( $items as $item ) {
			if ( is_numeric( $item ) ) {
				$this->iterate( (int) $item );
			} else {
				$this->level_names[ ++$this->level - 1 ] = $item;
			}
		}
	}

	/**
	 * Registers currrent Ajax iteration fields.
	 *
	 * @since 1.3.0
	 * @static
	 *
	 * @return array Current ajax its fields. Passed by reference.
	 */
	public static function &_collect_ajax_its_fields() {
		return static::$ajax_it_fields;
	}

	private function prepare_ajax_iteration_fields() {

		//* TODO Move this into method parameter so we can loop?
		$k = key( static::$ajax_it_fields );

		static::$ajax_it_fields[ $k ]['_type'] = 'iterate_ajax';
		static::$ajax_it_fields[ $k ]['_ajax_it_start'] = static::get_ajax_iteration_start();
		static::$ajax_it_fields[ $k ]['_ajax_it_new'] = static::get_ajax_iteration_amount();
	}

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

		empty( $args['o_index'] ) and \wp_die( __METHOD__ . ': Assign o_index.' );

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

	/**
	 * Gets private properties.
	 *
	 * The $what is preset, this means not all properties can be attained.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what The property to get.
	 * @return void|mixed The private property.
	 */
	public function get( $what = '' ) {
		switch ( $what ) :
			case 'bits' :
				return $this->bits;

			case 'max_it' :
				return $this->max_it;

			default;
		endswitch;
	}

	/**
	 * Returns or echos the form wrap.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what What to get. 'start' or 'end'.
	 * @param string $url The form action POST URL for $what 'start'.
	 * @param string $type Either 'echo' or 'get'.
	 * @return void|string The form wrap on $type 'get'. Void otherwise.
	 */
	public function _form_wrap( $what, $url = '', $type = 'echo' ) {

		if ( 'get' === $type )
			return $this->get_form_wrap( $what, $url );

		//* Already escaped.
		echo $this->get_form_wrap( $what, $url );
	}

	/**
	 * Returns the form wraps.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what What to get. 'start' or 'end'.
	 * @param string $url The form action POST URL for $what 'start'.
	 * @return string|void The form wrappers. Void if unknown $what.
	 */
	private function get_form_wrap( $what, $url ) {

		switch ( $what ) :
			case 'start' :
				return vsprintf(
					'<form action="%s" method=post id="%s" enctype="multipart/form-data" class="tsfem-form">',
					[
						\esc_url( $url ),
						$this->get_form_id(),
					]
				);

			case 'end' :
				return '</form>';

			default;
		endswitch;
	}

	/**
	 * Returns or echos the form button.
	 * The button may be placed outside the form wrap.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what What to get. Currently only supports 'submit'.
	 * @param string $name The form name where the button is for.
	 * @param string $type Either 'echo' or 'get'.
	 * @return void|string The form button on $type 'get'. Void otherwise.
	 */
	public function _form_button( $what, $name, $type = 'echo' ) {

		if ( 'get' === $type )
			return $this->get_form_button( $what, $name );

		echo $this->get_form_button( $what, $name );
	}

	/**
	 * Returns the form button.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what What to get. Currently only supports 'submit'.
	 * @param string $name The form name where the button is for.
	 * @return string The form button.
	 */
	private function get_form_button( $what, $name ) {

		switch ( $what ) :
			case 'submit' :
				return vsprintf(
					'<button type=submit name="%1$s" form="%1$s" class="tsfem-button-primary">%2$s</button>',
					[
						$this->get_form_id(),
						\esc_html( $name ),
					]
				);

			default;
		endswitch;
	}

	/**
	 */
	public function _fields( array $fields, $type = 'echo' ) {

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
	 * Returns next field name and ID attributes for form fields based on $key.
	 *
	 * Careful, when used, it should be used for all fields within scope.
	 * Otherwise, data will not get through POST. As the current ID is converted
	 * to an array, rather than string.
	 *
	 * @since 1.3.0
	 * @uses $this->get_field_id()
	 *
	 * @param string $key The next form field key.
	 * @return string Full field ID/name attribute.
	 */
	private function get_sub_field_id( $key ) {

		$id = $this->get_field_id();

		return sprintf( '%s[%s]', $id, $key );
	}

	/**
	 * Gets fields by reference.
	 *
	 * @since 1.3.0
	 * @see http://php.net/manual/en/language.references.return.php
	 * @uses $this->generate_fields()
	 *
	 * @param array $fields. Passed by reference for performance.
	 * @return string $_fields.
	 */
	private function get_fields( array &$fields ) {

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

	private function iterate( $c = 0 ) {
		//* Add $c + 1 to current level. We normally count from 0.
		$this->it += ( ++$c << ( ( $this->level - 1 ) * $this->bits ) );
	}

	private function deiterate() {
		$this->it &= ~( ( pow( 2, $this->bits ) - 1 ) << ( $this->bits * ( --$this->level ) ) );
		//* Unset highest level.
		unset( $this->level_names[ $this->level + 1 ] );
	}

	/**
	 *
	 * @return mixed string the fields; empty string failure; bool true or false; void.
	 */
	private function create_field( array $args ) {

		if ( empty( $args['_edit'] ) )
			return '';

		$this->clean_desc_index( $args['_desc'] );

		switch ( $args['_type'] ) :
			case 'multi' :
				return $this->create_fields_multi( $args );
				break;

			case 'iterate_main' :
				//= Can only be used on main output field. Will echo. Will try to defer.
				return $this->fields_iterator( $args, 'echo' );
				break;

			case 'iterate_ajax' :
				//= Can only be used in AJAX. Will echo. Will try to defer.
				return $this->fields_iterator( $args, 'ajax' );
				break;

			case 'iterate' :
				return $this->fields_iterator( $args, 'get' );
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
								'<div class="tsfem-form-setting-label-item tsfem-flex"><div class="%s">%s</div></div>',
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

		switch ( $type ) :
			case 'echo' :
				$this->output_fields_iterator( $args );
				break;

			case 'ajax' :
				$this->output_ajax_fields_iterator( $args );
				break;

			case 'get' :
				$o = $this->get_fields_iterator( $args );
				break;

			default;
		endswitch;

		return $o;
	}

	/**
	 * Sets max iterations based on bits and current value.
	 *
	 * Empty values will be converted to max it. Iterations shouldn't go lower than 1.
	 *
	 * @since 1.3.0
	 * @param unsigned int (R>0) $max The maximum value. Passed by reference.
	 */
	private function set_max_iterations( &$max ) {

		if ( $max < 1 || $max > $this->max_it ) {
			$max = $this->max_it;
		}
	}

	/**
	 *
	 * @iterator
	 */
	private function output_fields_iterator( array $args ) {

		echo '<div class="tsfem-form-iterator-setting tsfem-flex">';

		$it_option_key = key( $args['_iterate_selector'] );
		//* Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		//= The selector. Already escaped.
		printf(
			'<div class="tsfem-form-iterator-selector-wrap tsfem-flex">%s</div>',
			$this->create_field( $args['_iterate_selector'][ $it_option_key ] )
		);

		$count = $this->get_field_value( $args['_iterate_selector'][ $it_option_key ]['_default'] );

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = isset( $args['_iterator_title'][1] ) ? $args['_iterator_title'][1] : $_it_title_main;

		$defer = $count > 6;
		//= Get wrap ID before iteration.
		$wrap_id = $this->get_field_id();

		//* Already escaped.
		$defer and printf( '<div class="tsfem-flex-status-loading tsfem-flex tsfem-flex-center" id="%s-loader" style=padding-top:4vh><span></span></div>', $wrap_id );

		//* Already escaped.
		printf(
			'<div class="tsfem-form-collapse-wrap tsfem-form-collapse-sub-wrap" id="%s-wrapper"%s>',
			$wrap_id,
			( $defer ? ' style=display:none' : '' )
		);

		for ( $it = 0; $it < $count; $it++ ) {
			//* PHP automatically checks if sprintf is meaningful.
			$_title = $it ? sprintf( $_it_title, $it + 1 ) : sprintf( $_it_title_main, $it + 1 );

			$this->iterate();

			$collapse_args = [
				'title' => $_title,
				'dyn_title' => $args['_iterator_title_dynamic'],
				'id' => $this->get_field_id(),
			];

			//* Already escaped.
			echo $this->get_collapse_wrap( 'start', $collapse_args );
			$this->output_fields( $args['_fields'], $_title );
			//* Already escaped.
			echo $this->get_collapse_wrap( 'end' );
		}

		echo '</div>';

		//* Already escaped.
		$defer and printf(
			'<script>window.onload=function(){var a=document.getElementById("%1$s-loader");a.parentNode.removeChild(a);document.getElementById("%1$s-wrapper").style=null;};</script>',
			$wrap_id
		);
	}

	/**
	 *
	 * @iterator
	 */
	private function output_ajax_fields_iterator( array $args ) {

		$it_option_key = key( $args['_iterate_selector'] );
		//* Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		$start = (int) $args['_ajax_it_start'];
		$amount = (int) $args['_ajax_it_new'];
		// $count = $amount + $start - 1; // (that's nice, dear.)

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = isset( $args['_iterator_title'][1] ) ? $args['_iterator_title'][1] : $_it_title_main;

		$this->iterate( $start - 1 );

		for ( $it = $start; $it < $amount; $it++ ) {
			//* PHP automatically checks if sprintf is meaningful.
			$_title = $it ? sprintf( $_it_title, $it + 1 ) : sprintf( $_it_title_main, $it + 1 );

			$this->iterate();

			$collapse_args = [
				'title' => $_title,
				'dyn_title' => $args['_iterator_title_dynamic'],
				'id' => $this->get_field_id(),
			];

			//* Already escaped.
			echo $this->get_collapse_wrap( 'start', $collapse_args );
			$this->output_fields( $args['_fields'], $_title );
			//* Already escaped.
			echo $this->get_collapse_wrap( 'end' );
		}
	}

	/**
	 *
	 * @iterator
	 */
	private function get_fields_iterator( array $args ) {

		$it_option_key = key( $args['_iterate_selector'] );

		//* Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		$selector = $this->create_field( $args['_iterate_selector'][ $it_option_key ] );

		$count = $this->get_field_value( $args['_iterate_selector'][ $it_option_key ]['_default'] );

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = isset( $args['_iterator_title'][1] ) ? $args['_iterator_title'][1] : $_it_title_main;

		//= Get wrap ID before iteration.
		$wrap_id = $this->get_field_id();

		$_fields = '';
		for ( $it = 0; $it < $count; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? sprintf( $_it_title, $it + 1 ) : sprintf( $_it_title_main, $it + 1 );

			$this->iterate();

			$collapse_args = [
				'title' => $_title,
				'dyn_title' => $args['_iterator_title_dynamic'],
				'id' => $this->get_field_id(),
			];

			$_fields .= $this->get_collapse_wrap( 'start', $collapse_args );
			$_fields .= $this->get_fields( $args['_fields'] );
			$_fields .= $this->get_collapse_wrap( 'end' );
		}

		return vsprintf(
			'<div class="tsfem-form-iterator-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-form-iterator-selector-wrap tsfem-flex">%s</div>',
					$selector
				),
				sprintf(
					'<div class="tsfem-form-collapse-wrap tsfem-form-collapse-sub-wrap" id="%s-wrapper">%s</div>',
					$wrap_id,
					$_fields
				),
			]
		);
	}

	private function get_collapse_wrap( $what, array $args = [] ) {

		if ( 'start' === $what ) {

			$s_id = $args['id'] ? sprintf( 'id="tsfem-form-collapse-%s"', $args['id'] ) : '';

			$checkbox_id = sprintf( 'tsfem-form-collapse-checkbox-%s', $args['id'] );
			$checkbox = sprintf( '<input type="checkbox" id="%s" checked>', $checkbox_id );

			$dyn_title_type = key( $args['dyn_title'] );
			$dyn_title_key = reset( $args['dyn_title'] );
			$data = vsprintf(
				'data-dyntitletype="%s" data-dyntitleid="%s" data-dyntitlekey="%s" data-dyntitleprep="%s"',
				[
					$dyn_title_type,
					$args['id'],
					$dyn_title_key,
					$args['title'],
				]
			);

			$_dyn_title = $this->get_field_value_by_key( $this->get_sub_field_id( $dyn_title_key ) );
			if ( is_array( $_dyn_title ) ) {
				$tmp = '';
				foreach ( $_dyn_title as $_tmp ) {
					$tmp = $_tmp . ',';
				}
				$_dyn_title = rtrim( $tmp, ', ' );
			}

			$_title = $_dyn_title ? $args['title'] . ' - ' . $_dyn_title : $args['title'];

			$title = sprintf( '<h3 class="tsfem-form-collapse-title">%s</h3>', \esc_html( $_title ) );
			$icon = '<span class="tsfem-form-collapse-icon tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-form-icon-unknown"></span>';

			$header = vsprintf(
				'<label class="tsfem-form-collapse-header tsfem-flex tsfem-flex-row tsfem-flex-nowrap tsfem-flex-nogrow tsfem-flex-space" for="%s" %s>%s%s</label>',
				[
					$checkbox_id,
					$data,
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
	 * Creates a description block from either a single description or multiple
	 * descriptions fed through array,
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

	private function get_field_value_by_key( $key, $default = null ) {
		return '';
	}

	/**
	 * Cleans up '_desc' index.
	 *
	 * @since 1.3.0
	 *
	 * @param array $desc The description index with plausibily missing values.
	 *              Passed by reference.
	 */
	private function clean_desc_index( array &$desc ) {
		$desc[0] = isset( $desc[0] ) ? $desc[0] : '';
		$desc[1] = isset( $desc[1] ) ? $desc[1] : '';
		$desc[2] = isset( $desc[2] ) ? $desc[2] : '';
	}

	/**
	 * Cleans up '_range' index.
	 *
	 * Up to steps e-/+10
	 *
	 * @since 1.3.0
	 *
	 * @param array $range The range index with plausibly missing or exceeding values.
	 *              Passed by reference.
	 */
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
		$s_ph   = ! empty( $args['_ph'] ) ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';
		$s_range = isset( $s_range ) ? $s_range : '';

		return vsprintf(
			'<div class="tsfem-%s-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				$s_type,
				sprintf(
					'<div class="tsfem-form-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-form-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
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
					'<div class="tsfem-form-setting-input tsfem-flex">%s</div>',
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
			'<div class="tsfem-%s-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				$args['_type'], //= Doesn't need escaping.
				sprintf(
					'<div class="tsfem-form-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-form-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
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
					'<div class="tsfem-form-setting-input tsfem-flex">%s</div>',
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
			'<div class="tsfem-selectmulti-a11y-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-form-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<div class="tsfem-form-setting-label-item tsfem-flex"><div class="%s">%s</div></div>',
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
					'<div class="tsfem-form-setting-input tsfem-flex">%s</div>',
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

	/**
	 * @generator
	 */
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
				if ( '' !== $selected && in_array( $args[0], [ $selected ], true ) ) {
					yield sprintf( '<li><label><input type=checkbox name="%s" value="%s" checked>%s</label></li>', $this->get_field_id(), $args[0], $args[1] );
				} else {
					yield sprintf( '<li><label><input type=checkbox name="%s" value="%s">%s</label></li>', $this->get_field_id(), $args[0], $args[1] );
				}
			}
		endforeach;

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
		$s_name = $s_id = $this->get_sub_field_id( 'url' );
		$s_name_id = $s_id_id = $this->get_sub_field_id( 'id' );
		$s_ph   = ! empty( $args['_ph'] ) ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-image-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-form-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-form-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
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
					'<div class="tsfem-form-setting-input tsfem-flex">%s%s<div class="tsfem-form-image-buttons-wrap tsfem-flex tsfem-flex-row tsfem-hide-if-no-js">%s</div></div>',
					[
						vsprintf(
							'<input type=url id="%s" name=%s value="%s" %s>',
							[
								$s_id,
								$s_name,
								\esc_attr( $args['_default'] ), // TODO get value
								$s_ph,
							]
						),
						vsprintf(
							'<input type=hidden id="%s" name=%s value="%s">',
							[
								$s_name_id,
								$s_id_id,
								\esc_attr( $args['_default'] ), // TODO get value
							]
						),
						vsprintf(
							'<button type=button class="%1$s" data-href="%2$s" title="%3$s" id="%4$s-select" data-input-url="%4$s" data-input-id="%5$s">%6$s</button>',
							[
								'tsfem-set-image-button tsfem-button-primary tsfem-button-primary-bright tsfem-button-small',
								\esc_url( \get_upload_iframe_src( 'image', -1, null ) ),
								\esc_attr_x( 'Select image', 'Button hover title', '' ),
								$s_id,
								$s_id_id,
								\esc_html__( 'Select Image', '' ),
							]
						),
					]
				),
			]
		);
	}
}
