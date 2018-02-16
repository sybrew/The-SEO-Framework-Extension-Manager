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
	activeAssessments: {},

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

	isActionableElement: function( element ) {

		if ( ! element instanceof HTMLElement )
			return false;

		let test =
			   element instanceof HTMLInputElement
			|| element instanceof HTMLSelectElement
			|| element instanceof HTMLLabelElement
			|| element instanceof HTMLButtonElement
			|| element instanceof HTMLTextAreaElement
			;

		return test;
	},

	getMaxIfOver: function( max, value ) {
		return value > max ? max : value;
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
	 *   'area' => [ selector => string Type 'append|dominate' ]
	 * }
	 * @param {bool|undefined} set Whether to add or remove the elements.
	 */
	updateFocusRegistry: function( elements, set ) {

		if ( ! elements || elements !== Object( elements ) ) return;

		let registry = tsfem_e_focus_inpost.focusRegistry;
		set = !! set;

		let selectors, type;

		//= Filter elements.
		for ( let area in elements ) {
			selectors = elements[ area ];
			if ( selectors !== Object( selectors ) )
				continue;

			registry[ area ]
				|| ( registry[ area ] = {} );

			for ( let selector in selectors ) {
				type = selectors[ selector ];

				registry[ area ][ type ]
					|| ( registry[ area ][ type ] = [] );

				if ( set ) {
					//= Test if entries exist.
					if ( registry[ area ][ type ].hasOwnProperty( selector ) )
						continue;

					registry[ area ][ type ].push( selector );
				} else {
					//= Unset
					registry[ area ][ type ] =
						registry[ area ][ type ].filter( s => s !== selector );
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
	 * @param {string|undefined} area The area to reset. Default undefined (catch-all).
	 */
	updateActiveFocusAreas: function( area ) {

		/**
		 * @param {!Object} elements : {
		 *   'area' => [ selector => string type 'append|dominate' ]
		 * }
		 */
		const elements = tsfem_e_focus_inpost.focusRegistry;

		if ( ! elements || elements !== Object( elements ) ) return;

		let areas = tsfem_e_focus_inpost.activeFocusAreas,
			types = {},
			hasDominant = false,
			lastDominant = '',
			keys = [];

		const update = ( area ) => {
			types = elements[ area ];
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
				areas[ area ] = [ lastDominant ];
			} else {
				if ( keys.length ) {
					areas[ area ] = keys;
				} else {
					delete areas[ area ];
				}
			}
		}

		if ( area ) {
			if ( area in elements )
				update( area );
		} else {
			//= Filter elements. The input is expected.
			for ( let _area in elements ) {
				update( _area );
			}
		}

		tsfem_e_focus_inpost.activeFocusAreas = areas;
	},

	// TODO:
	// 1. Add synonyms entry.
	/**
	 *
	 * @param {object<integer,string>|(array|undefined)} synonyms
	 */
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
			contents = contents.match( /[^>]+(?=<|$|^)/gi );
			return contents && contents.join( '' ).length || 0;
		};
		const countKeywords = ( kw, contents ) => {
			let n = regex.length,
				p;

			for ( let i = 0; i < n; ) {
				p = /\/(.*)\/(.*)/.exec( regex[ i ] );

				contents = contents.match( new RegExp(
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
				// TEMP
				// $description.text( phrase ).animate( { 'opacity' : 1 }, { queue: false, duration: 250 } );
				// TEMP: exchange for line above!!
				$description.html( phrase + ' <code>Temp eval: ' + realScore + '</code>' ).animate( { 'opacity' : 1 }, { queue: false, duration: 250 } );
				tsfem_e_focus_inpost.setRaterIconClass(
					rater.querySelector( '.tsfem-e-focus-assessment-rating' ),
					iconType
				);
			}
		} );
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

	/**
	 * Prepares all scores after keyword entry. Sets action listeners and performs
	 * first check. Asynchronously.
	 *
	 * @since 1.0.0
	 */
	prepareAllScores: function( event ) {

		let idPrefix = tsfem_e_focus_inpost.getSubIdPrefix( event.target.id ),
			contentWrap = tsfem_e_focus_inpost.getSubElementById( idPrefix, 'wrap' ),
			scoresWrap = tsfem_e_focus_inpost.getSubElementById( idPrefix, 'scores' ),
			subScores = scoresWrap && scoresWrap.querySelectorAll( '.tsfem-e-focus-assessment-wrap' );

		if ( ! subScores || subScores !== Object( subScores ) ) {
			//= subScores isn't set.
			tsfem_e_focus_inpost.toggleEvaluationVisuals(
				idPrefix,
				'error'
			);
			return;
		}

		tsfem_e_focus_inpost.toggleEvaluationVisuals(
			idPrefix,
			'enable'
		);

		subScores.forEach( el => {
			//= Set vars here to prevent async clashes.
			let data, $el, rating, blind, input;

			rating = el.querySelector( '.tsfem-e-focus-assessment-rating' );
			tsfem_e_focus_inpost.setRaterIconClass( rating, 'loading' );
			blind = true;
			$el = jQuery( el );
			data = $el.data( 'scores' );

			if ( data && data.hasOwnProperty( 'assessment' ) ) {
				if ( tsfem_e_focus_inpost.activeFocusAreas.hasOwnProperty( data.assessment.content ) ) {
					el.dataset.assess = true;
					tsfem_e_focus_inpost.addToChangeListener( el, data.assessment.content );
					blind = false;
					$el.fadeIn( {
						queue: false, // defer, go to next check.
						duration: 150,
						complete: () => {
							// defer and wait for paint lag.
							setTimeout( () => {
								tsfem_e_focus_inpost.doCheck( el, event.target.value );
							}, 150 );
						}
					} );
				}
			}
			if ( blind ) {
				//= Hide the element when it can't be parsed, for now.

				tsfem_e_focus_inpost.setRaterIconClass( rating, 'unknown' );
				el.dataset.assess = false;

				input = document.getElementsByName( el.id );
				if ( input && input[0] ) input[0].value = 0;

				$el.fadeOut( { queue: false, duration: 250 } );
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
			tsfem_e_focus_inpost.toggleEvaluationVisuals(
				tsfem_e_focus_inpost.getSubIdPrefix( event.target.id ),
				'disable'
			);
			return;
		}

		if ( tsfem_e_focusInpostL10n.isPremium ) {
			tsfem_e_focus_inpost.prepareSubjectSetter( event );
		}

		tsfem_e_focus_inpost.prepareAllScores( event );
		// tsfem_e_focus_inpost.prepareHighlighter( event );
	},

	toggleEvaluationVisuals: function( idPrefix, state ) {

		let contentWrap = tsfem_e_focus_inpost.getSubElementById( idPrefix, 'wrap' ),
			$wrap = jQuery( contentWrap ),
			hideClasses = [
				'.tsfem-e-focus-scores-wrap',
				'.tsfem-e-focus-no-keyword-wrap',
				'.tsfem-e-focus-something-wrong-wrap'
			],
			show = '';

		switch ( state ) {
			case 'disable' :
				show = '.tsfem-e-focus-no-keyword-wrap';
				break;

			case 'enable' :
				show = '.tsfem-e-focus-scores-wrap';
				break;

			default :
			case 'error' :
				show = '.tsfem-e-focus-something-wrong-wrap';
				break;
		}

		hideClasses.splice( hideClasses.indexOf( show ), 1 );

		$wrap.find( hideClasses.join( ', ' ) ).fadeOut( 150, () => {
			//= Paint lag escape.
			setTimeout( () => {
				$wrap.find( show ).fadeIn( 250 );
			}, 150 );
		} );
	},

	resetCollapserListeners: function() {

		//= Make the whole collapse bar a double-clickable expander/retractor.
		jQuery( '.tsfem-e-focus-collapse-header' )
			.off( 'dblclick.tsfem-e-focus' )
			.on( 'dblclick.tsfem-e-focus', ( e ) => {
				if ( tsfem_e_focus_inpost.isActionableElement( e.target ) )
					return;

				let $a = jQuery( e.target ).closest( '.tsfem-e-focus-collapse-wrap' ).find( 'input' );
				$a.prop( 'checked', ! $a.prop( 'checked' ) );
				//= Doesn't support IE11.
				// let a = e.target.closest( '.tsfem-e-focus-collapse-wrap' ).querySelector( 'input' );
				// if ( a instanceof Element ) a.checked = ! a.checked;
			} );

		let keywordBuffer = {},
			keywordTimeout = 1500;

		let barSmoothness = 2.5,
			superSmooth = true,
			barWidth = {},
			barBuffer = {},
			barTimeout = keywordTimeout / ( 100 * barSmoothness );

		//= Subtract a little of the bar timer to prevent painting/scripting overlap.
		if ( superSmooth )
			barTimeout *= .975;

		const barGo = ( id, bar ) => {
			bar.style.width = ++barWidth[ id ] / barSmoothness + '%';
		}
		const barStop = ( id, bar ) => {
			barWidth[ id ] = 0;
			bar.style.width = '0%';
		}

		//= Set keyword entry listener
		jQuery( '.tsfem-e-focus-keyword-entry' )
			.off( 'input.tsfem-e-focus' )
			.on( 'input.tsfem-e-focus', event => {

				//= Vars must be registered here as it's asynchronous.
				let loaderId = event.target.name;
				let bar = jQuery( event.target )
						.closest( '.tsfem-e-focus-collapse-wrap' )
						.find( '.tsfem-e-focus-content-loader-bar' )[0];

				clearInterval( barBuffer[ loaderId ] );
				clearTimeout( keywordBuffer[ loaderId ] );
				barStop( loaderId, bar );
				barBuffer[ loaderId ] = setInterval( () => barGo( loaderId, bar ), barTimeout );

				keywordBuffer[ loaderId ] = setTimeout( () => {
					clearInterval( barBuffer[ loaderId ] );
					tsfem_e_focus_inpost.doKeywordEntry( event );
					barStop( loaderId, bar );
				}, keywordTimeout );
			} );
	},

	setRaterIconClass: function( element, to ) {

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

	//! TODO use inline style instead.
	//= @see toggleEvaluationVisuals
	disableFocus: function() {
		let el = document.getElementById( 'tsfem-e-focus-analysis-wrap' )
			.querySelector( '.tsf-flex-setting-input' );

		if ( el instanceof Element )
			el.innerHTML = wp.template( 'tsfem-e-focus-nofocus' )();
	},

	setAllRatersOfTo: function( type, to ) {

		let assessments = tsfem_e_focus_inpost.activeAssessments;

		if ( ! assessments || assessments !== Object( assessments )
		|| ! ( type in assessments ) ) {
			return;
		}

		to = to || 'unknown';

		assessments[ type ].forEach( id => {
			let el = document.getElementById( id ),
				prefixID = tsfem_e_focus_inpost.getSubIdPrefix( id ),
				rater = el.querySelector( '.tsfem-e-focus-assessment-rating' );

			tsfem_e_focus_inpost.setRaterIconClass( rater, to );
		} );
	},

	addToChangeListener: function( checkerWrap, contentType ) {

		let areas = tsfem_e_focus_inpost.activeFocusAreas,
			assessments = tsfem_e_focus_inpost.activeAssessments;

		if ( ! assessments || assessments !== Object( assessments ) ) {
			assessments = [];
		}

		if ( ! assessments[ contentType ]
		|| assessments[ contentType ] !== Object( assessments[ contentType ] ) ) {
			assessments[ contentType ] = [];
		}

		//? Redundant check.
		if ( areas.hasOwnProperty( contentType ) ) {
			assessments[ contentType ].push( checkerWrap.id );
		} else {
			delete assessments[ contentType ][ checkerWrap.id ];
		}

		tsfem_e_focus_inpost.activeAssessments = assessments;
	},

	triggerChangeListener: function( event ) {

		let assessments = tsfem_e_focus_inpost.activeAssessments;

		if ( ! assessments || assessments !== Object( assessments )
		|| ! ( event.data.type in assessments ) ) {
			return;
		}

		assessments[ event.data.type ].forEach( id => {
			let el = document.getElementById( id ),
				prefixID = tsfem_e_focus_inpost.getSubIdPrefix( id ),
				kwInput = tsfem_e_focus_inpost.getSubElementById( prefixID, 'keyword' ),
				kw = kwInput.value || '';

			if ( ! kw.length )
				return; // continue

			let rater = el.querySelector( '.tsfem-e-focus-assessment-rating' );
			tsfem_e_focus_inpost.setRaterIconClass( rater, 'loading' );

			//= defer.
			setTimeout( () => {
				tsfem_e_focus_inpost.doCheck(
					el,
					kw,
					void 0 // TODO set synonyms
				);
			}, 150 );
		} );
	},

	/**
	 *
	 * @TODO test if this leaks memory. If so, place listener and buffers in object's scope.
	 *
	 * @param {string|undefined} type The type to reset. Default undefined (catch-all).
	 */
	resetChangeListeners: function( type ) {
		let areas = tsfem_e_focus_inpost.activeFocusAreas;

		let changeTimeout = 1500,
			changeBuffers = {};

		const listener = ( event ) => {
			let key = event.target.id || event.target.classList.join();
			clearTimeout( changeBuffers[ key ] );
			changeBuffers[ key ] = setTimeout( () => {
				tsfem_e_focus_inpost.triggerChangeListener( event );
			}, changeTimeout );
		};
		const reset = ( type ) => {
			let changeEventName = tsfem_e_focus_inpost.getChangeEventName( type );
			jQuery( areas[ type ].join( ', ' ) )
				.off( changeEventName )
				.on( changeEventName, { 'type' : type }, listener );
		};

		if ( type ) {
			if ( type in areas )
				reset( type );
		} else {
			for ( let _type in areas ) {
				reset( _type );
			}
		}
	},

	getChangeEvents: function( type ) {
		return [
			'input.tsfem-e-focus-' + type,
			// 'click.tsfem-e-focus-' + type,
			'change.tsfem-e-focus-' + type
		];
	},

	getChangeEventName: function( type ) {
		return tsfem_e_focus_inpost.getChangeEvents( type ).join( ' ' );
	},

	getChangeEventTrigger: function( type ) {
		return tsfem_e_focus_inpost.getChangeEvents( type )[0];
	},

	monkeyPatch: function() {

		const MutationObserver =
			   window.MutationObserver
			|| window.WebKitMutationObserver
			|| window.MozMutationObserver;

		const interval = 3000;

		/**
		 * Add interval-based change event listeners on '#post_name'
		 * @see ..\wp-admin\post.js:editPermalink()
		 */
		(() => {
			let lastValue, listenNode;
			listenNode = document.getElementById( 'post_name' );
			const compare = () => {
				if ( listenNode.value !== lastValue ) {
					lastValue = listenNode.value;
					jQuery( listenNode ).trigger(
						tsfem_e_focus_inpost.getChangeEventName( 'pageUrl' )
					);
				}
			};
			if ( listenNode ) {
				lastValue = listenNode.value;
				setInterval( compare, interval );
			}
		})();

		/**
		 * Add change event listeners on '#content' for tinyMCE
		 * @see ..\wp-admin\editor.js:initialize()
		 */
		(() => {
			if ( typeof tinyMCE === 'undefined' || typeof tinyMCE.on !== 'function' )
				return;

			let loaded = false;

			tinyMCE.on( 'addEditor', ( event ) => {
				if ( loaded ) return;
				if ( event.editor.id === 'content' ) {
					loaded = true;

					tsfem_e_focus_inpost.updateActiveFocusAreas( 'pageContent' );
					tsfem_e_focus_inpost.resetChangeListeners( 'pageContent' );

					let buffers = {},
						editor = tinyMCE.get( 'content' ),
						buffering = false;

					editor.on( 'GetContent', ( event ) => {
						clearTimeout( buffers['GetContent'] );
						if ( ! buffering && editor.isDirty() ) {
							tsfem_e_focus_inpost.setAllRatersOfTo( 'pageContent', 'loading' );
							buffering = true;
						}
						buffers['GetContent'] = setTimeout( () => {
							editor.isDirty() || jQuery( '#content' ).trigger(
								tsfem_e_focus_inpost.getChangeEventTrigger( 'pageContent' )
							);
							buffering = false;
						}, 1000 );
					} );
					editor.on( 'Dirty', ( event ) => {
						clearTimeout( buffers['Dirty'] );
						if ( ! buffering ) {
							buffers['Dirty'] = setTimeout( () => {
								tsfem_e_focus_inpost.setAllRatersOfTo( 'pageContent', 'unknown' );
							}, 500 );
						}
					} );
				}
			} );

			// let listenNode, observer, config;
			// observer = new MutationObserver( mutationsList => {
			// 	tsfem_e_focus_inpost.updateActiveFocusAreas( 'pageContent' );
			// 	tsfem_e_focus_inpost.resetChangeListeners( 'pageContent' );
			// 	jQuery( '#content' ).trigger(
			// 		tsfem_e_focus_inpost.getChangeEventName( 'pageContent' )
			// 	);
			// } );
			//= Tab switch events and heartbeat: Not important.
			// config = { attributes: true, characterData: true };
			// listenNode = document.getElementById( 'content' );
			// listenNode && observer.observe( listenNode, config );
		})();
	},

	onReady: function( event ) {

		tsfem_e_focus_inpost.monkeyPatch();

		if ( tsfem_e_focusInpostL10n.hasOwnProperty( 'focusElements' ) ) {
			tsfem_e_focus_inpost.updateFocusRegistry( tsfem_e_focusInpostL10n.focusElements, true );
			tsfem_e_focus_inpost.updateActiveFocusAreas();
			tsfem_e_focus_inpost.resetChangeListeners();
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
