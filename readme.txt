=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: the seo framework, extensions, api, monitor, modules, title
Requires at least: 4.4.0
Tested up to: 4.8.0
Stable tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add more powerful SEO features to The SEO Framework right from your WordPress dashboard. No sign-up required.

== Description ==

**This plugin extends [The SEO Framework](https://wordpress.org/plugins/autodescription/).**

This plugin adds an extra dashboard menu entry, in which you can activate the latest free and premium extensions.

= Requirements: =

* For security and structural reasons, this plugin requires **PHP 5.5 or later** and **WordPress 4.4 or later**, or it will deactivate itself.
* This plugin requires **The SEO Framework 2.7.0 or later** to be active, or it won't do anything at all.

> <strong>The premium software is Open Source:</strong><br>
> This plugin and all extensions within are open source. This means they can be easily altered and shared.<br>
> If you've acquired extensions for The SEO Framework from outside your WordPress Dashboard or WordPress.org, they could contain malware.
>
> This also accounts for any other premium software acquired for free. Please, be careful.

= About Premium =

A premium subscription will allow you to activate all premium extensions for one single subscription price.

Some premium extensions can communicate with The SEO Framework's API server to provide extra functionality. This added functionality is optional and its usage differs per extension.

As long as the subscription is active, you're allowed to use all premium extensions. When the subscription expires or is deactivated, the premium extensions will no longer be accessible.

= Privacy =

* This plugin can send API requests to "https://premium.theseoframework.com/" and our other sites. Read our [privacy policy](https://theseoframework.com/privacy/).

== Installation ==

1. Install [The SEO Framework](https://wordpress.org/plugins/autodescription/) either via the plugin installer, or by uploading the files to your server.
1. Follow [those installation instructions](https://wordpress.org/plugins/autodescription/installation/).
1. Install The SEO Framework - Extension Manager either via the plugin installer, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. Follow the activation steps provided on your screen.
1. You're good to go! Enjoy!

== Screenshots ==

1. The activation page. You can choose both premium and free subscriptions.
2. The SEO Extensions overview page, running on a multisite.

== Frequently Asked Questions ==

= What is this? =
The Extension Manager for The SEO Framework allows you to enable various extensions to enhance your experience and improve your SEO.

= What is The SEO Framework? =
A free SEO plugin, for everyone! Download it [from WordPress.org](https://wordpress.org/plugins/autodescription/).

= What is an Extensions? =
An Extension is like a plugin, and can be activated and deactivated on demand. All available extensions are included and are available to be seen by anyone in this plugin package.

= Do I need to activate all extensions? =
No. The extensions are tailored for very specific types of websites. Incorrect usage of certain extensions could even harm your website's SERP rankings.
Each extension will include carefully crafted documentation in the near future.

= Do I require an account? =
Not at all! This extension manager provides up to date free extensions, without requiring an API connection.

= Are there advertisements? =
Nope. This plugin only shows which extensions are available on the activation page. It will show both free and premium ones.
The extension activation page will lead you to the site where you can purchase a license, but only if you choose to do so.

= What's the release cycle of extensions? =
Every y.X update (1.1, 1.2, 1.3, etc.) will include a new extension. Every y.y.X update (1.0.1, 1.0.2, etc.) fixes bugs and/or adds new functionality to existing extensions.
No X.y updates are planned as of now (2.0, 3.0, etc.). We plan to release a new extension every (other) month.

= I've received an error code, what now? =
Follow the steps provided next to the error code, if any. If the error keeps coming back, let us know through on the support forums.
Note that Premium Extensions aren't supported within the WordPress.org support forums; you'll have to [contact us directly](https://theseoframework.com/contact/).

= Which users can activate extensions? =
Only users who can install, update or activate plugins are allowed to interact with this plugin.
On multisite, it is planned that this behavior is restricted to the activation of plugins only.

= Where are the extensions acquired from? =
Both free and premium extensions are provided within the plugin package acquired from WordPress.org.

= Does the Extension Manager need to stay activated in order to run the extensions? =
Yes. The Extension Manager supplies its own extension activation management system apart from WordPress'.
Deactivating the Extension Manager will also deactivate all extensions.

= When my subscription expires, what happens? =
When your premium subscription expires, the premium extensions will automatically deactivate.
A margin of error is allowed, so you don't have to worry for if the activation server is down.

= I want to modify an extension, is this allowed? =
Of course! This plugin is licensed with GPLv3, after all. However, please note that the plugin checks for file changes and compares hashes to prevent rogue software penetration.
After all, this plugin can connect with our API server and we don't want third party plugins to interfere in any way.

= Does this plugin work on MultiSite Networks? =
This plugin can be network activated while The SEO Framework base plugin is activated site by site without issues.

= Are there WordPress MultiSite network specific SEO extensions? =
They're planned!

= What happens when I deactivate my account? =
All plugin settings will be deleted, this includes which extensions were enabled.
Each extension handles its own options (if any); those settings won't be lost, not even upon re-activation of the extension at a later time.

= Does my website support this plugin? =
If it doesn't, it will tell you why. Otherwise, you're good to go! All known issues are correctly labeled with an identification number.
If you were to get a plugin activation error, either open a support ticket [here](https://wordpress.org/support/plugin/the-seo-framework-extension-manager) or contact your host and ask them to upgrade PHP to a stable and secure version.

= The layout of the plugin pages just doesn't look right, why? =
This plugin has been tested against many browsers with the help of [BrowserStack](https://www.browserstack.com/), we support the latest and most popular browsers, even Internet Explorer!
However, because the plugin pages have been completely written in [state-of-the-art experimental CSS flexbox](https://www.w3.org/TR/css-flexbox-1/), it's possible not all browsers act alike.
Although everything should always fit perfectly; if you do find any issue, please state your browser and operating system and tell us where the issue resides. Thanks!

== Changelog ==

= 1.1.0 - Articulated SEO =

* **Release date:**
	* ???

**New Extensions:**

1. **Articles**: This automatically adds the [Structured Data for Articles](https://developers.google.com/search/docs/data-types/articles) to your posts and AMP posts.
	* **Experimental module**: Google will tell its output is erroneous.
		* They will tell it's erroneous only in their [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool).
		* They will tell it's good on your website's [Structured Data Overview](https://www.google.com/webmasters/tools/structured-data?hl=en) page.
		* The former is wrongfully determined, the output is based on [their documentation](https://developers.google.com/search/docs/data-types/articles#article_types).
2. **AMP**: This improves SEO for AMP pages.
	* This extension connects The SEO Framework to [Automattic's AMP plugin](https://wordpress.org/plugins/amp/).
	* This extension interacts with Articles, when activated.

**Detailed log:**

**Plugin Improvements:**

* **Local:**
	* Performance: Improved plugin performance by eliminating duplicated autoloader checks.
	* Improvement: Plugin is tested and working on WordPress 4.8 (alpha).
	* Improvement: Extension list has been reordered.
	* Improvement: Extensions' compatibility has been verified and updated.
	* Improvement: When options have been deleted through an internal API request completion which failed externally, they can't be rewritten by cache again.
	* Development: Namespace `TSF_Extension_Manager_Extension` now is `TSF_Extension_Manager\Extension`
* **API:**
	* **Internal:**.
		* Deactivation on decoupled sites now work again.
	* **External:**.
		* No notable API changes have been made.

**Extension Improvements:**

* **Free - AMP:**
	* **Version:**
		* 1.0.0
	* **This is a new extension**.
	* It binds The SEO Framework social and general output to the [AMP plugin](https://wordpress.org/plugins/amp/).
	* Use this extension in combination with the Articles extension to further enhance output.
* **Premium - Articles:**
	* **Version:**
		* 1.0.0-gamma
	* **This is a new extension**.
	* **Premium only until gamma-testing is completed.**
	* It outputs Article Schema.org output for both AMP (AMP extension required) and non-AMP pages.
	* Note: Google's Structured Data Tester renders output data invalid, even though it's valid and output according to their set requirements.
* **Premium - Monitor:**
	* **Version:**
		* 1.0.0-beta-2
	* **Internal:**
		* **Improved:**
			* The "invalid sitemap" notification now suggest to contact premium support, rather than stating the inconvinient obvious.
		* **Fixed:**
			* The robots.txt file got marked as static whilst being dynamic when another plugin or theme affects the output.
			* Requesting a crawl through AJAX now works correctly when debugging is enabled.
			* Updating data through AJAX now works correctly when debugging is enabled.
	* **API:**
		* **Note:** These API changes affect only this plugin version.
			* **Fixed:**
				* A PHP warning was output when making the first connection.
				* Disconnection on decoupled sites now works.
				* When the site has been decoupled from the API server, the notices are now more in-line with events.
				* When the site has been decoupled from the API server, the remote data will be removed from view.
		* **Note:** These API changes affect all plugin versions.
			* **Improved:**
				* The API server has been moved to a dedicated server instance. This allows for more accurate measurements in upcoming features.
				* The API server now parses crawl requests automatically.
			* **Fixed:**
				* The sitemap detection for more advanced sitemaps now works.
				* When a HTTP error is generated when fetching the sitemap, this is now correctly handled.
* **Free - Incognito:**
	* **Version:**
		* 1.1.0
	* **Added:**
		* Now also removes The SEO Framework 2.8.0 sitemap stylesheet plugin link.
* **Free - Title Fix:**
	* **Version:**
		* 1.0.3
	* **Improved:**
		* Removed redundant UTF-8 check.
		* It now enqueues fewer actions when the title is fixed early.
		* It also enqueues fewer WordPress actions overall. Which reduces memory usage marginally.
	* **Fixed:**
		* The SEO Framework can now recognize this extension, therefore re-enabling otherwise disabled features.

= 1.0.0 - Amplified SEO =

* **Release date:**
	* January 1st, 2017

**Changelog:**

* Initial public release.

== Upgrade Notice ==

== Other Notes ==

= Are you a developer? =
And do you wish to add your own extension to the extension manager? Please contact me on [Slack](https://wordpress.slack.com/messages/@cybr/) about your idea.
A full code review will take place prior to releasing it. The code has to pass at least all of the WordPress.org plugin standards and all code must be licensed under GPLv3.
Feedback and points for improvement will be always given. No monetized nor premium extensions are being accepted as of yet. API connections aren't allowed either.

= For developers: Security =
Because this plugin handles multiple input fields, multiple nonce fields had to be created in order to prevent XSS from otherwise unauthorized users.
In order to minimize overhead, each nonce type has been supplied an action. This way, the validation all falls under one function.
Many more security techniques, some unprecedented in open source, have been implemented into this plugin.
If you have any questions, before blindly implementing or circumvent security, feel free to contact me (the plugin author) on [Slack](https://wordpress.slack.com/messages/@cybr/).

= Reluctance towards plugin modifications and backwards compatibility =
This plugin should be compatible with any other plugin or theme (unless they cause PHP errors on their own).
You are allowed to edit this plugin and use hooks on this plugin (as per GPLv3), but any external or internal modification can stop working on any update without prior notice.
This is to enhance the plugin security, stability and overall performance. Please note that most core functions, classes, methods and files are shielded against both direct and indirect calls.
No backwards compatibility will be programmed into this plugin, unless required for WordPress Core or PHP.
