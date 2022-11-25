<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Packs Schema.org JSON-LD data from input with the help from a forged-through
 * Schema.org defined layout.
 *
 * Compliments FormGenerator.
 *
 * @since 1.3.0
 * @access private
 * @see \TSF_Extension_Manager\FormGenerator
 *
 * @final Can't be extended.
 */
final class SchemaPacker {

	/**
	 * @since 1.3.0
	 * @see $max_it
	 * @var int Maximum bits assignable ((64|32)/levels-requested).
	 */
	private $bits;

	/**
	 * @since 1.3.0
	 * @see $bits
	 * @var int Max iteration of bits for current OS (64/32 bits).
	 */
	private $max_it;

	/**
	 * This corresponds to FormGenerator, but, it doesn't keep perfect track of
	 * all data.
	 * It's only maintained when iterating, as we access the `'$nth'` schema key.
	 *
	 * @since 1.3.0
	 * @see $it
	 * @var int The current reiteration level.
	 */
	private $level = 0;

	/**
	 * This corresponds to FormGenerator, but, it doesn't keep perfect track of
	 * all data.
	 * It's only maintained when iterating, as we access the `'$nth'` schema key.
	 *
	 * NOTE: $it should not ever exceed $max_it.
	 *
	 * @since 1.3.0
	 * @see $level
	 * @var int The current iteration of level.
	 */
	private $it = 0;

	/**
	 * @since 1.3.0
	 * @see $schema
	 * @var array All data packable via $schema.
	 */
	private $data;

	/**
	 * @since 1.3.0
	 * @see $data
	 * @var object Schema.org setup for $data.
	 */
	private $schema;

	/**
	 * @since 1.3.0
	 * @var object Schema.org output.
	 */
	private $output;

	/**
	 * @since 1.3.0
	 * @var array $registered_conditions The registered conditions to handle.
	 */
	private $registered_conditions;

	/**
	 * Constructor. Sets up class main variables.
	 *
	 * @param array     $data   The data to iterate over.
	 * @param \stdClass $schema The JSON decoded schema to use. {
	 *    object '_OPTIONS' : Any processing options attached.
	 *    object '_MAIN'    : The main data to iterate over.
	 * }
	 * @return bool true On setup. False otherwise.
	 */
	public function __construct( $data, $schema ) {

		if ( ! isset( $schema->_OPTIONS, $schema->_MAIN ) )
			return false;

		$this->data =& $data;
		$o          = $schema->_OPTIONS;

		$architecture = $o->architecture ?: ( \tsfem()->is_64() ? 64 : 32 );
		$levels       = $o->levels ?: 5;
		$this->bits   = floor( $architecture / $levels );
		$this->max_it = 2 ** $this->bits;

		$this->schema = $schema->_MAIN;

		return true;
	}

	/**
	 * Adds iterations prior to packing that ups the first $nth value in $this->schema.
	 *
	 * @since 1.3.0
	 *
	 * @param int <unsigned> (R>0) $by The base iterations.
	 */
	public function _iterate_base( $by = 1 ) {
		$this->level or ++$this->level;
		$this->iterate( $by - 1 );
	}

	/**
	 * Packs current iteration data.
	 *
	 * @since 1.3.0
	 *
	 * @return object The packed data.
	 */
	public function _pack() {
		return $this->pack( $this->schema );
	}

	/**
	 * Collects the output.
	 *
	 * A fun little thing. Simply call this function and write the variable to
	 * adjust its output.
	 *
	 * @since 1.3.0
	 * @collector
	 *
	 * @return object $this->output
	 */
	public function &_collector() {
		return $this->output;
	}

	/**
	 * Returns the collected output without collecting.
	 *
	 * @since 1.3.0
	 *
	 * @return object $this->output
	 */
	public function _get() {
		return $this->output;
	}

	/**
	 * Returns iteration from current level.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Offsetted level calculation to variable to circumvent PHP7 compat checks.
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits()
	 *
	 * @return int The current iteration of the current level.
	 */
	private function get_current_iteration() {
		$it_level = $this->level - 1;
		return $this->it >> $it_level;
	}

