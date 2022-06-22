/**
 * This file holds Extension Manager manager-UI code for extensions.
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
 * Holds tsfem_ui values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 2.5.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfem_ui = function( $ ) {

	/**
	 * Engages switcher button reset toggle.
	 *
	 * @since 1.5.0
	 * @since 2.5.0 Moved to different object.
	 * @access private
	 *
	 * @function
	 * @param {Object} event jQuery event
	 */
	const _engageSwitcher = event => {

		const events = 'click.tsfemResetSwitcher';

		const resetSwitcher = ( event ) => {
			let $switcher = $( '.tsfem-switch-button-container > input[type="checkbox"]:checked' );

			if ( 'undefined' !== typeof $switcher && $switcher.length > 0 ) {
				let $wrap = $switcher.parents( '.tsfem-switch-button-container-wrap' );

				if ( $( event.target ).closest( $wrap ).length < 1 ) {
					$switcher.prop( 'checked', false );
					$( window ).off( events );
				}
			}
		}

		$( window ).off( events ).on( events, resetSwitcher );
	}

	/**
	 * Fades in target.
	 * Can also fade out a target when show if false. It will remove the target
	 * on completion.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 : 1. Added done parameter.
	 *                2. Added roughness to reduce FLOPS.
	 * @since 2.5.0 Moved to different object.
	 * @access public
	 *
	 * @function
	 * @param {Element} target The target to fade in (or out).
	 * @param {number} ms The time it takes to fade in (or out).
	 * @param {function} done Callback to run after transition is done.
	 * @param {boolean} show Whether to show or hide and delete the target.
	 * @return {undefined}
	 */
	const fadeIn = ( target, ms, done, show ) => {

		if ( void 0 === target || ! target instanceof HTMLElement )
			return;

		if ( ! target.style || ! ( 'opacity' in target.style ) )
			return;

		target.style.willChange = 'opacity';

		ms   = ms || 250;
		show = void 0 === show ? true : show;

		let opacity   = 0,
			start,
			progress,
			fadeGo;

		if ( show ) {
			target.style.opacity = 0;
			target.style.display = null;
			fadeGo = timestamp => {
				start    = start || timestamp;
				progress = ( timestamp - start ) / ms;

				opacity = Number.parseFloat( progress ).toPrecision( 2 ); // at 144hz, 1 paint every ~2 frames.
				target.style.opacity = opacity;
				if ( opacity >= 1 ) {
					target.style.opacity    = 1;
					target.style.willChange = 'auto';
					typeof done === 'function' && (done)();
				} else {
					target.style.opacity = opacity;
					requestAnimationFrame( fadeGo );
				}
			};
		} else {
			opacity = 100;
			fadeGo = timestamp => {
				start    = start || timestamp;
				progress = ( timestamp - start ) / ms;

				opacity = Number.parseFloat( 1 - progress ).toPrecision( 2 ); // at 144hz, 1 paint every ~2 frames.
				if ( opacity <= 0 ) {
					target.style.opacity    = 0;
					target.style.willChange = 'auto';
					//= Defer paint asynchronously to prevent bounce if there's a callback.
					setTimeout( () => { target.style.display = 'none' }, 0 );
					typeof done === 'function' && (done)();
				} else {
					target.style.opacity = opacity;
					requestAnimationFrame( fadeGo );
				}
			};
		}
		requestAnimationFrame( fadeGo );
	}

	/**
	 * Fades out and deletes target.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Added done parameter.
	 * @since 2.5.0 Moved to different object.
	 * @access public
	 *
	 * @function
	 * @param {Element} target The target to fade out.
	 * @param {number} ms The time it takes to fade out.
	 * @param {function} done Callback to run after transition is done.
	 * @return {undefined}
	 */
	const fadeOut = ( target, ms, done ) => fadeIn( target, ms, done, false );

	/**
	 * @since 1.3.0
	 * @access private
	 * @type {boolean} noticeBuffer Maintains notice loader buffer
	 */
	let _noticeBuffer = false;
	/**
	 * Gets and inserts AJAX top notice.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Now uses fallback notices on fatal AJAX error.
	 * @since 2.4.0 Now uses a lower-level nonce.
	 * @since 2.5.0 Moved to different object.
	 * @access public
	 *
	 * @function
	 * @param {number} noticeKey The notice key.
	 * @param {(string|undefined)} msg The notice message, if set this is going to be used.
	 * @return {undefined}
	 */
	const setTopNotice = ( noticeKey, msg ) => {

		// Wait a little until AJAX is resolved.
		if ( _noticeBuffer ) {
			window.setTimeout( () => {
				setTopNotice( noticeKey, msg );
			}, 500 );
			return;
		}

		_noticeBuffer = true;

		let hasMsg = msg ? 1 : 0;

		$.ajax( {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				action: 'tsfem_get_dismissible_notice',
				nonce: tsfem.insecureNonce,
				'tsfem-notice-key': noticeKey,
				'tsfem-notice-has-msg': hasMsg,
			},
			timeout: 7000,
			async: true,
		} ).done( ( response ) => {

			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			// debugger;

			if ( ! data || ! type || 'undefined' === typeof data.notice ) {
				// Erroneous output. Do nothing as this error is invoked internally.
			} else {
				let notice = data.notice;

				if ( hasMsg ) {
					notice = $( notice );
					if ( window.isRtl ) {
						notice.find( 'p' ).first().prepend( msg + ' ' );
					} else {
						notice.find( 'p' ).first().append( ' ' + msg );
					}
				}

				_appendTopNotice( notice );
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			// Simply log what happened.
			if ( tsf.l10n.states.debug ) {
				console.log( jqXHR.responseText );
				console.log( errorThrown );
			}

			// Output fallback notice.
			let fallbackNotice = hasMsg ? wp.template( 'tsfem-fbtopnotice-msg' ) : wp.template( 'tsfem-fbtopnotice' ),
				template       = fallbackNotice( { 'code' : noticeKey, 'msg' : msg } );
			_appendTopNotice( template );
		} ).always( () => {
			_noticeBuffer = false;
		} );
	}

	/**
	 * Appends top notice.
	 *
	 * @since 1.5.0
	 * @since 2.5.0 Moved to different object.
	 * @access private
	 *
	 * @function
	 * @param {string} notice The notice to append.
	 */
	const _appendTopNotice = notice => {

		let $top     = $( '#tsfem-notice-wrap' ),
			$notices = $top.children( '.tsf-notice, #tsfem-notice-wrap .notice' ),
			slideOps = {
				duration: 200,
				queue: false,
			};

		$top.css( 'willChange', 'contents' );

		//= Prevent bounce by locking maxHeight to current height.
		$notices.length > 1 && $top.css( 'maxHeight', $top.outerHeight() + 'px' );

		if ( $notices.length > 1 ) {
			// Kill them all with fire. Except one, one may stay.
			$notices.slice( 0, $notices.length - 1 ).each( function() {
				$( this ).slideUp( $.extend(
					slideOps,
					{ complete: () => this.remove() }
				) );
			} );
		}

		$( notice ).hide().appendTo( $top ).slideDown( $.extend(
			slideOps,
			//= Reset CSS.
			{ complete: () => $top.css( 'maxHeight', '' ) }
		) );

		tsf.triggerNoticeReset();
	}

	/**
	 * Generates AJAX notices and top notices based on error return values.
	 *
	 * @since 1.3.0
	 * @since 2.5.0 Moved to different object.
	 * @access public
	 *
	 * @function
	 * @param {object} response The response body.
	 * @return {undefined}
	 */
	const unexpectedAjaxErrorNotice = response => {

		response = tsf.convertJSONResponse( response ) || void 0;

		let data = response && response.data || void 0;

		if ( tsf.l10n.states.debug ) console.log( response );

		if ( data && 'results' in data && 'code' in data.results )
			setTopNotice( data.results.code, data.results.notice );
	}

	/**
	 * Creates modal dialog box from options. Also allows multiselect, instead
	 * of just confirm/cancel.
	 *
	 * NOTE: If options.select is set, you must set options.confirm to get the
	 *       return value.
	 *
	 * @since 1.3.0
	 * @since 2.5.0 Moved to different object.
	 * @access public
	 *
	 * @function
	 * @param {object} options The dialog options.
	 * @return {undefined}
	 */
	const dialog = options => {

		let title   = options.title || '',
			text    = options.text || '',
			select  = options.select || '',
			confirm = options.confirm || '',
			cancel  = options.cancel || '',
			modal   = {};

		modal.mask = document.createElement( 'div' );
		modal.mask.className = 'tsfem-modal-mask';
		modal.maskNoScroll = document.createElement( 'div' );
		modal.maskNoScroll.className = 'tsfem-modal-mask-noscroll';
		modal.mask.appendChild( modal.maskNoScroll );

		modal.container = document.createElement( 'div' );
		modal.container.className = 'tsfem-modal-container';

		modal.dialogWrap = document.createElement( 'div' );
		modal.dialogWrap.className = 'tsfem-modal-dialog-wrap';
		modal.dialogWrap.style.marginLeft = document.getElementById( 'adminmenuwrap' ).offsetWidth + 'px';
		modal.dialogWrap.style.marginTop = document.getElementById( 'wpadminbar' ).offsetHeight + 'px';

		modal.dialog = document.createElement( 'div' );
		modal.dialog.className = 'tsfem-modal-dialog';

		modal.trap = document.createElement( 'div' );
		modal.trap.className = 'tsfem-modal-trap';
		modal.trap.tabIndex = 0;
		modal.bottomTrap = modal.trap.cloneNode( false );
		modal.dialog.appendChild( modal.trap );

		modal.x = document.createElement( 'div' );
		modal.x.className = 'tsfem-modal-dismiss';
		modal.x.addEventListener( 'click', () => {
			window.dispatchEvent( new Event( 'tsfem_modalCancel' ) );
		} );
		modal.dialog.appendChild( modal.x );

		if ( title ) {
			modal.titleWrap = document.createElement( 'div' );
			modal.titleWrap.className = 'tsfem-modal-title';

			modal.titleWrapTitle = document.createElement( 'h4' );
			modal.titleWrapTitle.innerHTML = title;
			modal.titleWrap.appendChild( modal.titleWrapTitle );

			modal.dialog.appendChild( modal.titleWrap );
		}

		modal.inner = document.createElement( 'div' );
		modal.inner.className = 'tsfem-modal-inner';

		if ( text ) {
			modal.textWrap = document.createElement( 'div' );
			modal.textWrap.className = 'tsfem-modal-text';

			if ( Array.isArray( text ) ) {
				for ( let _iT in text ) {
					modal.textWrapContent = document.createElement( 'p' );
					modal.textWrapContent.innerHTML = text[ _iT ];
					modal.textWrap.appendChild( modal.textWrapContent );
				}
			} else {
				modal.textWrapContent = document.createElement( 'p' );
				modal.textWrapContent.innerHTML = text;
				modal.textWrap.appendChild( modal.textWrapContent );
			}

			modal.inner.appendChild( modal.textWrap );
		}

		let hasSelect = false;

		if ( select ) {
			hasSelect = true;

			modal.selectWrap = document.createElement( 'div' );
			modal.selectWrap.className = 'tsfem-modal-select';

			let selectWrapItem = {};

			selectWrapItem.wrap = document.createElement( 'div' );
			selectWrapItem.wrap.className = 'tsfem-modal-select-option';

			selectWrapItem.radio = document.createElement( 'input' );
			selectWrapItem.radio.setAttribute( 'type', 'radio' );
			selectWrapItem.radio.setAttribute( 'name', 'tsfem-modal-select-option-group' );
			selectWrapItem.radio.tabIndex = 0;

			selectWrapItem.label = document.createElement( 'label' );

			for ( let i in select ) {
				let wrap  = selectWrapItem.wrap.cloneNode( true ),
					radio = selectWrapItem.radio.cloneNode( false ),
					label = selectWrapItem.label.cloneNode( false );

				radio.setAttribute( 'value', i );
				label.innerHTML = select[ i ];

				//= i can be a string and integer because of "possible" JSON parsing.
				if ( i == 0 ) {
					radio.checked = true;
				}

				let id = `tsfem-dialog-option-${i}`;

				radio.setAttribute( 'id', id );
				label.setAttribute( 'for', id );

				wrap.appendChild( radio );
				wrap.appendChild( label );

				modal.selectWrap.appendChild( wrap );
			}

			modal.inner.appendChild( modal.selectWrap );
		}

		modal.dialog.appendChild( modal.inner );

		if ( confirm || cancel ) {
			modal.buttonWrap           = document.createElement( 'div' );
			modal.buttonWrap.className = 'tsfem-modal-buttons';

			if ( confirm ) {
				modal.confirmButton           = document.createElement( 'button' );
				modal.confirmButton.className = 'tsfem-modal-confirm tsfem-button-small';
				if ( hasSelect ) {
					modal.confirmButton.className += ' tsfem-button-primary tsfem-button-primary-bright';
				} else {
					modal.confirmButton.className += ' tsfem-button';
				}

				modal.confirmButton.innerHTML = confirm;
				modal.confirmButton.addEventListener( 'click', function() {
					let detail = void 0;
					if ( hasSelect ) {
						detail = { 'detail' : {
							'checked' : document.querySelector( '.tsfem-modal-select input:checked' ).value
						} };
					}
					window.dispatchEvent( new CustomEvent( 'tsfem_modalConfirm', detail ) );
				} );

				modal.buttonWrap.appendChild( modal.confirmButton );
			}

			if ( cancel ) {
				modal.cancelButton           = document.createElement( 'button' );
				modal.cancelButton.className = 'tsfem-modal-cancel tsfem-button tsfem-button-small';
				modal.cancelButton.innerHTML = cancel;
				modal.cancelButton.addEventListener( 'click', () => {
					window.dispatchEvent( new Event( 'tsfem_modalCancel' ) );
				} );

				modal.buttonWrap.appendChild( modal.cancelButton );
			}

			modal.dialog.appendChild( modal.buttonWrap );
		}

		modal.dialog.appendChild( modal.bottomTrap );

		modal.dialogWrap.appendChild( modal.dialog );
		modal.container.appendChild( modal.dialogWrap );

		document.body.appendChild( modal.mask );
		document.body.appendChild( modal.container );

		const resetFocus = () => {
			modal.trap.focus();
		};
		modal.trap.addEventListener( 'focus', resetFocus );
		modal.bottomTrap.addEventListener( 'focus', resetFocus );
		modal.trap.focus();

		fadeIn( modal.mask );
		fadeIn( modal.container );

		const preventDefault = e => {
			e.preventDefault();
		};
		modal.maskNoScroll.addEventListener( 'wheel', preventDefault );
		modal.maskNoScroll.addEventListener( 'touchmove', preventDefault );

		const resizeListener = () => {
			modal.dialogWrap.style.marginLeft = document.getElementById( 'adminmenuwrap' ).offsetWidth + 'px';
			modal.dialogWrap.style.marginTop = document.getElementById( 'wpadminbar' ).offsetHeight + 'px';
		}
		window.addEventListener( 'resize', resizeListener );

		const removeModal = () => {
			modal.maskNoScroll.removeEventListener( 'wheel', preventDefault );
			modal.maskNoScroll.removeEventListener( 'touchmove', preventDefault );
			window.removeEventListener( 'tsfem_modalCancel', removeModal );
			window.removeEventListener( 'tsfem_modalConfirm', removeModal );
			window.removeEventListener( 'resize', resizeListener );
			fadeOut( modal.mask, 250, () => modal.mask.remove() );
			fadeOut( modal.container, 250, () => modal.container.remove() );
		};

		window.addEventListener( 'tsfem_modalCancel', removeModal );
		window.addEventListener( 'tsfem_modalConfirm', removeModal );
	}

	/**
	 * Observes loggers and autoscrolls them if necessary.
	 *
	 * @since 2.6.0
	 *
	 * @param {HTMLElement} logger
	 */
	const observeLogger = logger => {
		( new MutationObserver( () => {
			// If 66% of bottom still is in view, then scroll.
			if ( logger.scrollHeight - logger.scrollTop < logger.clientHeight * ( 4 / 3 ) )
				logger.scrollTop = logger.scrollHeight;
		} ) ).observe( logger, { childList: true } );
	}

	/**
	 * Prepares logger for fast logging without freezing the interface.
	 *
	 * @since 2.6.0
	 *
	 * @return <{start:function(HTMLElement):string,stop:function(HTMLElement),queue:function(HTMLElement|string)}>
	 */
	const logger = () => {

		const FPS        = 30;
		const animations = {};
		const charLimit = {
			'hard': 0x7FFF,
			'trim': 0x1000,
		};

		let animating  = false,
			frame      = -1;

		function startAnimation() {
			if ( ! animating ) {
				animating = true;
				requestAnimationFrame( tick );
			}
		}
		function hasRunningAnimations() {
			let running = false;
			for ( let id in animations ) {
				if ( animations[ id ].animating ) {
					running = true;
					break;
				}
			}
			return running;
		}
		function tick( timestamp ) {
			let seg = Math.floor( timestamp / ( 1000 / FPS ) );

			if ( seg > frame ) {
				for ( let id in animations ) {
					animations[ id ].tStart = animations[ id ].tStart || timestamp;
					isAnimating( id ) && paintFrame( id );
				}
				frame = seg;
			}

			if ( hasRunningAnimations() ) {
				requestAnimationFrame( tick );
			} else {
				animating = false;
			}
		}
		function isAnimating( canvasId ) {
			return animations[ canvasId ] && animations[ canvasId ].animating || false;
		}
		function paintFrame( id ) {

			const queue = animations[ id ].queue;

			animations[ id ].queue = '';

			const logElement = document.getElementById( id );
			const scroll     = logElement.scrollHeight - logElement.clientHeight - logElement.scrollTop < 1;

			logElement.innerHTML += queue;

			// Trim 4k chars when hitting 32k chars.
			if ( logElement.innerHTML.length > 0x7FFF ) {
				const pretrimScrollHeight = logElement.scrollHeight;

				logElement.innerHTML = logElement.innerHTML.substring( charLimit.trim );

				if ( ! scroll ) {
					// Restore relative scrollposition if not scrolling (to prevent content jump)
					logElement.scrollTop -= ( pretrimScrollHeight - logElement.scrollHeight );
					// Let's not get in trouble with some random future browser bug.
					if ( logElement.scrollTop < 0 ) logElement.scrollTop = 0;
				}
			}

			if ( scroll )
				logElement.scrollTop = logElement.scrollHeight;
		}

		return {
			/**
			 * Starts logger animation listener.
			 *
			 * @param {HTMLElement} logger
			 * @return {String} The unique logger animation ID.
			 */
			start: logger => {

				const id = logger.id ||= `tsfem-logger-${Date.now().toString( 36 )}-${Math.floor( Math.random() * 5e5 ).toString( 36 )}`;

				// Write required entries, but don't overwrite them.
				animations[ id ] ||= {
					animating: true,
					queue: '',
				}
				// Start if already ran before and stopped.
				animations[ id ].animating = true;

				startAnimation();
				// observeLogger( logger );

				return id;
			},
			/**
			 * Stops logger animation listener.
			 *
			 * NOTE: It will perform one last paint to clear the queue.
			 *
			 * @param {HTMLElement|String} logger The logger element or animation ID.
			 */
			stop: logger => {
				const id = logger?.id || logger;
				paintFrame( id );
				animations[ id ].animating = false;
			},
			/**
			 * Stops logger animation listener.
			 *
			 * NOTE: It will perform one last paint.
			 *
			 * @param {HTMLElement|String} logger The logger element or animation ID.
			 */
			queue: ( logger, logText ) => {
				const id   = logger?.id || logger;
				const text = animations[ id ].queue += logText;
				// Trim 4k if exceeds 32k characters. Queue can grown indefinitely if user isn't focussing screen.
				if ( text > charLimit.hard ) {
					console.log( 'clearing queue' );
					animations[ id ].queue = text.queue.substring( charLimit.trim );
				}
			},
		}
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
		// Reset switcher button to default when clicked outside.
		$( '.tsfem-switch-button-container-wrap' ).on( 'click', 'label', _engageSwitcher );

		// document.querySelectorAll( '.tsfem-logger' ).forEach( el => observeLogger( el ) );
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
	}, {
		/**
		 * Constant functions.
		 * Don't overwrite these.
		 *
		 * @since 2.5.0
		 * @access public
		 */
		fadeIn,
		fadeOut,
		setTopNotice,
		unexpectedAjaxErrorNotice,
		dialog,
		logger: logger(),
	} );
}( jQuery );
window.tsfem_ui.load();
