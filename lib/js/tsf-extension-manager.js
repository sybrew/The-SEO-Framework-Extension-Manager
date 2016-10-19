/**
 * This file holds The SEO Framework Extension Manager plugin's JS code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @pluginURI https://wordpress.org/plugins/the-seo-framework-extension-manager/
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @output_file_name tsf-extension-manager.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @js_externs /** @constructor */ function tsfem() {}; /** @type {Array|string} */ var tsfemL10n;
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

/* global tsfem, tsfemL10n  */

/**
 * Advanced Optimizations caused me to move away from dot annotations, as they
 * get wrongfully minified.
 */

/**
 * Holds tsfem values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window[ 'tsfem' ] = {

	/**
	 * @since 1.0.0
	 * @param {String} nonce Ajax nonce
	 */
	nonce : tsfemL10n['nonce'],

	/**
	 * @since 1.0.0
	 * @param {Array} i18n Localized strings
	 */
	i18n : tsfemL10n['i18n'],

	/**
	 * Initializes the description balloon hover and click actions.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	initDescHover: function() {
		'use strict';

		var elem = '.tsfem-has-hover-balloon',
			$item = jQuery( elem );

		//* Force delagation.
		$item.children( '*' ).on( {
			mouseenter: function( event ) {
				jQuery( event.target ).parents( elem ).trigger( 'mouseenter' );
			},
			mousemove: function( event ) {
				jQuery( event.target ).parents( elem ).trigger( 'mousemove', event.pageX );
			},
			mouseleave: function( event ) {
				jQuery( event.target ).parents( elem ).trigger( 'mouseleave' );
			},
		} );

		/**
		 * mouseout is required for when hovering through the balloon on non-bubbled items.
		 * mouseleave is in favor of mouseout for bubbled and/or propagated items to prevent flickering.
		 */
		$item.on( {
			'mouseenter' : tsfem.enterDescHover,
			'mousemove'  : tsfem.moveDescHover,
			'mouseleave' : tsfem.leaveDescHover,
			'mouseout'   : tsfem.leaveDescHover,
		} );

		jQuery( document.body ).on( 'click touchstart MSPointerDown', tsfem.touchLeaveDescHover );
	},

	/**
	 * Animates the description balloon and arrow on mouse or touch enter.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 * @return {Boolean} false If event is propagated.
	 */
	enterDescHover: function( event ) {
		'use strict';

		var $item = jQuery( event.target );

		if ( ! $item.hasClass( 'tsfem-has-hover-balloon' ) )
			return false;

		var desc = $item.data( 'desc' );

		// Only run if a desc is present and no balloon has yet been added.
		if ( desc && 0 === $item.children( 'div.tsfem-desc-balloon' ).length ) {

			// Remove default browser title behavior as this replaces it.
			// No removeProp(): "Do not use this method to remove native properties."
			$item.prop( 'title', '' );

			$item.append( '<div class="tsfem-desc-balloon"><span>' + desc + '</span><div></div></div>' );

			var $balloon = $item.children( 'div.tsfem-desc-balloon' ),
				height = $item.outerHeight() + 8;

			$balloon.css( 'bottom', height + 'px' );

			var $wrap = $balloon.closest( '.tsfem-pane-content' ),
				$text = $balloon.children( 'span' );

			// Fix overflow right to the right. To fix left we'd have to substract width.
			// But that's not needed, yet.
			if ( $wrap !== 'undefined' && $wrap.length > 0 ) {
				var wrapOffset = $wrap.offset().left + $wrap.outerWidth(),
					textOffset = $text.offset().left + $text.outerWidth();

				// 20 = padding
				if ( textOffset > ( wrapOffset - 20 ) ) {
					$balloon.css( 'left', ( wrapOffset - textOffset - 20 ) + 'px' );
				}
			}
		}
	},

	/**
	 * Animates the description balloon arrow on mouse move.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 * @param {Integer} pageX Page X location from trigger.
	 * @return {Boolean} false If event is propagated.
	 */
	moveDescHover: function( event, pageX ) {
		'use strict';

		var $item = jQuery( event.target );

		if ( ! $item.hasClass( 'tsfem-has-hover-balloon' ) )
			return false;

		var desc = $item.data( 'desc' );

		if ( desc ) {
			var $balloon = $item.children( 'div.tsfem-desc-balloon' ),
				$text = $balloon.children( 'span' ),
				$arrow = $balloon.children( 'div' ),
				halfArrow = $arrow.outerWidth() / 2,
				cpageX = pageX ? pageX : event.pageX,
				textOffset = $text.offset().left,
				mousex = cpageX - textOffset - halfArrow;

			if ( mousex < 1 ) {
				// Overflown left.
				$arrow.css( 'left', "0px" );
			} else {
				var width = $text.outerWidth(),
					maxOffset = textOffset + width - halfArrow;

				if ( cpageX > maxOffset ) {
					// Overflown right.
					var pos = width - halfArrow - ( ( width - $text.width() ) / 2 );

					$arrow.css( 'left', pos + "px" );
				} else {
					// In-between.
					var offset = mousex + ( ( width - $text.width() ) / 2 ) - halfArrow;

					$arrow.css( 'left', offset + "px" );
				}
			}
		}
	},

	/**
	 * Removes the description balloon and arrow on mouse leave.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 * @return {Boolean} false If event is propagated.
	 */
	leaveDescHover: function( event ) {
		'use strict';

		var $item = jQuery( event.target );

		if ( ! $item.hasClass( 'tsfem-has-hover-balloon' ) )
			return false;

		jQuery( event.target ).find( 'div.tsfem-desc-balloon' ).remove();
	},

	/**
	 * Removes the description balloon and arrow at aside touches or clicks.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 */
	touchLeaveDescHover : function() {
		'use strict';

		var $target = jQuery( document.body.target ),
			$item = jQuery( '.tsfem-has-hover-balloon' );

		if ( ! $target.closest( $item ).length )
			$item.find( 'div.tsfem-desc-balloon' ).remove();
	},

	/**
	 * Visualizes AJAX loading time through target class change.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {String} target
	 */
	setAjaxLoader: function( target ) {
		'use strict';

		jQuery( target ).toggleClass( 'tsfem-loading' );
	},

	/**
	 * Adjusts class loaders on Ajax response.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {String} target
	 * @param {Integer} success
	 * @param {String} notice
	 * @param {Integer} html
	 */
	unsetAjaxLoader: function( target, success, notice, html ) {
		'use strict';

		var newclass = 'tsfem-success',
			fade = 2500;

		if ( ! success ) {
			newclass = 'tsfem-error';
			fade = html ? 20000 : 10000;
		} else if ( 2 === success ) {
			newclass = 'tsfem-unknown';
			fade = 7500;
		}

		//* Slow down if there's a notice.
		fade = notice ? fade * 2 : fade;

		if ( html ) {
			jQuery( target ).removeClass( 'tsfem-loading' ).addClass( newclass ).html( notice ).fadeOut( fade );
		} else {
			jQuery( target ).removeClass( 'tsfem-loading' ).addClass( newclass ).text( notice ).fadeOut( fade );
		}
	},

	/**
	 * Cleans and resets Ajax wrapper class and contents to default.
	 * Also stops any animation and resets fadeout to beginning.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {String} target
	 */
	resetAjaxLoader: function( target ) {
		'use strict';

		jQuery( target ).stop().empty().prop( 'class', 'tsfem-ajax' ).css( { 'opacity' : '1', 'display' : 'initial' } ).prop( 'style', '' );
	},

	/**
	 * Updates the feed option.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {jQuery.event} event
	 */
	updateFeed: function( event ) {
		'use strict';

		var disabled = 'tsfem-button-disabled',
			$button = jQuery( event.target ),
			loader = '#tsfem-feed-ajax',
			status = 0;

		if ( $button.prop( 'disabled' ) )
			return;

		$button.addClass( disabled );
		$button.prop( 'disabled', true );

		//* Reset ajax loader
		tsfem.resetAjaxLoader( loader );

		//* Set ajax loader.
		tsfem.setAjaxLoader( loader );

		//* Setup external update.
		var settings = {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				'action' : 'tsfem_enable_feeds',
				'nonce' : tsfem.nonce,
			},
			timeout: 10000,
			async: true,
			success: function( response ) {

				response = jQuery.parseJSON( response );

				if ( 'success' === response.type && response.content ) {
					status = 1;
					jQuery( '.tsfem-trends-wrap' ).empty().css( 'opacity', 0 ).append( response.content ).css( 'opacity', 1 ).find( '.tsfem-feed-wrap' ).css(
						{ 'opacity' : 0, 'max-height' : '0px' }
					).animate(
						{ 'max-height' : '3000px', 'opacity' : 1 },
						{ queue: true, duration: 2000 },
						'swing'
					);
					setTimeout( function() { tsfem.updatedResponse( loader, status, '', 0 ); }, 1000 );
				} else if ( 'unknown' === response.type ) {
					status = 2;
					jQuery( '.tsfem-trends-wrap' ).empty().append( response.content );
					tsfem.updatedResponse( loader, status, '', 0 );
				} else {
					$button.removeClass( disabled );
					$button.prop( 'disabled', false );
					tsfem.updatedResponse( loader, status, '', 0 );
				}
			},
			error: function() {
				$button.removeClass( disabled );
				$button.prop( 'disabled', false );
				tsfem.updatedResponse( loader, status, '', 0 );
			},
		}

		jQuery.ajax( settings );
	},

	/**
	 * Updates the selected extension state.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 */
	updateExtension: function( event ) {
		'use strict';

		var disabled = 'tsfem-button-disabled',
			$button = jQuery( event.target ),
			$buttons = jQuery( '.tsfem-button-extension-activate, .tsfem-button-extension-deactivate' ).not( jQuery( '.' + disabled ) ),
			loader = '#tsfem-extensions-ajax',
			actionSlug = $button.data( 'slug' ),
			actionCase = $button.data( 'case' );

		if ( $button.prop( 'disabled' ) )
			return;

		//* Disable buttons
		$buttons.map( function() {
			jQuery( this ).addClass( disabled );
			jQuery( this ).prop( 'disabled', true );
		} );

		//* Reset ajax loader
		tsfem.resetAjaxLoader( loader );

		//* Set ajax loader.
		tsfem.setAjaxLoader( loader );

		//* Setup external update.
		var settings = {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				'action' : 'tsfem_update_extension',
				'nonce' : tsfem.nonce,
				'slug' : actionSlug,
				'case' : actionCase,
			},
			timeout: 10000,
			async: true,
			success: function( response ) {

				response = jQuery.parseJSON( response );

				if ( 'undefined' === typeof response.status || 'undefined' === typeof response.status['success'] || 'undefined' === typeof response.status['notice'] ) {
					//* Erroneous input.
					tsfem.updatedResponse( loader, 0, '', 0 );
				} else {

					var status = response.status['success'],
						notice = response.status['notice'];

					if ( -1 === status ) {
						//* Erroneous input.
						tsfem.updatedResponse( loader, 0, notice, 0 );
					} else {
						if ( 'activate' === actionCase ) {
							if ( false === status ) {
								/**
								 * Not activated as no extension has been put in.
								 * This should never happen.
								 */
								tsfem.updatedResponse( loader, 0, notice, 0 );
							} else {
								switch ( status ) {
									case 10001 :
										//* No extensions checksum found.
										tsfem.updatedResponse( loader, 0, notice, 0 );
										break;

									case 10002 :
										//* Extensions checksum mismatch.
										tsfem.updatedResponse( loader, 0, notice, 0 );
										break;

									case 10003 :
										//* Account isn't allowed to use premium extension.
										tsfem.updatedResponse( loader, 0, notice, 0 );
										break;

									case 10004 :
										//* Extension caused fatal error.
										tsfem.updatedResponse( loader, 0, notice, 1 );
										//* Update hover cache.
										tsfem.initDescHover();
										break;

									case 10005 :
										//* Option update failed for unknown reason. Maybe overload.
										tsfem.updatedResponse( loader, 2, notice, 0 );
										break;

									default :
										//* Extension is activated.
										$button.removeClass( 'tsfem-button-extension-activate' );
										$button.addClass( 'tsfem-button-extension-deactivate' );
										$button.data( 'case', 'deactivate' );
										$button.text( tsfem.i18n['Deactivate'] );
										tsfem.updatedResponse( loader, 1, notice, 0 );
										break;
								}
							}
						} else if ( 'deactivate' === actionCase ) {
							if ( false === status ) {
								//* Not deactivated.
								tsfem.updatedResponse( loader, 0, notice, 0 );
							} else {
								//* Deactivated.
								$button.removeClass( 'tsfem-button-extension-deactivate' );
								$button.addClass( 'tsfem-button-extension-activate' );
								$button.data( 'case', 'activate' );
								$button.text( tsfem.i18n['Activate'] );
								tsfem.updatedResponse( loader, 1, notice, 0 );
							}
						} else {
							//* Erroneous input.
							tsfem.updatedResponse( loader, 0, '', 0 );
						}
					}
				}
			},
			error: function() {
				tsfem.updatedResponse( loader, 0, '', 0 );
			},
			complete: function() {
				$buttons.removeClass( disabled );
				$buttons.prop( 'disabled', false );
			},
		}

		jQuery.ajax( settings );
	},

	/**
	 * Visualizes the AJAX response to the user.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {String} target
	 * @param {Integer} success 0 = error, 1 = success, 2 = unknown but success.
	 * @param {String} notice The updated notice.
	 * @param {Integer} html
	 */
	updatedResponse: function( target, success, notice, html ) {
		'use strict';

		switch ( success ) {
			case 0 :
				tsfem.unsetAjaxLoader( target, 0, notice, html );
				break;
			case 1 :
				tsfem.unsetAjaxLoader( target, 1, notice, html );
				break;
			case 2 :
				tsfem.unsetAjaxLoader( target, 2, notice, html );
				break;
			default :
				tsfem.resetAjaxLoader( target );
				break;
		}
	},

	/**
	 * Prevents browser default actions.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 */
	preventDefault: function( event ) {
		'use strict';

		event.preventDefault();
		event.stopPropagation();
	},

	/**
	 * Resets switcher button to original state if clicked outside of its wrap.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 * @param {Object} event jQuery event
	 */
	resetSwitcher: function( event ) {
		'use strict';

		var $switcher = jQuery( '.tsfem-switch-button-container > input[type="checkbox"]:checked' );

		if ( 'undefined' !== typeof $switcher && $switcher.length > 0 ) {
			var $wrap = $switcher.parents( '.tsfem-switch-button-container-wrap' );

			if ( jQuery( event.target ).closest( $wrap ).length < 1 ) {
				$switcher.prop( 'checked', false );
			}
		}
	},

	/**
	 * Sets last uneven extension wrapper to be the same width as the first.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	setLastExtensionEntry: function() {

		var $extensions = jQuery( '.tsfem-extensions-overview-content' ).children( '.tsfem-extension-entry-wrap' ),
			amount = $extensions.length;

		if ( amount % 2 && amount > 2 ) {
			//* Uneven amount.
			var $first = $extensions.first(),
				$last = $extensions.last();

			$last.css( { 'max-width' : $first.width() } );
		}
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} jQ jQuery
	 * @function
	 */
	ready: function( jQ ) {
		'use strict';

		// Move the page updates notices below the top-wrap.
		jQ( 'div.updated, div.error, div.notice-warning' ).insertAfter( 'section.tsfem-top-wrap' );

		// AJAX feed update.
		jQ( 'a#tsfem-enable-feeds' ).on( 'click', tsfem.updateFeed );

		// AJAX extension update.
		jQ( 'a.tsfem-button-extension-activate, a.tsfem-button-extension-deactivate' ).on( 'click', tsfem.updateExtension );

		// AJAX on-heartbeat active extension check to update buttons accordingly. @TODO
		//jQ( document ).on( 'heartbeat-tick', tsfem.checkExtensions );

		// Disable semi-disabled buttons.
		jQ( 'a.tsfem-button-disabled' ).on( 'click', tsfem.preventDefault );

		// Initialize the balloon hover effects.
		jQ( document.body ).ready( tsfem.initDescHover );

		// Proportionate uneven amounts of extension entry boxes.
		jQ( document.body ).ready( tsfem.setLastExtensionEntry );
		jQ( window ).on( 'resize orientationchange', tsfem.setLastExtensionEntry );

		// Reset switcher button to default on non-click.
		jQ( window ).on( 'click touchend', tsfem.resetSwitcher );
	}
};
jQuery( tsfem.ready );
