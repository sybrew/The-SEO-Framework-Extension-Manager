/**
 * This file holds The SEO Framework Extension Manager plugin's JS code.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/the-seo-framework-extension-manager/>
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
// @language ECMASCRIPT6_STRICT
// @language_out ECMASCRIPT5_STRICT
// @output_file_name tsfem.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/externs/tsfem.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

'use strict';

/**
 * Holds tsfem values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window.tsfem = {

	/**
	 * @since 1.0.0
	 * @access private
	 * @type {string|null} nonce Ajax nonce
	 */
	nonce : tsfemL10n.nonce,

	/**
	 * @since 1.0.0
	 * @access private
	 * @param {object|null} i18n Localized strings
	 */
	i18n : tsfemL10n.i18n,

	/**
	 * @since 1.0.0
	 * @since 1.3.0 Now public.
	 * @access public
	 * @param {boolean|undefined|null} rtl RTL enabled
	 */
	rtl : tsfemL10n.rtl,

	/**
	 * @since 1.0.0
	 * @since 1.3.0 Now public.
	 * @access public
	 * @param {boolean|undefined|null} debug Debugging enabled
	 */
	debug : tsfemL10n.debug,

	/**
	 * @since 1.0.0
	 * @since 1.3.0 Now public.
	 * @access public
	 * @param {boolean} touchBuffer Maintains touch-buffer
	 */
	touchBuffer : false,

	/**
	 * @since 1.3.0
	 * @access private
	 * @param {boolean} noticeBuffer Maintains notice loader buffer
	 */
	noticeBuffer : false,

	/**
	 * @since 1.3.0
	 * @access private
	 * @param {boolean} navWarn Whether to warn the user on navigation.
	 */
	navWarn : false,

	/**
	 * Sets touch buffer to set ms. After which it resets.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Now public.
	 * @access public
	 *
	 * @function
	 * @param {number} ms The touch buffer in miliseconds.
	 * @return {undefined}
	 */
	setTouchBuffer: function( ms ) {

		tsfem.touchBuffer = true;

		setTimeout( function() {
			tsfem.touchBuffer = false;
		}, ms );
	},

	/**
	 * Initializes status bar hover entries.
	 *
	 * @since 1.5.0
	 *
	 * @function
	 * @return {undefined}
	 */
	_initToolTips: function() {

		let touchBuffer = 0,
			inTouchBuffer = false,
			passiveSupported = false;

		/**
		 * Sets passive support flag.
		 * @link https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
		 */
		try {
			let options = Object.defineProperty( {}, 'passive', {
				get: function() { passiveSupported = true; }
			} );
			window.addEventListener( 'tsfem-test-passive', options, options )
				.removeEventListener( 'tsfem-test-passive', options, options );
		} catch( err ) {}

		const setTouchBuffer = function() {
			inTouchBuffer = true;
			clearTimeout( touchBuffer );
			touchBuffer = setTimeout( function() {
				inTouchBuffer = false;
			}, 250 );
		}

		const setEvents = function( target, unset ) {

			unset = unset || false;

			let touchEvents = 'pointerdown.tsfemTT touchstart.tsfemTT click.tsfemTT',
				$target = jQuery( target );

			if ( unset ) {
				$target.off( 'mousemove mouseleave mouseout tsfem-tooltip-update' );
				jQuery( document.body ).off( touchEvents );
			} else {
				$target.on( {
					'mousemove'  : mouseMove,
					'mouseleave' : mouseLeave,
					'mouseout'   : mouseLeave,
				} );
				jQuery( document.body ).off( touchEvents ).on( touchEvents, touchRemove );
			}

			$target.on( 'tsfem-tooltip-update', updateDesc );
		}
		const unsetEvents = function( target ) {
			setEvents( target, true );
		}
		const updateDesc = function( event ) {
			if ( event.target.classList.contains( 'tsfem-tooltip-item' ) ) {
				let tooltipText = event.target.querySelector( '.tsfem-tooltip-text' );
				if ( tooltipText instanceof Element )
					tooltipText.innerHTML = event.target.dataset.desc;
			}
		}
		const mouseEnter = function( event ) {
			let $hoverItem = jQuery( event.target ),
				desc = event.target.dataset.desc || event.target.title || '';

			if ( desc && 0 === event.target.getElementsByClassName( 'tsfem-tooltip' ).length ) {
				//= Exchanges data-desc with found desc to sustain easy access.
				event.target.dataset.desc = desc;
				//= Clear title to prevent default browser tooltip.
				event.target.title = '';

				tsfem.doTooltip( event, event.target, desc );
			}
		}
		const mouseMove = function( event ) {
			let $target = jQuery( event.target ),
				$tooltip = $target.find( '.tsfem-tooltip' ),
				$arrow = $tooltip.find( '.tsfem-tooltip-arrow' ),
				pagex = event.originalEvent && event.originalEvent.pageX || event.pageX, // iOS touch support
				arrowBoundary = 7,
				arrowWidth = 16,
				$hoverItemWrap = $target.closest( '.tsfem-tooltip-wrap' ),
				mousex = pagex - $hoverItemWrap.offset().left - arrowWidth / 2,
				originalMousex = mousex,
				$textWrap = $tooltip.find( '.tsfem-tooltip-text-wrap' ),
				textWrapWidth = $textWrap.outerWidth( true ),
				adjust = $tooltip.data( 'adjust' ),
				adjustDir = $tooltip.data( 'adjustDir' ),
				boundaryRight = textWrapWidth - arrowWidth - arrowBoundary;

			//= mousex is skewed, adjust.
			adjust = parseInt( adjust, 10 );
			adjust = isNaN( adjust ) ? 0 : Math.round( adjust );
			if ( adjust ) {
				adjust = 'left' === adjustDir ? -adjust : adjust;
				mousex = mousex + adjust;

				//= Use textWidth for right boundary if adjustment exceeds.
				if ( boundaryRight - adjust > $hoverItemWrap.outerWidth( true ) ) {
					let $innerText = $textWrap.find( '.tsfem-tooltip-text' ),
						textWidth = $innerText.outerWidth( true );
					boundaryRight = textWidth - arrowWidth - arrowBoundary;
				}
			}

			if ( mousex <= arrowBoundary ) {
				//* Overflown left.
				$arrow.css( 'left', arrowBoundary + "px" );
			} else if ( mousex >= boundaryRight ) {
				//* Overflown right.
				$arrow.css( 'left', boundaryRight + "px" );
			} else {
				//= Somewhere in the middle.
				$arrow.css( 'left', mousex + "px" );
			}
		}
		const mouseLeave = function( event ) {
			//* @see touchMove
			if ( inTouchBuffer )
				return;

			tsfem.removeTooltip( event.target );
			unsetEvents( event.target );
		}
		/**
		 * ^^^
		 * These two methods conflict eachother in EdgeHTML.
		 * Thusly, touch buffer.
		 * vvv
		 */
		const touchRemove = function( event ) {

			//* @see mouseLeave
			setTouchBuffer();

			let itemSelector = '.tsfem-tooltip-item',
				balloonSelector = '.tsfem-tooltip';

			let $target = jQuery( event.target ),
				$keepBalloon;

			if ( $target.hasClass( 'tsfem-tooltip-item' ) ) {
				$keepBalloon = $target.find( balloonSelector );
			}
			if ( ! $keepBalloon ) {
				let $children = $target.children( itemSelector );
				if ( $children.length ) {
					$keepBalloon = $children.find( balloonSelector );
				}
			}

			if ( $keepBalloon && $keepBalloon.length ) {
				//= Remove all but this.
				jQuery( balloonSelector ).not( $keepBalloon ).remove();
			} else {
				//= Remove all.
				jQuery( balloonSelector ).remove();
			}
		}

		/**
		 * Loads tooltips within wrapper.
		 * @function
		 * @param {Event} event
		 */
		const loadToolTip = function( event ) {

			if ( inTouchBuffer )
				return;

			let isTouch = false;

			switch ( event.type ) {
				case 'mouseenter' :
					//= Most likely, thus placed first.
					break;

				case 'pointerdown' :
				case 'touchstart' :
					isTouch = true;
					break;

				default :
					break;
			}

			//= Removes previous items and sets buffer.
			isTouch && touchRemove( event );

			mouseEnter( event );
			//= Initiate placement directly for Windows Touch or when overflown.
			mouseMove( event );

			// Set other events.
			setEvents( event.target );
		}

		/**
		 * Handles earliest stages of the tooltip.
		 *
		 * @param {Event} event
		 */
		const toolTipHandler = function( event ) {
			if ( event.target.classList.contains( 'tsfem-tooltip-item' ) ) {
				loadToolTip( event );
			}
			event.stopPropagation();
		}

		/**
		 * Initializes tooltips.
		 * @function
		 */
		const initTooltips = function() {
			let wraps = document.querySelectorAll( '.tsfem-tooltip-wrap' ),
				options = passiveSupported ? { capture: true, passive: true } : true;

			for ( let i = 0; i < wraps.length; i++ ) {
				'mouseenter pointerdown touchstart'.split( ' ' ).forEach( e => {
					wraps[i].removeEventListener( e, toolTipHandler, options );
					wraps[i].addEventListener( e, toolTipHandler, options );
				} );
			}
		}
		initTooltips();
		jQuery( window ).on( 'tsfem-reset-tooltips', initTooltips );

		tsfem.addTooltipBoundary( '#wpcontent' );
	},

	/**
	 * Loads tooltip.
	 *
	 * @since 1.3.0 : 1. Now adds indentation, when possible.
	 *                2. Now checks top collision, with scrollchecking, when possible.
	 * @since 1.5.0 : Rewritten to use the new TSF tooltips.
	 * @access public
	 *
	 * @function
	 * @param {(event|undefined)} event
	 * @param {Element} element
	 * @param {string} desc
	 * @return {undefined}
	 */
	doTooltip: function( event, element, desc ) {

		let $hoverItem = jQuery( element );

		let hasBalloon = $hoverItem.find( '.tsfem-tooltip' ).length;

		if ( hasBalloon ) {
			tsfem.removeTooltip( element );
		}

		if ( ! desc.length ) {
			return;
		}

		let $tooltip = jQuery(
				'<div class="tsfem-tooltip"><span class="tsfem-tooltip-text-wrap"><span class="tsfem-tooltip-text">'
					+ desc +
				'</span></span><div class="tsfem-tooltip-arrow"></div></div>'
			);
		$hoverItem.append( $tooltip );

		let $boundary = $hoverItem.closest( '.tsfem-tooltip-boundary' );
		$boundary = $boundary.length && $boundary || jQuery( document.body );

		//= arrow (8)
		let tooltipHeight = $hoverItem.outerHeight() + 8,
			tooltipTop = $tooltip.offset().top - tooltipHeight,
			boundaryTop = $boundary.offset().top - ( $boundary.prop( 'scrolltop' ) || 0 );

		if ( boundaryTop > tooltipTop ) {
			$tooltip.addClass( 'tsfem-tooltip-down' );
			$tooltip.css( 'top', tooltipHeight + 'px' );
		} else {
			$tooltip.css( 'bottom', tooltipHeight + 'px' );
		}

		let $hoverItemWrap = $hoverItem.closest( '.tsfem-tooltip-wrap' );
		if ( ! $hoverItemWrap.length )
			$hoverItemWrap = $hoverItem.parent();

		let $textWrap = $tooltip.find( '.tsfem-tooltip-text-wrap' ),
			$innerText = $textWrap.find( '.tsfem-tooltip-text' ),
			hoverItemWrapWidth = $hoverItemWrap.width(),
			textWrapWidth = $textWrap.outerWidth( true ),
			textWidth = $innerText.outerWidth( true ),
			textLeft = $textWrap.offset().left,
			textRight = textLeft + textWidth,
			boundaryLeft = $boundary.offset().left - ( $boundary.prop( 'scrollLeft' ) || 0 ),
			boundaryRight = boundaryLeft + $boundary.outerWidth();

		//= RTL and LTR are normalized to abide to left.
		let direction = 'left';

		if ( textLeft < boundaryLeft ) {
			//= Overflown over left boundary (likely window)
			//= Add indent relative to boundary. 24px width of arrow / 2 = 12 middle
			let horIndent = boundaryLeft - textLeft + 12,
				basis = parseInt( $textWrap.css( 'flex-basis' ), 10 );

			/**
			 * If the overflow is greater than the tooltip flex basis,
			 * the tooltip was grown. Shrink it back to basis and use that.
			 */
			if ( horIndent < -basis )
				horIndent = -basis;

			$tooltip.css( direction, horIndent + 'px' );
			$tooltip.data( 'adjust', horIndent );
			$tooltip.data( 'adjustDir', direction );
		} else if ( textRight > boundaryRight ) {
			//= Overflown over right boundary (likely window)
			//= Add indent relative to boundary minus text wrap width. Add 12px for visual appeal.
			let horIndent = boundaryRight - textLeft - textWrapWidth - 12,
				basis = parseInt( $textWrap.css( 'flex-basis' ), 10 );

			/**
			 * If the overflow is greater than the tooltip flex basis,
			 * the tooltip was grown. Shrink it back to basis and use that.
			 */
			if ( horIndent < -basis )
				horIndent = -basis;

			$tooltip.css( direction, horIndent + 'px' );
			$tooltip.data( 'adjust', horIndent );
			$tooltip.data( 'adjustDir', direction );
		} else if ( hoverItemWrapWidth < 42 ) {
			//= Small tooltip container. Add indent to make it visually appealing.
			let indent = -15;
			$tooltip.css( direction, indent + 'px' );
			$tooltip.data( 'adjust', indent );
			$tooltip.data( 'adjustDir', direction );
		} else if ( event && jQuery( event.target ).find( $tooltip ).length < 1 ) {
			//= Manually triggered event that doesn't overflow.
			$tooltip.css( direction, 0 + 'px' );
			$tooltip.data( 'adjust', 0 );
			$tooltip.data( 'adjustDir', direction );
		} else if ( hoverItemWrapWidth > textWrapWidth ) {
			//= Wrap is bigger than tooltip. Adjust accordingly.
			let pagex = event.originalEvent && event.originalEvent.pageX || event.pageX, // iOS touch support,
				hoverItemLeft = $hoverItemWrap.offset().left,
				center = pagex - hoverItemLeft,
				left = center - textWrapWidth / 2,
				right = left + textWrapWidth;

			if ( left < 0 ) {
				//= Don't overflow left.
				left = 0;
			} else if ( right > hoverItemWrapWidth ) {
				//= Don't overflow right.
				//* Use textWidth instead of textWrapWidth as it gets squashed in flex.
				left = hoverItemWrapWidth - textWidth;
			}

			$tooltip.css( direction, left + 'px' );
			$tooltip.data( 'adjust', left );
			$tooltip.data( 'adjustDir', direction );
		}
	},

	/**
	 * Adds tooltip boundaries.
	 *
	 * @since 1.5.0
	 *
	 * @function
	 * @param {!jQuery|Element|string} e The jQuery element, DOM Element or query selector.
	 * @return {undefined}
	 */
	addTooltipBoundary: function( e ) {
		jQuery( e ).addClass( 'tsfem-tooltip-boundary' );
	},

	/**
	 * Removes the description balloon and arrow from element.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @param {Element} element
	 * @return {undefined}
	 */
	removeTooltip: function( element ) {
		tsfem.getTooltip( element ).remove();
	},

	/**
	 * Returns the description balloon node form element.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @param {Element} element
	 * @return {jQuery.element}
	 */
	getTooltip: function( element ) {
		return jQuery( element ).find( '.tsfem-tooltip' ).first();
	},

	/**
	 * Visualizes AJAX loading time through target class change.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.element|Element|string)} arg1
	 * @return {undefined}
	 */
	setAjaxLoader: function( target ) {
		jQuery( target ).toggleClass( 'tsfem-loading' );
	},

	/**
	 * Adjusts class loaders on Ajax response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.element|Element|string)} arg1
	 * @param {number} success
	 * @param {string} notice
	 * @param {number} html
	 * @return {undefined}
	 */
	unsetAjaxLoader: function( target, success, notice, html ) {

		let newclass = 'tsfem-success',
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
			notice = jQuery( '<span/>' ).html( notice ).text();
			jQuery( target ).removeClass( 'tsfem-loading' ).addClass( newclass ).text( notice ).fadeOut( fade );
		}
	},

	/**
	 * Cleans and resets Ajax wrapper class and contents to default.
	 * Also stops any animation and resets fadeout to beginning.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.element|Element|string)} arg1
	 * @return {undefined}
	 */
	resetAjaxLoader: function( target ) {
		//* Reset CSS, with IE compat.
		jQuery( target ).stop().empty().prop( 'class', 'tsfem-ajax' ).css( { 'opacity' : '1', 'display' : 'initial' } ).prop( 'style', '' );
	},

	/**
	 * Updates the feed option.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event
	 * @return {undefined}
	 */
	updateFeed: function( event ) {

		let disabled = 'tsfem-button-disabled',
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

		const unknownError = function() {
			$button.removeClass( disabled );
			$button.prop( 'disabled', false );
			tsfem.updatedResponse( loader, status, tsfem.i18n['UnknownError'], 0 );
		};

		//* Setup external update.
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfem_enable_feeds',
				'nonce' : tsfem.nonce,
			},
			timeout: 12000,
			async: true,
			success: function( response ) {

				response = tsfem.convertJSONResponse( response );

				tsfem.debug && console.log( response );

				let data = response && response.data || void 0,
					type = response && response.type || void 0;

				if ( 'success' === type && data ) {

					let content = data.content;

					switch ( content.status ) {
						case 'success' :
							status = 1;

							//* Insert wrap.
							jQuery( '.tsfem-trends-wrap' ).empty().css( 'opacity', 0 ).append( content.wrap ).animate(
								{ 'opacity' : 1 },
								{ 'queue' : true, 'duration' : 250 }
							);

							var duration = 400,
								total = content.data.length,
								wait = 0;

							//* Calculate loader wait.
							// Remove last entry from calculation (total-1) as it has adds no timing effect.
							for ( let i = 1; i < total - 1; i++ ) {
								wait += Math.round( duration / Math.pow( 1 + ( i / 2 ) / 100, 2 ) );
							}
							// Remove first and last entries from calculation as they have no timing effects.
							wait -= ( duration * 2 ) + ( duration / 2 );

							//* Loop through each issue and slowly insert it. It's run asynchronously...
							jQuery.each( content.data, function( index, value ) {
								duration = Math.round( duration / Math.pow( 1 + ( index / 2 ) / 100, 2 ) );
								setTimeout( function() {
									jQuery( value ).hide().appendTo( '.tsfem-feed-wrap' ).slideDown( duration );
								}, duration / 2 * index );
							} );

							//* Expected to be done in 3.858 seconds
							setTimeout( function() { tsfem.updatedResponse( loader, status, '', 0 ); }, wait );
							break;

						case 'parse_error' :
						case 'unknown_error' :
						default :
							jQuery( '.tsfem-trends-wrap' ).empty().css( 'opacity', 0 ).append( content.error_output ).css( 'opacity', 1 ).find( '.tsfem-feed-wrap' ).css(
								{ 'opacity' : 0 }
							).animate(
								{ 'opacity' : 1 },
								{ queue: true, duration: 2000 }
							);
							//* 2 means the feed is offline. 0 means a server parsing error.
							// Don't enable the button. Make the user reload.
							status = 'unknown_error' === content.status ? 2 : 0;
							setTimeout( function() { tsfem.updatedResponse( loader, status, tsfem.i18n['UnknownError'], 0 ); }, 1000 );
							break;
					}
				} else if ( 'unknown' === response.type ) {
					status = 2;
					unknownError();
				} else {
					unknownError();
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				let _error = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

				$button.removeClass( disabled );
				$button.prop( 'disabled', false );
				tsfem.updatedResponse( loader, 0, _error, 0 );
			},
			complete: function() { },
		} );
	},

	/**
	 * Updates the selected extension state.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {jQuery.event} event jQuery event
	 * @return {undefined}
	 */
	updateExtension: function( event ) {

		let disabled = 'tsfem-button-disabled',
			$button = jQuery( event.target ),
			$buttons = jQuery( '.tsfem-button-extension-activate, .tsfem-button-extension-deactivate' ).not( jQuery( '.' + disabled ) ),
			actionSlug = $button.data( 'slug' ),
			actionCase = $button.data( 'case' );

		let loader = '#tsfem-extensions-ajax',
			status = 0,
			topNotice = '',
			topNoticeCode = 0,
			loaderText = '';

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
		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfem_update_extension',
				'nonce' : tsfem.nonce,
				'slug' : actionSlug,
				'case' : actionCase,
			},
			timeout: 10000,
			async: true,
		} ).done( function( response ) {

			response = tsfem.convertJSONResponse( response );

			tsfem.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0; // type is unused but it's a standard.

			if ( ! data || ! type ) {
				//* Erroneous output.
				loaderText = tsfem.i18n['UnknownError'];
			} else {
				let rCode = data.results && data.results.code || void 0,
					success = data.results && data.results.success || void 0;
					loaderText = data.results && data.results.notice || void 0;

				if ( 'activate' === actionCase ) {
					switch ( rCode ) {
						case 10001 : // No extensions checksum found.
						case 10002 : // Extensions checksum mismatch.
						case 10003 : // Method outcome mismatch.
						case 10004 : // Account isn't allowed to use premium extension.
						case 10006 : // Option update failed for unknown reason. Maybe overload.
						case 10007 : // No slug set.
							status = 0;
							topNoticeCode = rCode;
							break;

						case 10005 : // Extension caused fatal error.
							status = 0;
							let fatalError = data && data.fatal_error || void 0;
							topNotice = fatalError;
							topNoticeCode = rCode;
							break;

						case 10008 : // Premium activated.
						case 10010 : // Free activated.
							status = 1;
							$button.removeClass( 'tsfem-button-extension-activate' ).addClass( 'tsfem-button-extension-deactivate' );
							$button.data( 'case', 'deactivate' );
							$button.text( tsfem.i18n['Deactivate'] );
							jQuery( '#' + actionSlug + '-extension-entry' ).removeClass( 'tsfem-extension-deactivated' ).addClass( 'tsfem-extension-activated' );
							tsfem.updateExtensionDescFooter( actionSlug, actionCase );
							break;

						case 10009 : // User not premium, trying to activate premium extension.
							status = 2;
							topNoticeCode = rCode;
							break;

						default :
							status = 0;
							loaderText = tsfem.i18n['UnknownError'];
							break;
					}
				} else if ( 'deactivate' === actionCase ) {
					switch ( rCode ) {
						case 11001 : // success.
							status = 1;
							$button.removeClass( 'tsfem-button-extension-deactivate' ).addClass( 'tsfem-button-extension-activate' );
							$button.data( 'case', 'activate' );
							$button.text( tsfem.i18n['Activate'] );
							jQuery( '#' + actionSlug + '-extension-entry' ).removeClass( 'tsfem-extension-activated' ).addClass( 'tsfem-extension-deactivated' );
							tsfem.updateExtensionDescFooter( actionSlug, actionCase );
							break;

						case 11002 : // failure.
							status = 0;
							topNoticeCode = rCode;
							break;

						default :
							status = 0;
							loaderText = tsfem.i18n['UnknownError'];
							break;
					}
				}
			}
		} ).fail( function( jqXHR, textStatus, errorThrown ) {
			// Set Ajax response for wrapper.
			loaderText = tsfem.getAjaxError( jqXHR, textStatus, errorThrown );

			// Try to set top notices, regardless.
			tsfem.setTopNotice( 1071100 ); // Notifies that there's an error saving.
			errorThrown && tsfem.setTopNotice( -1, 'jQ error: ' + errorThrown );
		} ).always( function() {
			tsfem.updatedResponse( loader, status, loaderText, 0 );
			$buttons.removeClass( disabled );
			$buttons.prop( 'disabled', false );
			topNoticeCode && tsfem.setTopNotice( topNoticeCode, topNotice );
		} );
	},

	/**
	 * Tries to convert JSON response to values if not already set.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @function
	 * @param {(object|string|undefined)} response
	 * @return {(object|undefined)}
	 */
	convertJSONResponse: function( response ) {

		let testJSON = response && response.json || void 0,
			isJSON = 1 === testJSON;

		if ( ! isJSON ) {
			let _response = response;

			try {
				response = JSON.parse( response );
				isJSON = true;
			} catch ( error ) {
				isJSON = false;
			}

			if ( ! isJSON ) {
				// Reset response.
				response = _response;
			}
		}

		return response;
	},

	/**
	 * Visualizes the AJAX response to the user.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {string} target
	 * @param {number} success 0 = error, 1 = success, 2 = unknown but success.
	 * @param {string} notice The updated notice.
	 * @param {number} html 0 = output text, 1 = output HTML
	 * @return {undefined}
	 */
	updatedResponse: function( target, success, notice, html ) {
		switch ( success ) {
			case 0 :
			case 1 :
			case 2 :
				tsfem.unsetAjaxLoader( target, success, notice, html );
				break;

			default :
				tsfem.resetAjaxLoader( target );
				break;
		}
	},

	/**
	 * Returns bound AJAX reponse error with the help from i18n.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @function
	 * @param {(jQuery.xhr|Object)} jqXHR
	 * @param {String} textStatus
	 * @param {String} errorThrown
	 * @return {String}
	 */
	getAjaxError: function( jqXHR, textStatus, errorThrown ) {

		if ( tsfem.debug ) {
			console.log( jqXHR.responseText );
			console.log( errorThrown );
		}

		let _error = '';

		switch ( errorThrown ) {
			case 'abort' : // client error, no code.
			case 'timeout' : // 408
				_error = tsfem.i18n['TimeoutError'];
				break;

			case 'Bad Request' : // 400
				_error = tsfem.i18n['BadRequest'];
				break;

			case 'Internal Server Error' : // 500
				_error = tsfem.i18n['FatalError'];
				break;

			case 'parsererror' : // PHP error, no code.
				_error = tsfem.i18n['ParseError'];
				break;

			default :
				// @TODO use ajaxOptions.status? i.e. 400, 401, 402, 503.
				_error = tsfem.i18n['UnknownError'];
				break;
		}

		return _error;
	},

	/**
	 * Generates AJAX notices and top notices based on error return values.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @param {object} response The response body.
	 * @return {undefined}
	 */
	unexpectedAjaxErrorNotice: function( response ) {

		response = tsfem.convertJSONResponse( response ) || void 0;

		let data = response && response.data || void 0;

		if ( tsfem.debug ) console.log( response );

		if ( data && 'results' in data && 'code' in data.results )
			tsfem.setTopNotice( data.results.code, data.results.notice );
	},

	/**
	 * Converts multidimensional arrays to single array with key wrappers.
	 * All first array keys become the new key. The final value becomes its value.
	 *
	 * Great for creating form array keys.
	 * matosa: "Multidimensional Array TO Single Array"
	 *
	 * The latest value must be scalar.
	 *
	 * Example: a = [ 1 => [ 2 => [ 3 => [ 'value' ] ] ] ];
	 * Becomes: '1[2][3]' => 'value';
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param {(String|Object)} value The array or string to loop.
	 * @return {(Object|Boolean)} The iterated array to string. False if input isn't array.
	 */
	matosa: function( value ) {

		var last = null,
			output = '';

		(function _matosa( _value, _i ) {
			_i++;
			if ( typeof _value === 'object' ) {
				let _index, _item;
				for ( _index in _value ) {
					_item = _value[ _index ];
				}

				last = _item;

				if ( 1 === _i ) {
					output += _index + _matosa( _item, _i );
				} else {
					output += '[' + _index + ']' + _matosa( _item, _i );
				}
			} else if ( 1 === _i ) {
				last = null;
				return output = false;
			}

			return output;
		})( value, 0 );

		if ( false === output )
			return false;

		let retval = {};
		retval[ output ] = last;

		return retval;
	},

	/**
	 * Gets and inserts the AJAX response for the Extension Description Footer.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {String} actionSlug The extension slug.
	 * @param {String} actionCase The update case. Either 'activate' or 'deactivate'.
	 * @return {undefined}
	 */
	updateExtensionDescFooter: function( actionSlug, actionCase ) {

		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				'action' : 'tsfem_update_extension_desc_footer',
				'nonce' : tsfem.nonce,
				'slug' : actionSlug,
				'case' : actionCase,
			},
			timeout: 3000,
			async: true,
			success: function( response ) {

				response = tsfem.convertJSONResponse( response );

				tsfem.debug && console.log( response );

				let data = response && response.data || void 0,
					type = response && response.type || void 0;

				if ( data ) {

					var $footer = jQuery( '#' + actionSlug + '-extension-entry .tsfem-extension-description-footer' ),
						direction = 'activate' === actionCase ? 'up' : 'down';

					$footer.addClass( 'tsfem-flip-hide-' + direction );

					setTimeout( function() {
						$footer.empty().append( data );
						//* Update hover cache.
						jQuery( window ).trigger( 'tsfem-reset-tooltips' );
					}, 250 );
					setTimeout( function() {
						$footer.addClass( 'tsfem-flip-show-' + direction );
					}, 500 );
					setTimeout( function() {
						$footer.removeClass( 'tsfem-flip-hide-' + direction + ' tsfem-flip-show-' + direction );
					}, 750 );
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				// Don't invoke anything fancy, yet. This is automatically called.
				if ( tsfem.debug ) {
					console.log( jqXHR.responseText );
					console.log( errorThrown );
				}
			},
			complete: function() { },
		} );
	},

	/**
	 * Prevents browser default actions.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @function
	 * @param {Object} event jQuery event
	 * @return {undefined}
	 */
	preventDefault: function( event ) {
		event.preventDefault();
		event.stopPropagation();
	},

	/**
	 * Engages switcher button reset toggle.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @function
	 * @param {Object} event jQuery event
	 */
	engageSwitcher: function( event ) {

		const events = 'click.tsfemResetSwitcher';
		const resetSwitcher = ( event ) => {
			let $switcher = jQuery( '.tsfem-switch-button-container > input[type="checkbox"]:checked' );

			if ( 'undefined' !== typeof $switcher && $switcher.length > 0 ) {
				let $wrap = $switcher.parents( '.tsfem-switch-button-container-wrap' );

				if ( jQuery( event.target ).closest( $wrap ).length < 1 ) {
					$switcher.prop( 'checked', false );
					jQuery( window ).off( events );
				}
			}
		}

		jQuery( window ).off( events ).on( events, resetSwitcher );
	},

	/**
	 * Sets last uneven extension wrapper to be the same width as the first.
	 *
	 * @since 1.0.0
	 * @access private
	 * @todo set Resizebuffer rather than use jQ().delay().
	 *
	 * @function
	 * @return {undefined}
	 */
	setLastExtensionEntry: function() {

		let $extensions = jQuery( '.tsfem-extensions-overview-content' ).children( '.tsfem-extension-entry-wrap' ),
			amount = $extensions.length;

		if ( amount & 1 && amount > 2 ) {
			//* Uneven amount.
			let $first = $extensions.first(),
				$last = $extensions.last();

			if ( window.innerWidth < 782 ) {
				$last.delay( 10 ).css( { 'max-width' : '' } );
			} else {
				$last.delay( 10 ).css( { 'max-width' : $first.width() } );
			}
		}
	},

	/**
	 * Set a flag, to indicate user needs to be warned on navigation.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	registerNavWarn: function() {
		tsfem.navWarn = true;
	},

	/**
	 * Set a flag, to indicate user needs to be warned on navigation.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @return {Boolean}
	 */
	mustNavWarn: function() {
		return !! tsfem.navWarn;
	},

	/**
	 * Sets up dismissible notice listener. Uses class .tsfem-dismiss.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	setDismissNoticeListener: function() {

		let $dismiss = jQuery( '.tsfem-dismiss' );

		const dismissNotice = ( event ) => {
			jQuery( event.target ).closest( '.tsfem-notice' ).slideUp( 200, function() {
				this.remove();
			} );
		};

		$dismiss.off( 'click', dismissNotice );
		$dismiss.on( 'click', dismissNotice );
	},

	/**
	 * Gets and inserts AJAX top notice.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Now uses fallback notices on fatal AJAX error.
	 * @access public
	 *
	 * @function
	 * @param {number} noticeKey The notice key.
	 * @param {(string|undefined)} msg The notice message, if set this is going to be used.
	 * @return {undefined}
	 */
	setTopNotice: function( noticeKey, msg ) {

		//* Wait a little until AJAX is resolved.
		if ( tsfem.noticeBuffer ) {
			window.setTimeout( function() {
				tsfem.setTopNotice( noticeKey, msg );
			}, 500 );
			return;
		}

		tsfem.noticeBuffer = true;

		let hasMsg = msg ? 1 : 0;

		jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				'action' : 'tsfem_get_dismissible_notice',
				'nonce' : tsfem.nonce,
				'tsfem-notice-key' : noticeKey,
				'tsfem-notice-has-msg' : hasMsg,
			},
			timeout: 7000,
			async: true,
		} ).done( function( response ) {

			response = tsfem.convertJSONResponse( response );

			tsfem.debug && console.log( response );

			let data = response && response.data || void 0,
				type = response && response.type || void 0;

			if ( ! data || ! type || 'undefined' === typeof data.notice ) {
				//* Erroneous output. Do nothing as this error is invoked internally.
			} else {
				let notice = '';

				if ( hasMsg ) {
					notice = jQuery( data.notice );
					if ( tsfem.rtl ) {
						notice.find( 'p' ).first().prepend( msg + ' ' );
					} else {
						notice.find( 'p' ).first().append( ' ' + msg );
					}
				} else {
					notice = data.notice;
				}

				tsfem.appendTopNotice( notice );
			}
		} ).fail( function( jqXHR, textStatus, errorThrown )  {
			// Simply log what happened.
			if ( tsfem.debug ) {
				console.log( jqXHR.responseText );
				console.log( errorThrown );
			}

			// Output fallback notice.
			let fallbackNotice = hasMsg ? wp.template( 'tsfem-fbtopnotice-msg' ) : wp.template( 'tsfem-fbtopnotice' ),
				template = fallbackNotice( { 'code' : noticeKey, 'msg' : msg } );
			tsfem.appendTopNotice( template );
		} ).always( function() {
			tsfem.noticeBuffer = false;
		} );
	},

	/**
	 * Appends top notice.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @function
	 * @param {string} notice The notice to append.
	 */
	appendTopNotice: function( notice ) {

		let $top = jQuery( '.tsfem-notice-wrap' ),
			$notices = $top.children( '.tsfem-notice, .tsfem-notice-wrap .notice' );

		if ( $notices.length > 1 ) {
			// Kill them all with fire.
			$notices.slice( 0, $notices.length - 1 ).each( function() {
				jQuery( this ).slideUp( 200, function() {
					this.remove();
				} );
			} );
		}

		jQuery( notice ).hide().appendTo( $top ).slideDown( 200 );

		tsfem.setDismissNoticeListener();
	},

	/**
	 * Creates modal dialog box from options. Also allows multiselect, instead
	 * of just confirm/cancel.
	 *
	 * NOTE: If options.select is set, you must set options.confirm to get the
	 *       return value.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @function
	 * @param {object} options The dialog options.
	 * @return {undefined}
	 */
	dialog: function( options ) {

		let title = options.title || '',
			text = options.text || '',
			select = options.select || '',
			confirm = options.confirm || '',
			cancel = options.cancel || '',
			modal = {};

		modal.mask = document.createElement( 'div' );
		modal.mask.className = 'tsfem-modal-mask';
		modal.maskNoScroll = document.createElement( 'div' );
		modal.maskNoScroll.className = 'tsfem-modal-mask-noscroll';
		modal.mask.appendChild( modal.maskNoScroll );

		modal.container = document.createElement( 'div' );
		modal.container.className = 'tsfem-modal-container';

		modal.dialogWrap = document.createElement( 'div' );
		modal.dialogWrap.className = 'tsfem-modal-dialog-wrap';
		modal.dialogWrap.style.marginLeft = document.getElementById( 'adminmenuwrap' ).offsetWidth + 'px';
		modal.dialogWrap.style.marginTop = document.getElementById( 'wpadminbar' ).offsetHeight + 'px';

		modal.dialog = document.createElement( 'div' );
		modal.dialog.className = 'tsfem-modal-dialog';

		modal.trap = document.createElement( 'div' );
		modal.trap.className = 'tsfem-modal-trap';
		modal.trap.tabIndex = 0;
		modal.bottomTrap = modal.trap.cloneNode( false );
		modal.dialog.appendChild( modal.trap );

		modal.x = document.createElement( 'div' );
		modal.x.className = 'tsfem-modal-dismiss';
		modal.x.addEventListener( 'click', function() {
			window.dispatchEvent( new Event( 'tsfem_modalCancel' ) );
		} );
		modal.dialog.appendChild( modal.x );

		if ( title ) {
			modal.titleWrap = document.createElement( 'div' );
			modal.titleWrap.className = 'tsfem-modal-title';

			modal.titleWrapTitle = document.createElement( 'h4' );
			modal.titleWrapTitle.innerHTML = title;
			modal.titleWrap.appendChild( modal.titleWrapTitle );

			modal.dialog.appendChild( modal.titleWrap );
		}

		modal.inner = document.createElement( 'div' );
		modal.inner.className = 'tsfem-modal-inner';

		if ( text ) {
			modal.textWrap = document.createElement( 'div' );
			modal.textWrap.className = 'tsfem-modal-text';

			if ( Array.isArray( text ) ) {
				for ( let _iT in text ) {
					modal.textWrapContent = document.createElement( 'p' );
					modal.textWrapContent.innerHTML = text[ _iT ];
					modal.textWrap.appendChild( modal.textWrapContent );
				}
			} else {
				modal.textWrapContent = document.createElement( 'p' );
				modal.textWrapContent.innerHTML = text;
				modal.textWrap.appendChild( modal.textWrapContent );
			}

			modal.inner.appendChild( modal.textWrap );
		}

		let hasSelect = false;

		if ( select ) {
			hasSelect = true;

			modal.selectWrap = document.createElement( 'div' );
			modal.selectWrap.className = 'tsfem-modal-select';

			let selectWrapItem = {};

			selectWrapItem.wrap = document.createElement( 'div' );
			selectWrapItem.wrap.className = 'tsfem-modal-select-option';

			selectWrapItem.radio = document.createElement( 'input' );
			selectWrapItem.radio.setAttribute( 'type', 'radio' );
			selectWrapItem.radio.setAttribute( 'name', 'tsfem-modal-select-option-group' );
			selectWrapItem.radio.tabIndex = 0;

			selectWrapItem.label = document.createElement( 'label' );

			(function() {
				for ( let i in select ) {
					let wrap = selectWrapItem.wrap.cloneNode( true ),
						radio = selectWrapItem.radio.cloneNode( false ),
						label = selectWrapItem.label.cloneNode( false );

					radio.setAttribute( 'value', i );
					label.innerHTML = select[ i ];

					//= i can be a string and integer because of "possible" JSON parsing.
					if ( i == 0 ) {
						radio.checked = true;
					}

					let id = 'tsfem-dialog-option-' + i;

					radio.setAttribute( 'id', id );
					label.setAttribute( 'for', id );

					wrap.appendChild( radio );
					wrap.appendChild( label );

					modal.selectWrap.appendChild( wrap );
				}
			})();

			modal.inner.appendChild( modal.selectWrap );
		}

		modal.dialog.appendChild( modal.inner );

		if ( confirm || cancel ) {
			modal.buttonWrap = document.createElement( 'div' );
			modal.buttonWrap.className = 'tsfem-modal-buttons';

			if ( confirm ) {
				modal.confirmButton = document.createElement( 'button' );
				modal.confirmButton.className = 'tsfem-modal-confirm tsfem-button-small';
				if ( hasSelect ) {
					modal.confirmButton.className += ' tsfem-button-primary tsfem-button-green';
				} else {
					modal.confirmButton.className += ' tsfem-button';
				}

				modal.confirmButton.innerHTML = confirm;
				modal.confirmButton.addEventListener( 'click', function() {
					let detail = void 0;
					if ( hasSelect ) {
						detail = { 'detail' : {
							'checked' : document.querySelector( '.tsfem-modal-select input:checked' ).value
						} };
					}
					window.dispatchEvent( new CustomEvent( 'tsfem_modalConfirm', detail ) );
				} );

				modal.buttonWrap.appendChild( modal.confirmButton );
			}

			if ( cancel ) {
				modal.cancelButton = document.createElement( 'button' );
				modal.cancelButton.className = 'tsfem-modal-cancel tsfem-button tsfem-button-small';
				modal.cancelButton.innerHTML = cancel;
				modal.cancelButton.addEventListener( 'click', function() {
					window.dispatchEvent( new Event( 'tsfem_modalCancel' ) );
				} );

				modal.buttonWrap.appendChild( modal.cancelButton );
			}

			modal.dialog.appendChild( modal.buttonWrap );
		}

		modal.dialog.appendChild( modal.bottomTrap );

		modal.dialogWrap.appendChild( modal.dialog );
		modal.container.appendChild( modal.dialogWrap );

		document.body.appendChild( modal.mask );
		document.body.appendChild( modal.container );

		const resetFocus = () => {
			modal.trap.focus();
		};
		modal.trap.addEventListener( 'focus', resetFocus );
		modal.bottomTrap.addEventListener( 'focus', resetFocus );
		modal.trap.focus();

		tsfem.fadeIn( modal.mask );
		tsfem.fadeIn( modal.container );

		const preventDefault = ( e ) => {
			e.preventDefault();
		};
		modal.maskNoScroll.addEventListener( 'wheel', preventDefault );
		modal.maskNoScroll.addEventListener( 'touchmove', preventDefault );

		const resizeListener = function() {
			modal.dialogWrap.style.marginLeft = document.getElementById( 'adminmenuwrap' ).offsetWidth + 'px';
			modal.dialogWrap.style.marginTop = document.getElementById( 'wpadminbar' ).offsetHeight + 'px';
		}
		window.addEventListener( 'resize', resizeListener );

		const removeModal = () => {
			modal.maskNoScroll.removeEventListener( 'wheel', preventDefault );
			modal.maskNoScroll.removeEventListener( 'touchmove', preventDefault );
			window.removeEventListener( 'tsfem_modalCancel', removeModal );
			window.removeEventListener( 'tsfem_modalConfirm', removeModal );
			window.removeEventListener( 'resize', resizeListener );
			tsfem.fadeOut( modal.mask, 250, () => modal.mask.remove() );
			tsfem.fadeOut( modal.container, 250, () => modal.container.remove() );
		};

		window.addEventListener( 'tsfem_modalCancel', removeModal );
		window.addEventListener( 'tsfem_modalConfirm', removeModal );
	},

	/**
	 * Fades in target.
	 * Can also fade out a target when show if false. It will remove the target
	 * on completion.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 : 1. Added done parameter.
	 *                2. Added roughness to reduce FLOPS.
	 * @access public
	 *
	 * @function
	 * @param {Element} target The target to fade in (or out).
	 * @param {number} ms The time it takes to fade in (or out).
	 * @param {function} done Callback to run after transition is done.
	 * @param {boolean} show Whether to show or hide and delete the target.
	 * @return {undefined}
	 */
	fadeIn: function( target, ms, done, show ) {

		if ( void 0 === target || ! target instanceof HTMLElement )
			return;

		if ( ! target.style || ! ( 'opacity' in target.style ) )
			return;

		ms = ms || 250;
		show = void 0 === show ? true : show;

		let opacity = 0,
			cO = 0,
			roughness = 3,
			oBuffer,
			fadeGo;

		if ( show ) {
			fadeGo = () => {
				cO = ( opacity += roughness ) / 100;
				target.style.display = null;
				target.style.opacity = cO;
				if ( cO >= 1 ) {
					clearInterval( oBuffer );
					target.style.opacity = 1;
					typeof done === 'function' && (done)();
				}
			};
		} else {
			opacity = 100;
			fadeGo = () => {
				cO = ( opacity -= roughness ) / 100;
				target.style.opacity = cO;
				if ( cO <= 0 ) {
					clearInterval( oBuffer );
					target.style.opacity = 0;
					//= Defer paint asynchronously to prevent bounce if there's a callback.
					setTimeout( () => { target.style.display = 'none' }, 0 );
					typeof done === 'function' && (done)()
				}
			};
		}
		oBuffer = setInterval( fadeGo, ms / 100 );
	},

	/**
	 * Fades out and deletes target.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Added done parameter.
	 * @access public
	 *
	 * @function
	 * @param {Element} target The target to fade out.
	 * @param {number} ms The time it takes to fade out.
	 * @param {function} done Callback to run after transition is done.
	 * @return {undefined}
	 */
	fadeOut: function( target, ms, done ) {
		tsfem.fadeIn( target, ms, done, false );
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
	 * @access private
	 *
	 * @TODO restructure calling. This file is loaded on extraneous pages.
	 *
	 * @function
	 * @param {Object} jQ jQuery
	 * @return {undefined}
	 */
	ready: function( jQ ) {

		// Move the page updates notices below the top-wrap.
		jQ( '#wpbody-content' ).children( '.updated, .error, .notice-warning' ).insertAfter( '.tsfem-top-wrap' );

		// AJAX feed update.
		jQ( 'a#tsfem-enable-feeds' ).on( 'click', tsfem.updateFeed );

		// AJAX extension update.
		jQ( 'a.tsfem-button-extension-activate, a.tsfem-button-extension-deactivate' ).on( 'click', tsfem.updateExtension );

		// AJAX on-heartbeat active extension check to update buttons accordingly on multi-admin sites or after timeout. @TODO
		//jQ( document ).on( 'heartbeat-tick', tsfem.checkExtensions );

		// Disable semi-disabled buttons.
		jQ( 'a.tsfem-button-disabled' ).on( 'click', tsfem.preventDefault );

		// Initialize the balloon hover effects.
		jQ( document.body ).ready( tsfem._initToolTips );

		// Proportionate uneven amounts of extension entry boxes.
		jQ( document.body ).ready( tsfem.setLastExtensionEntry );
		jQ( window ).on( 'resize orientationchange', tsfem.setLastExtensionEntry );

		// Reset switcher button to default when clicked outside.
		jQ( '.tsfem-switch-button-container-wrap' ).on( 'click', 'label', tsfem.engageSwitcher );

		// Set dismissible notice listener.
		jQ( document.body ).ready( tsfem.setDismissNoticeListener );
	}
};
jQuery( tsfem.ready );
