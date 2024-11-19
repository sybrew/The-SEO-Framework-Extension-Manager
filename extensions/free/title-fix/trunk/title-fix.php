<?php
/**
 * @package TSF_Extension_Manager\Extension\Title_Fix
 */

namespace TSF_Extension_Manager\Extension\Title_Fix;

/**
 * Extension Name: Title Fix
 * Extension URI: https://theseoframework.com/extensions/title-fix/
 * Extension Description: The Title Fix extension makes sure your title output is as configured. Even if your theme is doing it wrong.
 * Extension Version: 1.3.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Title Fix extension for The SEO Framework
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
 * Tell this extension is active.
 *
 * @since 1.0.2
 * @todo deprecate, use `TSFEM_E_TITLE_FIX_VERSION` instead.
 */
\define( 'TSFEM_E_TITLE_FIX', true );

/**
 * The Title Fix extension version.
 *
 * @since 1.0.2
 */
\define( 'TSFEM_E_TITLE_FIX_VERSION', '1.3.0' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.3.0
 */
\define( 'TSFEM_E_TITLE_FIX_BASE_FILE', __FILE__ );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.3.0
 */
\define( 'TSFEM_E_TITLE_FIX_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( \TSFEM_E_TITLE_FIX_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.3.0
 */
\define( 'TSFEM_E_TITLE_FIX_PATH_CLASS', \TSFEM_E_TITLE_FIX_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'classes' . \DIRECTORY_SEPARATOR );

/**
 * Verify integrity and sets up autoloader.
 *
 * @since 1.3.0
 */
if ( ! \tsfem()->_init_early_extension_autoloader( \TSFEM_E_TITLE_FIX_PATH_CLASS, 'Title_Fix', $_instance, $bits ) )
	return;

// Don't load if the old WordPress.org version is active.
// phpcs:ignore, TSF.Performance.Functions.PHP -- required, memoized.
if ( class_exists( 'The_SEO_Framework_Title_Fix', false ) )
	return;

// Backward compatibility, set only if class The_SEO_Framework_Title_Fix doesn't exist.
\define( 'THE_SEO_FRAMEWORK_TITLE_FIX', true );

if ( ! \is_admin() )
	new Front;
