<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @since 1.0.0
	 * @var array The update instance array.
	 */
	private static $_instance = []; // phpcs:ignore, PSR2.Classes.PropertyDeclaration.Underscore -- confusing otherwise.

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type     Required. The instance type.
	 * @param string $instance Required. The instance key. Passed by reference.
	 * @param array  $bits     Required. The instance bits. Passed by reference.
	 */
	public static function initialize( $type, &$instance = '', &$bits = null ) {

		self::reset();

		if ( ! $type ) {
			\tsf()->_doing_it_wrong( __METHOD__, 'You must specify an initialization type.' );
		} else {
			switch ( $type ) {
				case 'update_option':
					\tsfem()->_verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', $type );
					break;

				case 'reset':
					self::reset();
					break;

				default:
					self::reset();
					self::invoke_invalid_type( __METHOD__ );
			}
		}
	}

	// phpcs:disable
	/**
	 * Returns false, unused.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @return bool false
	 */
	public static function get( $type ) {
		return false;
	}
	// phpcs:enable

	/**
	 * Sets up verification instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance The update instance.
	 * @param array  $bits     The 4-dimension validation bits.
	 * @return bool True on success, false on failure;
	 */
	public static function set_update_instance( $instance, $bits ) {

		switch ( self::get_property( '_type' ) ) {
			case 'update_option':
				static::$_instance = [ $instance, $bits ];
				return true;

			default:
				self::reset();
				\tsf()->_doing_it_wrong( __METHOD__, 'You must specify a correct instance type.' );
		}

		return false;
	}

	/**
	 * Verifies verification instance and updates option if verified.
	 * Performs wp_die() on failure.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 This now returns the old value instead of executing wp_die();
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param mixed  $old_value The old option value.
	 * @param string $option    The option name.
	 * @return mixed $value on success.
	 */
	public static function verify_option_instance( $value, $old_value, $option ) {

		$type = self::get_property( '_type' );

		if ( empty( self::$_instance ) || ! $type ) {
			self::reset();
			\wp_die( '<code>' . \esc_html( $option ) . '</code> is a protected option.' );
			return $old_value;
		}

		$instance = self::$_instance;

		if ( isset( $instance[0], $instance[1][1] ) ) {
			\tsfem()->_verify_instance( $instance[0], $instance[1][1] );
		} else {
			static::verify_option_update( false );
			self::reset();
			\wp_die( 'Instance verification failed on option update.' );
			return '';
		}

		if ( \tsfem()->are_options_valid() ) {
			static::verify_option_update( true );
		} else {
			static::verify_option_update( false );
			self::reset();

			$value = $old_value;

			if ( \wp_doing_ajax() ) {
				$notice = \esc_html__(
					"Options have been altered outside of this plugin's scope. Please deactivate your account and try again.",
					'the-seo-framework-extension-manager'
				);

				\tsfem()->send_json(
					[
						'results' => \TSF_Extension_Manager\get_ajax_notice( false, $notice, -1 ),
					],
					'failure'
				);

				\wp_die();
				return;
			}
		}

		return $value;
	}

	/**
	 * Determines if the option has been verified.
	 *
	 * @since 1.0.0
	 * @since 2.6.1 Now can be set to unverify.
	 *
	 * @param ?bool $set Whether to verify the option update.
	 * @return bool True on success, false on failure.
	 */
	protected static function verify_option_update( $set = null ) {

		static $verified = false;

		if ( isset( $set ) )
			$verified = $set;

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
