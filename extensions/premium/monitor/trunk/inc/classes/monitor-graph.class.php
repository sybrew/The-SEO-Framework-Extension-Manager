<?php
//* THIS FILE ISN'T LOADED (YET). Creating interactive (zooming) graphs is very difficult and requires a lot of time :(.

/**
 * @package TSF_Extension_Manager\Extension\Monitor\Tests
 */
namespace TSF_Extension_Manager\Extension;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Core_Static_Final as Construct_Core_Static_Final;

/**
 * Class TSF_Extension_Manager\Extension\Monitor_Graph
 *
 * Renders Monitor Data input to generate graphs.
 *
 * @since 1.0.0
 * @access private
 */
final class Monitor_Graph {
	use Enclose_Core_Final, Construct_Core_Static_Final;

	/**
	 * The object instance.
	 *
	 * @since 1.0.0
	 *
	 * @var object|null This object instance.
	 */
	private static $instance = null;

	/**
	 * The constructor. Does nothing.
	 */
	private function construct() { }

	/**
	 * Handles unapproachable invoked methods.
	 * Silently ignores errors on this call.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return string Empty.
	 */
	public function __call( $name, $arguments ) {
		return '';
	}

	/**
	 * Sets the class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public static function set_instance() {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return object The current instance.
	 */
	public static function get_instance() {

		if ( is_null( static::$instance ) ) {
			static::set_instance();
		}

		return static::$instance;
	}

	public function stats_uptime( $data ) {

		$chartdata = '';
		$id = 'uptime';

		foreach ( $this->generate_chart_points( $data, [ 'x' => 'unixtimestamp', 'y' => 'stackline', 'gap' => 300 ] ) as $points ) {
			$chartdata .= sprintf( '{x=%s,y=%s}', json_encode( $points['x'] ), json_encode( $points['y'] ) );
		}

		if ( $chartdata ) {
			$this->store_graph_js_data( $chartdata, $id, 'stackline' );
		} else {
			// TODO
		}

		return [
			'content' => $this->render_graph_canvas( $id ),
		];
	}

	public function stats_perfomance() {

	}

	public function stats_traffic() {

	}

	protected function generate_chart_points( $data, $args = [] ) {

		$defaults = [
			'x' => 'unixtimestamp',
			'y' => 'line',
			'gap' => 300, //5 min
		];

		$args = \wp_parse_args( $args, $defaults );

		//* TODO: shift data?
		// \the_seo_framework()->set_timezone();

		switch ( $args['y'] ) :
			case 'line' :
			case 'stackline' :

				$first_key = key( $data );
				/* TODO: shift data?
				$difference = $this->get_timezone_difference( $first_key );
				if ( 0 !== $difference ) {
					$data = $this->shift_data_stack( $data, $difference, $args['gap'] );
				}
				*/

				//* Remove overflowing data from the first hours of the "day before".
				unset( $data[ $first_key ] );

				end( $data );
				//* Remove overflowing data from the first hours of the "day after".
				$last_key = key( $data );
				unset( $data[ $last_key ] );

				//* Reset array pointer
				reset( $data );

				foreach ( $data as $x => $y ) {
					$x = \the_seo_framework()->gmt2date( 'Y-m-d H:i', date( 'Y-m-d', $x ) ) . ' GMT';
					yield [ 'x' => $x, 'y' => $y ];
				}
				break;

			default :
				yield [ 'x' => '', 'y' => '' ];
				break;
		endswitch;

		//* TODO: shift data?
		// \the_seo_framework()->reset_timezone();
	}

	protected function store_graph_js_data( $data, $id, $type = 'line' ) {

		$var = 'tsfemGraph_' . $id;

		$jsdata = sprintf( 'var %s={"data":[%s],"type":%s};', \esc_js( $var ), \wp_json_encode( $data ), \wp_json_encode( $type ) );

		$this->set_js_data_cache( $jsdata, $id );
	}

	protected function set_js_data_cache( $data = '', $id = '', $get = false ) {

		if ( empty( $id ) )
			return '';

		static $cache = [];

		if ( empty( $cache[ $id ] ) )
			$cache[ $id ] = '';

		$cache[ $id ] .= is_string( $data ) && $data ? $data : '';

		if ( $get )
			return false === empty( $cache[ $id ] ) ? $cache[ $id ] : '';
	}

	protected function get_js_data_cache( $id = '' ) {
		return $this->set_js_data_cache( '', $id, true );
	}

	/**
	 * Calculates timezone difference based on input timestamp.
	 * Expects to be run between the_seo_framework() methods 'set_timezone' and 'reset_timezone'.
	 *
	 * @since 1.0.0
	 * @staticvar int $difference
	 *
	 * @param int $timestamp The external timestamp.
	 * @return int The local timezone difference from the external one.
	 */
	protected function get_timezone_difference( $timestamp ) {

		if ( empty( $timestamp ) )
			return '';

		static $difference = null;

		if ( is_null( $difference ) )
			$difference = strtotime( 'midnight', $timestamp ) - $timestamp;

		return $difference;
	}

	protected function shift_data_stack( $data, $difference, $gap ) {
		//* TODO.
		return $data;

		$_data = [];

		$shift = $difference / $gap;

		if ( $difference < 0 ) {
			$previous = [ 0, 0 ];
			foreach ( $data as $timestamp => $value ) {
				$timestamp = $timestamp - $difference;

				foreach ( explode( ',', $value ) as $t => $v ) {
					$v = explode( 'x', $v );
					$_shift = 0;
					foreach ( $v as $count => $type ) {
						if ( $count > $shift ) {
							//* Nothing to worry about.
							// TODO
							break 2;
						} else {
							//* Get from cache and move on?
							// TODO
							break 1;
						}
					}
				}

				$_data = [ $timestamp, $value ];
				$previous = [ $dif_key, $dif_value ];
			}
		} elseif ( $difference > 0 ) {

		}

		return $_data;
	}

	protected function get_start_of_day( $timestamp = '' ) {

		$date = strtotime( 'gmt', $timestamp );

		return $timestamp;
	}

	protected function render_graph_canvas( $id = '' ) {

		if ( empty( $id ) )
			return '';

		$nosupport = \__( "Your browser doesn't support HTML5 canvas.", 'the-seo-framework-extension-manager' );
		$nojs = \__( 'This element requires JavaScript.', 'the-seo-framework-extension-manager' );

		$cdata = sprintf( '<script type="text/javascript">/*<![CDATA[*/%s/*]]>*/</script>', $this->get_js_data_cache( $id ) );
		//* @TODO set class.
		$canvas = sprintf(
			'<canvas id="tsfem-graph-%s" style="border:1px solid #d3d3d3;">%s%s</canvas>',
			\esc_attr( $id ), sprintf( '<p>%s</p>', \esc_html( $nosupport ) ), sprintf( '<noscript><p>%s</p></noscript>', \esc_html( $nojs ) )
		);

		return $cdata . $canvas;
	}
}