	/**
	 * Returns iteration from level.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Offsetted level calculation to variable to circumvent PHP7 compat checks.
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits()
	 *
	 * @param int $l The level to get
	 * @return int The current iteration of the current level.
	 */
	private function get_iteration_from_level( $l = 0 ) {
		$bits_level   = ( $l - 1 ) * $this->bits;
		$written_bits = ( 2 ** $this->bits - 1 );
		return ( $this->it >> $bits_level ) & $written_bits;
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
	 * Packs input iteration data. Checks condition prior to packing.
	 * Can and will destroy output based on conditions.
	 *
	 * @since 1.3.0
	 *
	 * @param \stdClass $schema The schema data to pack.
	 * @return object The packed data.
	 */
	private function pack( $schema ) {

		$_ = [];

		foreach ( $this->generate_data( $schema ) as $key => $data ) {
			switch ( $this->get_condition( $key ) ) {
				case 'kill_pack':
					return null;

				case 'kill_sub':
					break 2;

				case 'kill_this':
					continue 2;

				default:
					break;
			}

			isset( $key, $data ) and $_[ $key ] = $data;
		}

		return (object) $_;
	}

	/**
	 * Generates data by looping over the schema.
	 *
	 * @since 1.3.0
	 * @generator
	 *
	 * @param \stdClass $schema The schema data to generate from.
	 * @yield array { string $key => mixed $value }
	 */
	private function generate_data( $schema ) {

		foreach ( $schema as $k => $s ) {
			yield $k => $this->get_value( $k, $s );
		}
	}

	/**
	 * Returns value from $key based on current $schema.
	 *
	 * @since 1.3.0
	 *
	 * @param string    $key    The key to get the value of.
	 * @param \stdClass $schema The schema data to get the value from.
	 * @return mixed The key's value.
	 */
	private function get_value( $key, $schema ) {

		switch ( $schema->_data->_type ) {
			case 'single':
				$value = $this->make_data( $schema );
				break;

			case 'object':
				$value = $this->pack( $schema->_data->_config );
				break;

			case 'iterate':
				$value = $this->make_iteration( $schema );
				break;
		}

		if ( isset( $schema->_handlers->_escape ) )
			$value = $this->escape( $value, $schema->_handlers->_escape );

		if ( isset( $schema->_handlers->_condition ) ) {
			$this->registered_conditions[ $key ] = [];

			$value = $this->condition( $key, $value, $schema->_handlers->_condition );
		}

		if ( isset( $value ) && isset( $schema->_handlers->_out ) )
			$value = $this->convert( $value, $schema->_handlers->_out );

		return $value;
	}

	/**
	 * Creates iteration for $schema.
	 *
	 * @since 1.3.0
	 *
	 * @param \stdClass $schema The schema data to iterate.
	 * @return mixed The packed iteration data, if successful.
	 */
	private function make_iteration( $schema ) {

		$count = $this->access_data( $schema->_data->_access );

		$_schema = $schema->_data->_config;

		$this->level();

		$data = [];
		for ( $i = 0; $i < $count; $i++ ) {
			$_d = $this->pack( $_schema );

			isset( $_d ) and $data[] = $_d;

			$this->iterate();
		}

		$this->delevel();

		return $data ?: null;
	}

	/**
	 * Builds data based on $schema.
	 *
	 * @since 1.3.0
	 *
	 * @param \stdClass $schema The schema data to generate data from.
	 * @return mixed The expected data.
	 */
	private function make_data( $schema ) {

		switch ( $schema->_data->_from ) {
			case 'default':
				$value = $schema->_data->_value;
				break;

			case 'data':
				$value = $this->access_data( $schema->_data->_access );
				break;

			case 'bloginfo':
				$value = \get_bloginfo( $schema->_data->_access );
				break;

			case 'concat':
				$value = $this->concat( $schema->_data->_config );
				break;

			default:
				$value = null;
				break;
		}

		return $value;
	}

	/**
	 * Builds concatenated data based on $schema.
	 *
	 * @since 1.3.0
	 *
	 * @param \stdClass $schema The schema data to pack and concatenate.
	 * @return mixed The concatenated data.
	 */
	private function concat( $schema ) {

		$value = '';
		// Not invoking a generator. Data does not yield, but return.
		foreach ( ( $this->pack( $schema ) ) as $k => $v ) {
			$value .= $v;
		}

		return $value;
	}

	/**
	 * Builds accessed data based on $schema from $this->data.
	 *
	 * @since 1.3.0
	 * @uses $this->data
	 *
	 * @param array $keys The $this->data access keys.
	 * @return mixed The data from $this->data's $keys' level.
	 */
	private function access_data( $keys ) {

		$v     = $this->data;
		$level = 0;

		foreach ( $keys as $k ) {
			if ( '$nth' === $k ) {
				$level++;
				$k = $this->get_iteration_from_level( $level );
			}

			if ( isset( $v[ $k ] ) ) {
				$v = $v[ $k ];
			} else {
				$v = null;
				break;
			}
		}

		return $v;
	}

	/**
	 * Escapes data based on prior input schema.
	 * Can loop through arrays of data.
	 *
	 * @todo implement this function in $this->condition() for conditional escape.
	 * @since 1.3.0
	 *
	 * @param mixed  $value The value to escape. $keys The $this->data access keys.
	 * @param string $how The how-to escape $value.
	 * @return mixed The escaped data.
	 */
	private function escape( $value, $how ) {

		if ( \is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = $this->escape( $v, $how );
			}
			return $value;
		}

		switch ( $how ) :
			case 'sanitize_key':
				return \sanitize_key( $value );

			case 'convert_to_host':
				return parse_url( $value, PHP_URL_HOST ) ?: '';

			case 'esc_url_raw':
				return \esc_url_raw( $value, [ 'https', 'http' ] );

			default:
			case 'sanitize_text_field':
				return \sanitize_text_field( $value );
		endswitch;
	}

