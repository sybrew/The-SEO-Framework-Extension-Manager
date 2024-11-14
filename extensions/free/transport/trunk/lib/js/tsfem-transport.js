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
 * copyright (C) 2022 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
window.tsfem_e_import = function () {

	/**
	 * @since 1.0.0
	 * @access private
	 * @type {{i18n:{?},nonce:string}|null} l10n The l10n parameters set in PHP to var.
	 */
	const l10n = tsfem_e_transportL10n;

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
			( new Date() ).toLocaleTimeString() + ' :: ' + l10n.i18n.logMessages.requestImport.replace(
				'%s',
				form.querySelector( `[value="${formData.get( `${formNs}[choosePlugin]` )}"]` )?.dataset.title
			)
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

	const _sseWorkerId = 'tsfem_e_transporter_sse';
	/**
	 * Handles AJAX post requests securely.
	 *
	 * @since 1.0.0
	 * @access private
	 * @TODO use sharedworker, so we can continue reading in the background and allow for somewhat more easy continuation?
	 *
	 * @param {String}   handle   The transport action type/handle.
	 * @param {FormData} formData The formdata to post for handle.
	 * @param {?String}  logStart The starting log text. Expected to be escaped.
	 * @function
	 * @return {<Promise{resolve(message:string):void,reject(message:string):void}>}
	 */
	const _handleEventStream = ( handle, formData, logStart ) => new Promise( async ( resolve, reject ) => {

		await (
			tsfem_worker.getWorker( _sseWorkerId )
			|| tsfem_worker.spawnWorker( l10n.scripts.sseWorker, _sseWorkerId )
		);

		if ( tsfem_worker.isWorkerBusy( _sseWorkerId ) ) return reject( 'Worker busy.' );

		tsfem_worker.occupyWorker( _sseWorkerId );

		tsfem_worker.assignWorker(
			_sseWorkerId,
			{
				handle,
				formData:    formData && ( new URLSearchParams( [ ...formData.entries() ] ) ).toString(),
				logStart,
				urlData:     {
					endpoint: ajaxurl,
					base:     new URL( document.baseURI ).origin
				},
				nonce:       l10n.nonce,
				logMessages: l10n.i18n.logMessages
			},
			messageEvent => {
				if ( 'log' in messageEvent.data )
					return _log( messageEvent.data.log );

				if ( 'reject' in messageEvent.data ) {
					tsfem_worker.despawnWorker( _sseWorkerId );
					return reject( messageEvent.data.reject );
				}
				if ( 'resolve' in messageEvent.data ) {
					tsfem_worker.freeWorker( _sseWorkerId );
					return resolve( messageEvent.data.resolve );
				}
			},
			errorEvent => {
				tsfem_worker.despawnWorker( _sseWorkerId );
				reject( errorEvent?.message );
			}
		);
	} );

	/**
	 * Handles AJAX post requests securely.
	 * This probably never runs, maybe when a text-based browser is used.
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
	 * @param {String}  message The message to log.
	 * @param {Integer} newLine Number of newlines to add after message.
	 * @function
	 */
	const _log = ( message, newLine ) => {
		if ( _logger && message?.length )
			tsfem_ui.logger.queue(
				_logger,
				`\n&ratio; ${message}${( '\n'.repeat( newLine || 0 ) )}`
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

		let disableSubmit = false;

		const setDisabled = () => {
			submit.disabled = disableSubmit;
			submit.classList.toggle( 'tsfem-button-disabled', disableSubmit );
		}

		const testActiveOptions = () => {
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

			disableSubmit = ! enabledTypes.length;
			setDisabled();
		}

		const populateOptionsTemplate = type => {
			const optionsTemplate = document.getElementById( `${basename}-options-template` ).content.cloneNode( true );
			const typeInput       = optionsTemplate.querySelector( `[name^="${basename}\\[selectType\\]"]` );

			optionsTemplate.querySelector( `.${basename}-selectType-description` ).innerText = l10n.i18n.optionNames[ type ] ?? '';

			typeInput.value = type;
			typeInput.addEventListener( 'change', testActiveOptions );

			// TODO maybe later.
			// const supportsTemplate = document.getElementById( `${basename}-supports-template` ).content.cloneNode( true );
			// data[ type ].supports?.forEach( metaType => {
			// 	const item = supportsTemplate.querySelector( `.${basename}-support\\[${metaType}\\]` );

			// 	if ( item ) {
			// 		item.style.display = null;

			// 		if ( ! data[ type ].transform?.includes( metaType ) ) {
			// 			item.querySelector( `.${basename}-transform` ).style.display = 'none';
			// 		} else {
			// 			hasTransformation = true;
			// 		}
			// 	}
			// } );

			// appendChild will disconnect the reference pointer from the constant -- do it as late as possible.
			// optionsTemplate.querySelector( `.${basename}-selectType-supports` ).appendChild( supportsTemplate );
			optionsWrap.appendChild( optionsTemplate );
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

		if ( hasOptions ) {
			optionsWrap.style.display = null;
			testActiveOptions();
		} else {
			disableSubmit = true;
			setDisabled();
		}

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
