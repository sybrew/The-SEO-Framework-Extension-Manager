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
 * Class TSF_Extension_Manager\SecureOption.
 *
 * Verifies options update handling.
 *
 * @since 1.0.0
 * @access private
 *         You'll need to invoke the TSF_Extension_Manager\Core verification handler. Which is impossible.
 * @final
 */
final class SecureOption extends Secure_Abstract {

	/**
	 * The update action instances.
	 *
	 * @since 1.0.0
	 *
	 * @var array The instance array.
	 */
	private static $_instance = [];

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Required. The instance type. Passed by reference.
	 * @param string $instance Required. The instance key. Passed by reference.
	 * @param array $bits Required. The instance bits.
	 */
	public static function initialize( $type = '', &$instance = '', &$bits = null ) {

		self::reset();

		if ( empty( $type ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an initialization type.' );
		} else {

			switch ( $type ) :
				case 'update_option' :
				case 'update_option_instance' :
					\tsf_extension_manager()->_verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', $type );
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
	 * Returns false, unused.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @return bool false
	 */
	public static function get( $type = '' ) {
		return false;
	}

	/**
	 * Sets up verification instance.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure;
	 */
	public static function set_update_instance( $instance, $bits ) {

		switch ( self::get_property( '_type' ) ) :
			case 'update_option' :
			case 'update_option_instance' :
				static::$_instance = [ $instance, $bits ];
				return true;
				break;

			default :
				self::reset();
				\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct instance type.' );
				break;
		endswitch;

		return false;
	}

	/**
	 * Verifies verification instance and updates option if verified.
	 * Performs wp_die() on failure.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The new, unserialized option value.
	 * @param mixed $old_value The old option value.
	 * @param string $option The option name.
	 * @return mixed $value on success.
	 */
	public static function verify_option_instance( $value, $old_value, $option ) {

		$type = self::get_property( '_type' );

		if ( empty( self::$_instance ) || empty( $type ) ) {
			self::reset();
			\wp_die( '<code>' . \esc_html( $option ) . '</code> is a protected option.' );
		}

		$instance = self::$_instance;

		if ( isset( $instance[0] ) && isset( $instance[1][1] ) ) {
			\tsf_extension_manager()->_verify_instance( $instance[0], $instance[1][1] );
		} else {
			self::reset();
			\wp_die( 'Instance verification could not be done on option update.' );
		}

		static $verified = false;

		if ( false === $verified ) {
			//* Always update instance before updating options when deactivating.
			if ( 'update_option_instance' === $type ) {
				$verified = true;
			} elseif ( 'update_option' === $type ) {
				$options = \get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS );
				if ( \tsf_extension_manager()->verify_options_hash( serialize( $options ) ) ) {
					$verified = true;
				} else {
					self::reset();

					$notice = "Options have been altered outside of this plugin's scope. This is not allowed for security reasons. Please deactivate your account and try again.";

					if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
						echo json_encode( [ 'status' => [ 'success' => false, 'notice' => \esc_html( $notice ) ] ] );
						\wp_die();
					} else {
						\wp_die( \esc_html( $notice ) );
					}
				}
			}
		}

		static::verify_option_update( true );

		return $value;
	}

	/**
	 * Determines if the option has been verified.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $set Whether to verify the option update.
	 * @return bool True on success, false on failure.
	 */
	protected static function verify_option_update( $set = false ) {

		static $verified = false;

		if ( $set )
			$verified = true;

		return $verified;
	}

	/**
	 * Determines if the option has been verified.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function verified_option_update() {
		return static::verify_option_update();
	}
}