	/**
	 * Gets condition for $this->pack(), if any.
	 * Note, the conditions get cleaned up after each $this->pack() run.
	 *
	 * @since 1.3.0
	 *
	 * @param string $key The schema conditional access key.
	 * @return integer|string The condition output, based on importance rather than order.
	 */
	private function get_condition( $key ) {

		if ( empty( $this->registered_conditions[ $key ] ) ) {
			unset( $this->registered_conditions[ $key ] );
			return -1;
		}

		$kill_this = $kill_sub = $kill_pack = 0;
		// Write to variables. TODO use array_flip/fill or eqv, which are safer?
		// $this->registered_conditions[ $key ] = [ 'kill_pack', 'kill_sub', 'kill_this' ];
		foreach ( $this->registered_conditions[ $key ] as $v )
			${$v} = 1;

		unset( $this->registered_conditions[ $key ] );

		// Returns in order of impact.
		if ( $kill_pack )
			return 'kill_pack';

		if ( $kill_sub )
			return 'kill_sub';

		if ( $kill_this )
			return 'kill_this';

		// This should never happen.
		return 0;
	}

	/**
	 * Conditions $value for $key based on schema $what.
	 *
	 * @since 1.3.0
	 * @since 2.0.0 Added level ($level) and iteration ($it) access in the 'set' _do->_to action.
	 * @todo implement self-resolving staticvar that breaks the loop?
	 *
	 * @param string       $key   The value's key
	 * @param mixed        $value The value to be conditioned.
	 * @param array|object $what The conditional parameters. Can and must loop
	 *                     over all conditions that apply, in order.
	 * @return mixed The likely conditioned value.
	 */
	private function condition( $key, $value, $what ) {

		if ( \is_array( $what ) && \count( $what ) > 1 ) {
			foreach ( $what as $w )
				$value = $this->condition( $key, $value, $w );

			return $value;
		}

		$c = \is_array( $what ) ? (object) current( $what ) : $what;

		switch ( $c->_if ) {
			case 'this':
				$v =& $value;
				break;

			case 'data':
				$v = $this->access_data( $c->_access );
				break;

			default:
				return $value;
		}

		switch ( $c->_op ) {
			case '==':
				// phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison -- that's the whole idea.
				$action = $v == $c->_value;
				break;

			case '===':
				$action = $v === $c->_value;
				break;

			case '!=':
				// phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison -- that's the whole idea.
				$action = $v != $c->_value;
				break;

			case '!==':
				$action = $v !== $c->_value;
				break;

			case '>':
				$action = $v > $c->_value;
				break;

			case '!':
			case 'empty':
				$action = ! $v;
				break;

			case 'count':
				// $v can be NULL or string.
				if ( ! $v ) {
					$action = 0 === $c->_value;
				} else {
					if ( ! \is_array( $v ) && ! \is_object( $v ) ) {
						$action = 1 === $c->_value;
					} else {
						$action = \count( $v ) === $c->_value;
					}
				}
				break;

			case 'count_gt':
				// $v can be NULL or string.
				if ( ! $v ) {
					$action = 0 > $c->_value;
				} else {
					if ( ! \is_array( $v ) && ! \is_object( $v ) ) {
						$action = 1 > $c->_value;
					} else {
						$action = \count( $v ) > $c->_value;
					}
				}
				break;

			case 'type_is':
				$action = \gettype( $v ) === $c->_value;
				break;

			case 'type_not':
				$action = \gettype( $v ) !== $c->_value;
				break;

			default:
				$action = false;
				break;
		}

		if ( ! $action )
			return $value;

		switch ( $c->_do ) {
			case 'kill_this':
			case 'kill_sub':
			case 'kill_pack':
				$this->registered_conditions[ $key ][] = $c->_do;
				return null;

			case 'set':
				if ( '$it' === $c->_to ) :
					return $this->it;
				elseif ( '$level' === $c->_to ) :
					return $this->level;
				endif;
				return $c->_to;

			case 'current':
				return current( $value );

			case 'round':
				return (float) number_format( (float) $value, $c->_to );

			case 'convert':
				return $this->convert( $value, $c->_to );

			default:
				return $value;
		}
	}

	/**
	 * Converts value to set type.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed  $value The value to convert.
	 * @param string $to    The type to convert the value to.
	 * @return mixed The probable converted value.
	 */
	private function convert( $value, $to ) {

		switch ( $to ) :
			case 'string':
				return (string) $value;

			case 'boolean':
				return (bool) $value;

			case 'integer':
				return (int) $value;

			case 'float':
				return (float) $value;

			case 'array':
				return array_values( (array) $value ) ?: [];

			case 'object':
				return (object) $value;

			default:
				return $value;
		endswitch;
	}
}
