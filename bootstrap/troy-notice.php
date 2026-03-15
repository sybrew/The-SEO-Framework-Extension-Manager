<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Clear the Troy migration notice once Troy Client is installed.
if ( \defined( 'Troy\Client\ABSPATH' ) )
	\add_action( 'admin_init', __NAMESPACE__ . '\\_clear_troy_migration_notice' );

/**
 * Clears the Troy migration notice.
 *
 * Once Troy Client is installed, the migration notice is no longer needed.
 *
 * @hook admin_init 10
 * @since 2.7.3
 * @access private
 */
function _clear_troy_migration_notice() {

	if ( ! \defined( 'TSF_EXTENSION_MANAGER_USE_MODERN_TSF' ) || ! \TSF_EXTENSION_MANAGER_USE_MODERN_TSF )
		return;

	\The_SEO_Framework\Admin\Notice\Persistent::clear_notice( 'tsfem-troy-migration' );
}
