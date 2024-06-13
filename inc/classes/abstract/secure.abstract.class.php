<?php
/**
 * @package TSF_Extension_Manager\Classes\Abstract
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, Generic.Files.OneObjectStructurePerFile.MultipleFound, we require both definitions and overrides.

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
	 * @param array  $bits     Required. The instance bits. Passed by reference.
	 */
	public static function initialize( $type, &$instance = null, &$bits = null );

	/**
	 * Returns the current call values based on initialization set in self::$_type.
	 * Must be converted to static as __construct() is forbidden.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @return string
	 */
	public static function get( $type );
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
	use Construct_Core_Static_Final,
		Ignore_Properties_Core_Public_Final;

	/**
	 * @since 1.0.0
	 * @var string The class instance type.
	 */
	private static $_type = ''; // phpcs:ignore, PSR2.Classes.PropertyDeclaration.Underscore -- confusing otherwise.

	/**
	 * @since 1.0.0
	 * @var string The current WordPress admin action.
	 */
	private static $_wpaction = ''; // phpcs:ignore, PSR2.Classes.PropertyDeclaration.Underscore -- confusing otherwise.

	/**
	 * @since 1.0.0
	 * @var string The validation nonce name.
	 */
	protected static $nonce_name;

	/**
	 * @since 1.0.0
	 * @var array The validation request name.
	 */
	protected static $request_name = [];

	/**
	 * @since 1.0.0
	 * @var array The validation nonce action.
	 */
	protected static $nonce_action = [];

	/**
	 * @since 1.0.0
	 * @var array The current account information.
	 */
	protected static $account = [];

	/**
	 * @since 2.6.1
	 * @var array Various data.
	 */
	protected static $misc = [];

	/**
	 * @since 2.1.0
	 * @var string Holds the secret API key, bound to the parent's instance.
	 */
	protected static $secret_api_key;

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
	private static function reset_instance() {

		$class_vars = get_class_vars( __CLASS__ );

		foreach ( $class_vars as $property => $value )
			if ( isset( self::$$property ) )
				self::$$property = \is_array( self::$$property ) ? [] : null;
	}

	/**
	 * Sets class variables.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type  Required. The property you wish to set.
	 * @param mixed  $value Required|Optional. The value the property needs to be set.
	 */
	final protected static function set( $type, $value = '' ) {

		switch ( $type ) {
			case '_wpaction':
				self::$_wpaction = \current_action();
				break;

			case '_type':
			case 'nonce_name':
			case 'request_name':
			case 'nonce_action':
			case 'account':
			case 'secret_api_key':
			case 'misc':
				self::$$type = $value;
				break;

			default:
				static::invoke_invalid_type( __METHOD__ );
		}
	}

	/**
	 * Sets parent class nonce variables.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type  Required. The property you wish to set.
	 * @param mixed  $value Required|Optional. The value the property needs to be set.
	 */
	final public static function set_nonces( $type, $value ) {

		if ( ! self::verify_instance() ) return;

		switch ( $type ) {
			case 'nonce_name':
			case 'request_name':
			case 'nonce_action':
				self::set( $type, $value );
				break;

			default:
				static::invoke_invalid_type( __METHOD__ );
		}
	}

	/**
	 * Sets parent class account variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $account Required. The user's account.
	 */
	final public static function set_account( $account ) {

		if ( ! self::verify_instance() ) return;

		self::set( 'account', $account );
	}

	/**
	 * Sets parent class misc variables.
	 *
	 * @since 2.6.1
	 *
	 * @param array $misc Various data.
	 */
	final public static function set_misc( $misc ) {

		if ( ! self::verify_instance() ) return;

		self::set( 'misc', $misc );
	}

	/**
	 * Sets secret API key.
	 *
	 * @since 2.1.0
	 *
	 * @param string $api_key The secret API key.
	 */
	final public static function set_secret_api_key( $api_key ) {

		if ( ! self::verify_instance() ) return;

		self::set( 'secret_api_key', $api_key );
	}

	/**
	 * Determines whether the account level is premium.
	 *
	 * @since 1.0.0
	 * @since 2.6.2 Removed memoization.
	 *
	 * @return bool Whether the current account is premium.
	 */
	final protected static function is_premium_user() {

		switch ( self::$account['level'] ?? '' ) {
			case 'Enterprise':
			case 'Premium':
				return true;
		}

		return false;
	}

	/**
	 * Determines whether the account level is premium or essential.
	 *
	 * @since 2.0.0
	 * @since 2.6.2 Removed memoization.
	 *
	 * @return bool Whether the current account is API connected.
	 */
	final protected static function is_connected_user() {

		switch ( self::$account['level'] ?? '' ) {
			case 'Enterprise':
			case 'Premium':
			case 'Essentials':
				return true;
		}

		return false;
	}

	/**
	 * Gets class property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Required. The property to acquire.
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

		// wp_die() can be filtered. Remove filters JIT.
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
			\tsf()->_doing_it_wrong( __METHOD__, 'The instance may not be left active between WordPress action hooks. Reset or initialize this instance first.' );
		} elseif ( empty( self::$_type ) ) {
			\tsf()->_doing_it_wrong( __METHOD__, 'You must first use initialize class and set property <code>$_type</code>.' );
		} elseif ( ! \tsfem()->_has_died() ) {
			$verified = true;
		}

		return $verified;
	}
}
