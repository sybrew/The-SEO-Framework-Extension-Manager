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

	focusRegistry: {},
	activeFocusAreas: {},

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

	/**
	 * Updates focus registry selectors.
	 *
	 * When adding objects dynamically, it's best to always use the append type.
	 *
	 * @since 1.0.0
	 * @see tsfem_e_focus_inpost.updateActiveFocusAreas() To be called after the
	 *      registry has been updated.
	 * @access private
	 *
	 * @function
	 * @param {!Object} elements : {
	 *   'context' => [ selector => string Type 'append|dominate' ]
	 * }
	 * @param {bool|undefined} set Whether to add or remove the elements.
	 */
	updateFocusRegistry: function( elements, set ) {

		if ( ! elements || elements !== Object( elements ) ) return;

		let registry = tsfem_e_focus_inpost.focusRegistry;
		set = !! set;

		let selectors, type;

		//= Filter elements.
		for ( let context in elements ) {
			selectors = elements[ context ];
			if ( selectors !== Object( selectors ) )
				continue;

			registry[ context ]
				|| ( registry[ context ] = {} );

			for ( let selector in selectors ) {
				type = selectors[ selector ];

				//= Test if entries exist.
				registry[ context ][ type ]
					|| ( registry[ context ][ type ] = [] );

				if ( set ) {
					if ( registry[ context ][ type ].hasOwnProperty( selector ) )
						continue;

					registry[ context ][ type ].push( selector );
				} else {
					//= Unset
					registry[ context ][ type ] =
						registry[ context ][ type ].filter( s => s !== selector );
				}
			}
		}
		tsfem_e_focus_inpost.focusRegistry = registry;
	},

	/**
	 * Parses focus elements and their existence and registers them as available areas.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {!Object} elements : {
	 *   'context' => [ selector => string type 'append|dominate' ]
	 * }
	 */
	updateActiveFocusAreas: function() {

		let elements = tsfem_e_focus_inpost.focusRegistry;

		if ( ! elements || elements !== Object( elements ) ) return;

		let areas = {},
			types = {},
			hasDominant = false,
			lastDominant = '',
			keys = [];

		//= Filter elements. The input is expected.
		for ( let context in elements ) {
			types = elements[ context ];
			hasDominant = false;
			lastDominant = '';
			keys = [];
			if ( 'dominate' in types ) {
				types.dominate.forEach( selector => {
					//= Skip if the selector doesn't exist.
					if ( ! document.querySelector( selector ) )
						return;
					hasDominant = true;
					lastDominant = selector;
				} );
			}
			if ( ! hasDominant && 'append' in types ) {
				types.append.forEach( selector => {
					//= Skip if the selector doesn't exist.
					if ( ! document.querySelector( selector ) )
						return;
					keys.push( selector );
				} );
			}
			if ( hasDominant ) {
				areas[ context ] = [ lastDominant ];
			} else {
				keys.length && (
					areas[ context ] = keys
				);
			}
		}

		tsfem_e_focus_inpost.activeFocusAreas = areas;
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
		let el = document.getElementById( 'tsfem-e-focus-analysis-wrap' )
			.querySelector( '.tsf-flex-setting-input' );

		if ( el instanceof Element )
			el.innerHTML = wp.template( 'tsfem-e-focus-nofocus' )();
	},

	onReady: function( event ) {

		if ( tsfem_e_focusInpostL10n.hasOwnProperty( 'focusElements' ) ) {
			tsfem_e_focus_inpost.updateFocusRegistry( tsfem_e_focusInpostL10n.focusElements, true );
			tsfem_e_focus_inpost.updateActiveFocusAreas();
		}

		//= There's nothing to focus on.
		if ( 0 === Object.keys( tsfem_e_focus_inpost.activeFocusAreas ).length ) {
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

		//= Reenable focus elements.
		$( '.tsfem-e-focus-enable-if-js' ).removeProp( 'disabled' );
		//= Disable nojs placeholders.
		$( '.tsfem-e-focus-disable-if-js' ).prop( 'disabled', 'disabled' );

		// Initialize image uploader button cache.
		$( document.body ).ready( tsfem_e_focus_inpost.onReady );
	}
};
jQuery( tsfem_e_focus_inpost.load );
