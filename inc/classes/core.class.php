<?php
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
 * Class TSF_Extension_Manager_Core
 *
 * Holds plugin core functions.
 *
 * @since 1.0.0
 */
class TSF_Extension_Manager_Core {

	/**
	 * Constructor.
	 * Latest Class. Doesn't have parent.
	 */
	public function __construct() { }

	/**
	 * Returns the minimum role required to adjust and access settings.
	 *
	 * Applies filter 'tsf_extension_manager_settings_capability' : string
	 * This filter changes the minimum role for viewing and editing the plugin's settings.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function settings_capability() {
		return (string) apply_filters( 'tsf_extension_manager_settings_capability', 'manage_options' );
	}

	/**
	 * Determines whether the plugin is network activated.
	 * Only works in admin screens.
	 *
	 * @since 1.0.0
	 * @staticvar bool $network_mode
	 *
	 * @return bool Whether the plugin is active in network mode.
	 */
	public function is_plugin_in_network_mode() {

		static $network_mode = null;

		if ( isset( $network_mode ) )
			return $network_mode;

		//* Simply crash on front-end. Calling this is bad practice.
		if ( is_plugin_active_for_network( TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ) )
			return $network_mode = true;

		return $network_mode = false;
	}

	/**
	 * Returns escaped Extension manager URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page The admin menu page slug.
	 * @param array $args Other query arguments.
	 * @return string Escaped Admin URL.
	 */
	public function get_admin_page_url( $page = '', $args = array() ) {

		$page = $page ? $page : $this->page_id;

		$url = add_query_arg( $args, menu_page_url( $page, 0 ) );

		return esc_url( $url );
	}

}
