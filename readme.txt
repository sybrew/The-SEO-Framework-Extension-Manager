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

local/externs/index.php:
/**
 * The desire to annoy no one, to harm no one, can equally well be the sign
 * of a just as of an anxious disposition.
 *
 * - Friedrich Nietzsche
 */


= 2.2.0 =

**Release date:**

* TODO TBA

**Feature highlights:**

* We had to concede on the user interface: It was not acccessible, and the browser couldn't handle it well. Far too often we got requests on "how do I do this" while it was only a matter of scrolling down inside an element. We also found that, for example in Focus (when working with over 100,000 input fields), the user interface became unresponsive. So, we reworked the interface:
	* The base interface no longer relies on flexbox, but on grid.
	* The base interface no longer tries to fit its contents, but expands automatically outward. It now relies on body-scrolling, instead of element-scrolling.
	* To still allow easily-accessible actions, we implemented a sticky header that goes down when you scroll.
		* Therein, we added saving/previewing actions for Focus.
		* The actionable notifications now recide within the sticky header. They can cover up a part of the page.
* We dropped support for The SEO Framework v3.2.4 and below. TSF v3.3 and higher is now required.
* We dropped support for WordPress v4.8. WP v4.9 and higher are now required.
* We dropped support for IE11 and all other browsers of that era. A modern browser is now required to use the plugin's interface as intended.
* We dropped support for PHP 5.5. PHP 5.6 or higher is now required.

* **NOTE:** Firefox v70 (October 22, 2019) or later is now required. Chrome v77 (September 10, 2019) or later is now required.
	* If the layout looks blatantly wrong (that is, items overlapping), go to "Menu -> About -> About Firefox/Chrome" and an update should be available.
	* Note that Microsoft Edge is supported; however, the layout doesn't render as neatly as intended.
	* All hope for IE11 is lost.

**Detailed log:**

* **Added:** We added a new options-interface, which can be populated by extensions on demand.
* TODO **Added:** The plugin now nags you that it requires The SEO Framework, with an installation/activation button.
	* https://github.com/sybrew/the-seo-framework/issues/302
* **Added:** New extension logos. They're now luminous.
* **Improved:** The interface has been rewritten to use grid instead of flexbox.
* **Improved:** The interface no longer tries to find UI boundaries, improving the performance greatly--especially with Focus.
* **Improved:** The interface is now in line with WordPress 5.3.
* **Improved:** The interface buttons are now more accessible. For instance, keyboard navigational hints are easier to identify, and we added a border to support high-contrast display.
* **Improved:** The top header is now sticky.
* **Improved:** The header-notifications are now also sticky, and have a backdrop so to easily discern them from the content.
* **Improved:** We repackaged all JS files via Babel, whereras before we used Google's Closure Compiler.
* **Improved:** Tabindexing-hooks no longer occur on items that don't have a tooltip, improving accessibility.
* **Improved:** The form validator now tries to align your scrolling position to 1/3rd of the screen.
* **Performance:** The sanitization of all (administrative) links now check for HTTPS prior to HTTP.
* **Changed:** We sacrificed some eye-candy in favor for accessibility and coherency with the updated WordPress v5.3 interface.
* **Fixed:** vertical alignment on various elements for Chromium v77. Most prominently affecting Chrome v77+.
* **Fixed:** vertical alignment on select elements for WordPress v5.3. Most prominently affecting, again, Chrome v77+.
* **Fixed:** The image cropper works again for images above 4096 pixels in either width or height.
* **Fixed:** The trends now work with the updated RSS feed.
* **Removed:** Support for all archaic browser vendor prefixes from the CSS files, so to reduce the stylesheet payload.
* **Other:** The SEO Framework v3.3 or later is now required (from v3.1).
* **Other:** WordPress v4.9 is now required (from v4.8).
* **Other:** PHP v5.6.5 is now required (from v5.5.21).
* **Other:** We now use a new engine for minifying the JS files. See https://github.com/sybrew/babel-tsf.
* **Other:** We cleaned up some code.

* **Added:** New extension settings page, class, and callbacks. These apply to all settings found on the new Extension Settings page.
	* **New filters:**
		* `tsf_extension_manager_register_extension_settings`.
		* `tsf_extension_manager_register_extension_settings_defaults`
		* `tsf_extension_manager_register_extension_settings_sanitization`.
	* **New actions:**
		* `tsfem_register_settings_sanitization`
		* `tsfem_register_settings_fields`

* TODO We added the Extension Manager and extension-loader boot-time to the new "HTML boot-time" indicator of The SEO Framework v4.0.
* TODO change occurrences of tsfem-button-green to tsfem-button-primary-bright?

* TODO See why there's a serialization error on `[message] => Notice (8): unserialize(): Error at offset 66 of 1873 bytes in [/public/wp-content/plugins/the-seo-framework-extension-manager/inc/traits/extension/post-meta.trait.php, line 61]`
* TODO Fixed: Post-Revision metadata can now be processed, just like in TSF v4.0+. Is ^this^ related?

* TODO Regression: The top bar now bounces when more than 2 notices are showing, and one gets replaced, while being at `scrollTop=0`.
	* The fix: Lock the height when a notification gets replaced... this may be tricky with mixed-height notiications.

**Updated extensions:**

* TODO Articles
	* Now requires TSF v4.0.2 or later.
* TODO Focus
	* 1. To improve support for the admin new interface, we added a replica of the actionable buttons in the header.
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
