<?php
/**
 * @package TSF_Extension_Manager\Classes\Abstract
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * @param string $type     Required. The instance type.
	 * @param string $instance Required. The instance key. Passed by reference.
	 * @param int    $bit      Required. The instance bit. Passed by reference.
	 */
	public static function initialize( $type = '', &$instance = null, &$bits = null );

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
abstract class Secure_Abstract implements Secure_Static_Abstracts {
	use Enclose_Core_Final, Construct_Core_Static_Final, Ignore_Properties_Core_Public_Final;

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
	 * @var array The validation request name.
	 * @var array The validation nonce action.
	 */
	protected static $nonce_name;
	protected static $request_name = [];
	protected static $nonce_action = [];

	/**
	 * The user's account information.
	 *
	 * @since 1.0.0
	 *
	 * @var array $account The account information.
	 */
	protected static $account = [];

	/**
	 * Resets current instance.
	 *
	 * @since 1.0.0
	 */
	final public static function reset() {
		self::reset_instance();
	}

	/**
	 * Resets current instance.
	 *
	 * @since 1.0.0
	 */
	final private static function reset_instance() {

		$class_vars = get_class_vars( __CLASS__ );

		foreach ( $class_vars as $property => $value ) :
			if ( isset( self::$$property ) )
				self::$$property = is_array( self::$$property ) ? [] : null;
		endforeach;
	}

	/**
	 * Sets class variables.
	 *
	 * @since 1.0.0
	 * @param string $type Required. The property you wish to set.
	 * @param mixed $value Required|Optional. The value the property needs to be set.
	 */
	final protected static function set( $type, $value = '' ) {

		switch ( $type ) :
			case '_wpaction' :
				self::$_wpaction = \current_action();
				break;

			case '_type' :
			case 'nonce_name' :
			case 'request_name' :
			case 'nonce_action' :
			case 'account' :
				self::$$type = $value;
				break;

			default:
				static::invoke_invalid_type( __METHOD__ );
				break;
		endswitch;

	}

	/**
	 * Sets parent class nonce variables.
	 *
	 * @since 1.0.0
	 * @param $type Required. The property you wish to set.
	 * @param $value Required|Optional. The value the property needs to be set.
	 */
	final public static function set_nonces( $type, $value ) {

		self::verify_instance() or die;

		switch ( $type ) :
			case 'nonce_name' :
			case 'request_name' :
			case 'nonce_action' :
				self::set( $type, $value );
				break;

			default:
				static::invoke_invalid_type( __METHOD__ );
				break;
		endswitch;

	}

	/**
	 * Sets parent class nonce variables.
	 *
	 * @since 1.0.0
	 * @param $account Required. The user's account.
	 */
	final public static function set_account( $account ) {

		self::verify_instance() or die;

		self::set( 'account', $account );

	}

	/**
	 * Determines whether the account level is premium.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the current account is premium.
	 */
	final protected static function is_premium_user() {

		static $is_premium = null;

		if ( isset( $is_premium ) )
			return $is_premium;

		$level = isset( self::$account['level'] ) ? self::$account['level'] : '';

		return $is_premium = 'Premium' === $level;
	}

	/**
	 * Gets class property.
	 *
	 * @since 1.0.0
	 * @param $name Required. The property to acquire.
	 * @param $value Required|Optional. The value the type needs to be set.
	 * @return mixed The property value.
	 */
	final protected static function get_property( $name ) {
		return self::$$name;
	}

	/**
	 * Kills PHP when an invalid type has been input.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method Required. The invoked Method where the error occured.
	 */
	final protected static function invoke_invalid_type( $method ) {

		//* wp_die() can be filtered. Remove filters JIT.
		\remove_all_filters( 'wp_die_ajax_handler' );
		\remove_all_filters( 'wp_die_xmlrpc_handler' );
		\remove_all_filters( 'wp_die_handler' );

		\wp_die( \esc_html( $method ) . '(): You must specify a correct initialization type.' );
		die;
	}

	/**
	 * Verifies current instance.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the instance has been verified.
	 */
	final protected static function verify_instance() {

		$verified = false;

		if ( \current_action() !== self::$_wpaction ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'The instance may not be left active between WordPress action hooks. Reset or initialize this instance first.' );
		} elseif ( empty( self::$_type ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must first use initialize class and set property <code>$_type</code>.' );
		} else {
			$verified = true;
		}

		return $verified;
	}
}
