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
 * @see https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/tsfem.externs.js
 * @externs
 */

/**
 * @type {Object}
 * @const
 */
var tsfem = {};

/**
 * @type {boolean|null}
 */
tsfem.debug;

/**
 * @type {boolean|null}
 */
tsfem.rtl;

/**
 * @type {boolean}
 */
tsfem.touchbuffer;

/**
 * @type {(Object<string, *>|Array<Object<string, *>>)}
 * @const
 */
var tsfemL10n = {};

/**
 * @type {string}
 */
var ajaxurl;

/**
 * @type {string|null}
 */
tsfemL10n.nonce;

/**
 * @type {boolean|null|undefined}
 */
tsfemL10n.debug;

/**
 * @const {Object<string, string>|null}
 */
tsfemL10n.i18n = {};

/**
 * @type {boolean}
 */
tsfemL10n.touchBuffer;

/**
 * @type {boolean|undefined}
 */
tsfemL10n.rtl = {};

/**
 * @type {string|null}
 */
tsfemL10n.prototype.nonce;

/**
 * @type {boolean|undefined|null}
 */
tsfemL10n.prototype.debug;

/**
 * @const {!Object<string, string>}
 */
tsfemL10n.prototype.i18n = {};

/**
 * @const {boolean|undefined|null}
 */
tsfemL10n.prototype.rtl;

/**
 * @param {Element} arg1
 * @param {string} arg2
 * @return {undefined}
 */
tsfem.doTooltip = function(arg1, arg2) {};

/**
 * @param {Element} arg1
 * @return {undefined}
 */
tsfem.removeTooltip = function(arg1) {};

/**
 * @param {Element} arg1
 * @return {!jQuery}
 */
tsfem.getTooltip = function(arg1) {};

/**
 * @param {(jQuery.element|Element|string)} arg1
 * @return {!jQuery}
 */
tsfem.setAjaxLoader = function(arg1) {};

/**
 * @param {number} arg1
 * @return {undefined}
 */
tsfem.setTouchBuffer = function(arg1) {};

/**
 * @return {undefined}
 */
tsfem.initDescHover = function() {};

/**
 * @param {string} arg1
 * @param {number} arg2
 * @param {string} arg3
 * @param {number} arg4
 * @return {!jQuery}
 */
tsfem.unsetAjaxLoader = function(arg1, arg2, arg3, arg4) {};

/**
 * @param {(jQuery.element|Element|string)} arg1
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
tsfem.updatedResponse = function(arg1, arg2, arg3, arg4) {};

/**
 * @param {(string|Object)} arg1
 * @param {string} arg2
 * @param {string} arg3
 * @return {(Object|Boolean)}
 */
tsfem.matosa = function(arg1, arg2, arg3) {};

/**
 * @param {(Object|array|string|undefined)} arg1
 * @return {(Object|array|undefined)}
 */
tsfem.convertJSONResponse = function(arg1) {};

/**
 * @param {(jQuery.xhr|Object)} arg1
 * @param {string} arg2
 * @param {string} arg3
 * @return {string}
 */
tsfem.getAjaxError = function(arg1, arg2, arg3) {};

/**
 * @param {object} response
 * @return {undefined}
 */
tsfem.unexpectedAjaxErrorNotice = function(arg1) {};

/**
 * @return {undefined}
 */
tsfem.registerNavWarn = function() {};

/**
 * @return {boolean}
 */
tsfem.mustNavWarn = function() {};

/**
 * @param {number} arg1
 * @param {(string|undefined)} arg2
 * @return {undefined}
 */
tsfem.setTopNotice = function(arg1) {};

/**
 * @param {object} arg1
 * @return {undefined}
 */
tsfem.dialog = function(arg1) {};

/**
 * @param {element} arg1
 * @param {number} arg2
 * @param {boolean} arg3
 * @return {undefined}
 */
tsfem.fadeIn = function(arg1, arg2, arg3) {};

/**
 * @param {element} arg1
 * @param {number} arg2
 * @return {undefined}
 */
tsfem.fadeOut = function(arg1, arg2) {};
