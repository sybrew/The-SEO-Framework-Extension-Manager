/**
 * This file holds Focus' code for interpreting keywords and their subjects.
 * Serve JavaScript as an addition, not as an ends or means.
 * Alas, there's no other way here.
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
		return ( /.*\[[0-9]+\]/.exec( id ) || '' )[0];
	}

	const createSubId = ( idPrefix, name ) => {
		return idPrefix + '[' + name + ']';
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
			//? Redundant?
			// if ( selectors !== Object( selectors ) ) continue;

			registry[ area ]
				|| ( registry[ area ] = {} );

			for ( let selector in selectors ) {
				type = selectors[ selector ];

				registry[ area ][ type ]
					|| ( registry[ area ][ type ] = [] );

				//= Test if entries exist.
				if ( set ) {
					// let values = Object.values( registry[ area ][ type ] );
					//= IE11 replacement for Object.values. <https://stackoverflow.com/a/42830295>
					let values = Object.keys( registry[ area ][ type ] ).map( e => registry[ area ][ type ][ e ] );
					if ( values.indexOf( selector ) > -1 ) continue;
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
			if ( typeof types.dominate !== 'undefined' ) {
				types.dominate.forEach( selector => {
					//= Skip if the selector doesn't exist.
					if ( ! document.querySelector( selector ) ) return;
					hasDominant = true;
					lastDominant = selector;
				} );
			}
			if ( ! hasDominant && typeof types.append !== 'undefined' ) {
				types.append.forEach( selector => {
					//= Skip if the selector doesn't exist.
					if ( ! document.querySelector( selector ) ) return;
					keys.push( selector );
				} );
			}
			if ( hasDominant ) {
				areas[ _area ] = [ lastDominant ];
			} else {
				if ( keys.length ) {
					areas[ _area ] = keys;
				} else {
					unset( _area );
				}
			}
		}
		const unset = ( area ) => {
			delete areas[ area ];
		}

		if ( area ) {
			if ( typeof elements[ area ] === 'undefined' ) {
				unset( area );
			} else {
				update( area );
			}
		} else {
			//= Filter elements. The input is expected.
			let _area;
			for ( _area in elements ) {
				update( _area );
			}
		}

		activeFocusAreas = areas;
	}

	/**
	 * @param {HTMLElement} rater
	 */
	const doCheck = ( rater ) => {

		let data = $( rater ).data( 'scores' ),
			inflectionCount = 0,
			synonymCount = 0,
			inflectionCharCount = 0,
			synonymCharCount = 0,
			contentCharCount = 0,
			content,
			regex = data.assessment.regex;

		//= Convert regex to object if it isn't already.
		if ( regex !== Object( regex ) ) {
			regex = [ regex ];
		}

		/**
		 * @param {(boolean|object<number,string>|array)} inflections
		 * @param {boolean|object<number,string>|array)} synonyms
		 */
		let idPrefix = getSubIdPrefix( rater.id ),
			inflections = activeWords( idPrefix ).get( 'inflections' ),
			synonyms = activeWords( idPrefix ).get( 'synonyms' );

		const countChars = ( contents ) => {
			// Strip all XML tags first.
			contents = contents.match( /[^>]+(?=<|$|^)/gi );
			return contents && contents.join( '' ).length || 0;
		}
		const countWords = ( word, contentMatch ) => {
			let pReg,
				sWord = tsfem_inpost.escapeRegex( tsfem_inpost.escapeStr( word ) );

			//= Iterate over multiple regex scripts.
			for ( let i = 0; i < regex.length; i++ ) {
				pReg = /\/(.*)\/(.*)/.exec( regex[ i ] );
				contentMatch = contentMatch.match( new RegExp(
					pReg[1].replace( /\{\{kw\}\}/g, sWord ),
					pReg[2]
				) );

				//= Stop if there's no content, or when this is the last iteration.
				if ( ! contentMatch || i === regex.length - 1 ) break;

				//= Join content as this is a recursive regexp.
				contentMatch = contentMatch.join( ' ' );
			}
			// Return the number of matches found.
			return contentMatch && contentMatch.length || 0;
		}
		const stripWord = ( word, contents ) =>
			contents.replace(
				new RegExp(
					tsfem_inpost.escapeRegex( tsfem_inpost.escapeStr( word ) ),
					'gi'
				),
				'/' //? A filler that doesn't break XML tag attribute closures (<|>|"|'|\s).
			);

		const countInflections = ( content ) => {
			let _inflections = inflections,
				_content = content;
			_inflections.length && _inflections.sort( ( a, b ) => b.length - a.length );

			return tsfem_inpost.promiseLoop( _inflections, ( inflection ) => {
				let count = countWords( inflection, _content );
				inflectionCount += count;
				inflectionCharCount += inflection.length * count;
				_content = stripWord( inflection, _content );
			}, 5 );
		}
		const countSynonyms = ( content ) => {
			let _synonyms = synonyms,
				_content = content;

			_synonyms.length && _synonyms.sort( ( a, b ) => b.length - a.length );

			return tsfem_inpost.promiseLoop( _synonyms, ( synonym ) => {
				let count = countWords( synonym, _content );
				synonymCount += count;
				synonymCharCount += synonym.length * count;
				_content = stripWord( synonym, _content );
			}, 5 );
		}

		const checkElement = ( element ) => {
			let selector = document.querySelector( element ),
		 		$dfd = $.Deferred();

			content = typeof selector.value !== 'undefined' ? selector.value : '';
			// if ( ! content.length ) content = selector.placeholder;
			if ( ! content.length ) content = selector.innerHTML;

			if ( ! content.length ) return $dfd.resolve();

			contentCharCount += countChars( content );
			$.when(
				countInflections( content ),
				countSynonyms( content )
			).done( () => {
				$dfd.resolve();
			} );

			return $dfd.promise();
		}

		//= Calculate scores when done.
		const showScore = () => {
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

					// endScore = realScore;
					break;

				case 'p' :
					if ( contentCharCount ) {
						if ( inflectionCharCount )
							density += calcDensity( contentCharCount, calcSChars( scoring.keyword.weight, inflectionCharCount ) );
						if ( synonymCharCount )
							density += calcDensity( contentCharCount, calcSChars( scoring.synonym.weight, synonymCharCount ) );
					}

					realScore = calcRealDensityScore( scoring, density );
					// endScore = calcEndDensityScore( realScore, scoring.max, scoring.min, scoring.penalty );
					break;
			}

			//= Store realScore in input for saving.
			let input = document.querySelector( 'input[name="' + rater.id + '"]' );
			if ( input ) input.value = realScore;

			//= Gets description based on nearest threshold value.
			let description = rater.querySelector( '.tsfem-e-focus-assessment-description' ),
				newDescription = getNearestNumericIndexValue( data.phrasing, realScore );

			if ( description.innerHTML === newDescription ) {
				// Nothing changed.
				tsfem_inpost.setIconClass(
					rater.querySelector( '.tsfem-e-focus-assessment-rating' ),
					getIconType( data.rating, realScore )
				);
				description.style.opacity = '1';
				description.style.willChange = 'auto';
			} else {
				description.style.willChange = 'contents, opacity';
				description.style.opacity = '0';
				description.innerHTML = newDescription;
				tsfem_inpost.setIconClass(
					rater.querySelector( '.tsfem-e-focus-assessment-rating' ),
					getIconType( data.rating, realScore )
				);
				tsfem_inpost.fadeIn( description );
				description.style.willChange = 'auto';
			}
		}

		//= Run over element asynchronously and sequentially, when done, resolve function promise.
		$.when( tsfem_inpost.promiseLoop( activeFocusAreas[ data.assessment.content ], checkElement, 5 ) ).done( showScore );
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

	/**
	 * Returns icon type based on rating.
	 *
	 * @TODO find new source after cleanup:
	 * @source PHP TSF_Extension_Manager\Extension\Focus\Admin\Views\$_get_icon_class();
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {Object<number,string>} ratings
	 * @param {number} value
	 * @return {string} The icon class.
	 */
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

		return ( typeof classes[ index ] !== 'undefined' )
			&& classes[ index ]
			|| classes['-1'];
	}

	const runDefinitionSetter = ( idPrefix ) => {
		let keyword = getSubElementById( idPrefix, 'keyword' ).value,
			lexicalFormField = getSubElementById( idPrefix, 'lexical_form' );

		if ( ! lexicalFormField instanceof HTMLInputElement ) return;

		setEditButton( idPrefix ).to( 'loading' );

		const setLexicalFormSelectionFields = ( entries ) => {
			let lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' ),
				lexicalData = getSubElementById( idPrefix, 'lexical_data' );

			//= Get default form field, and append to it.
			let _forms = JSON.parse( l10n.defaultLexicalForm );

			//? IE11 replacement for Object.values( _forms ).
			_forms = Object.keys( _forms ).map( e => _forms[ e ] );

			if ( entries.length ) {
				entries.forEach( ( entry ) => {
					if ( entry.inflection && entry.category ) {
						_forms.push( {
							'value' : entry.inflection,
							'category' : entry.category,
							'name' : entry.category + ': ' + entry.inflection,
						} );
					}
				} );

				//= Start change listeners.
				setLexicalFormSelectionListener( idPrefix ).to( 'enabled' );
			}
			let formValues = _forms.length && JSON.stringify( _forms ) || l10n.defaultLexicalForm;

			lexicalFormField.value = '';
			if ( lexicalData instanceof HTMLInputElement ) {
				lexicalData.value = formValues;
			}
			if ( lexicalSelector instanceof HTMLSelectElement ) {
				updateLexicalSelector( idPrefix, formValues );
				lexicalSelector.disabled = false;
				lexicalSelector.selectedIndex = 0;
			}
		}
		const getLexicalForms = ( idPrefix, keyword ) => {
			let ops = {
				method: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					'action' : 'tsfem_e_focus_get_lexicalforms',
					'nonce' : l10n.nonce,
					'post_ID' : tsfem_inpost.postID,
					'args' : {
						'keyword': keyword,
						'language': 'en' // language || 'en', // TODO
					},
				},
				timeout: 10000,
				async: true,
			};

			let $dfd = $.Deferred();

			tsfem_inpost.doAjax(
				$dfd,
				ops,
				{ 'noticeArea': noticeArea, 'premium': true }
			);

			return $dfd.promise();
		}

		$.when( getLexicalForms( idPrefix, keyword ) ).done( ( data ) => {
			setLexicalFormSelectionFields( data.forms );
		} ).fail( () => {
			//= Redundant.
			clearData( idPrefix, 'lexical' );
		} ).always( () => {
			setEditButton( idPrefix ).to( 'edit' );
		} );
	}

	const clearData = ( idPrefix, what ) => {
		const clearLexical = () => {
			let lexicalFormField = getSubElementById( idPrefix, 'lexical_form' ),
				lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' ),
				lexicalData = getSubElementById( idPrefix, 'lexical_data' );

			if ( lexicalFormField instanceof HTMLInputElement ) {
				lexicalFormField.value = '';
			}
			if ( lexicalData instanceof HTMLInputElement ) {
				lexicalData.value = '';
			}
			if ( lexicalSelector instanceof HTMLSelectElement ) {
				lexicalSelector.disabled = true;
				lexicalSelector.selectedIndex = 0;
				lexicalSelector.dataset.prev = 0;
				updateLexicalSelector( idPrefix, l10n.defaultLexicalForm );
			}
			setEditButton( idPrefix ).to( 'unchecked' );

			//= Always calls clearDefinition!!
		}
		const clearDefinition = () => {
			let definitionSelection = getSubElementById( idPrefix, 'definition_selection' );
			if ( definitionSelection instanceof HTMLSelectElement ) {
				definitionSelection.selectedIndex = 0;
				definitionSelection.innerHTML = '';
				let definitionSelector = getSubElementById( idPrefix, 'definition_selector' );
				definitionSelector.style.display = 'none';
				definitionSelector.querySelector( '.tsfem-e-focus-definition-editor' ).innerHTML = '';
			}
			activeWords( idPrefix ).clearCache();
		}
		const clearInflections = () => {
			let inflectionSection = getSubElementById( idPrefix, 'inflections' ),
				inflectionEntries = inflectionSection && inflectionSection.querySelector( '.tsfem-e-focus-subject-selection' ),
				inflectionData = getSubElementById( idPrefix, 'inflection_data' ),
				activeInflections = getSubElementById( idPrefix, 'active_inflections' );

			if ( inflectionEntries instanceof Element ) {
				inflectionEntries.innerHTML = '';
			}
			if ( inflectionData instanceof HTMLInputElement ) {
				inflectionData.value = '';
			}
			if ( activeInflections instanceof HTMLInputElement ) {
				activeInflections.value = '';
			}
			//= Clear cache.
			activeWords( idPrefix ).clearCache( 'inflections' );
		}
		const clearSynonyms = () => {
			let synonymSection = getSubElementById( idPrefix, 'synonyms' ),
				synonymEntries = synonymSection && synonymSection.querySelector( '.tsfem-e-focus-subject-selection' ),
				synonymData = getSubElementById( idPrefix, 'synonym_data' ),
				activeSynonyms = getSubElementById( idPrefix, 'active_synonyms' );

			if ( synonymEntries instanceof Element ) {
				synonymEntries.innerHTML = '';
			}
			if ( synonymData instanceof HTMLInputElement ) {
				synonymData.value = '';
			}
			if ( activeSynonyms instanceof HTMLInputElement ) {
				activeSynonyms.value = '';
			}
			activeWords( idPrefix ).clearCache( 'inflections' );
			tsfem_inpost.isPremium && refillSubjectSelection( idPrefix );
		}
		const clearRatings = () => {
			getSubElementById( idPrefix, 'scores' )
				.querySelectorAll( 'input' ).forEach( el => el.value = 0 );
		}

		switch ( what ) {
			case 'lexical' :
				clearLexical();
				clearDefinition();
				break;
			case 'definition' :
				clearDefinition();
				break;
			case 'inflections' :
				clearInflections();
				break;
			case 'synonyms' :
				clearSynonyms();
				break;
			case 'ratings' :
				clearRatings();
				break;
			default :
				clearLexical();
				clearDefinition();
				clearInflections();
				clearSynonyms();
				clearRatings();
				break;
		}
	}

	const updateLexicalSelector = ( idPrefix, entries ) => {
		let lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' );

		//= Removes all options.
		for ( let _i = lexicalSelector.options.length; _i--; ) {
			lexicalSelector.remove( _i );
		}

		let _option = document.createElement( 'option' ),
			_list = JSON.parse( entries );

		if ( _list ) {
			for ( let i in _list ) {
				_option = _option.cloneNode();
				_option.value = tsfem_inpost.escapeStr( i );
				_option.innerHTML = tsfem_inpost.escapeStr( _list[ i ].name );
				lexicalSelector.appendChild( _option );
			}
		}
	}

	var lexicalFormSelectionBuffer = {};
	const lexicalSelectorChangeHandler = ( event ) => {
		let idPrefix = event.data.idPrefix,
			lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' ),
			lexicalFormField = getSubElementById( idPrefix, 'lexical_form' ),
			lexicalDataField = getSubElementById( idPrefix, 'lexical_data' );

		/**
		 * AJAX handler.
		 */
		const getSynonyms = ( idPrefix ) => {
			let forms = JSON.parse( lexicalDataField.value ),
				form = forms[ lexicalFormField.value ];

			let ops = {
				method: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					'action' : 'tsfem_e_focus_get_synonyms',
					'nonce' : l10n.nonce,
					'post_ID' : tsfem_inpost.postID,
					'args' : {
						'form': form,
						'language': 'en' // language || 'en', // TODO
					},
				},
				timeout: 15000,
				async: true,
			};

			let $dfd = $.Deferred();

			tsfem_inpost.doAjax(
				$dfd,
				ops,
				{ 'noticeArea': noticeArea, 'premium': true }
			);

			return $dfd.promise();
		}

		const setDefinition = ( value ) => {
			//= Set static lexicalform field.
			lexicalFormField.value = value;
			lexicalSelector.value = value;
			lexicalSelector.selectedIndex = +value;
		}
		const fetchInflections = ( idPrefix ) => {
			let inflectionHolder = getSubElementById( idPrefix, 'inflection_data' );

			//= We can't retrieve these yet. Set current value and base value.
			let keyword = getSubElementById( idPrefix, 'keyword' ).value.toLowerCase(),
				lexicalForm = lexicalFormField.value,
				lexicalData = lexicalDataField.value,
				lexicalWord = '',
				words = [];

			lexicalWord = JSON.parse( lexicalData )[ lexicalForm ].value.toLowerCase();
			words.push( keyword );
			if ( keyword !== lexicalWord ) words.push( lexicalWord );

			inflectionHolder.value = JSON.stringify( [ { 'inflections' : words } ] );
		}
		const fetchSynonyms = () => {
			let idPrefix = event.data.idPrefix;
			$.when( getSynonyms( idPrefix ) ).done( ( data ) => {
				let synonymHolder = getSubElementById( idPrefix, 'synonym_data' ),
					definitionSelection = getSubElementById( idPrefix, 'definition_selection' ),
					definitionSelector = getSubElementById( idPrefix, 'definition_selector' );

				event.target.dataset.prev = event.target.value;

				synonymHolder.value = JSON.stringify( data.synonyms );

				let definitionSelectionClone = definitionSelection.cloneNode( true );

				//= Removes all previous options.
				for ( let _i = definitionSelectionClone.options.length; _i--; ) {
					definitionSelectionClone.remove( _i );
				}
				let _option = document.createElement( 'option' );
				for ( let _i = 0; _i < data.synonyms.length; _i++ ) {
					_option = _option.cloneNode();
					_option.value = _i;
					_option.innerHTML = tsfem_inpost.escapeStr( data.synonyms[ _i ].example );
					definitionSelectionClone.appendChild( _option );
				}
				definitionSelection.innerHTML = definitionSelectionClone.innerHTML;
				definitionSelector.style.display = null;
				//= Make value visible.
				$( definitionSelector ).find( '.tsfem-e-focus-definition-editor' )
					.trigger( 'set-tsfem-e-focus-definition' );

				setEditButton( idPrefix ).to( 'enabled, edit, checked' );
			} ).fail( () => {
				setDefinition( lexicalSelector.dataset.prev || 0 );
				setEditButton( idPrefix ).to( 'edit' );
			} );
		}
		const run = () => {
			setEditButton( idPrefix ).to( 'loading' );
			clearTimeout( lexicalFormSelectionBuffer[ idPrefix ] );

			lexicalFormSelectionBuffer[ idPrefix ] = setTimeout( () => {
				if ( event.target.dataset.prev == event.target.value ) {
					setEditButton( idPrefix ).to( 'edit' );
				} else {
					setDefinition( event.target.value );
					if ( +event.target.value ) {
						//? Button changes at prepareSynonyms
						fetchSynonyms();
					} else {
						event.target.dataset.prev = event.target.value;
						setEditButton( idPrefix ).to( 'unchecked, disabled, edit' );
						clearData( idPrefix, 'definition' );
						clearData( idPrefix, 'synonyms' );
						clearData( idPrefix, 'inflections' );
					}
					fetchInflections( idPrefix );
				}
			}, 1500 );
		}
		run();
	}

	/**
	 * Enables lexical selection change handlers.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param {string} idPrefix
	 * @return {function} { to : {
	 *    @param {string} state
	 * } }
	 */
	const setLexicalFormSelectionListener = ( idPrefix ) => {
		const enable = ( selector ) => {
			$( selector )
				.off( 'change.tsfemInpostLexical' )
				.on( 'change.tsfemInpostLexical', { 'idPrefix': idPrefix }, lexicalSelectorChangeHandler );
		}
		const disable = ( selector ) => {
			$( selector ).off( 'change.tsfemInpostLexical' );
		}

		return {
			to: ( what ) => {
				let lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' );
				if ( ! lexicalSelector instanceof HTMLSelectElement ) return;
				switch ( what ) {
					case 'enabled' :
						enable( lexicalSelector );
						break;
					default :
					case 'disabled' :
						disable( lexicalSelector );
						break;
				}
			}
		}
	}

	/**
	 * Prepares all scores after keyword entry. Sets action listeners and performs
	 * first check. Asynchronously.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {string} idPrefix
	 */
	const prepareWrapScoreElements = ( idPrefix ) => {
		let scoresWrap = getSubElementById( idPrefix, 'scores' ),
			subScores = scoresWrap && scoresWrap.querySelectorAll( '.tsfem-e-focus-assessment-wrap' );

		if ( ! subScores || subScores !== Object( subScores ) ) {
			//= subScores isn't set.
			setEvaluationVisuals( idPrefix ).to( 'error' );
		} else {
			setEvaluationVisuals( idPrefix ).to( 'enable' );
			for ( let _i = subScores.length; _i--; ) {
				prepareScoreElement( subScores[ _i ] );
			}
			triggerAllAnalysis( idPrefix );
		}
	}

	const prepareScoreElement = ( el ) => {

		let idPrefix = getSubIdPrefix( el.id ),
			kw = getSubElementById( idPrefix, 'keyword' ).value;

		if ( ! kw ) return;

		let data, $el, rating, blind, input, reassess;

		rating = el.querySelector( '.tsfem-e-focus-assessment-rating' );
		tsfem_inpost.setIconClass( rating, 'loading' );
		blind = true;

		//= Determine if value must be made visible again, e.g. after DOM entry.
		//! @see updateActiveFocusAreas()
		reassess = typeof el.dataset.assess !== 'undefined' && ! +el.dataset.assess;

		//? jQuery unpacks JSON data.
		data = $( el ).data( 'scores' );


		if ( data && data.hasOwnProperty( 'assessment' ) ) {
			if ( typeof activeFocusAreas[ data.assessment.content ] !== 'undefined' ) {
				el.dataset.assess = 1;
				addToChangeListener( el, data.assessment.content );
				blind = false;
			}
		}
		if ( blind ) {
			//= Hide the element when it can't be parsed, for now.
			tsfem_inpost.setIconClass( rating, 'unknown' );
			el.dataset.assess = 0;

			input = document.getElementsByName( el.id );
			if ( input && input[0] ) input[0].value = 0;
			tsfem_inpost.fadeOut( el, 250 );
		} else if ( reassess ) {
			tsfem_inpost.fadeIn( el, 250 );
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
		clearData( idPrefix );
		setEditButton( idPrefix ).to( 'disabled, edit' );

		$( getSubElementById( idPrefix, 'scores' ) )
			.find( '.tsfem-e-focus-assessment-description' )
			.css( 'opacity', '0' );

		if ( ! val.length ) {
			setEvaluationVisuals( idPrefix ).to( 'disable' );
			setLexicalFormSelectionListener( idPrefix ).to( 'disabled' );
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
		let $content = $( getSubElementById( idPrefix, 'content' ) ),
			hideClasses = [
				'.tsfem-e-focus-scores-wrap',
				'.tsfem-e-focus-no-keyword-wrap',
				'.tsfem-e-focus-something-wrong-wrap'
			];

		const set = ( show ) => {
			hideClasses.splice( hideClasses.indexOf( show ), 1 );

			$content.find( hideClasses.join( ', ' ) ).fadeOut( 150, () => {
				//= Paint lag escape.
				setTimeout( () => {
					$content.find( show ).fadeIn( 250 );
				}, 150 );
			} );
		}

		return {
			to: ( state ) => {
				let show;
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
				set( show );
			}
		};
	}

	const setEditButton = ( idPrefix ) => {
		return { to: ( state ) => {
			let editToggle = getSubElementById( idPrefix, 'subject_edit' );
			if ( ! editToggle || ! editToggle instanceof HTMLInputElement ) return;

			let editLabel = document.querySelector( 'label[for="' + editToggle.id + '"]' ),
				editWrap = editToggle.parentNode,
				disabledClass = 'tsfem-e-focus-edit-subject-button-wrap-disabled';

			state.split( ',' ).forEach( ( _state ) => {
				switch ( _state.trim() ) {
					case 'checked' :
						if ( ! editToggle.checked ) {
							editToggle.checked = true;
							$( editToggle ).trigger( 'change' );
						}
						break;
					case 'unchecked' :
						if ( editToggle.checked ) {
							editToggle.checked = false;
							$( editToggle ).trigger( 'change' );
						}
						break;

					case 'loading' :
						editLabel && tsfem_inpost.setIconClass( editLabel, 'loading' );
						break;
					case 'edit' :
						editLabel && tsfem_inpost.setIconClass( editLabel, 'edit' );
						break;

					case 'enabled' :
						editToggle.disabled = false;
						//= Simulate toggle(*,false) IE11.
						editWrap.classList.add( disabledClass );
						editWrap.classList.remove( disabledClass );
						break;
					case 'disabled' :
						editToggle.disabled = true;
						//= Simulate toggle(*,true) IE11.
						editWrap.classList.remove( disabledClass );
						editWrap.classList.add( disabledClass );
						break;

					default:break;
				}
			} );
		} };
	}

	const resetCollapserListeners = () => {
		const toggleCollapser = ( event ) => {
			if ( tsfem_inpost.isActionableElement( event.target ) ) return;

			let $target = $( event.target ).closest( '.tsfem-e-focus-collapse-wrap' ),
				idPrefix = getSubIdPrefix( $target.attr( 'id' ) ),
				collapser = getSubElementById( idPrefix, 'collapser' );

			if ( collapser instanceof HTMLInputElement ) collapser.checked = ! collapser.checked;
		}

		//= Make the whole collapse bar a double-clickable expander/retractor.
		$( '.tsfem-e-focus-collapse-header' )
			.off( 'dblclick.tsfem-e-focus' )
			.on( 'dblclick.tsfem-e-focus', toggleCollapser );
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
				let idPrefix = getSubIdPrefix( event.target.id ),
					bar = getSubElementById( idPrefix, 'content' ).querySelector( '.tsfem-e-focus-content-loader-bar' ),
					collapser = getSubElementById( idPrefix, 'collapser' );

				if ( collapser.checked ) {
					collapser.checked = false;
					$( collapser ).trigger( 'change' );
				}

				clearInterval( barBuffer[ idPrefix ] );
				clearTimeout( keywordBuffer[ idPrefix ] );
				barStop( idPrefix, bar );

				barBuffer[ idPrefix ] = setInterval( () => barGo( idPrefix, bar ), barTimeout );

				keywordBuffer[ idPrefix ] = setTimeout( () => {
					clearInterval( barBuffer[ idPrefix ] );
					doKeywordEntry( event );
					barStop( idPrefix, bar );
				}, keywordTimeout );
			} );

		$keywordEntries.each( ( i, el ) => {
			if ( ! el.value.length ) {
				let idPrefix = getSubIdPrefix( el.id );
				clearData( idPrefix );
			}
		} );
	}

	const resetSubjectEditListeners = () => {

		/**
		 * Shows dropdown edit field and attaches listeners.
		 *
		 * @since 1.0.0
		 *
		 * @function
		 * @param {jQuery.event} event
		 * @return {boolean|undefined} False on error. Undefined otherwise.
		 */
		const editSubject = ( event ) => {
			let clicker = event.target,
				selectId = void 0,
				selector = void 0,
				lastVal = 0,
				lastText = '',
				newVal = 0,
				newText = '',
				setNow = event.data && event.data.change || false;

			if ( typeof clicker.dataset.for !== 'undefined' )
				selectId = clicker.dataset.for;

			if ( ! selectId )
				return false;

			selector = document.getElementById( selectId );

			if ( ! selector )
				return false;

			lastVal = selector.value;
			lastText = clicker.innerHTML;

			const doChange = () => {
				//= Show new option...
				clicker.innerHTML = newText;
				selector.value = newVal;

				refillSubjectSelection( getSubIdPrefix( selectId ) );
				reset();
			}
			const undoChanges = () => {
				clicker.innerHTML = lastText;
				selector.value = lastVal;
			}
			const showForm = () => {
				$( clicker ).hide();
				$( selector ).slideDown( 200 ).focus();
			}
			const showClicker = () => {
				$( selector ).blur().hide();
				$( clicker ).fadeIn( 300 );
			}
			const onChange = ( event ) => {
				let _target = event.target;
				setVals( event.target );
				newVal == lastVal && reset() || doChange();
			}
			const setVals = ( target ) => {
				newVal = target.value;
				newText = target.options[ target.selectedIndex ].text;
			}
			const clickOff = ( event ) => {
				let $select = $( event.target ).closest( selector );
				if ( $select.length < 1 ) {
					reset();
				}
			}
			const reset = () => {
				removeListeners();
				showClicker();
				return true;
			}
			const doNow = () => {
				selector.value = 0;
				selector.selectedIndex = 0;
				setVals( selector );
				doChange();
			}
			const addListeners = () => {
				selector.addEventListener( 'blur', reset );
				selector.addEventListener( 'change', onChange );
				//= Fallback:
				window.addEventListener( 'click', clickOff );

				setNow && doNow();
			}
			const removeListeners = () => {
				selector.removeEventListener( 'blur', reset );
				selector.removeEventListener( 'change', onChange );
				window.removeEventListener( 'click', clickOff );
			}
			showForm();
			//= Don't propagate current events to new listeners.
			setTimeout( addListeners, 10 );
		}
		const a11yEditSubject = ( event ) => {
			if ( 32 == event.which ) {
				event.preventDefault();
				editSubject( event );
			}
		}

		const showSubjectEditor = ( event ) => {
			let idPrefix = getSubIdPrefix( event.target.id ),
				editor = getSubElementById( idPrefix, 'edit' ),
				evaluator = getSubElementById( idPrefix, 'evaluate' ),
				label = document.querySelector( 'label[for="' + event.target.id + '"]' );

			let collapser = getSubElementById( idPrefix, 'collapser' );

			if ( collapser.checked ) {
				//= Collapsed. Act as if it's getting checked either way, without fancy trickery.
				event.target.checked = true;
				evaluator.style.display = 'none';
				evaluator.style.opacity = 0;
				editor.style.display = null;
				editor.style.opacity = 1;
				collapser.checked = false; // Expand collapser.
			} else {
				//= Toggle fancy.
				if ( event.target.checked ) {
					tsfem_inpost.fadeOut( evaluator, 150, {
						cb: () => {
							tsfem_inpost.fadeIn( editor, 250 );
							//? Because the promise is dropped, make sure it's set.
							event.target.checked = true;
						},
						promise: false,
					} );
				} else {
					tsfem_inpost.fadeOut( editor, 150, {
						cb: () => {
							tsfem_inpost.fadeIn( evaluator, 250 ),
							//? Because the promise is dropped, make sure it's unset.
							event.target.checked = false;
						},
						promise: false,
					} );
				}
			}
		}

		let subjectEditAction = 'click.tsfem-e-focus-definition-editor',
			a11ySubjectEditAction = 'keypress.tsfem-e-focus-definition-editor',
			customSubjectEditAction = 'set-tsfem-e-focus-definition';
		$( '.tsfem-e-focus-definition-editor' )
			.off( subjectEditAction )
			.on( subjectEditAction, editSubject )
			.off( a11ySubjectEditAction )
			.on( a11ySubjectEditAction, a11yEditSubject )
			.off( customSubjectEditAction )
			.on( customSubjectEditAction, { 'change': 1 }, editSubject );

		let subjectEditToggle = 'change.tsfem-e-focus-edit-subject-toggle';
		$( '.tsfem-e-focus-edit-subject-checkbox' )
			.off( subjectEditToggle )
			.on( subjectEditToggle, showSubjectEditor );
	}

	var subjectFilterBuffer = {};
	const resetSubjectFilterListeners = () => {

		const getIterationFromId = id => ( /\[([0-9]+)\]$/.exec( id ) || '' )[1];
		const setActiveInflections = ( idPrefix ) => {
			let inflectionSection = getSubElementById( idPrefix, 'inflections' ),
				selected = inflectionSection.querySelectorAll( 'input:checked' ),
				values = [];
			selected.forEach( el => {
				values.push( getIterationFromId( el.id ) );
			} );
			getSubElementById( idPrefix, 'active_inflections' ).value = values.join();
			activeWords( idPrefix ).clearCache( 'inflections' );
			triggerAllAnalysis( idPrefix );
		}
		const setActiveSynonyms = ( idPrefix ) => {
			let synonymSection = getSubElementById( idPrefix, 'synonyms' ),
				selected = synonymSection.querySelectorAll( 'input:checked' ),
				values = [];
			selected.forEach( el => {
				values.push( getIterationFromId( el.id ) );
			} );
			getSubElementById( idPrefix, 'active_synonyms' ).value = values.join();
			activeWords( idPrefix ).clearCache( 'synonyms' );
			triggerAllAnalysis( idPrefix );
		}
		const updateActive = ( event ) => {
			let idPrefix = event.data.idPrefix,
				type = event.data.type,
				bufferKey = idPrefix + type;

			clearTimeout( subjectFilterBuffer[ bufferKey ] );
			subjectFilterBuffer[ bufferKey ] = setTimeout( () => {
				switch ( type ) {
					case 'inflections' :
						setActiveInflections( idPrefix );
						break;
					case 'synonyms' :
						setActiveSynonyms( idPrefix );
						break;
				}
			}, 1500 );
		}
		const resetElementListeners = ( idPrefix, type ) => {
			$( getSubElementById( idPrefix, type ) ).find( 'input' )
				.off( 'change.tsfem-e-focus' )
				.on( 'change.tsfem-e-focus', { 'idPrefix': idPrefix, 'type': type }, updateActive );
		}

		const fillSubjectSelection = ( event, data ) => {
			if ( ! data || ! data.idPrefix ) return;

			const idPrefix = data.idPrefix;
			const
				inflectionHolder = getSubElementById( idPrefix, 'inflection_data' ),
				synonymHolder = getSubElementById( idPrefix, 'synonym_data' ),

				definitionSelector = getSubElementById( idPrefix, 'definition_selection' ),
				definitionEntry = definitionSelector.value,

				inflectionSection = getSubElementById( idPrefix, 'inflections' ),
				synonymSection = getSubElementById( idPrefix, 'synonyms' ),

				inflectionEntries = inflectionSection.querySelector( '.tsfem-e-focus-subject-selection' ),
				synonymEntries = synonymSection.querySelector( '.tsfem-e-focus-subject-selection' );
			const subjectTemplate = wp.template( 'tsfem-e-focus-subject-item' );

			//? There's always just one form of inflections in latin.
			let availableInflections = inflectionHolder.value && JSON.parse( inflectionHolder.value ),
				inflections = availableInflections && availableInflections[0].inflections;

			let availableSynonyms = synonymHolder.value && JSON.parse( synonymHolder.value ),
				synonyms = availableSynonyms && availableSynonyms[ definitionEntry ].synonyms;

			inflectionEntries.style.opacity = 0;
			synonymEntries.style.opacity = 0;

			let html = '',
				prefix = createSubId( idPrefix, 'inflection' );
			//= We need the keys... but they're sequential?
			for ( let i in inflections ) {
				html += subjectTemplate( {
					'id': createSubId( prefix, i ),
					'value': inflections[ i ],
					'disabled': !!+i ? '' : 'disabled' // Disable the first inflection.
				} );
			}
			inflectionEntries.innerHTML = html;

			html = '';
			prefix = createSubId( idPrefix, 'synonym' );
			//= We need the keys... but they're sequential?
			for ( let i in synonyms ) {
				html += subjectTemplate( {
					'id': createSubId( prefix, i ),
					'value': synonyms[ i ]
				} );
			}
			synonymEntries.innerHTML = html;

			tsfem_inpost.fadeIn( inflectionEntries );
			tsfem_inpost.fadeIn( synonymEntries );

			setActiveInflections( idPrefix );
			setActiveSynonyms( idPrefix );
			resetElementListeners( idPrefix, 'inflections' );
			resetElementListeners( idPrefix, 'synonyms' );
		}

		let updatedAction = 'tsfem-e-focus-updated-subject.tsfem-e-focus';
		$( window )
			.off( updatedAction )
			.on( updatedAction, fillSubjectSelection );
	}
	const refillSubjectSelection = ( idPrefix ) => {
		$( window ).trigger(
			'tsfem-e-focus-updated-subject',
			[ { 'idPrefix' : idPrefix } ]
		);
	}

	var cachedActiveWords = {}
	const activeWords = ( idPrefix ) => {
		if ( ! activeWords.hasOwnProperty( idPrefix ) ) {
			cachedActiveWords[ idPrefix ] = {};
			cachedActiveWords[ idPrefix ].inflections = null;
			cachedActiveWords[ idPrefix ].synonyms = null;
		}

		const getActiveInflections = () => {
			let ret, inflections,
				active = getSubElementById( idPrefix, 'active_inflections' );

			if ( active instanceof HTMLInputElement && active.value ) {
				let inflectionData = getSubElementById( idPrefix, 'inflection_data' ).value;
				inflections = JSON.parse( inflectionData )[0].inflections;

				ret = [];
				active.value.split( ',' ).forEach( i => {
					ret.push( inflections[ +i ] );
				} );
			}
			if ( ! ret ) {
				let kw = getSubElementById( idPrefix, 'keyword' ).value;
				ret = kw && [ kw ] || false;
			}
			ret = ret || false;
			cachedActiveWords[ idPrefix ].inflections = ret;

			return ret;
		}
		const geActiveSynonyms = () => {
			let ret, synonyms,
				active = getSubElementById( idPrefix, 'active_synonyms' ),
				selectedDefinition = getSubElementById( idPrefix, 'definition_selection' );

			if ( active instanceof HTMLInputElement && active.value ) {
				let synonymData = getSubElementById( idPrefix, 'synonym_data' ).value;
				synonyms = JSON.parse( synonymData )[ +selectedDefinition.value ].synonyms;

				ret = [];
				active.value.split( ',' ).forEach( i => {
					ret.push( synonyms[ +i ] );
				} );
			}
			if ( ! ret ) {
				let kw = getSubElementById( idPrefix, 'keyword' ).value;
				ret = kw && [ kw ] || false;
			}
			ret = ret || false;
			cachedActiveWords[ idPrefix ].synonyms = ret;

			return ret;
		}

		const getActive = ( what ) => {
			if ( null !== cachedActiveWords[ idPrefix ][ what ] )
				return cachedActiveWords[ idPrefix ][ what ];

			switch ( what ) {
				case 'inflections' :
					return getActiveInflections();
					break;
				case 'synonyms' :
					return geActiveSynonyms();
					break;

				default :
					return {
						'inflections': getActiveInflections(),
						'synonyms': geActiveSynonyms()
					};
					break;
			}
		}

		const clear = ( what ) => {
			if ( what ) {
				delete cachedActiveWords[ idPrefix ][ what ];
			} else {
				delete cachedActiveWords[ idPrefix ];
			}
		}

		return {
			get: what => getActive( what ),
			clearCache: what => clear( what )
		};
	}

	/**
	 * Changes icon class of all raters of assessment type.
	 *
	 * @since 1.0.0
	 * @access private
	 * @see tsfem_e_focus_inpost.setIconClass()
	 *
	 * @function
	 * @param {string} type
	 * @param {string} to
	 * @return {undefined}
	 */
	const setAllRatersOf = ( type, to ) => {
		if ( typeof activeAssessments[ type ] === 'undefined' ) return;

		to = to || 'unknown';

		for ( let i = activeAssessments[ type ].length; i--; ) {
			tsfem_inpost.setIconClass(
				document.getElementById( activeAssessments[ type ][ i ] ).querySelector( '.tsfem-e-focus-assessment-rating' ),
				to
			);
		};
	}

	/**
	 * Registers change listener for wrap.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @function
	 * @param {(HTMLInputElement|HTMLElement)} checkerWrap The scoring wrap to add checks for.
	 * @param {string} contentType The assessment content type to add the wrap to.
	 */
	const addToChangeListener = ( checkerWrap, contentType ) => {
		let assessments = activeAssessments;

		if ( ! assessments || assessments !== Object( assessments ) ) {
			assessments = [];
		}
		if ( ! assessments[ contentType ]
		|| assessments[ contentType ] !== Object( assessments[ contentType ] ) ) {
			assessments[ contentType ] = [];
		}
		if ( checkerWrap.id ) {
			if ( typeof activeFocusAreas[ contentType ] !== 'undefined' ) {
				if ( assessments[ contentType ].indexOf( checkerWrap.id ) < 0 )
					assessments[ contentType ].push( checkerWrap.id );
			} else {
				delete assessments[ contentType ][ checkerWrap.id ];
			}
		}

		activeAssessments = assessments;
	}

	var changeListenersBuffers = {}, changeListenerFlags = {};
	/**
	 * Resets change listeners for analysis on the available content elements.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {string|undefined} type The type to reset. Default undefined (catch-all).
	 */
	const resetAnalysisChangeListeners = ( type ) => {
		/**
		 * One of two main function that runs the checks on registered change events.
		 */
		const triggerChangeListener = ( type ) => {
			tsfem_inpost.promiseLoop( activeAssessments[ type ], ( id ) => {
				let idPrefix = getSubIdPrefix( id ),
					kwInput = getSubElementById( idPrefix, 'keyword' ),
					kw = kwInput.value || '';

				if ( ! kw.length ) return; // continue

				let el = document.getElementById( id ),
					rater = el.querySelector( '.tsfem-e-focus-assessment-rating' );
				tsfem_inpost.setIconClass( rater, 'loading' );
				doCheck( el );
			} );
		}
		const listener = ( event ) => {
			if ( typeof activeAssessments[ event.data.type ] === 'undefined' ) return;

			// let key = event.target.id || event.target.name || event.target.classList.join('');
			clearTimeout( changeListenersBuffers[ event.data.type ] );

			//= Show the world it's unknown, maintaining a caching flag.
			//! Don't revert this flag here when this event is done. The state remains unknown on failure!
			if ( ! changeListenerFlags[ event.data.type ] ) {
				setTimeout( () => tsfem_inpost.setIconClass(
					document.querySelectorAll(
						'[data-assessment-type="' + event.data.type + '"] .tsfem-e-focus-assessment-rating'
					), 'unknown'
				), 0 );
				changeListenerFlags[ event.data.type ] = true;
			}

			changeListenersBuffers[ event.data.type ] = setTimeout( () => {
				triggerChangeListener( event.data.type );
				changeListenerFlags[ event.data.type ] = false;
			}, 1500 );
		}
		const reset = ( type ) => {
			let changeEventName = analysisChangeEvents( type ).get( 'names' );
			$( activeFocusAreas[ type ].join( ', ' ) )
				.off( changeEventName )
				.on( changeEventName, { 'type' : type }, listener );
		}
		const unset = ( type ) => {
			let changeEventName = analysisChangeEvents( type ).get( 'names' );
			$( activeFocusAreas[ type ].join( ', ' ) ).off( changeEventName );
		}

		if ( type ) {
			if ( typeof activeFocusAreas[ type ] === 'undefined' ) {
				unset( type );
			} else {
				reset( type );
			}
		} else {
			let _type;
			for ( _type in activeFocusAreas ) {
				reset( _type );
			}
		}
	}

	var triggerAllBuffer = {};
	/**
	 * Triggers all analysis.
	 *
	 * Another buffer is maintained at trigger level.
	 * However, this loop can be heavy.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(string|undefined)} idPrefix The idPrefix. If omitted, all checks are performed.
	 */
	const triggerAllAnalysis = ( idPrefix ) => {
		idPrefix = idPrefix || 0;
		clearTimeout( triggerAllBuffer[ idPrefix ] );
		triggerAllBuffer[ idPrefix ] = setTimeout( () => {
			if ( idPrefix ) {
				let scoresWrap = getSubElementById( idPrefix, 'scores' ),
					subScores = scoresWrap && scoresWrap.querySelectorAll( '.tsfem-e-focus-assessment-wrap[data-assess="1"]' );
				if ( subScores instanceof NodeList ) {
					for ( let _i = subScores.length; _i--; ) {
						tsfem_inpost.setIconClass( subScores[ _i ].querySelector( '.tsfem-e-focus-assessment-rating' ), 'loading' );
						//= defer.
						setTimeout( () => doCheck( subScores[ _i ] ), 0 );
					}
				}
			} else {
				let _type;
				for ( _type in activeFocusAreas ) {
					$( activeFocusAreas[ _type ].join( ', ' ) )
						.trigger( analysisChangeEvents( _type ).get( 'trigger' ) );
				}
			}
		}, 1000 );
	}

	/**
	 * Returns all analysis jQuery event types from type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param {string} type The content type.
	 * @return {object<string,?>)}
	 */
	const analysisChangeEvents = ( type ) => {
		const events = [
			'tsfem-e-focus.tsfem-e-focus-' + type, // Custom trigger.
			'input.tsfem-e-focus-' + type,
			'change.tsfem-e-focus-' + type
		];
		return { get: ( what ) => {
			switch ( what ) {
				case 'names'   : return events.join( ' ' );
				case 'trigger' : return events[0];
				default : return events;
			}
		} };
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
		// const interval = 3000;

		/**
		 * Observes page URL changes.
		 * @see [...]\wp-admin\post.js:editPermalink()
		 */
		(()=>{
			if ( typeof l10n.focusElements.pageUrl === 'undefined' ) return;

			let listenNode = document.getElementById( 'edit-slug-box' );
			if ( ! listenNode || ! listenNode instanceof HTMLElement ) {
				return;
			}

			const updatePageUrlRegistry = () => {
				let unregisteredUrlAssessments = document.querySelectorAll(
					'.tsfem-e-focus-assessment-wrap[data-assessment-type="url"][data-assess="0"]'
				);
				if ( unregisteredUrlAssessments instanceof NodeList && unregisteredUrlAssessments.length ) {
					updateActiveFocusAreas( 'pageUrl' );
					resetAnalysisChangeListeners( 'pageUrl' );
					unregisteredUrlAssessments.forEach( el => prepareScoreElement( el ) );
				}
			}

			new MutationObserver( mutationsList => {
				updatePageUrlRegistry();
				$( '#sample-permalink' ).trigger( analysisChangeEvents( 'pageUrl' ).get( 'trigger' ) );
			} ).observe( listenNode, { 'childList': true, 'subtree' : true } );
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
					buffering = false,
					setUnknown = false;

				editor.on( 'Dirty', ( event ) => {
					if ( ! setUnknown ) {
						setAllRatersOf( 'pageContent', 'unknown' );
						setUnknown = true;
					}
				} );
				editor.on( 'GetContent', ( event ) => {
					clearTimeout( buffers['GetContent'] );
					if ( ! buffering && editor.isDirty() ) {
						setAllRatersOf( 'pageContent', 'loading' );
						buffering = true;
						setUnknown = false;
					}
					buffers['GetContent'] = setTimeout( () => {
						editor.isDirty() || $( '#content' ).trigger( analysisChangeEvents( 'pageContent' ).get( 'trigger' ) );
						buffering = false;
						setUnknown = false;
					}, 1000 );
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

		//= There's nothing to focus on.  Stop plugin and show why.
		//?! The monkeyPatch is still running...
		if ( 0 === Object.keys( activeFocusAreas ).length ) {
			let el = document.getElementById( 'tsfem-e-focus-analysis-wrap' ).querySelector( '.tsf-flex-setting-input' );
			if ( el instanceof Element )
				el.innerHTML = wp.template( 'tsfem-e-focus-nofocus' )();
			return;
		}

		resetCollapserListeners();
		resetKeywordEntryListeners();

		if ( tsfem_inpost.isPremium ) {
			resetSubjectEditListeners();
			resetSubjectFilterListeners();
		}
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
		triggerAllAnalysis,
		analysisChangeEvents,
		getSubIdPrefix,
		getSubElementById,
	} );
}( jQuery );
//= Run before jQuery.ready() === DOMContentLoaded
jQuery( window.tsfem_e_focus_inpost.load );
