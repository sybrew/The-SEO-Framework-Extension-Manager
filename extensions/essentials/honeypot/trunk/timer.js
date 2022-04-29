/**
 * This file holds Honeypot's code for the timer field. The contents are minified, stored, and outputted via PHP.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * Honeypot extension for The SEO Framework
 * Copyright (C) 2021-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Optimized for minification, don't reorder it unless you can make it even smaller.
 *
 * @since 2.0.0
 * @param {Object} phpValues See method output_timer_honeypot()'s $php_values.
 */
( phpValues => {
	let el = document.getElementsByName( phpValues.n )[0];

	let start,
		elapsed;

	/**
	 * Random 8 bit number that offsets 0. This is added to the timer to throw
	 * off timer detections.
	 */
	let rZeroOffset = ( 1 - Math.random() ) * 0xFF;

	let timer = ( timestamp ) => {
		if ( void 0 === start )
			start = timestamp;

		elapsed = timestamp - start;

		if ( elapsed < ( +phpValues.t / +phpValues.s ) * 1000 ) {
			// We could just like... not paint at all. That'd also work just as well.
			// But it's fun to visualize. It also helps devs that change the timer debug.
			el.value = +phpValues.t + rZeroOffset - ( elapsed / 1000 );
			tick();
		} else {
			// This is necessary because we're working with floating points.
			el.value = '';
		}
	}
	let tick = () => setTimeout( () => requestAnimationFrame( timer ), 100 + Math.random() * 200 );

	if ( el ) {
		// Set before frame-timer begins. Otherwise, bots can bypass this without loading frames.
		el.value = +phpValues.t + rZeroOffset;
		tick();
	}
} )( $php_values );
