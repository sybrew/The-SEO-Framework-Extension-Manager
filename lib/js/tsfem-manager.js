/**
 * This file holds Extension Manager manager-page code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfem_manager values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 2.5.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfem_manager = function( $ ) {

	/**
	 * Signifies known states on-load.
	 *
	 * @since 2.5.0
	 * @access public
	 *
	 * @const {object<string,string>} i18n
	 */
	const i18n = tsfemManagerL10n.i18n;

	/**
	 * Updates the feed option.
	 *
	 * @since 1.0.0
	 * @since 2.5.0 Moved to different object.
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event
	 * @return {undefined}
	 */
	const _updateFeed = ( event ) => {

		let disabledClass = 'tsfem-button-disabled',
			$button       = $( event.target ),
			loader        = '#tsfem-feed-ajax',
			status        = 0;

		if ( $button.prop( 'disabled' ) )
			return;

		$button.addClass( disabledClass );
		$button.prop( 'disabled', true );

		// Reset ajax loader
		tsfem.resetAjaxLoader( loader );

		// Set ajax loader.
		tsfem.setAjaxLoader( loader );

		const unknownError = () => {
			$button.removeClass( disabledClass );
			$button.prop( 'disabled', false );
			tsfem.updatedResponse( loader, status, tsfem.i18n['UnknownError'] );
		};

		// Setup external update.
		$.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'tsfem_enable_feeds',
				nonce:  tsfem.nonce,
			},
			timeout: 12000,
			async: true,
		} ).done( ( response ) => {

			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			if ( 'success' === type && data ) {

				let content = data.content;

				switch ( content.status ) {
					case 'success':
						status = 1;

						// Insert wrap.
						$( '.tsfem-trends-wrap' ).empty().css( 'opacity', 0 ).append( content.wrap ).animate(
							{ 'opacity' : 1 },
							{ 'queue' : true, 'duration' : 250 }
						);

						let duration = 400,
							total    = content.data.length,
							wait     = 0;

						// Calculate loader wait.
						// Remove last entry from calculation (total-1) as it has adds no timing effect.
						for ( let i = 1; i < total - 1; i++ ) {
							wait += Math.round( duration / Math.pow( 1 + ( i / 2 ) / 100, 2 ) );
						}
						// Remove first and last entries from calculation as they have no timing effects.
						wait -= ( duration * 2 ) + ( duration / 2 );

						// Loop through each issue and slowly insert it. It's run asynchronously...
						$.each( content.data, ( index, value ) => {
							duration = Math.round( duration / Math.pow( 1 + ( index / 2 ) / 100, 2 ) );
							setTimeout( () => {
								$( value ).hide().appendTo( '.tsfem-feed-wrap' ).slideDown( duration );
							}, duration / 2 * index );
						} );

						// Expected to be done in 3.858 seconds
						setTimeout( () => { tsfem.updatedResponse( loader, status, '' ); }, wait );
						break;

					case 'parse_error':
					case 'unknown_error':
					default:
						$( '.tsfem-trends-wrap' ).empty().css( 'opacity', 0 ).append( content.error_output ).css( 'opacity', 1 ).find( '.tsfem-feed-wrap' ).css(
							{ 'opacity' : 0 }
						).animate(
							{ 'opacity' : 1 },
							{ queue: true, duration: 2000 }
						);
						// 2 means the feed is offline. 0 means a server parsing error.
						// Don't enable the button. Make the user reload.
						status = 'unknown_error' === content.status ? 2 : 0;
						setTimeout( () => { tsfem.updatedResponse( loader, status, tsfem.i18n['UnknownError'] ); }, 1000 );
						break;
				}
			} else if ( 'unknown' === response.type ) {
				status = 2;
				unknownError();
			} else {
				unknownError();
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			let _error = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

			$button.removeClass( disabledClass );
			$button.prop( 'disabled', false );
			tsfem.updatedResponse( loader, 0, _error );
		} );
	}

	/**
	 * Updates the selected extension state.
	 *
	 * @since 1.0.0
	 * @since 2.5.0 Moved to different object.
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event jQuery event
	 * @return {undefined}
	 */
	const _updateExtension = event => {

		let disabledClass = 'tsfem-button-disabled',
			button        = event.target,
			$button       = $( event.target );

		if ( button.disabled || button.classList.contains( disabledClass ) )
			return;

		let actionSlug = button.dataset.slug,
			actionCase = button.dataset.case;

		let loader = '#tsfem-extensions-ajax',
			status = 0,
			topNotice = '',
			topNoticeCode = 0,
			loaderText = '';

		// Disable all buttons

		let allButtons = tsfem.getNodeArray( '.tsfem-button-extension-activate, .tsfem-button-extension-deactivate', disabledClass );
		allButtons.forEach( ( _button ) => {
			_button.classList.add( disabledClass );
			_button.disabled = true;
		} );

		// Reset ajax loader
		tsfem.resetAjaxLoader( loader );

		// Set ajax loader.
		tsfem.setAjaxLoader( loader );

		// Setup external update.
		$.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'tsfem_update_extension',
				nonce:  tsfem.nonce,
				slug:   actionSlug,
				case:   actionCase,
			},
			timeout: 10000,
			async: true,
		} ).done( ( response ) => {

			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0; // type is unused but it's a standard.

			if ( ! data || ! type ) {
				// Erroneous output.
				loaderText = tsfem.i18n['UnknownError'];
			} else {
				let rCode   = data.results && data.results.code || void 0,
					success = data.results && data.results.success || void 0;

				loaderText = data.results && data.results.notice || void 0;

				if ( 'activate' === actionCase ) {
					switch ( rCode ) {
						case 10001: // No extensions checksum found.
						case 10002: // Extensions checksum mismatch.
						case 10003: // Method outcome mismatch.
						case 10004: // Account isn't allowed to use premium extension.
						case 10006: // Option update failed for unknown reason. Maybe overload.
						case 10007: // No slug set.
						case 10013: // Forced inactive...
						case 10014: // Hidden... User didn't log out when this was imposed.
							status        = 0;
							topNoticeCode = rCode;
							break;

						case 10005: // Extension caused fatal error.
							status         = 0;
							let fatalError = data && data.fatal_error || void 0;
							topNotice      = fatalError;
							topNoticeCode  = rCode;
							break;

						case 10008: // Premium/Essentials activated.
						case 10010: // Free activated.
						case 10012: // Already active...
							status = 1;
							button.classList.remove( 'tsfem-button', 'tsfem-button-extension-activate' );
							button.classList.add( 'tsfem-button-primary', 'tsfem-button-primary-dark', 'tsfem-button-extension-deactivate' );

							button.dataset.case = 'deactivate';
							button.innerText    = i18n['Deactivate'];

							let _entry = document.getElementById( `${actionSlug}-extension-entry` );
							if ( _entry ) {
								_entry.classList.remove( 'tsfem-extension-deactivated' );
								_entry.classList.add( 'tsfem-extension-activated' );
							}

							_updateExtensionDescFooter( actionSlug, actionCase );
							break;

						case 10009: // User not premium, trying to activate premium extension.
							status = 2;
							topNoticeCode = rCode;
							break;

						default:
							status = 0;
							loaderText = tsfem.i18n['UnknownError'];
							break;
					}
				} else if ( 'deactivate' === actionCase ) {
					switch ( rCode ) {
						case 11001: // success.
							status = 1;
							button.classList.remove( 'tsfem-button-primary', 'tsfem-button-primary-dark', 'tsfem-button-extension-deactivate' );
							button.classList.add( 'tsfem-button', 'tsfem-button-extension-activate' );

							button.dataset.case = 'activate';
							button.innerText    = i18n['Activate'];

							let _entry = document.getElementById( `${actionSlug}-extension-entry` );
							if ( _entry ) {
								_entry.classList.add( 'tsfem-extension-deactivated' );
								_entry.classList.remove( 'tsfem-extension-activated' );
							}

							_updateExtensionDescFooter( actionSlug, actionCase );
							break;

						case 11002: // failure.
						case 11003: // Forced active...
						case 11004: // Hidden... User didn't log out when this was imposed.
							status        = 0;
							topNoticeCode = rCode;
							break;

						default :
							status     = 0;
							loaderText = tsfem.i18n['UnknownError'];
							break;
					}
				}
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			// Set Ajax response for wrapper.
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

			// Try to set top notices, regardless.
			tsfem_ui.setTopNotice( 1071100 ); // Notifies that there's an error saving.
			errorThrown && tsfem_ui.setTopNotice( -1, 'Thrown error: ' + errorThrown );
		} ).always( () => {
			tsfem.updatedResponse( loader, status, loaderText );

			allButtons.forEach( ( _button ) => {
				_button.classList.remove( disabledClass );
				_button.disabled = false;
			} );

			button.focus();

			topNoticeCode && tsfem_ui.setTopNotice( topNoticeCode, topNotice );
		} );
	}

	/**
	 * Gets and inserts the AJAX response for the Extension Description Footer.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {String} actionSlug The extension slug.
	 * @param {String} actionCase The update case. Either 'activate' or 'deactivate'.
	 * @return {undefined}
	 */
	const _updateExtensionDescFooter = ( actionSlug, actionCase ) => {

		$.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'tsfem_update_extension_desc_footer',
				nonce:  tsfem.nonce,
				slug:   actionSlug,
				case:   actionCase,
			},
			timeout: 7000,
			async: true,
		} ).done( ( response ) => {

			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			if ( ! data ) return;

			let $footer   = $( '#' + actionSlug + '-extension-entry .tsfem-extension-description-footer' ),
				direction = 'activate' === actionCase ? 'up' : 'down';

			$footer.addClass( 'tsfem-flip-hide-' + direction );

			//! TODO use promises...
			setTimeout( () => {
				$footer.empty().append( data );
				// Flush tooltip cache.
				tsfTT.triggerReset();
			}, 250 );
			setTimeout( () => {
				$footer.addClass( 'tsfem-flip-show-' + direction );
			}, 500 );
			setTimeout( () => {
				$footer.removeClass( 'tsfem-flip-hide-' + direction + ' tsfem-flip-show-' + direction );
			}, 750 );
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			// Don't invoke anything fancy, yet. This is automatically called.
			if ( tsf.l10n.states.debug ) {
				console.log( jqXHR.responseText );
				console.log( errorThrown );
			}
		} );
	}

	/**
	 * Runs document-on-ready actions.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	const _doReady = () => {
		// AJAX feed update.
		$( '#tsfem-enable-feeds' ).on( 'click', _updateFeed );

		// AJAX extension update.
		$( '.tsfem-button-extension-activate, .tsfem-button-extension-deactivate' ).on( 'click', _updateExtension );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 *
		 * @since 2.5.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			document.body.addEventListener( 'tsf-ready', _doReady );
		}
	}, {
		/**
		 * Constant variables.
		 * Don't overwrite these.
		 *
		 * @since 2.5.0
		 * @access public
		 */
		i18n,
	}, {
		/**
		 * Constant functions.
		 * Don't overwrite these.
		 *
		 * @since 2.5.0
		 * @access public
		 */
	} );
}( jQuery );
window.tsfem_manager.load();
