=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.1
Tested up to: 5.6
Requires PHP: 5.6.5
Requires TSF: 4.0.5
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

= 2.5.0 - TODO SEO =

**To use this plugin after updating, you will need The SEO Framework v4.1.2 or higher.**

**This release brings support for WordPress 5.6's updated interface. Sorry for the delay!**

**Release date:**

* February TODOnth, 2021

* **Added:** This plugin now supports The SEO Framework's new quick-and bulk-edit functionality for its extensions.
	* The first extension to make use of this is Articles.
* **Improved:** TODO Akin to TSF v4.1.1, we also dropped many jQuery calls in this plugin, greatly improving the UI's responsiveness in the administrative dashboards.
	* TODO Note to self: We already did... can we plan for more before PHP 8.0 launches?
* **Improved:** Animations are now smoother by utilising modern browser APIs.
* **Improved:** This plugin is now compatible with jQuery 3.5.
* **Fixed:** Resolved a race condition with the AJAX loader notifications.
* **Fixed:** Resolved an issue where multiple non-unique identifiers for no-JS-submit-buttons were used on the Extension Manager overview page.
* **Fixed:** Resolved an issue where double-quotes in metadata could cause serialization issues, corrupting Extension Manager's extension-post-metadata.
* **Fixed:** Resolved an issue where Extension Settings panes weren't outputted on PHP 5.6.
* **Dev:** Constant `TSF_EXTENSION_MANAGER_PRESENT` is now available. It indicates that the plugin is activated by WordPress, but it does not necessarily indicate that it's running otherwise.
* **Dev:** Script debugging will no longer occur with `WP_DEBUG` enabled, but with `SCRIPT_DEBUG` enabled, instead.
* **Other:** The SEO Framework 4.1.2 or higher is now required, from 4.0.0.
* **Other:** WordPress 5.1 or higher is now required, from 4.9.
* **Other:** We now enforce [TSF coding standards](https://github.com/theseoframework/wpcs-tsf) for opcode performance, intercompatibility, and reliability.
* **Other:** This plugin is now compatible with PHP 8.0.0-RC1<https://wiki.php.net/todo/php80>. This ensures compatibility with PHP 8.0.0 when it becomes generally available, but changes may be provisionary.
	* Although a new PHP version is exciting, we advise against updating until the dust has settled. PHP 8.0 brings many deprecations and breaking changes, and those will probably cause many issues on your website for months to come, until all your plugins and theme have been updated accordingly. There's also no noticeable nor notable benefit using PHP 8.0 over PHP 7.4 for WordPress.
* **Other:** We removed all script loaders in this plugin, and rely on The SEO Framework to load scripts, instead.
	* The exception is for the scripts that are used when TSF isn't available, wherefor we've been using a simpler loader.

TODO for honeypot: Add a hidden input field with just a bit of JS with value 0. The JS should add 10 seconds to the field, after which it counts down. If the field contains a value other than empty/0, then honeypot will spam the comment.

TODO https://wordpress.org/support/topic/paginated-sitemap-like-google-xml-sitemaps/
	-> We probably can't.
TODO fix index.php todos
TODO remove all images, and rely on SVG only?
TODO remove feed? Our plans never came to fruition for it...
TODO convert all JS objects to functions?
TODO update images displayed for Local readme.
TODO consider adding issue "Crawl data is old, the issues may be outdated or incorrect -> please request a new crawl."
TODO consider refactoring Honeypot's readme? -> Later?
TODO .tsfem-e-focus-assessment-rating & tsfem-e-focus-assessment-title-wrap need rtl margins.
TODO homographic example selector's dropdown isn't hidden with minified scripts on WP 5.6

TODO installer scripts does not refill "installed"-notice correctly nor refill the loader button neatly on WP 5.6.
TODO this plugin and extensions are compatible up to WP 5.6.
TODO consider setKeywordEntryListeners()/setupIterations() using animationFrames? -> later?

TODO 2020 -> 2021

TODO update:
https://theseoframework.com/docs/api/constants/

**Updated extensions:**

NOTE these version numbers are already correct.
* [Articles at version 2.1.0](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.5.0](https://theseoframework.com/extensions/focus/#changelog)
* [Local at version 1.1.8](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.7](https://theseoframework.com/extensions/monitor/#changelog)


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
