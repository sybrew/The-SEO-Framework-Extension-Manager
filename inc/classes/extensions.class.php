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
_tsf_extension_manager_load_trait( 'extensions' );

/**
 * Require extensions layout traits depending on admin page type.
 * @since 1.0.0
 * @NOTE The following check is insecure, but the included traits are only
 *       deferred for their memory usage. Secure_Abstract prevents interaction.
 */
if ( is_admin() && tsf_extension_manager()->is_tsf_extension_manager_page( false ) ) {
	_tsf_extension_manager_load_trait( 'extensions-layout' );
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
final class Extensions extends Secure_Abstract {
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
				static::$extensions = static::get_extensions();
				break;

			case 'activation' :
			case 'list' :
			case 'load' :
				static::$extensions = static::get_extensions();
				break;

			case 'ajax' :
			default :
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
				case 'ajax' :
				case 'activation' :
				case 'list' :
				case 'load' :
					tsf_extension_manager()->_verify_instance( $instance, $bits[1] ) or tsf_extension_manager()->_maybe_die();
					self::set( '_type', $type );
					static::set_up_variables();
					break;

				case 'reset' :
					self::reset();
					break;

				default :
					self::reset();
					self::invoke_invalid_type( __METHOD__ );
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
			case 'layout_header' :
				return static::get_layout_header();
				break;

			case 'layout_content' :
				return static::get_layout_content();
				break;

			case 'extensions_checksum' :
				return static::get_extensions_checksum();
				break;

			case 'extensions_list' :
				return static::get_extensions();
				break;

			case 'active_extensions_list' :
				return static::get_active_extensions();
				break;

			default :
				the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct get type.' );
				break;
		endswitch;

		return false;
	}

	/**
	 * Sets instance's extension slug to handle.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The extension slug.
	 */
	public static function set_instance_extension_slug( $slug ) {

		self::verify_instance() or die;

		switch ( self::get_property( '_type' ) ) :
			case 'activation' :
			case 'ajax' :
				static::$current_slug = isset( static::$extensions[ $slug ] ) ? $slug : '';
				break;

			default :
				break;
		endswitch;
	}

	/**
	 * Removes items from extension list based on $what and website conditions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extensions The extension list.
	 * @param string|array $what What to filter out of the list.
	 * @return array The leftover extensions.
	 */
	private static function filter_extensions( array $extensions = array(), $what = 'maybe_network' ) {

		if ( is_array( $what ) ) {
			foreach ( $what as $filter )
				$extensions = static::filter_extensions( $extensions, $filter );

			return $extensions;
		}

		if ( 'maybe_network' === $what ) {
			$network_mode = tsf_extension_manager()->is_plugin_in_network_mode();

			if ( $network_mode )
				return $extensions;

			$filters = array( 'network' => '0' );
		} elseif ( 'network' === $what ) {
			$filters = array( 'network' => '1' );
		} elseif ( 'single' === $what ) {
			$filters = array( 'network' => '0' );
		}

		return wp_list_filter( $extensions, $filters, 'AND' );
	}
}
