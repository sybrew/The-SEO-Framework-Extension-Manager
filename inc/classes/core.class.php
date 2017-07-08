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
 * Require option trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'options' );

/**
 * Require error trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'error' );

/**
 * Class TSF_Extension_Manager\Core
 *
 * Holds plugin core functions.
 *
 * @since 1.0.0
 * @access private
 */
class Core {
	use Enclose_Stray_Private, Construct_Core_Interface, Destruct_Core_Public_Final, Options, Error;

	/**
	 * The POST nonce validation name, action and name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 * @var string The validation request name.
	 * @var string The validation nonce action.
	 */
	protected $nonce_name;
	protected $request_name = [];
	protected $nonce_action = [];

	/**
	 * Returns an array of active extensions real path.
	 *
	 * @since 1.0.0
	 *
	 * @var array List of active extensions real path.
	 */
	protected $active_extensions = [];

	/**
	 * Constructor, initializes actions and sets up variables.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . ( \is_admin() ? '\\LoadAdmin' : '\\LoadFront' );
		$this instanceof $that or \wp_die( -1 );

		$this->nonce_name = 'tsf_extension_manager_nonce_name';
		$this->request_name = [
			//* Reference convenience.
			'default'           => 'default',

			//* Account activation and more.
			'activate-key'      => 'activate-key',
			'activate-external' => 'activate-external',
			'activate-free'     => 'activate-free',
			'deactivate'        => 'deactivate',
			'enable-feed'       => 'enable-feed',

			//* Extensions.
			'activate-ext'      => 'activate-ext',
			'deactivate-ext'    => 'deactivate-ext',
		];
		$this->nonce_action = [
			//* Reference convenience.
			'default'           => 'tsfem_nonce_action',

			//* Account activation and more.
			'activate-free'     => 'tsfem_nonce_action_free_account',
			'activate-key'      => 'tsfem_nonce_action_key_account',
			'activate-external' => 'tsfem_nonce_action_external_account',
			'deactivate'        => 'tsfem_nonce_action_deactivate_account',
			'enable-feed'       => 'tsfem_nonce_action_feed',

			//* Extensions.
			'activate-ext'      => 'tsfem_nonce_action_activate_ext',
			'deactivate-ext'    => 'tsfem_nonce_action_deactivate_ext',
		];
		/**
		 * Set error notice option.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_error_notice_option';

	}

	/**
	 * Determines if the PHP handler can handle 64 bit integers.
	 *
	 * @since 1.2.0
	 * @since 1.3.0 Now is public.
	 *
	 * @return bool True if handler supports 64 bits, false otherwise (63 or lower).
	 */
	final public function is_64() {
		return is_int( 9223372036854775807 );
	}

