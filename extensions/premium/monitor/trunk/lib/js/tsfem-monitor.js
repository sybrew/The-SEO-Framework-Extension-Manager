/**
 * This file holds Monitor extension for The SEO Framework plugin's JS code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @pluginURI <https://theseoframework.com/extension-manager/>
 */

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfem_e_monitor values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window.tsfem_e_monitor = {

	/**
	 * @since 1.0.0
	 * @param {String} nonce Ajax nonce
	 */
	nonce: tsfem_e_monitorL10n.nonce,

	/**
	 * @since 1.0.0
	 * @param {Array} i18n Localized strings
	 */
	i18n: tsfem_e_monitorL10n.i18n,

	/**
	 * @since 1.0.0
	 * @param {Number} rDataTimeout Remote data fetch timeout
	 */
	rDataTimeout: tsfem_e_monitorL10n.remote_data_timeout,

	/**
	 * @since 1.0.0
	 * @param {Number} rCrawlTimeout Remote crawl request timeout
	 */
	rCrawlTimeout: tsfem_e_monitorL10n.remote_crawl_timeout,

	/**
	 * Expands readmore button's content whilst removing button.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 */
	showReadMore: event => {
		jQuery( `#${event.target.id}-wrap` ).remove();
		jQuery( `#${event.target.id}-content` ).slideDown( 500 );
	},

	/**
	 * Requests crawl from Monitor API server.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {!jQuery.event} event
	 * @return {(undefined|null)}
	 */
	requestCrawl: event => {

		let $button = jQuery( event.target );

		if ( $button.prop( 'disabled' ) )
			return;

		let loading = 'tsfem-button-disabled tsfem-button-loading',
			loader = '#tsfem-e-monitor-cp-pane .tsfem-pane-header .tsfem-ajax';

		$button.addClass( loading );
		$button.prop( 'disabled', true );

		// Reset ajax loader
		tsfem.resetAjaxLoader( loader );

		// Set ajax loader.
		tsfem.setAjaxLoader( loader );

		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				action:              'tsfem_e_monitor_crawl',
				nonce:                tsfem_e_monitor.nonce,
				remote_crawl_timeout: tsfem_e_monitor.rCrawlTimeout,
			},
			timeout: 10000,
		} ).done( response => {

			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			let data = response?.data,
				type = response?.type;

			if ( ! data ) {
				// Erroneous output.
				tsfem.updatedResponse( loader, 0, tsfem.i18n['InvalidResponse'] );
			} else {
				if ( 'undefined' !== typeof data.status['timeout'] )
					tsfem_e_monitor.rCrawlTimeout = data.status['timeout'];

				let status = data.status['type'],
					code   = data.status['code'],
					notice = data.status['notice'];

				if ( 'success' === status ) {
					tsfem.updatedResponse( loader, 1, '' );
					tsfem_ui.setTopNotice( code, notice );
				} else if ( 'yield_unchanged' === status ) {
					tsfem.updatedResponse( loader, 2, notice );
				} else if ( 'requires_fix' === status ) {
					tsfem_e_monitor.addRequiresFix( data.status['requires_fix'] );
					tsfem.updatedResponse( loader, 0, '' );
					tsfem_ui.setTopNotice( code, notice );
				} else {
					tsfem.updatedResponse( loader, 0, '' );
					tsfem_ui.setTopNotice( code, notice );
				}
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			let _error = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );
			tsfem.updatedResponse( loader, 0, _error );
		} ).always( () => {
			$button.removeClass( loading );
			$button.prop( 'disabled', false );
		} );
	},

	/**
	 * Fetches the data option and returns new values.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	fetchData: event => {

		let $button = jQuery( event.target );

		if ( $button.prop( 'disabled' ) )
			return;

		let loading = 'tsfem-button-disabled tsfem-button-loading',
			loader  = '#tsfem-e-monitor-issues-pane .tsfem-pane-header .tsfem-ajax';

		$button.addClass( loading );
		$button.prop( 'disabled', true );

		// Reset ajax loader
		tsfem.resetAjaxLoader( loader );

		// Set ajax loader.
		tsfem.setAjaxLoader( loader );

		// Set lastCrawled ajax loader.
		let lastCrawled = document.getElementById( 'tsfem-e-monitor-last-crawled' ),
			lastCrawledClass = lastCrawled.classList.contains( 'tsfem-success' ) ? 'tsfem-success' : 'tsfem-error';
		lastCrawled.classList.remove( 'tsfem-success' );
		lastCrawled.classList.remove( 'tsfem-error' );
		lastCrawled.classList.add( 'tsfem-loading' );

		// Set settings loader.
		tsfem_e_monitor.setSettingsLoader();

		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				'action' : 'tsfem_e_monitor_fetch',
				'nonce' : tsfem_e_monitor.nonce,
				'remote_data_timeout' : tsfem_e_monitor.rDataTimeout,
			},
			timeout: 15000,
		} ).done( response => {

			response = tsf.convertJSONResponse( response );

			if ( tsf.l10n.states.debug ) console.log( response );

			let data = response?.data,
				type = response?.type;

			if ( ! data || ! data.status ) {
				// Erroneous output.
				tsfem.updatedResponse( loader, 0, tsfem.i18n['InvalidResponse'] );
			} else {
				if ( 'undefined' !== typeof data.status['timeout'] )
					tsfem_e_monitor.rDataTimeout = data.status['timeout'];

				let status  = data.status['type'],
					content = data.status['content'],
					code    = data.status['code'],
					notice  = data.status['notice'];

				if ( 'success' === status ) {
					let issues   = content['issues'],
						lc       = content['lc'],
						settings = content['settings'];

					if ( 'undefined' !== typeof settings ) {
						for ( let _setting in settings ) {
							// Ignore prototypes.
							if ( ! settings.hasOwnProperty( _setting ) )
								continue;

							tsfem_e_monitor.setSetting( _setting, settings[ _setting ], true );
						}
					}

					if ( issues['found'] ) {
						// Expected to be inputting a single div.
						jQuery( '.tsfem-e-monitor-issues-wrap' ).empty().css( 'opacity', 0 ).append( issues.data.wrap ).animate(
							{ 'opacity' : 1 },
							{ queue: true, duration: 250 }
						);

						// Loop through each issue and slowly insert it.
						jQuery.each( issues.data.info, ( index, value ) => {
							setTimeout( () => {
								jQuery( value ).appendTo( '.tsfem-e-monitor-issues-wrap > div' ).css( 'opacity', 0 ).animate(
									{ 'opacity' : 1 },
									{ queue: false, duration: 250 }
								);
							}, 250 * index );
						} );
					} else {
						jQuery( '.tsfem-e-monitor-issues-wrap' ).empty().css( 'opacity', 0 ).append( issues.data ).animate(
							{ 'opacity' : 1 },
							{ queue: true, duration: 1000 }
						);
					}

					jQuery( '#tsfem-e-monitor-last-crawled' ).replaceWith( jQuery( lc ).css( 'opacity', 0 ) );
					// Node is gone from memory. Reaccess it.
					jQuery( '#tsfem-e-monitor-last-crawled' ).animate(
						{ 'opacity' : 1 },
						{ queue: true, duration: 1000 }
					);

					setTimeout( () => { tsfem.updatedResponse( loader, 1, notice ); }, 1000 );

					// Update hover cache.
					tsfTT.triggerReset();
				} else if ( 'yield_unchanged' === status ) {
					tsfem.updatedResponse( loader, 2, notice );
				} else if ( 'requires_fix' === status ) {
					tsfem_e_monitor.addRequiresFix();
					tsfem.updatedResponse( loader, 0, '' );
					tsfem_ui.setTopNotice( code, notice );
				} else {
					tsfem.updatedResponse( loader, 0, '' );
					tsfem_ui.setTopNotice( code, notice );
				}
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			let _error = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );
			tsfem.updatedResponse( loader, 0, _error );
		} ).always( () => {
			/**
			 * If the element isn't replaced, this will work as intended.
			 * If the elemnt is replaced, then the replacement is correct.
			 */
			if ( document.body.contains( lastCrawled ) ) {
				lastCrawled.classList.remove( 'tsfem-loading' );
				lastCrawled.classList.add( lastCrawledClass );
			}

			tsfem_e_monitor.unsetSettingsLoader();
			$button.removeClass( loading );
			$button.prop( 'disabled', false );
		} );
	},

	/**
	 * Inserts content fetched from AJAX into the account information wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @param {String} content The inserted content.
	 * @function
	 * @return {Void} If element already exists.
	 */
	addRequiresFix: () => {

		if ( jQuery( '.tsfem-account-fix' ).length > 0 || jQuery( '.tsfem-account-info' ).length < 1 )
			return;

		jQuery.ajax( {
			method:   'POST',
			url:      ajaxurl,
			datatype: 'json',
			data:     {
				action: 'tsfem_e_monitor_get_requires_fix',
				nonce:  tsfem_e_monitor.nonce,
			},
			timeout:  7000,
			async:    true,
		} ).done( response => {

			response = tsf.convertJSONResponse( response );

			if ( tsf.l10n.states.debug ) console.log( response );

			let data = response?.data;

			// No error handling, as this is invoked automatically.
			if ( data?.html )
				jQuery( data.html ).insertAfter( '.tsfem-account-info' ).hide().slideDown( 500 );
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			// No elaborate handling, as this function is invoked automatically.
			if ( tsf.l10n.states.debug ) {
				console.log( jqXHR.responseText );
				console.log( errorThrown );
			}
		} );
	},

	/**
	 * Sets loaders to setting clickers, effectively blocking them from input.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 * @return {undefined}
	 */
	setSettingsLoader: () => {
		let settingElements = document.querySelectorAll( '.tsfem-e-monitor-edit' );
		for ( let i = 0; i < settingElements.length; i++ ) {
			settingElements[ i ].classList.remove( 'tsfem-edit' );
			settingElements[ i ].classList.remove( 'tsfem-dashicon-fadeout-3000' );
			settingElements[ i ].classList.add( 'tsfem-loading' );
		}
	},

	/**
	 * Removes loaders from setting clickers.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 * @return {undefined}
	 */
	unsetSettingsLoader: () => {
		let e = document.querySelectorAll( '.tsfem-e-monitor-edit' );
		for ( let i = 0; i < e.length; i++ ) {
			tsfem_e_monitor.animateResetClicker( e[ i ] );
		}
	},

	/**
	 * Sets new settings for display.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 * @param {string} what The setting to update.
	 * @param {integer|string} value The new setting.
	 * @param {boolean|undefined} animate Whether to animate and reset the edit button.
	 * @return {undefined}
	 */
	setSetting: ( what, value, animate ) => {

		animate ||= false;

		let clicker = document.querySelector( `.tsfem-e-monitor-settings-holder[data-option-id="${what}"]` )
			?.querySelector( '.tsfem-e-monitor-edit' );

		if ( ! clicker ) {
			// Clicker not found. Update your plugins message.
			tsfem_ui.setTopNotice( 1011800 );
			return;
		}

		let selectId = '';

		if ( 'for' in clicker.dataset )
			selectId = clicker.dataset.for;

		let selector = selectId && document.getElementById( selectId );

		if ( ! selector )
			return; // User edited the DOM. Shun.

		selector.value = value;
		clicker.innerHTML = selector.options[ selector.selectedIndex ].text;
		animate && tsfem_e_monitor.animateResetClicker( clicker );
	},

	/**
	 * Animates clicker by resetting all values.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 * @param {Element} clicker The clicker element to animate.
	 * @return {undefined} Early so if already animated.
	 */
	animateResetClicker: clicker => {

		if ( clicker.classList.contains( 'tsfem-dashicon-fadeout-3000' )
		|| ( clicker.classList.contains( 'tsfem-edit' ) ) )
			return;

		clicker.classList.remove( 'tsfem-loading' );

		// Thank you for using IE11.
		if ( ! clicker.classList.contains( 'tsfem-success' )
		&& ( ! clicker.classList.contains( 'tsfem-error' ) )
		&& ( ! clicker.classList.contains( 'tsfem-unknown' ) ) ) {
			clicker.classList.add( 'tsfem-edit' );
		} else {
			clicker.classList.add( 'tsfem-dashicon-fadeout-3000' );
			setTimeout( () => {
				clicker.classList.remove( 'tsfem-success' );
				clicker.classList.remove( 'tsfem-error' );
				clicker.classList.remove( 'tsfem-dashicon-fadeout-3000' );
				// End thanks.
				clicker.classList.add( 'tsfem-edit' );
			}, 3000 );
		}
	},

	/**
	 * Shows dropdown edit field and attaches listeners.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 * @return {boolean|undefined} False on error. Undefined otherwise.
	 */
	editSetting: event => {

		let clicker  = event.target,
			selectId = void 0,
			selector = void 0,
			lastVal  = 0,
			lastText = '',
			newVal   = 0,
			newText  = '',
			option   = '';

		if ( clicker.classList.contains( 'tsfem-loading' ) )
			return false;

		if ( 'for' in clicker.dataset )
			selectId = clicker.dataset.for;

		if ( ! selectId )
			return false;

		selector = document.getElementById( selectId );

		if ( ! selector )
			return false;

		lastVal = selector.value;
		lastText = clicker.innerHTML;

		const doChange = () => {
			const showAjaxEditError = () => {
				clicker.classList.remove( 'tsfem-loading' );
				clicker.classList.add( 'tsfem-error' );
			}
			const showAjaxEditSuccess = () => {
				clicker.classList.remove( 'tsfem-loading' );
				clicker.classList.add( 'tsfem-success' );
			}

			removeListeners();
			showClicker();

			let loader = '#tsfem-e-monitor-cp-pane .tsfem-pane-header .tsfem-ajax',
				status = 0,
				topNotice = '',
				topNoticeCode = 0,
				loaderText = '';

			// Reset ajax loader
			tsfem.resetAjaxLoader( loader );

			// Set ajax loader.
			tsfem.setAjaxLoader( loader );

			// Set settings Ajax loaders.
			tsfem_e_monitor.setSettingsLoader();

			// Show new option...
			clicker.innerHTML = newText;

			jQuery.ajax( {
				method:   'POST',
				url:      ajaxurl,
				datatype: 'json',
				data:     {
					action: 'tsfem_e_monitor_update',
					nonce:  tsfem_e_monitor.nonce,
					option: option,
					value:  newVal,
				},
				timeout:  15000,
				async:    true,
			} ).done( response => {

				response = tsf.convertJSONResponse( response );

				tsf.l10n.states.debug && console.log( response );

				let data = response?.data,
					type = response?.type;

				if ( ! data || ! type ) {
					// Erroneous output.
					loaderText = tsfem.i18n['UnknownError'];
					undoChanges();
					showAjaxEditError();
				} else {
					let rCode   = data?.results?.code,
						success = data?.results?.success;

					loaderText = data?.results?.notice;

					if ( success ) {
						showAjaxEditSuccess();
					} else {
						showAjaxEditError();
					}

					switch ( rCode ) {
						case 1010805: // updated.
							status = 1;
							break;

						case 1010804: // Settings didn't save. Suggest Fetch Data.
							topNoticeCode = rCode;
							status = 2;
							break;

						default:
						case 1010801: // Remote error.
						case 1010802: // Instance mismatch.
						case 1010803: // Site marked inactive by Monitor.
						case 1010806: // Request limit reached.
						case 1010807: // License too low.
						case 1019002: // No access.
						case 1010702: // No option sent.
							loaderText = tsfem.i18n['UnknownError'];
							topNoticeCode = rCode || false;
							status = 0;
					}

					if ( 'undefined' !== typeof data.settings ) {
						for ( let _setting in data.settings ) {
							// Ignore prototypes.
							if ( ! data.settings.hasOwnProperty( _setting ) )
								continue;

							tsfem_e_monitor.setSetting( _setting, data.settings[ _setting ], false );
						}
					}
				}
			} ).fail( ( jqXHR, textStatus, errorThrown ) => {
				// Undo new option.
				undoChanges();
				showAjaxEditError();

				// Set Ajax response for wrapper.
				loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

				// Try to set top notices, regardless.
				tsfem_ui.setTopNotice( 1011700 ); // Notifies that there's an error saving.
				errorThrown && tsfem_ui.setTopNotice( -1, `Thrown error: ${errorThrown}` );
			} ).always( () => {
				tsfem.updatedResponse( loader, status, loaderText );
				if ( topNoticeCode ) {
					tsfem_ui.setTopNotice( topNoticeCode, topNotice );
				}
				tsfem_e_monitor.unsetSettingsLoader();
			} );
		}
		const undoChanges = () => {
			clicker.innerHTML = lastText;
			selector.value = lastVal;
		}
		const showForm = () => {
			jQuery( clicker ).hide();
			jQuery( selector ).slideDown( 200 ).trigger( 'focus' );
		}
		const showClicker = () => {
			jQuery( selector ).trigger( 'blur' ).hide();
			jQuery( clicker ).fadeIn( 300 );
		}
		const onChange = event => {
			let _target = event.target;
			newVal  = _target.value;
			newText = _target.options[ _target.selectedIndex ].text;
			option  = _target.name;

			newVal == lastVal && reset() || doChange();
		}
		const clickOff = event => {
			let $select = jQuery( event.target ).closest( selector );
			if ( $select.length < 1 ) {
				reset();
			}
		}
		const reset = () => {
			removeListeners();
			showClicker();
			return true;
		}
		const addListeners = () => {
			selector.addEventListener( 'blur', reset );
			selector.addEventListener( 'change', onChange );
			// Fallback:
			window.addEventListener( 'click', clickOff );
		}
		const removeListeners = () => {
			selector.removeEventListener( 'blur', reset );
			selector.removeEventListener( 'change', onChange );
			window.removeEventListener( 'click', clickOff );
		}
		showForm();
		// Don't propagate current events.
		setTimeout( addListeners, 10 );
	},

	/**
	 * Propagates keyboard event to editSetting.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	a11yEditSetting: event => {
		if ( 32 === event.which ) { // spacebar nonJQ: ' ' === event.key
			event.preventDefault();
			tsfem_e_monitor.editSetting( event );
		}
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
	ready: jQ => {
		// Disable semi-disabled buttons.
		jQ( '#tsfem-e-monitor-privacy-readmore' ).on( 'click', tsfem_e_monitor.showReadMore );

		// AJAX crawl request.
		jQ( '#tsfem-e-monitor-crawl-button' ).on( 'click', tsfem_e_monitor.requestCrawl );

		// AJAX data update.
		jQ( '#tsfem-e-monitor-fetch-button' ).on( 'click', tsfem_e_monitor.fetchData );

		// AJAX edit setting attacher.
		jQ( '.tsfem-e-monitor-edit' ).on( 'click', tsfem_e_monitor.editSetting );
		jQ( '.tsfem-e-monitor-edit' ).on( 'keypress', tsfem_e_monitor.a11yEditSetting );
	}
};
jQuery( tsfem_e_monitor.ready );
