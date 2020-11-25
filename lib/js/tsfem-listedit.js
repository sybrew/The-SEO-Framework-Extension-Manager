/**
 * This file holds ListEdit core code for extensions.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfem_listedit values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 2.5.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfem_listedit = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 2.5.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfem_listeditL10n && tsfem_listeditL10n;

	/**
	 * Sets inline post values for quick-edit.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @function
	 * @param {string} id
	 */
	const _setInlinePostValues = id => {

		let dataElement = document.getElementById( `tsfLeData[${id}]` ),
			data        = void 0;

		try {
			data = JSON.parse( dataElement.dataset.le ) || void 0;
		} catch( e ) {}

		data = data && data.tsfem || void 0;

		if ( ! data ) return;

		let element,
			curData;

		for ( let pmIndex in data ) {
			for ( let option in data[ pmIndex ] ) {
				element = document.getElementById( 'tsfem-pm-quick[%1$s][%2$s]'.replace( '%1$s', pmIndex ).replace( '%2$s', option ) );
				if ( ! element ) continue;

				curData = data[ pmIndex ][ option ];

				if ( curData.isSelect ) {
					tsf.selectByValue( element, curData.value );

					// Do `sprintf( 'Default (%s)', x.default )`.
					let _default = element.querySelector( '[value="0"]' );
					if ( _default )
						_default.innerHTML = _default.innerHTML.replace( '%s', tsf.decodeEntities( curData.default ) );
				} else {
					element.value = tsf.decodeEntities( curData.value );
				}
			}
		}
	}

	/**
	 * Sets inline term values for quick-edit.
	 * Copy of _setInlinePostValues(), for now.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @function
	 * @param {string} id
	 */
	const _setInlineTermValues = id => _setInlinePostValues( id );

	/**
	 * Hijacks the quick and bulk-edit listeners.
	 *
	 * NOTE: The bulk-editor doesn't need adjusting, yet.
	 *       Moreover, the bulk-edit doesn't have a "save" callback, because it's
	 *       not using AJAX to save data.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @function
	 */
	const _hijackListeners = () => {

		let _oldInlineEditCallback;

		if ( window.inlineEditPost ) {
			_oldInlineEditCallback = 'edit' in window.inlineEditPost && window.inlineEditPost.edit;

			if ( _oldInlineEditCallback ) {
				window.inlineEditPost.edit = function( id ) {
					let ret = _oldInlineEditCallback.apply( this, arguments );

					if ( typeof( id ) === 'object' )
						id = window.inlineEditPost.getId( id );

					if ( ! id ) return ret;

					_setInlinePostValues( id );
					return ret;
				}
			}
		}

		if ( window.inlineEditTax ) {
			_oldInlineEditCallback = 'edit' in window.inlineEditTax && window.inlineEditTax.edit;

			if ( _oldInlineEditCallback ) {
				window.inlineEditTax.edit = function( id ) {
					let ret = _oldInlineEditCallback.apply( this, arguments );

					if ( typeof( id ) === 'object' )
						id = window.inlineEditTax.getId( id );

					if ( ! id ) return ret;

					_setInlineTermValues( id );
					return ret;
				}
			}
		}
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 2.5.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _hijackListeners );
		}
	}, {}, {
		l10n
	} );
}( jQuery );
window.tsfem_listedit.load();
