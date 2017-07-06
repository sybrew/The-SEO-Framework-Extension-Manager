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
			fIt;

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
				let $item = jQuery( e.target ),
					$label = $item.closest( '.tsfem-form-setting' ).find( '.tsfem-form-setting-label-inner-wrap' ),
					$curBar = $label.find( 'span.tsfem-form-iterator-timer' );

				//* Disable other iterators.
				$items.not( this ).prop( 'disabled', true );

				//* (re)set visual countdown timer.
				clearInterval( vBuffer );
				clearTimeout( itBuffer );
				vBarStop();
				vBuffer = setInterval( vBarGo, vBarTimeout );

				if ( ! $curBar.length ) {
					$label.append( vBar );
					$curBar = $label.find( 'span.tsfem-form-iterator-timer' );
				}

				itBuffer = setTimeout( function() {

					//* Disable own iterator.
					$item.prop( 'disabled', true );

					//* Output new items...
					tsfemForm.loadIterations( e.target );

					//* Enable inputs again...
					setTimeout( function() {
						$items.prop( 'disabled', false );
						$curBar.remove();
						vBarReset();
					}, 100 );
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

		let outputWrapper = document.getElementById( target.id + '-wrapper' ),
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
					'caller' : target.id,
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

		jQ( document.body ).ready( tsfemForm.setupIterations );

		//* Set function to (re)load this:
		// jQ( '.tsfem-form-collapse + input' ).off( 'change', func );
		// jQ( '.tsfem-form-collapse + input' ).on( 'change', func );

	}
};
jQuery( tsfemForm.ready );
