<?php
/**
 * @package TSF_Extension_Manager\Traits
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
 * Holds option functions for class TSF_Extension_Manager\Core.
 *
 * All options are irreversibly cached per request. Only to be changed upon POST.
 *
 * @since 1.0.0
 * @access private
 */
trait Options {

	/**
	 * Determines whether the options have been killed.
	 *
	 * @since 1.1.0
	 *
	 * @var bool $killed_options
	 */
	private $killed_options = false;

	/**
	 * Returns TSF Extension Manager options array.
	 *
	 * @since 1.0.0
	 * @staticvar array $cache The cached options.
	 *
	 * @return array TSF Extension Manager options.
	 */
	final protected function get_all_options() {

		if ( $this->killed_options )
			return [];

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		\remove_all_filters( 'pre_option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS );
		\remove_all_filters( 'default_option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS );
		\remove_all_filters( 'option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS );

		return $cache = (array) \get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] );
	}

	/**
	 * Fetches TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The Option name.
	 * @param mixed $default The fallback value if the option doesn't exist.
	 * @return mixed The option value if exists. Otherwise $default.
	 */
	final protected function get_option( $option, $default = null ) {

		if ( ! $option )
			return null;

		if ( $this->killed_options )
			return [];

		static $options_cache = [];

		if ( isset( $options_cache[ $option ] ) )
			return $options_cache[ $option ];

		$options = $this->get_all_options();

		return $options_cache[ $option ] = isset( $options[ $option ] ) ? $options[ $option ] : $default;
	}

	/**
	 * Determines whether update_option has already run. Always returns false on
	 * first call. Always returns true on second or later call.
	 *
	 * @since 1.0.0
	 * @staticvar $run Whether update_option has run.
	 *
	 * @return bool True if run, false otherwise.
	 */
	final protected function has_run_update_option() {

		static $run = false;

		return \TSF_Extension_Manager\is_done( $run );
	}

	/**
	 * Updates TSF Extension Manager option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed $value The option value.
	 * @param string $type The option update type, accepts 'instance' and 'regular'.
	 * @param bool $kill Whether to kill the plugin on invalid instance.
	 * @return bool True on success or the option is unchanged, false on failure.
	 */
	final protected function update_option( $option, $value, $type = 'instance', $kill = false ) {

		if ( ! $option )
			return false;

		if ( $this->killed_options )
			return false;

		$_options = $this->get_all_options();

		//* Cache current options from loop. This is used for activation where _instance needs to be used.
		static $options = [];

		if ( empty( $options ) )
			$options = $_options;

		//* If option is unchanged, return true.
		if ( isset( $options[ $option ] ) && $value === $options[ $option ] )
			return true;

		$options[ $option ] = $value;

		//* Set option update tick to prevent collision.
		$this->has_run_update_option();

		$this->initialize_option_update_instance( $type );

		if ( empty( $options['_instance'] ) && '_instance' !== $option )
			\wp_die( 'Error 7008: Supply an instance key before updating other options.' );

		$success = \update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );

		$key = '_instance' === $option ? $value : $options['_instance'];
		$this->set_options_instance( $options, $key );

		if ( false === $this->verify_option_update_instance( $kill ) ) {
			$this->set_error_notice( [ 7001 => '' ] );

			//* Revert option.
			if ( false === $kill )
				\update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $_options );

			return false;
		}

