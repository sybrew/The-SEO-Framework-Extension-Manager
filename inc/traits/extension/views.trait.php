<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * copyright (C) 2022-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds View functionality.
 *
 * @since 2.6.0
 * @access private
 * @uses trait TSF_Extension_Manager\Extension_Options
 * @see TSF_Extension_Manager\Traits\Extension_Options
 */
trait Extension_Views {

	/**
	 * @since 2.6.0
	 * @var string The view location base.
	 */
	protected $view_location_base;

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 2.6.0
	 *
	 * @param string $view   The file name.
	 * @param array  $__args The arguments to be supplied within the file name.
	 *                       Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, $__args = [] ) {

		foreach ( $__args as $__k => $__v ) $$__k = $__v;
		unset( $__k, $__v, $__args );

		// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- forwarded to include...
		$_secret = $this->create_view_secret( uniqid( '', true ) );

		include $this->_get_view_location( $view );
	}

	/**
	 * Stores and returns view secret.
	 *
	 * This is not cryptographically secure, but it's enough to fend others off including our files where they shouldn't.
	 * Our view-files have a certain expectation of inputs to meet. If they don't meet that, we could expose our users to security issues.
	 * We could not measure any meaningful performance impact by using this (0.02% of 54x get_view() runtime).
	 *
	 * @since 2.6.0
	 *
	 * @param string|null $value The secret.
	 * @return string|null The stored secret.
	 */
	protected function create_view_secret( $value = null ) {
		// Use a unique key that's shared accross all trait's instances.
		return \The_SEO_Framework\umemo( __METHOD__, $value );
	}

	/**
	 * Verifies view secret.
	 *
	 * This can be bypassed unless we extract this method from `$this` by mimicking
	 * the functionality. However, the purpose is that `$this` cannot get leaked via
	 * a hypothetical (opcode) file cache vulnerability, which is served well here.
	 * The files can still get accessed through the wilfully cunning, though won't
	 * provide any useful integrity violation.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $value The secret.
	 * @return bool
	 */
	public function _verify_include_secret( $value ) {
		return isset( $value ) && $this->create_view_secret() === $value;
	}

	/**
	 * Gets view location.
	 *
	 * @since 2.6.0
	 * @access private
	 * @TODO add path traversal mitigation via realpath()?
	 *    -> $file must always be dev-supplied, never user-.
	 *
	 * @param string $file The file name.
	 * @return string The view location.
	 */
	public function _get_view_location( $file ) {
		return "{$this->view_location_base}$file.php";
	}
}
