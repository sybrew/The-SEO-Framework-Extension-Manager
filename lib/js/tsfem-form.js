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
	 * @todo maintain disabled form submission when parsing...
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
			vBarSmoothness = 2,
			fIt,
			$item, $label, $curBar,
			lastTarget;

		//* (Re)create visual timer bar.
		const vBarReset = function() {
			vBar = document.createElement( 'span' );
			vBar.className = 'tsfem-form-iterator-timer';
			vBarS = document.createElement( 'span' );
			vBarS.style.width = '0%';
			vBar.appendChild( vBarS );
		}
		vBarReset();
		vBarTimeout = itTimeout / ( 100 * vBarSmoothness );

		const vBarGo = function() {
			vBarS.style.width = ++vBarWidth / vBarSmoothness + '%';
		}
		const vBarStop = function() {
			vBarWidth = 0;
			vBarS.style.width = '0%';
			vBar.classList.remove( 'tsfem-form-iterator-timer-invalid' );
		}
		const vBarInvalid = function() {
			vBarWidth = 100;
			vBarS.style.width = vBarWidth + '%';
			vBar.classList.add( 'tsfem-form-iterator-timer-invalid' );
		}
		const resetLoaders = function() {
			$curBar && $curBar.remove();
			$item = $label = $curBar = void 0;
			vBarReset();
			setTimeout( function() {
				$items.prop( 'disabled', false );
			}, 500 );
			$items.off( 'input', fIt );
			$items.on( 'input', fIt );
		}
		const rebuildEvents = function() {
			$items.off( 'input', fIt );
			$items = jQuery( '.tsfem-form-iterator-selector-wrap input' );
			tsfemForm.prepareItItems( $items );
			tsfemForm.prepareCollapseItems();
			tsfemForm.setupGeo();
			$items.on( 'input', fIt );
			tsfem.initDescHover();
			lastTarget = void 0;
		}
		const undoInput = function() {
			if ( lastTarget ) lastTarget.value = lastTarget.tsfemFormPrevValue;
		}

		window.addEventListener( 'tsfem_iterationFail', undoInput );

		//= Loader resetter.
		window.addEventListener( 'tsfemForm_iterate_load', resetLoaders );
		window.addEventListener( 'tsfem_iterationFail', resetLoaders );
		//= Event resetter.
		window.addEventListener( 'tsfemForm_iterate_complete', rebuildEvents );
		window.addEventListener( 'tsfem_iterationFail', rebuildEvents );

		/**
		 * Loads iterations based on timeouts.
		 * fIt = Function Iterations.
		 * @param {jQuery.event} e The input event.
		 */
		fIt = function( e ) {

			//* (re)set visual countdown timer.
			clearInterval( vBuffer );
			clearTimeout( itBuffer );
			vBarStop();
			tsfemForm.disableSubmit( e.target.form );

			lastTarget = e.target;

			if ( ! e.target.checkValidity() ) {
				vBarInvalid();
				return;
			}

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

				if ( +e.target.value === +e.target.tsfemFormPrevValue ) {
					//= Welp... Nothing happened.
					resetLoaders();
					tsfemForm.enableSubmit( e.target.form );
				} else if ( +e.target.value < +e.target.tsfemFormPrevValue ) {
					//= Deiterate.
					tsfemForm.unloadIterations( e.target );
					tsfemForm.enableSubmit( e.target.form );
				} else {
					//= Iterate.
					if ( +e.target.value > 40 ) {
						if ( +e.target.value > 200 ) {
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

						let _events = {};

						_events._cancel = function() {
							undoInput();
							resetLoaders();
							_events._reset();
							tsfemForm.enableSubmit( e.target.form );
						}
						_events._confirm = function() {
							tsfemForm.loadIterations( e.target, e.target.form );
							_events._reset();
						}
						_events._reset = function() {
							window.removeEventListener( 'tsfem_modalCancel', _events._cancel );
							window.removeEventListener( 'tsfem_modalConfirm', _events._confirm );
						}
						window.addEventListener( 'tsfem_modalCancel', _events._cancel );
						window.addEventListener( 'tsfem_modalConfirm', _events._confirm );
					} else {
						tsfemForm.loadIterations( e.target, e.target.form );
					}
				}
			}, itTimeout );
		}
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
	 * @param {element} form The target form. When passed, it will try to enable the submit buttons.
	 * @return {undefined}
	 */
	loadIterations: function( target, form ) {

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
			 * 30 seconds.
			 * It's massive and we might wish to fine-tune this based on
			 * non-cached slow PHP5.5 instances and expectancies based on
			 * item count fetching.
			 */
			timeout: 30000,
			async: true,
			success: function( response, status, xhr ) {

				let contentType = xhr.getResponseHeader( 'content-type' );

				window.dispatchEvent( new Event( 'tsfemForm_iterate_load' ) );

				if ( contentType.indexOf( 'json' ) > -1 ) {
					target.value = target.tsfemFormPrevValue;
					tsfem.unexpectedAjaxErrorNotice( response );
				} else {
					jQuery( outputWrapper ).append( response );
				}

				window.dispatchEvent( new Event( 'tsfemForm_iterate_complete' ) );
			},
			error: function( jqXHR, textStatus, errorThrown ) {

				// loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );
				tsfem.setTopNotice( -1, errorThrown );
				window.dispatchEvent( new Event( 'tsfem_iterationFail' ) );
			},
			complete: function() {
				outputWrapper.removeChild( loader );
				tsfemForm.enableSubmit( form );
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
		let reverseWarningWrap;
		//= Fills buttonWrap, with GC for stray items.
		(function() {
			buttonWrap = document.createElement( 'div' );
			buttonWrap.className = 'tsfem-form-setting-action tsfem-flex';
			buttonWrap.dataset.geoApiIsButtonWrap = 1;
			buttonWrap.style.opacity = 0;
			let button = document.createElement( 'button' );
			button.className = 'tsfem-button-primary tsfem-button-green tsfem-button-cloud';
			button.innerHTML = tsfemForm.i18n['validate'];
			button.type = 'button';
			buttonWrap.appendChild( button );

			reverseWarningWrap = document.createElement( 'div' );
			reverseWarningWrap.className = 'tsfem-description tsfem-form-option-description';
			reverseWarningWrap.dataset.geoApiIsButtonWarning = 1;
			reverseWarningWrap.style.opacity = 0;
			reverseWarningWrap.innerHTML = tsfemForm.i18n['reverseGeoWarning'];
		})();

		/**
		 * Fills in address fields by requested input data.
		 *
		 * @param {event.target} target The propagated button target.
		 * @param {object} data The address data.
		 * @return {undefined}
		 */
		const fillAddress = function( target, data ) {

			let $wrap = jQuery( target ).closest( '[data-geo-api-component="action"]' ),
				$fields = getFields( $wrap );

			$fields.each( function( index, element ) {
				let components = getComponents( element ),
					routeCombine = {};

				loopComponents:
				for ( let i in components ) {
					if ( data.hasOwnProperty( components[ i ] ) ) {
						switchComponents:
						switch ( components[ i ] ) {
							case 'route' :
							case 'street_number' :
								// Collect route and street number if combined.
								if ( components.length > 1 ) {
									routeCombine[ components[ i ] ] = data[ components[ i ] ];
									if ( Object.keys( routeCombine ).length > 1 ) {
										//= Fill if 2 are matched.
										element.value = routeCombine['route'] + ' ' + routeCombine['street_number'];
										break loopComponents;
									}
									break switchComponents;
								} else {
									element.value = data[ components[ i ] ];
									break loopComponents;
								}
								break;

							case 'locality' :
							case 'country' :
							case 'postal_code' :
							case 'region' :
								element.value = data[ components[ i ] ];
								break loopComponents;

							case 'lat' :
							case 'lng' :
								//* Convert string to float, convert float to 7 decimal places.
								element.value = parseFloat( parseFloat( data[ components[ i ] ] ).toFixed( 7 ) );
								break loopComponents;

							default :
								break loopComponents;
						}
					}
					continue;
				}
			} );
		}
		/**
		 * @param {event.target} target
		 * @param {object|array} data
		 * @return {undefined}
		 */
		const selectAddress = function( target, data ) {

			let _optionValues = {},
				_optionFields = {};

			loopData:
			for ( let i in data ) {
				let components = data[ i ]['address_components'],
					geometry = data[ i ]['geometry']['location'];

				_optionValues[ i ] = {};
				_optionFields[ i ] = data[ i ]['formatted_address'];

				_optionValues[ i ]['lat'] = geometry['lat'];
				_optionValues[ i ]['lng'] = geometry['lng'];

				loopComponents:
				for ( let _i in components ) {

					//let longname = components[ _i ]['long_name'],
					let shortname = components[ _i ]['short_name'],
						types = components[ _i ]['types'];

					loopTypes:
					for ( let __i in types ) {
						switchTypes:
						switch ( types[ __i ] ) {
							case 'route' :
							case 'street_number' :
							case 'locality' :
							case 'country' :
							case 'postal_code' :
								_optionValues[ i ][ types[ __i ] ] = shortname;
								break loopTypes;

							case 'administrative_area_level_1' :
								_optionValues[ i ]['region'] = shortname;
								break loopTypes;

							default :
								break switchTypes;
						}
					}
				}
				/**
				 * We condone street_address omission.
				 *
				 * Yes, this is after the loop.
				 * However, the above loop is short, few data is passed, and
				 * if we keep track of this inside that loop it's very hard
				 * to maintain the code.
				 */
				if ( ! _optionValues[ i ].hasOwnProperty( 'route' ) ) {
					delete _optionValues[ i ];
					delete _optionFields[ i ];
				}
			};

			tsfem.dialog( {
				'title' : tsfemFormL10n.i18n['selectAddressTitle'],
				'text' : tsfemFormL10n.i18n['selectAddressText'],
				'select' : _optionFields,
				'confirm' : tsfemFormL10n.i18n['select'],
				'cancel' : tsfemFormL10n.i18n['cancel'],
			} );

			let _events = {};

			_events._cancel = function() {
				tsfemForm.enableButton( target );
				_events._close();
			}
			_events._confirm = function( e ) {
				if ( 'checked' in e.detail ) {
					let _dataFill = _optionValues[ e.detail.checked ];
					if ( _dataFill !== void 0 ) fillAddress( target, _dataFill );
				}
				unloadButton( jQuery( target ).closest( '[data-geo-api-component="action"]' ) );
				_events._close();
			}
			_events._close = function() {
				window.removeEventListener( 'tsfem_modalCancel', _events._cancel );
				window.removeEventListener( 'tsfem_modalConfirm', _events._confirm );
				//tsfemForm.enableSubmit( target.form );
			}
			window.addEventListener( 'tsfem_modalCancel', _events._cancel );
			window.addEventListener( 'tsfem_modalConfirm', _events._confirm );
		}
		/**
		 * Requests AJAX call for geocoding data, opens modal and propagates
		 * events to new functions, like `selectAddress`.
		 *
		 * @param {event} event The button click event.
		 * @return {undefined}
		 */
		const requestGeoData = function( event ) {

			let $wrap = jQuery( event.target ).closest( '[data-geo-api-component="action"]' ),
				$fields = getFields( $wrap ),
				_data = {};

			tsfemForm.disableButton( event.target );

			$fields.each( function( index, element ) {
				if ( element.value )
					_data[ element.dataset.geoApiComponent ] = element.value;
			} );

			let completeData = {
				'target' : event.target,
				'callbacks' : {
					'success' : selectAddress,
					'failure' : function() {
						tsfemForm.enableButton( event.target );
					},
					'always' : function() {},
				},
			};

			//* Get data, open modals, etc.
			tsfemForm.getGeoData( _data, event.target.dataset.formId, completeData );
		}

		/**
		 * @param {jQuery.element} $wrap The target wrap.
		 * @param {string} formId The target form ID.
		 * @param {number} valid If 2, it will warn about reverse Geocoding
		 * @return {undefined}
		 */
		const loadButton = function( $wrap, formId, valid = 1 ) {

			if ( ! valid ) {
				return unloadButton( $wrap );
			}

			let $target = $wrap && $wrap.find( '.tsfem-form-multi-setting-label-inner-wrap' );

			if ( ! $target.length )
				return;

			if ( ! $wrap.data( 'geo-api-has-button' ) ) {
				let _node = buttonWrap.cloneNode( true );

				$target.append( _node );
				$wrap.data( 'geo-api-has-button', 1 );

				//= Add form target id
				_node.getElementsByTagName( 'button' )[0].dataset.formId = formId;
				_node.getElementsByTagName( 'button' )[0].addEventListener( 'click', requestGeoData );

				tsfem.fadeIn( _node, 300 );
			}

			let $warning = $target.find( '[data-geo-api-is-button-warning]' ).first();

			if ( 2 === valid ) {
				if ( $warning.length )
					return;

				let _warnNode = reverseWarningWrap.cloneNode( true );
				$target.append( _warnNode );
				tsfem.fadeIn( _warnNode, 300 );
			} else {
				if ( ! $warning.length )
					return;

				tsfem.fadeOut( $warning[0], 300 );
			}
		}
		/**
		 * Alias of loadButton.
		 *
		 * @param {jQuery.element} $wrap The target wrap.
		 * @param {string} formId The target form ID.
		 * @param {number} valid If 2, it will warn about reverse Geocoding
		 * @return {undefined}
		 */
		const updateButton = function( $wrap, formId, valid = 1 ) {
			loadButton( $wrap, formId, valid );
		}
		/**
		 * @param {jQuery.element} $wrap The target wrap.
		 * @return {undefined}
		 */
		const unloadButton = function( $wrap ) {
			let _target = $wrap && $wrap.find( '.tsfem-form-multi-setting-label-inner-wrap' );
			_target.find( _target.children( '[data-geo-api-is-button-wrap], [data-geo-api-is-button-warning]' ) ).fadeOut( 300, function() {
				this.remove();
				$wrap.removeData( 'geo-api-has-button' );
			} );
		}
		/**
		 * @param {jQuery.element} $wrap The geodata parent button wrapper.
		 * @return {jQuery.element} The fields
		 */
		const getFields = function( $wrap ) {
			return $wrap.find( '[data-geo-api="1"]' );
		}
		/**
		 * @param {event.target} target
		 * @return {array} The components.
		 */
		const getComponents = function( target ) {
			let component = target && target.dataset.geoApiComponent || void 0;
			return component && component.split( ',' ) || [];
		}
		/**
		 * Validate Address fields. Returns 2 if lat/lng are valid. 1 or 0 otherwise.
		 * @param {jQuery.element} $wrap The geodata parent button wrapper.
		 * @return {number} Whether the field is valid. 2, 1 or 0.
		 */
		const validateFields = function( $wrap ) {

			let valid = 0,
				$fields = getFields( $wrap ),
				lat = $fields.filter( '[data-geo-api-component="lat"]' ).first().val(),
				lng = $fields.filter( '[data-geo-api-component="lng"]' ).first().val(),
				route;

			$fields.each( function( index, element ) {
				if ( jQuery( element ).data( 'geo-api-component' ).indexOf( 'route' ) > -1 ) {
					route = jQuery( this ).val();
					return false;
				}
			} );

			if ( lat && lng ) {
				valid = /^(\-|\+)?([0-9]+(\.[0-9]+)?)$/.test( lat ) && lat >= -90 && lat <= 90 ? 2 : 0;
				valid = valid && /^(\-|\+)?([0-9]+(\.[0-9]+)?)$/.test( lng ) && lng >= -180 && lng <= 180 ? 2 : 0;
			} else if ( route ) {
				//* This tests street name + number.
				valid = /^((([0-9\/-]+([\/-0-9A-Z]+)?(\s|(,\s)))([\u00a1-\uffffa-zA-Z\s]|[0-9_/-])+))|(([\u00a1-\uffffa-zA-Z\s]|[0-9_/-])+)((\s|(,\s))([0-9\/-]+([\/-0-9A-Z]+)?))$/.test( route ) ? 1 : 0;
			}

			//= It will be erroneous if lat/lng are filled in. Invalidate if they're not validated.
			if ( 1 === valid && lat && lng ) {
				valid = 0;
			}

			return valid;
		}

		let tBuffer = 0, tTimeout = 500;
		/**
		 * @param {event} event The geodata form input event
		 */
		const addButtonIfValid = function( event ) {
			clearTimeout( tBuffer );
			tBuffer = setTimeout( function() {

				if ( ! event.target.dataset.geoApiComponent )
					return;

				let $wrap = jQuery( event.target ).closest( '[data-geo-api-component="action"]' ),
					valid = validateFields( $wrap );

				if ( $wrap.data( 'geo-api-has-button' ) ) {
					if ( ! valid ) {
						unloadButton( $wrap );
					} else {
						updateButton( $wrap, event.target.form.id, valid );
					}
				} else {
					loadButton( $wrap, event.target.form.id, valid );
				}
			}, tTimeout );
		}

		let $input = jQuery( 'input[data-geo-api="1"]' ),
			$select = jQuery( 'select[data-geo-api="1"]' );

		if ( $input ) {
			$input.off( 'input', addButtonIfValid );
			$input.on( 'input', addButtonIfValid );
		}
		if ( $select ) {
			$select.off( 'change', addButtonIfValid );
			$select.on( 'change', addButtonIfValid );
		}
	},

	/**
	 * Gets Geocoding data from input selection.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {object} input The predefined geo input data.
	 * @param {string} formId The form wrapper ID.
	 * @param {object} completeData The callbacks for form completion and event data.
	 * @return {jQuery.ajax} The jQuery AJAX response object.
	 */
	getGeoData: function( input, formId, completeData ) {

		let form = document.getElementById( formId ),
			output;

		let $loader = jQuery( form ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status = 0, loaderText = '';

		//* Disable form submission.
		tsfemForm.disableSubmit( form );

		//* Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		//* Set ajax loader.
		tsfem.setAjaxLoader( $loader );

		// Do ajax...
		return jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfemForm_get_geocode',
				'nonce' : tsfemForm.nonce,
				'input' : input,
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
			} else if ( 'failure' === type ) {
				tsfem.unexpectedAjaxErrorNotice( response );
				completeData.callbacks.failure( completeData.target );
			} else if ( 'geodata' in data ) {
				completeData.callbacks.success( completeData.target, data.geodata.results );
			}
		} ).fail( function( jqXHR, textStatus, errorThrown ) {
			//= Set AJAX response for wrapper.
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );
			tsfem.setTopNotice( 17200 );
			tsfem.setTopNotice( -1, errorThrown );

			completeData.callbacks.failure( completeData.target );
		} ).always( function() {
			tsfem.updatedResponse( $loader, status, loaderText, 0 );
			tsfemForm.enableSubmit( form );
			completeData.callbacks.always( completeData.target );
		} );
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

		const prepareItems = function( event ) {
			tsfemForm.prepareCollapseTitles( event );
		}

		let buttons = document.querySelector( '.tsfem-form-collapse > input' );

		if ( buttons ) {
			buttons.removeEventListener( 'change', prepareItems );
			buttons.addEventListener( 'change', prepareItems );
		}
	},

	/**
	 * Prepares collapse item titles by adding item change listeners.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event} event The button activation event.
	 * @return {undefined}
	 */
	prepareCollapseTitles: function( event ) {

		/**
		 * @param {event} event The target input field change.
		 */
		const doTitleChangeSingle = function( event ) {

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.children( 'h3' ),
				prep = $label.data( 'dyntitleprep' ),
				val = jQuery( event.target ).val();

			if ( val ) {
				$title.text( prep + ' - ' + val );
			} else {
				$title.text( prep );
			}
		}
		/**
		 * @param {event} event The target input fields change.
		 */
		const doTitleChangePlural = function( event ) {

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
		}
		/**
		 * Prepares title change when collapsable item is expanded.
		 * It's done this way to prevent huge onLoad iterations.
		 *
		 * @param {event} event The button activation event.
		 */
		const prepareTitleChange = function( event ) {

			let $label = jQuery( event.target ).siblings( 'label' ),
				type = $label.data( 'dyntitletype' ),
				key = $label.data( 'dyntitleid' ) + '[' + $label.data( 'dyntitlekey' ) + ']';

			switch ( type ) {
				case 'single' :
					let el = document.getElementById( key );

					jQuery( el ).off( 'input', doTitleChangeSingle );
					jQuery( el ).on( 'input', { '_tsfemFormLabel' : $label }, doTitleChangeSingle );
					break;

				case 'plural' :
					let $things = jQuery( document.getElementById( key ) ).find( 'input' );

					$things.off( 'change', doTitleChangePlural );
					$things.on( 'change', { '_tsfemFormLabel' : $label, '_tsfemFormThings' : $things }, doTitleChangePlural );
					break;
			}
		}
		console.log( event );
		prepareTitleChange( event );
	},

	/**
	 * Saves form input through AJAX.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event jQuery event
	 * @return {(undefined|boolean)} void If form isn't valid. True on AJAX completion.
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

		//* Disable the submit button.
		tsfemForm.disableButton( button );

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
				let rCode = data.results && data.results.code || void 0;

				if ( rCode ) {
					status = 1;
				// let sData = data.sdata && data.sdata || void 0;
				// continue using sData..., adjust rCode if necessary
				// Also set loaderText...

					tsfem.setTopNotice( rCode );
				} else {
					//* Erroneous output.
					loaderText = tsfem.i18n['UnknownError'];
				}
			}
		} ).fail( function( jqXHR, textStatus, errorThrown ) {
			// Set Ajax response for wrapper.
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

			// Try to set top notices, regardless. First notifies that there's an error saving.
			tsfem.setTopNotice( 1071100 );
			tsfem.setTopNotice( -1, errorThrown );
		} ).always( function() {
			tsfem.updatedResponse( $loader, status, loaderText, 0 );
			tsfemForm.enableButton( button );
		} );

		return true;
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
	 * Enables button.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event.target} target The button to enable.
	 * @return {boolean} false
	 */
	enableButton: function( target ) {
		//= setTimeout prevents paint lag.
		setTimeout( function() {
			target.classList.remove( 'tsfem-button-disabled', 'tsfem-button-loading' );
			target.disabled = false;
		}, 1 );
	},

	/**
	 * Disables button.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event.target} target The button to disable.
	 * @param {boolean} loading Whether to hint it's disabled.
	 * @return {boolean} false
	 */
	disableButton: function( target, loading = 1 ) {

		if ( loading ) {
			target.classList.add( 'tsfem-button-disabled', 'tsfem-button-loading' );
		} else {
			target.classList.add( 'tsfem-button-disabled' );
		}
		target.disabled = true;
	},

	/**
	 * Enables form submission.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {element} form The form to enable submit from.
	 * @return {boolean} false
	 */
	enableSubmit: function( form ) {
		if ( form && form.id ) {
			tsfemForm.enableButton( document.querySelector( '[form="' + form.id + '"]' ) );
		}
	},

	/**
	 * Disables form submission.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {element} form The form to disable submit from.
	 * @param {boolean} loading Whether to hint it's disabled.
	 * @return {boolean} false
	 */
	disableSubmit: function( form, loading = 1 ) {
		if ( form && form.id ) {
			tsfemForm.disableButton( document.querySelector( '[form="' + form.id + '"]' ), loading );
		}
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
