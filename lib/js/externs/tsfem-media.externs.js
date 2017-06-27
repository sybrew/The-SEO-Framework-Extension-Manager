/**
 * This file holds The SEO Framework Extension Manager plugin's Media JS code
 * externs for Google's Closure Compiler.
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

/**
 * @fileoverview Externs for The SEO Framework Extension Manager tsfem-media.js
 *
 * This file acts as a sort of interface of all public tsfem JS object methods.
 *
 * @see https://github.com/sybrew/The-SEO-Framework-Extension-Manager
 * @see https://developer.theseoframework.com/ (i.e. https://theseoframework.com/api/)
 * @see https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/tsfem-media.externs.js
 * @externs
 */

/**
 * @constructor
 * @struct
 */
function tsfemMedia() {};

/**
 * @const {Object<string, ?>}
 */
tsfemMedia.cropper = {};

/**
 * @type {(Object<string, *>)}
 * @const
 */
var tsfemMediaL10n = {};

/**
 * @constructor
 * @struct
 */
function wp() {};

/**
 * @const
 * @type {!Object<*, *>}
 */
wp.prototype.media = {};

/**
 * @type {!Object<*, *>}
 */
wp.media.prototype.controller = {};

/**
 * @param {*=} arg1
 * @return {*}
 */
wp.media.prototype.query = function( arg1 ) {};

/**
 * @type {!Object<*, *>}
 */
wp.media.controller.prototype.Cropper = {};

/**
 * @type {!Object<*, *>}
 */
wp.media.controller.prototype.Library = {};

/**
 * @type {!Object<*, *>}
 */
wp.prototype.ajax = {};

/**
 * @param {string} action
 * @param {*=} arg2
 * @return {*}
 */
wp.ajax.prototype.post = function( action, arg2 ) {};

/**
 * @constructor {Object.wp}
 * @struct
 */
var frame = {};

/**
 * @param {string} action
 * @param {*=} arg2
 * @return {undefined}
 */
frame.prototype.on = function( action, arg2 ) {};

/**
 * @return {undefined}
 */
frame.prototype.open = function() {};

/**
 * @param {string} state
 * @return {undefined}
 */
frame.prototype.setState = function( state ) {};

/**
 * @constructor
 * @param {!Object} attachment
 * @return {!jQuery.jqXHR}
 */
function doCrop( attachment ) {};

/**
 * @param {string} arg1
 * @return {!Object}
 * @nosideeffects
 */
doCrop.prototype.get = function( arg1 ) {};

/**
 * @return {string}
 * @nosideeffects
 */
doCrop.get.prototype.edit = function() {};
