<?php
/**
 * @package TSF_Extension_Manager\Functions
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

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
 * Extracts the basename of an extension from its file location.
 *
 * @since 1.0.0
 *
 * @param string $file The extension file.
 * @return string The normalized extension basename.
 */
function extension_basename( $file ) {

	$file = wp_normalize_path( $file );

	$file = trim( $file, '/' );
	$extension_dir = wp_normalize_path( TSF_EXTENSION_MANAGER_DIR_PATH );
	$extension_dir = trim( $extension_dir, '/' );

	$file = preg_replace( '#^' . preg_quote( $extension_dir, '#' ) . '/#' ,'' , $file );
	$file = trim( $file, '/' );

	return $file;
}

/**
 * Extracts the dirname of an extension from its file locaiton.
 *
 * @since 1.0.0
 *
 * @param string $file The extension file.
 * @return string The extension directory path.
 */
function extension_dir_path( $file ) {
	return trailingslashit( dirname( $file ) );
}

/**
 * Extracts the directory URL of an extension from its file locaiton.
 *
 * @since 1.0.0
 *
 * @param string $file The extension file path.
 * @return The extension URL path.
 */
function extension_dir_url( $file ) {

	$path = dirname( extension_basename( $file ) );
	$url = TSF_EXTENSION_MANAGER_DIR_URL;

	$url .= trim( $path, '/ ' ) . '/';

	return $url;
}
