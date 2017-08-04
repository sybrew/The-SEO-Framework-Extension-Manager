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
 * Packs Schema.org JSON-LD data from input with the help from a forged-through
 * Schema.org defined layout.
 *
 * Compliments FormGenerator.
 * @see \TSF_Extension_Manager\FormGenerator
 *
 * @since 1.3.0
 * @access private
 * @uses trait TSF_Extension_Manager\Enclose_Core_Final
 * @see TSF_Extension_Manager\Traits\Overload
 *
 * @final Can't be extended.
 * @return void|fase on failure.
 */
final class SchemaPacker {
	use Enclose_Core_Final;

	/**
	 * Holds the bits and maximum iterations thereof.
	 *
	 * @since 1.3.0
	 *
	 * @var int $bits
	 * @var int $max_it
	 */
	private $bits,
	        $max_it;

	/**
	 * Maintains the reiteration level and the iteration within.
	 *
	 * This corresponds to FormGenerator, but, it doesn't keep perfect track of
	 * all data.
	 * It's only maintained when iterating, as we access the `'$nth'` schema key.
	 *
	 * NOTE: $it should not ever exceed $max_it.
	 *
	 * @since 1.3.0
	 *
	 * @var int   $level
	 * @var int   $it
	 */
	private $level = 0,
	        $it = 0;

	private $data;
	private $schema;
	private $output;

	//	private $unpack = false;
	//	private $it;

	public function __construct( array $data, \stdClass $schema ) {

		$this->data =& $data;

		if ( ! isset( $schema->_OPTIONS, $schema->_MAIN ) ) {
			return false;
		}

		$o = $schema->_OPTIONS;

		$architecture = $o->architecture ?: ( \tsf_extension_manager()->is_64() ? 64 : 32 );
		$levels = $o->levels ?: 5;
		$this->bits = floor( $architecture / $levels );
		$this->max_it = pow( 2, $this->bits );

		$this->schema = $schema->_MAIN;

		return true;
	}

	public function _iterate_base() {
		$this->level or ++$this->level;
		$this->iterate();
	}

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
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits()
	 *
	 * @return int The current iteration of the current level.
	 */
	private function get_current_iteration() {
		return $this->it >> ( ( $this->level - 1 ) * $this->bits );
	}

	/**
	 * Returns iteration from level.
	 *
	 * @since 1.3.0
	 * @uses $this->it
	 * @uses $this->level
	 * @uses $this->bits()
	 *
	 * @param int $l The level to get
	 * @return int The current iteration of the current level.
	 */
	private function get_iteration_from_level( $l = 0 ) {
		return ( $this->it >> ( ( $l - 1 ) * $this->bits ) ) & ( pow( 2, $this->bits ) - 1 );
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
		$this->it &= ~( ( pow( 2, $this->bits ) - 1 ) << ( $this->bits * ( --$this->level ) ) );
		//= Unset highest level.
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
		//* Add $c + 1 to current level. We normally count from 0.
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
		//* Subtract $c + 1 to current level. We normally count from 0.
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
		$this->it &= ~( ( pow( 2, $this->bits ) - 1 ) << ( $this->bits * ( $this->level - 1 ) ) );
		$this->iterate();
	}

	/**
	 * @since 1.3.0
	 */
	private function pack( \stdClass $schema ) {

		$_ = [];

		foreach ( $this->generate_data( $schema ) as $key => $data ) {
			isset( $key, $data ) and $_[ $key ] = $data;
		}

		return (object) $_;
	}

	/**
	 * @since 1.3.0
	 * @generator
	 */
	private function generate_data( \stdClass $schema ) {

		foreach ( $schema as $k => $s ) {
			$a = $this->get_data( $k, $s );
			yield key( $a ) => current( $a );
		}
	}

	private function get_data( $key, \stdClass $schema ) {

		switch ( $schema->_data->_type ) {
			case 'single' :
				$data = $this->make_data( $schema );
				break;

			case 'object' :
				$data = $this->pack( $schema->_data->_config );
				break;

			case 'iterate' :
				$data = $this->make_iteration( $schema );
				break;
		}

		return [ $key => $data ];
	}

	private function make_iteration( \stdClass $schema ) {

		$count = $this->access_data( $schema->_data->_access );

		$_schema = $schema->_data->_config;

		$this->level();

		$data = [];
		for ( $i = 0; $i < $count; $i++ ) {
			$data[] = $this->pack( $_schema );
			$this->iterate();
		}

		$this->delevel();

		return $data;
	}

	private function make_data( \stdClass $schema ) {

		switch ( $schema->_data->_from ) {
			case 'default' :
				$value = $schema->_data->_value;
				break;

			case 'data' :
				$value = $this->access_data( $schema->_data->_access );
				break;

			case 'bloginfo' :
				$value = \get_bloginfo( $schema->_data->_value );
				break;

			default :
				return null;
				break;
		}

		if ( isset( $schema->_handlers->_escape ) )
			$value = $this->escape( $value, $schema->_handlers->_escape );

		if ( isset( $schema->_handlers->_condition ) )
			$value = $this->condition( $value, $schema->_handlers->_condition );

		if ( isset( $schema->_handlers->_out ) ) {
			$value = $this->convert( $value, $schema->_handlers->_out );
		}

		return $value;
	}

	private function access_data( array $keys ) {

		$v = $this->data;
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

	private function escape( $value, $how ) {

		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = $this->escape( $v, $how );
			}
			return $value;
		}

		switch ( $how ) :
			case 'sanitize_key' :
				return \sanitize_key( $value );

			case 'convert_to_host' :
				return parse_url( $value, PHP_URL_HOST ) ?: '';

			case 'esc_url_raw' :
				return \esc_url_raw( $value );

			default :
			case 'sanitize_text_field' :
				return \sanitize_text_field( $value );
		endswitch;
	}

	private function condition( $value, $what ) {
		//* TODO This requires traversing
		return $value;
	}

	private function convert( $value, $to ) {

		switch ( $to ) :
			case 'string' :
				return (string) $value;

			case 'integer' :
				return (int) $value;

			case 'float' :
				return (float) $value;

			case 'array' :
				return array_values( (array) $value ) ?: [];

			case 'object' :
				return (object) $value;

			default :
				return $value;
		endswitch;
	}
}
