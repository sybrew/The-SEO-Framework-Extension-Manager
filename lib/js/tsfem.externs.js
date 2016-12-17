/**
 * This file holds The SEO Framework Extension Manager plugin's JS code externs
 * for Google's Closure Compiler.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://wordpress.org/plugins/the-seo-framework-extension-manager/
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

/**
 * @fileoverview Externs for The SEO Framework Extension Manager tsfem.js
 *
 * This file acts as a sort of interface of all public tsfem JS object methods.
 *
 * @see https://github.com/sybrew/The-SEO-Framework-Extension-Manager
 * @see https://developers.theseoframework.com/ (i.e. https://theseoframework.com/api/)
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
 * @type {string}
 */
tsfemL10n.prototype.nonce;

/**
 * @type {number}
 */
tsfemL10n.prototype.debug;

/**
 * @type {!Object<string, string>}
 */
tsfemL10n.prototype.i18n = {};

/**
 * @type {number}
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
