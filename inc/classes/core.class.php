<?php
/**
 * @package TSF_Extension_Manager\Classes
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
	 * @var string The validation nonce name.
	 */
	protected $nonce_name;

	/**
	 * @since 1.0.0
	 * @var string The validation request name.
	 */
	protected $request_name = [];

	/**
	 * @since 1.0.0
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
			'transfer-domain'   => 'transfer-domain',

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
			'transfer-domain'   => 'tsfem_nonce_action_transfer_domain',

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

		static $loaded;

		if ( isset( $loaded ) )
			return $loaded;

		if ( \wp_installing() || ! $this->is_plugin_activated() )
			return $loaded = false;

		if ( ! $this->are_options_valid() ) {
			// Failed options instance checksum.
			$this->set_error_notice( [ 2001 => '' ] );
			return $loaded = false;
		}

		// Some AJAX functions require Extension layout traits to be loaded.
		if ( \wp_doing_ajax() ) {
			// This should not ever be a security issue. However, sanity.
			if ( \TSF_Extension_Manager\can_do_manager_settings() && \check_ajax_referer( 'tsfem-ajax-nonce', 'nonce', false ) )
				$this->ajax_is_tsf_extension_manager_page( true );
		}

		$this->get_verification_codes( $_instance, $bits );
		Extensions::initialize( 'list', $_instance, $bits );
		Extensions::set_account( $this->get_subscription_status() );

		$checksum = Extensions::get( 'extensions_checksum' );
		$result   = $this->validate_extensions_checksum( $checksum );

		if ( true !== $result ) {
			// -2: failed checksum, -1: no extensions have ever been active.
			if ( -2 === $result ) {
				// Failed checksum.
				$this->set_error_notice( [ 2002 => '' ] );
			}

			Extensions::reset();
			return $loaded = false;
		}

		$extensions = Extensions::get( 'active_extensions_list' );

		Extensions::reset();

		if ( ! $extensions )
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
	 * Returns website's instance key from option. Generates one if non-existent.
	 *
	 * @since 1.0.0
	 * @since 2.6.1 1. Now generates numerics at the end, to reduce potential offensive content.
	 *              2. Renamed from `get_activation_instance()` and moved to Core to allow front-end tests.
	 *
	 * @return string Instance key.
	 */
	final protected function get_options_instance_key() {
		static $instance;
		return $instance ??= $this->get_option( '_instance' )
			?: \wp_generate_password( 29, false )
				. mt_rand( 12, 98 )
				. mt_rand( 1, 9 ); // Remove likelihood of leading zeros.
	}

	/**
	 * Verifies integrity of the options.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now is public.
	 * @since 2.6.1 Now handles pre-activation.
	 *
	 * @return bool True if options are valid, false if not.
	 */
	final public function are_options_valid() {

		static $memo;
		if ( isset( $memo ) ) return $memo;

		$options = \get_option( \TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] );

		// There's nothing to verify yet during setup.
		if ( ! $options ) return $memo = true;

		return $memo = hash_equals(
			$this->hash_options( $options ),
			(string) \get_option( "tsfem_i_{$this->get_options_instance_key()}" )
		);
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

		static $last;

		if ( \is_array( $value ) ) {

			$index = key( $value );

			$last = $item = reset( $value );

			if ( \is_array( $item ) ) {
				if ( 1 === $i ) {
					$output .= $index . $this->matosa( $item, $i, false );
				} else {
					$output .= "[{$index}]" . $this->matosa( $item, $i, false );
				}
			}
		} elseif ( 1 === $i ) {
			// Input is scalar or object.
			$last = null;
			return false;
		}

		if ( $get ) {
			// FIXME? $last isn't cleared here. Probably a foreach would be better.
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
	final public function has_required_array_keys( $input, $compare ) {
		return ! array_diff_key( array_flip( $compare ), $input );
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

		// secure=false because we're shutting down the plugin.
		if ( $this->is_tsf_extension_manager_page( false ) ) {
			// wp_die() can be filtered. Remove filters JIT.
			\remove_all_filters( 'wp_die_ajax_handler' );
			\remove_all_filters( 'wp_die_xmlrpc_handler' );
			\remove_all_filters( 'wp_die_handler' );

			\wp_die( \esc_html( $message ) );
			die;
		}

		// Don't spam error log.
		if ( ! $this->_has_died() ) {

			$this->_has_died( true );

			if ( $message ) {
				// Use debug_print_backtrace() to debug.
				\tsf()->_doing_it_wrong( __CLASS__, 'Class execution stopped with message: <strong>' . \esc_html( $message ) . '</strong>' );
			} else {
				\tsf()->_doing_it_wrong( __CLASS__, 'Class execution stopped because of an error.' );
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

		foreach ( $properties as $property => $value )
			if ( isset( $this->$property ) )
				$this->$property = \is_array( $this->$property ) ? [] : null;

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

		static $_this;

		if ( ! $_this )
			$_this = \get_class( $this );

		if ( \is_array( $filter['function'] ?? null ) )
			foreach ( $filter['function'] as $function )
				if ( \is_object( $function ) && $function instanceof $_this )
					unset( $GLOBALS['wp_filter'][ $key ][ $_key ] );

		return true;
	}

	/**
	 * Verifies extension loading instances. May clear the input through reference.
	 * Kills extension load sequence on failure.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $instance The verification instance key. Passed by reference.
	 * @param int    $bit      The verification instance bit. Passed by reference.
	 * @return bool False when extension may be loaded. True otherwise.
	 */
	final public function _blocked_extension_file( &$instance, &$bit ) {

		if ( $this->_has_died() )
			return true;

		if ( ! ( $this->_verify_instance( $instance, $bit ) or $this->_maybe_die() ) )
			return true;

		return false;
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

		if ( $this->_verify_instance( $instance, $bits[1] ) ) {
			for ( $i = 0; $i < $count; $i++ ) {
				yield [
					'bits'     => $_bits = $this->get_bits(),
					'instance' => $this->get_verification_instance( $_bits[1] ),
				];
			}
		}
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
			// Timing attack mitigated.

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

		static $timer;
		static $pepper;

		if ( isset( $timer ) ) {
			$timer += $pepper;
		} else {
			// It's over ninethousand! And also a prime.
			$timer  = $this->is_64() ? time() * 9001 : \PHP_INT_MAX / 9001;
			$pepper = mt_rand( -$timer, $timer );
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
		set: {
			$bit = $_bit = 0;

			// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis -- goto loop.
			$_prime    = $_prime ?? 317539;
			$_boundary = 10000;

			$_time = time();

			// phpcs:disable, Generic.Formatting.MultipleStatementAlignment -- It'll be worse to read.
			$_i = $this->is_64() && $_time > $_boundary ? $_time : ( \PHP_INT_MAX - $_boundary ) / $_prime;
			$_i > 0 or $_i = ~$_i;
			$_i = (int) $_i;

			    $_i = $_i * $_prime
			and \is_int( $_i )
			and ( $_i + $_boundary ) < \PHP_INT_MAX // if this fails, there's a precision error in PHP.
			and $bit = $_bit = mt_rand( ~ $_i, $_i )
			and $bit & 1
			and $bit = $_bit++;
			// phpcs:enable, Generic.Formatting.MultipleStatementAlignment
		}

		// Hit 0 or is overflown on 32 bit. Retry.
		if ( 0 === $bit || \is_double( $bit ) ) {
			$_prime = array_rand( array_flip( [ 317539, 58171, 16417, 6997, 379, 109, 17 ] ) );
			goto set;
		}

		generate: {
			/**
			 * Count to create an irregular bit verification.
			 * This can jump multiple sequences while maintaining the previous.
			 * It traverses in three (actually two, but get_verification_instance makes it
			 * three) dimensions: up (positive), down (negative) and right (new sequence).
			 *
			 * Because it always moves based on arbitrary last input, it's timing attack secure.
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
	 * Generates static hash based on a unique ID.
	 * Prescrambles the input.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @param string $uid The unique ID for the hash.
	 *                    A good choice would be the page ID + concatentated blog name.
	 * @return string A hash from scrambled UID input.
	 */
	final public function _get_uid_hash( $uid ) {

		$a   = (string) $uid;
		$b   = strrev( $a );
		$len = \strlen( $a );
		$r   = '';

		for ( $i = 0; $i < $len; $i++ )
			$r .= \ord( $a[ $i ] ) . $b[ $i ];

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

		return $this->hash( "$uid\\$now_x\\$uid", 'auth' );
	}

	/**
	 * Generates salt from WordPress defined constants.
	 *
	 * Taken from WordPress core function `wp_salt()` and adjusted accordingly.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_salt/
	 * @since 1.0.0
	 * @since 2.6.1 Added an options salt, formed from static entries on the site.
	 *              Annoyingly, this affects staging on WP Engine unlike it previously didn't.
	 *
	 * @param string $scheme Authentication scheme, accepts 'instance', 'auth', 'secure_auth',
	 *                       'nonce', and 'options'.
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

		switch ( $scheme ) {
			case 'domain':
				// A combobulation of various static yet less unique values.
				$values = [
					'key'  => 'k' . (
						\get_option( 'initial_db_version' ) + 2219423
					) . md5( \get_site_option( 'siteurl' ) ) . '+++42===',
					'salt' => 's' . (
						\get_option( 'the_seo_framework_initial_db_version' ) + 2367569
					) . '+++69---',
				];
				break;

			case 'options':
				// A combobulation of various static yet unique values.
				$values = [
					'key'  => 'k' . ( \get_option( 'initial_db_version' ) + 1493641 ) . '+++42===',
					'salt' => 's' . md5( \dirname( \TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE ) )
						. \get_option( 'the_seo_framework_initial_db_version' ) . '+++69---',
				];
				break;

			case 'auth':
			case 'secure_auth':
			case 'logged_in':
			case 'nonce':
			case 'instance':
				if ( 'instance' === $scheme ) {
					$schemes = [ 'auth', 'secure_auth', 'logged_in', 'nonce' ];
					// 'instance' picks a random key. Store in other variable so we can cache this result.
					$_scheme = $schemes[ mt_rand( 0, \count( $schemes ) - 1 ) ];
				} else {
					// We reserve $scheme to cache 'instance' later. Create a copy.
					$_scheme = $scheme;
				}

				foreach ( [ 'key', 'salt' ] as $type ) {
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
				}
				break;
			default:
				\wp_die( 'Invalid scheme supplied for <code>' . __METHOD__ . '</code>.' );
		}

		return $cached_salts[ $scheme ] = "{$values['key']}{$values['salt']}";
	}

	/**
	 * Returns working hash type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The working hash type to be used within hash() functions.
	 */
	final public function get_hash_type() {

		static $type;

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
		static $network_mode;

		if ( isset( $network_mode ) )
			return $network_mode;

		if ( ! \is_multisite() )
			return $network_mode = false;

		$plugins = \get_site_option( 'active_sitewide_plugins' );

		return $network_mode = isset( $plugins[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
		// phpcs:enable
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

		if ( $this->_blocked_extension_file( $_instance, $bits[1] ) )
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

		if ( ! $this->are_options_valid() )
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
	 * @since 2.5.1 Now supports mixed cases in classnames.
	 * @since 2.5.2 Now supports mixed cases in paths again (oops). Props Carl D. Erling.
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
				$locations[ $namespace ] = $path;
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
	 * @since 2.6.0 1. Now supports branched path files.
	 *              2. Now uses `hrtime()` instead of `microtime()`.
	 *
	 * @param string $class The extension classname.
	 * @return bool False if file hasn't yet been included, otherwise true.
	 */
	final protected function autoload_extension_class( $class ) {

		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'tsf_extension_manager\\extension\\', 0 ) )
			return;

		if ( $this->_has_died() ) {
			$this->create_class_alias( $class );
			return false;
		}

		static $_timer;

		$_timer ??= hrtime( true );

		$_class = str_replace( 'tsf_extension_manager\\extension\\', '', $class );
		$_ns    = substr( $_class, 0, strpos( $_class, '\\' ) );

		$_path = $this->get_extension_autload_path( $_ns );

		if ( $_path ) {
			$_file = str_replace(
				[ "$_ns\\", '\\', '/', '_' ],
				[ '', \DIRECTORY_SEPARATOR, \DIRECTORY_SEPARATOR, '-' ],
				$_class
			);

			$this->get_verification_codes( $_instance, $bits );

			require "{$_path}{$_file}.class.php";
		} else {
			\tsf()->_doing_it_wrong( __METHOD__, 'Class <code>' . \esc_html( $class ) . '</code> has not been registered.' );

			// Prevent fatal errors.
			$this->create_class_alias( $class );
		}

		if ( isset( $_timer ) ) {
			// When the class extends, the last class in the stack will reach this first.
			// All classes before cannot reach this any more.
			$_t = ( hrtime( true ) - $_timer ) / 1e9;
			\The_SEO_Framework\_bootstrap_timer( $_t );
			\TSF_Extension_Manager\_bootstrap_timer( $_t );
			$_timer = null;
		}
	}

	/**
	 * Validates extensions option checksum.
	 *
	 * @since 1.0.0
	 *
	 * @param array $checksum The extensions checksum.
	 * @return int|bool Negative int on failure, true on success.
	 */
	final protected function validate_extensions_checksum( $checksum ) {

		// If the required keys aren't found, bail.
		if ( ! $this->has_required_array_keys( $checksum, [ 'hash', 'matches', 'type' ] ) ) {
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

		foreach ( Extensions::get( 'extensions_list' ) as $slug => $data )
			$order[ $slug ] = ( $last = $last + 10 );

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
		return $this->_verify_instance( $_instance, $bits[1] ) ? $this->get_subscription_status() : false;
	}

	/**
	 * Returns subscription status from local options.
	 *
	 * @since 1.0.0
	 * @since 2.6.2 Removed memoization.
	 *
	 * @return array Current subscription status.
	 */
	final protected function get_subscription_status() {
		return [
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
			return \TSF_EXTENSION_MANAGER_DIR_URL . "lib/fonts/$font";
		} else {
			return \TSF_EXTENSION_MANAGER_DIR_PATH . 'lib' . \DIRECTORY_SEPARATOR . 'fonts' . \DIRECTORY_SEPARATOR . $font;
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
			return \TSF_EXTENSION_MANAGER_DIR_URL . "lib/images/$image";
		} else {
			return \TSF_EXTENSION_MANAGER_DIR_PATH . 'lib' . \DIRECTORY_SEPARATOR . 'images' . \DIRECTORY_SEPARATOR . $image;
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
	 * Also prevents JS-regex execution.
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
	 * @param array $keys The desired keys, e.g. ['key','key2']
	 * @return array The $input array with only indexes from $keys.
	 */
	final public function filter_keys( $input, $keys ) {
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

		if ( ! \is_admin() || empty( $this->seo_extensions_menu_page_hook ) )
			return false;

		if ( $secure ) {
			return ( $GLOBALS['page_hook'] ?? null ) === $this->seo_extensions_menu_page_hook;
		} else {
			if ( \wp_doing_ajax() ) {
				return $this->ajax_is_tsf_extension_manager_page();
			} else {
				return ( $GLOBALS['page_hook'] ?? null ) === $this->seo_extensions_menu_page_hook
					|| ( $_GET['page'] ?? null ) === $this->seo_extensions_menu_page_hook; // phpcs:ignore, WordPress.Security.NonceVerification;
			}
		}
	}

	/**
	 * Determines if TSFEM AJAX has determined the correct page.
	 *
	 * @since 1.0.0
	 * @NOTE Warning: Only set after valid nonce verification pass.
	 *
	 * @param bool $set If true, it registers the AJAX page.
	 * @return bool True if set, false otherwise.
	 */
	protected function ajax_is_tsf_extension_manager_page( $set = false ) {

		static $memo = false;

		return $set ? $memo = true : $memo;
	}
}
