/**
 * This file holds The SEO Framework Extension Manager plugin's JS code externs
 * for Google's Closure Compiler.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://wordpress.org/plugins/the-seo-framework-extension-manager/
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @fileoverview Externs for The SEO Framework Extension Manager tsfem.js
 *
 * This file acts as a sort of interface of all public tsfem JS object methods.
 *
 * @see https://github.com/sybrew/The-SEO-Framework-Extension-Manager
 * @see https://developer.theseoframework.com/ (i.e. https://theseoframework.com/api/)
 * @externs
 */

/**
 * @constructor
 */
function tsfem() {};

/**
 * @type {Object}
 * @const
 */
var tsfem = {};

/**
 * @type {(Object<string, *>|Array<Object<string, *>>)}
 * @const
 */
var tsfemL10n = {};

/**
 * @type {String}
 */
tsfemL10n.prototype.nonce;

/**
 * @type {Boolean|undefined}
 */
tsfemL10n.prototype.debug;

/**
 * @type {!Object<string, string>}
 */
tsfemL10n.prototype.i18n = {};

/**
 * @type {Boolean|undefined}
 */
tsfemL10n.prototype.rtl;

/**
 * @param {(function(String))} arg1
 * @return {!jQuery}
 */
tsfem.setAjaxLoader = function(arg1) {};

/**
 * @param {(function(String))} arg1
 * @return {!jQuery}
 */
tsfem.resetAjaxLoader = function(arg1) {};

/**
 * @param {string} arg1
 * @param {number} arg2
 * @param {string} arg3
 * @param {number} arg4
 * @return {!jQuery}
 */
tsfem.unsetAjaxLoader = function(arg1, arg2, arg3, arg4) {};

/**
 * @param {string} arg1
 * @param {number} arg2
 * @param {string} arg3
 * @param {number} arg4
 * @return {!jQuery}
 */
tsfem.updatedResponse = function(arg1, arg2, arg3, arg4) {};

/**
 * @param {(String|Object)} arg1
 * @param {String} arg2
 * @param {String} arg3
 * @return {(Object|Boolean)}
 */
tsfem.matosa = function(arg1, arg2, arg3) {};

/**
 * @param {(Object|Array|String|Undefined)} arg1
 * @return {(Object|Array|Undefined)}
 */
tsfem.convertJSONResponse = function(arg1) { }

/**
 * @param {(jQuery.xhr|jqXHR|Object)} arg1
 * @param {String} arg2
 * @param {String} arg3
 * @return {String}
 */
tsfem.getAjaxError = function(arg1, arg2, arg3) { }
