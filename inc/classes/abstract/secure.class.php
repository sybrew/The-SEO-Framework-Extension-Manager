<?php
/**
 * @package TSF_Extension_Manager\Classes\Abstract
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
 * Declares static functions to always be used within the Secure class.
 *
 * @since 1.0.0
 * @access private
 */
interface Secure_Static_Abstracts {

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 * Must be converted to static as __construct() is forbidden.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Required. The instance type.
	 * @param string $instance Required. The instance key.
	 * @param int $bit Required. The instance bit.
	 */
	public static function initialize( $type = '', $instance = '', $bits = null );

	/**
	 * Returns the current call values based on initialization set in self::$_type.
	 * Must be converted to static as __construct() is forbidden.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @return string
	 */
	public static function get( $type = '' );
}

/**
 * This class allows handling of secure nonces and interfaces through a singleton pattern.
 *
 * @since 1.0.0
 * @access private
 *      You'll need to invoke the TSF_Extension_Manager\Core verification handler.
 *      Which is impossible.
 * @abstract
 *      Implements static functions from Secure_Static_Abstracts to be passed down
 *      to the extending classes.
 */
abstract class Secure implements Secure_Static_Abstracts {

	/**
	 * Cloning is forbidden.
	 */
	protected function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	protected function __wakeup() { }

	/**
	 * Constructing is forbidden.
	 */
	protected function __construct() { }

	/**
	 * Holds the class instance type.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_type
	 */
	private static $_type = '';

	/**
	 * Holds the current WordPress admin action.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_wpaction
	 */
	private static $_wpaction = '';

	/**
	 * The POST nonce validation name, action and name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 * @var string The validation request name.
	 * @var string The validation nonce action.
	 */
	protected static $nonce_name;
	protected static $request_name = array();
	protected static $nonce_action = array();

	/**
	 * Resets current instance.
	 *
	 * @since 1.0.0
	 */
	public static function reset() {
		self::reset_instance();
	}

	/**
	 * Resets current instance.
	 *
	 * @since 1.0.0
	 */
	private static function reset_instance() {
		self::$_type = self::$_wpaction = self::$nonce_name = self::$request_name = self::$nonce_action = '';
	}

	/**
	 * Sets class variables.
	 *
	 * @since 1.0.0
	 * @param $type Required. The property you wish to set.
	 * @param $value Required|Optional. The value the property needs to be set.
	 */
	protected static function set( $type, $value = '' ) {

		switch ( $type ) :
			case '_wpaction' :
				self::$_wpaction = current_action();
				break;

			case '_type' :
			case 'nonce_name' :
			case 'request_name' :
			case 'nonce_action' :
				self::$$type = $value;
				break;

			default:
				the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to specify a correct type.' );
				wp_die();
				break;
		endswitch;

	}

	/**
	 * Gets class property.
	 *
	 * @since 1.0.0
	 * @param $name Required. The property to acquire.
	 * @param $value Required|Optional. The value the type needs to be set.
	 */
	protected static function get_property( $name ) {
		return self::$$name;
	}

	/**
	 * Verifies current instance.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the instance has been verified.
	 */
	protected static function verify_instance() {

		$verified = false;

		if ( current_action() !== self::$_wpaction ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'The instance may not be left active between WordPress action hooks. Reset or initialize this instance first.' );
		} elseif ( empty( self::$_type ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must first use initialize and set <code>instance::$_type</code>.' );
		} else {
			$verified = true;
		}

		return $verified;
	}
}
