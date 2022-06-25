/**
 * This file holds Worker core code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfem_worker values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 2.6.0
 *
 * @constructor
 */
window.tsfem_worker = function() {

	const workers       = {},
		  activeWorkers = {};

	/**
	 * Sets Worker status by ID.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @function
	 * @param {String} id
	 * @param {String|undefined} to Either 'busy' or anything else.
	 */
	const setWorkerStatus = ( id, to ) => {
		if ( 'busy' === to ) {
			activeWorkers[ id ] = true;
		} else {
			delete activeWorkers[ id ];
		}
	}

	/**
	 * Occupies Worker by ID.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const occupyWorker = id => setWorkerStatus( id, 'busy' );

	/**
	 * Deoccupies Worker by ID.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const freeWorker = id => setWorkerStatus( id, 'clear' );

	/**
	 * Tells Worker status by ID.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @param {String} id
	 * @return {Boolean}
	 */
	const isWorkerBusy = id => id in activeWorkers;

	/**
	 * Assigns a new Worker by ID.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @function
	 * @param {String} file
	 * @param {String} id
	 * @return {Worker}
	 */
	const spawnWorker = ( file, id ) => workers[ id ] = new Worker( file );

	/**
	 * Returns an active Worker by ID.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 * @return {Worker|void}
	 */
	const getWorker = id => id in workers && workers[ id ] || void 0;

	/**
	 * Stops Worker by ID.
	 *
	 * Worker needs to be respawned after terminated. Alternatively, use despawnWorker.
	 * @see tsfem_worker.spawnWorker()
	 * @see tsfem_worker.despawnWorker()
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const stopWorker = id => {
		if ( workers[ id ] ) {
			workers[ id ].terminate();
			freeWorker( id );
		}
	}

	/**
	 * Stops and removes Worker by ID.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @function
	 * @param {String} id
	 */
	const despawnWorker = id => {
		if ( workers[ id ] ) {
			stopWorker( id );
			delete workers[ id ];
		}
	}

	/**
	 * Tells worker to process input data via postMessage, and waits for first message.
	 *
	 * @since 2.6.0
	 * @access public
	 * @see tsfem_worker.assignWorker() for custom message handling.
	 *
	 * @function
	 * @param {String}    id        The worker ID.
	 * @param {*}         data      Data to send to the worker.
	 * @return {Promise} The promise object.
	 */
	const tellWorker = ( id, data ) => new Promise( ( resolve, reject ) => {
		const worker = getWorker( id );

		if ( ! worker ) return reject( 'No worker available.' );

		worker.onmessage = oEvent => {
			if ( 'error' in oEvent.data ) {
				// debug && console.log( oEvent.data.error );
				console.log( oEvent.data.error ); // DEBUG: phase this out later?
				return reject( oEvent.data.error );
			}
			return resolve( oEvent.data );
		};
		worker.onerror = error => {
			// debug && console.log( error );
			console.log( error ); // DEBUG: phase this out later?
			return reject( error );
		};

		worker.postMessage( { id, data } );
	} );

	/**
	 * Assigns worker to process input data via postMessage.
	 *
	 * @since 2.6.0
	 * @access public
	 * @see tsfem_worker.tellWorker() for default message handling.
	 *
	 * @function
	 * @param {String}   id        The worker ID.
	 * @param {*}        data      Data to send to the worker.
	 * @param {Function} onmessage The custom message function to bind.
	 * @param {Function} onerror   The custom error function to bind.
	 */
	const assignWorker = ( id, data, onmessage, onerror ) => {
		const worker = getWorker( id );

		if ( ! worker ) return onerror( 'No worker available.' );

		worker.onmessage = onmessage;
		worker.onerror = onerror;

		worker.postMessage( { id, data } );
	};

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 *
		 * @since 2.6.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {}
	}, {
		/**
		 * Constant functions.
		 * Don't overwrite these.
		 *
		 * @since 2.6.0
		 * @access public
		 */
		occupyWorker,
		freeWorker,
		isWorkerBusy,
		spawnWorker,
		getWorker,
		stopWorker,
		despawnWorker,
		tellWorker,
		assignWorker,
	} );
}();
window.tsfem_worker.load();
