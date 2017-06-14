<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Front
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface as Construct_Master_Once_Final_Interface;

/**
 * Class TSF_Extension_Manager\Extension\Front
 *
 * Holds extension front-end methods.
 *
 * @since 1.0.0
 * @access private
 */
final class Front implements \ArrayAccess, \Iterator {
	use Enclose_Core_Final, Construct_Master_Once_Final_Interface;

	private function construct() { }

	/**
	 * Placeholder... PHP7+
	 */
/*	private function generator( iterable $data, int $i ) : string {
		yield [ ( $a = key( $data ) ) => $data[ $a ] ];
		return '';
	}
*/
}
