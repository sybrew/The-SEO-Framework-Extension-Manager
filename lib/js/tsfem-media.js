/**
 * This file holds The SEO Framework Extension Manager plugin's JS code for Image
 * Selection and Cropping.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://wordpress.org/plugins/the-seo-framework-extension-manager/
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
// @output_file_name tsfem-media.min.js
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/jquery-1.9.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/tsfem.externs.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/tsfem-media.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

/**
 * Holds tsfemMedia values in an object to avoid polluting global namespace.
 *
 * @since 1.3.0
 *
 * @constructor
 */
window[ 'tsfemMedia' ] = {

	/**
	 * @since 1.3.0
	 * @param {(Object<string, *>)|boolean} i18n Localized strings
	 */
	i18n : typeof tsfemMediaL10n === 'undefined' || tsfemMediaL10n,

	/**
	 * Image cropper instance.
	 *
	 * @since 2.8.0
	 *
	 * @type {!Object} cropper
	 */
	cropper : {},

	/**
	 * Escapes HTML class or ID keys. Doesn't double-escape.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param {String} str
	 * @return {(String|null)} HTML to jQuery converted string
	 */
	escapeKey: function( str ) {
		'use strict';

		if ( str )
			return str.replace( /(?!\\)(?=[\[\]\/])/g, '\\' );

		return str;
	},

	/**
	 * Opens the image editor on request.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {(undefined|null)}
	 */
	openImageEditor: function( event ) {
		'use strict';

		if ( jQuery( event.target ).prop( 'disabled' ) || 'undefined' === typeof wp.media ) {
			return;
		}

		var $target = jQuery( event.target ),
			inputID = $target.data( 'inputid' ),
			s_inputID = tsfemMedia.escapeKey( inputID ),
			frame;

		if ( frame ) {
			frame.open();
			return;
		}

		//* Init extend cropper.
		tsfemMedia.extendCropper();

		frame = wp.media( {
			button : {
				'text' : tsfemMedia.i18n['imgFrameButton'],
				'close' : false,
			},
			states: [
				new wp.media.controller.Library( {
					'title' : tsfemMedia.i18n['imgFrameTitle'],
					'library' : wp.media.query({ 'type' : 'image' }),
					'multiple' : false,
					'date' : false,
					'priority' : 20,
					'suggestedWidth' : 1920, // TODO USE DATA
					'suggestedHeight' : 1080 // TODO USE DATA
				} ),
				new tsfemMedia.cropper( {
					'imgSelectOptions' : tsfemMedia.calculateImageSelectOptions
				} ),
			],
		} );

		let selectFunc = (function() {
			frame.setState( 'cropper' );
		} );
		frame.off( 'select', selectFunc );
		frame.on( 'select', selectFunc );

		let croppedFunc = (function( croppedImage ) {
			let url = croppedImage.url,
				attachmentId = croppedImage.id,
				w = croppedImage.width,
				h = croppedImage.height;

			// Send the attachment id to our hidden input. URL to explicit output.
		//	jQuery( '#' + s_inputID + '-url' ).val( url );
			jQuery( '#' + s_inputID ).val( url );
		//	jQuery( '#' + s_inputID + '-id' ).val( attachmentId );
		} );
		frame.off( 'cropped', croppedFunc );
		frame.on( 'cropped', croppedFunc );

		let skippedcropFunc = (function( selection ) {
			let url = selection.get( 'url' ),
				attachmentId = selection.get( 'id' ),
				w = selection.get( 'width' ),
				h = selection.get( 'height' );

			// Send the attachment id to our hidden input. URL to explicit output.
		//	jQuery( '#' + s_inputID + '-url' ).val( url );
			jQuery( '#' + s_inputID ).val( url );
		//	jQuery( '#' + s_inputID + '-id' ).val( attachmentId ); // TODO?
		} );
		frame.off( 'skippedcrop', skippedcropFunc );
		frame.on( 'skippedcrop', skippedcropFunc );

		let doneFunc = (function( imageSelection ) {
			jQuery( '#' + s_inputID + '-select' ).text( tsfemMedia.i18n['imgChange'] );
			/*
			jQuery( '#' + s_inputID + '-url' ).prop( 'readonly', true ).css( 'opacity', 0 ).animate(
				{ 'opacity' : 1 },
				{ 'queue' : true, 'duration' : 1000 },
				'swing'
			);
			*/
			jQuery( '#' + s_inputID ).prop( 'readonly', true ).css( 'opacity', 0 ).animate(
				{ 'opacity' : 1 },
				{ 'queue' : true, 'duration' : 1000 },
				'swing'
			);

			tsfemMedia.appendRemoveButton( $target, inputID, true );

			//* Remove button active state.
			$target.trigger('blur');

			tsfem.registerNavWarn();
		} );
		frame.off( 'skippedcrop cropped', doneFunc );
		frame.on( 'skippedcrop cropped', doneFunc );

		frame.open();
	},

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @param {!jQuery.event.target} target jQuery event.target
	 * @param {string} inputID The input ID.
	 * @return {(undefined|null)}
	 */
	appendRemoveButton: function( target, inputID, animate ) {
		'use strict';

		if ( target && inputID ) {
			let s_inputID = tsfemMedia.escapeKey( inputID );

			if ( ! jQuery( '#' + s_inputID + '-remove' ).length ) {
				target.after(
					'<button id="'
						+ inputID + '-remove" class="tsfem-remove-image-button tsfem-button-primary tsfem-button-small" data-inputid="'
						+ inputID +
					'" title="' + tsfemMedia.i18n['imgRemoveTitle'] + '">' + tsfemMedia.i18n['imgRemove'] + '</button>'
				);
				if ( animate ) {
					jQuery( '#' + s_inputID + '-remove' ).css( 'opacity', 0 ).animate(
						{ 'opacity' : 1 },
						{ 'queue' : true, 'duration' : 1000 },
						'swing'
					);
				}
			}
		}

		//* Reset cache.
		tsfemMedia.resetImageEditorActions();
	},

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {(undefined|null)}
	 */
	removeEditorImage: function( event ) {
		'use strict';

		let $target = jQuery( event.target ),
			inputID = $target.data( 'inputid' ),
			s_inputID = tsfemMedia.escapeKey( inputID ),
			disabledClass = 'tsfem-button-disabled';

		if ( jQuery( '#' + s_inputID + '-select' ).prop( 'disabled' ) )
			return;

		jQuery( '#' + s_inputID + '-select' ).addClass( disabledClass ).prop( 'disabled', true ).text( tsfemMedia.i18n['imgSelect'] );

		//* target.event.id === '#' + s_inputID + '-remove'.
		jQuery( '#' + s_inputID + '-remove' ).addClass( disabledClass ).prop( 'disabled', true ).fadeOut( 500, function() {
			jQuery( this ).remove();
			jQuery( '#' + s_inputID + '-select' ).removeClass( disabledClass ).removeProp( 'disabled' );
		} );

		/*
		jQuery( '#' + s_inputID + '-url' ).val( '' ).removeProp( 'readonly' ).css( 'opacity', 0 ).animate(
			{ 'opacity' : 1 },
			{ 'queue' : true, 'duration' : 500 },
			'swing'
		);
		*/
		jQuery( '#' + s_inputID ).val( '' ).removeProp( 'readonly' ).css( 'opacity', 0 ).animate(
			{ 'opacity' : 1 },
			{ 'queue' : true, 'duration' : 500 },
			'swing'
		);

		// jQuery( '#' + s_inputID + '-id' ).val( '' );

		tsfem.registerNavWarn();
	},

	/**
	 * Builds constructor for media cropper.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	extendCropper: function() {
		'use strict';

		if ( 'undefined' !== typeof tsfemMedia.cropper.control )
			return;

		/**
		 * tsfemMedia.extendCropper => wp.media.controller.TSFCropper
		 *
		 * A state for cropping an image.
		 *
		 * @class
		 * @augments wp.media.controller.Cropper
		 * @augments wp.media.controller.State
		 * @augments Backbone.Model
		 */
		var TSFCropper;
		let Controller = wp.media.controller;

		TSFCropper = Controller.Cropper.extend( {
			doCrop: function( attachment ) {
				var cropDetails = attachment.get( 'cropDetails' ),
					control = tsfemMedia.cropper.control;

				// Use crop measurements when flexible in both directions.
				if ( control.params.flex_width && control.params.flex_height ) {
					// Square
					if ( cropDetails.width === cropDetails.height ) {
						if ( cropDetails.width > control.params.flex_width ) {
							cropDetails.dst_width = cropDetails.dst_height = control.params.flex_width;
						}
					// Landscape/Portrait
					} else {
						// Resize to flex width/height
						if ( cropDetails.width > control.params.flex_width || cropDetails.height > control.params.flex_height ) {
							// Landscape
							if ( cropDetails.width > cropDetails.height ) {
								var _ratio = cropDetails.width / control.params.flex_width;

								cropDetails.dst_width  = control.params.flex_width;
								cropDetails.dst_height = Math.round( cropDetails.height / _ratio );
							// Portrait
							} else {
								var _ratio = cropDetails.height / control.params.flex_height;

								cropDetails.dst_height = control.params.flex_height;
								cropDetails.dst_width  = Math.round( cropDetails.width / _ratio );
							}
						}
					}
				}

				// Nothing happened. Set destination to 0 and let PHP figure it out.
				if ( 'undefined' === typeof cropDetails.dst_width ) {
					cropDetails.dst_width  = 0;
					cropDetails.dst_height = 0;
				}

				return wp.ajax.post( 'tsfem-crop-image', {
					'nonce' : tsfemMedia.nonce,
					'id' : attachment.get( 'id' ),
					'context' : 'tsfem-image',
					'cropDetails' : cropDetails,
				} );
			}
		} );

		TSFCropper.prototype.control = {};
		TSFCropper.control = {
			'params' : {
				'flex_width' : 4096,
				'flex_height' : 4096,
				'width' : 1920,  // TODO USE DATA
				'height' : 1080, // TODO USE DATA
			},
		};

		tsfemMedia.cropper = TSFCropper;

		return;
	},

	/**
	 * Returns a set of options, computed from the attached image data and
	 * control-specific data, to be fed to the imgAreaSelect plugin in
	 * wp.media.view.Cropper.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @param {wp.media.model.Attachment} attachment
	 * @param {wp.media.controller.Cropper} controller
	 * @return {Object} imgSelectOptions
	 */
	calculateImageSelectOptions: function( attachment, controller ) {
		'use strict';

		let control = tsfemMedia.cropper.control;

		var flexWidth  = !! parseInt( control.params.flex_width, 10 ),
			flexHeight = !! parseInt( control.params.flex_height, 10 ),
			xInit = parseInt( control.params.width, 10 ),
			yInit = parseInt( control.params.height, 10 );

		let realWidth  = attachment.get( 'width' ),
			realHeight = attachment.get( 'height' ),
			ratio = xInit / yInit,
			xImg  = xInit,
			yImg  = yInit,
			x1,
			y1,
			imgSelectOptions;

		controller.set( 'control', control.params );
		controller.set( 'canSkipCrop', ! tsfemMedia.mustBeCropped( control.params.flex_width, control.params.flex_height, realWidth, realHeight ) );

		if ( realWidth / realHeight > ratio ) {
			yInit = realHeight;
			xInit = yInit * ratio;
		} else {
			xInit = realWidth;
			yInit = xInit / ratio;
		}

		x1 = ( realWidth - xInit ) / 2;
		y1 = ( realHeight - yInit ) / 2;

		imgSelectOptions = {
			'handles' : true,
			'keys' : true,
			'instance' : true,
			'persistent' : true,
			'imageWidth' : realWidth,
			'imageHeight' : realHeight,
			'minWidth' : xImg > xInit ? xInit : xImg,
			'minHeight' : yImg > yInit ? yInit : yImg,
			'x1' : x1,
			'y1' : y1,
			'x2' : xInit + x1,
			'y2' : yInit + y1
		};

		if ( false === flexHeight && false === flexWidth ) {
			imgSelectOptions.aspectRatio = xInit + ':' + yInit;
		}

		if ( true === flexHeight ) {
			imgSelectOptions.minHeight = 200; // TODO USE DATA
			imgSelectOptions.maxWidth = realWidth;
		}

		if ( true === flexWidth ) {
			imgSelectOptions.minWidth = 200; // TODO USE DATA
			imgSelectOptions.maxHeight = realHeight;
		}

		return imgSelectOptions;
	},

	/**
	 * Return whether the image must be cropped, based on required dimensions.
	 * Disregards flexWidth/Height.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @param {Number} dstW
	 * @param {Number} dstH
	 * @param {Number} imgW
	 * @param {Number} imgH
	 * @return {Boolean}
	 */
	mustBeCropped: function( dstW, dstH, imgW, imgH ) {
		'use strict';

		if ( imgW <= dstW && imgH <= dstH )
			return false;

		return true;
	},

	/**
	 * Checks if input is filled in by image editor.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	checkImageEditorInput: function() {
		'use strict';

		let $buttons = jQuery( '.tsfem-set-image-button' );

		if ( $buttons.length ) {
			let s_inputID = '',
				$valID = '';

			jQuery.each( $buttons, function( index, value ) {
				s_inputID = tsfemMedia.escapeKey( jQuery( value ).data( 'inputid' ) );
			//	$valID = jQuery( '#' + s_inputID + '-id' );

			//	if ( $valID.length && $valID.val() > 0 ) {
			//		jQuery( '#' + s_inputID + '-url' ).prop( 'readonly', true );
			//		jQuery( '#' + s_inputID ).prop( 'readonly', true );
			//		tsfemMedia.appendRemoveButton( jQuery( value ), s_inputID, false );
			//	}

				/*
				if ( jQuery( '#' + s_inputID + '-url' ).val() ) {
					jQuery( '#' + s_inputID + '-select' ).text( tsfemMedia.i18n['imgChange'] );
				}
				*/
				if ( jQuery( '#' + s_inputID ).val() ) {
					jQuery( '#' + s_inputID + '-select' ).text( tsfemMedia.i18n['imgChange'] );
				}
			} );
		}
	},

	/**
	 * Resets jQuery image editor cache.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	resetImageEditorActions: function() {
		'use strict';

		jQuery( '.tsfem-remove-image-button' ).off( 'click', tsfemMedia.removeEditorImage );
		jQuery( '.tsfem-remove-image-button' ).on( 'click', tsfemMedia.removeEditorImage );
	},

	/**
	 * Sets up jQuery image editor cache.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	setupImageEditorActions: function() {
		'use strict';

		jQuery( '.tsfem-set-image-button' ).off( 'click', tsfemMedia.openImageEditor );
		jQuery( '.tsfem-remove-image-button' ).off( 'click', tsfemMedia.removeEditorImage );
		jQuery( '.tsfem-set-image-button' ).on( 'click', tsfemMedia.openImageEditor );
		jQuery( '.tsfem-remove-image-button' ).on( 'click', tsfemMedia.removeEditorImage );
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @param {!jQuery} jQ jQuery
	 * @return {undefined}
	 */
	ready: function( jQ ) {
		'use strict';

		// Initialize image uploader button cache.
		jQ( document.body ).ready( tsfemMedia.setupImageEditorActions );

		// Determine image editor button input states.
		jQ( document.body ).ready( tsfemMedia.checkImageEditorInput );
	}
};
jQuery( tsfemMedia.ready );