		return $success;
	}

	/**
	 * Updates multiple TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options : {
	 *    $option_name => $value,
	 *    ...
	 * }
	 * @param string $type The option update type, accepts 'instance' and 'regular'.
	 * @param bool $kill Whether to kill the plugin on invalid instance.
	 * @return bool True on success, false on failure or when options haven't changed.
	 */
	final protected function update_option_multi( array $options = [], $type = 'instance', $kill = false ) {

		static $run = false;

		if ( empty( $options ) )
			return false;

		if ( $this->killed_options )
			return false;

		$_options = $this->get_all_options();

		//* If options are unchanged, return true.
		if ( serialize( $options ) === serialize( $_options ) )
			return true;

		if ( $run ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You may only run this method once per request. Doing so multiple times will result in data loss.' );
			\wp_die();
		}

		if ( $this->has_run_update_option() ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, \esc_html( __CLASS__ . '::update_option() has already run in the current request. Running this function will lead to data loss.' ) );
			\wp_die();
		}

		//* This won't fire the filter 'wp_parse_str'. As $options requires to be an array.
		$options = \wp_parse_args( $options, $_options );
		$run = true;

		$this->initialize_option_update_instance( $type );

		if ( empty( $options['_instance'] ) )
			\wp_die( 'Error 7009: Supply an instance key before updating other options.' );

		$this->set_options_instance( $options, $options['_instance'] );

		$success = \update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );

		if ( false === $this->verify_option_update_instance( $kill ) ) {
			$this->set_error_notice( [ 7002 => '' ] );

			//* Revert option.
			if ( false === $kill )
				\update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $_options );

			return false;
		}

		return $success;
	}

	/**
	 * Returns verification instance option.
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool The hashed option. False if non-existent.
	 */
	final protected function get_options_instance() {
		return \get_option( 'tsfem_i_' . $this->get_option( '_instance' ) );
	}

	/**
	 * Updates verification instance option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value The option value.
	 * @param string $key Optional. The options key. Must be supplied when activating account.
	 * @return bool True on success, false on failure.
	 */
	final protected function update_options_instance( $value, $key = '' ) {

		$key = $key ? $key : $this->get_option( '_instance' );

		return \update_option( 'tsfem_i_' . $key, $value );
	}

	/**
	 * Deletes option instance on account deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance Optional. When supplied it will delete entered instance.
	 * @return bool
	 */
	final protected function delete_options_instance( $instance = '' ) {

		$instance = $instance ?: $this->get_option( '_instance' );

		\delete_option( 'tsfem_i_' . $instance );

		return true;
	}

	/**
	 * Binds options to an unique hash and saves it in a comparison option.
	 * This prevents users from altering the options from outside this plugin.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Improved hashing algorithm.
	 *
	 * @param array $options The options to hash.
	 * @param string $key The instance key, needs to be supplied on plugin activation.
	 * @return bool True on success, false on failure.
	 */
	final protected function set_options_instance( $options, $key = '' ) {

		if ( empty( $options['_instance'] ) )
			return false;

		$hash = $this->hash( serialize( $options ), 'auth' );

		if ( $hash ) {
			$update = $this->update_options_instance( $hash, $key );

			if ( false === $update ) {
				$this->set_error_notice( [ 7001 => '' ] );
				return false;
			}
			return true;
		} else {
			$this->set_error_notice( [ 7002 => '' ] );
			return false;
		}
	}


	/**
	 * Verifies options hash.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Expanded hashing algorithm.
	 * @uses PHP 5.6 hash_equals : WordPress core has compat.
	 *
	 * @param string $data The data to compare hash with.
	 * @return bool True when hash passes, false on failure.
	 */
	final public function verify_options_hash( $data ) {
		return hash_equals( $this->hash( $data, 'auth' ), (string) $this->get_options_instance() );
	}

	/**
	 * Initializes option update instance.
	 * Requires the instance to be closed.
	 *
	 * @since 1.0.0
	 * @see $this->verify_option_update_instance().
	 *
	 * @param string $type What type of update this is, accepts 'instance' and 'regular'.
	 */
	final protected function initialize_option_update_instance( $type = 'regular' ) {

		if ( 'instance' === $type ) {
			$type = 'update_option_instance';
		} elseif ( 'regular' === $type ) {
			$type = 'update_option';
		}

		$this->get_verification_codes( $_instance, $bits );
		\TSF_Extension_Manager\SecureOption::initialize( $type, $_instance, $bits );

		$this->get_verification_codes( $_instance, $bits );
		\TSF_Extension_Manager\SecureOption::set_update_instance( $_instance, $bits );

	}

	/**
	 * Verifies if update went as expected.
	 * Deletes plugin options if data has been adjusted.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $kill Whether to kill plugin options.
	 * @return bool True on success, false on failure.
	 */
	final protected function verify_option_update_instance( $kill = false ) {

		if ( $this->_has_died() )
			return false;

		$verified = \TSF_Extension_Manager\SecureOption::verified_option_update();

		if ( $kill && false === $verified )
			$this->kill_options();

		\TSF_Extension_Manager\SecureOption::reset();

		return $verified;
	}

	/**
	 * Deletes all plugin options when an options breach has been spotted.
	 *
	 * @since 1.0.0
	 * @uses $this->killed_options
	 *
	 * @return bool True on success, false on failure.
	 */
	final protected function kill_options() {

		$success = [];
		$success[] = $this->delete_options_instance();
		$success[] = \delete_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS );

		$this->killed_options = ! in_array( false, $success, true );

		return $this->killed_options;
	}
}
