=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.1.0
Tested up to: 5.5.1
Requires PHP: 5.6.5
Requires TSF: 4.0.5
Stable tag: 2.4.1
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

= 2.4.1 =

* **Added:** This plugin now supports The SEO Framework's 4.0.5+ quick-and bulk-edit functionality for its extensions.
* **Improved:** TODO Akin to TSF v4.1.1, we also dropped most jQuery calls in this plugin, greatly improving the UI's responsiveness in the administrative dashboards.
* **Fixed:** Resolved a race condition with the AJAX loader notifications.
* **Fixed:** Resolved an issue where multiple non-unique identifiers for no-JS-submit-buttons were used on the Extension Manager overview page.
* **Fixed:** Resolved an issue where quotes in metadata could cause serialization issues.
* **Dev:** Constant `TSF_EXTENSION_MANAGER_PRESENT` is now available. It indicates that the plugin is activated by WordPress, but it does not necessarily indicate that it's running otherwise.
* **Other:** The SEO Framework 4.0.5 or higher is now required, from 4.0.0.
* **Other:** WordPress 5.1 or higher is now required, from 4.9.
* **Other:** We now enforce [TSF coding standards](https://github.com/theseoframework/wpcs-tsf) for opcode performance, intercompatibility, and reliability.
* **Other:** TODO This plugin is now compatible with PHP 8.0-RC1. This ensures compatibility with PHP 8.0 when it releases to the public, but changes may be provisionary.

**Updated extensions:**

NOTE these version numbers are already correct.
* [Articles at version 2.1.0](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.4.1](https://theseoframework.com/extensions/focus/#changelog)
* [Local at version 1.1.8](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.7](https://theseoframework.com/extensions/monitor/#changelog)

TODO update images displayed for Local readme.
TODO consider adding issue "Crawl data is old, the issues may be outdated or incorrect -> please request a new crawl."
TODO consider refactoring Honeypot's readme? -> Later?

= 2.4.0 - Linguistic SEO =

**Release date:**

* June 2nd, 2020

**Feature highlights:**

* In this update, we reduced the plugin package size by 30%. Thanks to offloading translation files elsewhere on our servers, this reduction saves you bandwidth and speeds up plugin installation.
* During Extension Manager plugin update requests, your WordPress website may now download new and updated translation files independently. Which files are requested is based on your site's supported languages.
* We upgraded the extension API endpoint, which allows for reverse inflection lookups via the Focus extension, for 7 languages!
* We removed a filter that directed admin access control. We found that it wasn't secure enough (by our insane standards); so, use the new constant definition, instead. With that constant, you can now (finally) independently control extension-settings from manager access.

**Updated extensions:**

* [Articles at version 2.0.4](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.4.0](https://theseoframework.com/extensions/focus/#changelog)
* [Local at version 1.1.7](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.6](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.4.0 changelog](https://theseoframework.com/?p=3572).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.2.0 =

This plugin now requires WordPress v4.9, PHP v5.6.5, and The SEO Framework v4.0 or higher.

= 2.1.0 =

This plugin now requires WordPress 4.8 or higher.
