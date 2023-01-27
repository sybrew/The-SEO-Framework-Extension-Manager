<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * @since 1.1.0
	 * @var bool Whether the options have been killed.
	 */
	private $killed_options = false;

	/**
	 * @since 2.6.1
	 * @var array The options cache.
	 */
	private $options = [];

	/**
	 * Resets the options cache.
	 *
	 * @since 2.6.1
	 */
	private function clear_options_cache() {
		$this->options = [];
	}

	/**
	 * Returns TSF Extension Manager options array.
	 *
	 * @since 1.0.0
	 *
	 * @return array TSF Extension Manager options.
	 */
	final protected function get_all_options() {

		if ( $this->killed_options )
			return [];

		if ( $this->options )
			return $this->options;

		\remove_all_filters( 'pre_option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS );
		\remove_all_filters( 'default_option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS );
		\remove_all_filters( 'option_' . TSF_EXTENSION_MANAGER_SITE_OPTIONS );

		return $this->options = (array) \get_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] );
	}

	/**
	 * Fetches TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 * @since 2.6.1 1. Removed memoization.
	 *              2. Second parameter now tells to reset cache.
	 *
	 * @param string $option The Option name.
	 * @return ?mixed The option value if exists. Otherwise null.
	 */
	final protected function get_option( $option ) {
		return $this->get_all_options()[ $option ] ?? null;
	}

	/**
	 * Updates TSF Extension Manager option.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Option reversal has been forwarded to option verification,
	 *              so the verification key no longers gets out of sync.
	 *
	 * @param string $option The option name.
	 * @param mixed  $value The option value.
	 * @param bool   $kill Whether to kill the plugin on invalid instance.
	 * @return bool True on success or the option is unchanged, false on failure.
	 */
	final protected function update_option( $option, $value, $kill = false ) {

		if ( ! $option || $this->killed_options )
			return false;

		$existing_options = $this->get_all_options();
		$options          = $existing_options;

		$options[ $option ] = $value;

		// If options are unchanged, return true.
		if ( $options === $existing_options )
			return true;

		$this->initialize_option_update_instance();

		// TODO add Ajax response? "Enable account -> open new tab, disable account in it -> load feed in first tab."
		if ( empty( $options['_instance'] ) ) {
			\wp_die( 'Error 7008: Supply an instance key before updating other options.' );
			return false;
		}

		$success          = \update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );
		$instance_updated = $success && $this->set_options_instance( $options, $options['_instance'] );

		if ( ! $instance_updated || ! $this->verify_option_update_instance( $kill ) ) {
			$this->set_error_notice( [ $instance_updated ? 6001 : 6002 => '' ] );

			// Revert on failure.
			if ( ! $kill )
				\update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $existing_options );
		}

		$this->clear_options_cache();

		return $instance_updated;
	}

	/**
	 * Updates multiple TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 * @since 2.6.1 Now works without crashing the instance, so it can be used to set the instance.
	 *
	 * @param array $options : {
	 *    $option_name => $value,
	 *    ...
	 * }
	 * @param bool  $kill Whether to kill the plugin on invalid instance.
	 * @return bool True on success, false on failure or when options haven't changed.
	 */
	final protected function update_option_multi( $options = [], $kill = false ) {

		if ( ! $options || $this->killed_options )
			return false;

		if ( $this->killed_options )
			return false;

		$existing_options = $this->get_all_options();
		$options          = array_merge( $existing_options, $options );

		// If options are unchanged, return true.
		if ( $options === $existing_options )
			return true;

		$this->initialize_option_update_instance();

		if ( empty( $options['_instance'] ) ) {
			\wp_die( 'Error 7108: Supply an instance key before updating other options.' );
			return false;
		}

		$success          = \update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );
		$instance_updated = $success && $this->set_options_instance( $options, $options['_instance'] );

		if ( ! $instance_updated || ! $this->verify_option_update_instance( $kill ) ) {
			$this->set_error_notice( [ $instance_updated ? 7101 : 7102 => '' ] );

			// Revert on failure.
			if ( ! $kill )
				\update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $existing_options );
		}

		$this->clear_options_cache();

		return $success && $instance_updated;
	}

	/**
	 * Deletes TSF Extension Manager options.
	 *
	 * @since 1.0.0
	 * @since 2.6.1 1. Removed memoization.
	 *              2. Second parameter now tells to reset cache.
	 *
	 * @param string|string[] $option The option name(s).
	 * @param bool            $kill   Whether to kill the plugin on invalid instance.
	 * @return ?mixed The option value if exists. Otherwise null.
	 */
	final protected function delete_option( $option, $kill = false ) {

		if ( ! $option || $this->killed_options )
			return false;

		$existing_options = $this->get_all_options();
		$options          = array_diff_key( $existing_options, array_flip( (array) $option ) );

		// If options didn't change, return true.
		if ( $options === $existing_options )
			return true;

		$this->initialize_option_update_instance();

		if ( empty( $options['_instance'] ) ) {
			\wp_die( 'Error 7208: Supply an instance key before updating other options.' );
			return false;
		}

		$success          = \update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $options );
		$instance_updated = $success && $this->set_options_instance( $options, $options['_instance'] );

		if ( ! $instance_updated || ! $this->verify_option_update_instance( $kill ) ) {
			$this->set_error_notice( [ $instance_updated ? 7101 : 7102 => '' ] );

			// Revert on failure.
			if ( ! $kill )
				\update_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS, $existing_options );
		}

		$this->clear_options_cache();

		return $success && $instance_updated;
	}

	/**
	 * Binds options to an unique hash and saves it in a comparison option.
	 * This prevents users from altering the options from outside this plugin.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Improved hashing algorithm.
	 *
	 * @param array  $options  The options to hash.
	 * @param string $instance The instance key, needs to be supplied on plugin activation.
	 * @return bool True on success, false on failure.
	 */
	final protected function set_options_instance( $options, $instance = '' ) {

		if ( empty( $options['_instance'] ) )
			return false;

		$hash = $this->hash_options( $options );

		if ( \strlen( $hash ) ) {
			$instance = $instance ?: $this->get_option( '_instance' );
			$updated  = \strlen( $instance ) && \update_option( "tsfem_i_$instance", $hash );

			if ( ! $updated ) {
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
	 * Hashes the options.
	 *
	 * @since 2.6.1
	 *
	 * @param mixed $options The option data to compare hash with.
	 * @return bool True when hash passes, false on failure.
	 */
	protected function hash_options( $options ) {
		switch ( $options['_instance_version'] ?? '1.0' ) {
			case '2.0':
				// phpcs:ignore -- No objects are inserted, nor is this ever unserialized.
				return $this->hash( \serialize( $options ), 'options' );

			default:
			case '1.0':
				// phpcs:ignore -- No objects are inserted, nor is this ever unserialized.
				return $this->hash( \serialize( $options ), 'auth' );
		}
	}

	/**
	 * Initializes option update instance.
	 * Requires the instance to be closed.
	 *
	 * @since 1.0.0
	 * @see $this->verify_option_update_instance().
	 */
	final protected function initialize_option_update_instance() {

		$this->get_verification_codes( $_instance, $bits );
		SecureOption::initialize( 'update_option', $_instance, $bits );

		$this->get_verification_codes( $_instance, $bits );
		SecureOption::set_update_instance( $_instance, $bits );
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

		$verified = SecureOption::verified_option_update();

		if ( $kill && ! $verified )
			$this->kill_options();

		SecureOption::reset();

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

		$instance = $this->get_option( '_instance' );

		// Don't record whether this is a success. It's lame if it fails, but there's no harm other than an autoloaded string.
		\delete_option( "tsfem_i_$instance" );

		$this->killed_options = \delete_option( TSF_EXTENSION_MANAGER_SITE_OPTIONS );
		$this->clear_options_cache();

		return $this->killed_options;
	}
}
