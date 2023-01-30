<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_DB_VERSION' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Load class overloading traits.
 *
 * @since 1.5.0
 */
\TSF_Extension_Manager\_load_trait( 'core/overload' );

/**
 * Require memory factory trait.
 *
 * @since 1.5.0
 */
\TSF_Extension_Manager\_load_trait( 'factory/memory' );

/**
 * @see EOF. Because of the trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_upgrader_class = function() {
	new Upgrader(); // phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape -- correct scope.
};

/**
 * Class TSF_Extension_Manager\Upgrader
 *
 * Upgrades plugin and extensions.
 *
 * @since 1.5.0
 * @access private
 * @uses trait \TSF_Extension_Manager\Construct_Core_Once_Interface
 * @see @package TSF_Extension_Manager\Overload
 * @uses trait \TSF_Extension_Manager\Memory
 * @see @package TSF_Extension_Manager\Factory
 */
final class Upgrader {
	use Construct_Core_Once_Interface,
		Memory;

	/**
	 * @since 1.5.0
	 * @property string The db revision option key.
	 */
	const O = 'tsfem_current_db_versions';

	/**
	 * @since 1.5.0
	 * @var \stdClass The upgrade container.
	 */
	private $upgrades;

	/**
	 * This value is reset upon critical upgrade for member 'core'.
	 *
	 * @since 1.5.0
	 * @var array The previous database revisions per member.
	 */
	private $previous_db_versions;

	/**
	 * @since 1.5.0
	 * @var array The upgraded database revisions per member.
	 */
	private $current_db_versions;

	/**
	 * @since 1.5.0
	 * @var array The currently active callbacks for member parsing.
	 */
	private $active_callbacks;

	/**
	 * Constructor.
	 *
	 * @uses trait \TSF_Extension_Manager\Memory
	 */
	private function construct() {
		$this->set_defaults();
		$this->increase_available_memory();

		// These are not available to extensions.
		\add_action( 'plugins_loaded', [ $this, '_load_critical_hook' ], 0 );
		\add_action( 'plugins_loaded', [ $this, '_parse_critical' ], 1 );

		\add_action( 'tsfem_extensions_initialized', [ $this, '_load_hooks' ], 10 );
		\add_action( 'tsfem_extensions_initialized', [ $this, '_parse' ], 11 );
	}

	/**
	 * Sets class properties to their default value.
	 *
	 * @since 1.5.0
	 */
	private function set_defaults() {
		$this->upgrades             = new \stdClass;
		$this->previous_db_versions = \get_option( static::O, [] );
		$this->current_db_versions  = $this->previous_db_versions;
		$this->active_callbacks     = [];
	}

	/**
	 * Gets a protected property value.
	 *
	 * @since 1.5.0
	 *
	 * @param string $what The property name to get.
	 * @return mixed
	 */
	public function get( $what ) {
		switch ( $what ) :
			case 'previous_db_versions':
			case 'current_db_versions':
				$val = $this->{$what};
				break;

			default:
				$val = '';
		endswitch;

		return $val;
	}

	/**
	 * Returns the previous database version of $member.
	 *
	 * @since 1.5.0
	 * @since 1.5.1 Now no longer always returns '0'.
	 *
	 * @param string $member The member of the upgrade, e.g. the extension slug.
	 * @return string
	 */
	public function get_previous_version( $member ) {
		return $this->previous_db_versions[ $member ] ?? '0';
	}

	/**
	 * Returns the current database version of $member.
	 *
	 * @since 1.5.0
	 * @since 1.5.1 Now no longer always returns '0'.
	 *
	 * @param string $member The member of the upgrade, e.g. the extension slug.
	 * @return string
	 */
	public function get_current_version( $member ) {
		return $this->current_db_versions[ $member ] ?? '0';
	}

	/**
	 * Registers upgrade. This method is the core factory method.
	 *
	 * We set the member as a base key so that the member can upgrade completely
	 * before any others. This helps with debugging and frees up memory by
	 * eliminating members when completed.
	 * Because the upgrader is loaded very early, the order of member upgrades
	 * shouldn't interfere flow, ever. Let defensive and state programming occur instead.
	 *
	 * @since 1.5.0
	 * @since 2.5.3 Now supports multiple callbacks per version. Order of registration is important.
	 * @factory
	 *
	 * @param string   $member   The member of the upgrade, e.g. the extension slug.
	 * @param string   $version  TSFEM's database version.
	 * @param callable $callback The callback to perform for the upgrade.
	 */
	public function _register_upgrade( $member, $version, $callback ) {

		$c = &$this->_upgrade_collector();

		// phpcs:ignore, WordPress.WhiteSpace.OperatorSpacing
		isset( $c->{$member} ) or $c->{$member} = new \stdClass;

		$c->{$member}->{$version}[] = $callback;
	}

	/**
	 * Collects upgrades.
	 *
	 * @since 1.5.0
	 * @collector
	 *
	 * @return $this->upgrades
	 */
	private function &_upgrade_collector() {
		return $this->upgrades;
	}

	/**
	 * Loads critical upgrade hook.
	 *
	 * @since 1.5.0
	 */
	public function _load_critical_hook() {
		$this->do_critical_upgrade();
	}

