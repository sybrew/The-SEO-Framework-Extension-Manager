=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 4.9.0
Tested up to: 5.3
Requires PHP: 5.6.5
Requires TSF: 4.0.0
Stable tag: 2.2.1
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

= 2.3.0 - TODO SEO =

**Release date:**

* TODO TBA

**Feature highlights:**

* TODO TBA

**Detailed log:**

* We added a new extension! Called [Cord](https://theseoframework.com/extensions/cord/).
	* TODO explain what it does. TODO make readme, logo, etc.

TODO remove extraneous favicon.ico check from TSFEM. Its use is being deprecated.

* **Added:**
	* A brand new extension, called Cord!
	* The 'info' notice type. These are highlighted via a blue color, with a question mark at the side.
* **Improved:**
	* The extension settings fields now leave a little more space for the inputs, depending on the description size.
	* The extension settings interface is much snappier, thanks to refactorization of old sluggish code.
	* The extension settings collapsible items are now validated on-load, instead of when expanding the items.
		* This has been done for improved accessibility. The trade off is that this will negatively affect browser-performance when loading in hundreds of Local departments.
	* Various server-sided adjustments have been made which improve performance.
	* TODO maybe: Overhaul of script loader, it now uses TSF 4.0 style script-loading, drastically improving performance.
		* tsfemForm:
			* tsfemForm
			* tsfemFormCollapse
			* tsfemFormValidator
			* tsfemFormSave (submit, save, ajax?)
			* tsfemFormGeo
		* tsfem:
			* tsfem
			* tsfemAccount
			* tsfemNotice
			* tsfemDialog
			* tsfemAys (currently not invoked anywhere)
			* tsfemFader
		* tsfemInpost:
			* tsfemInpost
			* tsfemAccount (see tsfem)
			* tsfemFader (see tsfem)
			* tsfemNotice (see tsfem)
			* tsfemSanitize
			* tsfemServiceWorker
	* We found a workaround with the non-Webkit/Blink rendering of the shrinking gridboxes. Enjoy a neat interface on Edge and Firefox now, too!
* **Changed:**
	* The extensions are now autoloaded in the order they're presented, instead of the order in which they're activated.
* **Other:**
	* We expanded the form-generator, where it now accepts various plain and dropdown fields.
	* The form-generator no longer parses the dropdown titles on the server. It now lets your browser take care of that.
	* All WordPress Filter/Action/Dependency API callbacks to static methods are no longer concatenated, but are instead put in an array.
* **Fixed:**
	* The available PHP memory is now asserted correctly during upgrades. Before, upgrading went to the absolute limit before deferring, resulting in memory exhaustion.
	* A browser memory leak and CPU job heaping after repeatedly adding extension settings form iterations.

= 2.2.1 =

**Release date:**

* November 21st, 2019

**Feature highlights:**

* In this update we work around a bug in WordPress 5.3 for Articles, where canonical URLs may point to a non-existing page.

**Updated extensions:**

* [Articles at version 2.0.1](https://theseoframework.com/extensions/articles/#changelog)

**Detailed log:**

View the [detailed v2.2.1 changelog](https://theseoframework.com/?p=3387).

= 2.2.0 - Adorned SEO =

**Release date:**

* November 5th, 2019

**Feature highlights:**

* A new extension settings overview has been added. The Articles extension is the first to support this.
* The interface is now much cleaner, swifter, and far more accessible. It is also in line with the upcoming WordPress 5.3 update.
* The plugin now requires:
	* WP 4.9 or higher.
	* TSF 4.0 or higher.
	* PHP 5.6.5 or higher.

**A few notes on browser support:**

The new interface now relies on bleeding-edge technology, which is an auto-fit grid with collapse and span-growth support. This technology isn't new, but until recently, no browser supported it well. To make sure everything works as intended:

* Chrome v77 (September 10th, 2019) equivalent or higher are now required.
* Safari v13 (September 19th, 2019) or higher is now required.

These browsers support the interface well enough, but may look slightly different from what's intended, because they don't support shrinking elements in grid yet:

* Firefox v70 (October 22nd, 2019) equivalent or higher are now required.
* Edge v44.18 (May 21st, 2019) or higher is now required.

_All hope for Internet Explorer is lost._

If the layout looks blatantly wrong on Chrome or Firefox (that is, items overlapping), go to "Menu -> Help -> About Firefox/Chrome," and an update should be available.

Microsoft notoriously forces updates on us, so you should be fine with Edge. And Apple made their latest updates so attractive; we doubt you've skipped on those.

**Updated extensions:**

* [Articles at version 2.0.0](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.3.1](https://theseoframework.com/extensions/focus/#changelog)
* [Monitor at version 1.2.3-β-5](https://theseoframework.com/extensions/monitor/#changelog)
* [Local at version 1.1.5](https://theseoframework.com/extensions/local/#changelog)
* [Title Fix at version 1.2.1](https://theseoframework.com/extensions/title-fix/#changelog)

**Detailed log:**

View the [detailed v2.2.0 changelog](https://theseoframework.com/?p=3355).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.2.0 =

This plugin now requires WordPress v4.9, PHP v5.6.5, and The SEO Framework v4.0 or higher.

= 2.1.0 =

This plugin now requires WordPress 4.8 or higher.
