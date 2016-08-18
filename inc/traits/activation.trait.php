<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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

/**
 * Holds activation data functions for TSF_Extension_Manager\Activation.
 *
 * @since 1.0.0
 * @access private
 */
trait Activation_Data {

	/**
	 * Returns product title to activate.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_activation_product_title() {
		return 'The SEO Framework Premium';
	}

	/**
	 * Returns domain host of plugin holder.
	 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
	 * so only the host portion of the URL can be sent. For example the host portion might be
	 * www.example.com or example.com. http://www.example.com includes the scheme http,
	 * and the host www.example.com.
	 * Sending only the host also eliminates issues when a client site changes from http to https,
	 * but their activation still uses the original scheme.
	 *
	 * @since 1.0.0
	 *
	 * @return string Domain Host.
	 */
	protected function get_activation_site_domain() {
		return str_ireplace( array( 'http://', 'https://' ), '', home_url() );
	}

	/**
	 * Returns API option prefix.
	 *
	 * @since 1.0.0
	 *
	 * @return string.
	 */
	protected function get_activation_prefix() {

		static $prefix = null;

		if ( isset( $prefix ) )
			return $prefix;

		return $prefix = str_ireplace( array( ' ', '_', '&', '?' ), '_', strtolower( $this->get_activation_product_title() ) );
	}
}
