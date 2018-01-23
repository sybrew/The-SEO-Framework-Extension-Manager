=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: the seo framework, extensions, monitor, modules, title, seo, schema, local, articles, honeypot, amp
Requires at least: 4.6.0
Tested up to: 4.9.2
Requires PHP: 5.5
Stable tag: 1.4.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add more powerful SEO features to The SEO Framework right from your WordPress dashboard. No sign-up required.

== Description ==

**Advanced and powerful SEO.**
**Delivered through extension for [The SEO Framework](https://wordpress.org/plugins/autodescription/).**

This plugin provides an advanced WordPress administrative dashboard page where you can activate the latest free and premium extensions.

= Included Extensions =

**The following extensions are included:**

* **[Local](https://theseoframework.com/extensions/local/):** The Local extension lets you set up important local business information for search engines to consume.
* **[AMP](https://theseoframework.com/extensions/amp/):** The AMP extension binds The SEO Framework to the AMP plugin for AMP supported articles and pages.
* **[Articles](https://theseoframework.com/extensions/articles/):** The Articles extension enhances your published posts by automatically adding both AMP and non-AMP Structured Data.
* **[Monitor](https://theseoframework.com/extensions/monitor/):** The Monitor extension keeps track of your website’s SEO, optimization, uptime and statistics.
* **[Incognito](https://theseoframework.com/extensions/incognito/):** The Incognito extension removes all front-end branding from The SEO Framework.
* **[Title Fix](https://theseoframework.com/extensions/title-fix/):** The Title Fix extension makes sure your title output is as configured. Even if your theme is doing it wrong.
* **[Honeypot](https://theseoframework.com/extensions/honeypot/):** The Honeypot extension catches comment spammers in four lightweight yet powerful ways.
* **[Origin](https://theseoframework.com/extensions/origin/):** The Origin extension redirects attachment-page visitors back to the parent post.

= Upcoming Extensions =

**These extensions are being worked on:**

* **Transporter:** It allows you to export and import your SEO settings from site to site.

= How it works =

This plugin offers a lightweight dashboard wherein you can activate and deactivate extensions.
Only the activated extensions are loaded. Other extensions are dormant and the files of those aren't even touched.

Some of the extensions are completely free to use, others are premium and are connected to our servers.

= About Premium =

A premium subscription will allow you to activate all premium extensions for one single subscription price.

The premium extensions communicate with The SEO Framework's API server to provide extra functionality. This added functionality is optional and its usage differs per extension.

When the subscription is active, you're allowed to use all premium extensions. If the subscription is expired or deactivated, the premium extensions will no longer be accessible.

== Installation ==

= This plugin requires: =

* PHP 5.5.21, 5.6.5, or later. For security and structural reasons.
* WordPress 4.4 or later. For improved AJAX and meta support.
* [The SEO Framework](https://wordpress.org/plugins/autodescription/) 2.7 or later. Or it will stay dormant.
* Internet Explorer 11 or later for the best admin experience.
* For improved performance, your PHP handler should use a 64 bits architecture. 32 bits is also supported.

= Installation instructions: =

1. Install "The SEO Framework - Extension Manager" either via the WordPress.org plugin directory or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it!

= This plugin will then guide you through an activation process: =

1. Follow the link provided or go to the Extensions submenu of SEO.
2. Choose your subscription type.
3. That's it! Feel free to activate any extension available.

The extensions test themselves upon activation. So, if any extension doesn't work on your site it will let you know why.

= Privacy =

* This plugin can send API requests to "https://premium.theseoframework.com/" and our other sites.
* Read our [privacy policy](https://theseoframework.com/privacy/), it states that we respect your privacy.
* Questions about privacy? Feel free to [contact us](https://theseoframework.com/contact/).

> <strong>The premium software is Open Source:</strong><br>
> This plugin and all extensions within are open source. This means they can be easily altered and shared.<br>
> If you've acquired extensions for The SEO Framework from outside your WordPress Dashboard or WordPress.org, they could contain malware.
>
> This also accounts for any other premium software acquired for free. Please, be careful.

= Are you a developer? =
And do you wish to add your own extension to the extension manager? Please contact me on [Slack](https://wordpress.slack.com/messages/@cybr/) about your idea.
A full code review will take place prior to releasing it. The code must pass at least all the WordPress.org plugin standards and all code must be licensed under GPLv3.
Feedback and points for improvement will be always given. No monetized nor premium extensions are being accepted, API connections aren't allowed either.

= For developers: Security =
Because this plugin handles multiple input fields, multiple nonce fields had to be created to prevent XSS from otherwise unauthorized users.
To minimize overhead, each nonce type has been supplied an action. This way, the validation all falls under one function.
Many more security techniques, some unprecedented in open source, have been implemented into this plugin.
If you have any questions, before blindly implementing or circumventing security, feel free to contact me (the plugin author) on [Slack](https://wordpress.slack.com/messages/@cybr/).

= For developers: Reluctance towards plugin modifications =
This plugin should be compatible with any other plugin or theme, unless they cause PHP errors on their own.
You are allowed to edit this plugin and use hooks on this plugin (as per GPLv3), but any external or internal modification can stop working on any update without prior notice.
This is to enhance the plugin security, stability, and overall performance. Please note that most core functions, classes, methods, and files are shielded against both direct and indirect calls.

== Screenshots ==

1. The activation page. You can choose both premium and free subscriptions.
2. The SEO Extensions overview page, running on a Multisite.

== Frequently Asked Questions ==

= What is this? =
The Extension Manager for The SEO Framework allows you to enable various extensions to enhance your experience and improve your SEO.

= What is The SEO Framework? =
A free SEO plugin, for everyone! Download it [from WordPress.org](https://wordpress.org/plugins/autodescription/).

= What is an extension? =
An extension is like a plugin, and can be activated and deactivated on demand. All available extensions, both free and premium, are included in this plugin package.

= Do I need to activate all extensions? =
No. The extensions are tailored for very specific types of websites. Incorrect usage of certain extensions could even harm your website's SERP rankings.
Each extension will include carefully crafted documentation soon.

= Do I need to register an account? =
Not at all! This extension manager provides up to date free extensions, without requiring an API connection.

= Are there advertisements? =
Nope. This plugin only shows which extensions are available on the activation page. It will show both free and premium ones.
Some buttons, like on the extension activation page, can lead you to the site where you can purchase a license, but only if you choose to do so.

= Does this plugin track my usage? =
Absolutely not! This plugin does not include any user tracking software. We completely rely on your feedback to know what you require.

= What's the release cycle of extensions? =
Every y.X update (1.1, 1.2, 1.3, etc.) will include a new extension. Every y.y.X update (1.0.1, 1.0.2, etc.) fixes bugs and/or adds new functionality to existing extensions.
No X.y updates (2.0, 3.0, etc.) are planned as of now. We plan to release a new extension every (other) month.

= I've received an error code, what now? =
Follow the steps provided next to the error code, if any. If the error keeps coming back, let us know through on the support forums.
Note that Premium Extensions aren't supported within the WordPress.org support forums; you'll have to [contact us directly](https://theseoframework.com/contact/).

= Which users can activate extensions? =
Only users who can manage options can interact with this plugin.

= Where are the extensions acquired from? =
Both free and premium extensions are provided within the plugin package acquired from WordPress.org.

= Does the Extension Manager need to stay activated to run the extensions? =
Yes. The Extension Manager supplies its own extension activation management system apart from WordPress'.
Deactivating the Extension Manager will also deactivate all extensions.

= When my subscription expires, what happens? =
When your premium subscription expires, the premium extensions will automatically deactivate.
A margin of error is allowed, so you don't have to worry for if the activation server is down.

= Does this plugin work on Multisite Networks? =
Absolutely. This plugin can be network activated while The SEO Framework base plugin is activated site-by-site without issues.
Alternatively, this plugin can be activated per site while The SEO Framework is network activated. Any other combination is possible too.

= Are there WordPress Multisite network specific SEO extensions? =
They're planned! However, there's still a lot to be done before this is even possible.

= What happens when I deactivate my account? =
All plugin settings will be deleted, this includes which extensions were enabled.
Each extension handles its own options (if any); those settings won't be lost, not even upon re-activation of the extension afterwards.

= Does my website support this plugin? =
If it doesn't, it will tell you why. Otherwise, you're good to go! All known issues are correctly labeled with an identification number.
If you were to get a plugin activation error, either open a support ticket [here](https://wordpress.org/support/plugin/the-seo-framework-extension-manager) or contact your host and ask them to upgrade PHP to a stable and secure version.

= The layout of the plugin pages just doesn't look right, why? =
This plugin has been tested against many browsers with the help of [BrowserStack](https://www.browserstack.com/), we support the latest and most popular browsers, even Internet Explorer!
However, because the plugin pages have been completely written in [state-of-the-art experimental CSS flexbox](https://www.w3.org/TR/css-flexbox-1/), it's possible not all browsers act alike.
Although everything should always fit perfectly; if you do find any issue, please state your browser and operating system and tell us where the issue resides. Thanks!

== Changelog ==

= 1.5.0 - Impeccable SEO =

* **Release date:**
	/
	* TODO

**New Extensions:**

* **Debugger**: This free extension enables The SEO Framework's debugging interface for admin users.
	/
	* TODO Is this actually possible as extensions are loaded after TSF? I'd like to think so...

**Important note:**

* This plugin now requires WordPress 4.6 or later.

**Plugin Improvements:**

* **Added:** Extensions no longer load when they're deemed incompatible due to WordPress' environmental changes to ensure stability.
* **Added:** Extensions can now add in-post options in predefined tabs (Audit, Structure and Advanced).
* **Added:** The plugin and extensions can now upgrade their database for future improvements when necessary.
* **Added:** The plugin and extensions can now abstractly tell time.
* **Added:** The plugin and extensions can now track memory performance to prevent exhaustion prior executing heavy tasks.
* **Added:** Filter `tsf_extension_manager_can_manage_options`, boolean.
* **Improved:** Users can now reactivate their account after migrating sites or changing security keys without a hassle.
* **Improved:** External links (e.g. to Google and TSF sites) are no longer tracked.
* **Improved:** Suppressed AJAX error messages now display at least the intended error code for support.
* **Improved:** The plugin can now deactivate itself on activation when running PHP 5.2, rather than WordPress doing it for us.
* **Improved:** The plugin's bootstrap has been rewritten to be more efficient, faster, and easier to read.
* **Improved:** The extension option database entries will now be deleted when no indexes are present.
* **Improved:** Various UI elements now fit more neatly on some screens.
* **Improved:** When an extension's class autoloader can't be registered, the extension in question fails safely.
* **Improved:** The new-and-improved tooltip generation of TSF 3.0 has been implemented.
* **Fixed:** When domains mismatch on switching sites, you're now correctly informed again.
* **Fixed:** Some array to object conversions in the schema packer caused invalid input data from Local SEO not to be parsed through conditions correctly, and caused several PHP warnings instead.
	* Because the affected condition checking happens through conditional HTML5 input fields and JavaScript for improved UX, this didn't cause unexpected output because the user couldn't save anyway.
	* No action is required from the user.

* **Updated Extensions:**
	* Articles.
	* Monitor.
	* Title Fix.
/
* TODO see <https://wordpress.org/support/topic/troubles-when-i-migrated-a-development-site/>
* TODO add shortcode for Local SEO addresses.
* TODO init uptime monitoring for Monitor.
* TODO Local SEO now accepts street entries without numbers? <- This is common in the UK, but it will break other things...
* TODO WP Fastest cache footer indicates PHP errors.... TODO notify user (mail inbox keyword: WP Fastest Cache).

= 1.4.0 - Extricated SEO =

* **Release date:**
	* November 10th, 2017

**New Extension:**

* **[Origin](https://theseoframework.com/extensions/origin/)**: This free extension redirects attachment-page visitors back to the parent post.

**Plugin Improvements:**

* **Added:** Since we now have extension overview pages, non-tracking links have been added for each extension.
* **Improved:** Semantics regarding the differences between Premium and Free.
* **Fixed:** Buttons no longer disappear on hover in EdgeHTML.
* **Fixed:** Button shadows no longer flicker on hover in Blink.

**Extension Improvements:**

* **Free - Honeypot:**
	* **Version:**
		* 1.1.0
		* **This extension is now freely available to everyone.**
	* [View full changelog](https://theseoframework.com/extensions/honeypot/#changelog).
* **Free - Articles:**
	* **Version:**
		* 1.1.0
		* **This extension is now freely available to everyone.**
	* [View full changelog](https://theseoframework.com/extensions/articles/#changelog).
* **Free - Origin:**
	* **Version:**
		* 1.0.0
		* **This is a new extension.**
	* [View full changelog](https://theseoframework.com/extensions/origin/#changelog).

= 1.3.2 =

* **Fixed:** On some PHP installations, the [arg_separator.output](http://php.net/arg-separator.output) can be unjustifiably adjusted, preventing API connections to our servers. The plugin now enforces the correct separator regardless.

= 1.3.1 =

* **Fixed:** Added missing underscore in the FormGenerator browser script file, which prevented ReservationAction settings from showing up on FoodEstablishment types.

= 1.3.0 - Secular SEO =

* **Release date:**
	* August 22nd, 2017

**New Extension:**

* **Local SEO**: This allows you to set up important local business information for search engines to consume.

**Detailed log:**

* View the [changeset on GitHub](https://github.com/sybrew/The-SEO-Framework-Extension-Manager/compare/988fa6211c57074303388b1fbc1a86ea186a58e0...1f56769f4a6010cea8046c9de7c2c8bc297da2e6).

**Plugin Improvements:**

* **Main:**
	* **Improvement:** The extension autoloader now uses namespaces rather than class names.
		* This changes all extensions' base structure.
		* This speeds up autoloading twofold, because it no longer has to manipulate the class name.
	* **Improvement:** The layout no longer is a massive stack of repaintable flexboxes, but is now split up instead on scroll-point.
		* This fixes a layout performance issue, most prominently seen in Google Chrome. Because it no longer has to repaint 20+ full-screen flexboxes on miniscule height variation changes when scrolling.
	* **Improvement:** This plugin has now been tested against xDebug to eliminate common errors, performance culprits, and resource hogs.
		* Most prominently were security functions. They unintentionally busted the cache.
	* **Improvement:** Admin error dismissible notices can now be send and set up through AJAX, instead of only a box-header or inline notification.
		* This is great for when you encounter an error when activating an extension.
		* At most two notifications can be displayed simultaneously. It's fun to play with.
	* **Improvement:** Notices generated by this plugin no longer jump when using JavaScript.
		* This also improved non-JS admin notices, where they're outputted where they should be instantaneously.
	* **Fixed:** Various browser memory leaks have been resolved.
	* **Fixed:** Tooltips no longer disappear when the caller is tapped or clicked upon.
	* **Fixed:** Tooltips no longer overflow out of bounds when shown too high up.
	* **Fixed:** Remote status check now warns you when the subscription is expiring.
	* **Fixed:** Extension files can now be validated, and therefore activated, on Windows servers.
	* **Noted:** WP Engine staging environments will generate error 2001 when switching from staging to production and vice-versa.
		* This means that the site's integrity can't be verified. This is intentional but unwanted. We're working on a fix. Stay tuned!
		* Current workaround:
			* Write down the active extensions.
			* Deactivate account.
			* Reactivate account.
			* Reactivate written down extensions.

* **API:**
	* **Internal:**
		* When a subscription fails to validate, it will now allow you to reactivate it, without killing the options.
			* This might require a second try for API propagation when you manually disconnect your site through our website, as our caches need to catch up.
		* You can now see your subscription end date and/or next payment date.
			* See "External" for more information.
		* Error code 301 now suggests to contact your hosting provider.
			* It means that our API services can't be contacted at its very early stages.
			* Because we constantly monitor our API services, this is most likely an issue with your host or how your site is secured.
			* If you receive this error code, try again in 30 minutes. If the error is still outputted, proceed with contacting support.
	* **External:**
		* We're now sending recurring payment data information. So you'll know better when your subscription expires.

**Extension Improvements:**

* **Premium - Local:**
	* **Version:**
		* 1.0.0
		* **This is a new extension.**
* **Free - AMP:**
	* **Version:**
		* 1.0.2
	* **Added:**
		* Output filters, respectively before and after:
			* `(string) the_seo_framework_amp_pre`
			* `(string) the_seo_framework_amp_pro`
* **Premium - Articles:**
	* **Version:**
		* 1.0.1-gamma
		* **Premium only until gamma-testing is completed.**
* **Premium - Monitor:**
	* **Version:**
		* 1.0.0-beta-4
* **Premium - Honeypot:**
	* **Version:**
		* 1.0.1-beta

= 1.2.0 - Aptitudinal SEO =

* **Release date:**
	* May 15th, 2017

**New Extension:**

* **Honeypot**: This catches comment spammers in four lightweight yet powerful ways.

**Detailed log:**

View the [changeset on GitHub](https://github.com/sybrew/The-SEO-Framework-Extension-Manager/compare/7e58299cfc771315c2ed4d7940bf0981b64dc903...988fa6211c57074303388b1fbc1a86ea186a58e0).

**Plugin Improvements:**

* **Main:**
	* Performance: Instance verification key options are now correctly deleted upon account deactivation.
	* Performance: Error notice option is now no longer deleted on sight, preventing incremental option ID pollution.
	* Performance: Error notice option is now no longer autoloaded when unset.
	* Performance: Trends Feed's links no longer bind to your browser's used threads when followed. [Learn more](https://developers.google.com/web/tools/lighthouse/audits/noopener).
	* Improvement: Added useful AJAX error reporting, like for when timeouts happen.
	* Improvement: Buttons' texts are now more readable across different browsers.
	* Change: The SEO Extensions menu item now is visible for users with the `manage_options` capability, rather than either of `install_plugins` or `activate_plugins`.
	* Fixed: The extension list now also renders correctly on Safari 9 (next fix is unrelated, but had exactly the same impact).
	* Fixed: Safari 6, 7 and 8 now show all extension; which is more than [two-thirds](https://bugs.webkit.org/show_bug.cgi?id=136041).
	* Fixed: Extension activation tester now tests JSON test file for errors too.
	* Fixed: In a very unlikely event, a fatal error could be produced on either the front-end or back-end (on average once every 2,984,876,523 requests as of Unix Timestamp 1492438262).
	* Fixed: Theoretically, this plugin will now work after December 31st, 2037 on 32 bits PHP handlers.
	* Fixed: Browser memory leak through tooltips on (de)activation of extensions.
	* Change: The tooltip is now a shade of black, rather than an off-color of ocean blue.
	* Change: The internal hashing algorithm has been expanded.
		* Unless a server administrator has destructively modified PHP's source files, this shouldn't have any effect.
		* If your server supports sha1, but not sha256, you might get error 2001.
		* To resolve error 2001, take note of your active extensions, deactivate your account and set up the plugin again.
			* No useful data is lost in this process. But so far, no useful data is held either; only renewable caches.

* **API:**
	* **Internal:**
		* When trying to activate an expired subscription, it will now tell the correct error.
			* The error code has been changed from 7009 to 308 because it tried to allow a margin of error incorrectly.
	* **External:**
		* May 9th, 2017: Cancelled subscriptions now pass the end-date too.

**Extension Improvements:**

* **Free - AMP:**
	* **Version:**
		* 1.0.1
	* **Added:**
		* Indicators of where TSF output starts and ends when using TSF 2.9.2 or later.
* **Premium - Articles:**
	* **Version:**
		* 1.0.0-gamma-2
	* **Premium only until gamma-testing is completed.**
* **Premium - Monitor:**
	* **Version:**
		* 1.0.0-beta-3
* **Premium - Honeypot:**
	* **Version:**
		* 1.0.0-beta
	* **This is a new extension.**
* **Free - Transporter:**
	* **Version:**
		* 1.0.0-dev2017-05-15
	* **This extension is not accessible at the moment, because it's still in development.**

= 1.1.0 - Articulated SEO =

* **Release date:**
	* February 17th, 2017

**New Extensions:**

1. **Articles**: This automatically adds the [Structured Data for Articles](https://developers.google.com/search/docs/data-types/articles) to your posts and AMP posts.
	* **Experimental module**: Google will tell its output is erroneous.
		* They will tell it's erroneous only in their [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool).
		* They will tell it's good on your website's [Structured Data Overview](https://www.google.com/webmasters/tools/structured-data?hl=en) page.
		* The former is wrongfully determined, the output is based on [their documentation](https://developers.google.com/search/docs/data-types/articles#article_types).
2. **AMP**: This improves SEO for AMP pages.
	* This extension connects The SEO Framework to [Automattic's AMP plugin](https://wordpress.org/plugins/amp/).
	* This extension interacts with Articles when both are activated.

**Detailed log:**

**Plugin Improvements:**

* **Main:**
	* Performance: Improved plugin performance by eliminating duplicated autoloader checks.
	* Improvement: Plugin is tested and working on WordPress 4.8 (alpha).
	* Improvement: Extension list has been reordered.
	* Improvement: Extensions' compatibility has been verified and updated.
	* Improvement: When options have been deleted through an internal API request completion which failed externally, they can't be rewritten by cache again.
	* Development: Namespace `TSF_Extension_Manager_Extension` now is `TSF_Extension_Manager\Extension`
* **API:**
	* **Internal:**
		* Deactivation on decoupled sites now works again.
	* **External:**
		* No notable API changes have been made.

**Extension Improvements:**

* **Free - AMP:**
	* **Version:**
		* 1.0.0
	* **This is a new extension.**
* **Premium - Articles:**
	* **Version:**
		* 1.0.0-gamma
	* **This is a new extension.**
	* **Premium only until gamma-testing is completed.**
* **Premium - Monitor:**
	* **Version:**
		* 1.0.0-beta-2
* **Free - Incognito:**
	* **Version:**
		* 1.1.0
* **Free - Title Fix:**
	* **Version:**
		* 1.0.3

= 1.0.0 - Amplified SEO =

* **Release date:**
	* January 1st, 2017

**Changelog:**

* Initial public release.

== Upgrade Notice ==

= 1.5.0 =

This plugin now requires WordPress 4.6 or higher.

== Other Notes ==
