/**
 * This file holds Focus' code for interpreting keywords and their subjects.
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
 * Holds tsfem_e_focus_inpost values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window.tsfem_e_focus_inpost = {

	focusAreas: {},

	/**
	 * Gets string until last numeric ID index.
	 * In this application, no multidimensional IDs should be in place.
	 *
	 * in:  `tsfem-pm[focus][kw][42][something]`
	 * out: `tsfem-pm[focus][kw][42]`
	 *
	 */
	getSubIdPrefix: function( id ) {
		return /.*\[[0-9]+\]/.exec( id );
	},

	doAnalysis: function( what ) {
		if ( ! what ) return;

	},

	setFocusAreas: function() {

		let elements;

		if ( 'object' === typeof tsfem_e_focusInpostL10n
		  && tsfem_e_focusInpostL10n.hasOwnProperty( 'focusElements' )
		) elements = tsfem_e_focusInpostL10n.focusElements;

		if ( ! elements ) return;

		let areas = {},
			hasDominant = false,
			lastDominant = '',
			keys = [],
			el;

		//= Filter elements.
		for ( let currentElement in elements ) {
			el = elements[ currentElement ];
			if ( el && 'object' === typeof el ) {
				hasDominant = false;
				lastDominant = '';
				keys = [];
				for ( let selector in el ) {
					if ( 'dominate' === el[ selector ] ) {
						hasDominant = true;
						lastDominant = selector;
					} else {
						//? No need to push if there's a dominant.
						hasDominant || keys.push( selector );
					}
				}
				if ( hasDominant ) {
					areas[ currentElement ] = [ lastDominant ];
				} else {
					areas[ currentElement ] = keys;
				}
			}
		}

		tsfem_e_focus_inpost.focusAreas = areas;
	},

	doCheckup: function() {
	},

	prepareSubjectSetter: function( event ) {

		let getIdPrefix = getSubIdPrefix( event.target.id );

	},

	prepareScores: function( event ) {

		let getIdPrefix = getSubIdPrefix( event.target.id );

	},

	doKeywordEntry: function( event ) {

		let target = event.target,
			val = target.value || '',
			prev = target.dataSet.prev || '';

		//= No change happened.
		if ( val === prev ) return;
		target.dataSet.prev = val;

		//! Weak check, but sufficient.
		if ( ! val ) return;

		if ( tsfem_e_focusInpostL10n.isPremium ) {
			tsfem_e_focus_inpost.prepareSubjectSetter( event );
		}

		tsfem_e_focus_inpost.prepareSubjectSetter( event );
		tsfem_e_focus_inpost.prepareScores( event );
	},

	resetCollapserListeners: function() {

		//= Make the whole collapse bar a double-clickable expander/retractor.
		jQuery( '.tsfem-e-focus-header' )
			.off( 'dblclick.tsfem-e-focus' )
			.on( 'dblclick.tsfem-e-focus', ( e ) => {
				let a = e.target.parentNode.querySelector( 'input' );
				if ( a instanceof Element ) a.checked = ! a.checked;
			} );

		let keywordBuffer = 0, keywordTimeout = 1000;
		//= Set keyword entry listener
		jQuery( '.tsfem-e-focus-keyword-entry' )
			.off( 'input.tsfem-e-focus' )
			.on( 'input.tsfem-e-focus', ( e ) => {
				clearTimeout( keywordBuffer );
				keywordBuffer = setTimeout( () => {
					tsfem_e_focus_inpost.doKeywordEntry( e );
				}, keywordTimeout );
			} );
	},

	disableFocus: function() {

	},

	onReady: function( event ) {

		//= There's nothing to focus on.
		if ( 0 === Object.keys( tsfem_e_focus_inpost.focusAreas ).length ) {
			tsfem_e_focus_inpost.disableFocus();
			return;
		}

		tsfem_e_focus_inpost.resetCollapserListeners();
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery} $ jQuery
	 * @return {undefined}
	 */
	load: function( $ ) {

		tsfem_e_focus_inpost.setFocusAreas();

		//= Reenable focus elements.
		$( '.tsfem-e-focus-enable-if-js' ).removeProp( 'disabled' );
		//= Disable nojs placeholders.
		$( '.tsfem-e-focus-disable-if-js' ).prop( 'disabled', 'disabled' );

		// Initialize image uploader button cache.
		$( document.body ).ready( tsfem_e_focus_inpost.onReady );
	}
};
jQuery( tsfem_e_focus_inpost.load );
