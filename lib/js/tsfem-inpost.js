/**
 * This file holds Inpost core code for extensions.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
window.tsfem_inpost = function ( $ ) {

	/**
	 * Signifies known states on-load.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @const {string|number}         postID
	 * @const {string}                nonce
	 * @const {boolean}               isPremium
	 * @const {string}                locale
	 * @const {object<string,string>} i18n
	 */
	const postID    = tsfem_inpostL10n.post_ID;
	const nonce     = tsfem_inpostL10n.nonce;
	const isPremium = tsfem_inpostL10n.isPremium;
	const locale    = tsfem_inpostL10n.locale;
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
	const isActionableElement = element => {

		if ( ! element instanceof HTMLElement )
			return false;

		return element instanceof HTMLInputElement
			|| element instanceof HTMLSelectElement
			|| element instanceof HTMLLabelElement
			|| element instanceof HTMLButtonElement
			|| element instanceof HTMLTextAreaElement;
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
			return str.replace( /[&<>]/g, m => {
				return {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
				}[ m ];
			} );
		} else {
			return str.replace( /[&<>"']/g, m => {
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
	 * @return {Promise} The promise object.
	 */
	const promiseLoop = ( iterable, cb, timeout, stopAt = 2000 ) => new Promise( ( resolve, reject ) => {
		let its = iterable.length;

		if ( ! its ) return resolve();

		const loop = it => {
			let looper, stopper, rejector;

			// Prepare loop stopper.
			if ( stopAt ) {
				stopper = setTimeout( () => {
					// Stopper fired: Stop loop.
					clearTimeout( looper );
					rejector = setTimeout( () => {
						// Rejector passed, reject loop.
						reject();
					}, 250 );
				}, stopAt );
			}

			looper = setTimeout( () => new Promise( async ( _resolve, _reject ) => {
				try {
					await cb( iterable[ it ] );
					_resolve();
				} catch ( e ) {
					_reject();
				}
			} ).then( () => {
				if ( stopAt ) {
					clearTimeout( stopper );
					// If the rejector is enqueued, see if there are still items to loop over.
					if ( rejector ) {
						if ( it < its ) {
							// There are still items... Don't propagate loop, and let the rejector do its thing.
							return;
						} else {
							// End of loop, nothing to reject: cancel rejection.
							clearTimeout( rejector );
						}
					}
				}
				if ( ++it === its ) {
					resolve();
				} else {
					looper = null;
					loop( it );
				}
			} ).catch( () => {
				reject();
			} ), timeout );
		}
		loop( 0 );
	} );

	/**
	 * Performs inpost AJAX request.
	 *
	 * @since 2.6.0 Removed first parameter.
	 * @access public
	 *
	 * @function
	 * @param {Promise} dfd
	 * @param {object<string,*>} ajaxOps
	 * @param {object<string,string>} options
	 * @return {string}
	 */
	const doAjax = ( ajaxOps, options ) => new Promise( ( resolve,reject ) => {
		let notice,
			noticeArea = options.noticeArea,
			premium    = options.premium || false;

		if ( premium && ! isPremium ) {
			// Reject early without notice as it's forged.
			return reject();
		}

		tsf.l10n.states.debug && console.log( ajaxOps );

		$.ajax( ajaxOps ).done( response => {
			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			let data = response?.data,
				type = response?.type;

			if ( ! data || ! type ) {
				notice = {
					type: 'error',
					code: -1,
					text: i18n['InvalidResponse'],
				};
				reject();
				return;
			}

			let noticeCode = data?.results?.code,
				noticeText = data?.results?.notice,
				noticeType = data?.results?.type;

			if ( noticeCode && noticeType ) {
				notice = {
					type: noticeType,
					code: noticeCode,
					text: noticeText,
				};
			}

			if ( 'success' !== type || ! ( 'data' in data ) ) {
				reject( noticeCode );
			} else {
				resolve( data.data );
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			if ( tsf.l10n.states.debug ) {
				console.log( jqXHR.responseText );
				console.log( errorThrown );
			}
			notice = {
				type: 'error',
				code: -1,
				text: getAjaxError( jqXHR, textStatus, errorThrown ),
			};
			reject();
		} ).always( () => {
			notice && setFlexNotice( notice ).in( noticeArea );
		} );
	} );

	/**
	 * @since 2.6.1
	 * @access private
	 * @type {boolean} _appendNoticeBuffer Maintains notice appending buffer.
	 */
	let _appendNoticeBuffer = false;
	/**
	 * @since 2.6.1
	 * @access private
	 * @type {boolean} _cleanNoticeWrapBuffer Maintains notice cleaning buffer
	 */
	let _cleanNoticeWrapBuffer = false;
	/**
	 * Gets and inserts the flex notice. May invoke AJAX.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {object<string,*?>} notice The notice message
	 * @return {{in:function(string):void}}
	 */
	const setFlexNotice = notice => {
		/**
		 * Appends flex notice.
		 *
		 * @since 1.5.0
		 * @access private
		 *
		 * @function
		 * @param {string} notice The notice to append.
		 * @param {string} wrapper The querySelector for the notice wrapper.
		 */
		const appendFlexNotice = ( notice, wrapper ) => {

			const noticeWrap = document.querySelector( wrapper ),
				  notices    = noticeWrap.querySelectorAll( '.tsf-notice, .notice' );

			const slideDuration = 200;
			const slideTiming   = {
				duration:   slideDuration,
				iterations: 1,
			};

			noticeWrap.style.willChange = 'contents';

			if ( notices.length > 1 ) {
				// Prevent bounce by locking maxHeight to current height. Use subpixels to prevent minor text shift.
				noticeWrap.style.maxHeight = `${noticeWrap.getBoundingClientRect().height}px`;
				noticeWrap.style.overflow  = 'hidden';

				// Kill them all with fire. Except one, one may stay.
				Array.from( notices ).slice( 0, notices.length - 1 ).forEach(
					el => {
						el.style.transformOrigin = 'bottom';
						el.animate(
							[
								{ transform: 'scaleY(1)', maxHeight: `${el.clientHeight}px`, opacity: 1 },
								{ transform: 'scaleY(0)', maxHeight: 0, opacity: 0 },
							],
							slideTiming
						);
						setTimeout( () => el.remove(), slideDuration );
					}
				);
			}

			const temp = document.createElement( 'template' );
			temp.innerHTML = notice;
			noticeWrap.append( temp.content );

			const lC = noticeWrap.lastChild;

			lC.style.transformOrigin = 'bottom';
			lC.animate(
				[
					{ transform: 'scaleY(0)', maxHeight: 0, opacity: 1 },
					{ transform: 'scaleY(1)', maxHeight: `${lC.clientHeight}px`, opacity: 1 },
				],
				slideTiming
			);
			// Debounce clearing of overflowing when multiple notices are processed in quick succession.
			clearTimeout( _cleanNoticeWrapBuffer );
			_cleanNoticeWrapBuffer = setTimeout( () => {
				noticeWrap.style.maxHeight = null;
				noticeWrap.style.overflow  = null;
			}, slideDuration );

			tsf.triggerNoticeReset();
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
					action: 'tsfem_inpost_get_dismissible_notice',
					post_ID: postID,
					nonce: nonce,
					'tsfem-notice-key': noticeKey,
					'tsfem-notice-has-msg': hasMsg,
				},
				timeout: 7000,
			} ).done( response => {

				response = tsf.convertJSONResponse( response );

				tsf.l10n.states.debug && console.log( response );

				let data = response?.data,
					type = response?.type;

				if ( ! data || ! type || 'undefined' === typeof data.notice ) {
					// Erroneous output. Do nothing as this error is invoked internally.
				} else {
					let notice = data.notice;

					if ( hasMsg ) {
						const temp = document.createElement( 'div' );
						temp.innerHTML = notice;
						if ( window.isRtl ) {
							temp.querySelector( 'p' )?.insertAdjacentHTML( 'beforebegin', `${msg} ` );
						} else {
							temp.querySelector( 'p' )?.insertAdjacentHTML( 'beforeend', ` ${msg}` );
						}
						notice = temp.innerHTML;
					}

					output = notice;
				}
			} ).fail( ( jqXHR, textStatus, errorThrown ) => {
				// Simply log what happened.
				if ( tsf.l10n.states.debug ) {
					console.log( jqXHR.responseText );
					console.log( errorThrown );
				}

				// Output fallback notice.
				let template = hasMsg ? wp.template( 'tsfem-inpost-notice-error' ) : wp.template( 'tsfem-inpost-notice-5xx' ),
					notice = template( { code: noticeKey, msg } );

				output = notice;
			} ).always( () => {
				dfd.resolve( output );
			} );

			return dfd.promise();
		}

		return {
			/**
			 * @param {string} wrapper The querySelector for the notice wrapper.
			 */
			in: wrapper => {
				if ( ! notice )
					return;

				// One notice at a time. This might stack up depending on AJAX.
				if ( _appendNoticeBuffer ) {
					setTimeout( () => {
						setFlexNotice( notice ).in( wrapper );
					}, 500 );
					return;
				}

				_appendNoticeBuffer = true;

				let type = notice.type || 'error',
					code = notice.code || void 0,
					text = notice.text || '';

				if ( void 0 === code ) {
					retrieveNotice( -1, text ).always( notice => {
						appendFlexNotice( notice, wrapper );
						_appendNoticeBuffer = false;
					} );
				} else if ( '' === text ) {
					retrieveNotice( code, '' ).always( notice => {
						appendFlexNotice( notice, wrapper );
						_appendNoticeBuffer = false;
					} );
				} else {
					const template = wp.template( `tsfem-inpost-notice-${type}` );

					appendFlexNotice( template( {
						code: code,
						msg:  text,
					} ), wrapper );

					_appendNoticeBuffer = false;
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

		if ( tsf.l10n.states.debug ) {
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
			element.classList.remove( `tsfem-e-inpost-icon-${c}` );
			c === to && element.classList.add( `tsfem-e-inpost-icon-${c}` );
		} );
	}

	var fadeBuffer = {}, fadeDfd = {};
	/**
	 * Fades in target.
	 * Can also fade out a target when show if false. It will remove the target
	 * on completion.
	 *
	 * @since 1.5.0
	 * @since 2.5.0 1. Prevented most race conditions.
	 *              2. Increased entropy.
	 *              3. Now uses the animationframe API.
	 * @access public
	 *
	 * @function
	 * @param {Element}                     target The target to fade in (or out).
	 * @param {number}                      ms     The time it takes to fade in (or out).
	 * @param {(Object<string,*>|function)} args   Callback arguments or callback. Not null.
	 * @param {boolean}                     show   Whether to show or hide and delete the target. Internal use.
	 * @return {undefined}
	 */
	const fadeIn = ( target, ms, args, show ) => {

		if ( ! target || ! target instanceof HTMLElement )
			return;

		target.style.willChange = 'opacity';

		let cb = false, promise = false;
		if ( typeof args === 'function' ) {
			cb      = args;
			promise = true;
		} else if ( typeof args === 'object' ) {
			cb      = args?.cb || false;
			promise = args?.promise || false;
		}

		ms ||= 250;
		show = void 0 === show ? true : show;
		// Increased entropy.
		let key = target.dataset.tsfemFadeId || '_' + crypto.randomUUID();

		let opacity = 0,
			fadeGo,
			start,
			progress;

		target.dataset.tsfemFadeId = key;

		if ( typeof fadeBuffer[ key ] !== 'undefined' ) {
			cancelAnimationFrame( fadeBuffer[ key ].frame );

			// Finish the animation. This can still race...?
			if ( 'show' === fadeBuffer[ key ].type ) {
				target.style.opacity = 1;
				target.style.display = null;
			} else {
				target.style.opacity = 0;
				target.style.display = 'none';
			}
			delete fadeBuffer[ key ];

			// Shouldn't we reject?
			if ( typeof fadeDfd[ key ] !== 'undefined' ) fadeDfd[ key ].resolve();
			delete fadeDfd[ key ];
		}
		if ( promise && cb ) {
			fadeDfd[ key ] = new Promise;
			(()=>{
				fadeDfd[ key ].then( () => {
					delete fadeDfd[ key ];
					(cb)();
				} );
			})(cb);
		}

		fadeBuffer[ key ] = {
			type: show ? 'show' : 'hide',
			frame: void 0,
		};

		if ( show ) {
			target.style.display = null; // affects race condition.
			target.style.opacity = 0;    // affects race condition.
			fadeGo = timestamp => {
				start    = undefined === start ? timestamp : start;
				progress = ( timestamp - start ) / ms;

				opacity = +Number.parseFloat( progress ).toPrecision( 3 ); // at 144hz, 1 paint every ~2 frames.

				target.style.opacity = opacity;
				// target.style.display = null;

				if ( opacity >= 1 ) {
					cancelAnimationFrame( fadeBuffer[ key ].frame );

					target.style.opacity    = 1;
					target.style.display    = null;
					target.style.willChange = 'auto';

					// if ( 'none' === target.style.display ) target.style.display = null; // introduces race condition.
					delete target.dataset.tsfemFadeId;

					delete fadeBuffer[ key ];
					if ( promise ) {
						fadeDfd?.[ key ].resolve();
					} else {
						cb && (cb)();
					}
				} else {
					fadeBuffer[ key ].frame = requestAnimationFrame( fadeGo );
				}
			}
		} else {
			target.style.display = null; // affects race condition.
			target.style.opacity = 1; // affects race condition.
			fadeGo = timestamp => {
				start    = undefined === start ? timestamp : start;
				progress = ( timestamp - start ) / ms;

				opacity = +Number.parseFloat( 1 - progress ).toPrecision( 3 ); // at 144hz, 1 paint every ~2 frames.

				target.style.opacity = opacity;
				target.style.display = null;

				if ( opacity <= 0 ) {
					cancelAnimationFrame( fadeBuffer[ key ].frame );

					target.style.opacity    = 0;
					target.style.display    = 'none'; // Prevents bounce, ! introduces race condition.
					target.style.willChange = 'auto';

					delete target.dataset.tsfemFadeId;

					delete fadeBuffer[ key ];
					if ( promise ) {
						fadeDfd?.[ key ].resolve();
					} else {
						cb && (cb)();
					}
				} else {
					fadeBuffer[ key ].frame = requestAnimationFrame( fadeGo );
				}
			}
		}
		fadeBuffer[ key ].frame = requestAnimationFrame( fadeGo );
	}

	/**
	 * Fades out and deletes target.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @function
	 * @param {Element}          target The target to fade out.
	 * @param {number}           ms     The time it takes to fade out.
	 * @param {Object<string,*>} args   Callback arguments.
	 * @return {undefined}
	 */
	const fadeOut = ( target, ms, args ) => {
		fadeIn( target, ms, args, false );
	}

	/**
	 * Debounces the input function.
	 * TODO deprecate this and use TSF 5.0.7's debouncer instead.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @function
	 * @param {CallableFunction} func
	 * @param {Int} timeout
	 * @return {Function}
	 */
	const debounce = ( func, timeout = 0 ) => {
		let timeoutId;
		return ( ...args ) => {
			clearTimeout( timeoutId );
			return {
				timeoutId: timeoutId = setTimeout( () => func( ...args ), timeout ),
				cancel: () => clearTimeout( timeoutId ),
			};
		};
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
		i18n
	}, {
		/**
		 * Constant functions.
		 * Don't overwrite these.
		 *
		 * @since 1.5.0
		 * @since 2.7.0 Added debounce.
		 * @access public
		 */
		isActionableElement,
		escapeStr,
		promiseLoop,
		doAjax,
		setFlexNotice,
		getAjaxError,
		setIconClass,
		fadeOut,
		fadeIn,
		debounce,
	} );
}( jQuery );
window.tsfem_inpost.load();
