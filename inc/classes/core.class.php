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
	 * @since 1.0.0
	 *
	 * @return string The minimum required capability for SEO Settings.
	 */
	public function can_do_settings() {
		return can_do_tsf_extension_manager_settings();
	}

	/**
	 * Determines whether the plugin is network activated.
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

		if ( ! is_multisite() )
			return $network_mode = false;

		$plugins = get_site_option( 'active_sitewide_plugins' );

		return $network_mode = isset( $plugins[TSF_EXTENSION_MANAGER_PLUGIN_BASENAME] );
	}

	/**
	 * Returns escaped admin page URL.
	 * Defaults to the Extension Manager page ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page The admin menu page slug.
	 * @param array $args Other query arguments.
	 * @return string Escaped Admin URL.
	 */
	public function get_admin_page_url( $page = '', $args = array() ) {

		$page = $page ? $page : $this->plugin_page_id;

		$url = add_query_arg( $args, menu_page_url( $page, 0 ) );

		return esc_url( $url );
	}

	/**
	 * Fetch an instance of a TSF_Extension_Manager_{*}_List_Table Class.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return object|bool Object on success, false if the class does not exist.
	 */
	public function get_list_table( $class, $args = array() ) {

		$classes = array(
			//Site Admin
			'TSF_Extension_Manager_Install_List_Table' => 'install',
			// Network Admin
			'TSF_Extension_Manager_Install_List_Table_MS' => 'ms-install',
		);

		if ( isset( $classes[ $class ] ) ) {
			foreach ( (array) $classes[ $class ] as $required )
				require_once( TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'tables/' . $required . '-list-table.class.php' );

			if ( isset( $args['screen'] ) )
				$args['screen'] = convert_to_screen( $args['screen'] );
			elseif ( isset( $GLOBALS['hook_suffix'] ) )
				$args['screen'] = get_current_screen();
			else
				$args['screen'] = null;

			return new $class( $args );
		}

		return false;
	}

}