	/**
	 * Loads upgrade hooks after extensions are initialized.
	 *
	 * @since 1.5.0
	 */
	public function _load_hooks() {

		$ms = \is_multisite();

		if ( \is_admin() ) {
			$this->do_admin_upgrade();
			$ms and $this->do_network_admin_upgrade();
		}

		$this->do_always_upgrade();
		$ms and $this->do_network_always_upgrade();
	}

	/**
	 * Does critical upgrade action, regardless of website state and environment.
	 * This only runs before the Extension Manager plugin is loaded.
	 *
	 * @since 1.5.0
	 */
	private function do_critical_upgrade() {
		/**
		 * @since 1.5.0
		 * @param Upgrader $upgrader
		 */
		\do_action_ref_array( 'tsfem_prepare_critical_upgrade', [ $this ] );
	}

	/**
	 * Does in-admin upgrade.
	 *
	 * @since 1.5.0
	 */
	private function do_admin_upgrade() {
		/**
		 * @since 1.5.0
		 * @param Upgrader $upgrader
		 */
		\do_action_ref_array( 'tsfem_prepare_admin_upgrade', [ $this ] );
	}

	/**
	 * Does network in-admin upgrade.
	 *
	 * @since 1.5.0
	 */
	private function do_network_admin_upgrade() {
		/**
		 * @since 1.5.0
		 * @param Upgrader $upgrader
		 */
		\do_action_ref_array( 'tsfem_prepare_network_admin_upgrade', [ $this ] );
	}

	/**
	 * Does upgrade.
	 *
	 * @since 1.5.0
	 */
	private function do_always_upgrade() {
		/**
		 * @since 1.5.0
		 * @param Upgrader $upgrader
		 */
		\do_action_ref_array( 'tsfem_prepare_always_upgrade', [ $this ] );
	}

	/**
	 * Does network upgrade.
	 *
	 * @since 1.5.0
	 */
	private function do_network_always_upgrade() {
		/**
		 * @since 1.5.0
		 * @param Upgrader $upgrader
		 */
		\do_action_ref_array( 'tsfem_prepare_network_always_upgrade', [ $this ] );
	}

	/**
	 * Parses critical upgrades before the plugin is loaded.
	 * Resets class variables.
	 *
	 * @since 1.5.0
	 */
	public function _parse_critical() {
		$this->_parse();
		$this->set_defaults();
	}

	/**
	 * Parses upgrades.
	 *
	 * @since 1.5.0
	 */
	public function _parse() {

		$upgrades = $this->upgrades;

		foreach ( $upgrades as $member => $version ) {
			$upgrades->{$member} = (array) $upgrades->{$member};
			// Sort by version.
			ksort( $upgrades->{$member} );
		}

		foreach ( $this->yield_runs( $upgrades ) as $member => $args ) {
			if ( $args['success'] ) {
				$updated = $this->update_member( $member, $args['version'] );
			} else {
				// TODO log error. Wait for logger factory/framework.
				// REASON: Undefined callback or callback returned false for any reason.
				// Continue anyway?
				break;
			}

			if ( ! $updated ) {
				// TODO log error. Wait for logger factory/framework.
				// REASON: Database error while trying to store data.
				break;
			}
			if ( ! $this->can_do_upgrade() ) {
				// TODO log error... count and fire if > 3? Wait for logger factory/framework.
				// REASON: Out of memory.
				break;
			}
		}
	}

	/**
	 * Iterates over upgrades and yields value.
	 * Doesn't yield when the input is empty.
	 *
	 * @since 1.5.0
	 * @since 2.5.3 Now supports multiple callbacks per version.
	 *
	 * @param \stdClass $upgrade The upgrade iterator object.
	 * @yield array { $member => $version }
	 */
	private function yield_runs( $upgrade ) {
		foreach ( $upgrade as $member => $versions )
			foreach ( $versions as $version => $callbacks )
				foreach ( $callbacks as $callback )
					yield $member => $this->do_upgrade_cb( (string) $version, $callback );
	}

	/**
	 * Performs the upgrade.
	 *
	 * @since 1.5.0
	 *
	 * @param string   $version  The version to upgrade to.
	 * @param callable $callback The upgrader callback.
	 * @return array {
	 *   'success' => bool $success
	 *   'version' => string $version
	 * }
	 */
	private function do_upgrade_cb( $version, $callback ) {
		return [
			'success' => (bool) \call_user_func_array( $callback, [ $version ] ),
			'version' => $version,
		];
	}

	/**
	 * Updates member to updated version.
	 *
	 * @since 1.5.0
	 *
	 * @param string $member  The member that has updated.
	 * @param string $version The new version number the member is at.
	 * @return bool True on success, false on failure.
	 */
	private function update_member( $member, $version ) {

		$_preset = $this->current_db_versions;

		$this->current_db_versions[ $member ] = (string) $version;

		$updated = (bool) \update_option( static::O, $this->current_db_versions );

		$updated or $this->current_db_versions = $_preset;

		return $updated;
	}

	/**
	 * Determines if we can do the next upgrade.
	 * When 2 MiB is free, an upgrade can occur.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if possible, false othewise.
	 */
	private function can_do_upgrade() {
		return $this->has_free_memory( 2 * MB_IN_BYTES );
	}
}

// Loads class.
$_load_upgrader_class();
