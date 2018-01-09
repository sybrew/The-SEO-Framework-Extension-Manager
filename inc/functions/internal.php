<?php
/**
 * @package TSF_Extension_Manager\Functions
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Extracts the basename of an extension from its file location.
 *
 * @since 1.0.0
 *
 * @param string $path The extension path.
 * @return string The normalized extension basename.
 */
function extension_basename( $path ) {

	$path = \wp_normalize_path( $path );
	$extension_dir = \wp_normalize_path( TSF_EXTENSION_MANAGER_DIR_PATH );

	$path = trim( $path, DIRECTORY_SEPARATOR );
	$extension_dir = trim( $extension_dir, DIRECTORY_SEPARATOR );

	/**
	 * @TODO figure out why preg_replace is used in WP Core.
	 * Isn't str_replace much more ideal, as we're simply replacing a known part?
	 * ...
	 */
	//$path = preg_replace( '#^' . preg_quote( $extension_dir, '#' ) . '/#', '', $path );
	$path = str_replace( $extension_dir, '', $path );
	$path = trim( $path, DIRECTORY_SEPARATOR );

	return $path;
}

/**
 * Extracts the dirname of an extension from its file locaiton.
 *
 * @since 1.0.0
 * @since 1.3.0 No longer uses trailingslashit
 *
 * @param string $file The extension file.
 * @return string The extension directory path.
 */
function extension_dir_path( $file ) {
	return dirname( $file ) . DIRECTORY_SEPARATOR;
}

/**
 * Extracts the directory URL of an extension from its file location.
 *
 * @since 1.0.0
 *
 * @param string $file The extension file path.
 * @return string The extension URL path.
 */
function extension_dir_url( $file ) {

	$path = dirname( \TSF_Extension_Manager\extension_basename( $file ) );
	$path = str_replace( '/', DIRECTORY_SEPARATOR, $path );

	$url = TSF_EXTENSION_MANAGER_DIR_URL;
	$url .= trim( $path, '/ ' ) . '/';

	return $url;
}

/**
 * Converts input variable to true if it's false.
 * Returns false if input variable is false.
 *
 * @since 1.2.0
 *
 * @param bool $bool The variable to convert. Passed by reference.
 * @return bool True if input is true, false otherwise.
 */
function is_done( &$bool ) {
	return (bool) $bool ?: ( ( $bool = true ) && false );
}

/**
 * Determines if the method or function has already run.
 *
 * @since 1.2.0
 * @staticvar array $cache
 *
 * @param string $caller The method or function that calls this.
 * @return bool True if already called, false otherwise.
 */
function has_run( $caller ) {

	static $cache = [];

	return isset( $cache[ $caller ] ) ?: ( ( $cache[ $caller ] = true ) && false );
}

/**
 * Loads the plugin upgrader.
 * Function is outside of the plugin namespace so it call be called in the boot file.
 *
 * @since 1.5.0
 */
function load_upgrader() {
	require_once TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'upgrader.class.php';
}

/**
 * Builds error notice.
 *
 * @since 1.5.0
 *
 * @param bool   $success The success status, either boolean, int, or other.
 * @param string $notice  The error notice displayed to the user.
 * @param int    $code    The error code. Defaults to -1 (undefined).
 * @return array {
 *    'success' => mixed $success,
 *    'notice'  => string $notice,
 *    'code'    => int $code,
 * }
 */
function get_ajax_notice( $success = false, $notice = '', $code = -1 ) {
	return [
		'success' => $success,
		'notice' => $notice,
		'code' => intval( $code ),
	];
}
