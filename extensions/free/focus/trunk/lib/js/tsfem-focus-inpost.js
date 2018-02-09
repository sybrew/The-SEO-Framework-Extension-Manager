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
		return /.*\[[0-9]+\]/.exec( id )[0];
	},

	getSubElementById: function( prefix, type ) {
		return document.getElementById( prefix + '[' + type + ']' );
	},

	sortMap: function( o ) {
		//? Objects are automatically sorted in Chrome and IE. Sort again anyway.
		Object.keys( o ).sort( ( a, b ) => {
			return Object.keys( a )[0] - Object.keys( b )[0];
		} );

		return o;
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

				registry[ context ][ type ]
					|| ( registry[ context ][ type ] = [] );

				if ( set ) {
					//= Test if entries exist.
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
				if ( keys.length ) areas[ context ] = keys;
			}
		}

		tsfem_e_focus_inpost.activeFocusAreas = areas;
	},

	doCheckAll: function( idPrefix ) {
	},

	// TODO:
	// 1. Add onchange listeners.
	doCheck: function ( rater, keyword, synonyms ) {

		let $rater = jQuery( rater ),
			data = $rater.data( 'scores' ),
			checkElements = tsfem_e_focus_inpost.activeFocusAreas[ data.assessment.content ],
			countKeyword = 0,
			countSubject = 0,
			charCountKeyword = 0,
			charCountSubject = 0,
			charCount = 0,
			content,
			regex = data.assessment.regex;

		//= Convert regex to object if it isn't already.
		if ( regex !== Object( regex ) ) {
			regex = [ regex ];
		}

		const countChars = ( contents ) => {
			// Strip all tags first.
			contents = contents.match( /[^>]+(?=<|$|^)/gm );
			return contents && contents.join( '' ).length || 0;
		};
		const countKeywords = ( kw, contents ) => {
			let n = regex.length,
				p;

			for ( let i = 0; i < n; ) {
				p = /\/(.*)\/(.*)/.exec( regex[ i ] );

				contents = contents.match( RegExp(
					p[1].replace(
						/\{\{kw\}\}/g,
						kw.replace( /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&' )
					),
					p[2]
				) );

				if ( ! contents )
					break;

				if ( ++i < n ) {
					//= Join content if this is a recursive regexp.
					contents = contents.join( ' ' );
				}
			}
			return contents && contents.length || 0;
		};

		//! TODO cache values found per selector. In data?
		checkElements.forEach( selector => {
			//= Wrap content in spaces to simulate word boundaries.
			let $selector = jQuery( selector );

			content = '';

			for ( let i in data.assessment.eval ) {
				switch ( data.assessment.eval[ i ] ) {
					case 'input' :
						content = $selector.val();
						break;

					case 'placeholder' :
						content = $selector.attr( 'placeholder' );
						break;

					case 'innerHTML' :
						content = $selector.text();
						break;
				}
				if ( content.length )
					break;
			}

			if ( content.length ) {
				switch ( data.scoring.type ) {
					case 'n' :
						countKeyword += countKeywords( keyword, content );
						if ( synonyms ) {
							synonyms.forEach( synonym => countSubject += countKeywords( synonym, content ) );
						}
						break;

					case 'p' :
						charCount += countChars( content );
						countKeyword += countKeywords( keyword, content );
						charCountKeyword += keyword.length * countKeyword;
						if ( synonyms ) {
							synonyms.forEach( synonym => {
								let _count = countKeywords( synonym, content );
								countSubject += _count;
								charCountKeyword += synonym.length * _count;
							} );
						}
						break;
				}
			}
		} );

		let scoring = data.scoring,
			maxScore = data.maxScore,
			density = 0,
			realScore = 0,
			endScore = 0;

		const calcScoreN = ( scoring, value ) => {
			return Math.floor( value / scoring.per ) * scoring.score;
		};
		const calcSChars = ( weight, value ) => {
			return value * ( weight / 100 );
		};
		/**
		 * @param {int} charCount Character count in text.
		 * @param {int} sChars Simulated chars through weight.
		 */
		const calcDensity = ( charCount, sChars ) => {
			return ( sChars / charCount ) * 100;
		};
		const calcRealDensityScore = ( scoring, density ) => {
			return density / scoring.threshold * data.maxScore;
		};
		const calcEndDensityScore = ( score, max, min, penalty ) => {
			return tsfem_e_focus_inpost.getMaxIfOver( max, Math.max( min, max - ( score - max ) * penalty ) );
		};

		switch ( scoring.type ) {
			case 'n' :
				if ( countKeyword )
					realScore += calcScoreN( scoring.keyword, tsfem_e_focus_inpost.getMaxIfOver( scoring.keyword.max, countKeyword ) );
				if ( countSubject )
					realScore += calcScoreN( scoring.subject, tsfem_e_focus_inpost.getMaxIfOver( scoring.subject.max, countSubject ) );

				endScore = realScore;
				break;

			case 'p' :
				if ( charCount ) {
					if ( charCountKeyword )
						density += calcDensity( charCount, calcSChars( scoring.keyword.weight, charCountKeyword ) );
					if ( charCountSubject )
						density += calcDensity( charCount, calcSChars( scoring.subject.weight, charCountSubject ) );
				}

				realScore = calcRealDensityScore( scoring, density );
				endScore = calcEndDensityScore( realScore, scoring.max, scoring.min, scoring.penalty );
				break;
		}

		let iconType = tsfem_e_focus_inpost.getIconType( data.rating, realScore ),
			phrase = tsfem_e_focus_inpost.getNearestNumericIndexValue( data.phrasing, realScore );

		let $description = jQuery( rater ).find( '.tsfem-e-focus-assessment-description' );
		$description.animate( { 'opacity' : 0 }, {
			queue: false,
			duration: 150,
			complete: () => {
				$description.text( phrase + ' ' + realScore ).animate( { 'opacity' : 1 }, { queue: false, duration: 250 } );
				tsfem_e_focus_inpost.setIconClass(
					rater.querySelector( '.tsfem-e-focus-assessment-rating' ),
					iconType
				);
			}
		} );
	},

	getMaxIfOver: function( max, value ) {
		return value > max ? max : value;
	},

	/**
	 *
	 * @TODO find new source after cleanup:
	 * @source PHP TSF_Extension_Manager\Extension\Focus\Admin\Views\$_get_nearest_numeric_index_value();
	 */
	getNearestNumericIndexValue: function( obj, value ) {

		let ret = void 0;

		obj = tsfem_e_focus_inpost.sortMap( obj );

		for ( let index in obj ) {
			if ( ! isNaN( parseFloat( index ) ) && isFinite( index ) ) {
				if ( index <= value ) {
					ret = obj[ index ];
				} else {
					break;
				}
			}
		}

		return ret ? ret : obj[ Object.keys( obj )[0] ];
	},

	getIconType: function( ratings, value ) {

		let index = tsfem_e_focus_inpost.getNearestNumericIndexValue( ratings, value ).toString(),
			classes = {
				'-1' : 'error',
				'0'  : 'unknown',
				'1'  : 'bad',
				'2'  : 'warning',
				'3'  : 'okay',
				'4'  : 'good',
			};

		return ( index in classes ) && classes[ index ] || classes['0'];
	},

	prepareSubjectSetter: function( event ) {

		let keyword = event.target.value,
			idPrefix = tsfem_e_focus_inpost.getSubIdPrefix( event.target.id ),
			subjectField = tsfem_e_focus_inpost.getSubElementById( idPrefix, 'subject' );

		if ( ! subjectField ) return;

		//! TODO AJAX here for subject fetching, setting and executing.
	},

	prepareScores: function( event ) {

		let idPrefix = tsfem_e_focus_inpost.getSubIdPrefix( event.target.id ),
			contentWrap = tsfem_e_focus_inpost.getSubElementById( idPrefix, 'wrap' ),
			scoresWrap = tsfem_e_focus_inpost.getSubElementById( idPrefix, 'scores' ),
			subScores = scoresWrap && scoresWrap.querySelectorAll( '.tsfem-e-focus-assessment-wrap' );

		// TODO add error?
		if ( ! subScores || subScores !== Object( subScores ) ) return;

		tsfem_e_focus_inpost.toggleKeywordVisuals(
			idPrefix,
			'enable'
		);

		let data, $e, rating, blind, input;
		subScores.forEach( e => {
			rating = e.querySelector( '.tsfem-e-focus-assessment-rating' );
			tsfem_e_focus_inpost.setIconClass( rating, 'loading' );
			blind = true;
			$e = jQuery( e );
			data = $e.data( 'scores' );

			if ( data && data.hasOwnProperty( 'assessment' ) ) {
				if ( tsfem_e_focus_inpost.activeFocusAreas.hasOwnProperty( data.assessment.content ) ) {
					e.dataset.assess = true;
					blind = false;
					$e.fadeIn( {
						queue: false, // defer, go to next check.
						duration: 250,
						complete: () => { tsfem_e_focus_inpost.doCheck( e, event.target.value ); }
					} );
				}
			}
			if ( blind ) {
				tsfem_e_focus_inpost.setIconClass( rating, 'unknown' );
				e.dataset.assess = false;

				input = document.getElementsByName( e.id );
				if ( input && input[0] ) input[0].value = 0;

				$e.fadeOut( 500 );
			}
		} );

	},

	doKeywordEntry: function( event ) {

		let target = event.target,
			val = target.value.trim().replace( /[\s\t\r\n]+/g, ' ' ) || '',
			prev = target.dataset.prev || '';

		// Feed back trimmed.
		event.target.value = val;

		//= No change happened.
		if ( val === prev ) return;

		target.dataset.prev = val;

		if ( ! val.length ) {
			tsfem_e_focus_inpost.toggleKeywordVisuals(
				tsfem_e_focus_inpost.getSubIdPrefix( event.target.id ),
				'disable'
			);
			return;
		}

		if ( tsfem_e_focusInpostL10n.isPremium ) {
			tsfem_e_focus_inpost.prepareSubjectSetter( event );
		}

		tsfem_e_focus_inpost.prepareScores( event );
		// tsfem_e_focus_inpost.prepareHighlighter( event );
	},

	toggleKeywordVisuals: function( idPrefix, state ) {
		let contentWrap = tsfem_e_focus_inpost.getSubElementById( idPrefix, 'wrap' );

		if ( 'disable' === state ) {
			jQuery( contentWrap ).find( '.tsfem-e-focus-scores' ).fadeOut( 150, () => {
				jQuery( contentWrap ).find( '.tsfem-e-focus-no-keyword-wrap' ).fadeIn( 250 );
			} );
		} else {
			jQuery( contentWrap ).find( '.tsfem-e-focus-no-keyword-wrap' ).fadeOut( 150, () => {
				jQuery( contentWrap ).find( '.tsfem-e-focus-scores' ).fadeIn( 250 );
			} );
		}
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

	setIconClass: function( element, to ) {

		let classes = [
			'edit',
			'loading',
			'unknown',
			'error',
			'bad',
			'warning',
			'okay',
			'good',
		];

		classes.forEach( c => {
			element.classList.remove( 'tsfem-e-focus-icon-' + c );
			c === to && element.classList.add( 'tsfem-e-focus-icon-' + c );
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

		//= Reenable highlighter.
		$( '.tsfem-e-focus-requires-javascript' ).removeClass( 'tsfem-e-focus-requires-javascript' );

		// Initialize image uploader button cache.
		$( document.body ).ready( tsfem_e_focus_inpost.onReady );
	}
};
jQuery( tsfem_e_focus_inpost.load );
