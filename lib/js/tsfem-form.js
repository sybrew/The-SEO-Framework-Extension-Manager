/**
 * This file holds The SEO Framework Extension Manager plugin's JS code for Extension
 * Form generation and iteration.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://wordpress.org/plugins/the-seo-framework-extension-manager/
 */

/**
 * The SEO Framework - Extension Manager plugin
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
// @output_file_name tsfem-form.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/externs/tsfem.externs.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/externs/tsfem-form.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

'use strict';

/**
 * Holds tsfemForm values in an object to avoid polluting global namespace.
 *
 * @since 1.3.0
 *
 * @constructor
 */
window[ 'tsfemForm' ] = {

	/**
	 * @since 1.3.0
	 * @access private
	 * @type {String} nonce Ajax nonce
	 */
	nonce : tsfemFormL10n.nonce,

	/**
	 * @since 1.3.0
	 * @access private
	 * @param {Object} i18n Localized strings
	 */
	i18n : tsfemFormL10n.i18n,

	/**
	 * @since 1.3.0
	 * @access private
	 * @type {String} callee Caller class
	 */
	callee : tsfemFormL10n.callee,

	/**
	 * Sets up iteration callbacks and loops through iteration events.
	 *
	 * @since 1.3.0
	 * @access private
	 * @todo disable form submission when parsing...
	 * @todo add i18n loader items to say they've been loaded
	 * @todo if $items aren't found, make sure the script can run singular when found later.
	 *
	 * @function
	 * @return {undefined}
	 */
	setupIterations: function() {

		let $items = jQuery( '.tsfem-form-iterator-selector-wrap input' );

		if ( ! $items.length )
			return;

		tsfemForm.prepareItItems( $items );
		tsfemForm.prepareCollapseItems();

		let itBuffer = 0, itTimeout = 3000,
			vBar, vBarS, vBarTimeout, vBarWidth = 0, vBuffer = 0,
			fIt,
			$item, $label, $curBar, proceed;

		//* (Re)create visual timer bar.
		let vBarReset = function() {
			vBar = document.createElement( 'span' );
			vBar.className = 'tsfem-form-iterator-timer';
			vBarS = document.createElement( 'span' );
			vBarS.style.width = '0%';
			vBar.appendChild( vBarS );
		}
		vBarReset();
		vBarTimeout = itTimeout / 100;

		let vBarGo = function() {
			vBarS.style.width = ++vBarWidth + '%';
		};
		let vBarStop = function() {
			vBarWidth = 0;
			vBarS.style.width = '0%';
		};
		let resetLoaders = function() {
			$curBar && $curBar.remove();
			$item = $label = $curBar = proceed = void 0;
			vBarReset();
			setTimeout( function() {
				$items.prop( 'disabled', false );
			}, 500 );
			$items.off( 'input', fIt );
			$items.on( 'input', fIt );
		};
		let rebuildEvents = function() {
			$items.off( 'input', fIt );
			$items = jQuery( '.tsfem-form-iterator-selector-wrap input' );
			tsfemForm.prepareItItems( $items );
			tsfemForm.prepareCollapseItems();
			tsfemForm.setupGeo();
			$items.on( 'input', fIt );
			tsfem.initDescHover();
		}

		//= Loader resetter.
		window.addEventListener( 'tsfemForm_iterate_load', resetLoaders );
		//= Event resetter.
		window.addEventListener( 'tsfemForm_iterate_complete', rebuildEvents );

		fIt = ( function( e ) {

			//* (re)set visual countdown timer.
			clearInterval( vBuffer );
			clearTimeout( itBuffer );
			vBarStop();
			vBuffer = setInterval( vBarGo, vBarTimeout );

			if ( ! $item ) {
				$item = jQuery( e.target );
				$label = $item.closest( '.tsfem-form-setting' ).find( '.tsfem-form-setting-label-inner-wrap' );

				//* Disable other iterators.
				$items.not( this ).prop( 'disabled', true );

				//* Show and assign timer.
				$label.append( vBar );
				$curBar = $label.find( 'span.tsfem-form-iterator-timer' );
			}

			itBuffer = setTimeout( function() {

				//* Disable own iterator.
				$item.prop( 'disabled', true );

				//= Race condition fix. Some browsers perform before they can paint.
				clearInterval( vBuffer );
				vBarS.style.width = '100%';

				if ( e.target.value === e.target.tsfemFormPrevValue ) {
					//= Welp... Nothing happened.
					resetLoaders();
				} else if ( e.target.value < e.target.tsfemFormPrevValue ) {
					//= Deiterate.
					tsfemForm.unloadIterations( e.target );
				} else {
					//= Iterate.
					if ( e.target.value > 30 ) {
						if ( e.target.value > 200 ) {
							tsfem.dialog( {
								'title' : tsfemFormL10n.i18n['performanceWarning'],
								'text' : [ tsfemFormL10n.i18n['itHugeConfirm'], tsfemFormL10n.i18n['aysProceed'] ],
								'confirm' : tsfemFormL10n.i18n['proceed'],
								'cancel' : tsfemFormL10n.i18n['cancel'],
							} );
						} else {
							tsfem.dialog( {
								'title' : tsfemFormL10n.i18n['performanceWarning'],
								'text' : [ tsfemFormL10n.i18n['itLargeConfirm'], tsfemFormL10n.i18n['aysProceed'] ],
								'confirm' : tsfemFormL10n.i18n['proceed'],
								'cancel' : tsfemFormL10n.i18n['cancel'],
							} );
						}

						let _cancelE, _confirmE, _resetE;

						_cancelE = function() {
							e.target.value = e.target.tsfemFormPrevValue;
							resetLoaders();
							_resetE();
						};
						_confirmE = function() {
							tsfemForm.loadIterations( e.target );
							_resetE();
						};
						_resetE = function() {
							window.removeEventListener( 'tsfem_modalCancel', _cancelE );
							window.removeEventListener( 'tsfem_modalConfirm', _confirmE );
						};
						window.addEventListener( 'tsfem_modalCancel', _cancelE );
						window.addEventListener( 'tsfem_modalConfirm', _confirmE );
					} else {
						tsfemForm.loadIterations( e.target );
					}
				}
			}, itTimeout );
		} );
		$items.off( 'input', fIt );
		$items.on( 'input', fIt );
	},

	/**
	 * Unloads iteration elements from DOM.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event.target} target
	 * @return {undefined}
	 */
	unloadIterations: function( target ) {

		if ( ! target )
			return;

		//= Get ID without '[count]', '[number]', or any other current iteration key.
		target.tsfemFormItId = target.id.slice( 0, target.id.lastIndexOf( '[' ) );

		let outputWrapper = document.getElementById( target.tsfemFormItId + '-wrapper' );

		window.dispatchEvent( new Event( 'tsfemForm_iterate_load' ) );
		jQuery( outputWrapper ).children( '.tsfem-form-collapse' ).slice( target.value ).remove();
		window.dispatchEvent( new Event( 'tsfemForm_iterate_complete' ) );
	},

	/**
	 * Loads iteration elements through AJAX.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event.target} target
	 * @return {undefined}
	 */
	loadIterations: function( target ) {

		if ( ! target )
			return;

		//= Get ID without '[count]', '[number]', or any other current iteration key.
		target.tsfemFormItId = target.id.slice( 0, target.id.lastIndexOf( '[' ) );

		let outputWrapper = document.getElementById( target.tsfemFormItId + '-wrapper' ),
			loader = document.createElement( 'div' );

		loader.className = 'tsfem-flex-status-loading tsfem-flex tsfem-flex-center';
		loader.appendChild( document.createElement( 'span' ) );
		outputWrapper.appendChild( loader );

		// Do ajax...
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'html',
			data: {
				'action' : 'tsfemForm_iterate',
				'nonce' : tsfemForm.nonce,
				'args' : {
					'caller' : target.tsfemFormItId,
					'callee' : tsfemForm.callee,
					'previousIt' : target.tsfemFormPrevValue,
					'newIt' : target.value,
				},
			},
			/**
			 * 20 seconds.
			 * It's massive and we might wish to fine-tune this based on
			 * non-cached slow PHP5.5 instances and expectancies based on
			 * item count fetching.
			 */
			timeout: 20000,
			async: true,
			success: function( response ) {
				window.dispatchEvent( new Event( 'tsfemForm_iterate_load' ) );
				jQuery( outputWrapper ).append( response );
				window.dispatchEvent( new Event( 'tsfemForm_iterate_complete' ) );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
	/*			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

				if ( jqXHR.responseText )
					tsfem.setTopNotice( -1, jqXHR.responseText );
					*/
			},
			complete: function() {
				outputWrapper.removeChild( loader );
				/*
					tsfem.updatedResponse( $loader, status, loaderText, 0 );

					button.classList.remove( ...buttonClassName.split( ' ' ) );
					button.disabled = false;*/
			},
		} );
	},

	/**
	 * Prepares iteration items by setting last known value into index.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {jQuery.element} $items jQuery input selectors
	 * @return {undefined}
	 */
	prepareItItems: function ( $items ) {

		//= Sets last known iteration values.
		$items.each( function( i, el ) {
			el.tsfemFormPrevValue = el.value;
			// el.tsfemFormHighestValue = el.tsfemFormHighestValue && el.tsfemFormHighestValue > el.value && el.tsfemFormHighestValue || el.value;
			// el.tsfemFormLowestValue  = el.tsfemFormLowestValue  && el.tsfemFormLowestValue  < el.value && el.tsfemFormLowestValue  || el.value;
		} );
	},

	/**
	 * Sets up address API callbacks.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	setupGeo: function() {

		if ( ! jQuery( '[data-geo-api-component="action"]' ).length )
			return;

		let buttonWrap;
		//= Fills buttonWrap, with GC for stray items.
		(function() {
			buttonWrap = document.createElement( 'div' );
			buttonWrap.className = 'tsfem-form-setting-action tsfem-flex';
			buttonWrap.dataset.geoApiIsButtonWrap = 1;
			buttonWrap.style.opacity = 0;
			let button = document.createElement( 'button' );
			button.className = 'tsfem-button-primary tsfem-button-green tsfem-button-cloud';
			button.innerHTML = tsfemForm.i18n['autocomplete'];
			button.type = 'button';
			button.addEventListener( 'click', function() {
				window.dispatchEvent( new Event( 'tsfem_loadGeoFromFields' ) );
			} );
			buttonWrap.appendChild( button );
		})();

		let loadButton = function( wrap ) {
			let _target = wrap && wrap.find( '.tsfem-form-multi-setting-label-inner-wrap' ),
				_node = buttonWrap.cloneNode( true );

			_target.length && _target.append( _node );
			wrap.data( 'geo-api-has-button', 1 );

			//= Dupe from tsfem.js:tsfem:dialog
			let opacity = 0, cO = 0, vTime = 300, oBuffer,
				fadeGo = function() {
					cO = ++opacity / 100;
					_node.style.opacity = cO;
					cO < 1 || clearInterval( oBuffer );
				};
			oBuffer = setInterval( fadeGo, vTime / 100 );
		};
		let unloadButton = function( wrap ) {
			let _target = wrap && wrap.find( '.tsfem-form-multi-setting-label-inner-wrap' );
			_target.find( _target.children( '[data-geo-api-is-button-wrap]' ) ).fadeOut( 300, function() {
				this.remove();
				wrap.removeData( 'geo-api-has-button' );
			} );
		};

		let getSibblings = function( wrap, target ) {
			return wrap.find( '[data-geo-api="1"]' ).not( jQuery( target ) );
		};
		let getComponents = function( target ) {
			let component = target && target.dataset.geoApiComponent || void 0;
			return component && component.split( ',' ) || [];
		};
		let validateField = function( wrap, target ) {

			let value = jQuery( target ).val();

			if ( ! value.length )
				return 0;

			let type = getComponents( target ),
				valid = 0;

			if ( type.indexOf( 'route' ) >= 0 ) {
				//* This tests street name + number.
				valid = value.match( /\w\s+\d/ ) ? 1 : 0;
			} else if ( type.indexOf( 'lat' ) >= 0 ) {
				valid = value.replace( /[^\d\.]]/, '' ) ? -1 : 0;
				if ( valid < 0 ) {
					let _val = getSibblings( wrap, target ).filter( '[data-geo-api-component="long"]' ).first().val();
					valid = _val.replace( /[^\d\.]]/, '' ) ? 1 : 0;
				}
			} else if ( type.indexOf( 'long' ) >= 0  ) {
				valid = value.replace( /[^\d\.]]/, '' ) ? -1 : 0;
				if ( valid < 0 ) {
					let _val = getSibblings( wrap, target ).filter( '[data-geo-api-component="lat"]' ).first().val();
					valid = _val.replace( /[^\d\.]]/, '' ) ? 1 : 0;
				}
			}
			return valid;
		};

		let tBuffer = 0, tTimeout = 500;
		let testButtonValidity = function( event ) {
			clearTimeout( tBuffer );
			tBuffer = setTimeout( function() {

				if ( ! event.target.dataset.geoApiComponent )
					return;

				let $wrap = jQuery( event.target ).closest( '[data-geo-api-component="action"]' );

				if ( $wrap.data( 'geo-api-has-button' ) ) {
					let _keepButton = 0;

					//= Test self.
					_keepButton = validateField( $wrap, event.target );

					if ( ! _keepButton ) {
						//= Test sibblings.
						getSibblings( $wrap, event.target ).each( function( index, element ) {
							_keepButton = _keepButton || validateField( $wrap, element );
						} );
					}
					if ( ! _keepButton ) {
						unloadButton( $wrap );
					}
				} else {
					//= Test only self.
					validateField( $wrap, event.target ) && loadButton( $wrap );
				}
			}, tTimeout );
		};

		let $input = jQuery( 'input[data-geo-api="1"]' ),
			$select = jQuery( 'select[data-geo-api="1"]' );

		if ( $input ) {
			$input.off( 'input', testButtonValidity );
			$input.on( 'input', testButtonValidity );
		}
		if ( $select ) {
			$select.off( 'change', testButtonValidity );
			$select.on( 'change', testButtonValidity );
		}
	},

	/**
	 * Prepares collapse items by adding item change listeners.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	prepareCollapseItems: function() {

		let doTitleChangeSingle = function( event ) {

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.children( 'h3' ),
				prep = $label.data( 'dyntitleprep' ),
				val = jQuery( event.target ).val();

			if ( val ) {
				$title.text( prep + ' - ' + val );
			} else {
				$title.text( prep );
			}
		};
		let doTitleChangePlural = function( event ) {

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.children( 'h3' ),
				prep = $label.data( 'dyntitleprep' ),
				vals = [];

			jQuery( event.data._tsfemFormThings ).map( function() {
				jQuery( this ).prop( 'checked' ) && vals.push( this.value );
			} );

			let val = vals && vals.join( ', ' );

			if ( val ) {
				$title.text( prep + ' - ' + val );
			} else {
				$title.text( prep );
			}
		};
		let prepareTitleChange = function( event ) {

			let $label = jQuery( event.target ).siblings( 'label' ),
				type = $label.data( 'dyntitletype' ),
				key = $label.data( 'dyntitleid' ) + '[' + $label.data( 'dyntitlekey' ) + ']';

			switch ( type ) {
				case 'single' :
					let thing = document.getElementById( key );

					jQuery( thing ).off( 'input', doTitleChangeSingle );
					jQuery( thing ).on( 'input', { '_tsfemFormLabel' : $label }, doTitleChangeSingle );
					break;

				case 'plural' :
					let $things = jQuery( document.getElementById( key ) ).find( 'input' );

					$things.off( 'change', doTitleChangePlural );
					$things.on( 'change', { '_tsfemFormLabel' : $label, '_tsfemFormThings' : $things }, doTitleChangePlural );
					break;
			}
		};

		let $input = jQuery( '.tsfem-form-collapse > input' );

		if ( $input ) {
			$input.off( 'change', prepareTitleChange );
			$input.on( 'change', prepareTitleChange );
		}
	},

	/**
	 * Saves form input through AJAX.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event jQuery event
	 * @return {(undefined|boolean)} void If form isn't valid.
	 */
	saveInput: function( event ) {

		//* For sanity, prevent regular form submission.
		tsfemForm.preventSubmit( event );

		let formId = event.target.getAttribute( 'form' ),
			form,
			button;

		if ( formId ) {
			form = document.getElementById( formId );
			if ( form ) {
				if ( ! form.checkValidity() ) {
					return;
				}
			} else {
				return tsfemForm.preventSubmit( event );
			}
			button = event.target;
		} else {
			form = event.target;
			button = document.querySelector( '[form="' + form.id + '"]' );
		}

		//let buttonClassName = 'tsfem-button-disabled tsfem-button-loading'; // ES6
		let $loader = jQuery( form ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status = 0, loaderText = '';

		// button.classList.add( ...buttonClassName.split( ' ' ) ); // ES6
		button.classList.add( 'tsfem-button-disabled', 'tsfem-button-loading' );
		button.disabled = true;

		//* Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		//* Set ajax loader.
		tsfem.setAjaxLoader( $loader );

		// Do ajax...
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfemForm_save',
				'nonce' : tsfemForm.nonce,
				'data' : jQuery( form ).serialize(),
			},
			processData: true,
			timeout: 7000,
			async: true,
			success: function( response ) {

				response = tsfem.convertJSONResponse( response );

				if ( tsfem.debug ) console.log( response );

				let data = response && response.data || void 0,
					type = response && response.type || void 0;

				if ( ! data || ! type ) {
					//* Erroneous output.
					loaderText = tsfem.i18n['InvalidResponse'];
				} else {
					let rCode = data.results && data.results.code || void 0;

					if ( rCode ) {
						status = 1;
					// let sData = data.sdata && data.sdata || void 0;
					// continue using sData..., adjust rCode if necessary

						tsfem.setTopNotice( rCode );
					} else {
						//* Erroneous output.
						loaderText = tsfem.i18n['UnknownError'];
					}
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

				tsfem.setTopNotice( 1071100 );

				if ( jqXHR.responseText )
					tsfem.setTopNotice( -1, jqXHR.responseText );
			},
			complete: function() {
				tsfem.updatedResponse( $loader, status, loaderText, 0 );

				// button.classList.remove( ...buttonClassName.split( ' ' ) ); // ES6
				button.classList.remove( 'tsfem-button-disabled', 'tsfem-button-loading' );
				button.disabled = false;
			},
		} );

		return false;
	},

	/**
	 * Prevents form submission.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event jQuery event
	 * @return {boolean} false
	 */
	preventSubmit : function( event ) {
		event.preventDefault();
		event.stopPropagation();
		return false;
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery} jQ jQuery
	 * @return {undefined}
	 */
	ready: function( jQ ) {

		//* Prepare AJAX iterations.
		jQ( document.body ).ready( tsfemForm.setupIterations );

		//* Prepare Geo API.
		jQ( document.body ).ready( tsfemForm.setupGeo );

		//* Turn form submit into an AJAX pusher.
		jQ( 'form.tsfem-form' ).on( 'submit', tsfemForm.saveInput );

	}
};
jQuery( tsfemForm.ready );

/*for ( let _c in _components ) {
	switch( _components[ _c ] ) {
		case 'route' :
			val && hasUseful++ || hasUseful--;
			break;

		case 'street_number' :
			if ( val.replace( /[^\d]/g, '' ) ) {
				hasUseful++;
			}
			break;

			break;

		case 'lat' :
			break;
		case 'long' :
			break;

		case 'locality' :
		case 'area' :
		case 'postal_code' :
		case 'country' :
			break;
	}
}*/
