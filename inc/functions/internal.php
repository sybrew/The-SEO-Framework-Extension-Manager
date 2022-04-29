<?php
/**
 * @package TSF_Extension_Manager\Functions
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Dev note: wp_normalize_path() replaces backslashes with forward slashes.
 *           So, we should NOT use DIRECTORY_SEPARATOR for variables parsed through it.
 *
 * @since 1.0.0
 * @since 2.5.0 Now only replaces the first occurence of the dir path, for rare instances
 *              where extension names match the exact root path on a server.
 * @since 2.5.1 Fixed DIRECTORY_SEPARATOR replacement issue on Windows installations.
 *
 * @param string $path The extension path.
 * @return string The normalized extension basename.
 */
function extension_basename( $path ) {

	$extension_dir = trim( \wp_normalize_path( TSF_EXTENSION_MANAGER_DIR_PATH ), '/' );
	$path          = trim( \wp_normalize_path( $path ), '/' );

	$path = preg_replace( '#^' . preg_quote( $extension_dir, '#' ) . '/#', '', $path );

	return trim( $path, '/' );
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
	return \dirname( $file ) . DIRECTORY_SEPARATOR;
}

/**
 * Extracts the directory URL of an extension from its file location.
 *
 * @since 1.0.0
 * @since 1.5.1 No longer generates links using directory separators on Windows.
 *
 * @param string $file The extension file path.
 * @return string The extension URL path.
 */
function extension_dir_url( $file ) {

	$path = \dirname( extension_basename( $file ) );
	//= Convert Windows/Unix paths to URL paths.
	$path = str_replace( DIRECTORY_SEPARATOR, '/', $path );

	return TSF_EXTENSION_MANAGER_DIR_URL . trim( $path, '/ ' ) . '/';
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
	static $_loaded;
	if ( $_loaded ) return;

	require TSF_EXTENSION_MANAGER_BOOTSTRAP_PATH . 'upgrader.class.php';

	$_loaded = true;
}

/**
 * Builds AJAX error notice and returns it.
 *
 * @since 1.5.0
 *
 * @param bool   $success The success status, either boolean, int, or other.
 * @param string $notice  The error notice displayed to the user.
 * @param int    $code    The error code. Defaults to -1 (undefined).
 * @param string $type    The notice type. Accepts 'success', 'warning', 'info', 'error'.
 *                        Defaults to $success state: 'success'/'error'
 * @return array {
 *    'success' => mixed $success,
 *    'notice'  => string $notice,
 *    'code'    => int $code,
 *    'type'    => string $type
 * }
 */
function get_ajax_notice( $success = false, $notice = '', $code = -1, $type = '' ) {
	return [
		'success' => $success,
		'notice'  => $notice,
		'code'    => \intval( $code ),
		'type'    => $type ?: ( $success ? 'success' : 'error' ),
	];
}
