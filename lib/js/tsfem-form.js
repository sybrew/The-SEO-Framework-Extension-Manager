/**
 * This file holds The SEO Framework Extension Manager plugin's JS code for Extension
 * Form generation and iteration.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfemForm values in an object to avoid polluting global namespace.
 *
 * TODO dejQueryfy this. Should improve performance up to nearly 100%.
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
	nonce: tsfemFormL10n.nonce,

	/**
	 * @since 1.3.0
	 * @access private
	 * @param {Object} i18n Localized strings
	 */
	i18n: tsfemFormL10n.i18n,

	/**
	 * @since 1.3.0
	 * @access private
	 * @TODO make multiple callee's possible? I.e. multiple iterable forms.
	 * @type {String} callee Caller class for iterations
	 */
	callee: tsfemFormL10n.callee,

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
	parseElementData: ( element, data ) => {

		let value = tsfemForm.getElementData( element, data ),
			ret   = void 0;

		try {
			ret = value && JSON.parse( value );
		} catch ( e ) {
			ret = value;
		}

		return ret;
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
	getElementData: ( element, data ) => {

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
	 * @todo use animation frames instead?
	 *
	 * @function
	 * @return {undefined}
	 */
	setupIterations: () => {

		let $items = jQuery( '.tsfem-form-iterator-selector-wrap input' );

		if ( ! $items.length )
			return;

		tsfemForm.prepareItItems( $items );

		let itBuffer = 0,
			itTimeout = 1500,
			vBar, vBarS, vBarTimeout, vBarWidth = 0, vBuffer = 0,
			vBarSmoothness = 2,
			vBarSuperSmooth = true,
			fIt,
			$item, $label, $curBar,
			lastTarget;

		// (Re)create visual timer bar.
		const vBarReset = () => {
			vBar = document.createElement( 'span' );
			vBar.className = 'tsfem-form-iterator-timer';
			vBarS = document.createElement( 'span' );
			vBarS.style.width = '0%';
			vBar.appendChild( vBarS );
		}
		vBarReset();
		vBarTimeout = itTimeout / ( 100 * vBarSmoothness );

		// Subtract a little of the bar timer to prevent painting/scripting overlap.
		if ( vBarSuperSmooth )
			vBarTimeout *= 0.975;

		const vBarGo = () => {
			vBarS.style.width = ++vBarWidth / vBarSmoothness + '%';
		}
		const vBarStop = () => {
			vBarWidth = 0;
			vBarS.style.width = '0%';
			vBar.classList.remove( 'tsfem-form-iterator-timer-invalid' );
		}
		const vBarInvalid = () => {
			vBarWidth = 100;
			vBarS.style.width = vBarWidth + '%';
			vBar.classList.add( 'tsfem-form-iterator-timer-invalid' );
		}
		const resetLoaders = () => {
			$curBar && $curBar.remove();
			$item = $label = $curBar = void 0;
			vBarReset();
			setTimeout( () => {
				$items.prop( 'disabled', false );
			}, 500 );
			$items.off( 'input.fIt' ).on( 'input.fIt', fIt );
		}
		const rebuildEvents = () => {
			$items.off( 'input.fIt' );
			$items = jQuery( '.tsfem-form-iterator-selector-wrap input' );
			tsfemForm.prepareItItems( $items );
			tsfemForm.prepareCollapseItems();
			tsfemForm.setupGeo();
			'tsfMedia' in window && tsfMedia.resetImageEditorActions();
			$items.on( 'input.fIt', fIt );
			tsfTT.triggerReset();
			lastTarget = void 0;
		}
		const undoInput = () => {
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
		fIt = e => {

			// (re)set visual countdown timer.
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

				// Disable other iterators.
				$items.not( e.target ).prop( 'disabled', true );

				// Show and assign timer.
				$label.append( vBar );
				$curBar = $label.find( 'span.tsfem-form-iterator-timer' );
			}

			itBuffer = setTimeout( () => {

				// Disable own iterator.
				$item.prop( 'disabled', true );

				// Race condition fix. Some browsers perform before they can paint.
				clearInterval( vBuffer );
				vBarS.style.width = '100%';

				if ( +e.target.value === +e.target.dataset.tsfemFormPrevValue ) {
					// Welp... Nothing happened.
					resetLoaders();
					tsfemForm.enableSubmit( e.target.form );
				} else if ( +e.target.value < +e.target.dataset.tsfemFormPrevValue ) {
					// Deiterate.
					tsfemForm.unloadIterations( e.target );
					tsfemForm.enableSubmit( e.target.form );
				} else {
					// Iterate.
					if ( +e.target.value > 40 ) {
						if ( +e.target.value > 200 ) {
							tsfem_ui.dialog( {
								'title' : tsfemFormL10n.i18n['performanceWarning'],
								'text' : [ tsfemFormL10n.i18n['itHugeConfirm'], tsfemFormL10n.i18n['aysProceed'] ],
								'confirm' : tsfemFormL10n.i18n['proceed'],
								'cancel' : tsfemFormL10n.i18n['cancel'],
							} );
						} else {
							tsfem_ui.dialog( {
								'title' : tsfemFormL10n.i18n['performanceWarning'],
								'text' : [ tsfemFormL10n.i18n['itLargeConfirm'], tsfemFormL10n.i18n['aysProceed'] ],
								'confirm' : tsfemFormL10n.i18n['proceed'],
								'cancel' : tsfemFormL10n.i18n['cancel'],
							} );
						}

						let _events = {};

						_events._cancel = () => {
							undoInput();
							resetLoaders();
							_events._reset();
							tsfemForm.enableSubmit( e.target.form );
						}
						_events._confirm = () => {
							tsfemForm.loadIterations( e.target );
							_events._reset();
						}
						_events._reset = () => {
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
	unloadIterations: target => {

		if ( ! target )
			return;

		// Get ID without '[count]', '[number]', or any other current iteration key.
		let itId          = target.id.slice( 0, target.id.lastIndexOf( '[' ) ),
			outputWrapper = document.getElementById( itId + '-wrapper' );

		outputWrapper.style.willChange = 'contents';

		let $window   = jQuery( window ),
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
	loadIterations: target => {

		if ( ! target )
			return;

		// Get ID without '[count]', '[number]', or any other current iteration key.
		let itId = target.id.slice( 0, target.id.lastIndexOf( '[' ) );

		// Wrap outer items.
		let $loader = jQuery( target ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status  = 0, loaderText = '';

		// Wrap inner items.
		let outputWrapper = document.getElementById( itId + '-wrapper' ),
			waiter        = document.createElement( 'div' );

		waiter.className = 'tsfem-flex-status-loading tsfem-flex tsfem-flex-center';
		waiter.appendChild( document.createElement( 'span' ) );

		// WillChange will stop the waiter from turning. Also, no added benefit found...
		// outputWrapper.style.willChange = 'transform, contents';

		outputWrapper.appendChild( waiter );

		// Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		// Set ajax loader.
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
			 * 10 seconds.
			 * It's massive and we might wish to fine-tune this based on
			 * non-cached slow PHP5.5 instances and expectancies based on
			 * item count fetching.
			 */
			timeout: 10000,
			async: true,
		} ).done( ( response, _status, xhr ) => {
			const contentType = xhr.getResponseHeader( 'content-type' ),
				  $window     = jQuery( window );

			$window.trigger( 'tsfemForm.iterationLoad' );

			if ( contentType.indexOf( 'json' ) > -1 ) {
				// We didn't ask for JSON. This means something interfered.
				target.value = target.dataset.tsfemFormPrevValue;
				tsfem_ui.unexpectedAjaxErrorNotice( response );
				loaderText = tsfem.i18n['InvalidResponse'];
				$window.trigger( 'tsfemForm.iterationFail' );
			} else {
				jQuery( outputWrapper ).append( response );
				status = 1;
				// @TODO add $(response).children($headers) to trigger data?
				$window.trigger( 'tsfemForm.iterationComplete' );
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );
			errorThrown && tsfem_ui.setTopNotice( -1, `Thrown error: ${errorThrown}` );
			jQuery( window ).trigger( 'tsfemForm.iterationFail' );
		} ).always( () => {
			outputWrapper.removeChild( waiter );
			// outputWrapper.style.willChange = 'auto';
			tsfemForm.enableSubmit( target.form );
			tsfem.updatedResponse( $loader, status, loaderText );
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
	prepareItItems: $items => {

		// Sets last known iteration values.
		$items.each( ( i, el ) => {
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
	setupGeo: () => {

		if ( ! jQuery( '[data-geo-api-component=action]' ).length )
			return;

		let fillingAddress = false;

		// Fills buttonWrap, with GC for stray items.
		{
			var buttonWrap = document.createElement( 'div' );
			buttonWrap.className = 'tsfem-form-setting-action tsfem-flex';
			buttonWrap.dataset.geoApiIsButtonWrap = 1;
			buttonWrap.style.opacity = 0;

			let button = document.createElement( 'button' );
			button.className = 'tsfem-button-primary tsfem-button-primary-bright tsfem-button-cloud';
			button.innerHTML = tsfemForm.i18n['validate'];
			button.type = 'button';
			buttonWrap.appendChild( button );

			var reverseWarningWrap = document.createElement( 'div' );
			reverseWarningWrap.className = 'tsfem-description tsfem-form-option-description';
			reverseWarningWrap.dataset.geoApiIsButtonWarning = 1;
			reverseWarningWrap.style.opacity = 0;
			reverseWarningWrap.innerHTML = tsfemForm.i18n['reverseGeoWarning'];
		};

		/**
		 * Fills in address fields by requested input data from dialog.
		 *
		 * @param {event.target} target The propagated button target.
		 * @param {object} data The address data.
		 * @return {undefined}
		 */
		const fillAddress = ( target, data ) => {

			let $wrap   = jQuery( target ).closest( '[data-geo-api-component=action]' ),
				$fields = getFields( $wrap );

			// Prevent re-checking own fields when filling.
			fillingAddress = true;

			$fields.each( ( index, element ) => {
				let components   = tsfemForm.parseElementData( element, 'geoApiComponent' ),
					routeCombine = {};

				// Convert to object if not.
				components = 'object' === typeof components && components || { 0 : components };

				loopComponents:
				for ( let i in components ) {
					if ( data.hasOwnProperty( components[ i ] ) ) {
						switchComponents:
						switch ( components[ i ] ) {
							case 'route':
							case 'street_number':
								// Collect route and street number if combined.
								if ( components.length > 1 ) {
									routeCombine[ components[ i ] ] = data[ components[ i ] ];
									if ( Object.keys( routeCombine ).length > 1 ) {
										// Fill if 2 are matched.
										element.value = `${routeCombine['route']} ${routeCombine['street_number']}`;
										break loopComponents;
									}
									break switchComponents;
								} else {
									element.value = data[ components[ i ] ];
									break loopComponents;
								}
								break;

							case 'locality':
							case 'country':
							case 'postal_code':
							case 'region':
								element.value = data[ components[ i ] ];
								break loopComponents;

							case 'lat':
							case 'lng':
								// Convert string to float, convert float to 7 decimal places.
								element.value = parseFloat( parseFloat( data[ components[ i ] ] ).toFixed( 7 ) );
								break loopComponents;

							default:
								break loopComponents;
						}
					}
					continue;
				}

				// Lets the world know something happened.
				jQuery( element ).trigger( 'change' );
			} );

			// Reset checking fields.
			fillingAddress = false;
		}
		/**
		 * Opens dialog where the user can select an address.
		 *
		 * @param {event.target} target
		 * @param {object|array} data
		 * @return {undefined}
		 */
		const selectAddress = ( target, data ) => {

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
							case 'route':
							case 'street_number':
							case 'country':
							case 'postal_code':
								_optionValues[ i ][ types[ __i ] ] = shortname;
								break loopTypes;

							case 'locality':
								_optionValues[ i ]['locality'] = shortname;
								break loopTypes;

							case 'postal_town':
								// Only fill this in if no locality is set.
								if ( ! types.locality ) _optionValues[ i ]['locality'] = shortname;
								break loopTypes;

							case 'administrative_area_level_1':
								_optionValues[ i ]['region'] = shortname;
								break loopTypes;

							case 'administrative_area_level_2':
								// Only fill this in if no administrative_area_level_1 is set.
								if ( ! types.administrative_area_level_1 ) _optionValues[ i ]['region'] = shortname;
								break loopTypes;

							default:
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
				if ( data.length > 1 && ! _optionValues[ i ].hasOwnProperty( 'route' ) ) {
					delete _optionValues[ i ];
					delete _optionFields[ i ];
				}
			};

			if ( ! Object.keys( _optionValues ).length ) {
				// Notifies no results are found.
				tsfem_ui.setTopNotice( 17004 );
				unloadButton( jQuery( target ).closest( '[data-geo-api-component=action]' ) );
			} else {
				tsfem_ui.dialog( {
					'title' : tsfemFormL10n.i18n['selectAddressTitle'],
					'text' : tsfemFormL10n.i18n['selectAddressText'],
					'select' : _optionFields,
					'confirm' : tsfemFormL10n.i18n['select'],
					'cancel' : tsfemFormL10n.i18n['cancel'],
				} );

				let _events = {};

				_events._cancel = () => {
					tsfemForm.enableButton( target );
					_events._close();
				}
				_events._confirm = e => {
					if ( 'checked' in e.detail ) {
						let _dataFill = _optionValues[ e.detail.checked ];
						if ( void 0 !== _dataFill ) fillAddress( target, _dataFill );
					}
					unloadButton( jQuery( target ).closest( '[data-geo-api-component=action]' ) );
					_events._close();
				}
				_events._close = () => {
					window.removeEventListener( 'tsfem_modalCancel', _events._cancel );
					window.removeEventListener( 'tsfem_modalConfirm', _events._confirm );
				}
				window.addEventListener( 'tsfem_modalCancel', _events._cancel );
				window.addEventListener( 'tsfem_modalConfirm', _events._confirm );
			}
		}
		/**
		 * Requests AJAX call for geocoding data, opens modal and propagates
		 * events to new functions, like `selectAddress`.
		 *
		 * @param {event} event The button click event.
		 * @return {undefined}
		 */
		const requestGeoData = event => {

			let $wrap = jQuery( event.target ).closest( '[data-geo-api-component=action]' ),
				$fields = getFields( $wrap ),
				_data = {};

			tsfemForm.disableButton( event.target );

			$fields.each( ( index, element ) => {
				if ( element.value )
					_data[ element.dataset.geoApiComponent ] = element.value;
			} );

			let completeData = {
				target: event.target,
				callbacks: {
					success : selectAddress,
					failure : () => {
						tsfemForm.enableButton( event.target );
					},
					always : () => {},
				},
			};

			// Get data, open modals, etc.
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
		const loadButton = ( $wrap, formId, valid ) => {

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

				// Add form target id
				_node.getElementsByTagName( 'button' )[0].dataset.formId = formId;
				_node.getElementsByTagName( 'button' )[0].addEventListener( 'click', requestGeoData );

				tsfem_ui.fadeIn( _node, 300 );
			}

			let $warning = $target.find( '[data-geo-api-is-button-warning]' ).first();

			if ( 2 === valid ) {
				if ( $warning.length )
					return;

				let _warnNode = reverseWarningWrap.cloneNode( true );
				$target.append( _warnNode );
				tsfem_ui.fadeIn( _warnNode, 300 );
			} else {
				if ( ! $warning.length )
					return;

				tsfem_ui.fadeOut( $warning[0], 300, () => $warning[0].remove() );
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
		const updateButton = ( $wrap, formId, valid ) => {
			loadButton( $wrap, formId, valid );
		}
		/**
		 * Unloads Geo validation button from $wrap.
		 *
		 * @param {jQuery.element} $wrap The target wrap.
		 * @return {undefined}
		 */
		const unloadButton = $wrap => {
			let $target = $wrap && $wrap.find( '.tsfem-form-multi-setting-label-inner-wrap' ) || void 0;

			$target && $target.find(
				$target.children( '[data-geo-api-is-button-wrap], [data-geo-api-is-button-warning]' )
			).fadeOut( 300, function () {
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
		const getFields = $wrap => $wrap.find( '[data-geo-api="1"]' );
		/**
		 * Validate Address fields. Returns 2 if lat/lng are valid. 1 or 0 otherwise.
		 *
		 * @param {jQuery.element} $wrap The geodata parent button wrapper.
		 * @return {number} Whether the field is valid. 2, 1 or 0.
		 */
		const validateFields = $wrap => {

			let $fields = getFields( $wrap );

			if ( ! $fields.length )
				return 0;

			let valid = 0,
				lat   = $fields.filter( '[data-geo-api-component=lat]' ).first().val(),
				lng   = $fields.filter( '[data-geo-api-component=lng]' ).first().val(),
				route;

			$fields.each( ( index, element ) => {
				if ( jQuery( element ).data( 'geo-api-component' ).indexOf( 'route' ) > -1 ) {
					route = element.value;
					return false;
				}
			} );

			if ( lat && lng ) {
				valid = /^(\-|\+)?([0-9]+(\.[0-9]+)?)$/.test( lat ) && lat >= -90 && lat <= 90 ? 2 : 0;
				valid = valid && /^(\-|\+)?([0-9]+(\.[0-9]+)?)$/.test( lng ) && lng >= -180 && lng <= 180 ? 2 : 0;
			} else if ( route ) {
				// This tests street_name+number, number+street_name. UTF-8.
				valid = /^((([0-9\/-]+([\/-0-9A-Z]+)?(\s|(,\s)))([\u00a1-\uffffa-zA-Z\.\s]|[0-9_/-])+))|(([\u00a1-\uffffa-zA-Z\.\s]|[0-9_/-])+)((\s|(,\s))([0-9\/-]+([\/-0-9A-Z]+)?))$/
						.test( route ) ? 1 : 0;

				if ( ! valid ) {
					/**
					 * Test if there's at least one other component filled in.
					 * It also counts route.
					 * @since 1.5.0
					 */
					let hasValues = 0;
					$fields.not( '[data-geo-api-component=lat], [data-geo-api-component=lng]' ).each( ( i, element ) => {
						if ( element.value.length ) {
							if ( ++hasValues > 1 ) {
								// Break loop on threshold.
								return false;
							}
						}
					} );

					valid = hasValues > 1;
				}
			}

			return valid;
		}

		let tBuffer = 0, tTimeout = 500;
		/**
		 * @param {event} event The geodata form input event
		 * @return {undefined} Early if filling address.
		 */
		const addButtonIfValid = event => {

			if ( fillingAddress )
				return;

			clearTimeout( tBuffer );
			tBuffer = setTimeout( () => {

				if ( ! tsfemForm.getElementData( event.target, 'geoApiComponent' ) )
					return;

				let $wrap = jQuery( event.target ).closest( '[data-geo-api-component=action]' ),
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

		let $input  = jQuery( 'input[data-geo-api="1"]' ),
			$select = jQuery( 'select[data-geo-api="1"]' );

		if ( $input ) {
			$input.off( 'input.tsfemForm.addButtonIfValid' ).on( 'input.tsfemForm.addButtonIfValid', addButtonIfValid );
		}
		if ( $select ) {
			$select.off( 'change.tsfemForm.addButtonIfValid' ).on( 'change.tsfemForm.addButtonIfValid', addButtonIfValid );
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
	getGeoData: ( input, formId, completeData ) => {

		let form = document.getElementById( formId );

		let $loader    = jQuery( form ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status     = 0,
			loaderText = '';

		// Disable form submission.
		tsfemForm.disableSubmit( form );

		// Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		// Set ajax loader.
		tsfem.setAjaxLoader( $loader );

		// Do AJAX and return object.
		return jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'tsfemForm_get_geocode',
				nonce: tsfemForm.nonce,
				input: JSON.stringify( input ),
			},
			processData: true,
			timeout: 14000,
			async: true,
		} ).done( response => {

			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			let data = response?.data,
				type = response?.type;

			if ( ! data || ! type ) {
				// Erroneous output.
				loaderText = tsfem.i18n['InvalidResponse'];
			} else if ( 'failure' === type ) {
				tsfem_ui.unexpectedAjaxErrorNotice( response );
				completeData.callbacks.failure( completeData.target );
			} else if ( 'geodata' in data ) {
				status = 1;
				completeData.callbacks.success( completeData.target, data.geodata.results );
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			// Set AJAX response for wrapper.
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );
			tsfem_ui.setTopNotice( 17200 );
			errorThrown && tsfem_ui.setTopNotice( -1, `Thrown error: ${errorThrown}` );

			completeData.callbacks.failure( completeData.target );
		} ).always( () => {
			tsfem.updatedResponse( $loader, status, loaderText );
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
	prepareCollapseItems: () => {

		const prepareItems = event => {
			tsfemForm.prepareCollapseTitles( event );
			tsfemForm.prepareCollapseValidity( event );
		}

		jQuery( '.tsfem-form-collapse > input' )
			.off( 'change.tsfemForm.prepareItems' )
			.on( 'change.tsfemForm.prepareItems', prepareItems )
			.trigger( 'change.tsfemForm.prepareItems' );
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
	prepareCollapseTitles: event => {

		/**
		 * Changes title based on single input.
		 *
		 * @param {event} event The target input field change.
		 */
		const doTitleChangeSingle = event => {

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.find( '.tsfem-form-collapse-title' ),
				prep   = $label.data( 'dyntitleprep' ),
				val    = event.target.value;

			if ( val ) {
				$title.text( `${prep} - ${val}` );
			} else {
				$title.text( prep );
			}
		}
		/**
		 * Changes title based on single input.
		 *
		 * @param {event} event The target input field change.
		 */
		const doTitleChangeCheckbox = event => {

			let checked = event.target.checked || false;

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.find( '.tsfem-form-collapse-title' ),
				prep   = $label.data( 'dyntitleprep' );

			if ( checked ) {
				$title.text( `${prep} - ${$label.data( 'dyntitlechecked' )}` );
			} else {
				$title.text( prep );
			}
		}
		/**
		 * Changes title based on plural options, i.e. checkboxes.
		 *
		 * @param {event} event The target input fields change.
		 */
		const doTitleChangePlural = event => {

			let $label = jQuery( event.data._tsfemFormLabel ),
				$title = $label.find( '.tsfem-form-collapse-title' ),
				prep   = $label.data( 'dyntitleprep' ),
				vals   = [];

			jQuery( event.data._tsfemFormThings ).map( ( i, element ) => {
				element.checked && vals.push( element.value );
			} );

			let val = vals?.join( ', ' );

			if ( val ) {
				$title.text( `${prep} - ${val}` );
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
		const prepareTitleChange = event => {

			let $label = jQuery( event.target ).siblings( 'label' ),
				type   = $label.data( 'dyntitletype' ),
				key    = $label.data( 'dyntitleid' ) + '[' + $label.data( 'dyntitlekey' ) + ']',
				action;

			switch ( type ) {
				case 'single':
				case 'checkbox':
					let el = document.getElementById( key );

					action = 'input.tsfemForm.doTitleChangeSingle';

					jQuery( el )
						.off( action )
						.on(
							action,
							{ _tsfemFormLabel: $label },
							'checkbox' === type ? doTitleChangeCheckbox : doTitleChangeSingle
						)
						.trigger( action );
					break;

				case 'plural':
					let $things = jQuery( document.getElementById( key ) ).find( 'input[type=checkbox]' );

					action = 'input.tsfemForm.doTitleChangeSingle';;

					$things
						.off( action )
						.on(
							action,
							{
								_tsfemFormLabel: $label,
								_tsfemFormThings: $things
							},
							doTitleChangePlural
						)
						.trigger( action );
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
	prepareCollapseValidity: event => {

		/**
		 * Adjusts classes for headers and the icons based on errors found.
		 *
		 * @param {jQuery.element} $headers The headers set classes for.
		 * @param {boolean} hasErrors Whether errors are present.
		 */
		const setClasses = ( $header, hasErrors ) => {

			let errorClass = 'tsfem-form-collapse-header-error',
				goodClass = 'tsfem-form-collapse-header-good';

			let iconSelector = '.tsfem-form-title-icon',
				iconUnknown  = 'tsfem-form-title-icon-unknown',
				iconError    = 'tsfem-form-title-icon-error',
				iconGood     = 'tsfem-form-title-icon-good';

			if ( hasErrors > 0 ) {
				$header.removeClass( goodClass ).addClass( errorClass );
				$header.find( iconSelector ).removeClass( `${iconUnknown} ${iconGood}` ).addClass( iconError );
			} else if ( hasErrors < 0 ) {
				$header.removeClass( `${errorClass} ${goodClass}` ).addClass( goodClass );
				$header.find( iconSelector ).removeClass( `${iconGood} ${iconError}` ).addClass( iconUnknown );
			} else {
				$header.removeClass( errorClass ).addClass( goodClass );
				$header.find( iconSelector ).removeClass( `${iconUnknown} ${iconError}` ).addClass( iconGood );
			}
		}
		/**
		 * Adds or subtracts error count for each $headers.
		 *
		 * @param {jQuery.element} $headers The headers to count errors for.
		 * @param {number} _i The addition (or subtraction).
		 */
		const countErrors = ( $headers, _i ) => {

			let $header,
				newCount;

			$headers.each( ( i, element ) => {
				$header  = jQuery( element );
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
		const checkValidity = event => {

			let wasValid = 0;
			if ( 'tsfemWasValid' in event.target.dataset ) {
				wasValid = +event.target.dataset.tsfemWasValid;
			} else {
				wasValid = event.target.dataset.tsfemWasValid = 1;
			}

			// It's valid when it's disabled :)
			let inputIsValid = event.target.disabled || event.target.checkValidity();

			/*
			// Neglegible performance effect, without the added benefit of header indication.
			if ( wasValid && inputIsValid ) {
				return;
			}
			*/

			// hadErrors works by counting the existence of data.
			let $headers = jQuery( event.target ).parents( '.tsfem-form-collapse' ).children( '.tsfem-form-collapse-header' ),
				hadErrors = +$headers.data( 'tsfemErrorCount' );

			if ( hadErrors ) {
				// Header is already marked invalid.
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
		const doFirstCheck = ( button, $items ) => {

			let didCheck = button.dataset.tsfemDidInitialValidation || 0;

			if ( ! didCheck ) {
				// Do initial run.
				$items.on( 'tsfemForm.first.checkValidity', checkValidity );
				$items.each( ( i, element ) => { jQuery( element ).trigger( 'tsfemForm.first.checkValidity' ); } );
				$items.off( 'tsfemForm.first.checkValidity' );

				// Register run.
				button.dataset.tsfemDidInitialValidation = 1;
			}
		}
		/**
		 * Prepares input change events when collapsable item is expanded.
		 * It's done this way to prevent huge onLoad iterations.
		 *
		 * @param {event} event The checkbox button activation event.
		 */
		const prepareChecks = event => {

			let $items = jQuery( event.target ).siblings( '.tsfem-form-collapse-content' ).find( 'input, select, textarea' ).not( '.tsfem-form-collapse-checkbox' );

			// Always turn the event off to prevent duplication.
			$items.off( 'change.tsfemForm.checkValidity' );

			// Reinitiate the event if the collapse header is expanded (unchecked).
			if ( ! event.target.checked ) {
				$items.on( 'change.tsfemForm.checkValidity', checkValidity );
				// doFirstCheck( event.target, $items );
			}

			doFirstCheck( event.target, $items );
		}
		prepareChecks( event );

		// Register custom validity checks. Pass checkValidity callback.
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
	prepareCustomChecks: checkValidityCb => {

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
		const customChecks = ( event, element ) => {

			if ( ! element )
				return;

			// Find and capture element.
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
		const addCustomChecks = () => {
			jQuery( window ).on( 'tsfemForm.customValidationChecks', customChecks );
		}
		/**
		 * Removes listener to window for custom validation checks.
		 *
		 * @return {undefined}
		 */
		const removeCustomChecks = () => {
			jQuery( window ).off( 'tsfemForm.customValidationChecks' );
		}
		/**
		 * Prepares listener to window for custom validation checks.
		 *
		 * @return {undefined}
		 */
		const prepareCustomChecks = () => {
			// Initial.
			removeCustomChecks();
			addCustomChecks();

			jQuery( window )
				.off( 'tsfemForm.iterationLoad.customValidation' )
				.off( 'tsfemForm.iterationFail.customValidation' )
				.off( 'tsfemForm.iterationComplete.customValidation' )
				.on( 'tsfemForm.iterationLoad.customValidation', removeCustomChecks )
				.on( 'tsfemForm.iterationFail.customValidation', addCustomChecks )
				.on( 'tsfemForm.iterationComplete.customValidation', addCustomChecks );
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
	triggerCustomValidation: item => {
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
	prepareDeiterationValidityChecks: checkValidityCb => {

		/**
		 * Disables all invalid items and revalidates them.
		 *
		 * @param {jQuery.event} event The unloader event.
		 * @param {event.target} target The iteration input target.
		 * @param {!jQuery} $toRemove The '.tsfem-form-collapse' headers to be removed.
		 * @return {undefined}
		 */
		const disableAndValidate = ( event, target, $toRemove ) => {

			if ( ! $toRemove )
				return;

			let $items = $toRemove.children( '.tsfem-form-collapse-content' ).find( 'input:invalid, select:invalid, textarea:invalid' );

			if ( ! $items )
				return;

			$items.prop( 'disabled', true );
			$items.on( 'tsfemForm.temp.disableAndValidate.checkValidityCb', checkValidityCb );
			$items.each( ( i, element ) => { jQuery( element ).trigger( 'tsfemForm.temp.disableAndValidate.checkValidityCb' ); } );
			$items.off( 'tsfemForm.temp.disableAndValidate.checkValidityCb' );
		}
		/**
		 * Initializes deiteration check events.
		 */
		const prepareDeiterationChecks = () => {
			jQuery( window )
				.off( 'tsfemForm.deiterationLoad.disableAndValidate' )
				.on( 'tsfemForm.deiterationLoad.disableAndValidate', disableAndValidate );
		}
		prepareDeiterationChecks();
	},

	/**
	 * Sets up special required fields, like a11y checkbox lists.
	 * This is done because we browser support for these fields lack whilst
	 * determining form push requirements.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Trap is no longer tab-focusable.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	setupSpecialRequired: () => {

		let trapClass = 'tsfem-form-checkbox-required';

		{
			var trap = document.createElement( 'input' );
			trap.className = trapClass;
			trap.setAttribute( 'type', 'checkbox' );
			trap.setAttribute( 'required', 'required' );
			trap.setAttribute( 'value', '1' );
			trap.setAttribute( 'tabIndex', '-1' );
		}

		/**
		 * Adds or subtracts error count for $box.
		 *
		 * @param {jQuery.element} $box The a11y required box.
		 * @param {number} _i The addition (or subtraction).
		 */
		const countChecked = ( $box, _i ) => {
			let newCount = ( +$box.data( 'required-check-count' ) || 0 ) + _i;
			$box.data( 'required-check-count', newCount );
			return newCount;
		}
		/**
		 * Adds invalid trap to $box.
		 *
		 * @param {jQuery.element} $box The a11y required box.
		 */
		const addTrap = $box => {
			if ( ! $box.children( `.${trapClass}` ).length ) {
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
		const removeTrap = $box => {
			let $_trap = $box.children( `.${trapClass}` );
			if ( $_trap.length ) {
				/**
				 * Because the old trap is tainted by jQuery, we need to rewrite
				 * it for it to pass validation.
				 * For some reason... Maybe we're just reading it from memory?
				 */
				/*
				jQuery( $_trap ).prop( 'checked', true );
				jQuery( $_trap ).prop( 'required', false );
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
		const testChecked = event => {
			// We can't test disabled. RC harness for showif. See tsfemForm.setupShowIfListener
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
				// There was as trap.
				if ( newVal > oldVal ) {
					removeTrap( $box );
				}
			} else {
				if ( newVal < 1 ) {
					// It's a trap!
					addTrap( $box );
				}
			}
		}
		/**
		 * Finds a11y boxes that might require traps.
		 */
		const find = () => {
			let $fields = jQuery( '.tsfem-form-multi-select-wrap[data-required="1"]' ),
				$box, countVal,
				checkedCount;

			$fields.each( ( index, element ) => {
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
	setupTypeListener: () => {

		/**
		 * Matches setter to value. When found, it returns the value to set type.
		 *
		 * @param {(object|string|number)} setter The value(s) of the setter to look for.
		 * @param {(string|number)} value The value to match.
		 * @return {(false|string|number)} False if not found. Value otherwise.
		 */
		const matchType = ( setter, value ) => {
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
		const setType = event => {
			let value, setter;

			const target = event.target;

			// Prevent additional runs on iteration.
			target.dataset.typeInitTested = 1;

			if ( target.type.toLowerCase() === 'checkbox' ) {
				value = target.checked ? target.value : '0';
			} else {
				value = target.value;
			}

			if ( ! value ) {
				// Value is empty. No need to loop.
				target.dataset.type = '';
				jQuery( target ).trigger( 'tsfemForm.typeIsSet' );
				return;
			}

			setter = tsfemForm.parseElementData( target, 'setTypeToIfValue' );

			// No setters found in data. Which is weird... Bail.
			if ( ! setter )
				return;

			target.dataset.type = matchType( setter, value ) || '';

			jQuery( target ).trigger( 'tsfemForm.typeIsSet' );
		}
		/**
		 * Finds type listeners and attaches change handlers.
		 */
		const find = () => {
			let $fields = jQuery( '[data-is-type-listener="1"]' );

			$fields.each( ( index, element ) => {
				if ( ! element.dataset.typeInitTested ) {
					jQuery( element )
						.off( 'change.tsfemForm.typeListener' )
						.on( 'change.tsfemForm.typeListener', setType )
						.trigger( 'change.tsfemForm.typeListener' );
				}
			} );
		}
		find();
		jQuery( window ).on( 'tsfemForm.iterationComplete', find );
	},

	/**
	 * Sets up showIf listeners based on DOM values.
	 *
	 * NOTE: This method maintains two types of data to check whether the DOM element
	 * is shown.
	 *     1: HTMLElement.dataset.isShown triggers-object
	 *     2: HTMLElement.dataset.disabledShowif count.
	 *
	 * @since 1.3.0
	 * @access private
	 * @see tsfemForm.setupTypeListener() which initiates data for this handler.
	 *
	 * @function
	 * @return {undefined}
	 */
	setupShowIfListener: () => {

		let input = 'input, select, textarea';

		let fadeOps = {
			duration: 150,
			easing: 'linear',
			queue: false,
			start: function () {
				this.style.willChange = 'opacity';
			},
			done: function () {
				// Prevent non-queue style-retain glitch on paint lag.
				let display = this.style.display;

				this.removeAttribute( 'style' );
				this.style.display = display;
			}
		};

		/**
		 * Checks stacked disabled showif listeners through JSON dataset on
		 * a per trigger basis.
		 *
		 * @param {Element} element
		 * @return {boolean} True if shown. False otherwise.
		 */
		const isShown = element => {
			let _isShown = element.dataset.isShown || void 0,
				_isShownO;

			try {
				_isShownO = JSON.parse( _isShown );
			} catch( e ) {}

			if ( _isShownO ) {
				// Find any remainders that disabled it.
				for ( let _i in _isShownO ) {
					if ( -1 === +_isShownO[ _i ] )
						return false;
				}
			}

			return true;
		}
		/**
		 * Maintains stacked disabled showif listeners through JSON dataset on
		 * a per trigger basis.
		 *
		 * @param {Element} element
		 * @param {string} trigger The trigger ID.
		 * @param {number} _i Whether to enable (1) or disable (-1).
		 * @return {boolean} True if value has changed. False otherwise.
		 */
		const setShown = ( element, trigger, _i ) => {

			let _isShown = element.dataset.isShown || void 0,
				_isShownO;

			try {
				_isShownO = JSON.parse( _isShown );
			} catch( e ) {}

			_isShownO ||= {};

			// Value didn't change. Return false.
			if ( _isShownO.hasOwnProperty( trigger ) && +_i === +_isShownO[ trigger ] )
				return false;

			_isShownO[ trigger ] = +_i;

			element.dataset.isShown = JSON.stringify( _isShownO );

			// Value has changed. Return true.
			return true;
		}
		/**
		 * Counts disabled listener amount. When it's zero, it should be valid.
		 * Can't become lower than 0.
		 *
		 * @param {Element} element
		 * @param {number} _i The number to add or subtract for element.
		 */
		const countDisabled = ( element, _i ) => {
			let count = element.dataset.disabledShowif;
			return element.dataset.disabledShowif = count ? +count + _i : +_i;
		}
		/**
		 * Hides element and counts how many times it's hidden, and maintains why
		 * it's hidden.
		 *
		 * @param {Element} element
		 * @param {string} trigger The trigger ID.
		 * @return {jQuery.Deferred}
		 */
		const triggerHide = ( element, trigger ) => {

			if ( ! setShown( element, trigger, -1 ) )
				return;

			let $el = jQuery( element );

			// Test is element is self (1), or a container (2).
			if ( $el.is( input ) ) {
				$el.closest( '.tsfem-form-setting' ).fadeOut( fadeOps );

				element.disabled = true;
				countDisabled( element, 1 );

				tsfemForm.triggerCustomValidation( element );
			} else {
				$el.fadeOut( fadeOps );
				$el.find( input ).each( ( index, _element ) => {
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
		 * @return {jQuery.Deferred}
		 */
		const triggerShow = ( element, trigger ) => {

			if ( ! setShown( element, trigger, 1 ) )
				return;

			let $el = jQuery( element );

			// Test is element is self (1), or a container (2).
			if ( $el.is( input ) ) {
				// Write then show.
				if ( countDisabled( element, -1 ) < 1 ) {
					element.disabled = false;
					tsfemForm.triggerCustomValidation( element );

					$el.closest( '.tsfem-form-setting' ).fadeIn( fadeOps );
				}
			} else {
				// Write then show.
				let _checkMore = $el.hasClass( 'tsfem-form-multi-setting' ),
					_count;

				$el.find( input ).each( ( index, _element ) => {
					// Always count.
					_count = countDisabled( _element, -1 );

					// Skip if not shown.
					if ( _checkMore && ! isShown( _element ) ) {
						// return true; === continue
						return true;
					}

					if ( _count < 1 ) {
						_element.disabled = false;
						tsfemForm.triggerCustomValidation( _element );
						jQuery( _element ).show();
					}
				} );
				$el.fadeIn( fadeOps );
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
		const attachHandler = ( element, target, value ) => {

			let buffer = {};

			// See tsfemForm.setupTypeListener.setType
			jQuery( target )
				.off( 'tsfemForm.typeIsSet', element )
				.on( 'tsfemForm.typeIsSet', element, () => {
					clearTimeout( buffer[ target.id ] );

					// This is a magic debouncer against a race condition that doesn't
					// work under the fadeOps.duration since there are deferred objects inside.
					// The +25ms works on fast computers. We can only hope the user doesn't enact
					// within the submillisecond timeframe during processing (validation, etc.) of the form.
					buffer[ target.id ] = setTimeout( () => {
						if ( target.dataset.type === value ) {
							triggerShow( element, target.id );
						} else {
							triggerHide( element, target.id );
						}
						delete buffer[ target.id ];
					}, fadeOps.duration + 25 ); // debounce
				} );

			// Initial run. Don't promise.
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
		const findTarget = element => {

			let data = tsfemForm.parseElementData( element, 'showif' );

			if ( ! data )
				return void 0;

			let $target,
				val,
				$group = jQuery( element ).closest( '.tsfem-form-collapse-content' );

			if ( ! $group.length ) {
				$group = jQuery( element ).closest( '.tsfem-form-multi-setting' );
			}

			// A single item. For now.
			for ( val in data ) break;

			if ( ! val )
				return void 0;

			if ( $group ) {
				// Group.
				$target = $group.find( `[data-showif-catcher="${val}"]` );
			} else {
				// No group, try anyway.
				$target = jQuery( element.form ).find( `[data-showif-catcher="${val}"]` );
			}

			if ( ! $target.length )
				return void 0;

			return {
				target: $target[0],
				value:  data[ val ]
			};
		}
		/**
		 * Initializes showif functionality for element.
		 * Attaches event handlers when the element has attached fields to listen for.
		 *
		 * @param {Element} element The element that might be hidden or shown.
		 */
		const showIfInit = element => {
			element.dataset.showIfIsInit = '1';
			let data = findTarget( element );
			if ( data )
				attachHandler( element, data.target, data.value );
		}
		/**
		 * Initializes showif functionality by getting fields.
		 * It runs once per element, preventing duplication by adding data.
		 */
		const init = () => {
			let $fields = jQuery( '[data-is-showif-listener="1"]' );

			let fadeOpsDuration = fadeOps.duration;
			fadeOps.duration = 0;

			$fields.each( ( index, element ) => {
				element.dataset.showIfIsInit || showIfInit( element );
			} );

			fadeOps.duration = fadeOpsDuration;
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
	adjustSubmit: () => {

		let $forms = jQuery( 'form.tsfem-form' );

		$forms.each( ( i, element ) => {
			jQuery( document.querySelectorAll( `[type=submit][form="${element.id}"]` ) ).attr( 'onclick', 'tsfemForm.saveInput( event )' );
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
	doValidityRoutine: ( form, notification ) => {

		notification ||= tsfemFormL10n.i18n['collapseValidate'];

		if ( ! form.checkValidity() ) {
			let firstInvalid = form.querySelector( 'input:invalid, select:invalid, textarea:invalid' ),
				enclosed     = false;

			/**
			 * Tries validation report on the first invalid field found.
			 * When errors are plausibly enclosed in hidden fields, it will
			 * propagate itself through tryAdvancedReport.
			 * On fail, it will log the error.
			 *
			 * @return {undefined}
			 */
			const tryReport = () => {
				// If the header is closed, we can't report validity.
				if ( enclosed ) {
					tryAdvancedReport();
					return;
				} else {
					try {
						firstInvalid.reportValidity();
						return;
					} catch ( err ) {
						tsf.l10n.states.debug && console.log( err );
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
			const tryAdvancedReport = () => {
				let $headers = jQuery( firstInvalid ).parents( '.tsfem-form-collapse' ),
					depth    = -1;

				// Reverse the stack
				$headers = jQuery( $headers.get().reverse() );

				jQuery( $headers ).each( ( i, element ) => {
					let isChecked = jQuery( element ).children( '.tsfem-form-collapse-checkbox' ).is( ':checked' );
					if ( isChecked ) {
						depth = i;
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
				const scrollTo = $to => {
					// Let the tooltip be painted first.
					clearTimeout( scrollToTimeout );
					scrollToTimeout = setTimeout( () => {
						let _scrollTop    = document.documentElement.scrollTop,
							_clientHeight = document.documentElement.clientHeight,
							_to           = $to.offset().top,
							/* Most eyes focus on 1/3th top of screen */
							_offSet       = jQuery( '#tsfem-sticky-top' ).height() + ( _clientHeight * 1/3 ),
							_doScroll     = false;

						if ( _to - _offSet < ( _scrollTop ) ) {
							// Scroll up.
							_doScroll = true;
						} else if ( _to + _offSet > ( _clientHeight + _scrollTop ) ) {
							// Scroll down.
							_doScroll = true;
						}

						if ( _doScroll ) {
							jQuery( document.documentElement ).animate( { scrollTop: _to - _offSet }, 500 );
						}
					}, 50 );
				}
				const removeToolTip = event => {
					let $el = jQuery( event.target );

					tsfTT.removeTooltip( $el.siblings( '.tsfem-form-collapse-header' ) );

					$el.off( 'change.tsfemForm.removeToolTip' );
					tryAdvancedReport();
				}

				// Create tooltip, scroll to it, add tooltip listeners.
				tsfTT.doTooltip( void 0, $label, notification );
				scrollTo( $label );
				$checkbox.off( 'change.tsfemForm.removeToolTip' ).on( 'change.tsfemForm.removeToolTip', removeToolTip );
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
	saveInput: event => {
		// TODO if we want to save all input, we need a new method, because 'let form, $loader' only handles one form and loader.
		// Or, we could walk over the event, and grab multiple formIds

		// For sanity, prevent regular form submission.
		tsfemForm.preventSubmit( event );

		const target = event.target;

		let formId = target.getAttribute( 'form' ),
			form,
			button;

		if ( formId ) {
			form = document.getElementById( formId );
			if ( ! form )
				return tsfemForm.preventSubmit( event );

			if ( ! tsfemForm.doValidityRoutine( form, tsfemFormL10n.i18n['collapseValidate'] ) )
				return;

			// TODO get all buttons of name/form... so we can disable them all.
			button = target;
		} else {
			form = target;
			// TODO get all buttons of name/form... so we can disable them all.
			button = document.querySelector( `[type=submit][form="${form.id}"]` );
		}

		//let buttonClassName = 'tsfem-button-disabled tsfem-button-loading'; // ES6
		let $loader    = jQuery( form ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status     = 0,
			loaderText = '';

		// Disable the submit button.
		tsfemForm.disableButton( button );

		// Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		// Set ajax loader.
		tsfem.setAjaxLoader( $loader );

		// Do ajax...
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'tsfemForm_save',
				nonce:  tsfemForm.nonce,
				data:   jQuery( form ).serialize(),
			},
			processData: true,
			timeout: 14000,
			async: true,
		} ).done( response => {

			response = tsf.convertJSONResponse( response );

			tsf.l10n.states.debug && console.log( response );

			const data = response?.data,
				  type = response?.type;

			if ( ! data || ! type ) {
				// Erroneous output.
				loaderText = tsfem.i18n['InvalidResponse'];
			} else {
				const rCode = data?.results.code;

				if ( rCode ) {
					if ( data?.data?.failed.length ) {
						// Here, we should decipher which extension failed..
						// TODO see message at top of function.
						status = 0;
						loaderText = tsfem.i18n['UnknownError'];
					} else {
						status = 1;
					}
					tsfem_ui.setTopNotice( rCode );
				} else {
					// Erroneous output.
					loaderText = tsfem.i18n['UnknownError'];
				}
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			// Set Ajax response for wrapper.
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

			// Try to set top notices, regardless. First notifies that there's an error saving.
			tsfem_ui.setTopNotice( 1071101 );
			errorThrown && tsfem_ui.setTopNotice( -1, `Thrown error: ${errorThrown}` );
		} ).always( () => {
			tsfem.updatedResponse( $loader, status, loaderText );
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
	preventSubmit: event => {
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
	enableButton: target => {
		// setTimeout prevents paint lag.
		setTimeout( () => {
			let $target = jQuery( target );
			$target.removeClass( 'tsfem-button-disabled tsfem-button-loading' );
			$target.prop( 'disabled', false );
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
	disableButton: ( target, loading ) => {

		loading = void 0 === loading ? true : loading;

		let $target = jQuery( target );

		if ( loading ) {
			$target.addClass( 'tsfem-button-disabled tsfem-button-loading' );
		} else {
			$target.addClass( 'tsfem-button-disabled' );
		}
		$target.prop( 'disabled', true );
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
	enableSubmit: form => {
		if ( form?.id )
			tsfemForm.enableButton( `[form="${form.id}"]` );
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
	disableSubmit: ( form, loading ) => {
		if ( form?.id )
			tsfemForm.enableButton( `[form="${form.id}"]`, loading );
	},

	/**
	 * Initializes actions on ready-state.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @function
	 */
	doReady: () => {

		// Prepare AJAX iterations.
		tsfemForm.setupIterations();

		// Prepare Geo API.
		tsfemForm.setupGeo();

		// Prepare Media.
		'tsfMedia' in window && tsfMedia.resetImageEditorActions();

		// Prepare required fields for special fields.
		tsfemForm.setupSpecialRequired();

		// Prepare type setters.
		tsfemForm.setupTypeListener();

		// Prepare show-if listeners.
		tsfemForm.setupShowIfListener();

		// Prepares collapse items. Must run after show-if listeners because we validate the forms here.
		tsfemForm.prepareCollapseItems();

		// Turn form submit into an AJAX pusher.
		tsfemForm.adjustSubmit();
	}
};
jQuery( tsfemForm.doReady );
