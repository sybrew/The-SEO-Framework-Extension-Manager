<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2021 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'manager/options' );

/**
 * Require error trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/error' );

/**
 * Class TSF_Extension_Manager\Core
 *
 * Holds plugin core functions.
 *
 * @since 1.0.0
 * @access private
 */
class Core {
	use Construct_Core_Interface,
		Destruct_Core_Public_Final_Interface,
		Options,
		Error;

	/**
	 * @since 1.0.0
	 *
	 * @var string The validation nonce name.
	 */
	protected $nonce_name;

	/**
	 * @since 1.0.0
	 *
	 * @var string The validation request name.
	 */
	protected $request_name = [];

	/**
	 * @since 1.0.0
	 *
	 * @var string The validation nonce action.
	 */
	protected $nonce_action = [];

	/**
	 * Constructor, initializes actions and sets up variables.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		// Verify integrity.
		$that = __NAMESPACE__ . ( \is_admin() ? '\\LoadAdmin' : '\\LoadFront' );
		$this instanceof $that or \wp_die( -1 );

		$this->nonce_name   = 'tsf_extension_manager_nonce_name';
		$this->request_name = [
			// Reference convenience.
			'default'           => 'default',

			// Account activation and more.
			'activate-key'      => 'activate-key',
			'activate-external' => 'activate-external',
			'activate-free'     => 'activate-free',
			'deactivate'        => 'deactivate',
			'enable-feed'       => 'enable-feed',

			// Extensions.
			'activate-ext'      => 'activate-ext',
			'deactivate-ext'    => 'deactivate-ext',
		];
		$this->nonce_action = [
			// Reference convenience.
			'default'           => 'tsfem_nonce_action',

			// Account activation and more.
			'activate-free'     => 'tsfem_nonce_action_free_account',
			'activate-key'      => 'tsfem_nonce_action_key_account',
			'activate-external' => 'tsfem_nonce_action_external_account',
			'deactivate'        => 'tsfem_nonce_action_deactivate_account',
			'enable-feed'       => 'tsfem_nonce_action_feed',

			// Extensions.
			'activate-ext'      => 'tsfem_nonce_action_activate_ext',
			'deactivate-ext'    => 'tsfem_nonce_action_deactivate_ext',
		];
		/**
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
		return \is_int( 9223372036854775807 );
	}

	/**
	 * Handles extensions. On both the front end and back-end.
	 *
	 * @since 1.0.0
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
			// Failed options instance checksum.
			$this->set_error_notice( [ 2001 => '' ] );
			return $loaded = false;
		}

		// Some AJAX functions require Extension layout traits to be loaded.
		if ( \is_admin() && \wp_doing_ajax() ) {
			// This should not ever be a security issue. However, sanity.
			if ( \TSF_Extension_Manager\can_do_manager_settings() && \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) )
				$this->ajax_is_tsf_extension_manager_page( true );
		}

		$this->get_verification_codes( $_instance, $bits );
		Extensions::initialize( 'list', $_instance, $bits );
		Extensions::set_account( $this->get_subscription_status() );

		$checksum = Extensions::get( 'extensions_checksum' );
		$result   = $this->validate_extensions_checksum( $checksum );

		if ( true !== $result ) :
			switch ( $result ) {
				case -2:
					// Failed checksum.
					$this->set_error_notice( [ 2002 => '' ] );
					break;

				case -1:
					// No extensions have ever been active...
					break;
			}

			Extensions::reset();
			return $loaded = false;
		endif;

		$extensions = Extensions::get( 'active_extensions_list' );

		Extensions::reset();

		if ( empty( $extensions ) )
			return $loaded = false;

		$this->get_verification_codes( $_instance, $bits );
		Extensions::initialize( 'load', $_instance, $bits );

		foreach ( $extensions as $slug => $active ) {
			$this->get_verification_codes( $_instance, $bits );
			Extensions::load_extension( $slug, $_instance, $bits );
		}

		Extensions::reset();

		return $loaded = true;
	}

	/**
	 * Verifies integrity of the options.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now is public.
	 *
	 * @return bool True if options are valid, false if not.
	 */
	final public function are_options_valid() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		// phpcs:ignore -- No objects are inserted, nor is this ever unserialized.
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
	 * @return int (bitwise) : {
	 *    0 = 00 : Did nothing.
	 *    1 = 01 : Cleared PHP output buffer.
	 *    2 = 10 : Cleared HTTP headers.
	 *    3 = 11 : Did 1 and 2.
	 * }
	 */
	final public function _clean_response_header() {

		$retval = 0;
		// PHP 5.6+ //= $i = 0;

		if ( $level = ob_get_level() ) { // phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- this is fine.
			while ( $level-- ) {
				ob_end_clean();
			}
			$retval |= 1; //= 2 ** $i
		}

		// PHP 5.6+ //= $i++;

		// wp_ajax sets required headers early.
		if ( ! headers_sent() ) {
			header_remove();
			$retval |= 2; //= 2 ** $i
		}

		return $retval;
	}

