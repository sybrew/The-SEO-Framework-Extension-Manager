=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.5
Tested up to: 6.1
Requires PHP: 7.3.0
Requires TSF: 4.2.0
Stable tag: 2.6.1
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

TODO Monitor: robots.txt on subdirectories should not be checked, and a warning should be outputted?

TODO transport: "These are old titles that have been imported."
	- Create Yoast 3.x titles, upgrade to Yoast xx.whatever and create more titles. Import everything.
	- Inspect the old data of Yoast, compare to new.
	- https://wordpress.org/support/topic/migrates-seo-metadata/

TODO get_my_account_link() cannot work in new instances..?

TODO https://www.php.net/manual/en/class.sensitiveparameter.php
TODO GA4 support (quickly now)
	-> Users like this one: https://wordpress.org/plugins/duracelltomi-google-tag-manager/
TODO set API to POST? -> Make a new revision where we handle this.
	-> Next major update, probably.
TODO use $_SERVER file location rather than __FILE__ for instance check (i.e., v2.1 check)
	-> Later, defunct this if issue reports keep coming.
	https://stackoverflow.com/questions/13771021/php-dir-or-file-symlinked
TODO updated pot file.

TODO notify https://github.com/sybrew/The-SEO-Framework-Extension-Manager/issues/74 of workaround.

TODO add to "There is no usable content, so no description could be generated.":
	"Search engines will try to generate a description from any content it can find on this page."
	Also make "Usable content" more clear: Usable content for generation...?

TODO de-abstract get_subscription_status, make data public.
TODO Now requires TSF v4.2.8!
TODO align if-statements a la TSF:
if ( $this->get_option( '_activated' )
&& ( $results['d

TODO set suggested size at create_image_field() -- if not found, set 0?

TODO user can get stuck in this function when API mismatch:
 - protected function handle_premium_disconnection( $args, $re
 - Though, during the "status" check, the user will be prompted for deactivation, no?

TODO Rank Math's Index -> Force index?
	-> How does their plugin behave on global settings change?

TODO if Transport fails to transport, keep a store for next request, and initiate retrieving that store once the request finishes (transient with 2 minute timeout or something).

TODO add "jobTitle" support for Articles.

TODO create a new key for update-generation -- Edge probably leaked this.

TODO

**Release date:**

* June TODOth, 2023

**Detailed log:**

* Disconnecting a Premium account when there's an issue with our servers is now possible.
* Installing Extension Manager for the first time will now use option instance verification v3.0, which relies on the site URL instead of plugin folder location (2.0) or salt keys (1.0).
* Constant `TSF_EXTENSION_MANAGER_INSTANCE_VERSION` is now available. This helps users with complex server setups maintain a stable and secure connection by changing to a salt key (1.0), folder location (2.0), or site URL (3.0, the new default).
*

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
