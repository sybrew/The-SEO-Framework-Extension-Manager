<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Admin
 * @package TSF_Extension_Manager\Extension\Cord\Front
 */

namespace TSF_Extension_Manager\Extension\Cord;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Cord extension for The SEO Framework
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Require extension options trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/options' );

/**
 * Class TSF_Extension_Manager\Extension\Cord\Core
 *
 * Holds extension core methods.
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 */
class Core {
	use \TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Core_Interface;

	/**
	 * Child constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		/**
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index    = 'cord';
		$this->o_defaults = [
			'analytics' => [
				'google_analytics' => [
					'tracking_id'               => '',
					'enhanced_link_attribution' => 0,
					'ip_anonymization'          => 1,
				],
				'facebook_pixel'   => [
					'pixel_id' => '',
				],
			],
		];
	}
}
