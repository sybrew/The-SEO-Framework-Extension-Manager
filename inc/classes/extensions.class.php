<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
\TSF_Extension_Manager\_load_trait( 'manager/extensions' );

/**
 * Require extensions layout traits depending on admin page type.
 * @since 1.0.0
 * @NOTE The following check is insecure, but the included traits are only
 *       deferred for their memory usage. Secure_Abstract prevents interaction.
 * @TODO Move trait items to own static class.
 */
if ( \tsf_extension_manager()->is_tsf_extension_manager_page( false ) ) {
	\TSF_Extension_Manager\_load_trait( 'manager/extensions-layout' );
} else {
	//* Empty dummy traits.
	trait Extensions_Layout { }
	trait Extensions_I18n { }
}

/**
 * Class TSF_Extension_Manager\Extensions.
 *
 * Handles extensions pane and activation.
 *
 * @since 1.0.0
 * @access private
 *         You'll need to invoke the TSF_Extension_Manager\Core verification handler.
 *         Which is impossible.
 * @final
 */
final class Extensions extends Secure_Abstract {
	use Extensions_I18n,
		Extensions_Properties,
		Extensions_Actions,
		Extensions_Layout;

	/**
	 * Sets up class variables.
	 *
	 * @since 1.0.0
	 */
	private static function set_up_variables() {

		switch ( self::get_property( '_type' ) ) :
			case 'overview' :
			case 'activation' :
			case 'list' :
			case 'load' :
			case 'ajax_layout' :
				static::$extensions = static::get_extensions();
				break;

			default :
				break;
		endswitch;
	}

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Required. The instance type. Passed by reference.
	 * @param string $instance Required. The instance key. Passed by reference.
	 * @param array $bits Required. The instance bits.
	 * @return void
	 */
	public static function initialize( $type = '', &$instance = '', &$bits = null ) {

		self::reset();

		if ( empty( $type ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an initialization type.' );
			return;
		}

		self::set( '_wpaction' );

		switch ( $type ) :
			case 'overview' :
			case 'activation' :
			case 'list' :
			case 'load' :
			case 'ajax_layout' :
				\tsf_extension_manager()->_verify_instance( $instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die();
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

	/**
	 * Returns the trend call.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @param string $slug The extension slug. Required with AJAX.
	 * @return string|bool False on failure. String on success.
	 */
	public static function get( $type = '', $slug = '' ) {

		self::verify_instance() or die;

		if ( empty( $type ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an get type.' );
			return false;
		}

		switch ( $type ) :
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

			case 'ajax_get_extension_header' :
				return static::get_extension_header( $slug );
				break;

			case 'ajax_get_extension_desc_footer' :
				return static::get_extension_description_footer( static::get_extension( $slug ), false );
				break;

			default :
				\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct get type.' );
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
	 * @since 1.3.0 : Now uses its own sorting system, speeding it up by roughly 40 times.
	 *
	 * @param array $extensions The extension list.
	 * @param string|array $what What to filter out of the list.
	 * @return array The leftover extensions.
	 */
	private static function filter_extensions( array $extensions = [], $what = 'maybe_network' ) {

		//* Temporarily. Exchange for count( $what ) > 1
		if ( is_array( $what ) ) {
			foreach ( $what as $w ) {
				//* Reassigns and retests itself until filtered.
				$extensions = static::filter_extensions( $extensions, $w );
			}

			return $extensions;
		}

		//* Temporarily check. Will be substituted by new functions that pass these as filters.
		if ( 'maybe_network' === $what ) {
			$network_mode = \tsf_extension_manager()->is_plugin_in_network_mode();

			if ( $network_mode )
				return $extensions;

			$filters = [ 'network' => '0' ];
		} elseif ( 'network' === $what ) {
			$filters = [ 'network' => '1' ];
		} elseif ( 'single' === $what ) {
			$filters = [ 'network' => '0' ];
		} else {
			return $extensions;
		}

		$_extensions = [];
		$_to_unset = [];

		foreach ( $filters as $k => $v ) {
			$_test = array_column( $extensions, $k, 'slug' );
			foreach ( $_test as $_slug => $_compare ) {
				if ( $_compare === $v ) {
					$_extensions[] = $_slug;
				} else {
					$_to_unset[] = $_slug;
				}
			}
		}

		foreach ( $_to_unset as $slug ) {
			unset( $_extensions[ $slug ] );
		}

		$output = [];
		foreach ( $_extensions as $slug ) {
			$output[ $slug ] = $extensions[ $slug ];
		}

		return $output;
	}
}
