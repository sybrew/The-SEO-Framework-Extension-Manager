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
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

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

		return $network_mode = isset( $plugins[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
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

		$page = $page ? $page : $this->seo_extensions_page_slug;

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

			if ( isset( $args['screen'] ) ) {
				$args['screen'] = convert_to_screen( $args['screen'] );
			} elseif ( isset( $GLOBALS['hook_suffix'] ) ) {
				$args['screen'] = get_current_screen();
			} else {
				$args['screen'] = null;
			}

			return new $class( $args );
		}

		return false;
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 * 		Each array key is converted to a variable with its value attached.
	 *
	 * @credits Akismet For most code.
	 */
	protected function get_view( $view, array $args = array() ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$file = TSF_EXTENSION_MANAGER_DIR_PATH . 'views/' . $view . '.php';

		include( $file );
	}

	/**
	 * Returns TSF Extension Manager options array.
	 *
	 * @since 1.0.0
	 *
	 * @return array TSF Extension Manager options.
	 */
	protected function get_all_options() {
		return get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, array() );
	}

	/**
	 * Fetches TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The Option name.
	 * @param mixed $default The fallback value if the option doesn't exist.
	 * @param bool $use_cache Whether to store and use options from cache.
	 * @param bool $reset_cache Whether to reset the cache.
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	protected function get_option( $option, $default = null, $use_cache = true, $reset_cache = false ) {

		if ( ! $option )
			return null;

		if ( false === $use_cache ) {
			$options = $this->get_all_options();

			return isset( $options[ $option ] ) ? $options[ $option ] : $default;
		}

		static $options_cache = array();

		if ( $reset_cache )
			$options_cache = array();
		elseif ( isset( $options_cache[ $option ] ) )
			return $options_cache[ $option ];

		$options = $this->get_all_options();

		return $options_cache[ $option ] = isset( $options[ $option ] ) ? $options[ $option ] : $default;
	}

	/**
	 * Resets the activation option cache.
	 *
	 * @since 1.0.0
	 */
	protected function reset_option_cache() {
		return $this->get_option( true, null, true, true );
	}

	/**
	 * Updates TSF Extension Manager option and flushes that specific cache.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed $value The option value.
	 * @return bool True on success, false on failure.
	 */
	protected function update_option( $option, $value ) {

		if ( ! $option )
			return false;

		$options = $this->get_all_options();

		$options[ $option ] = $value;

		return update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );
	}
}
