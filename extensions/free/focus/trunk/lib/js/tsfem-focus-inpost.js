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
 * This is a self-constructed function assigned as an object.
 *
 * @since 1.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfem_e_focus_inpost = function( $ ) {

	const l10n = tsfem_e_focusInpostL10n;
	const noticeArea = '#tsfem-e-focus-analysis-notification-area';

	var focusRegistry = {},
		activeFocusAreas = {},
		activeAssessments = {};

	/**
	 * Gets string until last numeric ID index.
	 * In this application, no multidimensional IDs should be in place.
	 *
	 * in:  `tsfem-pm[focus][kw][42][something]`
	 * out: `tsfem-pm[focus][kw][42]`
	 *
	 */
	const getSubIdPrefix = ( id ) => {
		return /.*\[[0-9]+\]/.exec( id )[0];
	}

	const getSubElementById = ( prefix, type ) => {
		return document.getElementById( prefix + '[' + type + ']' );
	}

	const sortMap = ( obj ) => {
		//? Objects are automatically sorted in Chrome and IE. Sort again anyway.
		Object.keys( obj ).sort( ( a, b ) => {
			return Object.keys( a )[0] - Object.keys( b )[0];
		} );

		return obj;
	}

	const isActionableElement = ( element ) => {

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
	}

	const getMaxIfOver = ( max, value ) => {
		return value > max ? max : value;
	}

	/**
	 * Updates focus registry selectors.
	 *
	 * When adding objects dynamically, it's best to always use the append type.
	 *
	 * @since 1.0.0
	 * @see updateActiveFocusAreas() To be called after the
	 *      registry has been updated.
	 * @access private
	 *
	 * @function
	 * @param {!Object} elements : {
	 *   'area' => [ selector => string Type 'append|dominate' ]
	 * }
	 * @param {bool|undefined} set Whether to add or remove the elements.
	 */
	const updateFocusRegistry = ( elements, set ) => {

		if ( ! elements || elements !== Object( elements ) ) return;

		let registry = focusRegistry;
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

					// let values = Object.values( registry[ area ][ type ] );
					//= IE11 replacement for Object.values. <https://stackoverflow.com/a/42830295>
					let values = Object.keys( registry[ area ][ type ] ).map( e => registry[ area ][ type ][ e ] );

					if ( values.indexOf( selector ) > -1 )
						continue;

					registry[ area ][ type ].push( selector );
				} else {
					//= Unset
					registry[ area ][ type ] =
						registry[ area ][ type ].filter( s => s !== selector );
				}
			}
		}
		focusRegistry = registry;
	}

	/**
	 * Parses focus elements and their existence and registers them as available areas.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {string|undefined} area The area to reset. Default undefined (catch-all).
	 */
	const updateActiveFocusAreas = ( area ) => {

		/**
		 * @param {!Object} elements : {
		 *   'area' => [ selector => string type 'append|dominate' ]
		 * }
		 */
		const elements = focusRegistry;

		if ( ! elements || elements !== Object( elements ) ) return;

		let areas = activeFocusAreas,
			types = {},
			hasDominant = false,
			lastDominant = '',
			keys = [];

		const update = ( _area ) => {
			types = elements[ _area ];
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
				areas[ _area ] = [ lastDominant ];
			} else {
				if ( keys.length ) {
					areas[ _area ] = keys;
				} else {
					delete areas[ _area ];
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

		activeFocusAreas = areas;
	}

	// TODO:
	// 1. Add synonyms entry.
	/**
	 * @param {HTMLElement} rater
	 * @param {(string|object<integer,string>|(array|undefined))} inflections
	 * @param {object<integer,string>|(array|undefined)} synonyms
	 */
	const doCheck = ( rater, inflections, synonyms ) => {

		let $rater = $( rater );

		//! TODO notify?
		if ( ! $rater.length ) return;

		let data = $rater.data( 'scores' ),
			checkElements = activeFocusAreas[ data.assessment.content ],
			inflectionCount = 0,
			synonymCount = 0,
			inflectctionCharCount = 0,
			synonymCharCount = 0,
			contentCharCount = 0,
			content,
			regex = data.assessment.regex;

		//= Convert regex to object if it isn't already.
		if ( regex !== Object( regex ) ) {
			regex = [ regex ];
		}

		//= Convert inflections to object if it isn't already.
		if ( inflections !== Object( inflections ) ) {
			inflections = [ inflections ];
		}

		const countChars = ( contents ) => {
			// Strip all XML tags first.
			contents = contents.match( /[^>]+(?=<|$|^)/gi );
			return contents && contents.join( '' ).length || 0;
		};
		const countWords = ( word, contents ) => {
			let n = regex.length,
				pReg,
				matches = contents,
				count = 0,
				sWord = tsfem_inpost.escapeRegex( tsfem_inpost.escapeStr( word ) );

			//= Iterate over multiple regex scripts.
			for ( let i = 0; i < n; i++ ) {
				pReg = /\/(.*)\/(.*)/.exec( regex[ i ] );
				matches = matches.match( new RegExp(
					pReg[1].replace( /\{\{kw\}\}/g, sWord ),
					pReg[2]
				) );

				if ( ! matches || i === n - 1 ) break;

				//= Join content as this is a recursive regexp.
				matches = matches.join( ' ' );
			}
			// Return the number of matches found.
			return matches && matches.length || 0;
		};
		const stripWord = ( word ) => { return {
			from: ( contents ) =>
				contents.replace(
					new RegExp(
						tsfem_inpost.escapeRegex( tsfem_inpost.escapeStr( word ) ),
						'gi'
					),
					'/' //? A filler that doesn't break XML tag attribute closures ("|'|\s).
				)
		} };
		const countInflections = ( inflections, content ) => {
			let _inflections = inflections,
				_content = content;
			//= Sort words by longest to shortest, but in natural language order (a-z).
			//= The natural order doesn't pertain to effectiveness. It's just cleaner in debugging.
			_inflections.sort( ( a, b ) => {
				return b.length - a.length || a.localeCompare( b );
			} );
			_inflections.forEach( ( cj ) => {
				let count = countWords( cj, _content );

				inflectionCount += count;
				inflectctionCharCount += cj.length * count;
				//= Strip found word from contents.
				_content = stripWord( cj ).from( _content );
			} );
		};
		const countSynonyms = ( synonyms, contents ) => {
		};
		const foundContent = content => !! ( void 0 !== content && content.length );

		//! TODO cache values found per selector. In data?
		checkElements.forEach( selector => {
			//= Wrap content in spaces to simulate word boundaries.
			let $selector = $( selector );

			content = '';

			//? Simulated goto
			switch ( 0 ) { default:
				content = $selector.val();
				if ( foundContent( content ) ) break;
				content = $selector.attr( 'placeholder' );
				if ( foundContent( content ) ) break;
				content = $selector.text();
			}

			if ( foundContent( content ) ) {
				countInflections( inflections, content );
				countSynonyms( synonyms, content );
				contentCharCount += countChars( content );
			}
		} );

		let scoring = data.scoring,
			maxScore = data.maxScore,
			density = 0,
			realScore = 0,
			endScore = 0;

		const calcScoreN = ( scoring, value ) => Math.floor( value / scoring.per ) * scoring.score;
		const calcSChars = ( weight, value ) => value * ( weight / 100 );
		/**
		 * @param {int} charCount Character count in text.
		 * @param {int} sChars Simulated chars through weight.
		 */
		const calcDensity = ( charCount, sChars ) => ( sChars / charCount ) * 100;
		const calcRealDensityScore = ( scoring, density ) => density / scoring.threshold * data.maxScore;
		const calcEndDensityScore = ( score, max, min, penalty ) => getMaxIfOver( max, Math.max( min, max - ( score - max ) * penalty ) );

		switch ( scoring.type ) {
			case 'n' :
				if ( inflectionCount )
					realScore += calcScoreN( scoring.keyword, getMaxIfOver( scoring.keyword.max, inflectionCount ) );
				if ( synonymCount )
					realScore += calcScoreN( scoring.synonym, getMaxIfOver( scoring.synonym.max, synonymCount ) );

				endScore = realScore;
				break;

			case 'p' :
				if ( contentCharCount ) {
					if ( inflectctionCharCount )
						density += calcDensity( contentCharCount, calcSChars( scoring.keyword.weight, inflectctionCharCount ) );
					if ( synonymCharCount )
						density += calcDensity( contentCharCount, calcSChars( scoring.synonym.weight, synonymCharCount ) );
				}

				realScore = calcRealDensityScore( scoring, density );
				endScore = calcEndDensityScore( realScore, scoring.max, scoring.min, scoring.penalty );
				break;
		}

		let iconType = getIconType( data.rating, realScore ),
			phrase = getNearestNumericIndexValue( data.phrasing, realScore );

		//= Store realScore in input for saving.
		let input = document.querySelector( 'input[name="' + rater.id + '"]' );
		if ( input ) input.value = realScore;

		let $description = $rater.find( '.tsfem-e-focus-assessment-description' );
		$description.animate( { 'opacity' : 0 }, {
			queue: false,
			duration: 150,
			complete: () => {
				// TEMP
				// $description.text( phrase ).animate( { 'opacity' : 1 }, { queue: false, duration: 250 } );
				// TEMP: exchange for line above!!
				$description.html( phrase + ' <code>Temp eval: ' + realScore + '</code>' ).animate( { 'opacity' : 1 }, { queue: false, duration: 250 } );
				tsfem_inpost.setIconClass(
					rater.querySelector( '.tsfem-e-focus-assessment-rating' ),
					iconType
				);
			}
		} );
	}

	/**
	 *
	 * @TODO find new source after cleanup:
	 * @source PHP TSF_Extension_Manager\Extension\Focus\Admin\Views\$_get_nearest_numeric_index_value();
	 */
	const getNearestNumericIndexValue = ( obj, value ) => {

		let ret = void 0;

		obj = sortMap( obj );

		for ( let index in obj ) {
			if ( isFinite( index ) && ! isNaN( parseFloat( index ) ) ) {
				if ( index <= value ) {
					ret = obj[ index ];
				} else {
					break;
				}
			}
		}

		return ret ? ret : obj[ Object.keys( obj )[0] ];
	}

	const getIconType = ( ratings, value ) => {

		let index = getNearestNumericIndexValue( ratings, value ).toString(),
			classes = {
				'-1' : 'error',
				'0'  : 'unknown',
				'1'  : 'bad',
				'2'  : 'warning',
				'3'  : 'okay',
				'4'  : 'good',
			};

		return ( index in classes ) && classes[ index ] || classes['0'];
	}

	const runDefinitionSetter = ( idPrefix ) => {

		clearDefinition( idPrefix );
		clearInflections( idPrefix );
		clearSynonyms( idPrefix );

		let keyword = getSubElementById( idPrefix, 'keyword' ).value,
			definitionField = getSubElementById( idPrefix, 'definition' );

		if ( ! definitionField instanceof HTMLInputElement ) return;

		setEditButton( idPrefix ).to( 'enabled, loading' );

		$.when( getDefinitions( idPrefix, keyword ) ).done( ( data ) => {
			setDefinitionSelectionFields( idPrefix, data.definitions );
			data.definitions.length && setDefinitionSelectionListener( idPrefix ).to( 'enabled' );
		} ).always( () => {
			setEditButton( idPrefix ).to( 'edit' );
		} );
	}

	const getDefinitions = ( idPrefix, keyword ) => {

		let ops = {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfem_e_local_get_definitions',
				'nonce' : l10n.nonce,
				'post_ID' : tsfem_inpost.postID,
				'args' : {
					'keyword': keyword,
					'language': 'en' // language || 'en', // TODO
				},
			},
			timeout: 5000,
			async: true,
		};

		let dfd = $.Deferred();

		tsfem_inpost.doAjax(
			dfd,
			ops,
			{ 'noticeArea': noticeArea, 'premium': true }
		);

		return dfd.promise();
	}

	const clearDefinition = ( idPrefix ) => {
		let definitionField = getSubElementById( idPrefix, 'definition' ),
			definitionSelector = getSubElementById( idPrefix, 'definition_selector' ),
			definitionData = getSubElementById( idPrefix, 'definition_data' );

		if ( definitionField instanceof HTMLInputElement ) {
			definitionField.value = '';
		}
		if ( definitionData instanceof HTMLInputElement ) {
			definitionData.value = '';
		}
		if ( definitionSelector instanceof HTMLSelectElement ) {
			definitionSelector.disabled = true;
			definitionSelector.selectedIndex = 0;
			updateDefinitionSelector( idPrefix, l10n.defaultDefinition );
		}
		//= TODO clear synonyms and all relationships thereof.
	}
	const clearInflections = ( idPrefix ) => { }
	const clearSynonyms = ( idPrefix ) => { }

	const updateDefinitionSelector = ( idPrefix, definitions ) => {
		let definitionSelector = getSubElementById( idPrefix, 'definition_selector' );

		//= Removes all options.
		for ( let _i = definitionSelector.options.length; _i >= 0; _i-- ) {
			definitionSelector.remove( _i );
		}

		let _option = document.createElement( 'option' ),
			_list = JSON.parse( definitions );

		if ( _list ) {
			for ( let i in _list ) {
				_option = _option.cloneNode();
				_option.value = tsfem_inpost.escapeStr( i );
				_option.innerHTML = tsfem_inpost.escapeStr( _list[ i ].name );
				definitionSelector.appendChild( _option );
			}
		}
	}

	const setDefinitionSelectionFields = ( idPrefix, definitions ) => {

		let definitionField = getSubElementById( idPrefix, 'definition' ),
			definitionSelector = getSubElementById( idPrefix, 'definition_selector' ),
			definitionData = getSubElementById( idPrefix, 'definition_data' );

		let _definitions = JSON.parse( l10n.defaultDefinition );
		// _definitions = Object.values( _definitions );
		//= IE11 replacement for Object.values.
		_definitions = Object.keys( _definitions ).map( e => _definitions[ e ] );
		definitions.forEach( ( entry ) => {
			if ( entry.inflection && entry.category ) {
				_definitions.push( {
					'value' : entry.inflection,
					'category' : entry.category,
					'name' : entry.category + ': ' + entry.inflection,
				} );
			}
		} );
		let definitionsValue = _definitions.length && JSON.stringify( _definitions ) || l10n.defaultDefinition;

		if ( definitionField instanceof HTMLInputElement ) {
			definitionField.value = '';
		}
		if ( definitionData instanceof HTMLInputElement ) {
			definitionData.value = definitionsValue;
		}
		if ( definitionSelector instanceof HTMLSelectElement ) {
			updateDefinitionSelector( idPrefix, definitionsValue );
			definitionSelector.disabled = false;
			definitionSelector.selectedIndex = 0;
		}
	}

	let definitionSelectionBuffer = {};
	/**
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param {string} idPrefix
	 * @return {function} { to : {
	 *    @param {string} state
	 * } }
	 */
	const setDefinitionSelectionListener = ( idPrefix ) => {

		let definitionSelector = getSubElementById( idPrefix, 'definition_selector' ),
			ns = 'tsfemInpostDefinition',
			changeTimeout = 1500;

		if ( ! definitionSelector instanceof HTMLSelectElement ) return;

		const setDefinition = ( idPrefix, value ) => {
			//= Set static definition field.
			getSubElementById( idPrefix, 'definition' ).value = value;
		}
		const prepareSynonyms = ( idPrefix ) => {
			$.when( getSynonyms( idPrefix ) ).done( ( data ) => {
				let synonymHolder = getSubElementById( idPrefix, 'synonym_data' );
				synonymHolder.value = JSON.stringify( data.synonyms );

				// setSubjectSelectionFields( idPrefix, data.subject );
				// data.subject.length && setSubjectSelectionListener( idPrefix ).to( 'enabled' );
			} ).always( () => {
				setEditButton( idPrefix ).to( 'edit' );
			} );
		}

		return {
			to: ( what ) => {
				switch ( what ) {
					case 'enabled' :
						jQuery( definitionSelector )
							.off( 'change.' + ns )
							.on( 'change.' + ns, { 'idPrefix': idPrefix }, ( event ) => {
								setEditButton( idPrefix ).to( 'loading' );
								clearTimeout( definitionSelectionBuffer[ idPrefix ] );
								definitionSelectionBuffer[ idPrefix ] = setTimeout(
									() => {
										setDefinition( event.data.idPrefix, event.target.value );
										if ( +event.target.value ) {
											prepareSynonyms( event.data.idPrefix );
										} else {
											setEditButton( idPrefix ).to( 'edit' );
										}
									},
									changeTimeout
								);
							} );
						break;

					default :
					case 'disabled' :
						jQuery( definitionSelector ).off( 'change.' + ns );
						break;
				}
			}
		}
	}

	const getSynonyms = ( idPrefix ) => {

		let definitionField = getSubElementById( idPrefix, 'definition' ),
			definitionData = getSubElementById( idPrefix, 'definition_data' );

		let definitions = JSON.parse( definitionData.value ),
			definition = definitions[ definitionField.value ];

		let ops = {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfem_e_local_get_synonyms',
				'nonce' : l10n.nonce,
				'post_ID' : tsfem_inpost.postID,
				'args' : {
					'definition': definition,
					'language': 'en' // language || 'en', // TODO
				},
			},
			timeout: 7000,
			async: true,
		};

		let dfd = $.Deferred();

		tsfem_inpost.doAjax(
			dfd,
			ops,
			{ 'noticeArea': noticeArea, 'premium': true }
		);

		return dfd.promise();
	}

	/**
	 * Prepares all scores after keyword entry. Sets action listeners and performs
	 * first check. Asynchronously.
	 *
	 * @since 1.0.0
	 */
	const prepareWrapScoreElements = ( idPrefix ) => {

		let contentWrap = getSubElementById( idPrefix, 'wrap' ),
			scoresWrap = getSubElementById( idPrefix, 'scores' ),
			subScores = scoresWrap && scoresWrap.querySelectorAll( '.tsfem-e-focus-assessment-wrap' );

		if ( ! subScores || subScores !== Object( subScores ) ) {
			//= subScores isn't set.
			setEvaluationVisuals( idPrefix ).to( 'error' );
			return;
		}

		setEvaluationVisuals( idPrefix ).to( 'enable' );

		subScores.forEach( el => prepareScoreElement( el ) );
	}

	const prepareScoreElement = ( el ) => {

		let idPrefix = getSubIdPrefix( el.id ),
			kw = getSubElementById( idPrefix, 'keyword' ).value;

		if ( ! kw ) return;

		let data, $el, rating, blind, input;

		rating = el.querySelector( '.tsfem-e-focus-assessment-rating' );
		tsfem_inpost.setIconClass( rating, 'loading' );
		blind = true;
		$el = $( el );
		data = $el.data( 'scores' );

		if ( data && data.hasOwnProperty( 'assessment' ) ) {
			if ( activeFocusAreas.hasOwnProperty( data.assessment.content ) ) {
				el.dataset.assess = 1;
				addToChangeListener( el, data.assessment.content );
				blind = false;
				$el.fadeIn( {
					queue: false, // defer, go to next check.
					duration: 150,
					complete: () => {
						// defer and wait for paint lag.
						setTimeout( () => {
							doCheck( el, kw );
						}, 150 );
					}
				} );
			}
		}
		if ( blind ) {
			//= Hide the element when it can't be parsed, for now.

			tsfem_inpost.setIconClass( rating, 'unknown' );
			el.dataset.assess = 0;

			input = document.getElementsByName( el.id );
			if ( input && input[0] ) input[0].value = 0;

			$el.fadeOut( { queue: false, duration: 250 } );
		}
	}

	const doKeywordEntry = ( event ) => {

		let target = event.target,
			val = target.value.trim().replace( /[\s\t\r\n]+/g, ' ' ) || '',
			prev = target.dataset.prev || '';

		// Feed back trimmed.
		event.target.value = val;

		//= No change happened.
		if ( val === prev ) return;

		let idPrefix = getSubIdPrefix( event.target.id );

		target.dataset.prev = val;

		if ( ! val.length ) {
			setEvaluationVisuals( idPrefix ).to( 'disable' );
			setEditButton( idPrefix ).to( 'disabled, edit' );
			setDefinitionSelectionListener( idPrefix ).to( 'disabled' );
			return;
		}

		if ( tsfem_inpost.isPremium ) {
			runDefinitionSetter( idPrefix );
		}

		prepareWrapScoreElements( idPrefix );
		// prepareHighlighter( event );
	}

	/**
	 * Toggles display of subject analysis lines.
	 * @since 1.0.0
	 * @function
	 *
	 * @param {string} idPrefix
	 * @return {function} to : {
	 *    @param {string} state
	 * }
	 */
	const setEvaluationVisuals = ( idPrefix ) => {
		let contentWrap = getSubElementById( idPrefix, 'wrap' ),
			$wrap = $( contentWrap ),
			hideClasses = [
				'.tsfem-e-focus-scores-wrap',
				'.tsfem-e-focus-no-keyword-wrap',
				'.tsfem-e-focus-something-wrong-wrap'
			],
			show = '';

		const run = () => {
			hideClasses.splice( hideClasses.indexOf( show ), 1 );

			$wrap.find( hideClasses.join( ', ' ) ).fadeOut( 150, () => {
				//= Paint lag escape.
				setTimeout( () => {
					$wrap.find( show ).fadeIn( 250 );
				}, 150 );
			} );
		}

		return {
			to: ( state ) => {
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
				run();
			}
		};
	}

	const setEditButton = ( idPrefix ) => {
		let editWrap = getSubElementById( idPrefix, 'subject_edit' ),
			className = 'tsfem-e-focus-edit-subject-wrap-disabled',
			editButton = editWrap && editWrap.querySelector( '.tsfem-e-focus-edit-subject' );

		return { to: ( state ) => {
			state.split( ',' ).forEach( ( _state ) => {
				switch ( _state.trim() ) {
					case 'loading' :
						editButton && tsfem_inpost.setIconClass( editButton, 'loading' );
						break;

					case 'edit' :
						editButton && tsfem_inpost.setIconClass( editButton, 'edit' );
						break;

					case 'enabled' :
						//= Simulate toggle(*,false) IE11.
						editWrap.classList.add( className );
						editWrap.classList.remove( className );
						break;
					case 'disabled' :
						//= Simulate toggle(*,true) IE11.
						editWrap.classList.remove( className );
						editWrap.classList.add( className );
						break;

					default:break;
				}
			} );
		} };
	}

	const resetCollapserListeners = () => {

		//= Make the whole collapse bar a double-clickable expander/retractor.
		$( '.tsfem-e-focus-collapse-header' )
			.off( 'dblclick.tsfem-e-focus' )
			.on( 'dblclick.tsfem-e-focus', ( event ) => {
				if ( isActionableElement( event.target ) )
					return;

				let $a = $( event.target ).closest( '.tsfem-e-focus-collapse-wrap' ).find( 'input' );
				$a.prop( 'checked', ! $a.prop( 'checked' ) );
				//= Doesn't support IE11.
				// let a = e.target.closest( '.tsfem-e-focus-collapse-wrap' ).querySelector( 'input' );
				// if ( a instanceof Element ) a.checked = ! a.checked;
			} );
	}

	const resetKeywordEntryListeners = () => {

		let keywordBuffer = {},
			keywordTimeout = 1500;

		let barSmoothness = 3,
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

		let $keywordEntries = $( '.tsfem-e-focus-keyword-entry' );

		//= Set keyword entry listener
		$keywordEntries
			.off( 'input.tsfem-e-focus' )
			.on( 'input.tsfem-e-focus', ( event ) => {
				//= Vars must be registered here as it's asynchronous.
				let loaderId = event.target.name;
				let bar = $( event.target )
						.closest( '.tsfem-e-focus-collapse-wrap' )
						.find( '.tsfem-e-focus-content-loader-bar' )[0];

				clearInterval( barBuffer[ loaderId ] );
				clearTimeout( keywordBuffer[ loaderId ] );
				barStop( loaderId, bar );
				barBuffer[ loaderId ] = setInterval( () => barGo( loaderId, bar ), barTimeout );

				keywordBuffer[ loaderId ] = setTimeout( () => {
					clearInterval( barBuffer[ loaderId ] );
					doKeywordEntry( event );
					barStop( loaderId, bar );
				}, keywordTimeout );
			} );

		$keywordEntries.each( ( i, el ) => {
			if ( ! el.value.length ) {
				clearDefinition( getSubIdPrefix( el.id ) );
			}
		} );
	}

	//! TODO use inline style instead.
	//= @see setEvaluationVisuals
	const disableFocus = () => {
		let el = document.getElementById( 'tsfem-e-focus-analysis-wrap' )
			.querySelector( '.tsf-flex-setting-input' );

		if ( el instanceof Element )
			el.innerHTML = wp.template( 'tsfem-e-focus-nofocus' )();
	}

	const setAllRatersOf = ( type ) => {
		return { to: ( to ) => {
			if ( ! activeAssessments || activeAssessments !== Object( activeAssessments )
			|| ! ( type in activeAssessments ) ) {
				return;
			}

			to = to || 'unknown';

			activeAssessments[ type ].forEach( id => {
				let el = document.getElementById( id ),
					prefixID = getSubIdPrefix( id ),
					rater = el.querySelector( '.tsfem-e-focus-assessment-rating' );

				tsfem_inpost.setIconClass( rater, to );
			} );
		} };
	}

	const addToChangeListener = ( checkerWrap, contentType ) => {

		let assessments = activeAssessments;

		if ( ! assessments || assessments !== Object( assessments ) ) {
			assessments = [];
		}

		if ( ! assessments[ contentType ]
		|| assessments[ contentType ] !== Object( assessments[ contentType ] ) ) {
			assessments[ contentType ] = [];
		}

		//? Redundant check.
		if ( activeFocusAreas.hasOwnProperty( contentType ) ) {
			assessments[ contentType ].push( checkerWrap.id );
		} else {
			delete assessments[ contentType ][ checkerWrap.id ];
		}

		activeAssessments = assessments;
	}

	const triggerChangeListener = ( event ) => {

		if ( ! activeAssessments || activeAssessments !== Object( activeAssessments )
		|| ! ( event.data.type in activeAssessments ) ) {
			return;
		}

		activeAssessments[ event.data.type ].forEach( id => {
			let el = document.getElementById( id ),
				prefixID = getSubIdPrefix( id ),
				kwInput = getSubElementById( prefixID, 'keyword' ),
				kw = kwInput.value || '';

			if ( ! kw.length )
				return; // continue

			let rater = el.querySelector( '.tsfem-e-focus-assessment-rating' );
			tsfem_inpost.setIconClass( rater, 'loading' );

			//= defer.
			setTimeout( () => {
				doCheck(
					el,
					kw,
					void 0 // TODO set synonyms
				);
			}, 150 );
		} );
	}

	let changeListenersBuffers = {};
	/**
	 * Resets change listeners for analysis on the available content elements.
	 *
	 * @since 1.0.0
	 * @access public
	 * @TODO test if this leaks memory. If so, place listener in class's scope.
	 *       It probably won't.
	 *
	 * @function
	 * @param {string|undefined} type The type to reset. Default undefined (catch-all).
	 */
	const resetAnalysisChangeListeners = ( type ) => {
		let changeTimeout = 1500;

		const listener = ( event ) => {
			let key = event.target.id || event.target.classList.join();
			clearTimeout( changeListenersBuffers[ key ] );
			changeListenersBuffers[ key ] = setTimeout( () => {
				triggerChangeListener( event );
			}, changeTimeout );
		};
		const reset = ( type ) => {
			let changeEventName = getAnalysisChangeEventNames( type );
			$( activeFocusAreas[ type ].join( ', ' ) )
				.off( changeEventName )
				.on( changeEventName, { 'type' : type }, listener );
		};

		if ( type ) {
			if ( type in activeFocusAreas )
				reset( type );
		} else {
			for ( let _type in activeFocusAreas ) {
				reset( _type );
			}
		}
	}

	/**
	 * Returns all analysis jQuery event types.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param {string} type The content type.
	 * @return {(array|object<number,string>)}
	 */
	const getAnalysisChangeEvents = ( type ) => {
		return [
			'input.tsfem-e-focus-' + type,
			// 'click.tsfem-e-focus-' + type,
			'change.tsfem-e-focus-' + type
		];
	}

	/**
	 * Returns all analysis event types in jQuery readable event string.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param {string} type The content type.
	 * @return {string}
	 */
	const getAnalysisChangeEventNames = ( type ) => {
		return getAnalysisChangeEvents( type ).join( ' ' );
	}

	/**
	 * Returns first Analysis jQuery event name to be used in triggers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param {string} type The content type.
	 * @return {string}
	 */
	const getAnalysisChangeEventTrigger = ( type ) => {
		return getAnalysisChangeEvents( type )[0];
	}

	/**
	 * Applies analysis listeners to elements that can't have default JS listeners.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
	const monkeyPatch = () => {

		const MutationObserver =
			   window.MutationObserver
			|| window.WebKitMutationObserver
			|| window.MozMutationObserver;
		const interval = 3000;

		/**
		 * Observes page URL changes.
		 * @see [...]\wp-admin\post.js:editPermalink()
		 */
		(()=>{
			if ( 'pageUrl' in l10n.focusElements ) {
				let listenNode, observer, config;

				const updatePageUrlRegistry = () => {
					let unregisteredUrlAssessments = document.querySelectorAll(
						'.tsfem-e-focus-assessment-wrap[data-assessment-type="url"][data-assess="0"]'
					);
					if ( unregisteredUrlAssessments.length ) {
						updateActiveFocusAreas( 'pageUrl' );
						resetAnalysisChangeListeners( 'pageUrl' );
						unregisteredUrlAssessments.forEach( el => prepareScoreElement( el ) );
					}
				}

				observer = new MutationObserver( mutationsList => {
					updatePageUrlRegistry();
					$( '#sample-permalink' ).trigger( getAnalysisChangeEventNames( 'pageUrl' ) );
				} );
				//? Observe the childList data.
				config = { childList: true };
				listenNode = document.getElementById( 'edit-slug-box' );
				listenNode && observer.observe( listenNode, config );
			}
		})();

		/**
		 * Add extra change event listeners on '#content' for tinyMCE.
		 * @see [...]\wp-admin\editor.js:initialize()
		 */
		(() => {
			if ( typeof tinyMCE === 'undefined' || typeof tinyMCE.on !== 'function' )
				return;

			let loaded = false;

			tinyMCE.on( 'addEditor', ( event ) => {
				if ( loaded ) return;
				if ( event.editor.id !== 'content' ) return;
				loaded = true; //= prevent further checks.

				updateActiveFocusAreas( 'pageContent' );
				resetAnalysisChangeListeners( 'pageContent' );

				let buffers = {},
					editor = tinyMCE.get( 'content' ),
					buffering = false;

				editor.on( 'GetContent', ( event ) => {
					clearTimeout( buffers['GetContent'] );
					if ( ! buffering && editor.isDirty() ) {
						setAllRatersOf( 'pageContent' ).to( 'loading' );
						buffering = true;
					}
					buffers['GetContent'] = setTimeout( () => {
						editor.isDirty() || $( '#content' ).trigger(
							getAnalysisChangeEventTrigger( 'pageContent' )
						);
						buffering = false;
					}, 1000 );
				} );
				editor.on( 'Dirty', ( event ) => {
					clearTimeout( buffers['Dirty'] );
					if ( ! buffering ) {
						buffers['Dirty'] = setTimeout( () => {
							setAllRatersOf( 'pageContent' ).to( 'unknown' );
						}, 500 );
					}
				} );
			} );
		})();
	}

	/**
	 * Initializes the plugin onReady.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.event} event
	 */
	const onReady = ( event ) => {

		monkeyPatch();

		if ( l10n.hasOwnProperty( 'focusElements' ) ) {
			updateFocusRegistry( l10n.focusElements, true );
			updateActiveFocusAreas();
			resetAnalysisChangeListeners();
		}

		//= There's nothing to focus on.
		if ( 0 === Object.keys( activeFocusAreas ).length ) {
			disableFocus();
			return;
		}

		resetCollapserListeners();
		resetKeywordEntryListeners();
	}

	//? IE11 Object.assign() alternative.
	return $.extend( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 1.0.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: function() {

			//= Reenable focus elements.
			$( '.tsfem-e-focus-enable-if-js' ).removeProp( 'disabled' );
			//= Disable nojs placeholders.
			$( '.tsfem-e-focus-disable-if-js' ).prop( 'disabled', 'disabled' );

			//= Reenable highlighter.
			$( '.tsfem-e-focus-requires-javascript' ).removeClass( 'tsfem-e-focus-requires-javascript' );

			// Initialize image uploader button cache.
			$( document.body ).ready( onReady );
		}
	}, {
		/**
		 * Copies internal public functions to tsfem_e_focus_inpost for public access.
		 * Don't overwrite these.
		 *
		 * @since 1.0.0
		 * @access public
		 */
 		focusRegistry,
 		activeFocusAreas,
 		activeAssessments,
		updateFocusRegistry,
		updateActiveFocusAreas,
		resetAnalysisChangeListeners,
		getAnalysisChangeEvents,
		getAnalysisChangeEventNames,
		getAnalysisChangeEventTrigger,
		getSubIdPrefix,
		getSubElementById,
	} );
}( jQuery );
//= Run before jQuery.ready() === DOMContentLoaded
jQuery( window.tsfem_e_focus_inpost.load );
