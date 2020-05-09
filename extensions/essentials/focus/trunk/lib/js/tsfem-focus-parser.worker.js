/**
 * This worker file holds Focus' code for interpreting contents.
 * Serve JavaScript as an addition, not as an ends or means.
 * Alas, there's no other way here.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2019-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

let workerId = '';

let inflectionCount     = 0,
	synonymCount        = 0,
	inflectionCharCount = 0,
	synonymCharCount    = 0,
	contentCharCount    = 0;

let regex,
	synonyms,
	inflections;

/**
 * Resets worker.
 *
 * @since 1.3.0
 *
 * @function
 */
const reset = () => {
	inflectionCount     = 0;
	synonymCount        = 0;
	inflectionCharCount = 0;
	synonymCharCount    = 0;
	contentCharCount    = 0;

	regex       = void 0;
	synonyms    = void 0;
	inflections = void 0;
}

/**
 * Normalizes duplicated spacing. Ignores line feeds.
 * Has an emphasis on TinyMCE's "&nbsp;" pollution, converting it to a single space.
 *
 * @since 1.3.0
 *
 * @function
 * @param {string} str
 * @return {string}
 */
const normalizeSpacing = str => str.replace( /(?!(\n+|\r)+)(&nbsp;|\s)+/gu, ' ' );

/**
 * Escapes regular expression input.
 *
 * @since 1.3.0
 *
 * @function
 * @param {string} str
 * @return {string}
 */
