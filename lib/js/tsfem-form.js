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
// @compilation_level SIMPLE_OPTIMIZATIONS
// @debug false
// @use_types_for_optimization false
// @disable_property_renaming true
// @extra_annotations access
// @language ECMASCRIPT6_STRICT
// @language_out ECMASCRIPT6_STRICT
// @output_file_name tsfem-form.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @externs_url http://testmijnphp7.nl/wp-content/plugins/the-seo-framework-extension-manager/lib/js/externs/tsfem.externs.js
// @externs_url http://testmijnphp7.nl/wp-content/plugins/the-seo-framework-extension-manager/lib/js/externs/tsfem-form.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home
//
// These don't work:
// // @use_types_for_optimization false
// // @disable_property_renaming true
// // @extra_annotations access
//
// x.dataset properties are rewritten, and I have yet to find a way to bypass that.
//
// Therefore, this file is annotated with SIMPLE_OPTIMIZATIONS.
// This means all "access private" methods are now "access public", regardless.
//
// We don't want to use homebrew closure-compiler (i.e. local command line) as it defeats the purpose of open source.
// Because, that would mean that users can't replicate the file and test its integrity.

'use strict';

/**
 * Holds tsfemForm values in an object to avoid polluting global namespace.
 *
 * @since 1.3.0
 *
 * @constructor
 */
