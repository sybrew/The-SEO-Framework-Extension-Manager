/**
 * This file holds Honeypot's code for the JS field. The contents are minified, stored, and outputted via PHP.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * Honeypot extension for The SEO Framework
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

'use strict';

/**
 * @since 2.0.0
 * @param {Object} phpValues See method output_timer_honeypot()'s $php_values.
 */
( phpValues => {
	let input   = document.getElementById( phpValues.i ),
		wrapper = document.getElementById( phpValues.w );

	if ( input && wrapper ) {
		input.value           = '';
		wrapper.style.display = 'none';
	}
} )( $php_values );
