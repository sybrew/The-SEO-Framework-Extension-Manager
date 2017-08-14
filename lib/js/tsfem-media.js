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
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/externs/tsfem.externs.js
// @externs_url https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/externs/tsfem-media.externs.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

'use strict';

/**
 * Holds tsfemMedia values in an object to avoid polluting global namespace.
 *
 * @since 1.3.0
 *
 * @constructor
 */
window.tsfemMedia = {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 1.3.0
	 * @access private
	 * @type {(Object<string, *>)|boolean} data Localized strings
	 */
	data : typeof tsfemMediaData !== 'undefined' && tsfemMediaData,

	/**
	 * Image cropper instance.
	 *
	 * @since 2.8.0
	 * @access public
	 * @type {!Object} cropper
	 */
	cropper : {},

	/**
	 * Escapes HTML class or ID keys. Doesn't double-escape.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {String} str
	 * @return {(string|undefined)} HTML to jQuery converted string
	 */
	escapeKey: function( str ) {

		if ( str )
			return str.replace( /(?!\\)(?=[\[\]\/])/g, '\\' );

		return str;
	},

	/**
	 * Opens the image editor on request.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {undefined}
	 */
	openImageEditor: function( event ) {

		if ( jQuery( event.target ).prop( 'disabled' ) || 'undefined' === typeof wp.media ) {
			return;
		}

		let $target = jQuery( event.target ),
			inputURL = $target.data( 'input-url' ),
			s_inputURL = tsfemMedia.escapeKey( inputURL ),
			inputID = $target.data( 'input-id' ),
			s_inputID = tsfemMedia.escapeKey( inputURL ),
			frame;

		if ( frame ) {
			frame.open();
			return;
		}

		//* Init extend cropper.
		tsfemMedia.extendCropper();

		frame = wp.media( {
			button : {
				'text' : tsfemMedia.data['imgFrameButton'],
				'close' : false,
			},
			states: [
				new wp.media.controller.Library( {
					'title' : tsfemMedia.data['imgFrameTitle'],
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

		let selectFunc = function() { frame.setState( 'cropper' ); };

		frame.off( 'select', selectFunc );
		frame.on( 'select', selectFunc );

		let croppedFunc = function( croppedImage ) {
			let url = croppedImage.url,
				attachmentId = croppedImage.id,
				w = croppedImage.width,
				h = croppedImage.height;

			// Send the attachment id to our hidden input. URL to explicit output.
			document.getElementById( inputURL ).value = url;
			document.getElementById( inputID ).value = attachmentId;
		};
		frame.off( 'cropped', croppedFunc );
		frame.on( 'cropped', croppedFunc );

		let skippedcropFunc = function( selection ) {
			let url = selection.get( 'url' ),
				attachmentId = selection.get( 'id' ),
				w = selection.get( 'width' ),
				h = selection.get( 'height' );

			// Send the attachment id to our hidden input. URL to explicit output.
			document.getElementById( inputURL ).value = url;
			document.getElementById( inputID ).value = attachmentId;
		};
		frame.off( 'skippedcrop', skippedcropFunc );
		frame.on( 'skippedcrop', skippedcropFunc );

		let doneFunc = function( imageSelection ) {
			jQuery( '#' + s_inputURL + '-select' ).text( tsfemMedia.data['imgChange'] );
			jQuery( document.getElementById( inputURL ) ).prop( 'readonly', true ).css( 'opacity', 0 ).animate(
				{ 'opacity' : 1 },
				{ 'queue' : true, 'duration' : 1000 },
				'swing'
			);
			let data = {};
			data.url = inputURL;
			data.id = inputID;

			tsfemMedia.appendRemoveButton( $target, data, true );

			//* Remove button active state.
			$target.trigger( 'blur' );

			tsfem.registerNavWarn();
		};
		frame.off( 'skippedcrop cropped', doneFunc );
		frame.on( 'skippedcrop cropped', doneFunc );

		frame.open();
	},

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.event.target} target jQuery event.target
	 * @param {(object|array)} input The input URL and ID.
	 * @return {(undefined|null)}
	 */
	appendRemoveButton: function( target, input, animate ) {

		if ( target && input.url ) {
			let s_inputURL = tsfemMedia.escapeKey( input.url );

			if ( ! jQuery( '#' + s_inputURL + '-remove' ).length ) {
				target.after(
					'<button type=button id="'
						+ input.url + '-remove" class="tsfem-remove-image-button tsfem-button-primary tsfem-button-small"'
						+ ' data-input-url="' + input.url + '"'
						+ ' data-input-id="' + input.id + '"'
					+ ' title="' + tsfemMedia.data['imgRemoveTitle'] + '">' + tsfemMedia.data['imgRemove'] + '</button>'
				);
				if ( animate ) {
					jQuery( '#' + s_inputURL + '-remove' ).css( 'opacity', 0 ).animate(
						{ 'opacity' : 1 },
						{ 'queue' : true, 'duration' : 1000 },
						'swing'
					);
				}
			}
		}

		//* Reset cache.
		tsfemMedia.resetImageEditorRemovalActions();
	},

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {(undefined|null)}
	 */
	removeEditorImage: function( event ) {

		let $target = jQuery( event.target ),
			inputURL = $target.data( 'input-url' ),
			inputID = $target.data( 'input-id' ),
			disabledClass = 'tsfem-button-disabled',
			selectButton = document.getElementById( inputURL + '-select' ),
			removeButton = document.getElementById( inputURL + '-remove' );

		if ( jQuery( selectButton ).prop( 'disabled' ) )
			return;

		jQuery( selectButton ).addClass( disabledClass ).prop( 'disabled', true ).text( tsfemMedia.data['imgSelect'] );

		//* target.event.id === '#' + s_inputURL + '-remove'.
		jQuery( removeButton ).addClass( disabledClass ).prop( 'disabled', true ).fadeOut( 500, function() {
			jQuery( this ).remove();
			jQuery( selectButton ).removeClass( disabledClass ).removeProp( 'disabled' );
		} );

		jQuery( document.getElementById( inputURL ) ).val( '' ).removeProp( 'readonly' ).css( 'opacity', 0 ).animate(
			{ 'opacity' : 1 },
			{ 'queue' : true, 'duration' : 500 },
			'swing'
		);

		document.getElementById( inputID ).value = '';

		tsfem.registerNavWarn();
	},

	/**
	 * Builds constructor for media cropper.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	extendCropper: function() {

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

				return wp.ajax.post( 'tsfem_crop_image', {
					'nonce' : tsfemMedia.data.nonce,
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
	 * @access private
	 *
	 * @function
	 * @param {wp.media.model.Attachment} attachment
	 * @param {wp.media.controller.Cropper} controller
	 * @return {Object} imgSelectOptions
	 */
	calculateImageSelectOptions: function( attachment, controller ) {

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

		let $buttons = jQuery( '.tsfem-set-image-button' );

		if ( $buttons.length ) {
			let inputURL, inputID,
				$button,
				data = {};

			jQuery.each( $buttons, function( index, value ) {
				$button = jQuery( value );
				inputURL = document.getElementById( $button.data( 'input-url' ) );
				inputID = document.getElementById( $button.data( 'input-id' ) );

				if ( inputID.length && inputID.value > 0 ) {
					data.url = $button.data( 'input-url' );
					data.id = $button.data( 'input-id' );

					jQuery( inputURL ).prop( 'readonly', true );
					tsfemMedia.appendRemoveButton( $button, data, false );
				}

				if ( inputURL.value ) {
					let s_inputID = tsfemMedia.escapeKey( $button.data( 'input-id' ) );
					jQuery( '#' + s_inputID + '-select' ).text( tsfemMedia.data['imgChange'] );
				}
			} );
		}
	},

	/**
	 * Resets jQuery image editor cache for when the removal button appears.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	resetImageEditorRemovalActions: function() {
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

		let setup = function() {
			jQuery( '.tsfem-set-image-button' ).off( 'click', tsfemMedia.openImageEditor );
			jQuery( '.tsfem-remove-image-button' ).off( 'click', tsfemMedia.removeEditorImage );
			jQuery( '.tsfem-set-image-button' ).on( 'click', tsfemMedia.openImageEditor );
			jQuery( '.tsfem-remove-image-button' ).on( 'click', tsfemMedia.removeEditorImage );
		}
		setup();

		// Reset image uploader button cache on iteration completion.
		jQuery( window ).on( 'tsfemForm.iterationComplete tsfemForm.deiterationComplete', setup );
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery} jQ jQuery
	 * @return {undefined}
	 */
	ready: function( jQ ) {

		// Initialize image uploader button cache.
		jQ( document.body ).ready( tsfemMedia.setupImageEditorActions );

		// Determine image editor button input states.
		jQ( document.body ).ready( tsfemMedia.checkImageEditorInput );
	}
};
jQuery( tsfemMedia.ready );