//? The \- makes for an invalid escape...?
// const escapeRegex = ( str ) => str.replace( /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&' );
const escapeRegex = str => str.replace( /[\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&' );

/**
 * Makes any non-word character in a regular expression a non-word character boundary.
 * Strips (accidental) non-word character boundaries at the start and end of the expression, afterwards, too.
 *
 * Example: "Something. Here." will become: Something\W+Here
 * The \W+ isn't found at the end, as it's also trimmed.
 *
 * Ref:
 * [\u0020-\u002F\u003A-\u003F] - Latin punctuation.
 * [\u2000-\u206F] - General punctuation.
 * [\u0591-\u05C4\u05F3-\u05F4] - Hebrew punctuation.
 * [\u060C-\u061F] - Arabic punctuation.
 * [\u0700-\u070F] - Syriac punctuation.
 * [\u3000-\u3030] - CJK punctuation.
 *
 * [\u005B-\u0060] - Accents, Low line.
 * [\u007B-\u007E] - Brackets, pipe, tilde.
 *
 * @since 1.3.0
 *
 * @function
 * @param {string} str
 * @return {string}
 */
const bewilderRegexNonWords = str => /^(\\W\+)*(.*?)(\\W\+)*$/.exec( str.replace( /\W+/gu, '\\W+' ) )[2];

/**
 * Escapes HTML input.
 *
 * @since 1.3.0
 * @source tsfem-inpost.js
 *
 * @function
 * @param {string}              str
 * @param {(boolean|undefined)} noquotes Whether to exclude quotes.
 * @return {string}
 */
const escapeStr = ( str, noquotes ) => {
	if ( ! str.length ) return '';

	if ( noquotes ) {
		return str.replace( /[&<>]/g, ( m ) => {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
			}[ m ];
		} );
	} else {
		return str.replace( /[&<>"']/g, ( m ) => {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			}[ m ];
		} );
	}
}

/**
 * Promises to do a loop, and tells when the loop is done.
 *
 * @since 1.3.0
 * @source tsfem-inpost.js
 *
 * @function
 * @param {(array|object<number,?>)} iterable The items to loop over.
 * @param {function}                 cb       The callback function returning a Promise.
 * @param {number|undefined}         timeout  The iteration timeout. Optional. Defaults to 0.
 * @param {number|undefined}         stopAt   The iteration anti-lag blocker. Optional. Defaults to 2000 ms.
 * @return {jQuery.Deferred|Promise} The promise object.
 */
const promiseLoop = ( iterable, cb, timeout = 0, stopAt = 2000 ) => new Promise( ( resolve, reject ) => {
	let its = iterable.length;

	// No iterable is set. That's OK, this should be handled earlier.
	if ( ! its ) return resolve();

	const loop = ( it ) => {
		let looper, stopper, rejector;
		// return new Promise( ( resolve, reject ) => {
			// Prepare loop stopper.
		if ( stopAt ) {
			stopper = setTimeout( () => {
				// Stopper fired: Stop loop.
				clearTimeout( looper );
				rejector = setTimeout( () => {
					// Rejector passed, reject loop.
					reject();
				}, 250 );
			}, stopAt );
		}

		looper = setTimeout( () => new Promise( ( _resolve, _reject ) => {
			try {
				cb( iterable[ it ] );
				_resolve();
			} catch ( e ) {
				_reject();
			}
		} ).then( () => {
			if ( stopAt ) {
				clearTimeout( stopper );
				// If the rejector is enqueued, see if there are still items to loop over.
				if ( rejector ) {
					if ( it < its ) {
						// There are still items... Don't propagate loop, and let the rejector do its thing.
						return;
					} else {
						// End of loop, nothing to reject: cancel rejection.
						clearTimeout( rejector );
					}
				}
			}
			if ( ++it === its ) {
				resolve();
			} else {
				looper = null;
				loop( it );
			}
		} ).catch( () => {
			reject();
		} ), timeout );
	}
	loop( 0 );
} );

const countChars = ( str ) => {
	// Strip all XML tags first.
	str = str.match( /(?=([^<>]+))\1(?=<|$)/gi );
	str = str && str.join( ' ' ) || '';
	// Strip duplicated spaces.
	str = str.replace( /\s+/giu, ' ' );
	return +str.length || 0;
}
const countWords = ( word, contentMatch ) => {
	// pReg = prepared Regex.
	let pReg;
	// sWord = sanitized Word
	let sWord = bewilderRegexNonWords( escapeRegex( escapeStr( word, true ) ) );

	// If nothing comes from sanitization, return 0 (nothing found).
	if ( ! sWord ) return 0;

	//= Iterate over multiple regex scripts.
	for ( let i = 0; i < regex.length; i++ ) {
		// Split Regex's flags from the expression.
		pReg = /\/(.*)\/(.*)/.exec( regex[ i ] );

		contentMatch = contentMatch.match( new RegExp(
			pReg[1].replace( /\{\{kw\}\}/g, sWord ), // Replace {{kww}} with the keyword, if any.
			pReg[2]                                  // flag.
		) );

		//= Stop if there's no content, or when this is the last iteration.
		if ( ! contentMatch || i === regex.length - 1 ) break;

		//= Join content as this is a recursive regexp.
		contentMatch = contentMatch.join( ' ' );
	}
	// Return the number of matches found.
	return contentMatch && contentMatch.length || 0;
}
const stripWord = ( word, str ) => str.replace(
	new RegExp(
		escapeRegex( escapeStr( word, true ) ),
		'gi'
	),
	'/' //? A filler that doesn't break XML tag attribute closures (<|>|"|'|\s).
);

const countCharacters = ( content ) => new Promise( ( resolve, reject ) => {
	setTimeout( () => {
		contentCharCount += countChars( content );
		resolve();
	}, 5 );
} );

const countInflections = ( content ) => {
	let _inflections = inflections,
		_content = content;
	_inflections.length && _inflections.sort( ( a, b ) => b.length - a.length );

	return promiseLoop( _inflections, ( inflection ) => {
		let count = countWords( inflection, _content );
		inflectionCount += count;
		inflectionCharCount += inflection.length * count;
		_content = stripWord( inflection, _content );
	}, 5, 10000 );
}
const countSynonyms = ( content ) => {
	let _synonyms = synonyms,
		_content = content;

	_synonyms.length && _synonyms.sort( ( a, b ) => b.length - a.length );

	return promiseLoop( _synonyms, ( synonym ) => {
		let count = countWords( synonym, _content );
		synonymCount += count;
		synonymCharCount += synonym.length * count;
		_content = stripWord( synonym, _content );
	}, 5, 10000 );
}

onmessage = message => {
	workerId = message.data.id;

	// Reset worker data.
	reset();

	let data    = message.data.data,
		content = normalizeSpacing( data.content );

	regex       = data.regex;
	inflections = data.inflections;
	synonyms    = data.synonyms;

	if ( ! content ) {
		postMessage( void 0 );
	} else {
		Promise.all( [
			data.assess.getCharCount && countCharacters( content ),
			countInflections( content ),
			countSynonyms( content ),
		] ).then( () => {
			postMessage( {
				inflectionCount,
				synonymCount,
				inflectionCharCount,
				synonymCharCount,
				contentCharCount,
			} );
		} ).catch( ( error ) => {
			postMessage( { workerId, error } );
		} );
	}
}

onerror = ( msg, url, lineNo, columnNo, error ) => {
	postMessage( { workerId, error } );
}
