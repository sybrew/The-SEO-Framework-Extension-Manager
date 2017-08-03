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
 * @return void
 */
final class SchemaPacker {
	use Enclose_Core_Final;

	private $data;
	private $output;

	//	private $unpack = false;
	//	private $it;

	public function __construct( array $data, \stdClass $schema ) {

		$this->data =& $data;

		$_ = &$this->collector();
		$_ = $this->pack( $schema );

	//	var_dump( $_ );

		//var_dump( json_encode( $_, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	public function &collector() {
		return $this->output;
	}

	public function get() {
		return $this->output;
	}

	private function pack( \stdClass $schema ) {

		$_ = [];

		foreach ( $this->generate_data( $schema ) as $key => $data ) {
			isset( $key, $data ) and $_[ $key ] = $data;
		}

		return (object) $_;
	}

	private function generate_data( \stdClass $schema ) {
		foreach ( current( $schema ) as $k => $s ) {
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
				$_schema = [];
				$_schema['_SUB'] = $schema->_data->_config;
				$data = $this->pack( (object) $_schema );
				break;

			case 'iterate' :
				$data = $this->make_iteration( $schema );
				break;
		}

		return [ $key => $data ];
	}

	private function make_iteration( \stdClass $schema ) {

		$count = $this->access_data( $schema->_data->_access );

		$_schema = [];
		$_schema[] = $schema->_data->_config;
		$_schema = (object) $_schema;

		$data = [];
		for ( $i = 0; $i < $count; $i++ ) {
			$this->it = $i;
			$data[] = $this->pack( $_schema );
		}
		$this->it = 0;

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

		if ( isset( $schema->_handlers->_out ) )
			$value = $this->convert( $value, $schema->_handlers->_out );

		return $value;
	}

	private function access_data( array $keys ) {

		$v = $this->data;
		$i = 0;

		foreach ( $keys as $k ) {
			'$nth' === $k and $k = $this->it;
			$v = isset( $v[ $k ] ) ? $v[ $k ] : null;
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
				return (array) $value;

			case 'object' :
				return (object) $value;

			default :
				return $value;
		endswitch;
	}
}
