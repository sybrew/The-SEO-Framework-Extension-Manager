<?php
/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager_Activation
 *
 * Holds plugin activation functions.
 *
 * @since 1.0.0
 */
class TSF_Extension_Manager_Activation extends TSF_Extension_Manager_Core {

	/**
	 * Constructor. Loads parent constructor and initializes actions.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Determines whether the plugin's use has been verified.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the plugin is connected to the API handler.
	 */
	protected function is_plugin_connected() {
	//	return true;
		return false;
	}

	/**
	 * Determines subscription status.
	 *
	 * @since 1.0.0
	 *
	 * @return array Current subscription status.
	 */
	protected function get_subscription_status() {
		return array( 'active', 'no-sub' );
		return array( 'active', 'used-key' );
		return array( 'active', 'account' );
		return array( 'active', 'expires-soon' );
		return array( 'inactive', 'cancelled' );
		return array( 'inactive', 'suspended' );
	}

}
