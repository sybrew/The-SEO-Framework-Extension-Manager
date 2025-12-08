<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus
 */

namespace TSF_Extension_Manager\Extension\Focus;

/**
 * Extension Name: Focus
 * Extension URI: https://theseoframework.com/extensions/focus/
 * Extension Description: The Focus extension guides you through the process of writing targeted content that ranks with focus keywords, inflections, and synonyms.
 * Extension Version: 1.6.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * The extension version.
 *
 * @since 1.0.0
 * NOTE: The presence does NOT guarantee the extension is loaded!!!
 */
\define( 'TSFEM_E_FOCUS_VERSION', '1.6.0' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_FOCUS_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_FOCUS_DIR_URL', \TSF_Extension_Manager\extension_dir_url( \TSFEM_E_FOCUS_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_FOCUS_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( \TSFEM_E_FOCUS_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_FOCUS_PATH_CLASS', \TSFEM_E_FOCUS_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'classes' . \DIRECTORY_SEPARATOR );

/**
 * Verify integrity and sets up autoloader.
 *
 * @since 1.0.0
 */
if ( ! \tsfem()->_init_early_extension_autoloader( \TSFEM_E_FOCUS_PATH_CLASS, 'Focus', $_instance, $bits ) )
	return;

if ( \is_admin() )
	new Admin;
