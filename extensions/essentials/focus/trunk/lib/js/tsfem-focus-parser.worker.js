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
 * Copyright (C) 2019-2021 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Workaround for Babel thinking it's smart by unminifying the file by packaging this into an unreachable.
 * It still thinks it's smart, but by 33% less so...
 */
const PLPN = (() => {
	/**
	 * @ref <https://caniuse.com/#feat=mdn-javascript_builtins_regexp_property_escapes>
	 * @ref <https://www.regular-expressions.info/unicode.html>
	 * @link <https://github.com/sybrew/the-seo-framework/wiki/Debugging-input-string>
	 * @source <https://github.com/slevithan/xregexp/blob/7d2c087d5b39346bc679d29aec852dfbf8b935d9/tools/output/properties.js>
	 *
	 * @NOTE set babel/minify options to: [	regexpConstructors: false, evaluate: false, ], to prevent extraneous RegExp
	 * unpacking and evaluation thereof. This is not ideal; but, then again, nothing of this is.
	 *
	 * This (roughly) equates [\P{L}\P{N}] (or) [^\p{L}\p{N}].
	 * Remove the ^ at the start, and you get the opposite, of course.
	 *
	 * Thanks for keeping up with the times, ECMA.
	 * You're only 15 years late in providing an alternative.
	 */
	const PLPN = "[^0-9A-Za-z\xAA\xB5\xBA\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0345\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0560-\u0588\u05B0-\u05BD\u05BF\u05C1\u05C2\u05C4\u05C5\u05C7\u05D0-\u05EA\u05EF-\u05F2\u0610-\u061A\u0620-\u0657\u0659-\u065F\u066E-\u06D3\u06D5-\u06DC\u06E1-\u06E8\u06ED-\u06EF\u06FA-\u06FC\u06FF\u0710-\u073F\u074D-\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0817\u081A-\u082C\u0840-\u0858\u0860-\u086A\u08A0-\u08B4\u08B6-\u08BD\u08D4-\u08DF\u08E3-\u08E9\u08F0-\u093B\u093D-\u094C\u094E-\u0950\u0955-\u0963\u0971-\u0983\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD-\u09C4\u09C7\u09C8\u09CB\u09CC\u09CE\u09D7\u09DC\u09DD\u09DF-\u09E3\u09F0\u09F1\u09FC\u0A01-\u0A03\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A3E-\u0A42\u0A47\u0A48\u0A4B\u0A4C\u0A51\u0A59-\u0A5C\u0A5E\u0A70-\u0A75\u0A81-\u0A83\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD-\u0AC5\u0AC7-\u0AC9\u0ACB\u0ACC\u0AD0\u0AE0-\u0AE3\u0AF9-\u0AFC\u0B01-\u0B03\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D-\u0B44\u0B47\u0B48\u0B4B\u0B4C\u0B56\u0B57\u0B5C\u0B5D\u0B5F-\u0B63\u0B71\u0B82\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BBE-\u0BC2\u0BC6-\u0BC8\u0BCA-\u0BCC\u0BD0\u0BD7\u0C00-\u0C03\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D-\u0C44\u0C46-\u0C48\u0C4A-\u0C4C\u0C55\u0C56\u0C58-\u0C5A\u0C60-\u0C63\u0C80-\u0C83\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD-\u0CC4\u0CC6-\u0CC8\u0CCA-\u0CCC\u0CD5\u0CD6\u0CDE\u0CE0-\u0CE3\u0CF1\u0CF2\u0D00-\u0D03\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D-\u0D44\u0D46-\u0D48\u0D4A-\u0D4C\u0D4E\u0D54-\u0D57\u0D5F-\u0D63\u0D7A-\u0D7F\u0D82\u0D83\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0DCF-\u0DD4\u0DD6\u0DD8-\u0DDF\u0DF2\u0DF3\u0E01-\u0E3A\u0E40-\u0E46\u0E4D\u0E81\u0E82\u0E84\u0E86-\u0E8A\u0E8C-\u0EA3\u0EA5\u0EA7-\u0EB9\u0EBB-\u0EBD\u0EC0-\u0EC4\u0EC6\u0ECD\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F71-\u0F81\u0F88-\u0F97\u0F99-\u0FBC\u1000-\u1036\u1038\u103B-\u103F\u1050-\u108F\u109A-\u109D\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16EE-\u16F8\u1700-\u170C\u170E-\u1713\u1720-\u1733\u1740-\u1753\u1760-\u176C\u176E-\u1770\u1772\u1773\u1780-\u17B3\u17B6-\u17C8\u17D7\u17DC\u1820-\u1878\u1880-\u18AA\u18B0-\u18F5\u1900-\u191E\u1920-\u192B\u1930-\u1938\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A1B\u1A20-\u1A5E\u1A61-\u1A74\u1AA7\u1B00-\u1B33\u1B35-\u1B43\u1B45-\u1B4B\u1B80-\u1BA9\u1BAC-\u1BAF\u1BBA-\u1BE5\u1BE7-\u1BF1\u1C00-\u1C36\u1C4D-\u1C4F\u1C5A-\u1C7D\u1C80-\u1C88\u1C90-\u1CBA\u1CBD-\u1CBF\u1CE9-\u1CEC\u1CEE-\u1CF3\u1CF5\u1CF6\u1CFA\u1D00-\u1DBF\u1DE7-\u1DF4\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2160-\u2188\u24B6-\u24E9\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2DE0-\u2DFF\u2E2F\u3005-\u3007\u3021-\u3029\u3031-\u3035\u3038-\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312F\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FEF\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA674-\uA67B\uA67F-\uA6EF\uA717-\uA71F\uA722-\uA788\uA78B-\uA7BF\uA7C2-\uA7C6\uA7F7-\uA805\uA807-\uA827\uA840-\uA873\uA880-\uA8C3\uA8C5\uA8F2-\uA8F7\uA8FB\uA8FD-\uA8FF\uA90A-\uA92A\uA930-\uA952\uA960-\uA97C\uA980-\uA9B2\uA9B4-\uA9BF\uA9CF\uA9E0-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA36\uAA40-\uAA4D\uAA60-\uAA76\uAA7A-\uAABE\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEF\uAAF2-\uAAF5\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB67\uAB70-\uABEA\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]";

	let escPLPN = escapeRegex( PLPN );

	return {
		base: PLPN,
		trim: new RegExp(
			`^(${escPLPN}\+)*(.*?)(${escPLPN}\+)*$`
		),
		find: new RegExp( `${PLPN}+`, 'gu' )
	}
})();

