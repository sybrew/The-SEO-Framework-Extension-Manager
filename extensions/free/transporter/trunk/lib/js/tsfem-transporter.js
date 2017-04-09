/**
 * This file holds Transporter extension for The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @pluginURI https://wordpress.org/plugins/the-seo-framework-extension-manager/
 */

/**
 * Transporter extension for The SEO Framework
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @output_file_name tsfem_e_transporter.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/tsfem.externs.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/extensions/free/transporter/trunk/lib/js/tsfem-transporter.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

/**
 * Holds tsfem_e_transporter values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window[ 'tsfem_e_transporter' ] = {

	/**
	 * @since 1.0.0
	 * @param {String} nonce Ajax nonce
	 */
	nonce : tsfem_e_transporterL10n.nonce,

	/**
	 * @since 1.0.0
	 * @param {Array} i18n Localized strings
	 */
	i18n : tsfem_e_transporterL10n.i18n,

	requestExport: function( event ) {
		'use strict';

		var loading = 'tsfem-button-disabled tsfem-button-loading',
			$button = jQuery( event.target ),
			loader = '#tsfem-e-transporter-transport-pane .tsfem-pane-header .tsfem-ajax';

		if ( $button.prop( 'disabled' ) )
			return;

		$button.addClass( loading );
		$button.prop( 'disabled', true );

		//* Reset ajax loader
		tsfem.resetAjaxLoader( loader );

		//* Set ajax loader.
		tsfem.setAjaxLoader( loader );

		//* Get external data.
		let settings = {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				'action' : 'tsfem_e_transporter_request_export',
				'nonce' : tsfem_e_transporter.nonce,
			},
			timeout: 10000,
			async: true,
			success: function( response ) {

				response = jQuery.parseJSON( response );

				if ( tsfem.debug ) console.log( response );

				if ( 'undefined' === typeof response || 'undefined' === typeof response.type || 'undefined' === typeof response.html ) {
					//* Erroneous input.
					tsfem.updatedResponse( loader, 0, '', 0 );
				} else {

					let status = response.type,
						html = response.html,
						notice = response.notice;

					if ( 'success' === status ) {
						if ( html ) {
							//* Expected to be inputting a single div.
							jQuery( '.tsfem-e-transporter-step-2' ).empty().css( 'opacity', 0 ).append( html ).animate(
								{ 'opacity' : 1 },
								{ queue: true, duration: 250 },
								'swing'
							);
						} else {
							/*
							let issuesOutput = '<div class="tsfem-pane-inner-wrap tsfem-e-monitor-issues-wrap tsfem-flex tsfem-flex-row">' + issues.data + '</div>';

							jQuery( '.tsfem-e-transporter-steps' ).empty().css( 'opacity', 0 ).append( issuesOutput ).animate(
								{ 'opacity' : 1 },
								{ queue: true, duration: 1000 },
								'swing'
							);*/
						}

						setTimeout( function() { tsfem.updatedResponse( loader, 1, notice, 0 ); }, 250 );
					} else {
						tsfem.updatedResponse( loader, 0, notice, 0 );
					}
				}
			},
			error: function( xhr, ajaxOptions, thrownError ) {
				if ( tsfem.debug ) {
					console.log( xhr.responseText );
					console.log( thrownError );
				}
				tsfem.updatedResponse( loader, 0, '', 0 );
			},
			complete: function() {
				$button.removeClass( loading );
				$button.prop( 'disabled', false );
			},
		}

		jQuery.ajax( settings );
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} jQ jQuery
	 * @function
	 */
	ready: function( jQ ) {
		'use strict';

		// AJAX request export data.
		jQ( 'a#tsfem-e-transporter-export-button' ).on( 'click', tsfem_e_transporter.requestExport );

	}
};
jQuery( tsfem_e_transporter.ready );
