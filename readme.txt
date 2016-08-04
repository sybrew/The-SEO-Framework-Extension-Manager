=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: the seo framework, extensions, api
Requires at least: 4.4.0
Tested up to: 4.6.0
Stable tag: 0.9.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add more powerful SEO features to The SEO Framework right from your WordPress Dashboard. No sign-up required.

== Description ==

= 87.5% lifetime premium discount if you order a license in 2016! =

This plugin adds an extra dashboard menu item, in which you can download the latest free and premium extensions.

**Note: This plugin requires [The SEO Framework](https://wordpress.org/plugins/autodescription/) to be active.**
**This plugin allows for both free and premium extensions to be acquired.**

= About Premium =
A premium subscription will allow you to download, update and activate all premium extensions for one single subscription price.
More extensions are added over time and are fetched securely from our servers.
As long as the subscription is active, you're allowed to use all old and new extensions.

> <strong>Read carefully:</strong><br>
> This plugin and all acquired extensions are open source. This means they can be easily altered and shared.<br>
> If you've acquired extensions for The SEO Framework from outside your WordPress Dashboard or WordPress.org, they could contain malware.
>
> This also accounts for any other premium software acquired for free. Please, be careful.

= Requirements: =
* This plugin requires **PHP 5.3 or later**, or it will deactivate itself.
* This plugin requires **The SEO Framework 2.7.0 or later** to be active, or it will do nothing at all :).

= Privacy =
* This plugin sends API requests to "https://premium.theseoframework.com/". Read our [privacy policy](https://premium.theseoframework.com/privacy/).

== Installation ==

1. Install [The SEO Framework](https://wordpress.org/plugins/autodescription/) either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Follow [those installation instructions](https://wordpress.org/plugins/autodescription/installation/).
1. Install The SEO Framework extension manager via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. Follow the activation steps.
1. You're good to go!

== Screenshots ==

TODO

== Frequently Asked Questions ==

TODO check again.

= What is this? =
This free extension is made because many themes are rendering the title wrong. This causes issues, which this plugin resolves.

= What is The SEO Framework? =
A free SEO plugin, for everyone! Download it [from WordPress.org](https://wordpress.org/plugins/autodescription/)

= Do I require an account? =
Not at all! This extension manager provides up to date free extensions.

= Are there advertisements? =
Not thoughout the dashboard. This plugin only shows which extensions are available on the activation page. It will show both free and premium ones.
The activation page will lead you through the site where you can purchase a license, but only if you choose to do so.

= Which users can activate extensions? =
Only users who can install and activate plugins are allowed to view and notice the presence of this plugin.

= Where are the plugins acquired from? =
The plugins are provided through a secure gateway to any authorized plugin user.

= Does the Extension Manager need to stay activated in order to run the plugins? =
Yes. The Extension Manager supplies its own plugin activation management system apart from WordPress core.

= Are there WordPress MultiSite network SEO plugins? =
They're planned!

= If I'm using a MultiSite network, do I need to activate the plugins site by site? =
You're able to choose. In network mode, only the super admin can download extensions. Only the downloaded extensions will be shown within the sub-site activation page.
Only admin users of those sites can activate the plugins, and only when the Plugins menu is active, see [filters](https://wordpress.org/plugins/the-seo-framework-extension-manager/other_notes/) for expanded options.
Please note that this plugin requires a network license in order to get any premium extension in network mode. In single site mode a single license is required.

TODO:
This plugin can be network activated while The SEO Framework base plugin is activated site by site without issues.

= Does my website support this plugin? =
If it doesn't, it will tell you why. Otherwise, you're good to go!
If you were to get an activation error, either open a support ticket [here](https://wordpress.org/support/plugin/the-seo-framework-extension-manager) or contact your host and ask them to upgrade PHP to a stable version.

== Changelog ==

= 1.0.0 - Amplified SEO =

* Initial public release.

= 0.9.0 - Developed SEO =

* Unregistered initial beta release.

== Upgrade Notice ==

= 1.0.0 =

* What are you still doing in beta?

== Other Notes ==

= Are you a developer? =
And do you wish to add your own extension to the extension manager? Please contact me on Slack about your idea.
A full code review will take place prior to releasing it. The code has to pass at least the WordPress.org plugin standards and all code must be licensed under GPLv2 or later.
Feedback and points for improvement will be always given. No monetized extensions are being accepted as of yet.

= For developers: Security =
Because this plugin handles multiple input fields, multiple nonce fields had to be created in order to prevent XSS from otherwise unauthorized users.
In order to minimize overhead, each nonce type has been supplied an action. This way, the validation all falls under one function.
Many more security techniques, some even newly developed specifically, have been implemented into this plugin.
If you have any questions, before blindly implementing or circumvate security, feel free to contact me (the plugin author) on Slack.

= General Filter Reference =

TODO

= Network Filter Reference =

TODO
