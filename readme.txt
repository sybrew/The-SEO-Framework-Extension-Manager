=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, keyword, schema, honeypot
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4.0
Requires TSF: 4.2.8
Stable tag: 2.7.0
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

= 2.7.1 =

**Release date:**

* November 20th, 2024

**Feature highlights:**

* This minor update squashes two ugly bugs that were introduced in the previous release.

**Updated extensions:**

* [Articles at version 2.3.2](https://theseoframework.com/extensions/articles/#changelog)
* [Local at version 1.3.1](https://theseoframework.com/extensions/local/#changelog)

**Detailed log:**

* **Fixed:**
	* Resolved an issue where Extension Settings couldn't save for Articles or Cord when Local was active.
	* Resolved an issue where Local wasn't marked as version 1.3.0 in the Extension Manager.

= 2.7.0 - Sane SEO =

**Release date:**

* November 19th, 2024

**Feature highlights:**

* Honeypot now tests against fast commenters that accelerate time virtually.
* Focus can now make API calls again when creating a new post using the latest Block Editor.
* We spent the past year optimizing this plugin, resolving annoyances and making the plugin almost twice as fast.

**Detailed log:**

* **The plugin database version is now at `2700`.**
* **Note:** Downgrading to an earlier version of this plugin might cause all extensions to become deactivated.
* **Changed:**
	* We decoupled the active extensions option from the API activation options. This means that extensions will no longer be deactivated after a full disconnect (e.g., after site migration).
	* The update API now engages even if WordPress is not checking this specific plugin. We found that users still accidentally downgraded to the WordPress.org version because of Core issues [44118](https://core.trac.wordpress.org/ticket/44118) and [61055](https://core.trac.wordpress.org/ticket/61055).
	* When your subscription expires or is pending, you no longer need to reconnect manually, but there's now a grace period of 7 days for Extension Manager to reconnect automatically.
		* Once this grace period is passed, your account will need to be upgraded manually again.
		* Automatic reconnecting is tried every 2 minutes.
		* You are also offered to manually switch the license key during this grace period.
	* We've updated the color scheme slightly to be more aligned with modern WordPress and The SEO Framework v5.1.
* **Improved:**
	* We changed the WordPress version compatibility test by using an unmodified variable, instead of one plugins can alter.
	* We modernized critical JavaScript code, slightly improving browser interaction performance.
	* We modernized some CSS code, slightly reducing the plugin file sizes.
	* We modernized some PHP code, slightly improving server response times.
	* We changed the load sequence of the plugin to remove action overhead. It now loads at `init`, instead of `plugins_loaded`.
		* Simultaneously, it resolves a deprecation notice with WordPress 6.7, which may be [reverted in 6.7.1](https://core.trac.wordpress.org/ticket/62462).
	* Added PHP 8.4 OPcache optimizations.
* **Removed:**
	* "SEO Trends and Updates" are no longer available. We had different plans for what would've been displayed (i.e., our own news feed), but it devolved into a marketing channel for Google unintentionally.
		* The transient data for this (`tsfem_latest_seo_feed`) will be cleaned up automatically by WordPress.
		* The option `_enable_feed` will disappear when the next API status request is made (or another change is made to the account).
* **Fixed:**
	* Resolved an issue where the plugin updater could cause a fatal error.
	* Resolved an issue where extension post-metadata could be double-unserialized by another plugin or store incoherent data on extraction failure.
	* Resolved an issue where API activation via a constant (`TSF_EXTENSION_MANAGER_API_INFORMATION`) could cause a site to get stuck in instance verification failure. Now, the site disconnects and reconnects automatically.
		* This should happen immediately, but there is a timeout of 3 minutes when this keeps recurring. Then, those who can manage Extension Manager can manually enter "Free" mode.
		* The site will upgrade automatically after 3 minutes, regardless of whether it's was set manually into "Free" mode.
* **Other:**
	* Another year has turned. So, we updated all files' copyright coverage.
	* New translations are available.

**Updated extensions:**

* [AMP at version 1.3.0](https://theseoframework.com/extensions/amp/#changelog)
* [Articles at version 2.3.1](https://theseoframework.com/extensions/articles/#changelog)
* [Honeypot at version 2.1.0](https://theseoframework.com/extensions/honeypot/#changelog)
* [Focus at version 1.6.0](https://theseoframework.com/extensions/focus/#changelog)
* [Monitor at version 1.2.12](https://theseoframework.com/extensions/monitor/#changelog)
* [Title Fix at version 1.3.0](https://theseoframework.com/extensions/title-fix/#changelog)

**Detailed log:**

View the [detailed v2.7.0 changelog](https://tsf.fyi/p/4306/).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.5.3 =

This plugin now requires WordPress v5.5 or higher.

= 2.5.0 =

This plugin now requires WordPress v5.1 and The SEO Framework v4.1.2 or higher.
