/**
 * This file holds the TSFEM's TSF new installer JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * This file is for WP>=5.5
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://theseoframework.com/extension-manager/>
 * @source <https://github.com/sybrew/the-seo-framework/tree/3.2.0/lib/js/installer>
 */

'use strict';

/* global pagenow */

/**
 * Hooks into WordPress's updates handler.
 * This is a self-constructed function assigned as an object.
 *
 * @since 2.5.0
 *
 * @constructor
 * @param {jQuery} $  jQuery object.
 * @param {object}  wp WP object.
 */
window.tsfinstaller = function( $, wp ) {

	const $document = $( document );

	const { __, _x, sprintf } = wp.i18n;

	/**
	  * Updates the UI appropriately after a successful TSFEM install.
	  *
	  * @since 2.5.0
	  * @credit wp.updates.installImporterSuccess
	  *
	  * @function
	  * @typedef {object} installTsfSuccess
	  * @param {object} response             Response from the server.
	  * @param {string} response.slug        Slug of the installed plugin.
	  * @param {string} response.pluginName  Name of the installed plugin.
	  * @param {string} response.activateUrl URL to activate the just installed plugin.
	  *                                      FIXME? This property can be empty if the plugin is already activated...
	  *                                      probably due to reinstallation via this script: Mega-edge-case.
	  */
	const installTsfSuccess = response => {

		let $button = $( `[data-slug="${response?.slug}"]` );

		// if ( ! response.activateUrl ) {
		// 	$button.remove();
		// } else {
		$button
			.removeClass( 'install-now installed button-disabled updating-message' )
			.addClass( 'activate-now' )
			.attr( {
				href: response.activateUrl + '&from=plugins',
				'aria-label': sprintf(
					/* translators: %s: Plugin name. */
					'plugins-network' === pagenow ? _x( 'Network Activate %s', 'plugin' ) : _x( 'Activate %s', 'plugin' ),
					response.pluginName
				)
			} )
			.text( 'plugins-network' === pagenow ? __( 'Network Activate' ) : __( 'Activate' ) );

		let $successButton = $button.clone()[0].outerHTML;

		wp.updates.addAdminNotice( {
			id:        'install-success',
			className: 'notice-success is-dismissible',
			message:   __( 'Installation completed successfully.' ) + ' ' + $successButton,
		} );

		wp.a11y.speak( __( 'Installation completed successfully.' ) );

		$document.trigger( 'tsfem-tsf-install-success', response );
	}

	/**
	 * Updates the UI appropriately after a failed TSF install.
	 *
	 * @since 2.5.0
	 * @credit wp.updates.installImporterError
	 *
	 * @function
	 * @typedef {object} installTsfError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the plugin to be installed.
	 * @param {string=} response.pluginName   Optional. Name of the plugin to be installed.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	const installTsfError = response => {
		let errorMessage = sprintf(
				/* translators: %s: Error string for a failed installation. */
				__( 'Installation failed: %s' ),
				response.errorMessage
			),
			$installLink = $( `[data-slug="${response?.slug}"]` ),
			pluginName   = $installLink.data( 'name' );

		if ( ! wp.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( wp.updates.maybeHandleCredentialError( response, 'install-plugin' ) ) {
			return;
		}

		wp.updates.addAdminNotice( {
			id:        response.errorCode,
			className: 'notice-error is-dismissible',
			message:   errorMessage
		} );

		// This will insert a non-AJAX-enabled (direct) fallback link.
		$installLink
			.removeClass( 'updating-message' )
			.attr(
				'aria-label',
				sprintf(
					/* translators: %s: Plugin name. */
					_x( 'Install %s now', 'plugin' ),
					pluginName
				)
			)
			.text( __( 'Install Now' ) );

		wp.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'tsfem-tsf-install-error', response );
	}

	/**
	 * Adds installation hooks on DOMContentLoaded.
	 *
	 * TODO rewrite as a standalone, being independent from WP's broken update.js?
	 *
	 * @since 2.5.0
	 *
	 * @function
	 * @param {event} event
	 */
	const onReady = ( event ) => {

		if ( ! wp || ! wp.updates ) return;

		let prev_addCallbacks = wp.updates._addCallbacks;

		/**
		 * Hooks into the installation button, to prevent redirect.
		 *
		 * WordPress normally enforces a redirect when the actionable page uses index.php.
		 * Indicating that it's not a "valid" installation page. Which is odd, as
		 * it perfectly allows any other action. Plus it allows this action on any other page,
		 * but the admin "main === index.php" dashboard page, too.
		 *
		 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/js/updates.js#L2395-L2415
		 */
		$( '#plugin_install_from_iframe' ).on( 'click', function( event ) {
			let target = window.parent === window ? null : window.parent,
				install;

			// Let the default handler take over.
			if ( -1 === window.parent.location.pathname.indexOf( 'index.php' ) )
				return;

			// Only enact when the slug matches.
			if ( $( this ).data( 'slug' ) !== tsfinstallerL10n.slug )
				return;

			$.support.postMessage = !! window.postMessage;

			if ( false === $.support.postMessage || null === target ) {
				return;
			}

			event.preventDefault();

			install = {
				action: 'install-plugin',
				data:   {
					slug: $( this ).data( 'slug' )
				}
			};

			target.postMessage( JSON.stringify( install ), window.location.origin );
		} );

		// Direct attach as WP is using preventDefault() when capturing.
		$( '#tsfem-tsf-tb' ).on( 'click', () => {
			let canReset = false;

			/**
			 * Overwrite installer callback catcher.
			 * This is duplicated code, basically, to revert whatever WP's installer was doing.
			 */
			wp.updates._addCallbacks = ( data, action ) => {
				if ( 'install-plugin' === action && tsfinstallerL10n.slug === data.slug ) {
					data.success = installTsfSuccess;
					data.error   = installTsfError;

					let $button = $( `[data-slug="${data.slug}"]` );

					$button
						.addClass( 'updating-message' )
						.attr(
							'aria-label',
							sprintf(
								/* translators: %s: Plugin name and version. */
								_x( 'Installing %s...', 'plugin' ),
								$button.data( 'name' )
							)
						)
						.text( __( 'Installing...' ) );

					canReset = true;
				}

				return data;
			}

			// Thread lightly: Pure magic below.
			$( window ).on( 'message', event => {
				let message;
				try {
					message = JSON.parse( event.originalEvent.data );
				} catch ( e ) {
					return;
				}
				if ( ! message || 'undefined' === typeof message.action ) {
					return;
				}
				if ( message.action === 'install-plugin' ) {
					// Fail safe.
					canReset = false;
				} else {
					// Fail secure.
					canReset = true;
				}
			} );
			let resetTicker, cbs;
			const resetCb = () => {
				wp.updates._addCallbacks = prev_addCallbacks;
				clearInterval( resetTicker );
				$document.off( cbs, resetCb );
			}
			const checkReset = () => {
				canReset && resetCb();
			}
			const prepareReset = () => {
				resetTicker = setInterval( checkReset, 100 );
				setTimeout( resetCb, 750 );
			}
			cbs = 'wp-plugin-installing wp-plugin-install-error wp-plugin-install-success';
			// Fail secure.
			$( 'body' ).one( 'thickbox:removed', prepareReset );
			$document.one( cbs, resetCb );
		} );

		$document.on( 'click', '#tsfem-tsf-install', event => {
			let $button = $( event.target );

			if ( $button.hasClass( 'activate-now' ) )
				return; // Follow link, activating the plugin.

			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) )
				return;

			if ( $button.html() !== __( 'Installing...' ) )
				$button.data( 'originaltext', $button.html() );

			$button
				.addClass( 'updating-message' )
				.attr(
					'aria-label',
					sprintf(
						/* translators: %s: Plugin name and version. */
						_x( 'Installing %s...', 'plugin' ),
						$button.data( 'name' )
					)
				)
				.text( __( 'Installing...' ) );

			if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
				wp.updates.requestFilesystemCredentials( event );

				$document.on( 'credential-modal-cancel', () => {
					$button
						.removeClass( 'updating-message' )
						.attr(
							'aria-label',
							sprintf(
								/* translators: %s: Plugin name. */
								_x( 'Install %s now', 'plugin' ),
								pluginName
							)
						)
						.text( __( 'Install Now' ) );

					wp.a11y.speak( __( 'Update canceled.' ) );
				} );
			}

			wp.updates.installPlugin( {
				slug:    $button.data( 'slug' ),
				pagenow: pagenow,
				success: installTsfSuccess,
				error:   installTsfError
			} );
		} );
	}

	return {
		/**
		 * Runs this script on DOMContentLoaded when WordPress Shiny Updates is
		 * available.
		 *
		 * @since 2.5.0
		 *
		 * @function
		 */
		load: () => {
			$( onReady );
		}
	};
}( jQuery, window.wp );
window.tsfinstaller.load();
