=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.5
Tested up to: 5.9
Requires PHP: 7.3.0
Requires TSF: 4.2.4
Stable tag: 2.5.3
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

= 2.6.0 =

* Extension Manager now requires PHP 7.3.0 or higher, from PHP 5.6.5.
* The SEO Framework v4.2 or higher is now required, from TSF v4.1.4.
* Touched up the interface, it's now centered, more compact, and easier on the eyes.
* Now relies on The SEO Framework's JavaScript availability test, instead of WordPress's, making unresponsive interfaces less likely when a faulty plugin or theme is installed.
* Modernized PHP code, making the plugin up to 30% faster.
	* TODO test.
* Modernized some JavaScript code, improving UI responsiveness significantly.
* Reduced plugin file size relatively by no longer packing rendered vector images for archaic browser support.
* Introduced a new API alias for `tsf_extension_manager()`: `tsfem()`.
* With this update we hit a new milestone: 2 million characters of amazing code.
* The plugin and its extensions now support PHP 8.2 flawlessly.
TODO POT file. (also update related github)

= 2.5.3 =

**Release date:**

* May 2nd, 2022

**Feature highlights:**

* Improved overall performance of the plugin by refactoring various parts using our latest coding standards.

**API Updates:**

* Jan 31st, 2022: Duplicated domain activations no longer count toward the activation limit.
* Jan 31st, 2022: Duplicated instance activations now automatically switch sites -- this helps ease migrating from staging to production, and vice versa.

**Updated extensions:**

* [Articles at version 2.2.0](https://theseoframework.com/extensions/articles/#changelog)

**Detailed log**

* **Improved:** WordPress update nags no longer cause a shadow effect on Extension Manager pages.

View the [detailed v2.5.2 changelog](https://tsf.fyi/p/3890).

= 2.5.2 =

**Release date:**

* November 9th, 2021

**Feature highlights:**

* This update addresses a regression brought in v2.5.1 where upper-case file paths were no longer recognized for.

**Detailed log:**

View the [detailed v2.5.2 changelog](https://tsf.fyi/p/3782).

= 2.5.1 =

**Release date:**

* November 8th, 2021

**Feature highlights:**

* Extension Manager is now fully prepared for the imminent release of TSF v4.2.0.
* The 2001 error is now less likely to get invoked.
* Asset URLs are now generated correctly on Windows-based servers.

**Updated extensions:**

* [Articles at version 2.1.1](https://theseoframework.com/extensions/articles/#changelog)
* [Local at version 1.1.9](https://theseoframework.com/extensions/local/#changelog)
* [Focus at version 1.5.1](https://theseoframework.com/extensions/focus/#changelog)

**Detailed log:**

View the [detailed v2.5.1 changelog](https://tsf.fyi/p/3779).

= 2.5.0 - Quick SEO =

**Release date:**

* February 9th, 2021

**Feature highlights:**

In this update, we added quick-and bulk-edit support, improved browser scripting performance, and improved various extensions. Most notably, Honeypot catches modernized spammers, Articles can now be disabled per-post, and Focus's API is vastly expanded.

**Transportant release notes:**

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

= 2.5.3 =

This plugin now requires WordPress v5.5 or higher.

= 2.5.0 =

This plugin now requires WordPress v5.1 and The SEO Framework v4.1.2 or higher.
