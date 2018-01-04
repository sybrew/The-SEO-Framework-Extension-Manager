/**
 * This file holds The SEO Framework Extension Manager plugin's Monitor extension
 * JS code externs for Google's Closure Compiler.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://wordpress.org/plugins/the-seo-framework-extension-manager/
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

/**
 * @fileoverview Externs for The SEO Framework - Extension Manager - Monitor Extension tsfem-monitor.js
 *
 * This file acts as a sort of interface of all public tsfem_e_monitor JS object methods.
 *
 * @see https://github.com/sybrew/The-SEO-Framework-Extension-Manager
 * @see https://developer.theseoframework.com/ (i.e. https://theseoframework.com/api/)
 * @see https://raw.githubusercontent.com/sybrew/The-SEO-Framework-Extension-Manager/master/extensions/premium/monitor/trunk/lib/js/externs/tsfem-monitor.externs.js
 * @externs
 */

/**
 * @type {object}
 * @const
 */
var tsfem_e_monitor = {};

/**
 * @type {object|null}
 * @const
 */
var tsfem_e_monitorL10n = {};

/**
 * @type {string}
 */
tsfem_e_monitorL10n.prototype.nonce;

/**
 * @type {!Object<string, string>}
 */
tsfem_e_monitorL10n.prototype.i18n = {};

/**
 * @type {number}
 */
tsfem_e_monitorL10n.prototype.remote_data_timeout;

/**
 * @type {number}
 */
tsfem_e_monitorL10n.prototype.remote_crawl_timeout;
