/**
 * This file holds The SEO Framework Extension Manager plugin's JS code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfem values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 * @since 2.5.0 converted to a function.
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfem = function ( $ ) {

	/**
	 * @since 1.0.0
	 * @since 2.5.0 Now public
	 * @access public
	 * @type {string|null} nonce Ajax nonce
	 */
	const nonce = tsfemL10n.nonce;

	/**
	 * @since 2.4.0
	 * @since 2.5.0 Now public
	 * @access public
	 * @type {string|null} nonce Insecure Ajax nonce
	 */
	const insecureNonce = tsfemL10n.insecureNonce;

	/**
	 * @since 1.0.0
	 * @access private
	 * @type {object|null} i18n Localized strings
	 */
	const i18n = tsfemL10n.i18n;

	/**
	 * Transforms elements and queries to an array from nodelists.
	 *
	 * @since 2.5.0
	 * @access public
	 *
	 * @function
	 * @param {(Element|string|Array<number,string>)} elements  The elements to get.
	 * @param {string}                                sansClass The classlist to filter from the their classList.
	 * @return {(Array<number,Element>)}
	 */
	const getNodeArray = ( elements, sansClass ) => {
		let ret = ( elements instanceof Element || elements instanceof Document )
				? [ elements ]
				: [ ...document.querySelectorAll( Array.isArray( elements ) ? elements.join( ', ' ) : elements ) ];

		if ( sansClass )
			ret = ret.filter( el => ! el.classList.contains( sansClass ) )

		return ret;
	}

	/**
	 * Visualizes AJAX loading time through target class change.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.element|Element|string)} arg1
	 * @return {undefined}
	 */
	const setAjaxLoader = target => {
		$( target ).toggleClass( 'tsfem-loading' );
	}

	/**
	 * Adjusts class loaders on Ajax response.
	 *
	 * @since 1.0.0
	 * @since 2.5.0 Removed the fourth parameter.
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.element|Element|string)} target
	 * @param {number} success
	 * @param {string} notice
	 * @return {undefined}
	 */
	const unsetAjaxLoader = ( target, success, notice ) => {

		let newclass, fade;

		switch ( success ) {
			case 2:
				newclass = 'tsfem-unknown';
				fade     = 7500;
				break;
			case 1:
				newclass = 'tsfem-success',
				fade     = 2500;
				break;
			default:
				newclass = 'tsfem-error';
				fade     = 10000;
		}

		$( target ).removeClass( 'tsfem-loading' ).addClass( newclass ).text(
			notice ? $( '<span/>' ).html( notice ).text() : ''
		).fadeOut(
			notice ? fade * 2 : fade
		);
	}

	/**
	 * Cleans and resets Ajax wrapper class and contents to default.
	 * Also stops any animation and resets fadeout to beginning.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.element|Element|string)} target
	 * @return {undefined}
	 */
	const resetAjaxLoader = target => {
		// Reset CSS, with IE compat.
		$( target ).stop( false, true ).empty().prop( 'class', 'tsfem-ajax' ).show();
	}

	/**
	 * Visualizes the AJAX response to the user.
	 *
	 * @since 1.0.0
	 * @since 2.5.0. No longer accepts the fourth HTML parameter.
	 * @access public
	 *
	 * @function
	 * @param {string} target
	 * @param {number} success 0 = error, 1 = success, 2 = unknown but success.
	 * @param {string} notice The updated notice.
	 * @return {undefined}
	 */
	const updatedResponse = ( target, success, notice ) => {
		switch ( success ) {
			case 0:
			case 1:
			case 2:
				unsetAjaxLoader( target, success, notice );
				break;

			default:
				resetAjaxLoader( target );
		}
	}

	/**
	 * Returns bound AJAX reponse error with the help from i18n.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.xhr|Object)} jqXHR
	 * @param {String} textStatus
	 * @param {String} errorThrown
	 * @return {String}
	 */
	const getAjaxError = ( jqXHR, textStatus, errorThrown ) => {

		tsf.l10n.states.debug && console.log( { jqXHR, textStatus, errorThrown } );

		let _error = '';

		switch ( errorThrown ) {
			case 'abort': // client error, no code.
			case 'timeout': // 408
				_error = tsfem.i18n['TimeoutError'];
				break;

			case 'Bad Request': // 400
				_error = tsfem.i18n['BadRequest'];
				break;

			case 'Internal Server Error': // 500
				_error = tsfem.i18n['FatalError'];
				break;

			case 'parsererror': // PHP error, no code.
				_error = tsfem.i18n['ParseError'];
				break;

			default:
				// @TODO use ajaxOptions.status? i.e. 400, 401, 402, 503.
				_error = tsfem.i18n['UnknownError'];
		}

		return _error;
	}

	/**
	 * Converts multidimensional arrays to single array with key wrappers.
	 * All first array keys become the new key. The final value becomes its value.
	 *
	 * Great for creating form array keys.
	 * matosa: "Multidimensional Array TO Single Array"
	 *
	 * The latest value must be scalar.
	 *
	 * Example: a = [ 1 => [ 2 => [ 3 => [ 'value' ] ] ] ];
	 * Becomes: '1[2][3]' => 'value';
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param {(String|Object)} value The array or string to loop.
	 * @return {(Object|Boolean)} The iterated array to string. False if input isn't array.
	 */
	const matosa = value => {

		var last   = null,
			output = '';

		(function _matosa( _value, _i ) {
			_i++;
			if ( typeof _value === 'object' ) {
				let _index, _item;

				for ( _index in _value )
					_item = _value[ _index ];

				last = _item;

				if ( 1 === _i ) {
					output += _index + _matosa( _item, _i );
				} else {
					output += `[${_index}]${_matosa( _item, _i )}`;
				}
			} else if ( 1 === _i ) {
				last = null;
				return output = false;
			}

			return output;
		})( value, 0 );

		if ( false === output )
			return false;

		let retval = {};
		retval[ output ] = last;

		return retval;
	}

	/**
	 * Runs document-on-ready actions.
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	const _doReady = () => { }

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
			// document.body.addEventListener( 'tsf-onload' );
			document.body.addEventListener( 'tsf-ready', _doReady );
			// document.body.addEventListener( 'tsf-interactive' );
		}
	}, {
		/**
		 * Constant variables.
		 * Don't overwrite these.
		 *
		 * @since 2.5.0
		 * @access public
		 */
		nonce,
		insecureNonce,
		i18n,
	}, {
		/**
		 * Constant functions.
		 * Don't overwrite these.
		 *
		 * @since 2.5.0
		 * @access public
		 */
		getNodeArray,
		setAjaxLoader,
		unsetAjaxLoader,
		resetAjaxLoader,
		updatedResponse,
		getAjaxError,
		matosa,
	} );
}( jQuery );
window.tsfem.load();
