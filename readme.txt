=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: the seo framework, extensions, monitor, modules, title, seo, schema, local, articles, honeypot, amp
Requires at least: 4.4.0
Tested up to: 4.8.1
Requires PHP: 5.5
Stable tag: 1.3.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add more powerful SEO features to The SEO Framework right from your WordPress dashboard. No sign-up required.

== Description ==

**Advanced and powerful SEO.**
**Delivered through extension for [The SEO Framework](https://wordpress.org/plugins/autodescription/).**

This plugin provides an advanced WordPress administrative dashboard page where you can activate the latest free and premium extensions.

= Included Extensions =

**The following extensions are included:**

* **Local:** The Local extension lets you set up important local business information for search engines to consume.
* **AMP:** The AMP extension binds The SEO Framework to the AMP plugin for AMP supported articles and pages.
* **Articles:** The Articles extension enhances your published posts by automatically adding both AMP and non-AMP Structured Data.
* **Monitor:** The Monitor extension keeps track of your website’s SEO, optimization, uptime and statistics.
* **Incognito:** The Incognito extension removes all front-end branding from The SEO Framework.
* **Title Fix:** The Title Fix extension makes sure your title output is as configured. Even if your theme is doing it wrong.
* **Honeypot:** The Honeypot extension catches comment spammers through four lightweight yet powerful ways.

= Upcoming Extensions =

**These extensions are being worked on:**

* **Transporter:** It allows you to export and import your SEO settings from site to site.
* **Attachment Redirect:** It will automatically redirect visitors to the parent post of attachment pages.

= How it works =

This plugin offers a lightweight dashboard wherein you can activate and deactivate extensions.
Only the activated extensions are loaded. Other extensions are dormant and the files of those aren't even touched.

Some of the extensions are completely free to use, others are premium.
Because we want to see how our new state-of-the-art extensions perform before releasing it to everyone, all beta-staged extensions are temporarily premium.

= About Premium =

A premium subscription will allow you to activate all premium extensions for one single subscription price.

Some premium extensions can communicate with The SEO Framework's API server to provide extra functionality. This added functionality is optional and its usage differs per extension.

If the subscription is active, you're allowed to use all premium extensions. When the subscription expires or is deactivated, the premium extensions will no longer be accessible.

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
Feedback and points for improvement will be always given. No monetized nor premium extensions are being accepted yet. API connections aren't allowed either.

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

= 1.4.0 - TODO SEO =

* **Release date:**
	/
	* TODO

**New Extension:**
/
* **Attachment Redirect**: TODO

* **API:**
	* **Internal:**
		/
		* TODO
	* **External:**
		/
		* August TODO-th, 2017: WP Fastest cache footer indicates PHP errors.... TODO notify user (mail inbox keyword: WP Fastest Cache).

**Extension Improvements:**

* **Premium - Honeypot:**
	* **Version:**
		* 1.0.2-beta
	* **Fixed:**
		* The scoped style node now works in newer version of Blink, Gecko and Webkit. Making the scoped field disappear for users as intended.

* **Premium - Articles:**
	* **Version:**
		* TODO
	* **Added:**
		* The SEO Framework 3.0.0 URL generation compatibility.
			* If you do not update the Extension Manager, this extension will invoke deprecation warnings.

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
	* **Features:**
		* [View use cases here](https://developers.google.com/search/docs/data-types/local-businesses#use_cases).
		* All supported businesses are included.
		* Food Establishment types can annotate their cuisines, menu URL, and reservation actions.
			* Are you missing a cuisine? Let us know!
		* This extension allows you to set up detailed business information as Structured Data.
			* It outputs Schema.org JSON-LD scripts as Structure Data when set up.
				* This means its output works on any theme.
				* Users generally won't see this output.
				* Note that its output will only be used when the site is adequate.
			* Note that the Structured Data should reflect on what's outputted as text for the users on the website.
			* Note that Google My Business is leading, whereas this extension then provides complimentary data.
				* If you haven't registered with Google My Business, then this extension's output is leading.
				* If you have multiple departments and are using Google My Business, they should also be registered there.
				* You do not need to verify your business address when using this extension before it shows up in Google.
		* It allows for up to 4095 sub-departments.
			* If you've also signed up for Google My Business, these sub-departments will be displayed under the main department's name only.
			* The sub-departments must be annotated on the same website.
		* It's great for both small and large businesses whom want to be found regionally.
		* It features an API connection with our servers for (reverse) geocoding, so your business address can be filled in accurately.
			* This means you won't have to sign up at Google yourself, which saves you a lot of time.
		* It features a state-of-the-art form generator, which allows you to register up to 4096 departments.
	* **Documentation:**
		* We're currently preparing our site for documentation. When finished, we'll release an update which will add hyperlinks to the extension entry boxes.
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
	* **Changed:**
		* Google states that [some output is ignored](https://developers.google.com/search/docs/data-types/articles), but that doesn't mean the output is overlooked. So:
			* Published date is now also output on non-AMP.
			* Modified date is now also output on non-AMP.
			* Post Author is now also output on non-AMP.
			* Publisher (Organization name) is now also output on non-AMP.
			* Description is now also output on non-AMP.
		* Note: The data may still be marked invalid by the [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool), although far less likely.
			* The data will always be checked for validity on both AMP and non-AMP, while adhering to Google's guidelines.
			* The data should never be marked invalid on the AMP version.
	* **Improved:**
		* The description is now taken from cache, rather than being regenerated.
			* This can yield a large beneficial performance effect when parsing large texts.
* **Premium - Monitor:**
	* **Version:**
		* 1.0.0-beta-4
	* **Internal:**
		* **Improved:**
			* The privacy policy link on the registration page now opens in a new window and no longer tells us from where you clicked it.
		* **Fixed:**
			* Manual update buttons are no longer handling clicks of it twice.
			* When debugging, the Update Data button no longer fails AJAX calls when parsing data.
	* **API:**
		* **External**
			* **Fixed:**
				* May 16th, 2017: The return data now supports 1.0.0-beta-3 and later, rather than 1.0.0 and later.
					* This outputted a notice on all testing fields and therefore showed no useful information.
				* July 6th, 2017: URLs with UTF-8 and UTF-16 characters can now be detected.
					* This improves canonical URL detection on some sites.
					* This improves icon detection when using Jetpack Photon, for example.
* **Premium - Honeypot:**
	* **Version:**
		* 1.0.1-beta
	* **Improved:**
		* The CSS rotation field now uses a scoped style node, rather than inline styling.
	* **Changed:**
		* Moved the honeypot above the comment form, so spammers will more easily fill it in.

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
	* **Changed:**
		* The automated description is now set to 155 characters, rather than 400.
			* Evidently, it uses the same restrictions as regular search results.
	* **Fixed:**
		* No more PHP notices should be output when fetching an image from the SEO settings.
		* Social image from SEO settings now works if set, but only if TSF 2.9+ is active.
			* Otherwise the Featured Image is used, if any.
		* It no longer outputs Schema.org metadata on WooCommerce products or other single custom post types.
			* Instead, it only outputs on WordPress' Posts.
* **Premium - Monitor:**
	* **Version:**
		* 1.0.0-beta-3
	* **Internal:**
		* **Changed:**
			* The Monitor menu item now is visible for users with the `manage_options` capability, rather than `install_plugins`.
		* **Fixed:**
			* More aggressive buffer cleaning has been put in place to prevent failing AJAX requests.
			* The SEO Framework's internal debugging methods can no longer interfere with the output.
			* Server and Browser AJAX errors can now resolve when requesting updates.
			* Server and Browser AJAX errors can now resolve when requesting crawl.
	* **API:**
		* **Note:** These API changes affect only this plugin version.
			* **Added:**
				* The crawler now tests for Scheme settings. It checks for HTTPS headers and the related canonical URL output.
					* Also vice versa, so if your site isn't accessible on HTTPS, but your canonical URL states it is, it will warn you.
		* **Note:** These API changes affect all plugin versions.
			* **Added:**
				* May 7th, 2017: Only the root file is now checked, rather than subdirectories.
				* May 14th, 2017: It now confirms if your website is accessible on HTTPS.
				* May 14th, 2017: It now checks canonical URLs for scheme issues.
			* **Fixed:**
				* March 18th, 2017: When your site embeds external services like YouTube, the crawler no longer crashes.
				* April 1st, 2017: When your metadata favicon isn't output by WordPress, it can now also be detected.
				* **A better foundation:**
					* May 14th, 2017: Alternative output of the metadata favicon tag's closing tag can now be detected too.
					* May 14th, 2017: Compressed pages can now also be correctly tested for favicons.
					* May 14th, 2017: Single quotes' favicons metadata can now also be detected.
* **Premium - Honeypot:**
	* **Version:**
		* 1.0.0-beta
	* **This is a new extension.**
	* It uses four powerful and lightweight techniques to stop comment spamming:
		1. A rotating by ID input field, hidden through rotating CSS.
			* This field must stay empty.
			* This requires the spammer to enable styling.
			* This field is rotated per post.
		1. A rotating by time input field, hidden through rotating CSS.
			* This field must stay empty.
			* This requires the spammer to enable styling.
			* A new key is generated every hour and for every post.
			* After two hours of waiting, this field has no positive or negative effect.
			* It acts as the first method when using caching.
		1. A rotating by time input field that must be emptied, hidden and automatically emptied through rotating JavaScript.
			* This field must become empty.
			* This requires the spammer to enable scripts.
			* If scripts are disabled, the user needs to manually clear the field.
				* A helpful message is displayed that clearly indicates it's an anti-spam technique.
				* That message is translatable; in the future options will be added so you can manually adjust these fields.
			* This always works with caching and is proven to be the most effective method with 99,99% catch rate over 1500 comments in 1 month.
		1. A rotating by time nonce field, that must be identical to the expected value.
			* This field must have an expected value.
			* This prevents spammers using PHP files to comment; instead, the must view and render your comment forms.
			* The nonce key is different for each post and is time-attack secure.
			* The nonce key changes every 12 hours. Each key is valid for 24 hours.
				* When using caching, the nonce key changes every 5 days. Each key is valid for 10 days.
	* It works by using expected hashing algorithms. So it doesn't make use of the database. Therefore, it's extremely lightweight.
	* It works wherever WordPress' comments are used, also on WooCommerce reviews.
	* It works only when users are logged out. Users who are logged in aren't checked.
	* It works with caching, then being less aggressive.
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
	* It binds The SEO Framework social and general output to the [AMP plugin](https://wordpress.org/plugins/amp/).
	* Use this extension in combination with the Articles extension to further enhance output.
* **Premium - Articles:**
	* **Version:**
		* 1.0.0-gamma
	* **This is a new extension.**
	* **Premium only until gamma-testing is completed.**
	* It outputs Article Schema.org output for both AMP (AMP extension required) and non-AMP pages.
	* Note: Google's Structured Data Tester renders output data invalid, even though it's valid and output according to their set requirements.
* **Premium - Monitor:**
	* **Version:**
		* 1.0.0-beta-2
	* **Internal:**
		* **Improved:**
			* The "invalid sitemap" notification now suggest you contacting premium support, rather than stating the inconvinient obvious.
		* **Fixed:**
			* The robots.txt file got marked as static whilst being dynamic when another plugin or theme affects the output.
			* Requesting a crawl through AJAX now works correctly when debugging is disabled.
			* Updating data through AJAX now works correctly when debugging is disabled.
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
				* When an HTTP error is generated when fetching the sitemap, this is now correctly handled.
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