	/**
	 * Handles extensions. On both the front end and back-end.
	 *
	 * @since 1.0.0
	 * @staticvar bool $loaded True if extensions are loaded, false otherwise.
	 * @access private
	 *
	 * @return true If loaded, false otherwise.
	 */
	final public function _init_extensions() {

		static $loaded = null;

		if ( isset( $loaded ) )
			return $loaded;

		if ( \wp_installing() || false === $this->is_plugin_activated() )
			return $loaded = false;

		if ( false === $this->are_options_valid() ) {
			//* Failed options instance checksum.
			$this->set_error_notice( [ 2001 => '' ] );
			return $loaded = false;
		}

		$this->get_verification_codes( $_instance, $bits );

		//* Some AJAX functions require Extension layout traits to be loaded.
		if ( \is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) )
				$this->ajax_is_tsf_extension_manager_page( true );
		}

		\TSF_Extension_Manager\Extensions::initialize( 'list', $_instance, $bits );
		\TSF_Extension_Manager\Extensions::set_account( $this->get_subscription_status() );

		$checksum = \TSF_Extension_Manager\Extensions::get( 'extensions_checksum' );
		$result = $this->validate_extensions_checksum( $checksum );

		if ( true !== $result ) :
			switch ( $result ) :
				case -1 :
					//* No extensions have ever been active...
					;

				case -2 :
					//* Failed checksum.
					$this->set_error_notice( [ 2002 => '' ] );
					;

				default :
					\TSF_Extension_Manager\Extensions::reset();
					return $loaded = false;
					break;
			endswitch;
		endif;

		$extensions = \TSF_Extension_Manager\Extensions::get( 'active_extensions_list' );

		\TSF_Extension_Manager\Extensions::reset();

		if ( empty( $extensions ) )
			return $loaded = false;

		$this->get_verification_codes( $_instance, $bits );

		\TSF_Extension_Manager\Extensions::initialize( 'load', $_instance, $bits );

		foreach ( $extensions as $slug => $active ) {
			$this->get_verification_codes( $_instance, $bits );

			\TSF_Extension_Manager\Extensions::load_extension( $slug, $_instance, $bits );
		}

		\TSF_Extension_Manager\Extensions::reset();

		return $loaded = true;
	}

	/**
	 * Verifies integrity of the options.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool True if options are valid, false if not.
	 */
	final protected function are_options_valid() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = $this->verify_options_hash( serialize( $this->get_all_options() ) );
	}

	/**
	 * Destroys output buffer and headers, if any.
	 *
	 * To be used with AJAX to clear any PHP errors or dumps.
	 * This works best when php.ini directive "output_buffering" is set to "1".
	 *
	 * @since 1.0.0
	 * @since 1.2.0 : 0. Renamed from _clean_ajax_response_header().
	 *                1. Now clears all levels, rather than only one.
	 *                2. Now removes all headers previously set.
	 *                3. Now returns a numeric value. From 0 to 3.
	 * @access private
	 *
	 * @return bitwise integer : {
	 *    0 = 0000 : Did nothing.
	 *    1 = 0001 : Cleared PHP output buffer.
	 *    2 = 0010 : Cleared HTTP headers.
	 *    3 = 0011 : Did 1 and 2.
	 * }
	 */
	final public function _clean_reponse_header() {

		$retval = 0;
		// PHP 5.6+ //= $i = 0;

		if ( $level = ob_get_level() ) {
			while ( $level-- ) {
				ob_end_clean();
			}
			$retval = $retval | 1; //= 2 ** $i
		}

		// PHP 5.6+ //= $i++;

		//* wp_ajax sets required headers early.
		if ( ! headers_sent() ) {
			header_remove();
			$retval = $retval | 2; //= 2 ** $i
		}

		return $retval;
	}

	/**
	 * Sets status header.
	 *
	 * @since 1.2.0
	 * @uses status_header(): https://developer.wordpress.org/reference/functions/status_header/
	 *
	 * @param string $type The header type.
	 * @param bool $code The status code.
	 */
	final public function set_status_header( $code = 200, $type = '' ) {

		switch ( $type ) :
			case 'json' :
				header( 'Content-Type: application/json; charset=' . \get_option( 'blog_charset' ) );
				break;

			case 'html' :
			default :
				header( 'Content-Type: text/html; charset=' . \get_option( 'blog_charset' ) );
				break;
		endswitch;

		if ( $code )
			\status_header( $code );
	}

	/**
	 * Sends out JSON data for AJAX.
	 *
	 * Sends JSON object as integer. When it's -1, it's uncertain if the response
	 * is actually JSON encoded. When it's 1, we can safely assume it's JSON.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $data The data that needs to be send.
	 * @param string $type The status type.
	 */
	final public function send_json( $data, $type = 'success' ) {

		$r = $this->_clean_reponse_header();
		$json = -1;

		if ( $r & 2 ) {
			$this->set_status_header( 200, 'json' );
			$json = 1;
		} else {
			$this->set_status_header( null, 'json' );
		}

		echo \wp_json_encode( compact( 'data', 'type', 'json' ) );

		die;
	}

	/**
	 * Sends out HTML data for AJAX.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $html The HTML that needs to be send. Must be escaped.
	 * @param string $type The status type.
	 */
	final public function send_html( $html, $type = 'success' ) {

		$r = $this->_clean_reponse_header();

		if ( $r & 2 ) {
			$this->set_status_header( 200, 'html' );
		} else {
			$this->set_status_header( null, 'html' );
		}

		//* Must be escaped prior to call.
		echo $html;

		die;
	}

	/**
	 * Generates AJAX POST object for looping AJAX callbacks.
	 *
	 * Example usage includes downloading files over AJAX, which is otherwise not
	 * possible.
	 *
	 * Includes enforced nonce security. However, the user capability allowance
	 * MUST be determined beforehand.
	 * Note that the URL can't be generated if the menu pages aren't set.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @param array $args - Required : {
	 *    'options_key'   => string The extension options key,
	 *    'options_index' => string The extension options index,
	 *    'menu_slug'     => string The extension options menu slug,
	 *    'nonce_name'    => string The extension POST actions nonce name,
	 *    'request_name'  => string The extension desired POST action request index key name,
	 *    'nonce_action'  => string The extesnion desired POST action request full name,
	 * }
	 * @return array|bool False on failure; array containing the jQuery.post object.
	 */
	final public function _get_ajax_post_object( array $args ) {

		$required = [
			'options_key' => '',
			'options_index' => '',
			'menu_slug' => '',
			'nonce_name' => '',
			'request_name' => '',
			'nonce_action' => '',
		];

		//* If the required keys aren't found, bail.
		if ( ! $this->has_required_array_keys( $args, $required ) )
			return false;

		$url = $this->get_admin_page_url( $args['menu_slug'] );

		if ( ! $url )
			return false;

		$args['options_key'] = \sanitize_key( $args['options_key'] );
		$args['options_index'] = \sanitize_key( $args['options_index'] );
		$args['nonce_name'] = \sanitize_key( $args['nonce_name'] );

		$post = [
			'url' => $url,
			'method' => 'post',
			'data' => [
				$args['options_key'] => [
					$args['options_index'] => [
						'nonce-action' => $args['request_name'],
					],
				],
				$args['nonce_name'] => \wp_create_nonce( $args['nonce_action'] ),
				'_wp_http_referer' => \esc_attr( \wp_unslash( $_SERVER['REQUEST_URI'] ) ),
			],
		];

		return \map_deep( $post, '\\esc_js' );
	}

	/**
	 * Converts multidimensional arrays to single array with key wrappers.
	 * All first array keys become the new key. The final value becomes its value.
	 *
	 * Great for creating form array keys.
	 * matosa: "Multidimensional Array TO Single Array"
	 *
	 * The latest value must be scalar.
	 *
	 * Example: [ 1 => [ 2 => [ 3 => [ 'value' ] ] ] ];
	 * Becomes: '1[2][3]' => 'value';
	 *
	 * @since 1.2.0
	 * @staticvar string $last The last value;
	 *
	 * @param string|array $value The array or string to loop. First call must be array.
	 * @param string $start The start wrapper.
	 * @param string $end The end wrapper.
	 * @param int $i The iteration count. This shouldn't be filled in.
	 * @param bool $get Whether to return the value. This shouldn't be filled in.
	 * @return array|false The iterated array to string. False if input isn't array.
	 */
	final public function matosa( $value, $start = '[', $end = ']', $i = 0, $get = true ) {

		$output = '';
		$i++;

		static $last = null;

		if ( is_array( $value ) ) {

			$index = key( $value );
			$last = $item = $value[ $index ];

			if ( is_array( $item ) ) {
				if ( 1 === $i ) {
					$output .= $index . $this->matosa( $item, $start, $end, $i, false );
				} else {
					$output .= $start . $index . $end . $this->matosa( $item, $start, $end, $i, false );
				}
			}
		} elseif ( 1 === $i ) {
			//* Input is scalar or object.
			$last = null;
			return false;
		}

		if ( $get ) {
			return [ $output => $last ];
		} else {
			return $output;
		}
	}

	/**
	 * Converts single dimensional strings from matosa to a multidimensional array.
	 *
	 * Great for parsing form array keys.
	 * satoma: "Single Array to Multidimensional Array"
	 *
	 *
	 * Example: '1[2][3]';
	 * Becomes: [ 1 => [ 2 => [ 3 ] ];
	 *
	 * @since 1.3.0
	 * @todo improve readability. It's very hackesque.
	 * @see parse_str() You might wish to use that instead.
	 *
	 * @param string|array $value The array or string to loop. First call must be array.
	 * @param string $start The start wrapper.
	 * @param string $end The end wrapper.
	 * @return array The iterated string to array.
	 */
	final public function satoma( $value, $start = '[', $end = ']' ) {

		$regex = sprintf( '/[\%s\%s]+/', $start, $end );

		$items = preg_split( $regex, $value, -1, PREG_SPLIT_NO_EMPTY );
		$count = count( $items );

		$ret = [];
		$ret[ $items[ $count - 2 ] ] = end( $items );
		for ( $i = $count - 3; $i > -1; $i-- ) {
			$ret[ $items[ $i ] ] = $ret;
			unset( $ret[ $items[ $i + 1 ] ] );
		}

		return $ret;
	}

	/**
	 * Returns last value.
	 *
	 * I should get a nobel prize for this.
	 *
	 * @since 1.3.0
	 * @see $this->satoma() Which creates a need for this.
	 *
	 * @param array $a
	 * @return string The last array value.
	 */
	final public function get_last_value( array $a ) {

		while ( is_array( $a = end( $a ) ) );

		return $a;
	}

	/**
	 * Determines if all required keys are set in $input.
	 *
	 * @since 1.2.0
	 *
	 * @param array $input The input keys.
	 * @param array $compare The keys to compare it to.
	 * @return bool True on success, false if keys are missing.
	 */
	final public function has_required_array_keys( array $input, array $compare ) {
		return empty( array_diff_key( $compare, $input ) );
	}

	/**
	 * Checks whether the variable is set and passes it back.
	 * If the value isn't set, it will set it to the fallback variable.
	 *
	 * It will also return the value so it can be used in a return statement.
	 *
	 * PHP < 7 wrapper for null coalescing.
	 * @link http://php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op
	 * @since 1.2.0
	 *
	 * @param mixed $v The variable that's maybe set. Passed by reference.
	 * @param mixed $f The fallback variable. Default null.
	 * @return mixed
	 */
	final public function coalesce_var( &$v = null, $f = null ) {
		return isset( $v ) ? $v : $v = $f;
	}

	/**
	 * Performs wp_die on TSF Extension Manager Page.
	 * Destructs class otherwise.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $message The error message.
	 * @return bool false If no wp_die has been performed.
	 */
	final public function _maybe_die( $message = '' ) {

		if ( $this->is_tsf_extension_manager_page( false ) ) {
			//* wp_die() can be filtered. Remove filters JIT.
			\remove_all_filters( 'wp_die_ajax_handler' );
			\remove_all_filters( 'wp_die_xmlrpc_handler' );
			\remove_all_filters( 'wp_die_handler' );

			\wp_die( \esc_html( $message ) );
	 	}

		//* Don't spam error log.
		if ( false === $this->_has_died() ) {

			$this->_has_died( true );

			if ( $message ) {
				\the_seo_framework()->_doing_it_wrong( __CLASS__, 'Class execution stopped with message: <strong>' . \esc_html( $message ) . '</strong>' );
			} else {
				\the_seo_framework()->_doing_it_wrong( __CLASS__, 'Class execution stopped because of an error.' );
			}
		}

		$this->stop_class();
		$this->_has_died( true );

		return false;
	}

	/**
	 * Stops class from executing. A true destructor.
	 * Removes all instance properties, and removes instance from global $wp_filter.
	 *
	 * @since 1.0.0
	 *
	 * @return bool true
	 */
	final protected function stop_class() {

		$class_vars = get_class_vars( __CLASS__ );
		$other_vars = get_class_vars( get_called_class() );

		$properties = array_merge( $class_vars, $other_vars );

		foreach ( $properties as $property => $value ) :
			if ( isset( $this->$property ) )
				$this->$property = is_array( $this->$property ) ? [] : null;
		endforeach;

		array_walk( $GLOBALS['wp_filter'], [ $this, 'stop_class_filters' ] );
		$this->__destruct();

		return true;
	}

	/**
	 * Forces wp_filter removal. It's quite heavy, used in "oh dear God" circumstances.
	 *
	 * Searches current filter, and if the namespace of this namespace is found,
	 * it will destroy it from globals $wp_filter.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Now uses instanceof comparison
	 *
	 * @param array $current_filter The filter to walk.
	 * @param string $key The current array key.
	 * @return bool true
	 */
	final protected function stop_class_filters( $current_filter, $key ) {

		$_key = key( $current_filter );
		$filter = isset( $current_filter[ $_key ] ) and reset( $current_filter[ $_key ] );

		static $_this = null;

		if ( null === $_this )
			$_this = get_class( $this );

		if ( isset( $filter['function'] ) ) {
			if ( is_array( $filter['function'] ) ) :
				foreach ( $filter['function'] as $k => $function ) :
					if ( is_object( $function ) && $function instanceof $_this )
						unset( $GLOBALS['wp_filter'][ $key ][ $_key ] );
				endforeach;
			endif;
		}

		return true;
	}

	/**
	 * Verifies views instances. Clears the input through reference.
	 *
	 * Is seems vulnerable to timing attacks, but that's mitigated further for
	 * improved performance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $instance The verification instance key. Passed by reference.
	 * @param int $bit The verification instance bit. Passed by reference.
	 * @return bool True if verified.
	 */
	final public function _verify_instance( &$instance, &$bit ) {
		return (bool) ( $instance === $this->get_verification_instance( $bit ) | $instance = $bit = null );
	}

	/**
	 * Loops through instance verification in order to fetch multiple instance keys.
	 *
	 * Must be used within a foreach loop. Instance must be verified within each loop iteration.
	 * Must be able to validate usage first with the 2nd and 3rd parameter.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $count The amount of instances to loop for.
	 * @param string $instance The verification instance key. Passed by reference.
	 * @param array $bits The verification instance bits. Passed by reference.
	 * @yield array Generator : {
	 *		$instance string The verification instance key
	 *		$bits array The verification instance bits
	 * }
	 */
	final public function _yield_verification_instance( $count, &$instance, &$bits ) {

		if ( $this->_verify_instance( $instance, $bits[1] ) ) :
			for ( $i = 0; $i < $count; $i++ ) :
				yield [
					'bits'     => $_bits = $this->get_bits(),
					'instance' => $this->get_verification_instance( $_bits[1] ),
				];
			endfor;
		endif;
	}

	/**
	 * Returns the verification instance codes by reference.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance The verification instance. Passed by reference.
	 * @param array $bits The verification bits. Passed by reference.
	 */
	final protected function get_verification_codes( &$instance = null, &$bits = null ) {
		$bits = $this->get_bits();
		$instance = $this->get_verification_instance( $bits[1] );
	}

	/**
	 * Generates view instance through bittype and hash comparison.
	 * It's a two-factor verification.
	 *
	 * Performs wp_die() on TSF Extension Manager's admin page. Otherwise it
	 * will silently fail and destruct class.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added small prime number to prevent time freeze cracking.
	 * @staticvar string $instance
	 * @staticvar int $timer
	 *
	 * @param int|null $bit The instance bit.
	 * @return string $instance The instance key.
	 */
	final protected function get_verification_instance( $bit = null ) {

		static $instance = [];

		$bits = $this->get_bits();
		$_bit = $bits[0];

		//* Timing-attack safe.
		if ( isset( $instance[ ~ $_bit ] ) ) {
			//= Timing attack mitigated.

			//* Don't use hash_equals(). This is already safe.
			if ( empty( $instance[ $bit ] ) || $instance[ ~ $_bit ] !== $instance[ $bit ] ) {
				//* Only die on plugin settings page upon failure. Otherwise kill instance and all bindings.
				$this->_maybe_die( 'Error -1: The SEO Framework Extension Manager instance verification failed.' ) xor $instance = [];
				return '';
			}

			//* Set retval and empty to prevent recursive timing attacks.
			$_retval = $instance[ $bit ] and $instance = [];

			return $_retval;
		}

		static $timer = null;

		//* It's over ninethousand! And also a prime.
		$_prime = 9001;

		if ( null === $timer ) {
			$timer = $this->is_64() ? time() * $_prime : PHP_INT_MAX / $_prime;
		} else {
			$timer += $_prime;
		}

		//* This creates a unique salt for each bit.
		$hash = $this->hash( $_bit . '\\' . mt_rand( ~ $timer, $timer ) . '\\' . $bit, 'instance' );

		return $instance[ $bit ] = $instance[ ~ $_bit ] = $hash;
	}

	/**
	 * Generates verification bits based on time.
	 *
	 * The bit generation is 4 dimensional and calculates a random starting integer.
	 * This makes it reverse-enginering secure, it's also time-attack secure.
	 * It other words: Previous bits can't be re-used as the match will be
	 * subequal in the upcoming check.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added small prime number to prevent time freeze cracking.
	 * @link http://theprime.site/
	 * @staticvar int $_bit : $bits[0]
	 * @staticvar int $bit  : $bits[1]
	 *
	 * @return array The verification bits.
	 */
	final protected function get_bits() {

		static $_bit, $bit;

		if ( isset( $bit ) )
			goto generate;

		/**
		 * Create new bits on first run.
		 * Prevents random abstract collision by filtering odd numbers.
		 *
		 * Uses various primes to prevent overflow (which is heavy and can loop) on x86 architecture.
		 * Time can never be 0, because it will then loop.
		 */
		set : {
			$bit = $_bit = 0;

			$this->coalesce_var( $_prime, 317539 );
			$_boundary = 10000;

			$_time = time();

			$_i = $this->is_64() && $_time > $_boundary ? $_time : ( PHP_INT_MAX - $_boundary ) / $_prime;
			$_i > 0 or $_i = ~$_i;
			$_i = (int) $_i;

			    $_i = $_i * $_prime
			and is_int( $_i )
			and ( $_i + $_boundary ) < PHP_INT_MAX
			and $bit = $_bit = mt_rand( ~ $_i, $_i )
			and $bit & 1
			and $bit = $_bit++;
		}

		//* Hit 0 or is overflown on x86. Retry.
		if ( 0 === $bit || is_double( $bit ) ) {
			$_prime = array_rand( array_flip( [ 317539, 58171, 16417, 6997, 379, 109, 17 ] ) );
			goto set;
		}

		generate : {
			/**
			 * Count to create an irregular bit verification.
			 * This can jump multiple sequences while maintaining the previous.
			 * It traverses in three (actually two, but get_verification_instance makes it
			 * three) dimensions: up (positive), down (negative) and right (new sequence).
			 *
			 * Because it either goes up or down based on integer, it's timing attack secure.
			 */
			    $bit  = $_bit <= 0 ? ~$bit-- | ~$_bit-- : ~$bit-- | ~$_bit++
			and $bit  = $bit++ & $_bit--
			and $bit  = $bit < 0 ? $bit++ : $bit--
			and $_bit = $_bit < 0 ? $_bit : ~$_bit
			and $bit  = ~$_bit++
			and $_bit = $_bit < 0 ? $_bit : ~$_bit
			and $bit++;
		}

		return [ $_bit, $bit ];
	}

	/**
	 * Hashes input $data with the best hash type available while also using hmac.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data The data to hash.
	 * @param string $scheme Authentication scheme ( 'instance', 'auth', 'secure_auth', 'nonce' ).
	 *                       Default 'instance'.
	 * @return string Hash of $data.
	 */
	final protected function hash( $data, $scheme = 'instance' ) {

		$salt = $this->get_salt( $scheme );

		return hash_hmac( $this->get_hash_type(), $data, $salt );
	}

	/**
	 * Generates static hash based on $uid.
	 *
	 * Caution: This function does not generate cryptographically secure values.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @param string $uid The unique ID for the hash.
	 *                    A good choice would be the page ID + concatentated blog name.
	 * @return string The timed hash that will always return the same.
	 */
	final public function _get_uid_hash( $uid ) {

		if ( empty( $uid ) )
			return '';

		$a = (string) $uid;
		$b = strrev( $a );
		$len = strlen( $a );
		$r = '';

		for ( $i = 0; $i < $len; $i++ ) {
			$r .= ord( $a[ $i ] ) . $b[ $i ];
		}

		return $this->hash( $r, 'auth' );
	}

	/**
	 * Generates timed hash based on $uid.
	 *
	 * Caution: It is timing attack secure. However because of $length, the value
	 * can be easily reproduced in $length seconds if caller is known; therefore
	 * rendering this method insecure for cryptographical purposes.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @param string $uid   The unique ID for the hash. A good choice would be the method name.
	 * @param int    $scale The time scale in seconds.
	 * @param int    $end   UNIX timestamp where the hash invalidates. Defaults to now.
	 * @return string The timed hash that will always return the same.
	 */
	final public function _get_timed_hash( $uid, $length = 3600, $end = 0 ) {

		if ( empty( $uid ) || empty( $length ) )
			return '';

		$_time = time();
		$_end = $end ?: $_time;
		$_delta = $_end > $_time ? $_end - $_time : $_time - $_end;

		$now_x = floor( ( $_time - $_delta ) / $length );

		$string = $uid . '\\' . $now_x . '\\' . $uid;

		return $this->hash( $string, 'auth' );
	}

	/**
	 * Generates salt from WordPress defined constants.
	 *
	 * Taken from WordPress core function `wp_salt()` and adjusted accordingly.
	 * @link https://developer.wordpress.org/reference/functions/wp_salt/
	 *
	 * @since 1.0.0
	 * @staticvar array $cached_salts Contains cached salts based on $scheme input.
	 * @staticvar string $instance_scheme Random scheme for instance verification. Determined at runtime.
	 *
	 * @param string $scheme Authentication scheme. ( 'instance', 'auth', 'secure_auth', 'nonce' ).
	 *                       Default 'instance'.
	 * @return string Salt value.
	 */
	final protected function get_salt( $scheme = 'instance' ) {

		static $cached_salts = [];

		if ( isset( $cached_salts[ $scheme ] ) )
			return $cached_salts[ $scheme ];

		$values = [
			'key'  => '',
			'salt' => '',
		];

		$schemes = [ 'auth', 'secure_auth', 'logged_in', 'nonce' ];

		//* 'instance' picks a random key.
		static $instance_scheme = null;
		if ( null === $instance_scheme ) {
			$_key = mt_rand( 0, count( $schemes ) - 1 );
			$instance_scheme = $schemes[ $_key ];
		}
		$scheme = 'instance' === $scheme ? $instance_scheme : $scheme;

		if ( in_array( $scheme, $schemes, true ) ) {
			foreach ( [ 'key', 'salt' ] as $type ) :
				$const = strtoupper( "{$scheme}_{$type}" );
				if ( defined( $const ) && constant( $const ) ) {
					$values[ $type ] = constant( $const );
				} elseif ( empty( $values[ $type ] ) ) {
					$values[ $type ] = \get_site_option( "{$scheme}_{$type}" );
					if ( ! $values[ $type ] ) {
						/**
						 * Hash keys not defined in wp-config.php nor in database.
						 * Let wp_salt() handle this. This should run at most once per site per scheme.
						 */
						$values[ $type ] = \wp_salt( $scheme );
					}
				}
			endforeach;
		} else {
			\wp_die( 'Invalid scheme supplied for <code>' . __METHOD__ . '</code>.' );
		}

		$cached_salts[ $scheme ] = $values['key'] . $values['salt'];

		return $cached_salts[ $scheme ];
	}

	/**
	 * Returns working hash type.
	 *
	 * @since 1.0.0
	 * @staticvar string $type
	 *
	 * @return string The working hash type to be used within hash() functions.
	 */
	final public function get_hash_type() {

		static $type = null;

		if ( isset( $type ) )
			return $type;

		$algos = hash_algos();

		if ( in_array( 'sha256', $algos, true ) ) {
			$type = 'sha256';
		} elseif ( in_array( 'sha1', $algos, true ) ) {
			$type = 'sha1';
		} else {
			$type = 'md5';
		}

		return $type;
	}

	/**
	 * Returns the minimum role required to adjust and access settings.
	 *
	 * @since 1.0.0
	 *
	 * @return string The minimum required capability for extensions Settings.
	 */
	final public function can_do_settings() {
		return \TSF_Extension_Manager\can_do_settings();
	}

	/**
	 * Determines whether the plugin is network activated.
	 *
	 * @since 1.0.0
	 * @staticvar bool $network_mode
	 *
	 * @return bool Whether the plugin is active in network mode.
	 */
	final public function is_plugin_in_network_mode() {
		//* TODO remove this! It now renders network mode as singular installations per site. This is NOT what I promised.
		return false;

		static $network_mode = null;

		if ( isset( $network_mode ) )
			return $network_mode;

		if ( ! \is_multisite() )
			return $network_mode = false;

		$plugins = \get_site_option( 'active_sitewide_plugins' );

		return $network_mode = isset( $plugins[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
	}

	/**
	 * Grants a class access to the verification instance and bits of this object.
	 * by returning the $_instance and $bits parameters.
	 * Once.
	 *
	 * @since 1.0.0
	 * @NOTE Expensive operation.
	 * @see $this->_yield_verification_instance() for faster looping instances.
	 * @access private
	 *
	 * @param object $object The class object. Passed by reference.
	 * @param string $_instance The verification instance. Passed by reference.
	 * @param array $bits The verification bits. Passed by reference.
	 * @return bool True on success, false on failure.
	 */
	final public function _request_premium_extension_verification_instance( &$object, &$_instance, &$bits ) {

		if ( false === $this->is_premium_user() || false === $this->are_options_valid() )
			goto failure;

		$allowed_classes = [
			'TSF_Extension_Manager\\Extension\\Monitor\\Admin',
		];

		if ( in_array( get_class( $object ), $allowed_classes, true ) ) {
			$this->get_verification_codes( $_instance, $bits );
			return true;
		}

		failure:;

		$this->_verify_instance( $_instance, $bits );
		return false;
	}

	/**
	 * Initializes class autoloader and verifies integrity.
	 *
	 * @since 1.3.0
	 *
	 * @param string $path      The extension path to look for.
	 * @param string $namespace The namespace.
	 * @param string $_instance The verification instance.
	 * @param array  $bits      The verification instance bits.
	 * @return bool False on failure, true on success.
	 */
	final public function _init_early_extension_autoloader( $path, $namespace, &$_instance = null, &$bits = null ) {

		if ( $this->_has_died() )
			return false;

		if ( false === ( $this->_verify_instance( $_instance, $bits[1] ) or $this->_maybe_die() ) )
			return false;

		$this->_register_premium_extension_autoload_path( $path, $namespace );
		return true;
	}

	/**
	 * Registers autoloading classes for extensions and activates autoloader.
	 * If the account isn't premium, it will not be loaded.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 : 1. Now handles namespaces instead of class bases.
	 *                2. Removed some checks as it's not protected.
	 *                2. Now is protected.
	 * @access private
	 *
	 * @param string $path      The extension path to look for.
	 * @param string $namespace The namespace.
	 * @return bool True on success, false on failure.
	 */
	final protected function _register_premium_extension_autoload_path( $path, $namespace ) {

		if ( false === $this->is_premium_user() || false === $this->are_options_valid() )
			return false;

		$this->register_extension_autoloader();

		return $this->set_extension_autoload_path( $path, $namespace );
	}

	/**
	 * Registers and activated autoloader for extensions.
	 *
	 * @since 1.2.0
	 * @staticvar bool $autoload_inactive Whether the autoloader is active.
	 */
	final protected function register_extension_autoloader() {

		static $autoload_inactive = true;

		if ( $autoload_inactive ) {
			spl_autoload_register( [ $this, 'autoload_extension_class' ], true, true );
			$autoload_inactive = false;
		}
	}

	/**
	 * Registers autoloading classes for extensions.
	 * Maintains a cache. So this can be fetched later.
	 *
	 * @since 1.2.0
	 * @since 1.3.0 : Now handles namespaces instead of class bases.
	 * @staticvar array $registered The registered classes.
	 *
	 * @param string|null $path      The extension path to look for.
	 * @param string|null $class     The $class name including namespace.
	 * @param string|null $get       The namespace path to get from cache.
	 * @return void|bool|array : {
	 *    false  : The extension namespace wasn't set.
	 *    true   : The extension namespace is set.
	 *    void   : The extension namespace isn't set.
	 *    string : The extension namespace location.
	 * }
	 */
	final protected function set_extension_autoload_path( $path, $namespace, $get = null ) {

		static $locations = [];

		if ( $get ) {
			if ( isset( $locations[ $get ] ) )
				return $locations[ $get ];

			return false;
		} else {
			if ( $namespace ) {
				$locations[ $namespace ] = $path;
				return true;
			}
		}

		return;
	}

	/**
	 * Returns the registered $namespace base path.
	 *
	 * @since 1.2.0
	 *
	 * @param string $namespace The namespace path to fetch.
	 * @return string|bool The path if found. False otherwise.
	 */
	final protected function get_extension_autload_path( $namespace ) {
		return $this->set_extension_autoload_path( null, null, $namespace );
	}

	/**
	 * Autoloads all class files. To be used when requiring access to all or any of
	 * the plugin classes.
	 *
	 * @since 1.2.0
	 * @since 1.3.0 : Now handles namespaces instead of class bases.
	 * @staticvar array $loaded Whether $class has been loaded.
	 *
	 * @param string $class The extension classname.
	 * @return bool False if file hasn't yet been included, otherwise true.
	 */
	final protected function autoload_extension_class( $class ) {

		$class = ltrim( $class, '\\' );

		if ( 0 !== strpos( $class, 'TSF_Extension_Manager\\Extension\\', 0 ) )
			return;

		static $loaded = [];

		if ( isset( $loaded[ $class ] ) )
			return $loaded[ $class ];

		$_class = str_replace( 'TSF_Extension_Manager\\Extension\\', '', $class );
		$_ns = substr( $_class, 0, strpos( $_class, '\\' ) );

		$_path = $this->get_extension_autload_path( $_ns );

		if ( $_path ) {
			$_file = strtolower( str_replace( '_', '-', str_replace( $_ns . '\\', '', $_class ) ) );

			$this->get_verification_codes( $_instance, $bits );

			return $loaded[ $class ] = require_once( $_path . $_file . '.class.php' );
		} else {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'Class <code>' . \esc_html( $class ) . '</code> has not been registered.' );

			//* Most likely, a fatal error will now occur.
			return $loaded[ $class ] = false;
		}
	}

	/**
	 * Validates extensions option checksum.
	 *
	 * @since 1.0.0
	 * @uses PHP 5.6 hash_equals : WordPress core has compat.
	 *
	 * @param array $checksum The extensions checksum.
	 * @return int|bool, Negative int on failure, true on success.
	 */
	final protected function validate_extensions_checksum( $checksum ) {

		$required = [
			'hash' => '',
			'matches' => '',
			'type' => '',
		];

		//* If the required keys aren't found, bail.
		if ( ! $this->has_required_array_keys( $checksum, $required ) ) {
			return -1;
		} elseif ( ! hash_equals( $checksum['matches'][ $checksum['type'] ], $checksum['hash'] ) ) {
			return -2;
		}

		return true;
	}

	/**
	 * Converts pixels to points.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $px The pixels amount. Accepts 42 as well as '42px'.
	 * @return int Points.
	 */
	final public function pixels_to_points( $px = 0 ) {
		return intval( $px ) * 0.75;
	}

	/**
	 * Determines whether the plugin's activated. Either free or premium.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the plugin is activated.
	 */
	final protected function is_plugin_activated() {
		return 'Activated' === $this->get_option( '_activated' );
	}

	/**
	 * Determines whether the plugin's use is premium.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool True if the plugin is connected to the API handler.
	 */
	final protected function is_premium_user() {
		return 'Premium' === $this->get_option( '_activation_level' );
	}

	/**
	 * Returns subscription status from local options.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 The parameters are now passed by reference.
	 * @access private
	 *
	 * @param string $_instance The verification instance key. Passed by reference.
	 * @param int $bit The verification instance bit. Passed by reference.
	 * @return array|boolean Current subscription status. False on failed instance verification.
	 */
	final public function _get_subscription_status( &$_instance, &$bits ) {

		if ( $this->_verify_instance( $_instance, $bits[1] ) ) {
			return $this->get_subscription_status();
		}

		return false;
	}

	/**
	 * Returns subscription status from local options.
	 *
	 * @since 1.0.0
	 * @staticvar array $status
	 *
	 * @return array Current subscription status.
	 */
	final protected function get_subscription_status() {

		static $status = null;

		if ( null !== $status )
			return $status;

		return $status = [
			'key'     => $this->get_option( 'api_key' ),
			'email'   => $this->get_option( 'activation_email' ),
			'active'  => $this->get_option( '_activated' ),
			'level'   => $this->get_option( '_activation_level' ),
			'data'    => $this->get_option( '_remote_subscription_status' ),
		];
	}

	/**
	 * Converts markdown text into HMTL.
	 *
	 * Does not support list or block elements. Only inline statements.
	 * Expects input to be escaped: For added security, the converted strings are escaped once more.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Can now convert stacked strong/em correctly.
	 * @since 1.2.0 : 1. Removed word boundary requirement for strong.
	 *                2. Now accepts regex count their numeric values in string.
	 *                3. Fixed header 1~6 calculation.
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text The text that might contain markdown. Expected to be escaped.
	 * @param array $convert The markdown style types wished to be converted.
	 *              If left empty, it will convert all.
	 * @return string The markdown converted text.
	 */
	final public function convert_markdown( $text, $convert = [] ) {

		preprocess : {
			$text = str_replace( "\r\n", "\n", $text );
			$text = str_replace( "\t", ' ', $text );
			$text = trim( $text );
		}

		if ( '' === $text )
			return '';

		/**
		 * The conversion list's keys are per reference only.
		 */
		$conversions = [
			'**'   => 'strong',
			'*'    => 'em',
			'`'    => 'code',
			'[]()' => 'a',
			'======'  => 'h6',
			'====='  => 'h5',
			'===='  => 'h4',
			'==='  => 'h3',
			'=='   => 'h2',
			'='    => 'h1',
		];

		$md_types = empty( $convert ) ? $conversions : array_intersect( $conversions, $convert );

		if ( 2 === count( array_intersect( $md_types, [ 'em', 'strong' ] ) ) ) :
			$count = preg_match_all( '/(?:\*{3})([^\*{\3}]+)(?:\*{3})/', $text, $matches, PREG_PATTERN_ORDER );

			for ( $i = 0; $i < $count; $i++ ) {
				$text = str_replace(
					$matches[0][ $i ],
					sprintf( '<strong><em>%s</em></strong>', \esc_html( $matches[1][ $i ] ) ),
					$text
				);
			}
		endif;

		foreach ( $md_types as $type ) :
			switch ( $type ) :
				case 'strong' :
					$count = preg_match_all( '/(?:\*{2})([^\*{\2}]+)(?:\*{2})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<strong>%s</strong>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'em' :
					$count = preg_match_all( '/(?:\*{1})([^\*{\1}]+)(?:\*{1})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<em>%s</em>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'code' :
					$count = preg_match_all( '/(?:`{1})([^`{\1}]+)(?:`{1})/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<code>%s</code>', \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'h6' :
				case 'h5' :
				case 'h4' :
				case 'h3' :
				case 'h2' :
				case 'h1' :
					$amount = filter_var( $type, FILTER_SANITIZE_NUMBER_INT );
					//* Considers word non-boundary. @TODO consider removing this?
					$expression = sprintf( '/(?:\={%1$s})\B([^\={\%1$s}]+)\B(?:\={%1$s})/', $amount );
					$count = preg_match_all( $expression, $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<%1$s>%2$s</%1$s>', \esc_attr( $type ), \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				case 'a' :
					$count = preg_match_all( '/(?:(?:\[{1})([^\]{1}]+)(?:\]{1})(?:\({1})([^\)\(]+)(?:\){1}))/', $text, $matches, PREG_PATTERN_ORDER );

					for ( $i = 0; $i < $count; $i++ ) {
						$text = str_replace(
							$matches[0][ $i ],
							sprintf( '<a href="%s" target="_blank" rel="nofollow noreferrer noopener">%s</a>', \esc_url( $matches[2][ $i ] ), \esc_html( $matches[1][ $i ] ) ),
							$text
						);
					}
					break;

				default :
					break;
			endswitch;
		endforeach;

		return $text;
	}

	/**
	 * Determines filesize in bytes from intput.
	 *
	 * Accepts multibyte.
	 *
	 * @since 1.2.0
	 *
	 * @param string The content to calculate size from.
	 * @return int The filesize in bytes/octets.
	 */
	final public function get_filesize( $content = '' ) {

		if ( '' === $content )
			return 0;

		return (int) strlen( $content );
	}

	/**
	 * Determines whether we're on the SEO extension manager settings page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @param bool $secure Whether to prevent insecure checks.
	 * @return bool
	 */
	final public function is_tsf_extension_manager_page( $secure = true ) {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( false === \is_admin() )
			return $cache = false;

		if ( $secure ) {
			//* Don't load from $_GET request if secure.
			return $cache = \the_seo_framework()->is_menu_page( $this->seo_extensions_menu_page_hook );
		} else {
			//* Don't cache if insecure.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return $this->ajax_is_tsf_extension_manager_page();
			} else {
				return \the_seo_framework()->is_menu_page( $this->seo_extensions_menu_page_hook, $this->seo_extensions_page_slug );
			}
		}
	}
}
