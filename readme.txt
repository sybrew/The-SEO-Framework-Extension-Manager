=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 7.4.0
Requires TSF: 4.2.8
Stable tag: 2.6.3
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

TODO Make "No valid license key was supplied" warning site-wide, instead of only Extension Manager page.

TODO: For Honeypot Timer, add a JS script that updates the timer when the page loads.
	-> If no JS, just keep the server-timer.
	-> If emptied -> fail!
	-> Use the same method PHP uses.

TODO figure out why TSFEM crashes on post-save (keyword email: Andy).

TODO when visiting the Extension Manager page, the notice notifyer isn't counted down
	-> Can this even be fixed? Maybe only when counting down to 0.
		-> Though, this issue could actually help users understand that the notifyer badge is for notifications...

TODO update pot file.

TODO test if TSF's installer still works with WP 6.5
	-> Yes, but slowly for some reason (might be a fluke, tested 1x).
	-> Also, the installer buttons don't "sync" when installed via the tickbox, but this is a non-issue.
		-> Solve in next update?
			-> Does this only affect the plugin activation page?
		-> Remove the "install" button and only show "view details" instead? This will be a bit more intuitive and familiar for the user.

TODO make a separate loader specifically when TSF is inactive (load installer + updater).
	-> And only when TSF is active and at the right version, load the main plugin.
		-> Here, we can freely assume TSF is available.
	-> We already do this, but it's not as clear as can be.

TODO use TSF's memo API?

TODO deJquerify as much as possible.

TODO require tsf and tsfem dependencies for all scripts
	-> This eases debugging, e.g. via tsf.l10n.states.debug.

TODO remove uptime monitoring toggles, it never would've worked out anyway.

TODO myMart and myMart Pharmacy isn't clear, they should be separated.
	-> Remove the brackets in `{store name} {department name}`,
		-> and make it `store name` OR `store name department name`

TODO trailing commas

TODO we can use is_wp_version_compatible() and is_php_version_compatible() now.

= 2.7.0 =

* June TODOth, 2024

**Feature highlights:**

* Honeypot now tests against fast commenters that accelerate time virtually.
* Focus can now make API calls again when creating a new post using the latest Block Editor.

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

**Updated extensions:**

* [Articles at version 2.3.1](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.6.0](https://theseoframework.com/extensions/focus/#changelog)
	-> TODO We need to add a "select all" button (next to "Choose inflections" and "Choose synonyms")
	-> add a dicitionary service badge.
	-> Make it 2.0.0 if we add the select all button, otherwise 1.6
* [Honeypot at version 2.1.0](https://theseoframework.com/extensions/honeypot/#changelog)
* [Monitor at version 1.2.12](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.7.0 changelog](https://tsf.fyi/p/ TODO).

= 2.6.3 =

* November 2nd, 2023

**Important release notes:**

* Extension Manager now requires **PHP 7.4.0 or higher** and **WordPress 5.9 or higher**.
* We recommend installing this update before upgrading to The SEO Framework v5.0.0 to ensure a smooth upgrade.

**Feature highlights:**

* Added compatibility with the upcoming TSF v5.0.0 update.
* Added WooCommerce Marketplace UI support. You can now get a [Premium Subscription via WooCommerce Marketplace](https://woo.com/products/the-seo-framework-premium/).

**Updated extensions:**

* [Articles at version 2.3.0](https://theseoframework.com/extensions/articles/#changelog)
* [Cord at version 1.1.1](https://theseoframework.com/extensions/cord/#changelog)
* [Monitor at version 1.2.11](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.6.3 changelog](https://tsf.fyi/p/4123).

= 2.6.2 =

**Release date:**

* June 22nd, 2023

**Feature highlights:**

* Google sunsets Universal Analytics 3 next month. Cord now supports Google Analytics 4. First, you should [migrate](https://support.google.com/analytics/answer/10110290); then, you can get your [Measurement ID](https://support.google.com/analytics/answer/12270356).
* The SEO Framework 4.2.8 is now required, from 4.2.0 or later.

**Updated extensions:**

* [Cord at version 1.1.0](https://theseoframework.com/extensions/cord/#changelog)
* [Transport at version 1.1.1](https://theseoframework.com/extensions/transport/#changelog)
* [Monitor at version 1.2.10](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.6.2 changelog](https://tsf.fyi/p/4090).

= 2.6.1 =

**Release date:**

* February 7th, 2023

**Feature highlights:**

* Transport now supports migration from SEOPress.
* Local now supports price range indication and scheduled opening hours.
* Reduced the likelihood of random disconnects for iThemes Security users.
* Improved overall performance by optimizing option handling and modernizing browser scripts.

**Updated extensions:**

* [Focus at version 1.5.3](https://theseoframework.com/extensions/focus/#changelog)
* [Honeypot at version 2.0.1](https://theseoframework.com/extensions/honeypot/#changelog)
* [AMP at version 1.2.1](https://theseoframework.com/extensions/amp/#changelog)
* [Transport at version 1.1.0](https://theseoframework.com/extensions/trasnport/#changelog)
* [Monitor at version 1.2.9](https://theseoframework.com/extensions/monitor/#changelog)
* [Local at version 1.3.0](https://theseoframework.com/extensions/local/#changelog)

**Detailed log:**

View the [detailed v2.6.1 changelog](https://tsf.fyi/p/4055).

= 2.6.0 - Mobile SEO =

**Release date:**

* October 4th, 2022

**Feature highlights:**

In this update, we added a new extension: [Transport](https://tsf.fyi/e/transport)! It allows you to migrate data from other SEO plugins to TSF easily. We also increased the server and browser requirements, which allowed us to modernize the codebase.

**Important release notes:**

* Henceforth, Extension Manager requires **The SEO Framework v4.2.0 or higher** and **PHP 7.3.0 or higher**.

**Updated extensions:**

* [Focus at version 1.5.2](https://theseoframework.com/extensions/focus/#changelog)
* [Articles at version 2.2.1](https://theseoframework.com/extensions/articles/#changelog)
* [Transport at version 1.0.0](https://theseoframework.com/extensions/transport/#changelog)
* [Local at version 1.2.0](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.8](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.6.0 changelog](https://theseoframework.com/?p=3968).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.5.3 =

This plugin now requires WordPress v5.5 or higher.

= 2.5.0 =

This plugin now requires WordPress v5.1 and The SEO Framework v4.1.2 or higher.
