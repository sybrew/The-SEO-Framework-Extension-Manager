<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

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
 * Class TSF_Extension_Manager\Core
 *
 * Holds plugin core functions.
 *
 * @since 1.0.0
 */
class Core {

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
	protected function __construct() { }

	/**
	 * Verifies views instances.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance
	 * @param int $bit
	 * @return bool True if verified.
	 */
	protected function verify_instance( $instance, $bit ) {
		return $instance === $this->get_verification_instance( $bit );
	}

	/**
	 * Generates view instance through bittype and hash comparison.
	 *
	 * @since 1.0.0
	 * @staticvar string $instance
	 * @staticvar int $bit
	 * @staticvar int $_bit
	 *
	 * @param int $bit
	 * @return string $instance The instance key.
	 */
	protected function get_verification_instance( $bit = null ) {

		static $instance = array();

		$bits = $this->get_bits( true );
		$_bit = $bits[1];

		if ( isset( $instance[ $_bit ] ) ) {
			if ( empty( $instance[ $bit ] ) )
				wp_die( 'Instance verification failed.' );

			return $instance[ $bit ];
		}

		$hash = wp_hash( $_bit . '\\' . __METHOD__ . '\\' . $bit, 'tsf-view-nonce-' . $bit );

		return $instance[ $bit ] = $instance[ $_bit ] = $hash;
	}

	/**
	 * Generates verification bits based on time.
	 * It's crack-able, but you'll need to know exactly when to intercept. One
	 * bit mistake and the plugin stops :).
	 *
	 * @since 1.0.0
	 *
	 * @param bool $previous Whether to fetch the previous set of bits.
	 * @return array The verification bits.
	 */
	protected function get_bits( $previous = false ) {

		static $bit = null;
		static $_bit = null;

		if ( $previous )
			return array( $bit, $_bit );

		if ( null === $bit ) {
			$bit = $_bit = mt_rand( 0, 12034337 );
			$_bit++;
		}

		$bit | $_bit && $bit++ ^ ~ $_bit-- && $bit ^ $_bit++ && $bit | $_bit++;

		return array( $bit, $_bit );
	}

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
	 * Returns admin page URL.
	 * Defaults to the Extension Manager page ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page The admin menu page slug. Defaults to TSF Extension Manager's.
	 * @param array $args Other query arguments.
	 * @return string Admin Page URL.
	 */
	public function get_admin_page_url( $page = '', $args = array() ) {

		$page = $page ? $page : $this->seo_extensions_page_slug;

		$url = add_query_arg( $args, menu_page_url( $page, 0 ) );

		return $url;
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
	 * @since 1.0.0
	 * @credits Akismet For most code.
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 *        Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, array $args = array() ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$bits = $this->get_bits();
		$_instance = $this->get_verification_instance( $bits[1] );

		$file = TSF_EXTENSION_MANAGER_DIR_PATH . 'views/' . $view . '.php';
		$file = str_replace( '/', DIRECTORY_SEPARATOR, $file );

		include( $file );
	}

	/**
	 * Creates a link and returns it.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The link arguments : {
	 *		'url'     => string The URL. Required.
	 *		'target'  => string The target. Default '_self'.
	 *		'class'   => string The link class. Default ''.
	 *		'title'   => string The link title. Default ''.
	 *		'content' => string The link content. Default ''.
	 * }
	 * @return string escaped link.
	 */
	public function get_link( array $args = array() ) {

		if ( empty( $args ) )
			return '';

		$defaults = array(
			'url'     => '',
			'target'  => '_self',
			'class'   => '',
			'title'   => '',
			'content' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$url = $args['url'] ? esc_url( $args['url'] ) : '';

		if ( empty( $url ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, esc_html__( 'No valid URL was supplied.', 'the-seo-framework-extension-manager' ), null );
			return '';
		}

		$url = ' href="' . $url . '"';
		$class = $args['class'] ? ' class="' . esc_attr( $args['class'] ) . '"' : '';
		$target = ' target="' . esc_attr( $args['target'] ) . '"';
		$title = $args['title'] ? ' title="' . esc_attr( $args['title'] ) . '"' : '';

		return '<a' . $url . $class . $target . $title . '>' . esc_html( $args['content'] ) . '</a>';
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
	 * Updates TSF Extension Manager option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed $value The option value.
	 * @return bool True on success or the option is unchanged, false on failure.
	 */
	protected function update_option( $option, $value ) {

		if ( ! $option )
			return false;

		$options = $this->get_all_options();

		//* If option is unchanged, return true.
		if ( isset( $options[ $option ] ) && $value === $options[ $option ] )
			return true;

		$options[ $option ] = $value;

		return update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );
	}

	/**
	 * Updates multiple TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options : {
	 *		$option_name => $value,
	 * }
	 * @return bool True on success, false on failure or when options haven't changed.
	 */
	protected function update_option_multi( array $options = array() ) {

		if ( empty( $options ) )
			return false;

		$_options = $this->get_all_options();

		//* If options are unchanged, return true.
		if ( serialize( $options ) === serialize( $_options ) )
			return true;

		$options = wp_parse_args( $options, $_options );

		return update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );
	}
}
