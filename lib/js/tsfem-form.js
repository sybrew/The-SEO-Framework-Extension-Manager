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

	nonce : tsfemFormL10n.nonce,

	callee : tsfemFormL10n.callee,

	/**
	 * Sets up iteration callbacks and loops through iteration events.
	 *
	 * @since 1.3.0
	 * @access private
	 * @todo disable form submission when parsing...
	 *
	 * @function
	 * @return {undefined}
	 */
	setupIterations: function() {

		var $items = jQuery( '.tsfem-form-iterator-selector-wrap input' );
		tsfemForm.prepareItItems( $items );
		tsfemForm.prepareCollapseItems();

		let itBuffer = 0, itTimeout = 1000,
			vBar, vBarS, vBarTimeout, vBarWidth = 0, vBuffer = 0,
			fIt,
			$item, $label, $curBar;

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

		window.addEventListener( 'tsfemForm_iterate_load', function() {
			$curBar && $curBar.remove();
			$item = $label = $curBar = void 0;
			vBarReset();
		} );

		//= Event resetter.
		window.addEventListener( 'tsfemForm_iterate_complete', function() {
			$items.off( 'input', fIt );
			$items = jQuery( '.tsfem-form-iterator-selector-wrap input' );
			tsfemForm.prepareItItems( $items );
			tsfemForm.prepareCollapseItems();
			$items.on( 'input', fIt );
			tsfem.initDescHover();
		} );

		if ( $items.length ) {
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

					let proceed = true;

					if ( e.target.value > 40 ) {
						if ( e.target.value > 200 ) {
							// TODO i18n and beautify.
							proceed = confirm( "You're about to load a huge number of elements. This might crash your browser. Are you sure you want to proceed?" );
						} else {
							// TODO i18n and beautify.
							proceed = confirm( "You're about to load a large number of elements. This might degredate browser performance. Are you sure you want to proceed?" );
						}
					}

					//* Output new items...
					if ( proceed ) {
						tsfemForm.loadIterations( e.target );
					} else {
						e.target.value = e.target.tsfemFormPrevValue;
					}

					//* Enable inputs again...
					setTimeout( function() {
						$items.prop( 'disabled', false );
					}, 500 );
				}, itTimeout );
			} );
			$items.on( 'input', fIt );
		}
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
			return false;

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
				let _error = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

				// TODO handle error in dismissible notification?
			//	$button.removeClass( disabled );
			//	$button.prop( 'disabled', false );
			//	tsfem.updatedResponse( loader, 0, _error, 0 );
			},
			complete: function() {
				outputWrapper.removeChild( loader );
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
			el.tsfemFormHighestValue = el.tsfemFormHighestValue && el.tsfemFormHighestValue > el.value && el.tsfemFormHighestValue || el.value;
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

		$input.off( 'change', prepareTitleChange );
		$input.on( 'change', prepareTitleChange );
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

		let buttonClassName = 'tsfem-button-disabled tsfem-button-loading',
			$loader = jQuery( form ).closest( '.tsfem-pane-wrap' ).find( '.tsfem-pane-header .tsfem-ajax' ),
			status = 0;

		button.classList.add( ...buttonClassName.split( ' ' ) );
		button.disabled = true;

		//* Reset ajax loader
		tsfem.resetAjaxLoader( $loader );

		//* Set ajax loader.
		tsfem.setAjaxLoader( $loader );

		// Do ajax...
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'html', // TODO convert to JSON.
			data: {
				'action' : 'tsfemForm_save',
				'nonce' : tsfemForm.nonce,
				'data' : jQuery( form ).serialize(),
			},
			//processData: false,
			timeout: 7000,
			async: true,
			success: function( response ) {

				console.log( response );

				status = 1;

				tsfem.setTopNotice( 10010 ); // TODO get real status.
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				let _error = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

				// TODO handle error in dismissible notification?
			//	$button.removeClass( disabled );
			//	$button.prop( 'disabled', false );
			//	tsfem.updatedResponse( loader, 0, _error, 0 );
			},
			complete: function() {
				tsfem.updatedResponse( $loader, status, '', 0 );

				button.classList.remove( ...buttonClassName.split( ' ' ) );
				button.disabled = false;
			},
		} );

		return false;
	},

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

		//* Turn form submit into an AJAX pusher.
		jQ( 'form.tsfem-form' ).on( 'submit', tsfemForm.saveInput );

		//* Prepare AJAX iterations.
		jQ( document.body ).ready( tsfemForm.setupIterations );

		//* Set function to (re)load this:
		// jQ( '.tsfem-form-collapse + input' ).off( 'change', func );
		// jQ( '.tsfem-form-collapse + input' ).on( 'change', func );

	}
};
jQuery( tsfemForm.ready );
