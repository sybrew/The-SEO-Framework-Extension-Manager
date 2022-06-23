/**
 * This file holds Import's code for interpreting keywords and their subjects.
 * Serve JavaScript as an addition, not as an ends or means.
 * Alas, there's no other way here.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * Import extension for The SEO Framework
 * Copyright (C) 2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfem_e_import values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window.tsfem_e_import = function() {

	/**
	 * @since 1.0.0
	 * @access private
	 * @type {{i18n:{?},nonce:string}|null} l10n The l10n parameters set in PHP to var.
	 */
	const l10n = tsfem_e_transportL10n;

	/**
	 * @since 1.0.0
	 * @access private
	 * @type {int} The retry timeout in milliseconds.
	 */
	const retryTimeout = 5000;

	/**
	 * @since 1.0.0
	 * @access private
	 * @type {int} The number of retries allowed before manual intervention is required.
	 */
	const retryLimit = 5;

	const _enableButtons = disable => {
		disable ||= false;
		[
			'importer-submit'
		].forEach( buttonName => {
			const button = document.getElementById( `tsfem-e-transport-${buttonName}` );
			button.classList.toggle( 'tsfem-button-disabled', disable );
			button.disabled = disable;
		} );
	}
	const _disableButtons = () => _enableButtons( true );

	/**
	 * Visualizes the AJAX response to the user.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {?string} type    null|'start' = start loading, 'end' = stop loading.
	 * @param {number}  success 0 = error, 1 = success, 2 = unknown but success.
	 * @param {string}  notice The updated notice.
	 */
	const _setLoggerLoader = ( type, success, notice ) => {

		const loggerLoaderQuery = '#tsfem-e-transport-log-ajax';

		switch ( type || 'start' ) {
			case 'start':
				// Reset ajax loader
				tsfem.resetAjaxLoader( loggerLoaderQuery );
				// Set ajax loader.
				tsfem.setAjaxLoader( loggerLoaderQuery );
				break;
			case 'end':
				tsfem.updatedResponse( loggerLoaderQuery, success, notice );
				break;
		}
	}

	/**
	 * Starts importing.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
	const _handleImport = event => {

		const button = event.target;

		if ( button.disabled ) return;

		let buttonLoadingClasses = [ 'tsfem-button-loading' ];

		button.classList.add( ...buttonLoadingClasses );
		_disableButtons();
		_setLoggerLoader();
		_startLoggerAnimation();

		const formNs   = 'tsfem-e-transport-importer';
		const form     = document.getElementById( formNs );
		const formData = new FormData( form );
		[ ...formData.keys() ].forEach( name => {
			form.querySelectorAll( `[name="[${name}]"]` ).forEach( el => {
				el.dataset.handlerDisabled = ! el.disabled; // Mark if already disabled.
				el.disable = true;
			} );
		} );

		const handler = 'undefined' !== typeof( EventSource ) ? _handleEventStream : _handlePost;

		handler(
			'import',
			formData,
			l10n.i18n.logMessages.requestImport.replace(
				'%s',
				form.querySelector( `[value="${formData.get( `${formNs}[choosePlugin]` )}"]` )?.dataset.title
			),
		).then( successMsg => {
			_setLoggerLoader( 'end', 1, successMsg );
		} ).catch( errorMsg => {
			_setLoggerLoader( 'end', 0, errorMsg );
		} ).finally( () => {
			_enableButtons();
			button.classList.remove( ...buttonLoadingClasses );

			[ ...formData.keys() ].forEach( name => {
				form.querySelectorAll( `[name="${name}"]` ).forEach( el => {
					if ( el.dataset.handlerDisabled )
						el.disabled = false;

					delete el.dataset.handlerDisabled;
				} );
			} );

			_stopLoggerAnimation();
		} );
	}

	/**
	 * Handles AJAX post requests securely.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param {String}   handle   The transport action type/handle.
	 * @param {FormData} formData The formdata to post for handle.
	 * @param {?String}  logStart The starting log text. Expected to be escaped.
	 * @function
	 */
	const _handleEventStream = ( handle, formData, logStart ) => new Promise( ( resolve, reject ) => {

		const url = new URL( ajaxurl, new URL( document.baseURI ).origin );

		url.searchParams.set( 'action', 'tsfem_e_transport' );
		url.searchParams.set( 'handle', handle );
		url.searchParams.set( 'nonce', l10n.nonce );
		url.searchParams.set( 'data', formData && ( new URLSearchParams( [ ...formData.entries() ] ) ).toString() );

		let retryCount           = 0,
			urlParamRetryAllowed = 1;

		const startSSE = () => {
			url.searchParams.set( 'retryAllowed', urlParamRetryAllowed );

			const SSE = new EventSource( url.href );

			SSE.onopen = () => {
				_log( logStart )
			};
			SSE.onerror = event => {
				SSE.close();
				_log( l10n.i18n.logMessages.unknownErrorFull, 2 );
				reject( l10n.i18n.logMessages.unknownError );
			}
			const extractEventData = data => {
				try {
					return JSON.parse( data );
				} catch ( error ) {
					return void 0;
				}
			}
			const doEventResolve = event => {
				SSE.close();
				let data = extractEventData( event.data );
				_log( '&nbsp;' );
				_log( "\n" + data.logMsg, 2 );
				resolve( data?.results.notice );
			}
			const doEventReject = event => {
				SSE.close();
				let data = extractEventData( event.data );
				_log( '&nbsp;' );
				_log( data?.logMsg, 2 );
				reject( data?.results.notice );
			}
			const doEventRetry = event => {

				retryCount++;

				_log( '===============', 1 );

				if ( retryCount > retryLimit ) {
					_log( l10n.i18n.logMessages.retryLimitReached );
					return doEventReject( event );
				}

				// Not <= because we counted.
				urlParamRetryAllowed = retryCount < retryLimit ? 1 : 0;

				SSE.close();
				// Only log, retry automatically.
				_log( extractEventData( event.data )?.logMsg, 1 );

				// Start at 1 because when we hit 1 we immediately start
				let retryTick = 1;
				const retryTicker = setInterval( () => {
					let countDown = ( retryTimeout / 1000 ) - retryTick++,
						lastTick  = countDown < 2;

					_log( l10n.i18n.logMessages.retryCountdown.replace( '%d', countDown ), lastTick ? 1 : 0 );

					if ( lastTick )
						clearInterval( retryTicker );
				}, 1000 );

				setTimeout( startSSE, retryTimeout );
			}

			SSE.addEventListener( 'tsfem-e-transport-log', event => {
				_log( extractEventData( event.data )?.content );
			} );
			SSE.addEventListener( 'tsfem-e-transport-done', doEventResolve );

			SSE.addEventListener( 'tsfem-e-transport-failure', doEventReject );
			SSE.addEventListener( 'tsfem-e-transport-locked', doEventReject );

			SSE.addEventListener( 'tsfem-e-transport-crash', doEventRetry );
			SSE.addEventListener( 'tsfem-e-transport-timeout', doEventRetry );
		}

		startSSE();
	} );

	/**
	 * Handles AJAX post requests securely.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param {String}   handle   The transport action type/handle.
	 * @param {FormData} formData The formdata to post for handle.
	 * @param {?String}  logStart The starting log text. Expected to be escaped.
	 * @function
	 */
	const _handlePost = ( handle, formData, logStart ) => new Promise( ( resolve, reject ) => {

		_log( logStart, 1 );

		wp.ajax.post(
			'tsfem_e_transport',
			{
				handle,
				nonce: l10n.nonce,
				data: formData && ( new URLSearchParams( [ ...formData.entries() ] ) ).toString(),
			}
		).done( data => {
			resolve( data?.results?.notice );
			_log( data?.logMsg, 2 );
		} ).fail( response => {
			reject( response.data?.results?.notice );
			_log( response.data?.logMsg, 2 );
		} );
	} );

	const _logger = document.getElementById( 'tsfem-e-transport-logger' );
	/**
	 * Writes data to logger queue.
	 * Must start logger animation.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param {String}  message The message to log
	 * @param {Integer} newLine Whether to add newlines.
	 * @function
	 */
	const _log = ( message, newLine ) => {
		if ( _logger && message?.length )
			tsfem_ui.logger.queue(
				_logger,
				`\n&ratio; ${tsf.escapeString( tsf.decodeEntities( message ) )}${( '\n'.repeat( newLine || 0 ) )}`
			);
	}

	/**
	 * Starts logger animation. Required to write to logger queue.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param {String}  message The message to log
	 * @param {Integer} newLine Whether to add newlines.
	 * @function
	 */
	const _startLoggerAnimation = () => _logger && tsfem_ui.logger.start( _logger );
	/**
	 * Stops logger animation.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
	const _stopLoggerAnimation = () => _logger && tsfem_ui.logger.stop( _logger );

	/**
	 * Copies logger's contents.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {Event} event Button click event.
	 */
	const _copyLog = event => {
		if ( ! _logger ) return;

		const removeTT = () => setTimeout( () => {
			tsfTT.removeTooltip( event.target );
		}, 3000 );

		tsfem_ui.logger.copy( _logger ).then( () => {
			tsfTT.doTooltip( event, event.target, event.target.dataset.copyconfirm );
			removeTT();
		} ).catch( () => {
			tsfTT.doTooltip( event, event.target, event.target.dataset.copyfail );
			removeTT();
		} );
	}
	/**
	 * Copies logger's contents.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
	const _scrollLogToBottom = () => _logger && tsfem_ui.logger.scrollToBottom( _logger );

	/**
	 * Sets up importer select display.
	 *
	 * This callback doesn't have its element-fetchers optimized; however, since it is a one-time operation,
	 * optimization isn't necessary, and can even be counter-productive by hogging memory needlessly.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
	const _importerSelectPlugin = event => {

		event.preventDefault();
		event.stopPropagation();

		if ( event.target.dataset?.lastValue === event.target.value )
			return;

		event.target.dataset.lastValue = event.target.value;

		const basename = 'tsfem-e-transport-importer';
		const data     = JSON.parse( event.target.selectedOptions[0]?.dataset.importers );

		const optionsWrap                = document.getElementById( `${basename}-options` );
		const supportsTransformationHelp = document.getElementById( `${basename}-supports-transformation-help` );
		const submit                     = document.getElementById( `${basename}-submit` );

		optionsWrap.innerHTML = '';
		optionsWrap.style.display = 'none';
		supportsTransformationHelp.style.display = 'none';

		let hasTransformation = false,
			hasOptions        = false;

		const handleTypeChange = e => {
			// We could "effeciently" keep track of how many items are checked (out of total)
			// We do that in ../../..tsfem-form.js, but this doesn't require that level of optimization.
			// Query all the fields! All the time! Yay for pushing out code ASAP. This is me not giving a hoot.
			// typeSupports.style.display = e.target.checked ? null : 'none';
			const enabledTypes = document.querySelectorAll( `[name^="${basename}\\[selectType\\]"]:checked` );
			hasTransformation = false;
			enabledTypes.forEach( el => {
				hasTransformation = !! data[ el.value ]?.transform?.length;
			} );

			supportsTransformationHelp.style.display = hasTransformation ? null : 'none';

			const disableSubmit = ! enabledTypes.length;
			submit.disabled = disableSubmit;
			submit.classList.toggle( 'tsfem-button-disabled', disableSubmit );
		}

		const populateOptionsTemplate = type => {
			const optionsTemplate = document.getElementById( `${basename}-options-template` ).content.cloneNode( true );
			optionsTemplate.querySelector( `.${basename}-selectType-description` ).innerText = l10n.i18n.optionNames[ type ] ?? '';

			const typeInput = optionsTemplate.querySelector( `[name^="${basename}\\[selectType\\]"]` );
			typeInput.value = type;

			optionsWrap.appendChild( optionsTemplate );

			typeInput.addEventListener( 'change', handleTypeChange );
			typeInput.dispatchEvent( new Event( 'change' ) );

			// const supportsTemplate = document.getElementById( `${basename}supports-template` ).content.cloneNode( true );
			// const typeSupports = optionsTemplate.querySelector( `.${basename}selectType-supports` );
			// optionsTemplate.querySelector( `.${basename}-selectType-supports` ).appendChild( supportsTemplate );
			// data[ name ].supports?.forEach( type => {
			// 	hasTransformation = ! data[ type ].transform?.includes( type );
			// 	// TODO? This ugly.
			// 	/*
			// 	const item = supportsTemplate.querySelector( `.${basename}-support\\[${type}\\]` );
			// 	if ( item ) {
			// 		item.style.display = null;

			// 		if ( ! data[ type ].transform?.includes( type ) ) {
			// 			item.querySelector( `.${basename}-transform` ).style.display = 'none';
			// 		} else {
			// 			hasTransformation = true;
			// 		}
			// 	}
			// 	*/
			// } );
		}

		// if ( data.settings ) {} // TODO? FORGO?
		if ( data.postmeta ) {
			populateOptionsTemplate( 'postmeta' );
			hasOptions = true;
		}
		if ( data.termmeta ) {
			populateOptionsTemplate( 'termmeta' );
			hasOptions = true;
		}

		if ( hasOptions )
			optionsWrap.style.display = null;

		// Register this. JS event handler should allow it only once.
		submit.addEventListener( 'click', _handleImport );
	}

	/**
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
	const _prepareUI = () => {
		const importSelector = document.getElementById( 'tsfem-e-transport-importer[choosePlugin]' );
		importSelector.addEventListener( 'change', _importerSelectPlugin );
		importSelector.dispatchEvent( new Event( 'change' ) );

		document.getElementById( 'tsfem-e-transport-copy-log' )?.addEventListener( 'click', _copyLog );
		document.getElementById( 'tsfem-e-transport-scroll-log' )?.addEventListener( 'click', _scrollLogToBottom );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 1.0.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _prepareUI );
		}
	} );
}();
window.tsfem_e_import.load();
