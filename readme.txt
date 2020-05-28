=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 4.9.0
Tested up to: 5.4.1
Requires PHP: 5.6.5
Requires TSF: 4.0.0
Stable tag: 2.3.1
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

= 2.4.0 =

**Release date:**

* TODO

**Feature highlights:**

* In this update, we reduced the plugin package size by 30%. Thanks to offloading translation files elsewhere on our servers, this reduction saves you bandwidth and speeds up plugin installation.
* During Extension Manager plugin update requests, your WordPress website may now download new and updated translation files independently. Which files are requested is based on your site's supported languages.
* We upgraded the extension API endpoint, which allows for reverse inflection lookups via the Focus extension, for 7 languages!
* We removed a filter that directed admin access control. We found that it wasn't secure enough (by our insane standards); so, use the new constant definition, instead. With that constant, you can now independently control extension and manager access.

TODO update privacy policy to reflect these changes (we now request your site's installed locale and installed translation file details of Extension Manager):
Information TSFEM sends to Us: (3) The plugin (at version 2.0.0 or later) may request plugin updates from our servers. While succesfully doing so, it sends us your WordPress version number, the PHP version number, the installed TSFEM plugin version number, your website’s IP address, and your website’s home URL. We collect this data for aggregating usage statistics, and to provide your site with the latest compatible version. The aggregated statistics will always be anonymized.

**Detailed log:**

View the [detailed v2.4.0 changelog](https://theseoframework.com/?p=TODO).

* **Added:** New constant `TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE`, that allows you to modify the access level of the extension settings in `wp-config.php` or a mu-plugin.
	* **Note:** Use `TSF_EXTENSION_MANAGER_MAIN_ADMIN_ROLE` to control the role required for managing the extension activation and API connection options.
* **Changed:** The plugin extension API now reaches our new version 2.1 endpoint, from 2.0.
	* Version 2.0 will remain available for the unforeseeable future.
* **Changed:** The plugin updater API now reaches our new version 1.1 endpoint, from 1.0.
	* Version 1.0 will remain available for the unforeseeable future.
* TODO **Updated:** Plugin translation POT file contains a few adjusted strings.
* **Removed:** We no longer ship the pomo translation files with the plugin.
	* However, the `/language/` folder still works as before, and manually inserted files therein supersede the update-service provided translations.
* **Removed:** Filter `tsf_extension_manager_can_manage_options` has been removed as it superimposes a security issue due to its nature in discrepancy, incoherency, and inconsistency. Use the constants instead; they can be defined only once, alleviating these issues altoghether.
* **Removed:** Method `tsf_extension_manager()->can_do_settings()`. Use the access control API functions, instead.

* TODO update pricing page language support.
* TODO update privacy policy (although not pressing, since we don't require any new person-identifying information).

**Updated extensions:**

* [Articles at version 2.0.4](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.4.0](https://theseoframework.com/extensions/focus/#changelog)
* [Local at version 1.1.7](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.6](https://theseoframework.com/extensions/monitor/#changelog)
	* TODO **Fixed:** when no current data can be processed (version discrepancy local vs from the server), a helpful message is now shown.
		* This only happens in development, though....

= 2.3.1 =

**Release date:**

* May 15th, 2020

**Feature highlights:**

* In this update, we focused on improving code quality for the plugin, and addressed a few issues in the updated extensions.

**Updated extensions:**

* [Articles at version 2.0.3](https://theseoframework.com/extensions/articles/#changelog)
* [Monitor at version 1.2.5](https://theseoframework.com/extensions/monitor/#changelog)
* [Local at version 1.1.6](https://theseoframework.com/extensions/local/#changelog)

**Detailed log:**

View the [detailed v2.3.1 changelog](https://theseoframework.com/?p=3557).

= 2.3.0 - Unified SEO =

**Release date:**

* December 18th, 2019

**Feature highlights:**

* We added a new extension! Called [Cord](https://theseoframework.com/extensions/cord/). It helps you integrate Google Analytics and Facebook pixel.
* The form generator has been refactored to make the settings page easier to work with.

**Updated extensions:**

* [Focus at version 1.3.2](https://theseoframework.com/extensions/focus/#changelog)
* [Articles at version 2.0.2](https://theseoframework.com/extensions/articles/#changelog)
* [Cord at version 1.0.0](https://theseoframework.com/extensions/cord/#changelog)
* [Monitor at version 1.2.4-β-5](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.3.0 changelog](https://theseoframework.com/?p=3430).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.2.0 =

This plugin now requires WordPress v4.9, PHP v5.6.5, and The SEO Framework v4.0 or higher.

= 2.1.0 =

This plugin now requires WordPress 4.8 or higher.