window.tsfemForm = {

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
	 * @TODO make multiple callee's possible? I.e. multiple iterable forms.
	 * @type {String} callee Caller class for iterations
	 */
	callee : tsfemFormL10n.callee,

	/**
	 * Returns element data. Tries to convert JSON if existent.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {Element} element The element with data
	 * @param {string} data The data type.
	 * @return {(Object|string)} JSON decoded object or string.
	 */
	parseElementData: function( element, data ) {

		let value = tsfemForm.getElementData( element, data ),
			ret = void 0;

		try {
			ret = value && JSON.parse( value );
		} catch (e) {
			ret = value;
		} finally {
			return ret;
		}
	},

	/**
	 * Returns element data if exist.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {Element} element The element with data
	 * @param {string} data The data type.
	 * @return {(string|boolean)} The data if existent. False otherwise.
	 */
	getElementData: function( element, data ) {

		if ( data.indexOf( "data-" ) === 0 ) {
			data = jQuery.camelCase( data.slice( 5 ) );
		}

		if ( element.dataset.hasOwnProperty( data ) )
			return element.dataset[ data ];

		return false;
	},

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
			$items.off( 'input.fIt', fIt );
			$items.on( 'input.fIt', fIt );
		}
		const rebuildEvents = function() {
			$items.off( 'input.fIt', fIt );
			$items = jQuery( '.tsfem-form-iterator-selector-wrap input' );
			tsfemForm.prepareItItems( $items );
			tsfemForm.prepareCollapseItems();
			tsfemForm.setupGeo();
			$items.on( 'input', fIt );
			tsfem.initDescHover();
			lastTarget = void 0;
		}
		const undoInput = function() {
			if ( lastTarget ) lastTarget.value = lastTarget.dataset.tsfemFormPrevValue;
		}

		let $window = jQuery( window );

		$window.on( 'tsfemForm.iterationFail', undoInput );
		$window.on( 'tsfemForm.iterationLoad tsfemForm.deiterationLoad tsfemForm.iterationFail', resetLoaders );
		$window.on( 'tsfemForm.iterationComplete tsfemForm.deiterationComplete tsfemForm.iterationFail', rebuildEvents );

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

				if ( +e.target.value === +e.target.dataset.tsfemFormPrevValue ) {
					//= Welp... Nothing happened.
					resetLoaders();
					tsfemForm.enableSubmit( e.target.form );
				} else if ( +e.target.value < +e.target.dataset.tsfemFormPrevValue ) {
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
							tsfemForm.loadIterations( e.target );
							_events._reset();
						}
						_events._reset = function() {
							window.removeEventListener( 'tsfem_modalCancel', _events._cancel );
							window.removeEventListener( 'tsfem_modalConfirm', _events._confirm );
						}
						window.addEventListener( 'tsfem_modalCancel', _events._cancel );
						window.addEventListener( 'tsfem_modalConfirm', _events._confirm );
					} else {
						tsfemForm.loadIterations( e.target );
					}
				}
			}, itTimeout );
		}
		$items.on( 'input.fIt', fIt );
	},

	/**
	 * Unloads iteration elements from DOM.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event.target} target The iteration input target.
	 * @return {undefined}
	 */
	unloadIterations: function( target ) {

		if ( ! target )
			return;

		//= Get ID without '[count]', '[number]', or any other current iteration key.
		let itId = target.id.slice( 0, target.id.lastIndexOf( '[' ) ),
			outputWrapper = document.getElementById( itId + '-wrapper' );

		outputWrapper.style.willChange = 'contents';

		let $window = jQuery( window ),
			$toRemove = jQuery( outputWrapper ).children( '.tsfem-form-collapse' ).slice( target.value );

		$window.trigger( 'tsfemForm.deiterationLoad', [ target, $toRemove ] );
		$toRemove.remove();
		$window.trigger( 'tsfemForm.deiterationComplete' );

		outputWrapper.style.willChange = 'auto';
	},

	/**
	 * Loads iteration elements through AJAX.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event.target} target The target iteration loader input.
	 * @return {undefined}
	 */
	loadIterations: function( target ) {

		if ( ! target )
			return;

		//= Get ID without '[count]', '[number]', or any other current iteration key.
		let itId = target.id.slice( 0, target.id.lastIndexOf( '[' ) );

		// Wrap outer items.
		let $loader = jQuery( target ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status = 0, loaderText = '';

		// Wrap inner items.
		let outputWrapper = document.getElementById( itId + '-wrapper' ),
			waiter = document.createElement( 'div' );

		waiter.className = 'tsfem-flex-status-loading tsfem-flex tsfem-flex-center';
		waiter.appendChild( document.createElement( 'span' ) );

		// WillChange will stop the waiter from turning. Also, no added benefit found...
		// outputWrapper.style.willChange = 'transform, contents';

		outputWrapper.appendChild( waiter );

		//* Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		//* Set ajax loader.
		tsfem.setAjaxLoader( $loader );

		// Do ajax...
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'html',
			data: {
				'action' : 'tsfemForm_iterate',
				'nonce' : tsfemForm.nonce,
				'args' : {
					'caller' : itId,
					'callee' : tsfemForm.callee,
					'previousIt' : target.dataset.tsfemFormPrevValue,
					'newIt' : target.value,
				},
			},
			/**
			 * 30 seconds.
			 * It's massive and we might wish to fine-tune this based on
			 * non-cached slow PHP5.5 instances and expectancies based on
			 * item count fetching.
			 */
			timeout: 5000,
			async: true,
		} ).done( function( response, _status, xhr ) {
			let contentType = xhr.getResponseHeader( 'content-type' ),
				$window = jQuery( window );

			$window.trigger( 'tsfemForm.iterationLoad' );

			if ( contentType.indexOf( 'json' ) > -1 ) {
				//* We didn't ask for JSON. This means something interfered.
				target.value = target.dataset.tsfemFormPrevValue;
				tsfem.unexpectedAjaxErrorNotice( response );
				loaderText = tsfem.i18n['InvalidResponse'];
				$window.trigger( 'tsfemForm.iterationFail' );
			} else {
				jQuery( outputWrapper ).append( response );
				status = 1;
				//* @TODO add $(response).children($headers) to trigger data?
				$window.trigger( 'tsfemForm.iterationComplete' );
			}
		} ).fail( function( jqXHR, textStatus, errorThrown ) {
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );
			tsfem.setTopNotice( -1, errorThrown );
			jQuery( window ).trigger( 'tsfemForm.iterationFail' );
		} ).always( function() {
			outputWrapper.removeChild( waiter );
			// outputWrapper.style.willChange = 'auto';
			tsfemForm.enableSubmit( target.form );
			tsfem.updatedResponse( $loader, status, loaderText, 0 );
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
	prepareItItems: function( $items ) {

		//= Sets last known iteration values.
		$items.each( function( i, el ) {
			el.dataset.tsfemFormPrevValue = el.value;
			// el.dataset.tsfemFormHighestValue = el.dataset.tsfemFormHighestValue && el.dataset.tsfemFormHighestValue > el.value && el.dataset.tsfemFormHighestValue || el.value;
			// el.dataset.tsfemFormLowestValue  = el.dataset.tsfemFormLowestValue  && el.dataset.tsfemFormLowestValue  < el.value && el.dataset.tsfemFormLowestValue  || el.value;
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

		let fillingAddress = false;

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
		 * Fills in address fields by requested input data from dialog.
		 *
		 * @param {event.target} target The propagated button target.
		 * @param {object} data The address data.
		 * @return {undefined}
		 */
		const fillAddress = function( target, data ) {

			let $wrap = jQuery( target ).closest( '[data-geo-api-component="action"]' ),
				$fields = getFields( $wrap );

			//= Prevent re-checking own fields when filling.
			fillingAddress = true;

			$fields.each( function( index, element ) {
				let components = tsfemForm.parseElementData( element, 'geoApiComponent' ),
					routeCombine = {};

				// Convert to object if not.
				components = 'object' === typeof components && components || { 0 : components };

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

				//= Lets the world know something happened.
				jQuery( this ).trigger( 'change' );
			} );

			//= Reset checking fields.
			fillingAddress = false;
		}
		/**
		 * Opens dialog where the user can select an address.
		 *
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
				 * We condemn 'street_address' omission (i.e. 'route').
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
					if ( void 0 !== _dataFill ) fillAddress( target, _dataFill );
				}
				unloadButton( jQuery( target ).closest( '[data-geo-api-component="action"]' ) );
				_events._close();
			}
			_events._close = function() {
				window.removeEventListener( 'tsfem_modalCancel', _events._cancel );
				window.removeEventListener( 'tsfem_modalConfirm', _events._confirm );
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
		 * Loads validation button.
		 *
		 * @param {jQuery.element} $wrap The target wrap.
		 * @param {string} formId The target form ID.
		 * @param {number} valid If 2, it will warn about reverse Geocoding
		 * @return {undefined}
		 */
		const loadButton = function( $wrap, formId, valid ) {

			valid = void 0 === valid ? 1 : valid;

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
		const updateButton = function( $wrap, formId, valid ) {
			loadButton( $wrap, formId, valid );
		}
		/**
		 * Unloads Geo validation button from $wrap.
		 *
		 * @param {jQuery.element} $wrap The target wrap.
		 * @return {undefined}
		 */
		const unloadButton = function( $wrap ) {
			let $target = $wrap && $wrap.find( '.tsfem-form-multi-setting-label-inner-wrap' ) || void 0;

			$target && $target.find( $target.children( '[data-geo-api-is-button-wrap], [data-geo-api-is-button-warning]' ) ).fadeOut( 300, function() {
				this.remove();
				$wrap.removeData( 'geo-api-has-button' );
			} );
		}
		/**
		 * Gets geo fields from input $wrap.
		 *
		 * @param {jQuery.element} $wrap The geodata parent button wrapper.
		 * @return {jQuery.element} The fields
		 */
		const getFields = function( $wrap ) {
			return $wrap.find( '[data-geo-api="1"]' );
		}
		/**
		 * Validate Address fields. Returns 2 if lat/lng are valid. 1 or 0 otherwise.
		 *
		 * @param {jQuery.element} $wrap The geodata parent button wrapper.
		 * @return {number} Whether the field is valid. 2, 1 or 0.
		 */
		const validateFields = function( $wrap ) {

			let $fields = getFields( $wrap );

			if ( ! $fields.length )
				return 0;

			let valid = 0,
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
				//* This tests street_name+number, number+street_name. UTF-8.
				valid = /^((([0-9\/-]+([\/-0-9A-Z]+)?(\s|(,\s)))([\u00a1-\uffffa-zA-Z\.\s]|[0-9_/-])+))|(([\u00a1-\uffffa-zA-Z\.\s]|[0-9_/-])+)((\s|(,\s))([0-9\/-]+([\/-0-9A-Z]+)?))$/
						.test( route ) ? 1 : 0;
			}

			return valid;
		}

		let tBuffer = 0, tTimeout = 500;
		/**
		 * @param {event} event The geodata form input event
		 * @return {undefined} Early if filling address.
		 */
		const addButtonIfValid = function( event ) {

			if ( fillingAddress )
				return;

			clearTimeout( tBuffer );
			tBuffer = setTimeout( function() {

				if ( ! tsfemForm.getElementData( event.target, 'geoApiComponent' ) )
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
			$input.off( 'input.tsfemForm.addButtonIfValid' );
			$input.on( 'input.tsfemForm.addButtonIfValid', addButtonIfValid );
		}
		if ( $select ) {
			$select.off( 'change.tsfemForm.addButtonIfValid' );
			$select.on( 'change.tsfemForm.addButtonIfValid', addButtonIfValid );
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

		// Do AJAX and return object.
		return jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfemForm_get_geocode',
				'nonce' : tsfemForm.nonce,
				'input' : JSON.stringify( input ),
			},
			processData: true,
			timeout: 14000,
			async: true,
		} ).done( function( response ) {

			response = tsfem.convertJSONResponse( response );

			tsfem.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			if ( ! data || ! type ) {
				//* Erroneous output.
				loaderText = tsfem.i18n['InvalidResponse'];
			} else if ( 'failure' === type ) {
				tsfem.unexpectedAjaxErrorNotice( response );
				completeData.callbacks.failure( completeData.target );
			} else if ( 'geodata' in data ) {
				status = 1;
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
			tsfemForm.prepareCollapseValidity( event );
		}

		let $buttons = jQuery( '.tsfem-form-collapse > input' );

		$buttons.off( 'change.tsfemForm.prepareItems' );
		$buttons.on( 'change.tsfemForm.prepareItems', prepareItems );
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
		 * Changes title based on single input.
		 *
		 * @param {event} event The target input field change.
		 */
		const doTitleChangeSingle = function( event ) {

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.find( '.tsfem-form-collapse-title' ),
				prep = $label.data( 'dyntitleprep' ),
				val = jQuery( event.target ).val();

			if ( val ) {
				$title.text( prep + ' - ' + val );
			} else {
				$title.text( prep );
			}
		}
		/**
		 * Changes title based on plural options, i.e. checkboxes.
		 *
		 * @param {event} event The target input fields change.
		 */
		const doTitleChangePlural = function( event ) {

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.find( '.tsfem-form-collapse-title' ),
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

					if ( event.target.checked ) {
						jQuery( el ).off( 'input.tsfemForm.doTitleChangeSingle' );
					} else {
						jQuery( el ).on( 'input.tsfemForm.doTitleChangeSingle', { '_tsfemFormLabel' : $label }, doTitleChangeSingle );
					}
					break;

				case 'plural' :
					let $things = jQuery( document.getElementById( key ) ).find( 'input' );

					if ( event.target.checked ) {
						$things.off( 'change.tsfemForm.doTitleChangePlural' );
					} else {
						$things.on( 'change.tsfemForm.doTitleChangePlural', { '_tsfemFormLabel' : $label, '_tsfemFormThings' : $things }, doTitleChangePlural );
					}
					break;
			}
		}
		prepareTitleChange( event );
	},

	/**
	 * Prepares collapse item banners by adding input element event listeners.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {event} event The button activation event.
	 * @return {undefined}
	 */
	prepareCollapseValidity: function( event ) {

		/**
		 * Adjusts classes for headers and the icons based on errors found.
		 *
		 * @param {jQuery.element} $headers The headers set classes for.
		 * @param {boolean} hasErrors Whether errors are present.
		 */
		const setClasses = function( $header, hasErrors ) {

			let errorClass = 'tsfem-form-collapse-header-error',
				goodClass = 'tsfem-form-collapse-header-good';

			let iconSelector = '.tsfem-form-title-icon',
				iconUnknown = 'tsfem-form-title-icon-unknown',
				iconError = 'tsfem-form-title-icon-error',
				iconGood = 'tsfem-form-title-icon-good';

			if ( hasErrors > 0 ) {
				$header.removeClass( goodClass ).addClass( errorClass );
				$header.find( iconSelector ).removeClass( iconUnknown + ' ' + iconGood ).addClass( iconError );
			} else if ( hasErrors < 0 ) {
				$header.removeClass( errorClass + ' ' + goodClass ).addClass( goodClass );
				$header.find( iconSelector ).removeClass( iconGood + ' ' + iconError ).addClass( iconUnknown );
			} else {
				$header.removeClass( errorClass ).addClass( goodClass );
				$header.find( iconSelector ).removeClass( iconUnknown + ' ' + iconError ).addClass( iconGood );
			}
		}
		/**
		 * Adds or subtracts error count for each $headers.
		 *
		 * @param {jQuery.element} $headers The headers to count errors for.
		 * @param {number} _i The addition (or subtraction).
		 */
		const countErrors = function( $headers, _i ) {

			let $header,
				newCount;

			$headers.each( function() {
				$header = jQuery( this );
				newCount = ( +$header.data( 'tsfemErrorCount' ) || 0 ) + _i;

				$header.data( 'tsfemErrorCount', newCount );
				setClasses( $header, newCount );
			} );
		}
		/**
		 * Checks validity of the element.
		 *
		 * @param {event} event The input change event.
		 */
		const checkValidity = function( event ) {

			let wasValid = 0;
			if ( 'tsfemWasValid' in event.target.dataset ) {
				wasValid = +event.target.dataset.tsfemWasValid;
			} else {
				wasValid = event.target.dataset.tsfemWasValid = 1;
			}

			//= It's valid when it's disabled :)
			let inputIsValid = event.target.disabled || event.target.checkValidity();

			/*
			// Neglegible performance effect, without the added benefit of header indication.
			if ( wasValid && inputIsValid ) {
				return;
			}
			*/

			//* hadErrors works by counting the existence of data.
			let $headers = jQuery( event.target ).parents( '.tsfem-form-collapse' ).children( '.tsfem-form-collapse-header' ),
				hadErrors = +$headers.data( 'tsfemErrorCount' );

			if ( hadErrors ) {
				//= Header is already marked invalid.
				if ( wasValid && ! inputIsValid ) {
					event.target.dataset.tsfemWasValid = +inputIsValid;
					countErrors( $headers, +1 );
				} else if ( ! wasValid && inputIsValid ) {
					event.target.dataset.tsfemWasValid = +inputIsValid;
					countErrors( $headers, -1 );
				}
			} else {
				if ( ! inputIsValid ) {
					event.target.dataset.tsfemWasValid = +inputIsValid;
					countErrors( $headers, +1 );
				} else {
					countErrors( $headers, 0 );
				}
			}
		}
		/**
		 * Does initial check.
		 *
		 * @param {Element} button The label wrap checkbox button.
		 * @param {!jQuery} $items The label wrap items.
		 */
		const doFirstCheck = function( button, $items ) {

			let didCheck = button.dataset.tsfemDidInitialValidation || 0;

			if ( ! didCheck ) {
				//= Do initial run.
				$items.on( 'tsfemForm.first.checkValidity', checkValidity );
				$items.each( function() { jQuery( this ).trigger( 'tsfemForm.first.checkValidity' ); } );
				$items.off( 'tsfemForm.first.checkValidity' );

				//= Register run.
				button.dataset.tsfemDidInitialValidation = 1;
			}
		}
		/**
		 * Prepares input change events when collapsable item is expanded.
		 * It's done this way to prevent huge onLoad iterations.
		 *
		 * @param {event} event The checkbox button activation event.
		 */
		const prepareChecks = function( event ) {

			let $items = jQuery( event.target ).siblings( '.tsfem-form-collapse-content' ).find( 'input, select, textarea' ).not( '.tsfem-form-collapse-checkbox' );

			//= Always turn the event off to prevent duplication.
			$items.off( 'change.tsfemForm.checkValidity' );

			//= Reinitiate the event if the collapse header is expanded (unchecked).
			if ( ! event.target.checked ) {
				$items.on( 'change.tsfemForm.checkValidity', checkValidity );
				doFirstCheck( event.target, $items );
			}
		}
		prepareChecks( event );

		//= Register custom validity checks. Pass checkValidity callback.
		tsfemForm.prepareCustomChecks( checkValidity );
		tsfemForm.prepareDeiterationValidityChecks( checkValidity );
	},

	/**
	 * Prepares collapse item banners by adding custom validation listeners.
	 *
	 * @since 1.3.0
	 * @access private
	 * @see tsfemForm.triggerCustomValidation
	 *
	 * @callback tsfemForm.prepareCollapseValidity
	 * @function
	 * @param {requestCallback} checkValidityCb The validity checker callback
	 * @return {undefined}
	 */
	prepareCustomChecks: function( checkValidityCb ) {

		/**
		 * Triggers listener for custom validation checks.
		 * When invoked, it will directly check for the event's target validity.
		 *
		 * Once.
		 *
		 * @param {event} event The window trigger event.
		 * @param {Element} element The element to check. This element is cloned
		 *                      and isn't attached to DOM.
		 * @return {undefined} Early if item details aren't specified.
		 */
		const customChecks = function( event, element ) {

			if ( ! element )
				return;

			//* Find and capture element.
			let $el = jQuery( element );

			if ( ! $el.length )
				return;

			$el.one( 'tsfemForm.temp.customChecks.checkValidityCb', checkValidityCb );
			$el.trigger( 'tsfemForm.temp.customChecks.checkValidityCb' );
		}
		/**
		 * Adds listener to window for custom validation checks.
		 *
		 * @return {undefined}
		 */
		const addCustomChecks = function() {
			jQuery( window ).on( 'tsfemForm.customValidationChecks', customChecks );
		}
		/**
		 * Removes listener to window for custom validation checks.
		 *
		 * @return {undefined}
		 */
		const removeCustomChecks = function() {
			jQuery( window ).off( 'tsfemForm.customValidationChecks' );
		}
		/**
		 * Prepares listener to window for custom validation checks.
		 *
		 * @return {undefined}
		 */
		const prepareCustomChecks = function() {
			//= Initial.
			removeCustomChecks();
			addCustomChecks();

			let $window = jQuery( window );

			/**
			 * Race conditions...
			 */
			//= Delegation resetters.
			$window.off( 'tsfemForm.iterationLoad.customValidation' );
			$window.off( 'tsfemForm.iterationFail.customValidation' );
			$window.off( 'tsfemForm.iterationComplete.customValidation' );
			//= Event resetter.
			$window.on( 'tsfemForm.iterationLoad.customValidation', removeCustomChecks );
			$window.on( 'tsfemForm.iterationFail.customValidation', addCustomChecks );
			$window.on( 'tsfemForm.iterationComplete.customValidation', addCustomChecks );
		}
		prepareCustomChecks();
	},

	/**
	 * Custom validation listener handler. It invokes events so the hanlders
	 * know when to perform checks.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {Element} item
	 * @return {undefined}
	 */
	triggerCustomValidation: function( item ) {
		if ( item instanceof HTMLElement )
			jQuery( window ).trigger( 'tsfemForm.customValidationChecks', [ item ] );
	},

	/**
	 * Prepares collapse item banners by adding validation listeners to
	 * deiteration event items.
	 *
	 * This prevents stray validation checks, that can never be validated again.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @callback tsfemForm.prepareCollapseValidity
	 * @function
	 * @param {requestCallback} checkValidityCb The validity checker callback
	 * @return {undefined}
	 */
	prepareDeiterationValidityChecks: function( checkValidityCb ) {

		/**
		 * Disables all invalid items and revalidates them.
		 *
		 * @param {jQuery.event} event The unloader event.
		 * @param {event.target} target The iteration input target.
		 * @param {!jQuery} $toRemove The '.tsfem-form-collapse' headers to be removed.
		 * @return {undefined}
		 */
		const disableAndValidate = function( event, target, $toRemove ) {

			if ( ! $toRemove )
				return;

			let $items = $toRemove.children( '.tsfem-form-collapse-content' ).find( 'input:invalid, select:invalid, textarea:invalid' );

			if ( ! $items )
				return;

			$items.prop( 'disabled', true );
			$items.on( 'tsfemForm.temp.disableAndValidate.checkValidityCb', checkValidityCb );
			$items.each( function() { jQuery( this ).trigger( 'tsfemForm.temp.disableAndValidate.checkValidityCb' ); } );
			$items.off( 'tsfemForm.temp.disableAndValidate.checkValidityCb' );
		}
		/**
		 * Initializes deiteration check events.
		 */
		const prepareDeiterationChecks = function() {

			let $window = jQuery( window );

			$window.off( 'tsfemForm.deiterationLoad.disableAndValidate' );
			$window.on( 'tsfemForm.deiterationLoad.disableAndValidate', disableAndValidate );
		}
		prepareDeiterationChecks();
	},

	/**
	 * Sets up special required fields, like a11y checkbox lists.
	 * This is done because we browser support for these fields lack whilst
	 * determining form push requirements.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	setupSpecialRequired: function() {

		let trapClass = 'tsfem-form-checkbox-required';

		let trap = document.createElement( 'input' );
		(function() {
			trap.className = trapClass;
			trap.setAttribute( 'type', 'checkbox' );
			trap.setAttribute( 'required', 'required' );
			trap.setAttribute( 'value', '1' );
		})();

		/**
		 * Adds or subtracts error count for $box.
		 *
		 * @param {jQuery.element} $box The a11y required box.
		 * @param {number} _i The addition (or subtraction).
		 */
		const countChecked = function( $box, _i ) {
			let newCount = ( +$box.data( 'required-check-count' ) || 0 ) + _i;
			$box.data( 'required-check-count', newCount );
			return newCount;
		}
		/**
		 * Adds invalid trap to $box.
		 *
		 * @param {jQuery.element} $box The a11y required box.
		 */
		const addTrap = function( $box ) {
			if ( ! $box.children( '.' + trapClass ).length ) {
				let newTrap = trap.cloneNode( false );
				newTrap.setCustomValidity( tsfemForm.i18n['requiredSelectAny'] );
				jQuery( newTrap ).prependTo( $box );
				tsfemForm.triggerCustomValidation( newTrap );
			}
			$box.removeClass( 'tsfem-form-multi-valid' ).addClass( 'tsfem-form-multi-invalid' );
		}
		/**
		 * Removes invalid trap from $box.
		 *
		 * @param {jQuery.element} $box The a11y required box.
		 */
		const removeTrap = function( $box ) {
			let $_trap = $box.children( '.' + trapClass );
			if ( $_trap.length ) {
				/**
				 * Because the old trap is tainted by jQuery, we need to rewrite
				 * it for it to pass validation.
				 * For some reason... Maybe we're just reading it from memory?
				 */
 				/*
 				jQuery( $_trap ).prop( 'checked', true );
 				jQuery( $_trap ).removeAttr( 'required' );
 				tsfemForm.triggerCustomValidation( $_trap[0] );
 				$_trap.remove();
 				*/
				let pseudoTrap = $_trap[0].cloneNode( false );
				$_trap.remove();
				pseudoTrap.checked = true;
				pseudoTrap.required = false;
				jQuery( pseudoTrap ).prependTo( $box );
				tsfemForm.triggerCustomValidation( pseudoTrap );
				pseudoTrap.remove();
			}
			$box.removeClass( 'tsfem-form-multi-invalid' ).addClass( 'tsfem-form-multi-valid' );
		}
		/**
		 * Tests and counts to see if invalid trap needs to be placed or removed.
		 *
		 * @param {jQuery.event} event The checkbox input change event.
		 */
		const testChecked = function( event ) {
			//* We can't test disabled. RC harness for showif. See tsfemForm.setupShowIfListener
			if ( event.target.disabled )
				return;

			let $box = jQuery( event.target ).closest( '.tsfem-form-multi-select-wrap[data-required="1"]' ),
				oldVal = $box.data( 'required-check-count' ),
				newVal;

			if ( event.target.checked ) {
				newVal = countChecked( $box, +1 );
			} else {
				newVal = countChecked( $box, -1 );
			}

			if ( oldVal < 1 ) {
				//= There was as trap.
				if ( newVal > oldVal ) {
					removeTrap( $box );
				}
			} else {
				if ( newVal < 1 ) {
					//= It's a trap!
					addTrap( $box );
				}
			}
		}
		/**
		 * Finds a11y boxes that might require traps.
		 */
		const find = function() {
			let $fields = jQuery( '.tsfem-form-multi-select-wrap[data-required="1"]' ),
				$box, countVal,
				checkedCount;

			$fields.each( function( index, element ) {
				countVal = element.dataset.requiredCheckCount;
				if ( void 0 === countVal ) {
					$box = jQuery( element );

					$box.find( 'input' ).on( 'change.tsfemForm.testChecked', testChecked );

					checkedCount = $box.find( 'input:checked' ).length;
					if ( checkedCount ) {
						$box.addClass( 'tsfem-form-multi-valid' );
					} else {
						addTrap( $box );
					}
					countChecked( $box, +checkedCount );
				}
			} );
		}
		find();
		jQuery( window ).on( 'tsfemForm.iterationComplete', find );
	},

	/**
	 * Sets up type listeners. So the type can be exclamated throughout the DOM.
	 *
	 * @since 1.3.0
	 * @access private
	 * @see tsfemForm.setupShowIfListener() which makes use of this data.
	 *
	 * @function
	 * @return {undefined}
	 */
	setupTypeListener: function() {

		/**
		 * Matches setter to value. When found, it returns the value to set type.
		 *
		 * @param {(object|string|number)} setter The value(s) of the setter to look for.
		 * @param {(string|number)} value The value to match.
		 * @return {(boolean|string|number)} False if not found. Value otherwise.
		 */
		const matchType = function( setter, value ) {
			let _v;

			if ( 'object' === typeof setter ) {
				for ( let i in setter ) {
					_v = matchType( setter[ i ], value );
					if ( false !== _v ) {
						return i;
					}
				}
			}

			if ( value === setter )
				return value;

			return false;
		}
		/**
		 * Finds type listeners and attaches change handlers.
		 *
		 * @param {jQuery.event} event The change event
		 * @return {undefined}
		 */
		const setType = function( event ) {
			let value, setter, type;

			//* Prevent additional runs on iteration.
			event.target.dataset.typeInitTested = 1;

			value = event.target.value;

			if ( ! value ) {
				//= Value is empty. No need to loop.
				event.target.dataset.type = '';
				jQuery( event.target ).trigger( 'tsfemForm.typeIsSet' );
				return;
			}

			setter = tsfemForm.parseElementData( event.target, 'setTypeToIfValue' );

			//= No setters found in data. Which is weird... Bail.
			if ( ! setter )
				return;

			type = matchType( setter, value );
			event.target.dataset.type = type || '';

			jQuery( event.target ).trigger( 'tsfemForm.typeIsSet' );
		}
		/**
		 * Finds type listeners and attaches change handlers.
		 */
		const find = function() {
			let $fields = jQuery( '[data-is-type-listener="1"]' ),
				$input, tested;

			$fields.each( function( index, element ) {
				if ( ! element.dataset.typeInitTested ) {
					$input = jQuery( element );
					$input.on( 'change.tsfemForm.typeListener', setType );
					$input.trigger( 'change.tsfemForm.typeListener' );
				}
			} );
		}
		find();
		jQuery( window ).on( 'tsfemForm.iterationComplete', find );
	},

	/**
	 * Sets up showIf listeners based on DOM values.
	 *
	 * @since 1.3.0
	 * @access private
	 * @see tsfemForm.setupTypeListener() which initiates data for this handler.
	 *
	 * @function
	 * @return {undefined}
	 */
	setupShowIfListener: function() {

		let things = 'input, select, textarea';

		let slideOps = {
			'duration' : 250,
			'easing' : 'linear',
			'queue' : false,
			'start' : function() {
				this.style.willChange = 'height, margin, padding';
			},
			'done' : function() {
				//= Prevent non-queue style-retain glitch on paint lag.
				let display = this.style.display;

				this.removeAttribute( 'style' );
				this.style.display = display;
			}
		};

		/**
		 * Maintains stacked disabled showif listeners through JSON dataset on
		 * a per trigger basis.
		 *
		 * @param {Element} element
		 * @param {string} trigger The trigger ID.
		 * @param {number} _i Whether to enable (1) or disable (-1).
		 */
		const setShown = function( element, trigger, _i ) {

			let isShown = element.dataset.isShown || void 0,
				isShownO;

			try {
				isShownO = JSON.parse( isShown );
			} catch( e ) {}

			isShownO = isShownO || {};

			//= Value didn't change. Return false.
			if ( isShownO.hasOwnProperty( trigger ) && +_i === +isShownO[ trigger ] )
				return false;

			isShownO[ trigger ] = +_i;

			element.dataset.isShown = JSON.stringify( isShownO );

			//= Value has changed. Return true.
			return true;
		}
		/**
		 * Counts disabled listener amount. When it's zero, it should be valid.
		 * Can't become lower than 0.
		 *
		 * @param {Element} element
		 * @param {number} _i The number to add or subtract for element.
		 */
		const countDisabled = function( element, _i ) {

			let count = element.dataset.disabledShowif;

			return element.dataset.disabledShowif = count ? +count + _i : +_i;
		}
		/**
		 * Hides element and counts how many times it's hidden, and maintains why
		 * it's hidden.
		 *
		 * @param {Element} element
		 * @param {string} trigger The trigger ID.
		 */
		const triggerHide = function( element, trigger ) {

			if ( ! setShown( element, trigger, -1 ) )
				return;

			let $el = jQuery( element ),
				count;

			//* Test is element is self (1), or a container (2).
			if ( $el.is( things ) ) {
				//= Hide then write.
				$el.closest( '.tsfem-form-setting' ).slideUp( slideOps );

				element.disabled = true;
				countDisabled( element, 1 );

				tsfemForm.triggerCustomValidation( element );
			} else {
				//= Hide then write.
				$el.slideUp( slideOps );
				$el.find( things ).each( function( index, _element ) {
					_element.disabled = true;
					countDisabled( _element, 1 );

					tsfemForm.triggerCustomValidation( _element );
				} );
			}
		}
		/**
		 * Might show element and counts how many times it should shown, and maintains why
		 * it's shown.
		 * When the hidden count is higher than the show count, then the item will
		 * not be shown until the count reaches 0.
		 *
		 * @param {Element} element
		 * @param {string} trigger The trigger ID.
		 */
		const triggerShow = function( element, trigger ) {

			if ( ! setShown( element, trigger, 1 ) )
				return;

			let $el = jQuery( element );

			//* Test is element is self (1), or a container (2).
			if ( $el.is( 'input, select, textarea' ) ) {
				//= Write then show.
				if ( countDisabled( element, -1 ) < 1 ) {
					element.disabled = false;
					tsfemForm.triggerCustomValidation( element );

					$el.closest( '.tsfem-form-setting' ).slideDown( slideOps );
				}
			} else {
				//= Write then show.
				$el.find( things ).each( function( index, _element ) {
					if ( countDisabled( _element, -1 ) < 1 ) {
						_element.disabled = false;
						tsfemForm.triggerCustomValidation( _element );
						jQuery( _element ).show();
					}
				} );
				$el.slideDown( slideOps );
			}
		}
		/**
		 * Attaches handler to element changes. Maintains buffer to prevent
		 * stacked animation effects.
		 *
		 * @param {Element} element The element that might be hidden or shown.
		 * @param {jQuery.element} target The target to listen to for value.
		 * @param {string} value The value the target must be, to show element.
		 */
		const attachHandler = function( element, target, value ) {

			let buffer = {};

			//= See tsfemForm.setupTypeListener.setType
			jQuery( target ).on( 'tsfemForm.typeIsSet', element, function( event ) {

				clearTimeout( buffer[ target.id ] );

				buffer[ target.id ] = setTimeout( function() {
					if ( target.dataset.type === value ) {
						triggerShow( element, target.id );
					} else {
						triggerHide( element, target.id );
					}
					delete buffer[ target.id ];
				}, 250 );
			} );

			//= Initial run.
			if ( target.dataset.type === value ) {
				triggerShow( element, target.id );
			} else {
				triggerHide( element, target.id );
			}
		}
		/**
		 * Finds attached target listeners for element that might contain type values
		 * to match, traversing up the DOM tree.
		 *
		 * @param {Element} element The element that might be hidden or shown.
		 * @return {(object|undefined)} Undefined if no target can be found.
		 */
		const findTarget = function( element ) {

			let data = tsfemForm.parseElementData( element, 'showif' );

			if ( ! data )
				return void 0;

			let $target,
				$collapse = jQuery( element ).closest( '.tsfem-form-collapse-content' ),
				val;

			// A single item. For now.
			for ( val in data ) break;

			if ( ! val )
				return void 0;

			if ( $collapse ) {
				//= Collapse.
				$target = $collapse.find( '[data-showif-catcher="' + val + '"]' );
			} else {
				//= Non-Collapse.
				$target = jQuery( element.form ).find( '[data-showif-catcher="' + val + '"]' );
			}

			if ( ! $target.length )
				return void 0;

			return {
				'target' : $target[0],
				'value' : data[ val ]
			};
		}
		/**
		 * Initializes showif functionality for element.
		 * Attaches event handlers when the element has attached fields to listen for.
		 *
		 * @param {Element} element The element that might be hidden or shown.
		 */
		const showIfInit = function( element ) {

			element.dataset.showIfIsInit = '1';

			let data = findTarget( element );
			if ( data )
				attachHandler( element, data.target, data.value );
		}
		/**
		 * Initializes showif functionality by getting fields.
		 * It runs once per element, preventing duplication by adding data.
		 */
		const init = function() {
			let $fields = jQuery( '[data-is-showif-listener="1"]' );

			$fields.each( function( index, element ) {
				element.dataset.showIfIsInit || showIfInit( element );
			} );
		}
		init();
		jQuery( window ).on( 'tsfemForm.iterationComplete', init );
	},

	/**
	 * Adjusts Save button so it works through AJAX.
	 * Which, through doValidityRoutine, also prevents focus errors on form
	 * submission while elements are hidden.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	adjustSubmit: function() {

		let $forms = jQuery( 'form.tsfem-form' );

		$forms.each( function() {
			jQuery( document.querySelector( '[type=submit][form="' + this.id + '"]' ) ).attr( 'onclick', 'tsfemForm.saveInput( event )' );
		} );
	},

	/**
	 * Goes through form to check for validity.
	 * Prevents focus errors on form submission while elements are hidden.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @param {Element} form The form to validate.
	 * @param {string} notification The notification shown above invalid wrappers.
	 * @return {Boolean} True if form is valid. False otherwise.
	 */
	doValidityRoutine: function( form, notification ) {

		notification = notification || tsfemFormL10n.i18n['collapseValidate'];

		if ( ! form.checkValidity() ) {
			let firstInvalid = form.querySelector( 'input:invalid, select:invalid, textarea:invalid' );
			let enclosed = false;

			/**
			 * Tries validation report on the first invalid field found.
			 * When errors are plausibly enclosed in hidden fields, it will
			 * propagate itself through tryAdvancedReport.
			 * On fail, it will log the error.
			 *
			 * @return {undefined}
			 */
			const tryReport = function() {
				//= If the header is closed, we can't report validity.
				if ( enclosed ) {
					tryAdvancedReport();
					return;
				} else {
					try {
						firstInvalid.reportValidity();
						return;
					} catch ( err ) {
						tsfem.debug && console.log( err );
					};
				}
			}
			/**
			 * Tries validation report for collapse fields.
			 * Outputs self-generated tooltips, rather than what the browser
			 * outputs, until the browser can interfere.
			 *
			 * @return {(boolean|undefined)} False on failure.
			 */
			const tryAdvancedReport = function() {
				let $headers = jQuery( firstInvalid ).parents( '.tsfem-form-collapse' ),
					depth = -1;

				//= Reverse the stack
				$headers = jQuery( $headers.get().reverse() );

				jQuery( $headers ).each( function( index ) {
					let isChecked = jQuery( this ).children( '.tsfem-form-collapse-checkbox' ).is( ':checked' );
					if ( isChecked ) {
						depth = index;
						// Break loop.
						return false;
					}
				} );

				if ( -1 === depth ) {
					enclosed = false;
					return tryReport();
				}
				enclosed = true;

				let $header, $label, $checkbox;

				$header = $headers.slice( depth ).first();
				$label = $header.find( '.tsfem-form-collapse-header' ).first();
				$checkbox = $header.find( '.tsfem-form-collapse-checkbox' ).first();

				let scrollToTimeout;
				const scrollTo = function( $to, $wrap ) {
					//= Let the tooltip be painted first.
					clearTimeout( scrollToTimeout );
					scrollToTimeout = setTimeout( function() {
						/**
						 * We require the current scrollTop in this calculation
						 * as the $to offset changes based on its position.
						 *
						 * We grab $wrap.position.top to create a nice relatable
						 * offset. It isn't to be trusted, but in this context
						 * it will always work.
						 */
						let _to = ( $to.offset().top + $wrap.prop( 'scrollTop' ) ) - $wrap.offset().top - $wrap.position().top;

						if ( $wrap.prop( 'scrollHeight' ) > $wrap.prop( 'clientHeight' ) ) {
							$wrap.animate( { 'scrollTop' : _to }, 500 );
						}
					}, 50 );
				}
				const removeToolTip = function( e ) {
					let $this = jQuery( e.target );

					tsfem.removeTooltip( $this.siblings( '.tsfem-form-collapse-header' ) );

					$this.off( 'change.tsfemForm.removeToolTip' );
					tryAdvancedReport();
				}

				//= Create tooltip, scroll to it, add tooltip listeners.
				tsfem.doTooltip( $label, notification );
				scrollTo($label, $label.closest( '.tsfem-pane-inner-wrap' ) );
				//scrollTo( tsfem.getTooltip( $label ), $label.closest( '.tsfem-pane-inner-wrap' ) );
				$checkbox.off( 'change.tsfemForm.removeToolTip' );
				$checkbox.on( 'change.tsfemForm.removeToolTip', removeToolTip );
			}

			if ( firstInvalid ) {
				tryAdvancedReport();
			} else {
				tryReport();
			}
			return false;
		}

		return true;
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
			if ( ! form )
				return tsfemForm.preventSubmit( event );

			if ( ! tsfemForm.doValidityRoutine( form, tsfemFormL10n.i18n['collapseValidate'] ) )
				return;

			button = event.target;
		} else {
			form = event.target;
			button = document.querySelector( '[type=submit][form="' + form.id + '"]' );
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

			tsfem.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			if ( ! data || ! type ) {
				//* Erroneous output.
				loaderText = tsfem.i18n['InvalidResponse'];
			} else {
				let rCode = data.results && data.results.code || void 0;

				if ( rCode ) {
					/**
					 * status doesn't have to be 1. We actually need to
					 * switch through the status codes to determine
					 * it being 1 or 0.
					 * @todo
					 */
					status = 1;
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
	 * @access public
	 *
	 * @function
	 * @param {event.target} target The button(s) to enable.
	 * @return {boolean} false
	 */
	enableButton: function( target ) {
		//= setTimeout prevents paint lag.
		setTimeout( function() {
			let $target = jQuery( target );
			$target.removeClass( 'tsfem-button-disabled tsfem-button-loading' );
			$target.attr( 'disabled', false );
		}, 1 );
	},

	/**
	 * Disables button.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @param {event.target} target The button(s) to disable.
	 * @param {boolean} loading Whether to hint it's disabled.
	 * @return {boolean} false
	 */
	disableButton: function( target, loading ) {

		loading = void 0 === loading ? true : loading;

		let $target = jQuery( target );

		if ( loading ) {
			$target.addClass( 'tsfem-button-disabled tsfem-button-loading' );
		} else {
			$target.addClass( 'tsfem-button-disabled' );
		}
		$target.attr( 'disabled', true );
	},

	/**
	 * Enables form submission.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {Element} form The form to enable submit from.
	 * @return {boolean} false
	 */
	enableSubmit: function( form ) {
		if ( form && form.id ) {
			tsfemForm.enableButton( '[form="' + form.id + '"]' );
		}
	},

	/**
	 * Disables form submission.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {Element} form The form to disable submit from.
	 * @param {boolean} loading Whether to hint it's disabled.
	 * @return {boolean} false
	 */
	disableSubmit: function( form, loading ) {
		if ( form && form.id ) {
			tsfemForm.disableButton( '[form="' + form.id + '"]', loading );
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

		//* Prepare required fields for special fields.
		jQ( document.body ).ready( tsfemForm.setupSpecialRequired );

		//* Prepare type setters.
		jQ( document.body ).ready( tsfemForm.setupTypeListener );

		//* Prepare show-if listeners.
		jQ( document.body ).ready( tsfemForm.setupShowIfListener );

		//* Turn form submit into an AJAX pusher.
		jQ( document.body ).ready( tsfemForm.adjustSubmit );
	}
};
jQuery( tsfemForm.ready );
