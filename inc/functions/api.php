<?php
/**
 * @package TSF_Extension_Manager
 */

namespace {
	defined( 'TSF_EXTENSION_MANAGER_DIR_PATH' ) or die;
}

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

namespace {
	/**
	 * Returns the class from cache.
	 *
	 * This is the recommended way of calling the class, if needed.
	 * Call this after action 'init' priority 0 otherwise it will kill the plugin,
	 * or even other plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return null|object The plugin class object.
	 */
	function tsf_extension_manager() {
		return \TSF_Extension_Manager\_init_tsf_extension_manager();
	}
}

namespace TSF_Extension_Manager {
	/**
	 * Returns the minimum role required to adjust and access settings.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return string The minimum required capability for extension installation.
	 */
	function can_do_settings() {

		static $cache = null;

		return isset( $cache ) ? $cache : $cache = \current_user_can( 'manage_options' );
	}
}