	/**
	 * Sets status header.
	 *
	 * @since 1.2.0
	 * @uses status_header(): https://developer.wordpress.org/reference/functions/status_header/
	 *
	 * @param bool   $code The status code.
	 * @param string $type The header type.
	 */
	final public function set_status_header( $code = 200, $type = '' ) {

		switch ( $type ) :
			case 'json':
				header( 'Content-Type: application/json; charset=' . \get_option( 'blog_charset' ) );
				break;

			case 'html':
			default:
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
	 * @TODO set a standard for $data, i.e. [ 'results'=>[],'html'=>"", etc. ];
	 *
	 * @param mixed  $data The data that needs to be send.
	 * @param string $type The status type.
	 */
	final public function send_json( $data, $type = 'success' ) {

		$json = -1;

		$r = $this->_clean_response_header();

		if ( $r & 2 ) {
			$this->set_status_header( 200, 'json' );
			$json = 1;
		} else {
			$this->set_status_header( null, 'json' );
		}

		echo json_encode( compact( 'data', 'type', 'json' ) );
		exit;
	}

	/**
	 * Sends out HTML data for AJAX.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed  $html The HTML that needs to be send. Must be escaped.
	 * @param string $type The status type.
	 */
	final public function send_html( $html, $type = 'success' ) {

		$r = $this->_clean_response_header();

		if ( $r & 2 ) {
			$this->set_status_header( 200, 'html' );
		} else {
			$this->set_status_header( null, 'html' );
		}

		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- See method doc.
		echo $html;
		exit;
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
	 * @since 2.4.0 Added nonce capability requirement, extra sanity for when the caller fails to do so.
	 * @access private
	 * @ignore unused. Leftover from the never-released Transporter
	 *
	 * @param array $args - Required : {
	 *    'options_key'   => string The extension options key,
	 *    'options_index' => string The extension options index,
	 *    'menu_slug'     => string The extension options menu slug,
	 *    'nonce_name'    => string The extension POST actions nonce name,
	 *    'request_name'  => string The extension desired POST action request index key name,
	 *    'nonce_action'  => string The extesnion desired POST action request full name,
	 *    'capability'    => string The extesnion desired user capability,
	 * }
	 * @return array|bool False on failure; array containing the jQuery.post object.
	 */
	final public function _get_ajax_post_object( array $args ) {

		$required = [
			'options_key',
			'options_index',
			'menu_slug',
			'nonce_name',
			'request_name',
			'nonce_action',
			'capability',
		];

		// If the required keys aren't found, bail.
		if ( ! $this->has_required_array_keys( $args, $required ) )
			return false;

		$url = $this->get_admin_page_url( $args['menu_slug'] );

		if ( ! $url )
			return false;

		$args = [
			'options_key'   => \sanitize_key( $args['options_key'] ),
			'options_index' => \sanitize_key( $args['options_index'] ),
			'nonce_name'    => \sanitize_key( $args['nonce_name'] ),
		];

		$post = [
			'url'    => $url,
			'method' => 'post',
			'data'   => [
				$args['options_key'] => [
					$args['options_index'] => [
						'nonce-action' => $args['request_name'],
					],
				],
				$args['nonce_name']  => \current_user_can( $required['capability'] ) ? \wp_create_nonce( $args['nonce_action'] ) : '',
				'_wp_http_referer'   => \esc_attr( \wp_unslash( $_SERVER['REQUEST_URI'] ) ), // input var & sanitization ok.
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
	 * @since 1.3.0 Removed and shifted 2nd and 3rd parameter.
	 *
	 * @param string|array $value The array or string to loop. First call must be array.
	 * @param int          $i     The iteration count. This shouldn't be filled in.
	 * @param bool         $get   Whether to return the value. This shouldn't be filled in.
	 * @return array|false The iterated array to string. False if input isn't array.
	 */
	final public function matosa( $value, $i = 0, $get = true ) {

		$output = '';
		$i++;

		static $last = null;

		if ( \is_array( $value ) ) {

			$index = key( $value );

			$last = $item = reset( $value );

			if ( \is_array( $item ) ) {
				if ( 1 === $i ) {
					$output .= $index . $this->matosa( $item, $i, false );
				} else {
					$output .= '[' . $index . ']' . $this->matosa( $item, $i, false );
				}
			}
		} elseif ( 1 === $i ) {
			// Input is scalar or object.
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
	 * Determines if all required keys are set in $input.
	 *
	 * @since 1.2.0
	 * @since 1.5.0 $compare now accepts keys as values only.
	 *
	 * @param array $input The input keys.
	 * @param array $compare The keys to compare it to.
	 * @return bool True on success, false if keys are missing.
	 */
	final public function has_required_array_keys( array $input, array $compare ) {
		return empty( array_diff_key( array_flip( $compare ), $input ) );
	}

	/**
	 * Checks whether the variable is set and passes it back.
	 * If the value isn't set, it will set it to the fallback variable.
	 *
	 * Basically, a PHP < 7 wrapper for null coalescing.
	 *
	 * It will also return the value so it can be used in a return statement.
	 *
	 * Example: `$v ?? $f` becomes `coalesce_var( $v, $f )`
	 * The fallback value must always be set, so performance benefits thereof aren't present.
	 *
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
			// wp_die() can be filtered. Remove filters JIT.
			\remove_all_filters( 'wp_die_ajax_handler' );
			\remove_all_filters( 'wp_die_xmlrpc_handler' );
			\remove_all_filters( 'wp_die_handler' );

			\wp_die( \esc_html( $message ) );
		}

		// Don't spam error log.
		if ( false === $this->_has_died() ) {

			$this->_has_died( true );

			if ( $message ) {
				// Use debug_print_backtrace() to debug.
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
		$other_vars = get_class_vars( \get_called_class() );

		$properties = array_merge( $class_vars, $other_vars );

		foreach ( $properties as $property => $value ) :
			if ( isset( $this->$property ) )
				$this->$property = \is_array( $this->$property ) ? [] : null;
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
	 * @param array  $current_filter The filter to walk.
	 * @param string $key            The current array key.
	 * @return bool true
	 */
	final protected function stop_class_filters( $current_filter, $key ) {

		$_key   = key( $current_filter );
		$filter = reset( $current_filter );

		static $_this = null;

		if ( null === $_this )
			$_this = \get_class( $this );

		if ( isset( $filter['function'] ) ) {
			if ( \is_array( $filter['function'] ) ) :
				foreach ( $filter['function'] as $k => $function ) :
					if ( \is_object( $function ) && $function instanceof $_this )
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
	 * @param int    $bit      The verification instance bit. Passed by reference.
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
	 * @param int    $count    The amount of instances to loop for.
	 * @param string $instance The verification instance key. Passed by reference.
	 * @param array  $bits     The verification instance bits. Passed by reference.
	 * @yield array Generator : {
	 *    $instance string The verification instance key
	 *    $bits array The verification instance bits
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
	 * @param array  $bits The verification bits. Passed by reference.
	 */
	final protected function get_verification_codes( &$instance = null, &$bits = null ) {
		$bits     = $this->get_bits();
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
	 * @since 2.5.1 Removed needless hashing to improve performance, exchanged it for pepper.
	 *
	 * @param int|null $bit The instance bit.
	 * @return string $instance The instance key.
	 */
	final protected function get_verification_instance( $bit = null ) {

		static $instance = [];

		$bits = $this->get_bits();
		$_bit = $bits[0];

		$n_bit = ~$_bit;

		// Timing-attack safe.
		if ( isset( $instance[ $n_bit ] ) ) {
			//= Timing attack mitigated.

			// Don't use hash_equals(). This is already safe.
			if ( empty( $instance[ $bit ] ) || $instance[ $n_bit ] !== $instance[ $bit ] ) {
				// Only die on plugin settings page upon failure. Otherwise kill instance and all bindings.
				$this->_maybe_die( 'Error -1: The SEO Framework - Extension Manager instance verification failed.' );
				$instance = [];
				return '';
			}

			// Set retval and empty to prevent recursive timing attacks.
			$_retval  = $instance[ $bit ];
			$instance = [];

			return $_retval;
		}

		static $timer  = null;
		static $pepper = null;

		if ( null === $timer ) {
			// It's over ninethousand! And also a prime.
			$timer  = $this->is_64() ? time() * 9001 : PHP_INT_MAX / 9001;
			$pepper = mt_rand( -$timer, $timer );
		} else {
			$timer += $pepper;
		}

		return $instance[ $bit ] = $instance[ $n_bit ] = "$_bit$timer$bit";
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
	 * @link https://theprime.site/
	 *
	 * @return array The verification bits.
	 */
	final protected function get_bits() {
		// phpcs:disable, Generic.WhiteSpace.DisallowSpaceIndent

		static $_bit, $bit;

		if ( isset( $bit ) )
			goto generate;

		/**
		 * Create new bits on first run.
		 * Prevents random abstract collision by filtering odd numbers.
		 *
		 * Uses various primes to prevent overflow (which is heavy and can loop) on 32 bit architecture.
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
			and \is_int( $_i )
			and ( $_i + $_boundary ) < PHP_INT_MAX // if this fails, there's a precision error in PHP.
			and $bit = $_bit = mt_rand( ~ $_i, $_i )
			and $bit & 1
			and $bit = $_bit++;
		}

		// Hit 0 or is overflown on 32 bit. Retry.
		if ( 0 === $bit || \is_double( $bit ) ) {
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
			    $bit  = $_bit <= 0 ? ~$bit | ~$_bit-- : ~$bit | ~$_bit++
			and $bit  = $bit++ & $_bit--
			and $_bit = $_bit < 0 ? $_bit : ~$_bit
			and $bit  = ( ~$_bit++ ) + 1;
		}

		return [ $_bit, $bit ];
		// phpcs:enable, Generic.WhiteSpace.DisallowSpaceIndent
	}

	/**
	 * Hashes input $data with the best hash type available while also using hmac.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $data   The data to hash.
	 * @param string $scheme Authentication scheme ( 'instance', 'auth', 'secure_auth', 'nonce' ).
	 *                       Default 'instance'.
	 * @return string Hash of $data.
	 */
	final protected function hash( $data, $scheme = 'instance' ) {
		return hash_hmac( $this->get_hash_type(), $data, $this->get_salt( $scheme ) );
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

		$a   = (string) $uid;
		$b   = strrev( $a );
		$len = \strlen( $a );
		$r   = '';

		for ( $i = 0; $i < $len; $i++ ) {
			$r .= \ord( $a[ $i ] ) . $b[ $i ];
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
	 * @param string $uid    The unique ID for the hash. A good choice would be the method name.
	 * @param int    $length The time length in seconds.
	 * @param int    $end    UNIX timestamp where the hash invalidates. Defaults to now.
	 * @return string The timed hash that will always return the same.
	 */
	final public function _get_timed_hash( $uid, $length = 3600, $end = 0 ) {

		if ( ! $uid || ! $length )
			return '';

		$_time  = time();
		$_end   = $end ?: $_time;
		$_delta = $_end > $_time ? $_end - $_time : $_time - $_end;

		$now_x = floor( ( $_time - $_delta ) / $length );

		$string = $uid . '\\' . $now_x . '\\' . $uid;

		return $this->hash( $string, 'auth' );
	}

	/**
	 * Generates salt from WordPress defined constants.
	 *
	 * Taken from WordPress core function `wp_salt()` and adjusted accordingly.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_salt/
	 * @since 1.0.0
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

		// 'instance' picks a random key.
		static $instance_scheme = null;
		if ( null === $instance_scheme ) {
			$_key            = mt_rand( 0, \count( $schemes ) - 1 );
			$instance_scheme = $schemes[ $_key ];
		}
		$_scheme = 'instance' === $scheme ? $instance_scheme : $scheme;

		if ( \in_array( $_scheme, $schemes, true ) ) {
			foreach ( [ 'key', 'salt' ] as $type ) :
				$const = strtoupper( "{$_scheme}_{$type}" );
				if ( \defined( $const ) && \constant( $const ) ) {
					$values[ $type ] = \constant( $const );
				} elseif ( empty( $values[ $type ] ) ) {
					$values[ $type ] = \get_site_option( "{$_scheme}_{$type}" );
					if ( ! $values[ $type ] ) {
						/**
						 * Hash keys not defined in wp-config.php nor in database.
						 * Let wp_salt() handle this. This should run at most once per site per scheme.
						 */
						$values[ $type ] = \wp_salt( $_scheme );
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
	 *
	 * @return string The working hash type to be used within hash() functions.
	 */
	final public function get_hash_type() {

		static $type = null;

		if ( isset( $type ) )
			return $type;

		$algos = hash_algos();

		if ( \in_array( 'sha256', $algos, true ) ) {
			$type = 'sha256';
		} elseif ( \in_array( 'sha1', $algos, true ) ) {
			$type = 'sha1';
		} else {
			$type = 'md5';
		}

		return $type;
	}

	/**
	 * Determines whether the plugin is network activated.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the plugin is active in network mode.
	 */
	final public function is_plugin_in_network_mode() {
		// TODO remove this! It now renders network mode as singular installations per site. This is NOT what I promised.
		return false;
		// phpcs:disable
		static $network_mode = null;

		if ( isset( $network_mode ) )
			return $network_mode;

		if ( ! \is_multisite() )
			return $network_mode = false;

		$plugins = \get_site_option( 'active_sitewide_plugins' );

		return $network_mode = isset( $plugins[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
		// phpcs:enable
	}

	/**
	 * Grants a class access to the verification instance and bits of this object.
	 * by returning the $_instance and $bits parameters.
	 * Once.
	 *
	 * @since 1.0.0
	 * @NOTE Expensive operation. TODO set secret instead?
	 * @see $this->_yield_verification_instance() for faster looping instances.
	 * @access private
	 *
	 * @param object $object    The class object. Passed by reference.
	 * @param string $_instance The verification instance. Passed by reference.
	 * @param array  $bits      The verification instance bits. Passed by reference.
	 * @return bool True on success, false on failure.
	 */
	final public function _request_premium_extension_verification_instance( &$object, &$_instance, &$bits ) {

		if ( false === $this->is_premium_user() || false === $this->are_options_valid() )
			goto failure;

		$allowed_classes = [
			'TSF_Extension_Manager\\Extension\\Monitor\\Admin',
		];

		if ( \in_array( \get_class( $object ), $allowed_classes, true ) ) {
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
	 * @param string $_instance The verification instance. Passed by reference.
	 * @param array  $bits      The verification instance bits. Passed by reference.
	 * @return bool False on failure, true on success.
	 */
	final public function _init_early_extension_autoloader( $path, $namespace, &$_instance, &$bits ) {

		if ( $this->_has_died() )
			return false;

		if ( false === ( $this->_verify_instance( $_instance, $bits[1] ) or $this->_maybe_die() ) )
			return false;

		return $this->register_extension_autoload_path( $path, $namespace );
	}

	/**
	 * Registers autoloading classes for extensions and activates autoloader.
	 * If the account isn't premium, it will not be loaded.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 : 1. Now handles namespaces instead of class bases.
	 *                2. Now is protected.
	 *                3. Removed some checks as it's protected.
	 * @since 1.5.0 No longer returns void.
	 * @access private
	 *
	 * @param string $path      The extension path to look for.
	 * @param string $namespace The namespace.
	 * @return bool : {
	 *    false  : The extension namespace wasn't set.
	 *    true   : The extension namespace is set.
	 * }
	 */
	final protected function register_extension_autoload_path( $path, $namespace ) {

		if ( false === $this->are_options_valid() )
			return false;

		$this->register_extension_autoloader();

		return (bool) $this->set_extension_autoload_path( $path, $namespace );
	}

	/**
	 * Registers and activated autoloader for extensions.
	 *
	 * @since 1.2.0
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
	 * @since 1.3.0 Now handles namespaces instead of class bases.
	 * @since 2.5.1 Now supports mixed cases.
	 *
	 * @param string|null $path      The extension path to look for.
	 * @param string|null $namespace The class name including namespace.
	 * @param string|null $get       The namespace path to get from cache.
	 * @return void|bool|string : {
	 *    false  : The extension namespace wasn't set when $get is true.
	 *    string : The extension namespace location when $get is true.
	 *    true   : The extension namespace is set.
	 *    void   : The extension namespace isn't set. $namespace was considered false-esque.
	 * }
	 */
	final protected function set_extension_autoload_path( $path, $namespace, $get = null ) {

		static $locations = [];

		if ( $get ) {
			$get = strtolower( $get );

			if ( isset( $locations[ $get ] ) )
				return $locations[ $get ];

			return false;
		} else {
			$namespace = strtolower( $namespace );

			if ( $namespace ) {
				$locations[ $namespace ] = strtolower( $path );
				return true;
			}
		}

		// phpcs:ignore, Squiz.PHP.NonExecutableCode.ReturnNotRequired -- readability.
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
	 * @since 1.3.0 Now handles namespaces instead of class bases.
	 * @since 2.5.1 Now supports mixed class case.
	 *
	 * @param string $class The extension classname.
	 * @return bool False if file hasn't yet been included, otherwise true.
	 */
	final protected function autoload_extension_class( $class ) {

		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'tsf_extension_manager\\extension\\', 0 ) )
			return;

		static $loaded = [];

		if ( isset( $loaded[ $class ] ) )
			return $loaded[ $class ];

		if ( $this->_has_died() ) {
			$this->create_class_alias( $class );
			return false;
		}

		static $_timenow = true;

		if ( $_timenow ) {
			$_bootstrap_timer = microtime( true );
			$_timenow         = false;
		} else {
			$_bootstrap_timer = 0;
		}

		$_class = str_replace( 'tsf_extension_manager\\extension\\', '', $class );
		$_ns    = substr( $_class, 0, strpos( $_class, '\\' ) );

		$_path = $this->get_extension_autload_path( $_ns );

		if ( $_path ) {
			$_file = str_replace( '_', '-', str_replace( $_ns . '\\', '', $_class ) );

			$this->get_verification_codes( $_instance, $bits );

			$loaded[ $class ] = require "{$_path}{$_file}.class.php";
		} else {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'Class <code>' . \esc_html( $class ) . '</code> has not been registered.' );

			// Prevent fatal errors.
			$this->create_class_alias( $class );

			$loaded[ $class ] = false;
		}

		if ( $_bootstrap_timer ) {
			$_t = microtime( true ) - $_bootstrap_timer;
			\The_SEO_Framework\_bootstrap_timer( $_t );
			\TSF_Extension_Manager\_bootstrap_timer( $_t );
			$_timenow = true;
		}

		return $loaded[ $class ];
	}

	/**
	 * Validates extensions option checksum.
	 *
	 * @since 1.0.0
	 *
	 * @param array $checksum The extensions checksum.
	 * @return int|bool, Negative int on failure, true on success.
	 */
	final protected function validate_extensions_checksum( $checksum ) {

		$required = [ 'hash', 'matches', 'type' ];

		// If the required keys aren't found, bail.
		if ( ! $this->has_required_array_keys( $checksum, $required ) ) {
			return -1;
		} elseif ( ! hash_equals( $checksum['matches'][ $checksum['type'] ], $checksum['hash'] ) ) {
			return -2;
		}

		return true;
	}

	/**
	 * Returns a numeric order list for all extensions.
	 *
	 * @since 2.0.0
	 *
	 * @return array { string Extension => int Order }
	 */
	final public function get_extension_order() {

		static $order = [];

		if ( $order ) return $order;

		$this->get_verification_codes( $_instance, $bits );
		Extensions::initialize( 'list', $_instance, $bits );

		$last = 0;

		foreach ( Extensions::get( 'extensions_list' ) as $slug => $data ) {
			$order[ $slug ] = ( $last = $last + 10 );
		}

		Extensions::reset();

		return $order;
	}

	/**
	 * Creates a class alias to prevent fatal errors.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class The class name to create.
	 */
	final protected function create_class_alias( $class ) {
		class_alias( __NAMESPACE__ . '\\Alias', $class, true ); // autoload ..\Alias.
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
		return \intval( $px ) * .75;
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
	 * Determines whether the plugin's use is connected.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if the plugin is connected to the Enterprise, Premium, or Essential API handler.
	 */
	final public function is_connected_user() {
		return \in_array( $this->get_option( '_activation_level' ), [ 'Enterprise', 'Premium', 'Essentials' ], true );
	}

	/**
	 * Determines whether the plugin's use is Premium.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now public. Enjoy.
	 *
	 * @return bool True if the plugin is connected to the Premium API handler.
	 */
	final public function is_premium_user() {
		return \in_array( $this->get_option( '_activation_level' ), [ 'Enterprise', 'Premium' ], true );
	}

	/**
	 * Determines whether the plugin's use is Enterprise.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if the plugin is connected to the Premium API handler and is of level Enterprise.
	 */
	final public function is_enterprise_user() {
		return 'Enterprise' === $this->get_option( '_activation_level' );
	}

	/**
	 * Returns subscription status from local options.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 The parameters are now passed by reference.
	 * @access private
	 *
	 * @param string $_instance The verification instance key. Passed by reference.
	 * @param array  $bits      The verification instance bits. Passed by reference.
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
	 *
	 * @return array Current subscription status.
	 */
	final protected function get_subscription_status() {

		static $status = null;

		if ( null !== $status )
			return $status;

		return $status = [
			'key'    => $this->get_option( 'api_key' ),
			'email'  => $this->get_option( 'activation_email' ),
			'active' => $this->get_option( '_activated' ),
			'level'  => $this->get_option( '_activation_level' ),
			'data'   => $this->get_option( '_remote_subscription_status' ),
		];
	}

	/**
	 * Returns font file location.
	 * To be used for testing font-pixels.
	 *
	 * @since 1.0.0
	 *
	 * @param string $font The font name, should include .ttf.
	 * @param bool   $url  Whether to return a path or URL.
	 * @return string The font URL or path. Not escaped.
	 */
	final public function get_font_file_location( $font = '', $url = false ) {
		if ( $url ) {
			return TSF_EXTENSION_MANAGER_DIR_URL . 'lib/fonts/' . $font;
		} else {
			return TSF_EXTENSION_MANAGER_DIR_PATH . 'lib' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $font;
		}
	}

	/**
	 * Returns image file location.
	 *
	 * @since 1.0.0
	 *
	 * @param string $image The image name, should include .jpg, .png, etc..
	 * @param bool   $url   Whether to return a path or URL.
	 * @return string The image URL or path. Not escaped.
	 */
	final public function get_image_file_location( $image = '', $url = false ) {
		if ( $url ) {
			return TSF_EXTENSION_MANAGER_DIR_URL . 'lib/images/' . $image;
		} else {
			return TSF_EXTENSION_MANAGER_DIR_PATH . 'lib' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $image;
		}
	}

	/**
	 * Determines filesize in bytes from intput.
	 *
	 * Accepts multibyte.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content The content to calculate size from.
	 * @return int The filesize in bytes/octets.
	 */
	final public function get_filesize( $content = '' ) {

		if ( '' === $content )
			return 0;

		return (int) \strlen( $content );
	}

	/**
	 * Sanitizes AJAX input string.
	 * Removes NULL, converts to string, normalizes entities and escapes attributes.
	 * Also prevents regex execution.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now is public and moved to class Core.
	 *
	 * @param string $input The AJAX input string.
	 * @return string $output The cleaned AJAX input string.
	 */
	final public function s_ajax_string( $input ) {
		return trim( \esc_attr( \wp_kses_normalize_entities( \strval( \wp_kses_no_null( $input ) ) ) ), ' \\/#' );
	}

	/**
	 * Filters keys from input array.
	 *
	 * @since 1.5.0
	 *
	 * @param array $input The input with possible keys.
	 * @param array $keys The wanted keys, e.g. ['key','key2']
	 */
	final public function filter_keys( array $input, array $keys ) {
		$expected_keys = array_fill_keys( $keys, '' );
		return array_intersect_key( array_merge( $expected_keys, $input ), $expected_keys );
	}

	/**
	 * Determines whether we're on the SEO extension manager settings page.
	 *
	 * @since 1.0.0
	 * @since 2.0.4 No longer caches invalid requests.
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
			// Don't load from $_GET request if secure.
			if ( \did_action( 'current_screen' ) ) {
				return $cache = \the_seo_framework()->is_menu_page( $this->seo_extensions_menu_page_hook );
			} else {
				// current_screen isn't set up.
				return false;
			}
		} else {
			// Don't cache if insecure.
			if ( \wp_doing_ajax() ) {
				return $this->ajax_is_tsf_extension_manager_page();
			} else {
				return \the_seo_framework()->is_menu_page( $this->seo_extensions_menu_page_hook, $this->seo_extensions_page_slug );
			}
		}
	}
}
