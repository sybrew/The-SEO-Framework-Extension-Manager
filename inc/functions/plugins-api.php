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
 * Filters the plugin list based on arguments.
 *
 * @since 1.0.0
 *
 * @param string $action The current query action. Unused as of now.
 * @param array $args The extension arguments.
 *
 * @return bool|object
 */
function tsf_extension_manager_filter_extensions( $action, $args = array() ) {

	if ( is_array( $args ) ) {
		$args = (object) $args;
	}

	if ( ! isset( $args->per_page ) ) {
		$args->per_page = 24;
	}

	if ( ! isset( $args->locale ) ) {
		$args->locale = get_locale();
	}

	if ( ! isset( $args->extensions ) )
		return false;

	$extensions = tsf_extension_manager_extract_extension( $action, $args, false );

	return $extensions;
}

/**
 * Filters the extensions by argument.
 *
 * @since 1.0.0
 * @access private
 *
 * @return object
 */
function tsf_extension_manager_extract_extension( $action, $args, $external = false ) {
	var_dump( $args );
}
