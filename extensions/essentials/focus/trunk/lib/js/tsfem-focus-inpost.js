/**
 * This file holds Focus' code for interpreting keywords and their subjects.
 * Serve JavaScript as an addition, not as an ends or means.
 * Alas, there's no other way here.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */


/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

	/**
	 * @since 1.0.0
	 * @access private
	 * @const l10n The l10n parameters set in PHP to var.
	 */
	const l10n = tsfem_e_focusInpostL10n;

	/**
	 * @since 1.0.0
	 * @access private
	 * @const noticeArea The notice area ID to place notices in.
	 */
	const noticeArea = '#tsfem-e-focus-analysis-notification-area';

	/**
	 * @since 1.0.0
	 * @access private
	 * @var focusRegistry     Maintains all plausible focus elements' registry.
	 * @var activeFocusAreas  Maintains all active focus elements' selectors.
	 * @var activeAssessments Maintains all active focus elements' bound raters.
	 */
	let focusRegistry = {},
		activeFocusAreas = {},
		activeAssessments = {};

	/**
	 * Gets string until last numeric ID index.
	 * In this application, no multidimensional IDs should be in place, aside from
	 * the synonym/inflection selections.
	 *
	 * in:  `tsfem-pm[focus][kw][42][something]`
	 * out: `tsfem-pm[focus][kw][42]`
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {string} id The element's ID.
	 * @return {string} The Element's sub ID, or an empty string.
	 */
	const getSubIdPrefix = ( id ) => {
		return ( /.*\[[0-9]+\]/.exec( id ) || '' )[0];
	}

	/**
	 * Creates sub element ID.
	 *
	 * @since 1.0.0
	 * @access private
	 * @see getSubIdPrefix() To get the prefix.
	 * @see getSubElementById() To create the type.
	 *
	 * @function
	 * @param {string} prefix The element's prefix.
	 * @param {string} type   The element's type.
	 */
	const createSubId = ( idPrefix, name ) => {
		return idPrefix + '[' + name + ']';
	}

	/**
	 * Returns an element based on prefix and type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @see getSubIdPrefix() To get the prefix.
	 * @see createSubId() To create the type.
	 *
	 * @function
	 * @param {string} idPrefix The element's prefix.
	 * @param {string} type     The element's type.
	 * @return {HTMLElement}
	 */
	const getSubElementById = ( idPrefix, type ) => {
		return document.getElementById( idPrefix + '[' + type + ']' );
	}

	/**
	 * Sorts map.
	 *
	 * Objects are automatically sorted in Chrome and IE.
	 * This is NOT according to spec. So, use this anyway.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {(Object<*,*>|Array|Map)} obj The object that needs to be sorted.
	 * @return {(Object)} The sorted map.
	 */
	const sortMap = ( obj ) => {
		Object.keys( obj ).sort( ( a, b ) => {
			return Object.keys( a )[0] - Object.keys( b )[0];
		} );

		return obj;
	}

	/**
	 * Updates focus registry selectors.
	 *
	 * When adding objects dynamically, it's best to always use the append type.
	 *
	 * @since 1.0.0
	 * @see updateActiveFocusAreas() To be called after the
	 *      registry has been updated.
	 * @uses @var focusRegistry
	 * @access private
	 *
	 * @function
	 * @param {!Object<string,object>} elements : {
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
					let values = Object.values( registry[ area ][ type ] );
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
	 * @uses @var focusRegistry
	 * @uses @var activeFocusAreas
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

	let _debouncedChecks = {};
	/**
	 * Performs check based on rater element's data, attached keyword, inflections
	 * and synonyms, based on the registry's elements.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {HTMLElement} rater The score element wrap.
	 */
	const doCheck = ( rater ) => {

		let idPrefix   = getSubIdPrefix( rater.id ),
			workerId   = 'e_focus_pw_' + $( rater ).data( 'assessment-type' ) + '_' + idPrefix,
			ratingIcon = rater.querySelector( '.tsfem-e-focus-assessment-rating' );

		if ( tsfem_inpost.isWorkerBusy( workerId ) ) {
			// Debounce if worker is busy.
			workerId in _debouncedChecks && clearTimeout( _debouncedChecks[ workerId ] );
			_debouncedChecks[ workerId ] = setTimeout( () => doCheck( rater ), 2000 );
			return;
		}
		delete _debouncedChecks[ workerId ];
		tsfem_inpost.occupyWorker( workerId );
		tsfem_inpost.setIconClass( ratingIcon, 'loading' );

		/**
		 * @param {(boolean|object<number,string>|array)} inflections
		 * @param {(boolean|object<number,string>|array)} synonyms
		 */
		let inflections = activeWords( idPrefix ).get( 'inflections' ),
			synonyms    = activeWords( idPrefix ).get( 'synonyms' );

		let data                = $( rater ).data( 'scores' ),
			inflectionCount     = 0,
			synonymCount        = 0,
			inflectionCharCount = 0,
			synonymCharCount    = 0,
			contentCharCount    = 0,
			content,
			regex               = data.assessment.regex;

		//= Convert regex to object if it isn't already.
		if ( regex !== Object( regex ) ) {
			regex = [ regex ];
		}

		const checkElement = ( element ) => {
			let selector = document.querySelector( element ),
				$dfd     = $.Deferred();

			content = typeof selector.value !== 'undefined' ? selector.value : '';
			// if ( ! content.length ) content = selector.placeholder;
			if ( ! content.length )
				content = selector.innerHTML;
			if ( ! content.length ) return $dfd.resolve();

			setTimeout( async () => {

				await (
					tsfem_inpost.getWorker( workerId )
					|| tsfem_inpost.spawnWorker( l10n.scripts.parserWorker, workerId )
				);

				$.when(
					tsfem_inpost.tellWorker( workerId, {
						regex:       regex,
						inflections: inflections,
						synonyms:    synonyms,
						content:     content,
						assess:      {
							getCharCount: 'p' === data.scoring.type, // p stands for "percent", which is relative.
						},
					} ),
				).done( ( data ) => {
					inflectionCount     = data.inflectionCount;
					synonymCount        = data.synonymCount;
					inflectionCharCount = data.inflectionCharCount;
					synonymCharCount    = data.synonymCharCount;
					contentCharCount    = data.contentCharCount;

					$dfd.resolve();
				} ).fail( ( message ) => { // NOTE: Parameters aren't set as intended?
					$dfd.reject();
				} );
			} );

			return $dfd.promise();
		}

		//= Calculate scores when done.
		const showScore = () => {
			let scoring = data.scoring,
				// maxScore = data.maxScore,
				density = 0,
				realScore = 0;
				// endScore = 0;

			/**
			 * Returns the value if not higher than max. Otherwise it returns max.
			 */
			const getMaxIfOver = ( max, value ) => value > max ? max : value;

			const calcScoreN = ( scoring, value ) => Math.floor( value / scoring.per ) * scoring.score;
			const calcSChars = ( weight, value ) => value * ( weight / 100 );
			/**
			 * @param {int} charCount Character count in text.
			 * @param {int} sChars Simulated chars through weight.
			 */
			const calcDensity = ( charCount, sChars ) => ( sChars / charCount ) * 100;
			const calcRealDensityScore = ( scoring, density ) => density / scoring.threshold * data.maxScore;
			// const calcEndDensityScore = ( score, max, min, penalty ) => getMaxIfOver( max, Math.max( min, max - ( score - max ) * penalty ) );

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
				//* Nothing changed.
				tsfem_inpost.setIconClass(
					ratingIcon,
					getIconType( data.rating, realScore )
				);
				description.style.opacity = '1';
				description.style.willChange = 'auto';
			} else {
				description.style.willChange = 'contents, opacity';
				description.style.opacity = '0';
				description.innerHTML = newDescription;
				tsfem_inpost.setIconClass(
					ratingIcon,
					getIconType( data.rating, realScore )
				);
				tsfem_inpost.fadeIn( description );
				description.style.willChange = 'auto';
			}
		}

		const showFailure = () => {
			//= Store realScore in input for saving (which is 0).
			let input = document.querySelector( 'input[name="' + rater.id + '"]' );
			if ( input ) input.value = 0;

			//= Gets description based on nearest threshold value.
			let description = rater.querySelector( '.tsfem-e-focus-assessment-description' ),
				newDescription = l10n.i18n.parseFailure;

			if ( description.innerHTML === newDescription ) {
				//* Nothing changed.
				tsfem_inpost.setIconClass(
					ratingIcon,
					getIconType( -1, -1 )
				);
				description.style.opacity = '1';
				description.style.willChange = 'auto';
			} else {
				description.style.willChange = 'contents, opacity';
				description.style.opacity = '0';
				description.innerHTML = newDescription;
				tsfem_inpost.setIconClass(
					ratingIcon,
					getIconType( -1, -1 )
				);
				tsfem_inpost.fadeIn( description );
				description.style.willChange = 'auto';
			}
		}

		//= Run over element asynchronously and sequentially, when done, resolve function promise.
		$.when( tsfem_inpost.promiseLoop(
			activeFocusAreas[ data.assessment.content ],
			checkElement,
			5,
			30000 // fail at 30s
		) )
		.done( () => {
			showScore();
			tsfem_inpost.freeWorker( workerId );
		} )
		.fail( () => {
			// Worker likely got stuck. Let's despawn it.
			showFailure();
			tsfem_inpost.despawnWorker( workerId );
		} );
	}

	/**
	 * Finds the nearest index value of the array.
	 *
	 * When the value isn't found, it returns the first index value.
	 * @source PHP TSF_Extension_Manager\Extension\Focus\Scoring\get_nearest_numeric_index_value();
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {(map|array|Object<number,?>)} $a The map with values. : {
	 *   int index => mixed value
	 * }
	 * @param {number} $value The value to find nearest index of.
	 * @return {?} The nearest index value.
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

		return ret ? ret : ( obj[ Object.keys( obj )[0] ] || '' );
	}

	/**
	 * Returns icon type based on rating.
	 *
	 * @source PHP TSF_Extension_Manager\Extension\Focus\Scoring\get_icon_class();
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

	/**
	 * Runs AJAX to obtain lexical selection fields.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {string} idPrefix
	 */
	const runLexicalFormSetter = ( idPrefix ) => {
		let keyword          = getSubElementById( idPrefix, 'keyword' ).value,
			lexicalFormField = getSubElementById( idPrefix, 'lexical_form' );

		if ( ! lexicalFormField instanceof HTMLInputElement ) return;

		setEditButton( idPrefix ).to( 'loading' );

		const setLexicalFormSelectionFields = ( entries ) => {
			let lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' ),
				lexicalData = getSubElementById( idPrefix, 'lexical_data' );

			//= Get default form field, and append to it.
			let _forms = Object.values( JSON.parse( l10n.defaultLexicalForm ) );

			if ( entries.length ) {
				entries.forEach( ( entry ) => {
					if ( entry.inflection && entry.category ) {
						_forms.push( {
							value:    entry.inflection,
							category: entry.category,
							name:     entry.category + ': ' + entry.inflection,
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
				method:   'POST',
				url:      ajaxurl,
				dataType: 'json',
				data: {
					action: 'tsfem_e_focus_get_lexicalforms',
					nonce:   l10n.nonce,
					post_ID: tsfem_inpost.postID,
					args:    {
						keyword,
						language: l10n.language,
					},
				},
				timeout:  15000,
				async:    true,
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

	/**
	 * Clears data of all fields corresponding to the "what" parameter.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 clearLexical now sets lexicalData.value to the default.
	 * @access private
	 *
	 * @function
	 * @param {string} idPrefix The idPrefix to clear.
	 * @param {string} what     What to clear.
	 */
	const clearData = ( idPrefix, what ) => {
		const clearLexical = () => {
			let lexicalFormField = getSubElementById( idPrefix, 'lexical_form' ),
				lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' ),
				lexicalData = getSubElementById( idPrefix, 'lexical_data' );

			if ( lexicalFormField instanceof HTMLInputElement ) {
				lexicalFormField.value = '';
			}
			if ( lexicalData instanceof HTMLInputElement ) {
				lexicalData.value = l10n.defaultLexicalForm;
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
			let definitionDropdown  = getSubElementById( idPrefix, 'definition_dropdown' ),
				definitionSelection = getSubElementById( idPrefix, 'definition_selection' );
			if ( definitionDropdown instanceof HTMLSelectElement ) {
				definitionDropdown.selectedIndex = 0;
				definitionDropdown.innerHTML = '';
				document.querySelector( '[data-for="' + definitionDropdown.id + '"]' ).innerHTML = '';
			}
			if ( definitionSelection instanceof HTMLInputElement ) {
				definitionSelection.value = '';
			}
			activeWords( idPrefix ).clearCache();
		}
		const clearInflections = () => {
			let inflectionSection = getSubElementById( idPrefix, 'inflections' ),
				inflectionEntries = inflectionSection && inflectionSection.querySelector( '.tsfem-e-focus-subject-selection' ),
				inflectionData    = getSubElementById( idPrefix, 'inflection_data' ),
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
				synonymData    = getSubElementById( idPrefix, 'synonym_data' ),
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
			activeWords( idPrefix ).clearCache( 'synonyms' );
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

	/**
	 * Updates lexical selector values.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {string} idPrefix
	 * @param {Object<number,*>|Array} entries
	 */
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

	let lexicalFormSelectionBuffer = {};
	/**
	 * Performs change actions on lexical selection update.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Converted inflection setter to getter.
	 * @access private
	 * @uses @var lexicalFormSelectionBuffer
	 *
	 * @function
	 * @param {!jQuery.event} event The jQuery event, containing the idPrefix.
	 *                              === idPrefix['lexical_selector']
	 */
	const lexicalSelectorChangeHandler = ( event ) => {
		let idPrefix         = event.data.idPrefix,
			lexicalSelector  = getSubElementById( idPrefix, 'lexical_selector' ),
			lexicalFormField = getSubElementById( idPrefix, 'lexical_form' ),
			lexicalDataField = getSubElementById( idPrefix, 'lexical_data' );

		const apiHandler = ( type ) => {
			let action,
				$dfd = $.Deferred();

			if ( 'inflections' === type ) {
				action = 'tsfem_e_focus_get_inflections';
			} else if ( 'synonyms' === type ) {
				action = 'tsfem_e_focus_get_synonyms';
			}

			if ( ! action ) {
				$dfd.reject();
				return $dfd.promise();
			}

			let ops = {
				method: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action,
					nonce: l10n.nonce,
					post_ID: tsfem_inpost.postID,
					args: {
						form: JSON.parse( lexicalDataField.value )[ lexicalFormField.value ],
						language: l10n.language,
					},
				},
				timeout: 15000,
				async: true,
			};

			tsfem_inpost.doAjax(
				$dfd,
				ops,
				{
					noticeArea,
					premium: true
				}
			);

			return $dfd.promise();
		}
		const getInflections = () => apiHandler( 'inflections' );
		const getSynonyms    = () => apiHandler( 'synonyms' );

		const setDefinition = ( value ) => {
			//= Set static lexicalform field.
			lexicalFormField.value        = value;
			lexicalSelector.value         = value;
			lexicalSelector.selectedIndex = +value;
		}
		const fetchInflections = () => {
			let $dfd = $.Deferred();

			if ( ! l10n.languageSupported.inflections ) {
				$dfd.reject();
				return $dfd.promise();
			}

			$.when( getInflections() ).done( ( data ) => {
				if ( data.inflections.length ) {
					clearData( idPrefix, 'inflections' );
					getSubElementById( idPrefix, 'inflection_data' ).value = JSON.stringify( [ { inflections: data.inflections } ] );
					$dfd.resolve();
				} else {
					$dfd.reject();
				}
			} ).fail( ( code ) => {
				switch ( code ) {
					case 1100302: // Word not found
					case 1100306: // Empty return value

					case 1100303: // Request limit reached
					case 1100304: // Remote API error
					case 1100305: // Empty response.

					case 1100308: // Sybre messed up and forgot to code in inflection response.
						// Ignore.
						break;

					default:
						// Don't forge the request, tyvm.
						$dfd.reject();
						return;
				}

				//= Let's try populate this anyway, with currently known data (input + lexical form).
				let keyword     = getSubElementById( idPrefix, 'keyword' ).value.toLowerCase(),
					lexicalForm = lexicalFormField.value,
					lexicalData = lexicalDataField.value,
					lexicalWord = '',
					words       = [];

				// get Lexical form, push with keyword when the words don't match.
				lexicalWord = JSON.parse( lexicalData )[ lexicalForm ].value.toLowerCase();
				words.push( keyword );
				if ( keyword !== lexicalWord ) {
					words.push( lexicalWord );
				} else {
					// No new word to work with. Reject.
					$dfd.reject();
					return;
				}

				clearData( idPrefix, 'inflections' );
				getSubElementById( idPrefix, 'inflection_data' ).value = JSON.stringify( [ { inflections: words } ] );

				// This is OK; we already have some form of inflection--hopefully.
				$dfd.resolve();
			} );

			return $dfd.promise();
		}
		const fetchSynonyms = () => {
			let $dfd = $.Deferred();

			if ( ! l10n.languageSupported.synonyms ) {
				$dfd.reject();
				return $dfd.promise();
			}

			$.when( getSynonyms() ).done( ( data ) => {
				if ( data.synonyms.length ) {
					clearData( idPrefix, 'definition' );
					clearData( idPrefix, 'synonyms' );
					getSubElementById( idPrefix, 'synonym_data' ).value = JSON.stringify( data.synonyms );
					$dfd.resolve();
				} else {
					$dfd.reject();
				}
			} ).fail( ( code ) => {
				switch ( code ) {
					case 1100202: // Word not found
					case 1100205: // Empty return value
						clearData( idPrefix, 'definition' );
						clearData( idPrefix, 'synonyms' );
						$dfd.reject();
						break;

					default:
						$dfd.reject();
						break;
				}
			} );
			return $dfd.promise();
		}
		const run = () => {
			setEditButton( idPrefix ).to( 'loading' );
			clearTimeout( lexicalFormSelectionBuffer[ idPrefix ] );

			lexicalFormSelectionBuffer[ idPrefix ] = setTimeout( () => {
				if ( lexicalSelector.dataset.prev == lexicalSelector.value ) {
					setEditButton( idPrefix ).to( 'edit' );
				} else {
					setDefinition( lexicalSelector.value );
					if ( +lexicalSelector.value ) {
						let $dfdInflection = void 0,
							$dfdSynonym    = void 0;

						let $dfdInflectionEnd = $.Deferred(),
							$dfdSynonymEnd    = $.Deferred();

						// FIXME: The user now gets spammed twice (or thrice) with notifications; find a way to combine them?
						// We should either combine the query (and offload it to PHP), or resolve it by comparing the notices here.

						// Workaround jQuery's promise limitations by offsetting the deferred object.
						$.when( $dfdInflection = fetchInflections() ).always( () => $dfdInflectionEnd.resolve() );
						// Don't bombard the user's server, wait a little.
						setTimeout( () => {
							$.when( $dfdSynonym = fetchSynonyms() ).always( () => $dfdSynonymEnd.resolve() );
						}, 100 );

						//? Button changes at prepareSynonyms
						$.when( $dfdInflectionEnd, $dfdSynonymEnd ).done( () => {
							// jQuery's deferred isn't as neat as ECMA's, let's work around that.
							if ( [ $dfdInflection.state(), $dfdSynonym.state() ].includes( 'resolved' ) ) {

								lexicalSelector.dataset.prev = lexicalSelector.value;

								if ( $dfdInflection.state() !== 'resolved' ) {
									clearData( idPrefix, 'inflections' );
								} else if ( $dfdSynonym.state() !== 'resolved' ) {
									clearData( idPrefix, 'definition' );
									clearData( idPrefix, 'synonyms' );
								}

								populateDefinitionSelector( idPrefix );
								refillSubjectSelection( idPrefix );
								setEditButton( idPrefix ).to( 'enabled, edit, checked' );
							} else {
								// FIXME: Notify user the query got reset to previous?... if dataset.prev is not 0?
								setDefinition( lexicalSelector.dataset.prev || 0 );
								setEditButton( idPrefix ).to( 'edit' );
							}
						} );
					} else {
						lexicalSelector.dataset.prev = lexicalSelector.value;
						setEditButton( idPrefix ).to( 'unchecked, disabled, edit' );
						clearData( idPrefix, 'definition' );
						clearData( idPrefix, 'inflections' );
						clearData( idPrefix, 'synonyms' );
					}
				}
			}, 1500 );
		}
		run();
	}

	/**
	 * Populates definiton selection dynamic dropdown selection field.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 No longer populates when no synonyms are present.
	 * @access private
	 *
	 * @function
	 * @param {string} idPrefix
	 */
	const populateDefinitionSelector = ( idPrefix ) => {
		let synonymHolder = getSubElementById( idPrefix, 'synonym_data' ),
			synonyms      = synonymHolder.value && JSON.parse( synonymHolder.value ) || {};

		let definitionDropdown      = getSubElementById( idPrefix, 'definition_dropdown' ),
			definitionSelection     = getSubElementById( idPrefix, 'definition_selection' ),
			definitionDropdownClone = definitionDropdown.cloneNode( true );

		//= Removes all previous options.
		for ( let _i = definitionDropdownClone.options.length; _i--; ) {
			definitionDropdownClone.remove( _i );
		}
		let _option = document.createElement( 'option' );
		for ( let _i = 0; _i < synonyms.length; _i++ ) {
			_option = _option.cloneNode();
			_option.value = _i;
			_option.innerHTML = tsfem_inpost.escapeStr( synonyms[ _i ].example || l10n.i18n.noExampleAvailable );
			definitionDropdownClone.appendChild( _option );
		}
		definitionDropdown.innerHTML = definitionDropdownClone.innerHTML;
		definitionDropdown.value = definitionSelection.value;
		definitionDropdown.selectedIndex = +definitionSelection.value;

		let definitionSelector = getSubElementById( idPrefix, 'definition_selector' );
		if ( definitionDropdown.options.length ) {
			//= Make selected index show up.
			$( document.querySelector( '[data-for="' + definitionDropdown.id + '"]' ) )
				.trigger( 'set-tsfem-e-focus-definition' );
			definitionSelector.style.display = null;
		} else {
			//= Hide definition selector if there's nothing to be selected.
			definitionSelector.style.display = 'none';
		}
	}

	/**
	 * Enables lexical selection change handlers.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
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
					case 'enabled':
						enable( lexicalSelector );
						break;
					default:
					case 'disabled':
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
	 * @access private
	 *
	 * @function
	 * @param {string} idPrefix
	 */
	const prepareWrapScoreElements = ( idPrefix ) => {
		let scoresWrap = getSubElementById( idPrefix, 'scores' ),
			subScores = scoresWrap && scoresWrap.querySelectorAll( '.tsfem-e-focus-assessment-wrap' );

		setAnalysisInterval( idPrefix ).to( 'disabled' );

		if ( ! subScores || subScores !== Object( subScores ) ) {
			//= subScores isn't set.
			setEvaluationVisuals( idPrefix ).to( 'error' );
		} else {
			setEvaluationVisuals( idPrefix ).to( 'enabled' );
			for ( let _i = subScores.length; _i--; ) {
				prepareScoreElement( subScores[ _i ] );
			}
			triggerAllAnalysis( idPrefix );
			setAnalysisInterval( idPrefix ).to( 'enabled' );
		}
	}

	let analysisIntervals = {};
	/**
	 * Loops over analysis in a set interval.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {string} idPrefix
	 * @return {function} to : {
	 *    @param {string} state
	 * }
	 */
	const setAnalysisInterval = ( idPrefix ) => {
		return {
			to: state => {
				switch( state ) {
					case 'disabled':
						if ( idPrefix in analysisIntervals ) {
							clearInterval( analysisIntervals[ idPrefix ] );
							delete analysisIntervals[ idPrefix ];
						}
						break;

					case 'enabled':
						setAnalysisInterval( idPrefix ).to( 'disabled' );
						if ( l10n.settings.analysisInterval > 4999 )
							analysisIntervals[ idPrefix ] = setInterval( () => triggerAllAnalysis( idPrefix ), l10n.settings.analysisInterval );
						break;

					default:
						break;
				}
			}
		}
	}

	/**
	 * Prepares score elements for display.
	 * It automatically determines, based on the registry, whether to display
	 * or hide the element.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {HTMLElement} rater The score element wrap.
	 */
	const prepareScoreElement = ( rater ) => {

		let idPrefix = getSubIdPrefix( rater.id ),
			kw = getSubElementById( idPrefix, 'keyword' ).value;

		if ( ! kw ) return;

		let data, rating, blind, input, reassess;

		rating = rater.querySelector( '.tsfem-e-focus-assessment-rating' );
		tsfem_inpost.setIconClass( rating, 'loading' );
		blind = true;

		//= Determine if value must be made visible again, e.g. after DOM entry.
		//! @see updateActiveFocusAreas()
		reassess = typeof rater.dataset.assess !== 'undefined' && ! +rater.dataset.assess;

		//? jQuery unpacks JSON data.
		data = $( rater ).data( 'scores' );

		if ( data && data.hasOwnProperty( 'assessment' ) ) {
			if ( typeof activeFocusAreas[ data.assessment.content ] !== 'undefined' ) {
				rater.dataset.assess = 1;
				addToChangeListener( rater, data.assessment.content );
				blind = false;
			}
		}
		if ( blind ) {
			//= Hide the element when it can't be parsed, for now.
			tsfem_inpost.setIconClass( rating, 'unknown' );
			rater.dataset.assess = 0;

			input = document.getElementsByName( rater.id );
			if ( input && input[0] ) input[0].value = 0;
			tsfem_inpost.fadeOut( rater, 250 );
		} else if ( reassess ) {
			tsfem_inpost.fadeIn( rater, 250 );
		}
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
					case 'disabled':
						show = '.tsfem-e-focus-no-keyword-wrap';
						break;

					case 'enabled':
						show = '.tsfem-e-focus-scores-wrap';
						break;

					default:
					case 'error':
						show = '.tsfem-e-focus-something-wrong-wrap';
						break;
				}
				set( show );
			}
		};
	}

	/**
	 * Changes collapse header's subject edit button to various states.
	 *
	 * The edit button is utilized as a visual placeholder for AJAX calls.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {string} idPrefix
	 * @return {Object<string,function>} : {
	 *	@function to : @param {(string|array)} state The state or states to transform the button into.
	 * }
	 */
	const setEditButton = ( idPrefix ) => {
		return { to: ( state ) => {
			let editToggle = getSubElementById( idPrefix, 'subject_edit' );
			if ( ! editToggle || ! editToggle instanceof HTMLInputElement ) return;

			let editLabel = document.querySelector( 'label[for="' + editToggle.id + '"]' ),
				editWrap = editToggle.parentNode,
				disabledClass = 'tsfem-e-focus-edit-subject-button-wrap-disabled';

			state.split( ',' ).forEach( ( _state ) => {
				switch ( _state.trim() ) {
					case 'checked':
						if ( ! editToggle.checked ) {
							editToggle.checked = true;
							$( editToggle ).trigger( 'change' );
						}
						break;
					case 'unchecked':
						if ( editToggle.checked ) {
							editToggle.checked = false;
							$( editToggle ).trigger( 'change' );
						}
						break;

					case 'loading':
						editLabel && tsfem_inpost.setIconClass( editLabel, 'loading' );
						break;
					case 'edit':
						editLabel && tsfem_inpost.setIconClass( editLabel, 'edit' );
						break;

					case 'enabled':
						editToggle.disabled = false;
						editWrap.classList.toggle( disabledClass, false );
						break;
					case 'disabled':
						editToggle.disabled = true;
						editWrap.classList.toggle( disabledClass, true );
						break;

					default:break;
				}
			} );
		} };
	}

	/**
	 * Resets collapser listeners, like allowing double clicks on non-actionable elements.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
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

	/**
	 * Resets keyword entry listeners, which opens subject overview collapser and shows a loading bar.
	 *
	 * @since 1.0.0
	 * @access private
	 * @see doKeywordEntry() : Function that's called when the keyword is validated.
	 *
	 * @function
	 */
	const resetKeywordEntryListeners = () => {

		let keywordBuffer = {},
			keywordTimeout = 1500;

		let barSmoothness = 3,
			superSmooth = true,
			barWidth = {},
			barBuffer = {},
			barTimeout = keywordTimeout / ( 100 * barSmoothness );

		//= Add a little to make it visually "faster".
		if ( superSmooth )
			barTimeout *= 1.175;

		const barGo = ( id, bar ) => {
			bar.style.width = ++barWidth[ id ] / barSmoothness + '%';
		}
		const barStop = ( id, bar ) => {
			barWidth[ id ] = 0;
			bar.style.width = '0%';
		}

		let $keywordEntries = $( '.tsfem-e-focus-keyword-entry' );

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
			setAnalysisInterval( idPrefix ).to( 'disabled' );

			$( getSubElementById( idPrefix, 'scores' ) )
				.find( '.tsfem-e-focus-assessment-description' )
				.css( 'opacity', '0' );

			if ( ! val.length ) {
				setEvaluationVisuals( idPrefix ).to( 'disabled' );
				setLexicalFormSelectionListener( idPrefix ).to( 'disabled' );
				return;
			}

			if ( tsfem_inpost.isPremium && l10n.languageSupported.any ) {
				runLexicalFormSetter( idPrefix );
			}

			prepareWrapScoreElements( idPrefix );
			// prepareHighlighter( event );
		}

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

	/**
	 * Maintains subject editor dropdown selection.
	 * It's a visual medium that acts as a regular text field.
	 *
	 * Utilizes custom actions, like 'set-tsfem-e-focus-definition'.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 */
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
			let clicker  = event.target,
				selectId = void 0,
				selector = void 0,
				holder   = void 0,
				idPrefix = void 0,
				lastVal  = 0,
				lastText = '',
				newVal   = 0,
				newText  = '',
				setNow   = event.data && event.data.change || false;

			if ( typeof clicker.dataset.for !== 'undefined' )
				selectId = clicker.dataset.for;

			if ( ! selectId )
				return false;

			selector = document.getElementById( selectId );
			idPrefix = getSubIdPrefix( selectId );
			holder = getSubElementById( idPrefix, 'definition_selection' );

			if ( ! selector )
				return false;

			lastVal = selector.value;
			lastText = clicker.innerHTML;

			const doChange = () => {
				//= Show new option...
				clicker.innerHTML = newText;
				selector.value = newVal;
				holder.value = newVal;

				if ( +newVal !== +lastVal ) {
					//= Refill selection.
					getSubElementById( idPrefix, 'active_synonyms' ).value = '';
					refillSubjectSelection( idPrefix );
				}

				reset();
			}
			const undoChanges = () => {
				clicker.innerHTML = lastText;
				selector.value    = lastVal;
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
				setVals( event.target );
				+newVal === +lastVal && reset() || doChange();
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
			const idPrefix = getSubIdPrefix( event.target.id );

			let editor    = getSubElementById( idPrefix, 'edit' ),
				evaluator = getSubElementById( idPrefix, 'evaluate' );

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

		let subjectEditAction       = 'click.tsfem-e-focus-definition-editor',
			a11ySubjectEditAction   = 'keypress.tsfem-e-focus-definition-editor',
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

	let subjectFilterBuffer = {};
	/**
	 * Enables Subject filling and filtering requests.
	 *
	 * @since 1.0.0
	 * @access private
	 * @uses @var subjectFilterBuffer
	 * @see refillSubjectSelection() : The trigger method.
	 *
	 * @function
	 */
	const resetSubjectFilterListeners = () => {

		const getIterationFromId = id => ( /\[([0-9]+)\]$/.exec( id ) || '' )[1];
		const setActiveInflections = ( idPrefix ) => {
			let inflectionSection = getSubElementById( idPrefix, 'inflections' ),
				selected          = inflectionSection.querySelectorAll( 'input:checked' ),
				values            = [];
			selected.forEach( el => {
				values.push( getIterationFromId( el.id ) );
			} );
			getSubElementById( idPrefix, 'active_inflections' ).value = values.join();
			activeWords( idPrefix ).clearCache( 'inflections' );
			triggerAllAnalysis( idPrefix );
		}
		const setActiveSynonyms = ( idPrefix ) => {
			let synonymSection = getSubElementById( idPrefix, 'synonyms' ),
				selected       = synonymSection.querySelectorAll( 'input:checked' ),
				values         = [];
			selected.forEach( el => {
				values.push( getIterationFromId( el.id ) );
			} );
			getSubElementById( idPrefix, 'active_synonyms' ).value = values.join();
			activeWords( idPrefix ).clearCache( 'synonyms' );
			triggerAllAnalysis( idPrefix );
		}
		const updateActive = ( event ) => {
			const idPrefix  = event.data.idPrefix,
				  type      = event.data.type,
				  bufferKey = idPrefix + type;

			clearTimeout( subjectFilterBuffer[ bufferKey ] );
			subjectFilterBuffer[ bufferKey ] = setTimeout( () => {
				switch ( type ) {
					case 'inflections':
						setActiveInflections( idPrefix );
						break;
					case 'synonyms':
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
				inflectionHolder    = getSubElementById( idPrefix, 'inflection_data' ),
				synonymHolder       = getSubElementById( idPrefix, 'synonym_data' ),

				definitionSelection = getSubElementById( idPrefix, 'definition_selection' ),

				inflectionSection   = getSubElementById( idPrefix, 'inflections' ),
				synonymSection      = getSubElementById( idPrefix, 'synonyms' ),

				inflectionEntries   = inflectionSection.querySelector( '.tsfem-e-focus-subject-selection' ),
				synonymEntries      = synonymSection.querySelector( '.tsfem-e-focus-subject-selection' );
			const subjectTemplate = wp.template( 'tsfem-e-focus-subject-item' );

			//?! There's always just one form of inflections in latin.
			let availableInflections = inflectionHolder.value && JSON.parse( inflectionHolder.value ),
				inflections          =
					availableInflections
					&& availableInflections[0]
					&& availableInflections[0].inflections
					|| [];

			//?! A little bit trickier, because they might or might not be available.
			let availableSynonyms = synonymHolder.value && JSON.parse( synonymHolder.value ),
				synonyms          = availableSynonyms
					&& availableSynonyms[ +definitionSelection.value ]
					&& availableSynonyms[ +definitionSelection.value ].synonyms
					|| [];

			inflectionEntries.style.opacity = 0;
			synonymEntries.style.opacity = 0;

			let activeInflections = getSubElementById( idPrefix, 'active_inflections' ).value.split( ',' ),
				activeSynonyms    = getSubElementById( idPrefix, 'active_synonyms' ).value.split( ',' ),
				kw                = getSubElementById( idPrefix, 'keyword' ).value.toLowerCase();

			// Can error; point of headache, let's forgo it.
			// kw                = getSubElementById( idPrefix, 'keyword' ).value.toLocaleLowerCase( l10n.language );

			let html = '',
				prefix = createSubId( idPrefix, 'inflection' ),
				isKw = false;
			//= We need the keys... but they're sequential?
			for ( let i in inflections ) {
				isKw = inflections[ i ] === kw;
				html += subjectTemplate( {
					id:       createSubId( prefix, i ),
					value:    inflections[ i ],
					checked:  isKw || ( activeInflections.indexOf( String( i ) ) > -1 ) ? 'checked' : '', // Always enable the keyword-inflection.
					disabled: isKw ? 'disabled' : ''  // Disable the keyword-inflection from altering.
				} );
			}
			inflectionEntries.innerHTML = html;

			html = '';
			prefix = createSubId( idPrefix, 'synonym' );
			//= We need the keys... but they're sequential?
			for ( let i in synonyms ) {
				if ( inflections.indexOf( synonyms[ i ] ) > -1 ) continue;
				html += subjectTemplate( {
					id:      createSubId( prefix, i ),
					value:   synonyms[ i ],
					checked: activeSynonyms.indexOf( String( i ) ) > -1 ? 'checked' : ''
				} );
			}
			synonymEntries.innerHTML = html;

			if ( inflections.length ) {
				inflectionSection.style.display = null;
				tsfem_inpost.fadeIn( inflectionEntries );
			} else {
				inflectionSection.style.display = 'none';
			}

			if ( synonyms.length ) {
				synonymSection.style.display = null;
				tsfem_inpost.fadeIn( synonymEntries );
			} else {
				synonymSection.style.display = 'none';
			}

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
	/**
	 * Forces subject filling and filtering requests.
	 *
	 * @since 1.0.0
	 * @access private
	 * @see resetSubjectFilterListeners() : Needs to be called first.
	 *
	 * @function
	 * @param {string} idPrefix
	 */
	const refillSubjectSelection = ( idPrefix ) => {
		$( window ).trigger(
			'tsfem-e-focus-updated-subject',
			[ { idPrefix } ]
		);
	}

	let cachedActiveWords = {};
	/**
	 * Returns active keyword, inflections and listeners.
	 *
	 * @since 1.0.0
	 * @access private
	 * @uses @var cachedActiveWords
	 *
	 * @function
	 * @param {string} idPrefix
	 * @return {Object<string,function>} : {
	 *  @function get        : @param what What to get.
	 *  @function clearCache : @param what What to clear.
	 * }
	 */
	const activeWords = ( idPrefix ) => {
		if ( ! cachedActiveWords.hasOwnProperty( idPrefix ) ) {
			cachedActiveWords[ idPrefix ] = {
				inflections: void 0,
				synonyms: void 0,
			};
		}

		const getActiveInflections = () => {
			let ret = [],
				kw  = getSubElementById( idPrefix, 'keyword' ).value;

			const activeInflections = getSubElementById( idPrefix, 'active_inflections' );

			if ( activeInflections instanceof HTMLInputElement && activeInflections.value ) {
				let inflectionData = getSubElementById( idPrefix, 'inflection_data' ).value,
					inflections    = JSON.parse( inflectionData )[0].inflections;

				activeInflections.value.split( ',' ).forEach( i => {
					ret.push( inflections[ +i ] );
				} );
			}
			// Always set kw as inflection (if not present).
			if ( -1 === ret.indexOf( kw ) )
				ret.push( kw );

			return cachedActiveWords[ idPrefix ].inflections = ret;
		}
		const getActiveSynonyms = () => {
			let ret = [];

			const activeSynonyms     = getSubElementById( idPrefix, 'active_synonyms' ),
				  selectedDefinition = getSubElementById( idPrefix, 'definition_selection' );

			if ( activeSynonyms instanceof HTMLInputElement && activeSynonyms.value ) {
				let synonymData = getSubElementById( idPrefix, 'synonym_data' ).value,
					synonyms    = JSON.parse( synonymData )[ +selectedDefinition.value ].synonyms;

				activeSynonyms.value.split( ',' ).forEach( i => {
					ret.push( synonyms[ +i ] );
				} );
			}

			return cachedActiveWords[ idPrefix ].synonyms = ret;
		}

		const getActive = ( what ) => {
			if ( void 0 !== cachedActiveWords[ idPrefix ][ what ] )
				return cachedActiveWords[ idPrefix ][ what ];

			switch ( what ) {
				case 'inflections':
					return getActiveInflections();
				case 'synonyms':
					return getActiveSynonyms();
				default:
					return {
						inflections: getActiveInflections(),
						synonyms:    getActiveSynonyms()
					};
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
			get:        what => getActive( what ),
			clearCache: what => clear( what )
		};
	}

	/**
	 * Changes icon class of all raters of assessment type.
	 *
	 * @since 1.0.0
	 * @access private
	 * @see tsfem_e_focus_inpost.setIconClass()
	 * @uses @var activeAssessments
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
	 * @uses @var activeAssessments
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

	let changeListenersBuffers = {}, changeListenerFlags = {};
	/**
	 * Resets change listeners for analysis on the available content elements.
	 *
	 * @since 1.0.0
	 * @access public
	 * @uses @var activeAssessments
	 * @uses @var changeListenersBuffers
	 * @uses @var changeListenerFlags
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
				setTimeout( () => doCheck( el ), 150 );
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

		if ( type ) {
			if ( typeof activeFocusAreas[ type ] !== 'undefined' ) {
				reset( type );
			}
		} else {
			for ( let _type in activeFocusAreas ) {
				if ( typeof activeFocusAreas[ _type ] !== 'undefined' ) {
					reset( _type );
				}
			}
		}
	}

	let triggerAllBuffer = {};
	/**
	 * Triggers all analysis.
	 *
	 * Another buffer is maintained at trigger level.
	 * However, this loop can be heavy.
	 *
	 * doCheck() debounces. As such, this method may be invoked concurrently with user input without issue.
	 *
	 * @see addToChangeListener()
	 * @see analysisChangeEvents()
	 *
	 * @since 1.0.0
	 * @access public
	 * @uses @var activeFocusAreas
	 * @uses @var triggerAllBuffer
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
				if ( subScores instanceof NodeList && subScores.length ) {
					tsfem_inpost.promiseLoop( subScores, item => doCheck( item ), 150 );
				} else {
					setEvaluationVisuals( idPrefix ).to( 'error' );
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
	 * @function
	 * @param {string} type The content type.
	 * @return {object<string,?>)}
	 */
	const analysisChangeEvents = ( type ) => {
		const events = [
			'tsfem-e-focus.analysis-' + type, // Custom trigger.
			'input.tsfem-e-focus-' + type,
			'change.tsfem-e-focus-' + type
		];
		return { get: ( what ) => {
			switch ( what ) {
				case 'names'   : return events.join( ' ' );
				case 'trigger' : return events[0];
				default        : return events;
			}
		} };
	}

	/**
	 * Applies analysis listeners to elements that can't have default JS listeners
	 * for the new Gutenberg Editor.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @function
	 */
	const patchNewEditor = () => {

		let $document = $( document ),
			holder = document.getElementById( 'tsf-gutenberg-data-holder' );

		if ( ! holder ) return;

		const getId      = type => `tsfem-focus-gbc-${type}`;
		const getElement = type => document.getElementById( getId( type ) );

		/**
		 * @param {string} type
		 * @param {string} assessment
		 */
		const setup = ( type, assessment ) => {
			createStore( type );
			// Wait for store to be written, defer:
			setTimeout( () => {
				updateActiveFocusAreas( assessment );
				resetAnalysisChangeListeners( assessment );
			} );
			$document.trigger( `tsfem-focus-gutenberg-${type}-store-set` );
		}

		/**
		 * @param {string} type
		 */
		const createStore = type => {
			if ( getElement( type ) ) return;
			let store = document.createElement( 'div' );
			store.id = getId( type );
			store.style.display = 'none';
			holder.appendChild( store );
		};

		/**
		 * @param {string} type
		 */
		const emptyStore = type => {
			getElement( type ).innerHTML = null;
		};

		/**
		 * @note: Setting data takes time.
		 * @param {string} type
		 * @param {string} data
	 	 * @return {jQuery.Deferred|Promise} The promise object.
		 */
		const fillStore = ( type, data ) => {
			// Security: WordPress (or React) escapes the data.
			getElement( type ).innerHTML = data;
		};

		/**
		 * @param {string} type
		 * @param {string} assessment
		 */
		const triggerRead = ( type, assessment ) => {
			$( '#' + getId( type ) ).trigger( analysisChangeEvents( assessment ).get( 'trigger' ) );
		}

		$document.on( 'tsf-updated-gutenberg-title', ( event, title ) => {
			fillStore( 'title', title );
			triggerRead( 'title', 'pageTitle' );
		} );
		setup( 'title', 'pageTitle' );

		$document.on( 'tsf-updated-gutenberg-link', ( event, link ) => {
			fillStore( 'link', link );
			triggerRead( 'link', 'pageUrl' );
		} );
		setup( 'link', 'pageUrl' );

		/**
		 * Debounced function, as the content is heavy, and we want to read typing states.
		 * @param {string} content
		 */
		const updateContent = content => {
			fillStore( 'content', content );
			triggerRead( 'content', 'pageContent' );
		}
		$document.on( 'tsf-updated-gutenberg-content', ( event, content ) => {
			let debouncer = lodash.debounce( updateContent, 500 );

			let isTyping = false,
				editor   = wp.data.select( 'core/block-editor' ) || wp.data.select( 'core/editor' );

			if ( 'function' === typeof editor.isTyping ) {
				isTyping = editor.isTyping();
			}

			if ( isTyping ) {
				// Don't process and lag when typing, just show that the data is invalidated.
				setAllRatersOf( 'pageContent', 'unknown' );
				debouncer( content );
			} else {
				debouncer.cancel(); // Cancel original debouncer.
				updateContent( content ); // Set content immediately.
			}
		} );
		setup( 'content', 'pageContent' );
	}

	/**
	 * Applies analysis listeners to elements that can't have default JS listeners
	 * for the Classic Editor.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @function
	 */
	const patchClassicEditor = () => {

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
				resetAnalysisChangeListeners( 'pageUrl' );
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
	 * Applies analysis listeners to elements that can't have default JS listeners.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Now checks for isGutenbergPage
	 * @access private
	 *
	 * @function
	 */
	const monkeyPatch = () => {
		if ( l10n.isGutenbergPage ) {
			patchNewEditor();
		} else {
			patchClassicEditor();
		}
	}

	/**
	 * Refills keyword data and actions from previous save.
	 * Should run before loading other methods.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Now no longer reenables an empty lexical_selector
	 * @access private
	 *
	 * @function
	 */
	const refillData = () => {
		let wraps = document.querySelectorAll( '.tsfem-e-focus-collapse-wrap' );

		for ( let _i = wraps.length; _i--; ) {
			let idPrefix = getSubIdPrefix( wraps[ _i ].id ),
				kwEntry = getSubElementById( idPrefix, 'keyword' );

			if ( kwEntry.value.length ) {
				//= Prepare keyword value.
				kwEntry.dataset.prev = kwEntry.value;
				if ( tsfem_inpost.isPremium && l10n.languageSupported.any ) {
					//= Prepare lexical selector values.
					let lexicalSelector = getSubElementById( idPrefix, 'lexical_selector' );
					lexicalSelector.dataset.prev = lexicalSelector.value;
					//? Enable it if there's more than 1 option. Option 0 is disabled.
					if ( lexicalSelector.length > 1 ) {
						setLexicalFormSelectionListener( idPrefix ).to( 'enabled' );
					} else {
						clearData( idPrefix, 'lexical' );
					}
					if ( +lexicalSelector.value ) {
						//= Prepare edit button.
						setEditButton( idPrefix ).to( 'enabled' );

						populateDefinitionSelector( idPrefix );
						refillSubjectSelection( idPrefix );
					}
				}
				prepareWrapScoreElements( idPrefix );
			}
		};
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
			let el = document.getElementById( 'tsfem-e-focus-analysis-wrap' );
			if ( el instanceof Element ) {
				let _el = el.querySelector( '.tsf-flex-setting-input' );
				if ( _el instanceof Element )
					_el.innerHTML = wp.template( 'tsfem-e-focus-nofocus' )();
			}
			return;
		}

		resetCollapserListeners();
		resetKeywordEntryListeners();

		if ( tsfem_inpost.isPremium && l10n.languageSupported.any ) {
			//= Prepare definition selector.
			resetSubjectEditListeners();
			resetSubjectFilterListeners();
		}

		refillData();
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
