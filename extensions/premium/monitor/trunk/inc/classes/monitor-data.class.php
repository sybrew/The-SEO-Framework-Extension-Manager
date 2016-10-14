<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Monitor_Data
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Require extension options trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'extension-options' );

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use TSF_Extension_Manager\Construct_Core_Once_Interface as Construct_Core_Once_Interface;
use TSF_Extension_Manager\Extension_Options as Extension_Options;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

class Monitor_Data {
	use Enclose_Stray_Private, Construct_Core_Once_Interface, Extension_Options;

	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . ( is_admin() ? '\\Monitor_Admin' : '\\Monitor_Frontend' );
		$this instanceof $that or wp_die( -1 );

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'monitor';
	}

	protected function api_get_remote_data() {
	//	return tsf_extension_manager()->get_api_url();
	}

	protected function fetch_new_data( $ajax = false ) {

		static $fetched = null;

		if ( isset( $fetched ) )
			return $fetched;

		$data = $this->api_get_remote_data();

		if ( is_array( $data ) ) {
			foreach ( $data as $type => $values ) {
				$this->store_session_data( $type, $values );
				$this->update_option( $type, $values );
			}
			$fetched = true;
		} else {
			$fetched = false;
		}

		return $fetched;
	}

	protected function get_remote_data( $type = '', $ajax = false ) {

		if ( ! $type )
			return false;

		$data = $this->get_option( $type, array() );

		if ( empty( $data ) ) {
			$this->fetch_new_data( $ajax );

			$data = $this->get_session_data( $type );
		}
	}

	protected function get_session_data( $type ) {
		return $this->store_session_data( $type );
	}

	protected function store_session_data( $type = '', $data = null ) {

		static $data_cache = array();

		if ( isset( $data_cache[ $type ] ) )
			return $data_cache[ $type ];

		if ( isset( $data ) )
			return $data_cache[ $type ] = $data;

		return false;
	}

	protected function get_data( $type, $default = null ) {

		$data = $this->get_remote_data( $type, false );

		return empty( $data ) ? $default : $data;
	}
}
