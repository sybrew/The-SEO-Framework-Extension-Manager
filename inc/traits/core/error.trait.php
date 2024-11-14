<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager\Traits;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	do_dismissible_notice,
};

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

// @TODO create error legend/index for codes.

/**
 * Holds Error handling functionality.
 *
 * @since 1.0.0
 * @access private
 */
trait Error {

	/**
	 * @since 1.0.0
	 * @var string The POST request status code option name.
	 */
	protected $error_notice_option;

	/**
	 * Initializes the UI traits.
	 *
	 * @since 1.0.0
	 */
	final protected function init_errors() {

		$this->error_notice_option or \tsf()->_doing_it_wrong( __METHOD__, 'You need to specify property <code>error_notice_option</code>' );

		// Can this be applied in-post too, when $this->error_notice_option is known? Otherwise, supply parameter?
		\add_action( 'tsfem_notices', [ $this, '_do_error_notices' ] );
	}

	/**
	 * Outputs notices. If any, and only on the Extension manager pages.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Now outputs multiple notices.
	 * @uses $this->error_notice_option
	 * @access private
	 */
	final public function _do_error_notices() {

		$options = \get_option( $this->error_notice_option, false );

		if ( ! $options ) return;

		$notices = $this->get_error_notices( $options );

		if ( ! $notices ) {
			$this->unset_error_notice_option();
			return;
		}

		foreach ( $notices as $notice )
			do_dismissible_notice(
				$notice['message'],
				[
					'type'   => $notice['type'],
					'escape' => false,
					'inline' => true,
				]
			);

		$this->unset_error_notice_option();
	}

	/**
	 * Sets notices option, only does so when in the admin area.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 : 1. Now stores multiple notices.
	 *                2. Added a new parameter to clear previous notices.
	 * @since 1.5.1 Added an exact-match check to prevent duplicated entries.
	 *
	 * @param array $notice    The notice. : {
	 *    0 => int    key,
	 *    1 => string additional message
	 * }
	 * @param bool  $clear_old When true, it removes all previous notices.
	 * @return void
	 */
	final protected function set_error_notice( $notice = [], $clear_old = false ) {

		if ( ! \is_admin() || ! $this->error_notice_option )
			return;

		$notices = ( $clear_old ? null : \get_option( $this->error_notice_option ) ) ?: [];

		if ( ! $notices ) {
			$notices = [ $notice ];
		} else {
			// This checks if the notice is already stored.
			//# This prevents adding timestamps preemptively in the future.
			// We could form a timestamp collection per notice, separately.
			//# But, that would cause performance issues.
			if ( \in_array( $notice, $notices, true ) ) {
				// We already have the notice stored in cache.
				return;
			} else {
				array_push( $notices, $notice );
			}
		}

		\update_option( $this->error_notice_option, $notices, 'yes' );
	}

	/**
	 * Removes notices option.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 1. No longer deletes option, but instead overwrites it.
	 *              2. Now removes the option from autoload.
	 * @since 1.5.0 Renamed from `unset_error_notice()`
	 */
	final protected function unset_error_notice_option() {
		$this->error_notice_option and \update_option( $this->error_notice_option, null, 'no' );
	}
}
