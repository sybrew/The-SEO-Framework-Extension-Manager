<?php
/**
 * @package TSF_Extension_Manager/Bootstrap
 */

//* @NOTE $__file should be defined as the plugin base file.
defined( 'TSF_EXTENSION_MANAGER_DB_VERSION' ) and isset( $__file ) or die;

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

/**
 * The plugin file, absolute unix path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE', $__file );

/**
 * The plugin map URL. Used for calling browser files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_URL', \plugin_dir_url( $__file ) );

/**
 * The plugin map absolute path. Used for calling php files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH', dirname( $__file ) . DIRECTORY_SEPARATOR );

/**
 * The plugin class map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_CLASS', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * The plugin class map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_TRAIT', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'traits' . DIRECTORY_SEPARATOR );

/**
 * The plugin function map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR );

/**
 * The plugin function map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_COMPAT', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'compat' . DIRECTORY_SEPARATOR );

/**
 * The plugin extensions base path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSIONS_PATH', TSF_EXTENSION_MANAGER_DIR_PATH . 'extensions' . DIRECTORY_SEPARATOR );

/**
 * The plugin options base name.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_SITE_OPTIONS', 'tsf-extension-manager-settings' );

/**
 * The extension options base name.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS', 'tsf-extension-manager-extension-settings' );

/**
 * The extension options base name.
 * Has an underscore to hide it from custom fields.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_POST_META', '_tsfem-extension-post-meta' );

/**
 * The extension options base name.
 * Has an underscore to conform to TSF_EXTENSION_MANAGER_EXTENSION_POST_META.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_TERM_META', '_tsfem-extension-term-meta' );

/**
 * The extension options stale base name.
 * @since 1.3.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS', 'tsf-extension-manager-extension-s-settings' );
