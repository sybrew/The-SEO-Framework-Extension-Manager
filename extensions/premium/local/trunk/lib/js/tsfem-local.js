/**
 * This file holds Local extension for The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @pluginURI https://wordpress.org/plugins/the-seo-framework-extension-manager/
 */

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
// @output_file_name tsfem_e_local.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/tsfem.externs.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/extensions/premium/local/trunk/lib/js/externs/tsfem-local.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

'use strict';

/**
 * Holds tsfem_e_local values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window.tsfem_e_local = {

	/**
	 * @since 1.0.0
	 * @param {String} nonce Ajax nonce
	 */
	nonce : tsfem_e_localL10n.nonce,

	/**
	 * @since 1.0.0
	 * @param {Array} i18n Localized strings
	 */
	i18n : tsfem_e_localL10n.i18n,

	/**
	 * Saves form input through AJAX.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event jQuery event
	 * @return {boolean} False if form isn't valid. True on AJAX completion.
	 */
	validateFormJson: function( event ) {

		let formId = event.target.getAttribute( 'form' ),
			form,
			button;

		if ( formId ) {
			form = document.getElementById( formId );
			if ( ! form )
				return false;

			if ( ! tsfemForm.doValidityRoutine( form, tsfem_e_local.i18n['fixForm'] ) )
				return false;

			button = event.target;
		} else {
			return false;
		}

		let $loader = jQuery( form ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status = 0, loaderText = '';

		//= Disable the submit button.
		tsfemForm.disableButton( button );

		//= Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		//= Set ajax loader.
		tsfem.setAjaxLoader( $loader );

		//= Capture current window.
		let _currentWindow = window;

		//= Assign a new window and open it. Regardless of outcome to circumvent popup blockers.
		let _windowTarget = '_tsfemMarkupTester',
			_window = window.open( 'about:blank', _windowTarget );

		// Do ajax...
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfem_e_local_validateFormJson',
				'nonce' : tsfem_e_local.nonce,
				'data' : jQuery( form ).serialize(),
			},
			processData: true,
			timeout: 14000,
			async: true,
		} ).done( function( response ) {

			response = tsfem.convertJSONResponse( response );

			if ( tsfem.debug ) console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			if ( ! data || ! type ) {
				//* Erroneous output.
				loaderText = tsfem.i18n['InvalidResponse'];
			} else {
				let rCode = data.results && data.results.code || void 0,
					success = data.results && data.results.success || void 0;

				if ( rCode ) {
					if ( ! success ) {
						tsfem.setTopNotice( rCode );
						_window.close();
						_currentWindow.focus();
					} else {
						let tdata = data.tdata || void 0;

						status = 1;
						loaderText = tsfem_e_local.i18n['testNewWindow'];

						if ( tdata ) {
							let $form = jQuery( '<form>', {
								action: 'https://search.google.com/structured-data/testing-tool',
								method: 'post',
								target: _windowTarget
							} );

							//jQuery( '<input>' ).attr( 'type', 'submit' ).css( 'display', 'none' ).appendTo( $form );
							jQuery( '<textarea>' ).attr( 'name', 'code' ).css( 'display', 'none' ).text( tdata ).appendTo( $form );
							$form.appendTo( 'body' ).submit();
							$form.remove();
							_window.focus();
						}
					}
				} else {
					//* Erroneous output.
					loaderText = tsfem.i18n['UnknownError'];
					_window.close();
					_currentWindow.focus();
				}
			}
		} ).fail( function( jqXHR, textStatus, errorThrown ) {
			_window.close();
			_currentWindow.focus();
			// Set Ajax response for wrapper.
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

			// Try to set top notices, regardless. First notifies that there's an error saving.
			tsfem.setTopNotice( 1072100 );
			tsfem.setTopNotice( -1, errorThrown );
		} ).always( function() {
			tsfem.updatedResponse( $loader, status, loaderText, 0 );
			tsfemForm.enableButton( button );
		} );

		return true;
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

		//* Turn validate button into an AJAX pusher.
		jQ( 'button[name="tsfem-e-local-validateFormJson"]' ).click( tsfem_e_local.validateFormJson );
	}
};
jQuery( tsfem_e_local.ready );
