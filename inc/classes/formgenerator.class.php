<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	convert_markdown,
	get_image_uploader_form,
};

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, Squiz.Commenting.VariableComment.DuplicateVar, PSR2.Classes.PropertyDeclaration.Multiple -- @TODO later. This class is too complex.

/**
 * Require extension settings trait.
 *
 * @since 2.6.0
 */
_load_trait( 'extension/options' );

/**
 * Holds settings generator functions for package TSF_Extension_Manager\Extension.
 *
 * The class maintains static functions as well as a constructor. They go hand-in-hand.
 *
 * @see package TSF_Extension_Manager\Extension\Local\Settings for an example.
 *
 * @TODO The AJAX part will be put in another class when PHP 5.6 will be the requirement.
 *       We miss variadic functionality for proper static propagated construction.
 *       Note to self: The static caller needs to be moved.
 *
 * Not according to DRY standards for improved performance.
 *
 * @NOTE Most generation pre-input data must be escaped.
 * The generator will NOT escape these:
 *   1. _pattern
 *   2. _data
 *
 * @since 1.3.0
 * @access private
 * @uses trait TSF_Extension_Manager\Extension_Options
 * @see TSF_Extension_Manager\Traits\Extension_Options
 *
 * @final Can't be extended.
 */
final class FormGenerator {
	use Extension_Options;

	/**
	 * @since 1.3.0
	 * @var string The current option key.
	 */
	private $o_key = '';

	/**
	 * @since 1.3.0
	 * @var bool Whether the option key is of stale options.
	 */
	private $use_stale = false;

	/**
	 * @since 1.3.0
	 * @var int Maximum bits assignable ((64|32)/levels-requested).
	 */
	private $bits;

	/**
	 * @since 1.3.0
	 * @var int Max iteration of bits for current OS (64/32 bits).
	 */
	private $max_it;

	/**
	 * NOTE: $it should not ever exceed $max_it.
	 * JavaScript should enforce values. POST even more so, actually.
	 *
	 * @since 1.3.0
	 * @var int The current reiteration level.
	 */
	private $level = 0;

	/**
	 * @since 1.3.0
	 * @var array[string|int] Option level name. Can be string, can be numeric.
	 */
	private $level_names = [];

	/**
	 * @since 1.3.0
	 * @var int The current iteration of level.
	 */
	private $it = 0;

	/**
	 * @since 1.3.0
	 * @var string Current AJAX caller.
	 */
	private static $cur_ajax_caller = '';

	/**
	 * @since 1.3.0
	 * @var array Current AJAX fields iterated.
	 */
	private static $ajax_it_fields = [];

	/**
	 * @since 1.3.0
	 * @var array AJAX request arguments.
	 */
	private static $ajax_it_args = [];

	/**
	 * Determines and initializes AJAX iteration listener.
	 *
	 * The listener will check for referrer and capability.
	 *
	 * @since 1.3.0
	 * @static
	 *
	 * @param string $class  The caller class.
	 * @param array  $args : The form arguments {
	 *   string 'caller'   : Required. The calling class. Checks for "doing it right" iteration listeners.
	 *   string 'o_index'  : Required. The option index field for storing extension options.
	 *   string 'o_key'    : The pre-assigned option key. Great for when working
	 *                       with multiple option fields.
	 *   int 'level_depth' : Set how many levels the options can traverse.
	 *                       e.g. 5 depth @ 64 bits => 12.8 bits =>> 12 bits === 4096 iterations.
	 *                       e.g. 5 depth @ 32 bits =>  6.4 bits =>>  6 bits ===   64 iterations.
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
			static::$ajax_it_args    = $args;

			/**
			 * Action is called in TSF_Extension_Manager\LoadAdmin::_wp_ajax_tsfemForm_iterate().
			 * It has already checked referrer and capability.
			 *
			 * @see \TSF_Extension_Manager\LoadAdmin
			 */
			\add_action( 'tsfem_form_do_ajax_iterations', __CLASS__ . '::_output_ajax_form_its', \PHP_INT_MIN );

