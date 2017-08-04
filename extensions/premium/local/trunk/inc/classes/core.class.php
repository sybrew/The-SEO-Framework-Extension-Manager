<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin
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
 * Require extension options trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension-options' );

/**
 * Class TSF_Extension_Manager\Extension\Local\Core
 *
 * Holds extension core methods.
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 */
class Core {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Core_Interface,
		\TSF_Extension_Manager\Extension_Options;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$that = __NAMESPACE__ . ( \is_admin() ? '\\Admin' : '\\Front' );
		$this instanceof $that or \wp_die( -1 );

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'local';
	}

	protected function get_packed_data( $pretty = false ) {

		$data = $this->get_stale_extension_options();
		$schema = json_decode( file_get_contents( TSFEM_E_LOCAL_DIR_PATH . 'lib/schema/schema.json', false ) );

		$packer = new \TSF_Extension_Manager\SchemaPacker( $data, $schema );

		$count = isset( $data['department']['count'] ) ? $data['department']['count'] : 0;
		$_json = null;
		if ( $count ) {
			$_json = &$packer->_collector();

			$packer->_iterate_base();
			$_json = $packer->_pack();

			if ( $count > 1 ) {
				$_json->department = [];
				for ( $i = 1; $i <= $count; $i++ ) {
					$packer->_iterate_base();
					$_json->department[] = (object) $packer->_pack();
				}
			}
		}

		$options = JSON_UNESCAPED_SLASHES;
		$options |= $pretty ? JSON_PRETTY_PRINT : 0;

		return json_encode( $packer->_get(), $options );
	}
}
