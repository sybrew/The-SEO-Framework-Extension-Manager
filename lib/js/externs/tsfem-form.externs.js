/**
 * This file holds The SEO Framework Extension Manager plugin's Form JS code
 * externs for Google's Closure Compiler.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://theseoframework.com/extension-manager/
 */

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @fileoverview Externs for The SEO Framework Extension Manager tsfem-form.js
 * @ignore: Outdated.
 *
 * This file acts as a sort of interface of all public tsfem JS object methods.
 *
 * @see https://github.com/sybrew/The-SEO-Framework-Extension-Manager
 * @see https://developer.theseoframework.com/ (i.e. https://theseoframework.com/api/)
 * @see https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/lib/js/externs/tsfem-form.externs.js
 * @externs
 */

/**
 * @type {Object}
 * @const
 */
var tsfemForm = {};

/**
 * @param {Element} arg1
 * @param {string} arg2
 * @return {boolean}
 */
tsfem.doValidityRoutine = function(arg1, arg2) {};

/**
 * @param {event.target} arg1
 * @return {boolean}
 */
tsfem.enableButton = function(arg1) {};

/**
 * @param {event.target} arg1
 * @param {boolean} arg2
 * @return {undefined}
 */
tsfem.disableButton = function(arg1, arg2) {};

/**
 * @type {!Object<string, string>}
 * @const
 */
var dataset = {};

/**
 * Dataset collection.
 * This is really a DOMStringMap but it behaves close enough to an object to
 * pass as an object.
 * @type {!Object<string, string>}
 * @const
 */
HTMLElement.prototype.dataset;

/**
 * @type {(Object<?, ?>|?string|Array<?>|undefined)}
 * @nosideeffects
 */
jQuery.prototype.dataset;
