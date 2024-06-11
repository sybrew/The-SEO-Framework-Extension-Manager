/**
 * This worker file holds Focus's code for interpreting contents.
 * Serve JavaScript as an addition, not as an ends or means.
 * Alas, there's no other way here.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2019-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @since 1.4.0 Now also escapes the `-` character
 *              This is redundant, since the string cannot opperate since the [] characters are also disabled.
 *
 * @function
 * @param {string} str
 * @return {string}
 */
const escapeRegex = str => str.replace( /[-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&' );

/**
 * Makes any non-word character in a regular expression a non-word character boundary.
 * Strips (accidental) non-word character boundaries at the start and end of the expression, afterwards, too.
 *
 * Example: "Something. Here!" will become: "Something\W+Here"
 * To achieve that, we need to create "Something\\W+Here\\W+" first. We escape the "\" because it is a literal match for the cleanup.
 * In the cleanup, we remove "\\W+"" from the start and end, so "Something\\W+Here\\W+" becomes "Something\\W+Here".
 *
 * Ultimately, we want to match "Something, Here." as well as "Something Here".
 *
 * Since `\W+` doesn't work with Unicode, we must use `[^\p{Alphabetic}\p{Mark}\p{Decimal_Number}\p{Connector_Punctuation}\p{Join_Control}]+)*(.*?)([^\p{Alphabetic}\p{Mark}\p{Decimal_Number}\p{Connector_Punctuation}\p{Join_Control}]+`.
 * See <https://github.com/tc39/proposal-regexp-unicode-property-escapes?tab=readme-ov-file#unicode-aware-version-of-w-1>.
 * This translates to `[^\p{L}\p{M}\p{Nd}\p{Pc}\p{Join_Control}]`.
 * This translates to `\P{XID_Continue}`.
 * This translates to `\P{XIDC}`.
 *
 * @since 1.3.0
 * @since 1.5.4 We now default to the Unicode detection built in the browser.
 * @link https://github.com/tc39/proposal-regexp-unicode-property-escapes?tab=readme-ov-file#unicode-aware-version-of-w-1
 *
 * @function
 * @param {string} str
 * @return {string}
 */
const bewilderRegexNonWords = str => /^(\\P{XIDC})*(.*?)(\\P{XIDC})*$/.exec(
	str.replace( /\P{XIDC}+/gu, '\\P{XIDC}' )
)[2];

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
		return str.replace( /[&<>]/g, m => {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
			}[ m ];
		} );
	} else {
		return str.replace( /[&<>"']/g, m => {
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
 * @return {Promise} The promise object.
 */
const promiseLoop = ( iterable, cb, timeout = 0, stopAt = 2000 ) => new Promise( ( resolve, reject ) => {
	let its = iterable.length;

	// No iterable is set. That's OK, this should be handled earlier.
	if ( ! its ) return resolve();

	const loop = it => {
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

		looper = setTimeout(
			() => new Promise( async ( _resolve, _reject ) => {
				try {
					await cb( iterable[ it ] );
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
			} ),
			timeout
		);
	}
	loop( 0 );
} );

const countChars = str => {
	// TODO does this stripping interfere with lone < (that become &lt;)?
	// Strip all XML tags first.
	str = str.match( /(?=([^<>]+))\1(?=<|$)/gi );
	str = str && str.join( ' ' ) || '';
	// Strip duplicated spaces.
	str = str.replace( /\s+/gu, ' ' );
	return +str.length || 0;
}
const countWords = ( word, contentMatch ) => {
	// pReg: prepared Regex.
	let pReg;
	// sWord: sanitized Word
	let sWord = bewilderRegexNonWords( escapeRegex( escapeStr( word, true ) ) );

	// If nothing comes from sanitization, return 0 (nothing found).
	if ( ! sWord ) return 0;

	// Iterate over multiple regex scripts.
	for ( let i = 0; i < regex.length; i++ ) {
		// Split Regex's flags from the expression.
		pReg = /\/(.*)\/(.*)/.exec( regex[ i ] );

		contentMatch = contentMatch.match( new RegExp(
			pReg[1].replace( /\{\{kw\}\}/g, sWord ), // Replace {{kw}} with the keyword, if any.
			pReg[2]                                  // flag.
		) );

		// Stop if there's no content, or when this is the last iteration.
		if ( ! contentMatch || i === regex.length - 1 ) break;

		// Join content as this is a recursive regexp.
		contentMatch = contentMatch.join( ' ' );
	}
	// Return the number of matches found.
	return contentMatch?.length || 0;
}
const stripWord = ( word, str ) => str.replace(
	new RegExp(
		escapeRegex( escapeStr( word, true ) ),
		'gi'
	),
	'/' // A filler that doesn't break XML tag attribute closures (<|>|"|'|\s).
);

const countCharacters = content => new Promise( ( resolve, reject ) => {
	setTimeout( () => {
		contentCharCount += countChars( content );
		resolve();
	}, 5 );
} );

const countInflections = content => {
	let _inflections = inflections,
		_content     = content;
	_inflections.length && _inflections.sort( ( a, b ) => b.length - a.length );

	return promiseLoop( _inflections, inflection => {
		let count = countWords( inflection, _content );
		inflectionCount += count;
		inflectionCharCount += inflection.length * count;
		_content = stripWord( inflection, _content );
	}, 5, 10000 );
}
const countSynonyms = content => {
	let _synonyms = synonyms,
		_content  = content;

	_synonyms.length && _synonyms.sort( ( a, b ) => b.length - a.length );

	return promiseLoop( _synonyms, synonym => {
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
		// TODO use allSettled? Will lead to faux data--let catch handle it.
		Promise.all( [
			data.assess.getCharCount && countCharacters( content ),
			countInflections( content ), // FIXME we should reuse content (stripWord)... at the expense of performance and delay...
			countSynonyms( content ),
		] ).then( () => {
			postMessage( {
				inflectionCount,
				synonymCount,
				inflectionCharCount,
				synonymCharCount,
				contentCharCount,
			} );
		} ).catch( error => {
			postMessage( { workerId, error } );
		} );
	}
}

onerror = ( msg, url, lineNo, columnNo, error ) => {
	postMessage( { workerId, error } );
}
