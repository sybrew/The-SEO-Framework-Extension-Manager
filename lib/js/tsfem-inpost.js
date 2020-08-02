/**
 * This file holds Inpost core code for extensions.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfem_inpost values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 1.5.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfem_inpost = function( $ ) {

	/**
	 * Signifies known states on-load.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @const {string|number}         postID
	 * @const {string}                nonce
	 * @const {boolean}               rtl
	 * @const {boolean}               isPremium
	 * @const {string}                locale
	 * @const {boolean}               debug
	 * @const {object<string,string>} i18n
	 */
	const postID    = tsfem_inpostL10n.post_ID;
	const nonce     = tsfem_inpostL10n.nonce;
	const rtl       = tsfem_inpostL10n.rlt;
	const isPremium = tsfem_inpostL10n.isPremium;
	const locale    = tsfem_inpostL10n.locale;
	const debug     = tsfem_inpostL10n.debug;
	const i18n      = tsfem_inpostL10n.i18n;

	/**
	 * Determines if element is actionable by default.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {HTMLElement} element
	 * @return {string}
	 */
	const isActionableElement = ( element ) => {

		if ( ! element instanceof HTMLElement )
			return false;

		return element instanceof HTMLInputElement
			|| element instanceof HTMLSelectElement
			|| element instanceof HTMLLabelElement
			|| element instanceof HTMLButtonElement
			|| element instanceof HTMLTextAreaElement
			;
	}

	/**
	 * Escapes HTML input.
	 *
	 * @since 1.5.0
	 *
	 * @function
	 * @param {string}              str
	 * @param {(boolean|undefined)} noquotes Whether to exclude quotes.
	 * @return {string}
	 */
	const escapeStr = ( str, noquotes ) => {
		if ( ! str.length ) return '';

		if ( noquotes ) {
			return str.replace( /[&<>]/g, ( m ) => {
				return {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
				}[ m ];
			} );
		} else {
			return str.replace( /[&<>"']/g, ( m ) => {
				return {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#039;'
				}[ m ];
			} );
		}
	}

	/**
	 * Promises to do a loop, and tells when the loop is done.
	 *
	 * @since 1.5.0
	 * @since 2.0.2: 1. Can now halt on heavy workloads, although the workload can't be terminated.
	 *               2. Can now reject promises.
	 *               3. Added stopAt parameter, defaults to 2000.
	 * @access public
	 *
	 * @function
	 * @param {(array|object<number,?>)} iterable The items to loop over.
	 * @param {function}                 cb       The callback function returning a Promise.
	 * @param {number|undefined}         timeout  The iteration timeout. Optional.
	 * @param {number|undefined}         stopAt   The iteration anti-lag blocker. Optional. Defaults to 2000 ms.
	 *                                            Set to 0 to turn off.
	 * @return {jQuery.Deferred|Promise} The promise object.
	 */
	const promiseLoop = ( iterable, cb, timeout, stopAt = 2000 ) => {
		let $dfd = $.Deferred(),
			its = iterable.length;

		if ( ! its ) return $dfd.resolve();

		const loop = ( it ) => {
			let looper, stopper, rejector;

			// Prepare loop stopper.
			if ( stopAt ) {
				stopper = setTimeout( () => {
					// Stopper fired: Stop loop.
					clearTimeout( looper );
					rejector = setTimeout( () => {
						// Rejector passed, reject loop.
						$dfd.reject();
					}, 250 );
				}, stopAt );
			}

			looper = setTimeout( () => {
				$.when( cb( iterable[ it ] ) ).done( () => {

					if ( stopAt ) {
						clearTimeout( stopper );
						// If the rejector is enqueued, see if there are still items to loop over.
						if ( rejector ) {
							if ( it < its ) {
								// There are still items... Cancel loop and let the rejector do its thing.
								return;
							} else {
								// End of loop, nothing to reject: cancel rejection.
								clearTimeout( rejector );
							}
						}
					}

					if ( ++it === its ) {
						$dfd.resolve();
					} else {
						loop( it );
						looper = null;
					}
				} ).fail( () => {
					$dfd.reject();
				} );
			}, timeout );
		}
		loop( 0 );

		return $dfd.promise();
	}

	let workers = {}, activeWorkers = {};
	/**
	 * Sets Worker status by ID.
	 *
	 * @since 2.0.2
	 * @access private
	 *
	 * @function
	 * @param {String} id
	 * @param {String|undefined} to Either 'busy' or anything else.
	 */
	const setWorkerStatus = ( id, to ) => {
		if ( 'busy' === to ) {
			activeWorkers[ id ] = true;
		} else {
			delete activeWorkers[ id ];
		}
	}

	/**
	 * Occupies Worker by ID.
	 *
	 * @since 2.0.2
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const occupyWorker = id => setWorkerStatus( id, 'busy' );

	/**
	 * Deoccupies Worker by ID.
	 *
	 * @since 2.0.2
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const freeWorker = id => setWorkerStatus( id, 'clear' );

	/**
	 * Tells Worker status by ID.
	 *
	 * @since 2.0.2
	 * @access public
	 *
	 * @param {String} id
	 * @return {Boolean}
	 */
	const isWorkerBusy = ( id ) => id in activeWorkers;

	/**
	 * Assigns a new Worker by ID.
	 *
	 * @since 2.0.2
	 * @access public
	 *
	 * @function
	 * @param {String} file
	 * @param {String} id
	 * @return {Worker}
	 */
	const spawnWorker = ( file, id ) => workers[ id ] = new Worker( file );

	/**
	 * Returns an active Worker by ID.
	 *
	 * @since 2.0.2
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 * @return {Worker|void}
	 */
	const getWorker = ( id ) => id in workers && workers[ id ] || void 0;

	/**
	 * Stops Worker by ID.
	 *
	 * Worker needs to be respawned after terminated. Alternatively, use despawnWorker.
	 * @see tsfem_inpost.spawnWorker()
	 * @see tsfem_inpost.despawnWorker()
	 *
	 * @since 2.0.2
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const stopWorker = ( id ) => {
		if ( workers[ id ] ) {
			workers[ id ].terminate();
			freeWorker( id );
		}
	}

	/**
	 * Stops and removes Worker by ID.
	 *
	 * @since 2.0.2
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const despawnWorker = ( id ) => {
		if ( workers[ id ] ) {
			stopWorker( id );
			delete workers[ id ];
		}
	}

	/**
	 * Tells worker to process input data via postMessage.
	 *
	 * @since 2.0.2
	 * @access public
	 * @TODO move these instances to tsfem-worker.js, and use the Promise object, instead of jQuery.
	 *
	 * @function
	 * @param {String} id
	 * @param {*}      data
	 * @return {jQuery.Deferred|Promise} The promise object.
	 */
	const tellWorker = ( id, data ) => {
		let $dfd = $.Deferred();

		setTimeout( () => {
			let worker = getWorker( id );

			if ( ! worker ) return $dfd.reject();
			worker.onmessage = ( oEvent ) => {
				if ( 'error' in oEvent.data ) {
					// debug && console.log( oEvent.data.error );
					console.log( oEvent.data.error ); // DEBUG: phase this out later?
					return $dfd.reject( oEvent.data.error );
				}
				return $dfd.resolve( oEvent.data );
			}
			worker.onerror = ( error ) => {
				// debug && console.log( error );
				console.log( error ); // DEBUG: phase this out later?
				return $dfd.reject( error );
			}

			worker.postMessage( {
				id,
				data
			} );
		} );

		return $dfd.promise();
	}

	/**
	 * Performs inpost AJAX request.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {jQuery.defered} dfd
	 * @param {object<string,*>} ajaxOps
	 * @param {object<string,string>} options
	 * @return {string}
	 */
	const doAjax = ( dfd, ajaxOps, options ) => {
		let notice,
			noticeArea = options.noticeArea,
			premium = options.premium || false;

		if ( premium && ! isPremium ) {
			//? Reject early without notice as it's forged.
			dfd.reject();
			return;
		}

		debug && console.log( ajaxOps );

		$.ajax( ajaxOps ).done( ( response ) => {
			dfd.notify();

			response = convertJSONResponse( response );

			debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			if ( ! data || ! type ) {
				dfd.reject();
				notice = {
					type: 'error',
					code: -1,
					text: i18n['InvalidResponse'],
				};
				return;
			}

			let noticeCode = data.results && data.results.code || void 0,
				noticeText = data.results && data.results.notice || void 0,
				noticeType = data.results && data.results.type || void 0;

			if ( noticeCode && noticeType ) {
				notice = {
					type: noticeType,
					code: noticeCode,
					text: noticeText,
				};
			}

			if ( 'success' !== type || ! ( 'data' in data ) ) {
				dfd.reject( noticeCode );
			} else {
				dfd.resolve( data.data );
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			dfd.reject();
			if ( debug ) {
				console.log( jqXHR.responseText );
				console.log( errorThrown );
			}
			notice = {
				type: 'error',
				code: -1,
				text: getAjaxError( jqXHR, textStatus, errorThrown ),
			};
		} ).always( () => {
			dfd.notify();
			notice && setFlexNotice( notice ).in( noticeArea );
		} );
	}

	/**
	 * Tries to convert JSON response to values if not already set.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {(object|string|undefined)} response
	 * @return {(object|undefined)}
	 */
	const convertJSONResponse = ( response ) => {

		let testJSON = response && response.json || void 0,
			isJSON = 1 === testJSON;

		if ( ! isJSON ) {
			let _response = response;
			try {
				response = JSON.parse( response );
				isJSON = true;
			} catch ( error ) {
				isJSON = false;
				// Reset response.
				response = _response;
			}
		}

		return response;
	}

	var noticeBuffer;
	/**
	 * Gets and inserts the flex notice. May invoke AJAX.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {object<string,*?>} notice The notice message
	 * @return {Object<in:string>}
	 */
	const setFlexNotice = ( notice ) => {
		/**
		 * Appends flex notice.
		 *
		 * @since 1.5.0
		 * @access private
		 *
		 * @function
		 * @param {string} notice The notice to append.
		 */
		const appendFlexNotice = ( notice, wrapper ) => {

			let $wrapper = $( wrapper ),
				$notices = $wrapper.children( '.tsfem-notice, .tsfem-notice-wrap .notice' );

			if ( $notices.length > 1 ) {
				// Kill them all with fire.
				$notices.slice( 0, $notices.length - 1 ).each( function() {
					$( this ).slideUp( 200, function() {
						this.remove();
					} );
				} );
			}

			$( notice ).hide().appendTo( $wrapper ).slideDown( 200 );

			let $dismiss = $( '.tsfem-flex-settings-notification-area .tsfem-dismiss' );

			const dismissNotice = ( event ) => {
				$( event.target ).closest( '.tsfem-notice' ).slideUp( 200, function() {
					this.remove();
				} );
			};

			$dismiss.off( 'click', dismissNotice ).on( 'click', dismissNotice );
		}
		/**
		 * Gets and inserts AJAX inpost-flex notice.
		 *
		 * @since 1.5.0
		 * @access public
		 *
		 * @function
		 * @param {number} noticeKey The notice key.
		 * @param {(string|undefined)} msg The notice message, if set this is going to be used.
		 * @return {jQuery.Deferred}
		 */
		const retrieveNotice = ( noticeKey, msg ) => {

			let dfd = $.Deferred();

			let hasMsg = msg ? 1 : 0,
				output = '';

			$.ajax( {
				method: 'POST',
				url: ajaxurl,
				datatype: 'json',
				data: {
					'action' : 'tsfem_inpost_get_dismissible_notice',
					'post_ID' : postID,
					'nonce' : nonce,
					'tsfem-notice-key' : noticeKey,
					'tsfem-notice-has-msg' : hasMsg,
				},
				timeout: 7000,
				async: true,
			} ).done( ( response ) => {

				response = convertJSONResponse( response );

				debug && console.log( response );

				let data = response && response.data || void 0,
					type = response && response.type || void 0;

				if ( ! data || ! type || 'undefined' === typeof data.notice ) {
					//* Erroneous output. Do nothing as this error is invoked internally.
				} else {
					let notice = '';

					if ( hasMsg ) {
						notice = $( data.notice );
						if ( rtl ) {
							notice.find( 'p' ).first().prepend( msg + ' ' );
						} else {
							notice.find( 'p' ).first().append( ' ' + msg );
						}
					} else {
						notice = data.notice;
					}

					output = notice;
				}
			} ).fail( ( jqXHR, textStatus, errorThrown ) => {
				// Simply log what happened.
				if ( debug ) {
					console.log( jqXHR.responseText );
					console.log( errorThrown );
				}

				// Output fallback notice.
				let template = hasMsg ? wp.template( 'tsfem-inpost-notice-error' ) : wp.template( 'tsfem-inpost-notice-5xx' ),
					notice = template( { 'code' : noticeKey, 'msg' : msg } );

				output = notice;
			} ).always( () => {
				dfd.resolve( output );
			} );

			return dfd.promise();
		}

		return {
			in: ( wrapper ) => {
				if ( ! notice ) {
					return;
				}

				//* One notice at a time. This might stack up depending on AJAX.
				if ( noticeBuffer ) {
					setTimeout( () => {
						setFlexNotice( notice ).in( wrapper );
					}, 500 );
					return;
				}

				noticeBuffer = true;

				let type = notice.type || 'error',
					code = notice.code || void 0,
					text = notice.text || '';

				if ( void 0 === code ) {
					retrieveNotice( -1, text ).always( ( notice ) => {
						appendFlexNotice( notice, wrapper );
						noticeBuffer = false;
					} );
				} else if ( '' === text ) {
					retrieveNotice( code, '' ).always( ( notice ) => {
						appendFlexNotice( notice, wrapper );
						noticeBuffer = false;
					} );
				} else {
					let template = wp.template( 'tsfem-inpost-notice-' + type );
						notice   = template( {
							code: code,
							msg:  text,
						} );

					appendFlexNotice( notice, wrapper );
					noticeBuffer = false;
				}
			}
		};
	}

	/**
	 * Returns bound AJAX reponse error with the help from i18n.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.xhr|Object)} jqXHR
	 * @param {String} textStatus
	 * @param {String} errorThrown
	 * @return {String}
	 */
	const getAjaxError = ( jqXHR, textStatus, errorThrown ) => {

		if ( debug ) {
			console.log( jqXHR.responseText );
			console.log( errorThrown );
		}

		let _error = '';

		switch ( errorThrown ) {
			case 'abort': // client error, no code.
			case 'timeout': // 408
				_error = i18n['TimeoutError'];
				break;

			case 'Bad Request': // 400
				_error = i18n['BadRequest'];
				break;

			case 'Internal Server Error': // 500
				_error = i18n['FatalError'];
				break;

			case 'parsererror': // PHP error, no code.
				_error = i18n['ParseError'];
				break;

			default:
				// @TODO use ajaxOptions.status? i.e. 400, 401, 402, 503.
				_error = i18n['UnknownError'];
				break;
		}

		return _error;
	}

	/**
	 * Exchanges icon class of element to another value.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {Element} element
	 * @param {string}  to
	 * @return {undefined}
	 */
	const setIconClass = ( element, to ) => {

		if ( element instanceof NodeList ) {
			element.forEach( el => setIconClass( el, to ) );
			return;
		}
		if ( ! element || ! element instanceof Element )
			return;

		let classes = [
			'edit',
			'loading',
			'unknown',
			'error',
			'bad',
			'warning',
			'okay',
			'good',
		];

		classes.forEach( c => {
			element.classList.remove( 'tsfem-e-inpost-icon-' + c );
			c === to && element.classList.add( 'tsfem-e-inpost-icon-' + c );
		} );
	}

	var fadeBuffer = {}, fadeDfd = {};
	/**
	 * Fades in target.
	 * Can also fade out a target when show if false. It will remove the target
	 * on completion.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {Element} target The target to fade in (or out).
	 * @param {number} ms The time it takes to fade in (or out).
	 * @param {(Object<string,*>|function)} args Callback arguments or callback. Not null.
	 * @param {boolean} show Whether to show or hide and delete the target. Internal use.
	 * @return {undefined}
	 */
	const fadeIn = ( target, ms, args, show ) => {

		if ( ! target || ! target instanceof HTMLElement )
			return;

		target.style.willChange = 'opacity';

		let cb = false, promise = false;
		if ( typeof args === 'function' ) {
			cb = args;
			promise = true;
		} else if ( typeof args === 'object' ) {
			cb = typeof args.cb === 'undefined' && false || args.cb;
			promise = typeof args.promise === 'undefined' && true || args.promise;
		}

		ms = ms || 250;
		show = void 0 === show ? true : show;

		let opacity = 0,
			cO = 0,
			roughness = 10,
			fadeGo,
			key = target.dataset.tsfemFadeId || '_' + Math.random().toString(22).substr(2,10);

		target.dataset.tsfemFadeId = key;

		if ( typeof fadeBuffer[ key ] !== 'undefined' ) {
			clearInterval( fadeBuffer[ key ] );
			if ( typeof fadeDfd[ key ] !== 'undefined' ) fadeDfd[ key ].resolve();
		}
		if ( promise && cb ) {
			fadeDfd[ key ] = $.Deferred();
			(()=>{
				$.when( fadeDfd[ key ] ).done( () => {
					delete fadeDfd[ key ];
					(cb)();
				} );
			})(cb);
		}

		if ( show ) {
			/**
			 * TODO convert to requestAnimationFrame
			 */
			target.style.opacity = 0;
			target.style.display = null;  //? affects race condition.
			fadeGo = () => {
				cO = ( opacity += roughness ) / 100;
				target.style.opacity = cO;
				if ( cO >= 1 ) {
					clearInterval( fadeBuffer[ key ] );
					setTimeout( () => target.style.opacity = 1, 0 );
					target.style.display = null;
					if ( promise ) {
						typeof fadeDfd[ key ] !== 'undefined' && fadeDfd[ key ].resolve();
					} else {
						cb && (cb)();
					}
					if ( 'none' === target.style.display ) target.style.display = null;
					target.style.willChange = 'auto';
					delete target.dataset.tsfemFadeId;
				}
			}
		} else {
			/**
			 * TODO convert to requestAnimationFrame
			 */
			opacity = target.style.opacity * 100;
			fadeGo = () => {
				cO = ( opacity -= roughness ) / 100;
				target.style.opacity = cO;
				if ( cO <= 0 ) {
					clearInterval( fadeBuffer[ key ] );
					target.style.opacity = 0;
					if ( promise ) {
						typeof fadeDfd[ key ] !== 'undefined' && fadeDfd[ key ].resolve();
					} else {
						cb && (cb)();
					}
					target.style.display = 'none'; //?! Prevents bounce, introduces race condition.
					target.style.willChange = 'auto';
					delete target.dataset.tsfemFadeId;
				}
			}
		}
		fadeBuffer[ key ] = setInterval( fadeGo, ( ms * roughness ) / 100 );
	}

	/**
	 * Fades out and deletes target.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {Element} target The target to fade out.
	 * @param {number} ms The time it takes to fade out.
	 * @param {Object<string,*>} args Callback arguments.
	 * @return {undefined}
	 */
	const fadeOut = ( target, ms, args ) => {
		fadeIn( target, ms, args, false );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 *
		 * @since 1.5.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {}
	}, {
		/**
		 * Constant variables.
		 * Don't overwrite these.
		 *
		 * @since 1.5.0
		 * @access public
		 */
 		postID,
 		nonce,
 		isPremium,
 		locale,
 		debug,
		i18n
	}, {
		/**
		 * Constant functions.
		 * Don't overwrite these.
		 *
		 * @since 1.5.0
		 * @access public
		 */
		isActionableElement,
		escapeStr,
		promiseLoop,
		occupyWorker,
		freeWorker,
		isWorkerBusy,
		spawnWorker,
		getWorker,
		stopWorker,
		despawnWorker,
		tellWorker,
		doAjax,
		convertJSONResponse,
		setFlexNotice,
		getAjaxError,
		setIconClass,
		fadeOut,
		fadeIn
	} );
}( jQuery );
window.tsfem_inpost.load();
