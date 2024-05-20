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

TODO Important!!! delete_site_transient( 'update_plugins' );
	-- Working with a fresh copy of the transient makes our plugin crash, no?
		- See email from "delivery".

TODO load an unmodified $wp_version for extension compatibility tests -- we got 5 reports already.

TODO fix bug in Focus for new posts without an ID (bug in Gutenberg, also affected TSF's image uploader...)
	-> marked with var_dump()
TODO do not disconnect sites when the subscription expires, but downgrade them to free instead. Increase retry time incrementally by up to one a week?
	do the var_dump()

TODO figure out why iThemes Sync (Solid Central) is loading the admin after is_admin() was false (and then true).
	-> Report it to Brent and them. This is not for us to fix, but iThemes.

TODO get_article_published_date and get_article_modified_date show gmdate('c') instead of the preference in TSF.

TODO for Articles, only display the Article post state type if it's non-default?
	-> We can't, because we have the "Disabled" type, i.e., "no articles" -- this is conveyed by not showing the Article type.

TODO for Focus, add extra support for WC's "short description" (aka excerpt)?

TODO for Monitor, when a site isn't registered with us, tell the user about it.
	-> To test, register, then delete from (or modify) DB.
	-> Currently, we send the generic 'failure' response.
		-> We could say the site isn't registered... but wouldn't this open the gate for spammers?
			-> Then again, the service is proxied and requires various secrets to align before responding.

TODO for Focus, when no inflections/synonyms are found, fill in the word itself?!
	-> Try "tyre" -> Noun: tyre.

TODO Make "site keys not valid" warning site-wide, instead of only Extension Manager page.

TODO: For Honeypot Timer, add a JS script that updates the timer when the page loads.
	-> If no JS, just keep the server-timer.
	-> If emptied -> fail!
	-> Use the same method PHP uses.

= 2.6.4 =

**Feature highlights:**

* Honeypot now tests against fast commenters that accelerate time.

**Detailed log:**

* **Changed:**
	* The update API is now engaged unconditionally. We found that users still accidentally downgraded to the WordPress.org version, this ought to prevent that.
* **Fixed:**
	* Resolved an issue where the plugin updater could cause a fatal error.

**Updated extensions:**

* [Articles at version 2.3.1](https://theseoframework.com/extensions/articles/#changelog)
* [Honeypot at version 2.1.0](https://theseoframework.com/extensions/honeypot/#changelog)

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
