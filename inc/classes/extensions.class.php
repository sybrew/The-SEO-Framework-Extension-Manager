<?php
/**
 * @package TSF_Extension_Manager\Classes
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
 * Require extensions traits.
 * @since 1.0.0
 */
tsf_extension_manager_load_trait( 'extensions' );

/**
 * Require extensions layout traits depending on admin page type.
 * @since 1.0.0
 */
if ( tsf_extension_manager()->is_tsf_extension_manager_page() ) {
	tsf_extension_manager_load_trait( 'extensions-layout' );
} else {
	//* Empty dummy traits.
	trait Extensions_Layout { }
	trait Extensions_i18n { }
}

/**
 * Class TSF_Extension_Manager\Extensions.
 *
 * Handles extensions pane and activation.
 *
 * @since 1.0.0
 * @access private
 * 		You'll need to invoke the TSF_Extension_Manager\Core verification handler. Which is impossible.
 * @final Please don't extend this.
 */
final class Extensions extends Secure {
	use Extensions_i18n, Extensions_Properties, Extensions_Actions, Extensions_Layout;

	/**
	 * Sets up class variables.
	 *
	 * @since 1.0.0
	 */
	private static function set_up_variables() {

		switch ( self::get_property( '_type' ) ) :
			case 'overview' :
				static::$header = array();
				static::$plugins = static::get_plugins();
				break;

			case 'check' :
				static::$plugins = static::get_plugins();
				break;
		endswitch;

	}

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Required. The instance type.
	 * @param string $instance Required. The instance key.
	 * @param array $bits Required. The instance bits.
	 */
	public static function initialize( $type = '', $instance = '', $bits = null ) {

		self::reset();

		if ( empty( $type ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an initialization type.' );
		} else {

			self::set( '_wpaction' );

			switch ( $type ) :
				case 'overview' :
					tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', 'overview' );
					static::set_up_variables();
					break;

				case 'check' :
					tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', 'check' );
					static::set_up_variables();
					break;

				case 'activation' :
					tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', 'activation' );
					static::set_up_variables();
					break;

				case 'reset' :
					self::reset();
					break;

				default :
					self::reset();
					the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct initialization type.' );
					break;
			endswitch;
		}
	}

	/**
	 * Returns the trend call.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @return string
	 */
	public static function get( $type = '' ) {

		self::verify_instance() or die;

		if ( empty( $type ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an get type.' );
			return false;
		}

		switch ( $type ) :
			case 'header' :
				return static::get_layout_header();
				break;

			case 'content' :
				return static::get_layout_content();
				break;

			case 'get-active' :
				return static::get_active_plugins();
				break;

			case 'do-activation' :
				return static::do_plugin_activation();
				break;

			case 'do-deactivation' :
				return static::do_plugin_deactivation();
				break;

			default :
				the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct get type.' );
				break;
		endswitch;

		return false;
	}

	/**
	 * Removes items from plugin list based on $what and website conditions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugins The plugin list.
	 * @param string|array $what What to filter out of the list.
	 * @return array The leftover plugins.
	 */
	private static function filter_plugins( array $plugins = array(), $what = 'maybe_network' ) {

		if ( is_array( $what ) ) {
			foreach ( $what as $filter )
				$plugins = static::filter_plugins( $plugins, $filter );

			return $plugins;
		}

		if ( 'maybe_network' === $what ) {
			$network_mode = tsf_extension_manager()->is_plugin_in_network_mode();

			if ( $network_mode )
				return $plugins;

			$filters = array( 'network' => '0' );
		} elseif ( 'network' === $what ) {
			$filters = array( 'network' => '1' );
		} elseif ( 'single' === $what ) {
			$filters = array( 'network' => '0' );
		}

		return wp_list_filter( $plugins, $filters, 'AND' );
	}
}