/**
 * Makes any non-word character in a regular expression a non-word character boundary.
 * Strips (accidental) non-word character boundaries at the start and end of the expression, afterwards, too.
 *
 * Example (old version): "Something. Here." will become: Something\W+Here
 * The \W+ isn't found at the start or end, because it's also trimmed.
 *
 * @since 1.3.0
 *
 * @function
 * @param {string} str
 * @return {string}
 */
const bewilderRegexNonWords = str => PLPN.trim.exec( str.replace( PLPN.find, `${PLPN.base}+` ) )[2];
// Old version, that doesn't support this well, despite the unicode flag.:
// const bewilderRegexNonWords = str => /^(\\W\+)*(.*?)(\\W\+)*$/.exec( str.replace( /\W+/gu, '\\W+' ) )[2];
// Future version, if only browsers vendors take less of their time (mind the use of template literals, they preserve backslashes):
// const bewilderRegexNonWords = str => /^(\[\\P\{L\}\\P\{N\}\]\+)*(.*?)(\[\\P\{L\}\\P\{N\}\]\+)*$/.exec( str.replace( /[\P{L}\P{N}]+/g, `[\P{L}\P{N}]+` ) )[2];

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
	// TODO does this stripping interfere with lone < (that become &lt;)?
	// Strip all XML tags first.
	str = str.match( /(?=([^<>]+))\1(?=<|$)/gi );
	str = str && str.join( ' ' ) || '';
	// Strip duplicated spaces.
	str = str.replace( /\s+/giu, ' ' );
	return +str.length || 0;
}
const countWords = ( word, contentMatch ) => {
	// pReg: prepared Regex.
	let pReg;
	// sWord: sanitized Word
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
		_content     = content;
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
		_content  = content;

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
		// todo use allSettled? Will lead to faux data--let catch handle it.
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
		} ).catch( ( error ) => {
			postMessage( { workerId, error } );
		} );
	}
}

onerror = ( msg, url, lineNo, columnNo, error ) => {
	postMessage( { workerId, error } );
}