			return static::get_ajax_target_id();
		}

		return false;
	}

	/**
	 * Verifies if the current AJAX callback caller is made for the callee class.
	 *
	 * @since 1.3.0
	 * @static
	 *
	 * @param string $caller The caller.
	 * @return bool True if matched, false otherwise.
	 */
	private static function is_ajax_callee( $caller ) {
		// Stripslashes is required, as `\WP_Scripts::localize` adds them.
		// phpcs:ignore, WordPress.Security.NonceVerification.Missing -- tsfem_form_prepare_ajax_iterations() is called before this, which performed user verification checks.
		return isset( $_POST['args']['callee'] ) && stripslashes( $_POST['args']['callee'] ) === $caller;
	}

	/**
	 * Returns the AJAX callback iterator name (aka target ID).
	 *
	 * @since 1.3.0
	 * @static
	 *
	 * @return string|bool The called iterator name. False otherwise.
	 */
	private static function get_ajax_target_id() {

		// phpcs:disable, WordPress.Security.NonceVerification.Missing -- _wp_ajax_tsfemForm_iterate() is called hereafter, performing user verification checks.
		if ( isset( $_POST['args']['caller'] ) )
			return FormFieldParser::get_last_value( FormFieldParser::umatosa( $_POST['args']['caller'] ) );
		// phpcs:enable, WordPress.Security.NonceVerification.Missing

		return false;
	}

	/**
	 * Returns the iteration start for AJAX callback.
	 *
	 * @since 1.3.0
	 * @static
	 *
	 * @return int <unsigned> (R>0) $i The previous iteration value. 1 if $_POST value not set.
	 */
	private static function get_ajax_iteration_start() {
		// Careful, smart logic. Will return 1 if not set.
		// phpcs:ignore, WordPress.Security.NonceVerification -- _wp_ajax_tsfemForm_iterate() is called hereafter, performing user verification checks
		return \absint( ! isset( $_POST['args']['previousIt'] ) ?: $_POST['args']['previousIt'] );
	}

	/**
	 * Returns the iteration start for AJAX callback.
	 *
	 * @since 1.3.0
	 * @static
	 *
	 * @return int <unsigned> (R>=0) $i The new iteration value. 0 if $_POST is not set.
	 */
	private static function get_ajax_iteration_amount() {
		// phpcs:ignore, WordPress.Security.NonceVerification -- _wp_ajax_tsfemForm_iterate() is called hereafter, performing user verification checks
		return \absint( $_POST['args']['newIt'] ?? 0 );
	}

	/**
	 * Outputs form iterations for AJAX.
	 *
	 * @since 1.3.0
	 * @static
	 * @see $this->prepare_ajax_iteration()
	 * @see $this->prepare_ajax_iteration_fields()
	 * @uses $this->_fields()
	 */
	public static function _output_ajax_form_its() {
		$o = new static( static::$ajax_it_args );
		$o->prepare_ajax_iteration();
		$o->prepare_ajax_iteration_fields();
		$o->_fields( static::$ajax_it_fields );
	}

	/**
	 * Prepares the current iteration and option levels and names.
	 *
	 * Performs sanitization on items.
	 *
	 * @since 1.3.0
	 * @iterator
	 */
	private function prepare_ajax_iteration() {

		// phpcs:ignore, WordPress.Security.NonceVerification -- tsfem_form_prepare_ajax_iterations() is called before this, which performed user verification checks.
		$caller = $_POST['args']['caller'];
		$items  = preg_split( '/[\[\]]+/', $caller, -1, \PREG_SPLIT_NO_EMPTY );

		// Unset the option indexes.
		$unset_count = $this->o_key ? 3 : 2;
		while ( $unset_count-- ) {
			array_shift( $items );
		}

		// Remove current item, as the iterator reintroduces it.
		array_pop( $items );

		foreach ( $items as $item ) {
			if ( is_numeric( $item ) ) {
				$this->iterate( (int) $item );
			} else {
				$this->level_names[ ++$this->level - 1 ] = $this->sanitize_id( $item );
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

	/**
	 * Prepares and sanitizes first $ajax_it_fields input.
	 *
	 * @since 1.3.0
	 * @static
	 * @uses $this->get_ajax_iteration_start()
	 * @uses $this->get_ajax_iteration_amount()
	 */
	private function prepare_ajax_iteration_fields() {

		// TODO Move this into method parameter so we can loop?
		$k = key( static::$ajax_it_fields );

		static::$ajax_it_fields[ $k ]['_type']          = 'iterate_ajax';
		static::$ajax_it_fields[ $k ]['_ajax_it_start'] = static::get_ajax_iteration_start();
		static::$ajax_it_fields[ $k ]['_ajax_it_new']   = static::get_ajax_iteration_amount();
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
	 *   string 'o_index'      : Required. The option index field for storing extension options.
	 *   string 'o_key'        : The pre-assigned option key. Great for when working
	 *                           with multiple option fields.
	 *   bool   'use_stale'    : Whether to fetch from stale options cache.
	 *   int    'level_depth'  : Set how many levels the options can traverse.
	 *                           e.g. 5 depth @ 64 bits => 12 bits =>> 12 bits === 4096 iterations.
	 *                           e.g. 5 depth @ 32 bits =>  6 bits =>>  6 bits ===   64 iterations.
	 *   int    'architecture' : The amount of bits to work with. If unassigned, it will autodetermine.
	 * }
	 */
	public function __construct( &$args ) {

		empty( $args['o_index'] ) and \wp_die( __METHOD__ . ': Assign o_index.' );

		$args = array_merge(
			[
				'o_index'      => '',
				'o_defaults'   => [],
				'o_key'        => '',
				'use_stale'    => false,
				'levels'       => 5,
				'architecture' => null,
			],
			$args
		);

		/**
		 * @see trait \TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index    = $args['o_index'];
		$this->o_defaults = $args['o_defaults'];

		$args['architecture'] = $args['architecture'] ?: ( \tsfem()->is_64() ? 64 : 32 );

		$this->bits   = floor( $args['architecture'] / $args['levels'] );
		$this->max_it = 2 ** $this->bits;

		$this->o_key     = $args['o_key'] = $this->sanitize_id( $args['o_key'] );
		$this->use_stale = (bool) $args['use_stale'];
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
		switch ( $what ) {
			case 'bits':
				return $this->bits;

			case 'max_it':
				return $this->max_it;
		}
	}

	/**
	 * Returns or echos the form wrap.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what What to get. 'start' or 'end'.
	 * @param string $url The form action POST URL for $what 'start'.
	 * @param bool   $validator Whether the form applies a validator.
	 * @param string $type Either 'echo' or 'get'.
	 * @return void|string The form wrap on $type 'get'. Void otherwise.
	 */
	public function _form_wrap( $what, $url = '', $validator = false, $type = 'echo' ) {

		if ( 'get' === $type )
			return $this->get_form_wrap( $what, $url, $validator );

		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
		echo $this->get_form_wrap( $what, $url, $validator );
	}

	/**
	 * Returns the form wraps.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what      What to get. 'start' or 'end'.
	 * @param string $url       The form action POST URL for $what 'start'.
	 * @param bool   $validator Whether the form applies a validator.
	 * @return string|void The form wrappers. Void if unknown $what.
	 */
	private function get_form_wrap( $what, $url, $validator ) {

		switch ( $what ) {
			case 'start':
				return vsprintf(
					'<form action="%s" method=post id="%s" enctype=multipart/form-data class="tsfem-form%s" autocomplete=off data-form-type=other>',
					[
						\esc_url( $url ),
						$this->get_form_id(),
						$validator ? ' tsfem-form-validate' : '',
					]
				);

			case 'end':
				return '</form>';
		}
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

		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
		echo $this->get_form_button( $what, $name );
	}

	/**
	 * Returns the form button.
	 *
	 * @since 1.3.0
	 * @since 2.2.0 Added hide-if-no-js class.
	 *
	 * @param string $what What to get. Currently only supports 'submit'.
	 * @param string $name The form name where the button is for.
	 * @return string The form button.
	 */
	private function get_form_button( $what, $name ) {

		switch ( $what ) {
			case 'submit':
				return vsprintf(
					'<button type=submit name="%1$s" form="%1$s" class="tsfem-button-primary tsfem-button-upload hide-if-no-tsf-js">%2$s</button>',
					[
						$this->get_form_id(),
						\esc_html( $name ),
					]
				);
		}
	}

	/**
	 * Outputs or returns fields.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $fields Passed by reference for performance.
	 * @param string $type   Accepts 'get' and 'echo'.
	 * @return string|void $_fields. Void if $type is echo.
	 */
	public function _fields( $fields, $type = 'echo' ) {

		if ( 'get' === $type )
			return $this->get_fields( $fields );

		$this->output_fields( $fields );
	}

	/**
	 * Gets fields by reference.
	 *
	 * @since 1.3.0
	 * @see http://php.net/manual/en/language.references.return.php
	 * @uses $this->generate_fields()
	 *
	 * @param array $fields Passed by reference for performance.
	 * @return string $_fields.
	 */
	private function get_fields( array &$fields ) {

		$_fields = '';

		foreach ( $this->generate_fields( $fields ) as $field ) {
			// Already escaped.
			$_fields .= $field;
		}

		return $_fields;
	}

	/**
	 * Outputs fields by reference.
	 *
	 * @see http://php.net/manual/en/language.references.return.php
	 * @uses $this->generate_fields()
	 *
	 * @param array $fields Passed by reference for performance.
	 */
	private function output_fields( array &$fields ) {
		foreach ( $this->generate_fields( $fields ) as $field ) {
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			echo $field;
		}
	}

	/**
	 * Sanitizeses ID. Mainly removing spaces and coding characters.
	 *
	 * Unlike sanitize_key(), it doesn't alter the case nor applies filters.
	 * It also maintains the '@' character.
	 *
	 * @see WordPress Core sanitize_key()
	 * @since 1.3.0
	 *
	 * @param string $id The unsanitized ID.
	 * @return string The sanitized ID.
	 */
	private function sanitize_id( $id ) {
		return preg_replace( '/[^a-zA-Z0-9_\-@]/', '', $id );
	}

	/**
	 * Returns form ID attribute for form wrap.
	 *
	 * @since 1.3.0
	 * @uses \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @uses $this->o_key
	 * @access private
	 *
	 * @return string Full form ID attribute.
	 */
	private function get_form_id() {

		if ( $this->o_key ) {
			$k = \sprintf( '%s[%s][%s]', \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $this->o_key );
		} else {
			$k = \sprintf( '%s[%s]', \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index );
		}

		return $k;
	}

	/**
	 * Returns field name and ID attributes for form fields.
	 *
	 * @since 1.3.0
	 * @uses \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @uses $this->o_key
	 *
	 * @return string Full field ID/name attribute.
	 */
	private function get_field_id() {

		if ( $this->o_key ) {
			$k = \sprintf( '%s[%s][%s]', \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $this->o_key );
		} else {
			$k = \sprintf( '%s[%s]', \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index );
		}

		// Correct the length of bits, split them and put them in the right order.
		$_f     = \sprintf( '%%0%db', ( $this->level * $this->bits ) );
		$levels = array_reverse( str_split( \sprintf( $_f, $this->it ), $this->bits ) );

		$i = 0;
		foreach ( $levels as $b ) {
			$k = \sprintf( '%s[%s]', $k, $this->sanitize_id( $this->level_names[ $i ] ) );
			// Only grab iterators, they start at 2 as the iteration caller is 1.
			if ( $b > 1 ) {
				$k = \sprintf( '%s[%d]', $k, bindec( $b ) - 1 );
			}
			++$i;
		}

		return $k;
	}

	/**
	 * Returns field name and ID attributes for form fields in associative
	 * multidimensional array form.
	 *
	 * When $what is not 'full', it will omit the option namespaces.
	 *
	 * @since 1.3.0
	 * @uses \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @uses $this->o_key
	 *
	 * @param string $what Whether to fetch the full key or the associative key.
	 * @return array Full current field ID/name attribute array.
	 */
	private function get_raw_field_id( $what = 'full' ) {

		$k = [];
		if ( 'full' === $what ) {
			$k[] = \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS;
			$k[] = $this->o_index;
		}

		if ( $this->o_key )
			$k[] = $this->o_key;

		// Correct the length of bits, split them and put them in the right order.
		$_f     = \sprintf( '%%0%db', ( $this->level * $this->bits ) );
		$levels = array_reverse( str_split( \sprintf( $_f, $this->it ), $this->bits ) );

		$i = 0;
		foreach ( $levels as $b ) {
			$k[] = $this->sanitize_id( $this->level_names[ $i ] );
			// Only grab iterators, they start at 2 as the iteration caller is 1.
			if ( $b > 1 ) {
				$k[] = bindec( $b ) - 1;
			}
			++$i;
		}

		return $k;
	}

	/**
	 * Returns custom following field name and ID attributes for form fields based on $key.
	 *
	 * Careful, when used, it should be used for all fields within scope.
	 * Otherwise, data will not get through POST. As the current ID is converted
	 * to an array, rather than string.
	 *
	 * @since 2.3.0
	 *
	 * @param string $id  The base associative field ID.
	 * @param string $key The next form field key.
	 * @return string Full field ID/name attribute.
	 */
	private function create_sub_field_id( $id, $key ) {
		return \sprintf( '%s[%s]', $id, $key );
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
		return $this->create_sub_field_id( $this->get_field_id(), $key );
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
	 * @param string $what Whether to fetch the full key or the associative key.
	 * @return string Full field ID/name attribute.
	 */
	private function get_raw_sub_field_id( $key, $what = 'full' ) {

		$id   = $this->get_raw_field_id( $what );
		$id[] = $key;

		return $id;
	}

	/**
	 * Returns custom following field name and ID attributes for form fields based on $key.
	 *
	 * Careful, when used, it should be used for all fields within scope.
	 * Otherwise, data will not get through POST. As the current ID is converted
	 * to an array, rather than string.
	 *
	 * @since 2.3.0
	 * @uses $this->get_field_id()
	 *
	 * @param array  $id  The current form field id.
	 * @param array  $key The next form field key.
	 * @param string $what Whether to fetch the full key or the associative key.
	 * @return string Full field ID/name attribute.
	 */
	private function create_raw_sub_field_id( $id, $key, $what = 'full' ) {

		// 0 = base option index. 1 = extension index.
		if ( 'full' !== $what ) {
			$slice = $this->o_key ? 3 : 2;
			$id    = \array_slice( $id, $slice );
		}

		$id[] = $key;

		return $id;
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
	private function generate_fields( $fields ) {

		// Store first key, to be caught later when iterating.
		$this->level_names[ $this->level ] = key( $fields );

		/**
		 * Pass down option level as main level.
		 * Because it allows for 6 bits setting, each loop can iterate at 64
		 * options for each depth (ie hex).
		 * Maximum of depth of 5 @ 32 bit. 10 @ 64 bits.
		 */
		$this->level();

		foreach ( $fields as $option => $_args ) {
			// Overwrite later keys, to be caught when generating IDs
			$this->level_names[ $this->level - 1 ] = $option;

			yield $this->create_field( $_args );
		}

		$this->delevel();
	}

	/**
	 * Levels current generator level by one.
	 *
	 * @since 1.3.0
	 * @uses $this->level
	 * @uses $this->iterate()
	 *
	 * @return void
	 */
	private function level() {
		++$this->level;
		$this->iterate();
	}

	/**
	 * Unsets current generator level.
	 *
	 * @since 1.3.0
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits
	 *
	 * @return void
	 */
	private function delevel() {
		$this->it &= ~( ( 2 ** $this->bits - 1 ) << ( $this->bits * ( --$this->level ) ) );
		// Unset highest level.
		unset( $this->level_names[ $this->level + 1 ] );
	}

	/**
	 * Iterates current generator level.
	 *
	 * @since 1.3.0
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits
	 *
	 * @param int $c The amount to iterate.
	 * @return void
	 */
	private function iterate( $c = 0 ) {
		// Add $c + 1 to current level. We normally count from 0.
		$this->it += ( ++$c << ( ( $this->level - 1 ) * $this->bits ) );
	}

	/**
	 * Deiterates current generator level.
	 *
	 * @since 1.3.0
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits
	 *
	 * @param int $c The amount to deiterate.
	 * @return void
	 */
	private function deiterate( $c = 0 ) {
		// Subtract $c + 1 to current level. We normally count from 0.
		$this->it -= ( ++$c << ( ( $this->level - 1 ) * $this->bits ) );
	}

	/**
	 * Resets and reiterates current generator level to 1.
	 *
	 * @since 1.3.0
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits
	 * @uses $this->level()
	 *
	 * @return void
	 */
	private function reiterate() {
		$this->it &= ~( ( 2 ** $this->bits - 1 ) << ( $this->bits * ( $this->level - 1 ) ) );
		$this->iterate();
	}

	/**
	 * Creates a field description.
	 *
	 * @since 2.2.0
	 *
	 * @param array  $args The field arguments.
	 * @param string $id   The field ID.
	 * @return string
	 */
	private function create_field_description( $args, $id ) {

		// Not escaped.
		$title = $args['_desc'][0];

		// Escaped
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1], ! empty( $args['_md'] ) ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2], ! empty( $args['_md'] ) ) : '';

		return \sprintf(
			'<div class="tsfem-form-setting-label tsfem-flex">%s</div>',
			vsprintf(
				'<div class="tsfem-form-setting-label-inner-wrap tsfem-flex">%s%s</div>',
				[
					vsprintf(
						'<label for="%s" class="tsfem-form-setting-label-item tsfem-flex"><span class="%s">%s</span></label>',
						[
							$id,
							\sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
							\sprintf( '<strong>%s</strong> %s', \esc_html( $title ), $s_more ),
						]
					),
					$s_desc,
				]
			)
		);
	}

	/**
	 * Creates option field.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args The field arguments.
	 * @return mixed string the fields; empty string failure; bool true or false; void.
	 */
	private function create_field( $args ) {

		if ( empty( $args['_edit'] ) )
			return '';

		$this->clean_desc_index( $args['_desc'] );

		switch ( $args['_type'] ) {
			case 'multi':
				return $this->create_fields_multi( $args );

			case 'plain_multi':
				return $this->create_fields_multi( $args, true );

			case 'multi_dropdown':
				return $this->create_fields_multi_dropdown( $args );

			case 'plain_dropdown':
				return $this->create_fields_multi_dropdown( $args, true );

			case 'multi_placeholder':
				return $this->create_fields_multi_placeholder( $args );

			case 'iterate_main':
				// Can only be used on main output field. Will echo. Will try to defer.
				return $this->fields_iterator( $args, 'echo' );

			case 'iterate_ajax':
				// Can only be used in AJAX. Will echo. Will try to defer.
				return $this->fields_iterator( $args, 'ajax' );

			case 'iterate':
				return $this->fields_iterator( $args, 'get' );

			case 'select':
			case 'selectmulti':
				return $this->create_select_field( $args );

			case 'selectmultia11y':
				// Select field, but then through checkboxes.
				return $this->create_select_multi_a11y_field( $args );

			case 'text':
			case 'password':
			case 'tel':
			case 'url':
			case 'search':
			case 'time':
			case 'week':
			case 'month':
			case 'datetime-local':
			case 'date':
			case 'number':
			case 'range':
			case 'color':
			case 'hidden':
				return $this->create_input_field_by_type( $args );

			case 'textarea':
				return $this->create_textarea_field( $args );

			case 'checkbox':
				return $this->create_checkbox_field( $args );

			case 'radio':
				return $this->create_radio_field( $args );

			case 'image':
				return $this->create_image_field( $args );
		}

		return '';
	}

	/**
	 * Creates field wrapper to generate multiple fields.
	 *
	 * @since 1.3.0
	 * @see $this->create_field()
	 *
	 * @param array $args  The field arguments.
	 * @param bool  $plain Whether to conver the fields to a plain wrap.
	 * @return mixed string the fields; empty string failure; bool true or false; void.
	 */
	private function create_fields_multi( $args, $plain = false ) {

		$this->clean_desc_index( $args['_desc'] );
		$title = $args['_desc'][0];

		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1], ! empty( $args['_md'] ) ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2], ! empty( $args['_md'] ) ) : '';

		$s_data = isset( $args['_data'] ) ? $this->get_fields_data( $args['_data'] ) : '';

		$s_type = $plain ? 'tsfem-form-plain-settings' : 'tsfem-form-multi-setting';

		$_halt_leveling = isset( $args['_nolevel'] ) && ! $args['_nolevel'];
		$_halt_leveling && $this->delevel();

		$ret = vsprintf(
			'<div class="%s tsfem-form-setting tsfem-flex"%s>%s%s</div>',
			[
				$s_type,
				$s_data,
				\sprintf(
					'<div class="%s-label tsfem-flex" id="%s">%s</div>',
					$s_type,
					$this->get_field_id(),
					vsprintf(
						'<div class="%s-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							$s_type,
							$title ? vsprintf(
								'<div class="tsfem-form-setting-label-item tsfem-flex"><span class="%s">%s</span></div>',
								[
									\sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
									\sprintf( '<strong>%s</strong> %s', \esc_html( $title ), $s_more ),
								]
							) : '',
							$s_desc,
						]
					)
				),
				\sprintf(
					'<div class="%s-input tsfem-flex">%s</div>',
					$s_type,
					$this->get_fields( $args['_fields'] )
				),
			]
		);

		$_halt_leveling && $this->level();

		return $ret;
	}

	/**
	 * Returns the fields iterator wrap and fields, without allowing manual iteration.
	 * AKA multi-select with dropdown.
	 *
	 * @since 2.3.0
	 * @iterator
	 *
	 * @param array $args  The field arguments.
	 * @param bool  $plain Whether to conver the fields to a plain wrap.
	 * @return string
	 */
	private function create_fields_multi_dropdown( $args, $plain = false ) {

		$this->clean_desc_index( $args['_desc'] );
		$title = $args['_desc'][0];

		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1], ! empty( $args['_md'] ) ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2], ! empty( $args['_md'] ) ) : '';

		$s_data = isset( $args['_data'] ) ? $this->get_fields_data( $args['_data'] ) : '';

		// Get wrap ID before iteration.
		$wrap_id = $this->get_field_id();
		$_fields = '';

		foreach ( $args['_fields'] as $field_id => $fields ) {

			$this->clean_desc_index( $fields['_desc'] );

			$collapse_args = [
				'title'             => $fields['_desc'][0],
				'dyn_title'         => $args['_dropdown_title_dynamic'] ?? '',
				'dyn_title_checked' => $args['_dropdown_title_checked'] ?? '',
				'id'                => $this->create_sub_field_id( $this->get_field_id(), $field_id ),
			];

			// Empty first field's title if it's of a plain multi-type. It'd be duplicated otherwise.
			if ( 'plain_multi' === $fields['_type'] )
				$fields['_desc'][0] = '';

			$_field_data = [ $field_id => $fields ];

			$_fields .= $this->get_collapse_wrap( 'start', $collapse_args );
			$_fields .= $this->get_fields( $_field_data );
			$_fields .= $this->get_collapse_wrap( 'end' );
		}

		$s_type = $plain ? 'tsfem-form-plain-settings' : 'tsfem-form-multi-setting';

		$_halt_leveling = isset( $args['_nolevel'] ) && ! $args['_nolevel'];
		$_halt_leveling && $this->delevel();

		$ret = vsprintf(
			'<div class="%s tsfem-form-setting tsfem-flex"%s>%s%s</div>',
			[
				$s_type,
				$s_data,
				\sprintf(
					'<div class="%s-label tsfem-flex" id="%s">%s</div>',
					$s_type,
					$this->get_field_id(),
					vsprintf(
						'<div class="%s-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							$s_type,
							! $plain && $title ? vsprintf(
								'<div class="tsfem-form-setting-label-item tsfem-flex"><span class="%s">%s</span></div>',
								[
									\sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
									\sprintf( '<strong>%s</strong> %s', \esc_html( $title ), $s_more ),
								]
							) : '',
							$s_desc,
						]
					)
				),
				\sprintf(
					'<div class="tsfem-form-collapse-wrap tsfem-form-collapse-sub-wrap %s-input tsfem-flex" id="%s-wrapper">%s</div>',
					$s_type,
					$wrap_id,
					$_fields
				),
			]
		);

		$_halt_leveling && $this->level();

		return $ret;
	}

	/**
	 * Mimics the multi-field wrapper, without the wrapper.
	 * To be used as a placeholder for future multi-expansion; using this ticks the
	 * fields iterator.
	 *
	 * @since 2.3.0
	 * @see $this->create_field()
	 *
	 * @param array $args The field arguments.
	 * @return mixed string the fields; empty string failure; bool true or false; void.
	 */
	private function create_fields_multi_placeholder( $args ) {
		return $this->get_fields( $args['_fields'] );
	}

	/**
	 * Outputs or returns iterator fields. Compatible with AJAX.
	 *
	 * Main field iterator callback.
	 *
	 * @since 1.3.0
	 * @see $this->create_field()
	 *
	 * @param array  $args The iterator fields arguments.
	 * @param string $type Determines whether to output, output for AJAX or return.
	 * @return string HTML on return. Empty string on echo.
	 */
	private function fields_iterator( $args, $type = 'echo' ) {

		switch ( $type ) {
			case 'echo':
				$this->output_fields_iterator( $args );
				break;

			case 'ajax':
				$this->output_ajax_fields_iterator( $args );
				break;

			case 'get':
				return $this->get_fields_iterator( $args );
		}

		return '';
	}

	/**
	 * Sets max iterations based on bits and current value.
	 *
	 * Empty values will be converted to max it. Iterations shouldn't go lower than 1.
	 *
	 * @since 1.3.0
	 *
	 * @param int <unsigned> (R>0) $max The maximum value. Passed by reference.
	 */
	private function set_max_iterations( &$max ) {
		if ( $max < 1 || $max > $this->max_it )
			$max = $this->max_it;
	}

	/**
	 * Outputs the fields iterator wrap and fields.
	 *
	 * @since 1.3.0
	 * @iterator
	 *
	 * @param array $args The fields iterator arguments.
	 */
	private function output_fields_iterator( $args ) {

		echo '<div class=tsfem-form-iterator-setting>';

		$it_option_key = key( $args['_iterate_selector'] );
		// Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		printf(
			'<div class="tsfem-form-iterator-selector-wrap tsfem-flex tsfem-flex-noshrink">%s</div>',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			$this->get_fields( $args['_iterate_selector'] )
		);

		$count = $this->get_field_value_by_key(
			$this->get_raw_sub_field_id( $it_option_key, 'associative' ),
			$args['_iterate_selector'][ $it_option_key ]['_default']
		);

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = $args['_iterator_title'][1] ?? $_it_title_main;

		$defer = $count > 6;
		// Get wrap ID before iteration.
		$wrap_id = $this->get_field_id();

		$defer and printf(
			'<div class="tsfem-flex-status-loading tsfem-flex tsfem-flex-center" id="%s-loader" style=padding-top:4vh><span></span></div>',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			$wrap_id
		);

		printf(
			'<div class="tsfem-form-collapse-wrap tsfem-form-collapse-sub-wrap" id="%s-wrapper"%s>',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			$wrap_id,
			( $defer ? ' style=display:none' : '' )
		);

		for ( $it = 0; $it < $count; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? \sprintf( $_it_title, $it + 1 ) : \sprintf( $_it_title_main, $it + 1 );

			$this->iterate();

			$collapse_args = [
				'title'             => $_title,
				'dyn_title'         => $args['_iterator_title_dynamic'] ?? '',
				'dyn_title_checked' => $args['_iterator_title_checked'] ?? '',
				'id'                => $this->get_field_id(),
			];

			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			echo $this->get_collapse_wrap( 'start', $collapse_args );
			$this->output_fields( $args['_fields'], $_title );
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			echo $this->get_collapse_wrap( 'end' );
		}

		echo '</div>';

		$defer and printf(
			'<script>window.onload=function(){var a=document.getElementById("%1$s-loader");a.parentNode.removeChild(a);document.getElementById("%1$s-wrapper").style=null;};</script>',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			$wrap_id
		);

		echo '</div>';
	}

	/**
	 * Outputs the fields iterator wrap and fields for AJAX.
	 *
	 * @since 1.3.0
	 * @iterator
	 *
	 * @param array $args The fields iterator arguments.
	 */
	private function output_ajax_fields_iterator( $args ) {

		$it_option_key = key( $args['_iterate_selector'] );
		// Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		$start  = (int) $args['_ajax_it_start'];
		$amount = (int) $args['_ajax_it_new'];
		// $count = $amount + $start - 1; // (that's nice, dear.)

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = $args['_iterator_title'][1] ?? $_it_title_main;

		$this->iterate( $start - 1 );

		for ( $it = $start; $it < $amount; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? \sprintf( $_it_title, $it + 1 ) : \sprintf( $_it_title_main, $it + 1 );

			$this->iterate();

			$collapse_args = [
				'title'             => $_title,
				'dyn_title'         => $args['_iterator_title_dynamic'] ?? '',
				'dyn_title_checked' => $args['_iterator_title_checked'] ?? '',
				'id'                => $this->get_field_id(),
			];

			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			echo $this->get_collapse_wrap( 'start', $collapse_args );
			$this->output_fields( $args['_fields'], $_title );
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
			echo $this->get_collapse_wrap( 'end' );
		}
	}

	/**
	 * Returns the fields iterator wrap and fields.
	 *
	 * @since 1.3.0
	 * @iterator
	 *
	 * @param array $args The fields iterator arguments.
	 * @return string
	 */
	private function get_fields_iterator( $args ) {

		$it_option_key = key( $args['_iterate_selector'] );

		// Set maximum iterations based on option depth if left unassigned.
		$this->set_max_iterations( $args['_iterate_selector'][ $it_option_key ]['_range'][1] );

		$selector = $this->get_fields( $args['_iterate_selector'] );

		$count = $this->get_field_value_by_key(
			$this->get_raw_sub_field_id( $it_option_key, 'associative' ),
			$args['_iterate_selector'][ $it_option_key ]['_default']
		);

		$_it_title_main = $args['_iterator_title'][0];
		$_it_title      = $args['_iterator_title'][1] ?? $_it_title_main;

		// Get wrap ID before iteration.
		$wrap_id = $this->get_field_id();

		$_fields = '';
		for ( $it = 0; $it < $count; $it++ ) {
			// PHP automatically checks if sprintf is meaningful.
			$_title = $it ? \sprintf( $_it_title, $it + 1 ) : \sprintf( $_it_title_main, $it + 1 );

			$this->iterate();

			$collapse_args = [
				'title'             => $_title,
				'dyn_title'         => $args['_iterator_title_dynamic'] ?? '',
				'dyn_title_checked' => $args['_iterator_title_checked'] ?? '',
				'id'                => $this->get_field_id(),
			];

			$_fields .= $this->get_collapse_wrap( 'start', $collapse_args );
			$_fields .= $this->get_fields( $args['_fields'] );
			$_fields .= $this->get_collapse_wrap( 'end' );
		}

		return vsprintf(
			'<div class=tsfem-form-iterator-setting>%s%s</div>',
			[
				\sprintf(
					'<div class="tsfem-form-iterator-selector-wrap tsfem-flex tsfem-flex-noshrink">%s</div>',
					$selector
				),
				\sprintf(
					'<div class="tsfem-form-collapse-wrap tsfem-form-collapse-sub-wrap" id="%s-wrapper">%s</div>',
					$wrap_id,
					$_fields
				),
			]
		);
	}

	/**
	 * Returns collapse wrap for fields iterator.
	 *
	 * @since 1.3.0
	 *
	 * @param string $what Whether to 'start' or 'end' the wrap.
	 * @param array  $args The collapse wrap arguments.
	 * @return string
	 */
	private function get_collapse_wrap( $what, $args = [] ) {

		if ( 'start' === $what ) {
			$s_id = $args['id'] ? \sprintf( 'id="tsfem-form-collapse-%s"', $args['id'] ) : '';

			$checkbox_id = \sprintf( 'tsfem-form-collapse-checkbox-%s', $args['id'] );
			$checkbox    = \sprintf( '<input type=checkbox id="%s" class=tsfem-form-collapse-checkbox checked>', $checkbox_id );

			$args['dyn_title'] = (array) $args['dyn_title'];

			// For now, we only support one test. I doubt we should support more, due to the complexity involved.
			$dyn_title_type = key( $args['dyn_title'] );
			$dyn_title_key  = reset( $args['dyn_title'] );

			$data = vsprintf(
				'data-dyntitletype="%s" data-dyntitleid="%s" data-dyntitlekey="%s" data-dyntitleprep="%s" data-dyntitlechecked="%s"',
				[
					$dyn_title_type,
					$args['id'],
					$dyn_title_key,
					\esc_attr( $args['title'] ),
					\esc_attr( $args['dyn_title_checked'] ),
				]
			);

			$title = vsprintf(
				'<h3 class=tsfem-form-collapse-title-wrap>%s%s</h3>',
				[
					'<span class="tsfem-form-title-icon tsfem-form-title-icon-unknown"></span>',
					\sprintf(
						'<span class=tsfem-form-collapse-title>%s</span>',
						\esc_html( $args['title'] )
					),
				]
			);
			$icon  = '<span class="tsfem-form-collapse-icon tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap"></span>';

			$header = vsprintf(
				'<label class="tsfem-form-collapse-header tsfem-flex tsfem-flex-row tsfem-flex-nowrap tsfem-flex-nogrow tsfem-flex-space" for="%s" %s>%s%s</label>',
				[
					$checkbox_id,
					$data,
					$title,
					$icon,
				]
			);

			$content_start = '<div class=tsfem-form-collapse-content>';

			return \sprintf(
				'<div class=tsfem-form-collapse %s>%s%s%s',
				$s_id,
				$checkbox,
				$header,
				$content_start
			);
		} elseif ( 'end' === $what ) {
			// ok.
			return '</div></div>';
		}

		return '';
	}

	/**
	 * Creates a JS and no-JS compatible description mark.
	 *
	 * @since 1.3.0
	 * @since 2.6.1 Added markdown support.
	 *
	 * @param string $description The description.
	 * @param bool   $use_markdown Whether to use markdown parsing.
	 * @return string The escaped inline HTML description output.
	 */
	private function create_fields_sub_description( $description, $use_markdown ) {

		$description = $use_markdown ? convert_markdown( $description ) : $description;

		// make_inline_tooltip escapes.
		return HTML::wrap_inline_tooltip( HTML::make_inline_tooltip(
			'',
			strip_tags( $description ), // phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- we don't deal with scripts here.
			$description,
			[ 'tsfem-dashicon', 'tsfem-unknown' ]
		) );
	}

	/**
	 * Creates a description block from either a single description or multiple
	 * descriptions fed through array,
	 *
	 * @since 1.3.0
	 * @since 2.2.0 Added $use_markdown.
	 *
	 * @param mixed $description  The description field(s).
	 * @param bool  $use_markdown Whether to use markdown parsing.
	 * @return string The escaped flex HTML description output.
	 */
	private function create_fields_description( $description, $use_markdown ) {

		if ( \is_scalar( $description ) ) {
			return \sprintf(
				'<span class=tsfem-form-option-description>%s</span>',
				$use_markdown ? convert_markdown( \esc_html( $description ) ) : \esc_html( $description )
			);
		} else {
			$ret = '';

			foreach ( $description as $desc )
				$ret .= $this->create_fields_description( $desc, $use_markdown );

			return $ret;
		}
	}

	/**
	 * Returns field value for current field.
	 *
	 * @NOTE Do not optimize this.
	 * It's basically a copy of $this->get_field_value_by_key(). But it will
	 * create function overhead if associated.
	 *
	 * @since 1.3.0
	 * @uses $this->get_raw_field_id()
	 * @uses trait \TSF_Extension_Manager\Extension_Options
	 *
	 * @param mixed $default The default field value.
	 * @return mixed The field value if set, $default otherwise.
	 */
	private function get_field_value( $default = null ) {

		$key = $this->get_raw_field_id( 'associative' );

		if ( $this->use_stale )
			return $this->get_stale_option_by_mda_key( $key, $default );

		return $this->get_option_by_mda_key( $key, $default );
	}

	/**
	 * Returns field value for input $key
	 *
	 * @since 1.3.0
	 * @uses trait \TSF_Extension_Manager\Extension_Options
	 *
	 * @param array $key The associative field key.
	 * @param mixed $default The default field value.
	 * @return mixed The field value if set, $default otherwise.
	 */
	private function get_field_value_by_key( $key, $default = null ) {

		if ( $this->use_stale )
			return $this->get_stale_option_by_mda_key( $key, $default );

		return $this->get_option_by_mda_key( $key, $default );
	}

	/**
	 * Cleans up `$args['_desc']` index by assigning missing values.
	 *
	 * @since 1.3.0
	 *
	 * @param array $desc The description index with plausibily missing values.
	 *              Passed by reference.
	 */
	private function clean_desc_index( array &$desc ) {
		$desc[0] ??= '';
		$desc[1] ??= '';
		$desc[2] ??= '';
	}

	/**
	 * Cleans up `$args['_range']` index by assigning missing values.
	 * Goes up (or down) to steps e-/+10.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Rounded decamals are now allowed in the steps [2].
	 *
	 * @param array $range The range index with plausibly missing or exceeding values.
	 *              Passed by reference.
	 */
	private function clean_range_index( array &$range ) {
		$range[0] = (string) ( $range[0] ?? '' );
		$range[1] = (string) ( $range[1] ?? '' );
		$range[2] = isset( $range[2] ) ? (string) rtrim( rtrim( \sprintf( '%.10F', $range[2] ), '0' ), '.' ) : '';
	}

	/**
	 * Creates fields data based on input.
	 *
	 * @since 1.3.0
	 * @since 2.1.0 Now accepts mixed quotes as values.
	 *
	 * @param array $data The field's data.
	 * @return string The field's data.
	 */
	private function get_fields_data( $data ) {

		$ret = [];

		foreach ( $data as $k => $v ) {
			if ( ! \is_scalar( $v ) ) {
				$ret[] = \sprintf(
					'data-%s="%s"',
					strtolower( preg_replace(
						'/([A-Z])/',
						'-$1',
						preg_replace( '/[^a-z0-9_\-]/i', '', $k )
					) ), // dash case.
					htmlspecialchars( json_encode( $v, \JSON_UNESCAPED_SLASHES ), \ENT_COMPAT, 'UTF-8' ),
				);
			} else {
				$ret[] = \sprintf(
					'data-%s="%s"',
					strtolower( preg_replace(
						'/([A-Z])/',
						'-$1',
						preg_replace( '/[^a-z0-9_\-]/i', '', $k )
					) ), // dash case.
					\esc_attr( $v ),
				);
			}
		}

		return ' ' . implode( ' ', $ret );
	}

	/**
	 * Creates fields pattern based on input.
	 *
	 * @since 1.3.0
	 *
	 * @param string $pattern The field's pattern.
	 * @return string The field's pattern.
	 */
	private function get_fields_pattern( $pattern ) {
		return \sprintf( 'pattern="%s"', $pattern );
	}

	/**
	 * Returns various input (text/numeric) inputs.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args The input field arguments.
	 * @return string The input field.
	 */
	private function create_input_field_by_type( $args ) {

		switch ( $args['_type'] ) {
			case 'date':
			case 'number':
			case 'range':
				$this->clean_range_index( $args['_range'] );

				$s_range  = '';
				$s_range .= '' !== $args['_range'][0] ? \sprintf( 'min=%s', $args['_range'][0] ) : '';
				$s_range .= '' !== $args['_range'][1] ? \sprintf( ' max=%s', $args['_range'][1] ) : '';
				$s_range .= '' !== $args['_range'][2] ? \sprintf( ' step=%s', $args['_range'][2] ) : '';

				if ( isset( $args['_pattern'] ) )
					$s_pattern = $this->get_fields_pattern( $args['_pattern'] );
				break;

			case 'color':
				// TODO
				break;

			case 'tel':
				$s_pattern = $this->get_fields_pattern(
					( $args['_pattern'] ?? '' )
						?: '(\+|00)(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$'
				);
				break;

			default:
			case 'text':
			case 'password':
			case 'url':
			case 'search':
			case 'time':
			case 'week':
			case 'month':
			case 'datetime-local':
			case 'hidden':
				if ( isset( $args['_pattern'] ) )
					$s_pattern = $this->get_fields_pattern( $args['_pattern'] );
		}

		// s = Escaped.
		$s_type = \esc_attr( $args['_type'] );
		$s_name = $s_id = $this->get_field_id();

		return vsprintf(
			'<div class="tsfem-%s-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				$s_type,
				$this->create_field_description( $args, $s_id ),
				\sprintf(
					'<div class="tsfem-form-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<input type=%s id="%s" name=%s value="%s" %s %s %s %s %s>',
						[
							$s_type,
							$s_id,
							$s_name,
							\esc_attr( $this->get_field_value( $args['_default'] ) ),
							$s_range ?? '',
							$s_pattern ?? '',
							$args['_req'] ? 'required' : '',
							! empty( $args['_ph'] ) ? \sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '',
							isset( $args['_data'] ) ? $this->get_fields_data( $args['_data'] ) : '',
						]
					)
				),
			]
		);
	}

	/**
	 * Creates single-select fields.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args The select field arguments.
	 * @return string A select field.
	 */
	private function create_select_field( $args ) {

		// s = Escaped.
		$s_name     = $s_id = $this->get_field_id();
		$s_data     = isset( $args['_data'] ) ? $this->get_fields_data( $args['_data'] ) : '';
		$s_required = $args['_req'] ? 'required' : '';

		$multiple = 'selectmulti' === $args['_type'];

		return vsprintf(
			'<div class="tsfem-%s-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				$args['_type'], // Doesn't need escaping.
				$this->create_field_description( $args, $s_id ),
				\sprintf(
					'<div class="tsfem-form-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<select id="%s" name=%s %s %s %s>%s</select>',
						[
							$s_id,
							$s_name,
							$s_required,
							( $multiple ? 'multiple' : '' ),
							$s_data,
							$this->get_select_options( $args['_select'], $this->get_field_value( $args['_default'] ), $multiple ),
						]
					)
				),
			]
		);
	}

	/**
	 * Returns select options from generator.
	 *
	 * @since 1.3.0
	 * @uses $this->generate_select_fields()
	 *
	 * @param array        $select   The select fields.
	 * @param string|array $selected The default or currently selected field.
	 * @param bool         $multiple Whether it's a multi-select field.
	 * @return string The select options.
	 */
	private function get_select_options( $select, $selected = '', $multiple = false ) {

		$_fields = '';

		foreach ( $this->generate_select_fields( $select, $selected, $multiple ) as $field ) {
			// Already escaped.
			$_fields .= $field;
		}

		return $_fields;
	}

	/**
	 * Generates select fields.
	 *
	 * It maintains its own iteration. Therefore, it's not depending on class iterator fields.
	 * It will clean up $selected if it is found. Unless $multiple is true.
	 *
	 * Heavily optimized for performance. Therefore, not according to DRY standards.
	 *
	 * @since 1.3.0
	 * @generator
	 *
	 * @param array        $select   The select fields.
	 * @param string|array $selected The default or currently selected field.
	 * @param bool         $multiple Whether it's a multi-select field.
	 * @yield The select option field.
	 */
	private function generate_select_fields( $select, $selected = '', $multiple = false ) {

		static $_level = 0;

		if ( null !== $selected && '' !== $selected && [] !== $selected ) {

			// Convert $selected to array.
			$a_selected = (array) $selected;

			foreach ( $select as $args ) {

				if ( $_level ) {
					// Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '&nbsp;&nbsp;' . $args[1] : str_repeat( '&nbsp;&nbsp;', $_level ) . $args[1];
					$args[1] = '&nbsp;&nbsp;' . $args[1];
				}

				$s_selected = \in_array( $args[0], $a_selected, true ) ? ' selected' : '';
				// Prevent more lookups if found.
				$_next = $s_selected && ! $multiple ? '' : $selected;

				if ( isset( $args[2] ) ) {
					// Level up.
					yield \sprintf( '<optgroup label="%s">', $args[1] );
					yield \sprintf( '<option value="%s"%s>%s</option>', $args[0], $s_selected, $args[1] );
					++$_level;
					yield $this->get_select_options( $args[2], $_next, $multiple );
					--$_level;
					yield '</optgroup>';
				} else {
					yield \sprintf( '<option value="%s"%s>%s</option>', $args[0], $s_selected, $args[1] );
				}
			}
		} else {
			foreach ( $select as $args ) {

				if ( $_level ) {
					// Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '&nbsp;&nbsp;' . $args[1] : str_repeat( '&nbsp;&nbsp;', $_level ) . $args[1];
					$args[1] = '&nbsp;&nbsp;' . $args[1];
				}

				if ( isset( $args[2] ) ) {
					// Level up.
					yield \sprintf( '<optgroup label="%s">', $args[1] );
					yield \sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					++$_level;
					yield $this->get_select_options( $args[2] );
					--$_level;
					yield '</optgroup>';
				} else {
					yield \sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
				}
			}
		}
	}

	/**
	 * Creates multi-select fields that are accessible. i.e. It creates an iterated
	 * list of checkboxes.
	 *
	 * @NOTE: Propagates to an iterator and generator. It will reset the next level of iterations.
	 *        This shouldn't accompany any negative effect.
	 *
	 * @iterator Careful: it can and will reset current iteration count.
	 * @since 1.3.0
	 *
	 * @param array $args The multi-select field arguments.
	 * @return string An accessible option checkbox list acting as a multiselect field.
	 */
	private function create_select_multi_a11y_field( $args ) {

		// Not escaped.
		$title = $args['_desc'][0];

		// s = escaped
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1], ! empty( $args['_md'] ) ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2], ! empty( $args['_md'] ) ) : '';
		$s_data = isset( $args['_data'] ) ? $this->get_fields_data( $args['_data'] ) : '';

		$s_data_required = $args['_req'] ? 'data-required=1' : '';

		return vsprintf(
			'<div class="tsfem-select-multi-a11y-field-wrapper tsfem-form-setting tsfem-flex" %s>%s%s</div>',
			[
				$s_data,
				\sprintf(
					'<div class="tsfem-form-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-select-multi-a11y-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<div class="tsfem-form-setting-label-item tsfem-flex"><span class="%s">%s</span></div>',
								[
									\sprintf( 'tsfem-form-option-title%s', ( $s_desc ? ' tsfem-form-option-has-description' : '' ) ),
									\sprintf( '<strong>%s</strong> %s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				\sprintf(
					'<div class="tsfem-form-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-form-multi-select-wrap %s" id="%s" %s>%s</div>',
						[
							isset( $args['_display'] ) && 'row' === $args['_display'] ? 'tsfem-form-multi-select-wrap-row' : '',
							$this->get_field_id(),
							$s_data_required,
							$this->get_select_multi_a11y_options( $args['_select'], $this->get_field_value( $args['_default'] ), true ),
						]
					)
				),
			]
		);
	}

	/**
	 * Passes select options through a generator to return multi-select checkbox
	 * fields.
	 *
	 * Propagates to an iterator. That's why it can reiterate.
	 * It loops back to itself to generate more fields.
	 *
	 * @since 1.3.0
	 * @iterator Careful: it can and will reset current iteration count.
	 *
	 * @param array $select   The select fields.
	 * @param array $selected The default or currently selected fields.
	 * @param bool  $reset    Determines whether to reset the iterations.
	 * @return string An unordered list of checkboxes acting as multiselect.
	 */
	private function get_select_multi_a11y_options( $select, $selected = [], $reset = false ) {

		$_fields = '';

		foreach ( $this->generate_select_multi_a11y_fields( $select, $selected ) as $field ) {
			// Already escaped.
			$_fields .= $field;
		}

		$reset and $this->reiterate();

		return $_fields;
	}

	/**
	 * Generates multi select fields that are accessible.
	 * Instead of creating an atrocious browser multi-select, it replaces the input
	 * with checkboxes that iterate.
	 *
	 * For this reason, the POST return value will differ from regular select fields.
	 *
	 * Heavily optimized for performance. Therefore, not according to DRY standards.
	 *
	 * @since 1.3.0
	 * @generator
	 * @iterator
	 *
	 * @param array $select   The select fields.
	 * @param array $selected The default or currently selected fields.
	 * @yield An unordered list of checkboxes acting as multiselect.
	 */
	private function generate_select_multi_a11y_fields( $select, $selected = [] ) {

		yield '<ul class=tsfem-form-multi-a11y-wrap>';

		foreach ( $select as $args ) {

			$this->iterate();

			if ( isset( $args[2] ) ) {
				// Level up.
				yield \sprintf( '<li><strong>%s</strong></li>', $args[1] );
				if ( [] !== $selected && \in_array( $args[0], $selected, true ) ) {
					yield \sprintf(
						'<li><label><input type=checkbox name="%1$s" id="%1$s" value="%2$s" checked>%3$s</label></li>',
						$this->get_field_id(),
						$args[0],
						$args[1]
					);
				} else {
					yield \sprintf(
						'<li><label><input type=checkbox name="%1$s" id="%1$s" value="%2$s">%3$s</label></li>',
						$this->get_field_id(),
						$args[0],
						$args[1]
					);
				}
				yield '<li>';
				// Level continue.
				yield $this->get_select_multi_a11y_options( $args[2], $selected );
				// Level down.
				yield '</li>';
			} else {
				if ( [] !== $selected && \in_array( $args[0], $selected, true ) ) {
					yield \sprintf(
						'<li><label><input type=checkbox name="%1$s" id="%1$s" value="%2$s" checked>%3$s</label></li>',
						$this->get_field_id(),
						$args[0],
						$args[1]
					);
				} else {
					yield \sprintf(
						'<li><label><input type=checkbox name="%1$s" id="%1$s" value="%2$s">%3$s</label></li>',
						$this->get_field_id(),
						$args[0],
						$args[1]
					);
				}
			}
		}

		yield '</ul>';
	}

	/**
	 * Creates an image URL and ID field.
	 * Adds dynamic buttons based on previous set value.
	 *
	 * Requires media scripts to be registered.
	 *
	 * @since 1.3.0
	 * @see TSF_Extension_Manager\Traits\UI
	 * @see method TSF_Extension_Manager\AJAX\_wp_ajax_crop_image() The AJAX cropper callback.
	 * @uses \get_upload_iframe_src()
	 *
	 * @param array $args The field generation arguments.
	 * @return string The image field input with buttons.
	 */
	private function create_image_field( $args ) {

		// s = Escaped.
		$s_field_id = $this->get_field_id();
		$s_url_name = $this->get_sub_field_id( 'url' );
		$s_url_id   = "{$s_field_id}-url";
		$s_id_name  = $this->get_sub_field_id( 'id' );
		$s_id_id    = "{$s_field_id}-id";

		$s_url_ph    = ! empty( $args['_ph'] ) ? \sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_url_value = \esc_url(
			$this->get_field_value_by_key(
				$this->get_raw_sub_field_id( 'url', 'associative' ),
				$args['_default']['url']
			)
		);
		$s_id_value  = \absint(
			$this->get_field_value_by_key(
				$this->get_raw_sub_field_id( 'id', 'associative' ),
				$args['_default']['id']
			)
		);

		$url_readonly = false;

		if ( ! empty( $args['_readonly'] ) ) {
			$args['_data']['readonly'] = true;
			$url_readonly              = true;
		}

		$s_required = $args['_req'] ? 'required' : '';
		$s_data     = isset( $args['_data'] ) ? $this->get_fields_data( $args['_data'] ) : '';

		if ( $s_id_value )
			$url_readonly = true;

		return vsprintf(
			'<div class="tsfem-image-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				$this->create_field_description( $args, $s_url_id ),
				vsprintf(
					'<div class="tsfem-form-setting-input tsfem-flex">%s%s<div class="tsfem-form-image-buttons-wrap hide-if-no-tsf-js">%s</div></div>',
					[
						vsprintf(
							'<input type=url id="%s" name=%s value="%s" %s %s%s%s>',
							[
								$s_url_id,
								$s_url_name,
								$s_url_value,
								$s_required,
								$s_url_ph,
								$url_readonly ? ' readonly' : '',
								$s_data,
							]
						),
						vsprintf(
							'<input type=hidden id="%s" name=%s value="%s">',
							[
								$s_id_id,
								$s_id_name,
								$s_id_value,
							]
						),
						get_image_uploader_form( [
							'id'           => $s_field_id,
							'button_class' => [
								'set'    => [
									'tsfem-button',
									'tsfem-button-primary',
									'tsfem-button-small',
								],
								'remove' => [
									'tsfem-button',
									'tsfem-button-small',
								],
							],
						] ),
					]
				),
			]
		);
	}

	/**
	 * Creates a checkbox field.
	 *
	 * @since 1.3.0 Instated.
	 * @since 2.2.0 Populated.
	 *
	 * @param array $args The field generation arguments.
	 * @return string The checkbox field.
	 */
	private function create_checkbox_field( $args ) {

		// s = Escaped.
		$s_name     = $s_id = $this->get_field_id();
		$s_required = $args['_req'] ? 'required' : '';
		$s_data     = isset( $args['_data'] ) ? $this->get_fields_data( $args['_data'] ) : '';

		return vsprintf(
			'<div class="tsfem-checkbox-field-wrapper tsfem-form-setting tsfem-flex">%s%s</div>',
			[
				$this->create_field_description( $args, $s_id ),
				\sprintf(
					'<div class="tsfem-form-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<label class=tsfem-form-checkbox-settings-content-label><input type=checkbox id="%s" name=%s value=1 %s %s %s> %s</label>',
						[
							$s_id,
							$s_name,
							$this->get_field_value( $args['_default'] ) ? 'checked' : '',
							$s_required,
							$s_data,
							\esc_html( $args['_check'][0] ),
						]
					)
				),
			]
		);
	}

	/**
	 * These methods are acting as a placeholder for future implementation.
	 * Will be built when required.
	 *
	 * @since 1.3.0 Instated.
	 *
	 * @param array $args The field generation arguments.
	 * @return void
	 */
	// phpcs:disable
	private function create_radio_field( $args ) {}
	private function create_textarea_field( $args ) {}
	// phpcs:enable
}
