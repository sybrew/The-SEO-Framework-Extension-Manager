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

= 2.3.1 =

**Release date:**

* TODO

**Detailed log:**

* **Changed:**
	* The version compatibility testing can now be done against major releases, instead of only minor.
* **Updated:** The Russian translation files. Thank you, [Vitaliy](https://profiles.wordpress.org/vit-1/)!
* **Updated:** The Spanish translation files. Thank you, [Manuel](https://profiles.wordpress.org/mbrsolution/)!
* **Fixed:** The plugin now gets rid of all extraneous admin footer texts on the Extension Manager setting pages.

**Updated extensions:**

* [Articles at version 2.0.3](https://theseoframework.com/extensions/articles/#changelog).
	* TODO Added: The Article type can now be set via quick-edit and bulk-edit.
		* This feature requires TSF 4.0.5. (`the_seo_framework_list_table_data`, `the_seo_framework_after_bulk_edit`, `the_seo_framework_after_quick_edit`).
* [Monitor at version 1.2.5](https://theseoframework.com/extensions/monitor/#changelog).
	* TODO assert redirect and get page ID by URL -> test Description & Title tag?
* [Local at version 1.1.6](https://theseoframework.com/extensions/local/#changelog).

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

* **Added:**
	* A brand new extension, called Cord!
	* The `info` notice type. These are highlighted via a blue color, with a question mark at the side.
* **Improved:**
	* The extension settings fields now leave a little more space for the inputs, depending on the description size.
	* The extension settings interface is much snappier, thanks to refactorization of old sluggish code.
	* The extension settings collapsible items are now validated on-load, instead of when expanding the items.
		* This has been done for improved accessibility. The trade-off is that this will negatively affect browser-performance when loading in hundreds of Local departments.
	* Various server-sided adjustments have been made which improve performance.
	* We found a workaround with the non-Webkit/Blink rendering of the shrinking gridboxes. Enjoy a neat interface on Edge and Firefox now, too!
* **Changed:**
	* The extensions are now autoloaded in the order they're presented, instead of the order in which they're activated.
* **Other:**
	* We expanded the form-generator, where it now accepts various plain and dropdown fields.
	* The form-generator no longer parses the dropdown titles on the server. It now lets your browser take care of that.
	* All WordPress Filter/Action/Dependency API callbacks to static methods are no longer concatenated, but are instead put in an array.
	* Updated translation POT file.
* **Fixed:**
	* The available PHP memory is now asserted correctly during upgrades. Before, upgrading went to the absolute limit before deferring, resulting in memory exhaustion.
	* A browser memory leak and CPU job heaping after repeatedly adding extension settings form iterations.
	* The correct script is now called for the installation of The SEO Framework, making for the intended visualized experience. We changed the names during development that called stale scripts that weren't in the final version earlier.
	* The default extension options are no longer repopulated erroneously in the form-generator when cleared.

**Detailed log:**

View the [detailed v2.3.0 changelog](https://theseoframework.com/?p=3430).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.2.0 =

This plugin now requires WordPress v4.9, PHP v5.6.5, and The SEO Framework v4.0 or higher.

= 2.1.0 =

This plugin now requires WordPress 4.8 or higher.
