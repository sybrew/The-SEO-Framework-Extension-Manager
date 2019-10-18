=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 4.9.0
Tested up to: 5.3
Requires PHP: 5.5.21
Stable tag: 2.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add more powerful SEO features to The SEO Framework right from your WordPress dashboard. No sign-up is required.

== Description ==

**Advanced and powerful SEO.**
**Delivered through extension for [The SEO Framework](https://wordpress.org/plugins/autodescription/).**

The Extension Manager is a WordPress plugin that gives access to more advanced and powerful SEO for The SEO Framework via extensions.

= Privacy =

View our detailed [Plugin Privacy Policy](https://theseoframework.com/privacy-policy/#pluginprivacyinformation).

Do you have questions about privacy? Feel free to [contact us](https://theseoframework.com/contact/).

= Translation credits =

* Portuguese (Brasil): [Douglas Ferraz](https://profiles.wordpress.org/douglasferraz89/)
* Russian: [Vitaliy Ralle](https://profiles.wordpress.org/vit-1/)
* Spanish: [Manuel Ballesta Ruiz](https://profiles.wordpress.org/mbrsolution/)
* Turkish: [Mehmet Genç](https://profiles.wordpress.org/gncmhmt/)

= Contributing =

Learn [how to contribute](https://tsf.fyi/contributing).

== Installation ==

Please refer to [the installation instructions on our website](https://kb.theseoframework.com/kb/how-to-install-the-extension-manager/).

== Changelog ==

TODO repurpose these for the new settings interface?
monitor/externs/index.php:
/**
 * The true university of these days is a collection of books.
 *
 * - Thomas Carlyle
 */

local/externs/index.php:
/**
 * The desire to annoy no one, to harm no one, can equally well be the sign
 * of a just as of an anxious disposition.
 *
 * - Friedrich Nietzsche
 */


= 2.1.1 =

**Release date:**

* TODO TBA

**Feature highlights:**

TODO

**Detailed log:**

* Fixed vertical alignment on various elements for Chromium v77. Most prominently affecting Chrome v77+.
* Fixed vertical alignment on select elements for WordPress v5.3. Most prominently affecting, again, Chrome v77+.
* We sacrificed some eye-candy in favor for accessibility and coherency with the updated WordPress v5.3 interface.
* TODO We added the Extension Manager and extension-loader boot-time to the new "HTML boot-time" indicator of The SEO Framework v4.0.
* We removed all archaic browser vendor prefixes from the CSS files, so to reduce the stylesheet payload.
* We repackaged all JS files via Babel, whereras before we used Google's Closure Compiler.
* The SEO Framework v3.3 or later is now required (from v3.1).
* WP v4.9 is now required (from v4.8).
* TODO Local seems to have received a hard pentaly in script-performance since Chrome v77... (hold/type key filling in department name, etc.)... assess why and fix (non-minified is also affected).
* TODO Fixed: Post-Revision metadata can now be processed, just like in TSF v4.0+.

**Updated extensions:**

* TODO Articles
* TODO (script cleanup only) Focus
* TODO (script cleanup only) Monitor

= 2.1.0 - Lucid SEO =

**Release date:**

* August 20th, 2019

**A major release without a new extension:**

* We had [one extension](https://github.com/sybrew/The-SEO-Framework-Extension-Manager/tree/cord-transporter/extensions/free/cord/trunk) planned, but [Site Kit by Google](https://sitekit.withgoogle.com/) is already implementing most of the ideas we had. We're keeping an eye on Google's progression, and we may set up connections via their plugin instead.

**Feature highlights:**

* Support for the upcoming TSF v4.0 release has been added.
* Requests to the new European API are no longer rerouted via our global API.
* Improved performance, especially on IIS-powered servers.
* Several QOL-improvements, like better accessibility, extended API, etc. have been added.

**Updated extensions:**

* [AMP](https://theseoframework.com/extensions/amp/#changelog)
* [Articles](https://theseoframework.com/extensions/articles/#changelog)
* [Local](https://theseoframework.com/extensions/local/#changelog)

**Detailed log:**

View the [detailed v2.1.0 changelog](https://theseoframework.com/?p=3236).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.1.0 =

This plugin now requires WordPress 4.8 or higher.
