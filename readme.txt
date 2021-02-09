=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.1
Tested up to: 5.7
Requires PHP: 5.6.5
Requires TSF: 4.1.2
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

= 2.5.0 - Quick SEO =

**Important release notes:**

* Henceforth, Extension Manager requires **The SEO Framework v4.1.2 or higher**.
* This release brings support for WordPress 5.6's updated interface. Sorry for the delay!
* Honeypot might momentarily mark legitimate comments as spam because it looks for new anti-spam data. Please manually moderate your comment section for a few minutes after updating, and be sure to clear your site's caches directly after updating to reduce the number of false negatives significantly.

**Release date:**

* February 9th, 2021

* **Added:** This plugin now supports The SEO Framework's new quick-and bulk-edit functionality for its extensions.
	* The first extension to make use of this is Articles.
* **Improved:** Akin to TSF v4.1.1, we also dropped many jQuery calls in this plugin, greatly improving the UI's responsiveness in the administrative dashboards.
	* We didn't tackle every file we'd like, for we could not make the time.
* **Improved:** Animations are now smoother by utilising modern browser APIs.
* **Improved:** This plugin is now compatible with jQuery 3.5.
* **Fixed:** Resolved a race condition with the AJAX loader notifications.
* **Fixed:** Resolved an issue where multiple non-unique identifiers for no-JS-submit-buttons were used on the Extension Manager overview page.
* **Fixed:** Resolved an issue where double-quotes in metadata could cause serialization issues, corrupting Extension Manager's extension-post-metadata.
* **Fixed:** Resolved an issue where Extension Settings panes weren't outputted on PHP 5.6.
* **Fixed:** Resolved an issue where the installer for The SEO Framework couldn't find translatable objects from WordPress 5.5 onward.
* **Dev:** Constant `TSF_EXTENSION_MANAGER_PRESENT` is now available. It indicates that the plugin is activated by WordPress, but it does not necessarily indicate that it's running otherwise.
* **Dev:** Script debugging will no longer occur with `WP_DEBUG` enabled, but with `SCRIPT_DEBUG` enabled, instead.
* **Other:** The SEO Framework v4.1.2 or higher is now required, from v4.0.0.
* **Other:** WordPress v5.1 or higher is now required, from v4.9.
* **Other:** We now enforce [TSF coding standards](https://github.com/theseoframework/wpcs-tsf) for opcode performance, intercompatibility, and reliability.
* **Other:** This plugin is now compatible with PHP 8.0.
	* Although a new PHP version is exciting, we advise against updating until the dust has settled. PHP 8.0 brings many deprecations and breaking changes, and those will probably cause many issues on your website for months to come, until all your plugins and theme have been updated accordingly. There's also no noticeable nor notable benefit using PHP 8.0 over PHP 7.4 for WordPress.
* **Other:** We removed all script loaders in this plugin, and rely on The SEO Framework to load scripts, instead.
	* The exception is for the scripts that are used when TSF isn't available, wherefor we've been using a simpler loader.
* **Other:** We concatenated differing code from most RTL-support scripts into the LTR scripts, reducing the installation package noticably.
* **Other:** This plugin now relies on The SEO Framework's media handler, instead of its own.
	* There's a bug, in Local, where the preview-loader isn't reset properly during iteration. Dependency issues like these make TSF's API more reliable and extensible; this is probably this first time we do not address such an issue before the public can face it, but issues are becoming more intricate and time's becoming more scarce as the project advances.
* **Other:** It's 2021, so we added another year to the copyright mandate.

TODO update POT file

**Updated extensions:**

NOTE these version numbers are already correct.
* [Articles at version 2.1.0](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.5.0](https://theseoframework.com/extensions/focus/#changelog)
* [Honeypot at version 2.0.0](https://theseoframework.com/extensions/honeypot/#changelog)
* [Local at version 1.1.8](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.7](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.5.0 changelog](https://theseoframework.com/?p= TODO).

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

= 2.5.0 =

This plugin now requires WordPress TODO (samever as TSF) v5.1, and The SEO Framework v4.1.2 or higher.

= 2.2.0 =

This plugin now requires WordPress v4.9, PHP v5.6.5, and The SEO Framework v4.0 or higher.

= 2.1.0 =

This plugin now requires WordPress 4.8 or higher.
