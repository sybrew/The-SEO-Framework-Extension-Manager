=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: the seo framework, extensions, api, monitor, modules, title
Requires at least: 4.4.0
Tested up to: 4.7.0
Stable tag: 0.9.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add more powerful SEO features to The SEO Framework right from your WordPress Dashboard. No sign-up required.

== Description ==

This plugin adds an extra dashboard menu item, in which you can activate the latest free and premium extensions.

**Note: This plugin requires [The SEO Framework](https://wordpress.org/plugins/autodescription/) to be active.**
**This plugin allows for both free and premium extensions to be activated.**

= About Premium =
A premium subscription will allow you to activate all premium extensions for one single subscription price.
These premium extensions can communicate with The SEO Framework's API server to provide extra functionality. This added functionality is optional and its usage differs per extension.
As long as the subscription is active, you're allowed to use all premium extensions. When the subscription expires or is deactivated, the premium extensions will be deactivated. TODO make this true.

> <strong>The premium software is Open Source:</strong><br>
> This plugin and all extensions within are open source. This means they can be easily altered and shared.<br>
> If you've acquired extensions for The SEO Framework from outside your WordPress Dashboard or WordPress.org, they could contain malware.
>
> This also accounts for any other premium software acquired for free. Please, be careful.

= Requirements: =
* For security reasons, this plugin requires **PHP 5.5 or later**, or it will deactivate itself.
* This plugin requires **The SEO Framework 2.7.0 or later** to be active, or it won't do anything at all.
* This plugin currently does not fully support MultiSite networks. This is planned, stay tuned!

= Privacy =
* This plugin can send API requests to "https://premium.theseoframework.com/". Read our [privacy policy](https://premium.theseoframework.com/privacy/).

== Installation ==

1. Install [The SEO Framework](https://wordpress.org/plugins/autodescription/) either via the plugin installer, or by uploading the files to your server.
1. Follow [those installation instructions](https://wordpress.org/plugins/autodescription/installation/).
1. Install The SEO Framework extension manager either via the plugin installer, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. Follow the activation steps provided on your website.
1. You're good to go! Enjoy!

== Screenshots ==

TODO

== Frequently Asked Questions ==

TODO check again.

= What is this? =
The extension manager for The SEO Framework allows more powerful and advanced SEO techniques.

= What is The SEO Framework? =
A free SEO plugin, for everyone! Download it [from WordPress.org](https://wordpress.org/plugins/autodescription/)

= Do I need to activate all extensions? =
No. The extensions are tailored for very specific types of websites. Incorrect usage of certain extensions could even harm your website's SERP rankings.
Each extension includes carefully crafted documentation and provides an indication whether it's suitable for your website.

= Do I require an account? =
Not at all! This extension manager provides up to date free extensions.

= Are there advertisements? =
Nope. This plugin only shows which extensions are available on the activation page. It will show both free and premium ones.
The activation page will lead you to the site where you can purchase a license, but only if you choose to do so.

= Which users can activate extensions? =
Only users who can install, update or activate plugins are allowed to interact with this plugin.
On multisite, this behavior is restricted to the activation of plugins only.

= Where are the plugins acquired from? =
No PHP files are downloaded. Both free and premium extensions are provided through WordPress.org.

= Which extensions are translated through WordPress.org? =
Only extensions that are free will have translations readily available.
Premium extensions will have no translations available as of yet, unless they're compatible with the free extension translations.

= Does the Extension Manager need to stay activated in order to run the plugins? =
Yes. The Extension Manager supplies its own plugin activation management system apart from WordPress core.

= When my subscription expires, what happens? =
When your premium subscription expires, the premium extensions will automatically deactivate.
A margin of error is allowed, so you don't have to worry for if the activation server is down.

= Are there WordPress MultiSite network SEO plugins? =
They're planned!

= I want to modify an extension, is this allowed? =
Of course! However, please note that the plugin checks for file changes and compares a hash against the activation server for premium extensions.
This check happens on a regular basis for any active plugin. This can't be done otherwise because this plugin has to adhere to the [plugin guidelines (especially #8)](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/).

= If I'm using a MultiSite network, do I need to activate the plugins site by site? TODO make this happen: =
You're able to choose whether you activate this plugin network wide, or per site.

In network mode, you're able to allow or disallow extensions from within the Network Admin.
In single side mode, each site takes full control of the extensions.

Please note that this plugin requires a network license in order to get any premium extension in network mode. In single site mode a unique single license is required per site.

Only admin users of those sites can activate the plugins, and only when the Plugins menu is active, see [filters](https://wordpress.org/plugins/the-seo-framework-extension-manager/other_notes/) for expanded options.

= What happens when I deactivate my account? =
All plugin settings will be deleted. Each individual extension handles its own options (if any); this means those settings won't be lost upon re-activation of the extension at a later time.

TODO make this happen:
This plugin can be network activated while The SEO Framework base plugin is activated site by site without issues.

= Does my website support this plugin? =
If it doesn't, it will tell you why. Otherwise, you're good to go! All known issues are correctly labeled with an identification number.
If you were to get an activation error, either open a support ticket [here](https://wordpress.org/support/plugin/the-seo-framework-extension-manager) or contact your host and ask them to upgrade PHP to a stable version.

= The layout of the extensions page just doesn't look right, why? =
Because the extensions page has been completely written in [state-of-the-art experimental CSS](https://drafts.csswg.org/css-flexbox/), it's possible not all browsers act alike.
Although everything should always fit perfectly; if you do find any issue, please state your browser and operating system and tell me where the issue resides. Thanks!

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

= Reluctance towards plugin modifications and backwards compatibility =
This plugin should be compatible with any other plugin or theme (unless they cause PHP errors on their own).
You are allowed to edit this plugin and use filters for this plugin (as per GPLv3), but any external or internal modification can stop working on any update without prior notice.
This is to enhance the plugin security, stability and overall performance. Please note that most functions, classes, methods and files are shielded against direct calls.
No backwards compatibility will be programmed within this plugin, unless required for WordPress Core or PHP.

= General Filter Reference =

TODO

= Network Filter Reference =

TODO
