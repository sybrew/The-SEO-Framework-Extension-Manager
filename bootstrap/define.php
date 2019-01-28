<?php
/**
 * @package TSF_Extension_Manager/Bootstrap
 */

defined( 'TSF_EXTENSION_MANAGER_DB_VERSION' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * NOTE:
 * The definable constants should be defined in `wp-config.php`.
 * Alternatively, you may define them conditionally in a mu-plugin.
 *
 * These constants may fail to work as intended when defined in a regular plugin. The plugin load sequence affects this behavior.
 * These constants won't work in a theme, this file is loaded before themes are.
 */

/**
 * The forced plugin license information.
 * @since 2.0.0
 * @param bool|array : [ 'email' => '', 'key' => '' ]
 */
defined( 'TSF_EXTENSION_MANAGER_API_INFORMATION' )
	or define( 'TSF_EXTENSION_MANAGER_API_INFORMATION', false );

/**
 * The forced activated extensions.
 * @since 2.0.0
 * @param bool|array : [ ...'extension_slug' => bool ]
 */
defined( 'TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS' )
	or define( 'TSF_EXTENSION_MANAGER_FORCED_EXTENSIONS', false );

/**
 * The hidden extensions, only activatable via THE_SEO_FRAMEWORK_FORCED_EXTENSIONS.
 * @since 2.0.0
 * @param bool|array : [ ...'extension_slug' ]
 */
defined( 'TSF_EXTENSION_MANAGER_HIDDEN_EXTENSIONS' )
	or define( 'TSF_EXTENSION_MANAGER_HIDDEN_EXTENSIONS', false );

/**
 * The user role required to access the extension overview page.
 * @since 2.0.0
 * @param string
 */
defined( 'TSF_EXTENSION_MANAGER_MAIN_ADMIN_ROLE' )
	or define( 'TSF_EXTENSION_MANAGER_MAIN_ADMIN_ROLE', 'manage_options' );

/**
 * The plugin map URL. Used for calling browser files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_URL', plugin_dir_url( TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE ) );

/**
 * The plugin map absolute path. Used for calling php files.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH', dirname( TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE ) . DIRECTORY_SEPARATOR );

/**
 * The plugin class map absolute path.
 * @since 1.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DIR_PATH_CLASS', TSF_EXTENSION_MANAGER_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * The plugin trait map absolute path.
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
 * The extension post meta options base name.
 * Has an underscore to hide it from custom fields.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_POST_META', '_tsfem-extension-post-meta' );

/**
 * The extension term meta options base name.
 * Has an underscore to conform to TSF_EXTENSION_MANAGER_EXTENSION_POST_META.
 * @since 1.5.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_TERM_META', '_tsfem-extension-term-meta' );

/**
 * The extension options stale base name.
 * @since 1.3.0
 */
define( 'TSF_EXTENSION_MANAGER_EXTENSION_STALE_OPTIONS', 'tsf-extension-manager-extension-s-settings' );

/**
 * The expected plugin slug.
 * @since 2.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PLUGIN_SLUG', 'the-seo-framework-extension-manager' );

/**
 * The updater cache key.
 * @since 2.0.0
 */
define( 'TSF_EXTENSION_MANAGER_UPDATER_CACHE', 'tsfem-updater-cache' );

/**
 * The DL URI.
 * @since 2.0.0
 */
define( 'TSF_EXTENSION_MANAGER_DL_URI', 'https://dl.theseoframework.com/' );

/**
 * The Premium URI.
 * @since 2.0.0
 */
define( 'TSF_EXTENSION_MANAGER_PREMIUM_URI', 'https://premium.theseoframework.com/' );
