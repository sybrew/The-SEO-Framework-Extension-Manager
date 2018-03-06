/**
 * This file holds Inpost core code for extensions.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/the-seo-framework-extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Signifies states.
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
	 * @return {undefined}
	 */
	const setFlexNotice = ( notice ) => {
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
						notice = template( { 'code' : code, 'msg' : text } );

					appendFlexNotice( notice, wrapper );
					noticeBuffer = false;
				}
			}
		};
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

		setDismissNoticeListener();
	}

	/**
	 * Sets up dismissible notice listener. Uses class .tsfem-dismiss.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const setDismissNoticeListener = () => {

		let $dismiss = $( '.tsfem-flex-settings-notification-area .tsfem-dismiss' );

		const dismissNotice = ( event ) => {
			$( event.target ).closest( '.tsfem-notice' ).slideUp( 200, function() {
				this.remove();
			} );
		};

		$dismiss.off( 'click', dismissNotice );
		$dismiss.on( 'click', dismissNotice );
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
			case 'abort' : // client error, no code.
			case 'timeout' : // 408
				_error = i18n['TimeoutError'];
				break;

			case 'Bad Request' : // 400
				_error = i18n['BadRequest'];
				break;

			case 'Internal Server Error' : // 500
				_error = i18n['FatalError'];
				break;

			case 'parsererror' : // PHP error, no code.
				_error = i18n['ParseError'];
				break;

			default :
				// @TODO use ajaxOptions.status? i.e. 400, 401, 402, 503.
				_error = i18n['UnknownError'];
				break;
		}

		return _error;
	}

	/**
	 * Internet Explorer's Object.assign() alternative.
	 */
	return $.extend( true, {
		/**
		 * Initialises all aspects of the scripts.
		 *
		 * @since 1.5.0
		 * @access private
		 *
		 * @function
		 * @return {undefined}
		 */
		load: function() { }
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
		convertJSONResponse,
		setFlexNotice,
		getAjaxError
	} );
}( jQuery );
//= Run before jQuery.ready() === DOMContentLoaded
jQuery( window.tsfem_inpost.load );
