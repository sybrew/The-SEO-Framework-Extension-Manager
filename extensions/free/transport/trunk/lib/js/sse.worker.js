/**
 * This worker file holds Transporter's code for SSE logging.
 * Serve JavaScript as an addition, not as an ends or means.
 * Alas, there's no other way here.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * Transporter extension for The SEO Framework
 * copyright (C) 2022 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * @since 1.0.0
 * @access private
 * @type {String} The current worker ID.
 */
let workerId = '';

/**
 * @since 1.0.0
 * @access private
 * @type {Object<string,Worker>}
 */
const SSE = {};

/**
 * @since 1.0.0
 * @access private
 * @type {int} The retry timeout in milliseconds.
 */
const retryTimeout = 5000;

/**
 * @since 1.0.0
 * @access private
 * @type {int} The number of retries allowed before manual intervention is required.
 */
const retryLimit = 3;

const _log     = message => postMessage( { workerId, log: message } );
const _resolve = message => postMessage( { workerId, resolve: message } );
const _reject  = message => postMessage( { workerId, reject: message } );

onerror   = ( msg, url, lineNo, columnNo, error ) => postMessage( { workerId, error } );
onmessage = message => {
	workerId = message.data.id;
	SSE?.[ workerId ]?.close();

	const { handle, formData, logStart, urlData, nonce, logMessages } = message.data.data;

	const sseURL = new URL( urlData.endpoint, urlData.base );

	sseURL.searchParams.set( 'action', 'tsfem_e_transport' );
	sseURL.searchParams.set( 'handle', handle );
	sseURL.searchParams.set( 'nonce', nonce );
	sseURL.searchParams.set( 'data', formData );

	let retryCount           = 0,
		urlParamRetryAllowed = 1;

	const startSSE = () => {
		sseURL.searchParams.set( 'retryAllowed', urlParamRetryAllowed );

		SSE[ workerId ] = new EventSource( sseURL.href );

		SSE[ workerId ].onopen = event => {
			_log( logStart, 1 );
		}
		SSE[ workerId ].onerror = event => {
			SSE[ workerId ].close();
			_log( logMessages.unknownErrorFull, 2 );
			_reject( logMessages.unknownError );
		}

		const extractEventData = data => {
			try {
				return JSON.parse( data );
			} catch ( error ) {
				return void 0;
			}
		}
		const doEventResolve = event => {
			SSE[ workerId ].close();
			const data = extractEventData( event.data );
			_log( "\n\n" + data.logMsg, 2 );
			_resolve( data?.results.notice );
		}
		const doEventReject = event => {
			SSE[ workerId ].close();
			const data = extractEventData( event.data );
			_log( '&nbsp;' );
			_log( data?.logMsg, 2 );
			_resolve( data?.results.notice );
		}
		const doEventDie = event => {
			// Don't close: On die it will close anyway.
			_log( '===============' );
			_log( '&nbsp;' );
			_log( extractEventData( event.data )?.content );
			_log( '&nbsp;' );
			_log( '===============', 1 );
		}
		const doEventRetry = event => {

			retryCount++;

			_log( '===============', 1 );

			if ( retryCount > retryLimit ) {
				_log( logMessages.retryLimitReached );
				return doEventReject( event );
			}

			// Not <= because we counted.
			urlParamRetryAllowed = retryCount < retryLimit ? 1 : 0;

			SSE[ workerId ].close();
			// Only log, retry automatically.
			_log( extractEventData( event.data )?.logMsg, 1 );

			// Start at 1 because when we hit 1 we immediately start
			let retryTick = 1;
			const retryTicker = setInterval( () => {
				const countDown = ( retryTimeout / 1000 ) - retryTick++,
					  lastTick  = countDown < 2;

				_log( logMessages.retryCountdown.replace( '%d', countDown ), lastTick ? 1 : 0 );

				if ( lastTick )
					clearInterval( retryTicker );
			}, 1000 );

			setTimeout( startSSE, retryTimeout );
		}

		SSE[ workerId ].addEventListener( 'tsfem-e-transport-log', event => {
			_log( extractEventData( event.data )?.content );
		} );
		SSE[ workerId ].addEventListener( 'tsfem-e-transport-done', doEventResolve );

		SSE[ workerId ].addEventListener( 'tsfem-e-transport-failure', doEventReject );
		SSE[ workerId ].addEventListener( 'tsfem-e-transport-locked', doEventReject );

		SSE[ workerId ].addEventListener( 'tsfem-e-transport-crash', doEventRetry );
		SSE[ workerId ].addEventListener( 'tsfem-e-transport-timeout', doEventRetry );

		SSE[ workerId ].addEventListener( 'tsfem-e-transport-die', doEventDie );
	}

	startSSE();
}
