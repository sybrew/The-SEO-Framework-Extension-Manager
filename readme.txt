=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.1
Tested up to: 5.7
Requires PHP: 5.6.5
Requires TSF: 4.1.4
Stable tag: 2.5.0
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

= 2.5.1 =

TODO see if we can get rid of 'require_once', which is the biggest performance hindrance.
TODO see if we can improve version_compare_lenient(), which is the slowest method.
	-> Simply add memoization?
TODO test PHP requirement for update.
TODO fix "update notice"
TODO if PHP 7+ is used, populate second parameter of unserialize with 'false'.
	-> 2.6.0?

* **Improved:** Extensions now load a tad faster.
* **Fixed:** Resolved an issue where asset-URLs were generated incorrectly for extensions in the admin area on Windows-based servers. Props [Vitaliy].(https://github.com/sybrew/The-SEO-Framework-Extension-Manager/issues/45)
* **Fixed:** Regression for JS debug states.
* TODO migrate the admin colors (for WP5.7, via TSF), dump the pngs?
* TODO incorporate fix for TSF's media.js iteration?
	-> Shouldn't TSF handle this fully, no?
* TODO: https://github.com/sybrew/The-SEO-Framework-Extension-Manager/issues/37
	-> Should we test if $new === $old then return earlier?
		-> Gotta POC it.
* TODO: Issue 6001/2001 errors... we could circumvent it by using unique option indexes per domain, so that, when transfering domains, the keys must revalidate --- this prevents option hash mismatches.
* TODO: Tell how to attach/change images for Cord: Media Library (list view) -> Uploaded to -> Attach.
* TODO: When TSF's Headless mode is active, disable the page-specific notification systems.

* **Updated extensions:**
	* Articles @ Version 2.1.1:
		* **Changed:** Now uses WordPress's timesystem.
	* Local @ Version 1.1.9
		* **Fixed:** You can now store and test your input correctly when the first department is disabled.
	* Cord @ Version ??
		* TODO **Added:** Google Analytics 4 support. New settings are added for this.


= 2.5.0 - Quick SEO =

**Release date:**

* February 9th, 2021

**Feature highlights:**

In this update, we added quick-and bulk-edit support, improved browser scripting performance, and improved various extensions. Most notably, Honeypot catches modernized spammers, Articles can now be disabled per-post, and Focus's API is vastly expanded.

**Important release notes:**

* Henceforth, Extension Manager requires **The SEO Framework v4.1.2 or higher**.
* This release brings support for WordPress v5.6's updated interface. Sorry for the delay!
* Honeypot might momentarily mark legitimate comments as spam because it looks for new anti-spam data. Please manually moderate your comment section for a few minutes after updating, and be sure to clear your site's caches directly after updating to reduce the number of false negatives significantly.

**Updated extensions:**

* [Articles at version 2.1.0](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.5.0](https://theseoframework.com/extensions/focus/#changelog)
* [Honeypot at version 2.0.0](https://theseoframework.com/extensions/honeypot/#changelog)
* [Local at version 1.1.8](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.7](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.5.0 changelog](https://theseoframework.com/?p=3686).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.5.1 =

This plugin now requires WordPress v5.4 and The SEO Framework v4.1.4 or higher.

= 2.5.0 =

This plugin now requires WordPress v5.1 and The SEO Framework v4.1.2 or higher.
