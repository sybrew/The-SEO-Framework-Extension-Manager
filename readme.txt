=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.5
Tested up to: 6.1
Requires PHP: 7.3.0
Requires TSF: 4.2.0
Stable tag: 2.5.3
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

TODO add RTL support for _print_tsf_nag_installer_styles (regression)
TODO align AMP's get_social_metadata() with TSF's output handling? This might incur deprecation of some filters.
TODO `tsfem_error_notice_option` -> `tsfem_error_notices`
TODO `tsf-extension-manager-settings` -> `tsfem_settings`?

TODO Convert tsfem-ui/tsfem-inpost notice handler into separate class?
	-> The one from tsfem-inpost is more advanced, allowing separate notice wraps.

TODO add save all button. -> Basically run through all forms and save them, and send message from "saved for one extension" to "saved for X (slug)."
	-> get_save_all_button()

TODO make $notice_count visible on SEO menu, not only subitem.

TODO add "reset instance" button -- also visible when using constant activation
	* Only display when SOMETHING is marked invalid.
	* This will reset the current installation API and local instances and will attempt to reconnect your account.
	* "Otherwise, disconnect..." -> rephrase.
		-> Transfer the license!

* **Added:** When you move WordPress between domains, you can now easily switch the registered domain with us without needing to reactivate extensions. A red button is presented under "Account and Actions" when a transfer is detected.
	* This is especially handy for multisite domain transfers.
	* TODO This won't work for switching between development, staging, and production environments, unless the file paths are identical.
* **Changed:** Reduced the likelihood of random disconnects caused by iThemes Security's nonsensical option to flush security keys every two weeks.
	* New sites no longer rely on these proper authentication keys for hashing, but now use a combination of environmental variables, like the database version and the installation location. Change any of these, and your site could disconnect from our services.
	* Only with a new installation, downgrading to an earlier version can cause the new instance to be invalidated. If you wish to downgrade, disconnect your account via Extension Manager and reconnect to set up a legacy instance key.
	* If you disconnect, your site will be seen as a new installation, and will switch to this new system.
	* Like before, you can reconnect after disconnecting without losing data.
	* This check performs entirely locally.
* **Improved:** Increased clarity about how the API connection is validated.
* **Improved:** Notification animations are faster now, no longer cause minor text movement, and no longer cause large screen movement due to race conditions.
* **Improved:** Refactored option handling for improved performance.
* **Updated:** Now uses our v2.2 licensing API, primarily for key naming convenience.
* **Other:** The "SEO Trends and Updates" feed now uses the relocated feed link, and sorts through it more quickly.
* **Other:** Modernized code.
* **Other:** New translations are available. TODO update POT

**Updated extensions:**

* [Focus at version 1.5.3](https://theseoframework.com/extensions/focus/#changelog)
* [Honeypot at version 2.0.1](https://theseoframework.com/extensions/honeypot/#changelog)
* [AMP at version 1.2.1](https://theseoframework.com/extensions/amp/#changelog)
* [Transport at version 1.1.0](https://theseoframework.com/extensions/trasnport/#changelog)
* [Monitor at version 1.2.9](https://theseoframework.com/extensions/monitor/#changelog)
* [Local at version 1.3.0](https://theseoframework.com/extensions/local/#changelog)
s
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
