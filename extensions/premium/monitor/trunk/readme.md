# Monitor
Location: https://theseoframework.com/extensions/monitor/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension keeps track of your website's SEO optimizations and statistics.

## Overview

### Let us take a look

Maintaining a WordPress website isn't easy. Before you start, you must find a suitable theme, useful plugins, and then set them all up.

Now, you might not be wary of your website's issues and plugins conflict with the theme or each other.

This is where the Monitor services comes in. The Monitor extension looks at your website as a real visitor would--directly from our servers.
Without WordPress's obstruction, it checks if all the basics have correctly been set up.

### An inspection for WordPress

Like other web inspectors, Monitor looks for common website errors.
Unlike other web inspectors, it compares found data to your WordPress environment, with a two-phase check.

The first phase happens on our servers, where we crawl several URLs on your website to find common issues. All information we find is sent back to the Monitor extension.

The second phase happens in your dashboard, where the information is compared to your WordPress environment, like SEO settings.

This cooperation creates personalized information like no other inspector could.

### Privacy first

Monitor is a web service running on our servers.
To operate, our servers only require your website's URL and a key.

The key is generated when you first connect the Extension Manager to our API services.
This helps us validate your website's integrity, so we know it's your website contacting us.

When crawling, Monitor looks at your website as any other visitor would, or could.
And, when you disconnect your site from our services, all crawled data will be erased.

We remove all data we crawled from your site automatically after 90 days of inactivity.

## Usage

[tsfep-bundled]

### Activate Monitor

First, you'll need to activate Monitor.

After activating the extension, you'll find a menu link displayed next to the activation button.
That link will direct you to the Monitor registration page.

[tsfep-image id="1"]

### Registration

On the registration page, you'll find a privacy statement. Beneath it, you can connect your website.

When you hit the "Register" button, your site will be registered.

Your website will also automatically be enqueued for a crawl.

[tsfep-image id="2"]

### Crawling

Crawling a website from our servers can take from a few seconds up to three minutes.

This is done in scheduled one-minute intervals. When many sites are in the queue, some might be moved up front at the next interval.

If you wish to enqueue a crawl, click on the "Request Crawl" button in the Control Panel.

[tsfep-image id="3"]

### Updating

To receive the latest Monitor data, hit "Update Data".

If you just initiated a crawl request, you might receive outdated or incorrect data. It takes time to crawl a website, after all.

[tsfep-image id="4"]

## Changelog

*Because Monitor is a two-part system, changes are differentiated via Extension and API.*

### 1.2.12

[tsfep-release time="-1"]

* **API - Our servers:**
	* **June 13th, 2024:**
		* **Improved:** Scheduled tasks are now performed twice as often.
		* **Fixed:** When a site is deleted due to inactivity, reconnecting is now possible.
		* **Fixed:** We found that not all site data was purged in the past, so we manually truncated the databases. You may find that recrawling is necessary.
* **Extension:**
	* **Improved:** To accomodate the increased frequency of scheduled tasks, we halved all wait times for crawls requesting and data fetching.
	* **Fixed:** API errors are handled more gracefully.
	* **Fixed:** Resolved an issue where not all actions invoked a reconnection resolution when required.

### 1.2.11

[tsfep-release time="November 2nd, 2023"]

* **Extension:**
	* **Fixed:** No longer conflicts with The SEO Framework's debugging features.

### 1.2.10

[tsfep-release time="June 22nd, 2023"]

* **API - Our servers:**
	* **April 21st, 2023:**
		* **Added:** WordPress sitemaps are now crawled by the sitemap tester.

### 1.2.9

[tsfep-release time="February 7th, 2023"]

* **Extension:**
	* **Fixed:** Connection errors are now forwarded correctly.
* **API - Our servers:**
	* **January 27th, 2023:**
		* **Improved:** Licensing errors are now more descriptive.

### 1.2.8

[tsfep-release time="October 4th, 2022"]

* **Extension:**
	* **Improved:** The valid sitemap message is now more descriptive.
	* **Improved:** Modernized code with a focus on improving performance.
* **API - Our servers:**
	* **September 27th, 2022:**
		* **Improved:** Improved lookups for descriptions, canonical URLs, and icons, now supporting more vigorous minification plugins.

### 1.2.7

[tsfep-release time="February 9th, 2021"]

* **API - Our servers:**
	* **July 5th, 2020:**
		* **Fixed:** The HTTPS test no longer expects subsequent redirects when the site can be served from both HTTP and HTTPS.
	* **July 7th, 2020:**
		* **Fixed:** The mixed scheme test no longer defaults to mixed, but correctly tests both the HTTP and HTTPS versions of your site.

### 1.2.6

[tsfep-release time="June 2nd, 2020"]

* **Changed:** This extension's admin access can now be controlled via the global constant `TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE`.
* **Fixed:** When all data from our server stored on your site is outdated, Monitor will now inform you about it, instead of displaying a blank pane.

### 1.2.5

[tsfep-release time="May 15th, 2020"]

* **Extension:**
	* **Other:** This extension is now out of beta! We're now ready to expand to per-page reviews, which will follow in a future update.
	* **Improved:** When no title tag is found, a non-generic message is now shown.
	* **Changed:** Removed the "coming soon" statistics pane.
	* **Changed:** Removed the "coming soon" issue.
	* **Fixed:** The PHP error evaluation link now works for the homepage.
	* **Fixed:** The `robots.txt` asserter now recognizes and extrudes The SEO Framework v4.0.5 robots' validator.
* **API - Our servers:**
	* **February 12th, 2020:**
		* **Fixed:** Nonconventional title tags are now recognized.
		* **Fixed:** Non-header title tags are no longer detected as extraneous.
	* **May 14th, 2020:**
		* **Fixed:** 302 redirects are now honored correctly.

### 1.2.4-beta-5

[tsfep-release time="December 18th, 2019"]

* **Extension:**
	* **Changed:** The `favicon.ico` check is now hidden when a meta icon is found. We changed this because modern browsers no longer ping the ico file when a meta-icon is proposed.
* **API - Our servers:**
	* **December 17th, 2019:**
		* **Fixed:** You can now disconnect from the Monitor service after moving domains.

### 1.2.3-beta-5

[tsfep-release time="November 5th, 2019"]

* **Improved:** To honor the new Extension Manager interface, we restyled the non-default items.

### 1.2.2-beta-5

[tsfep-release time="January 28th, 2019"]

* **Changed:** This extension now uses TSF's script loader.

### 1.2.1-beta-5

[tsfep-release time="December 4th, 2018"]

* **Extension:**
	* **Added:** The plugin now shows description meta tag information.
	* **Changed:** The scheme warning is now more severe, because Firefox and Chrome display warnings that are off-putting.
	* **Changed:** When the canonical URL isn't found, or is deemed invalid, an error now shows.
	* **Changed:** The `favicon.ico` test is now always displayed, regardless of the site icon settings.
	* **Changed:** Crawling status notifications are now displayed at the top of the page.
	* **Fixed:** Various keyboard accessibility issues.
* **API - Our servers:**
	* **December 3rd, 2018:**
		* The server is now allowed to communicate with v1.2.1.
		* Added description meta tag tests.
		* Reduced false negatives of canonical URL detection.
		* Reduced false negatives of embedded icon detection.
		* A favicon.ico redirect can now be followed.
		* The server now purges all website data older than 90 days automatically.

### 1.2.0-beta-5

[tsfep-release time="August 28th, 2018"]

* **Extension:**
	* **Added:** TSF v3.1 support.

### 1.1.0-beta-5

[tsfep-release time="March 31st, 2018"]

* **Extension:**
	* **Improved:** The admin layout matches the new and modernized standard.
* **API - Extension:**
	* **Added:** The crawler now tests for title tags. It checks if the [tsfep-extension name="title-fix"] is needed, or when you need to consult with your theme developer.
	* **Added:** Last-crawled date is now fetched and displayed.
	* **Added:** You can now set uptime monitoring and performance monitoring delays.
		* When enabled, your website can participate in the initial uptime and performance monitoring runtime testing stages.
	* **Fixed:** When a scripting error occurs on the Monitor servers, you'll now be notified correctly.
* **API - Our servers:**
	* **January 16th, 2018:**
		* **Fixed:** Data is being sent correctly again (outage lasted 12 hours due to a scripting error).
	* **January 15th, 2018:**
		* **Added:** Sites can now manage their monitoring settings independently.
	* **December 17th, 2017:**
		* **Added:** We now store title tags.
	* **December 20th, 2017:**
		* **Added:** We now record last-crawled date.

### 1.0.0-beta-4

[tsfep-release time="August 22nd, 2017"]

*N.B. This version is annotated as 1.0.0-β-3 within the extension activation page.*

* **Extension:**
	* **Improved:** The privacy policy link on the registration page now opens in a new window and no longer tells us from where you clicked it.
	* **Fixed:** Manual update buttons are no longer handling clicks of it twice.
	* **Fixed:** When debugging, the Update Data button no longer fails AJAX calls when parsing data.
* **API - Our servers:**
	* **May 16th, 2017:**
		* **Fixed:** The return data now supports 1.0.0-beta-3 and later, rather than 1.0.0 and later.
			* This outputted a notice on all testing fields and therefore showed no useful information.
	* **July 6th, 2017:**
		* **Fixed:** URLs with UTF-8 and UTF-16 characters can now be detected.
			* This improves canonical URL detection on some sites.
			* This improves icon detection when using Jetpack Photon, for example.

### 1.0.0-beta-3

[tsfep-release time="May 15th, 2017"]

* **Extension:**
	* **Changed:** The Monitor menu item now is visible for users with the `manage_options` capability, rather than `install_plugins`.
	* **Fixed:** More aggressive buffer cleaning has been put in place to prevent failing AJAX requests.
	* **Fixed:** The SEO Framework's internal debugging methods can no longer interfere with the output.
	* **Fixed:** Server and Browser AJAX errors can now resolve when requesting updates.
	* **Fixed:** Server and Browser AJAX errors can now resolve when requesting crawl.
* **API - Extension:**
	* **Added:** The crawler now tests for Scheme settings. It checks for HTTPS headers and the related canonical URL output.
		* Also vice versa, so if your site isn't accessible on HTTPS, but your canonical URL states it is, it will warn you.
* **API - Our servers:**
	* **March 18th, 2017:**
		* **Fixed:** When your site embeds external services like YouTube, the crawler no longer crashes.
	* **April 1st, 2017:**
		* **Fixed:** When your metadata favicon isn't outputted by WordPress, it can now also be detected.
	* **May 7th, 2017:**
		* **Added:** Only the root file is now checked, rather than subdirectories.
	* **May 14th, 2017:**
		* **Added:** We now confirm if your website is accessible on HTTPS.
		* **Added:** We now check canonical URLs for scheme issues.
		* **Fixed:** Alternative output of the metadata favicon tag's closing tag can now be detected too.
		* **Fixed:** Compressed pages can now also be correctly tested for favicons.
		* **Fixed:** Single quotes' favicons metadata can now also be detected.

### 1.0.0-beta-2

[tsfep-release time="February 17th, 2017"]

* **Extension:**
	* **Improved:** The "invalid sitemap" notification now suggest you contacting premium support, rather than stating the inconvenient obvious.
	* **Fixed:** The robots.txt file got marked as static while being dynamic when another plugin or theme affects the output.
	* **Fixed:** Requesting a crawl through AJAX now works correctly when debugging is disabled.
	* **Fixed:** Updating data through AJAX now works correctly when debugging is disabled.
* **API - Extension:**
	* **Fixed:** A PHP warning was output when making the first connection.
	* **Fixed:** The disconnection button on decoupled sites now works.
	* **Fixed:** When your website has been decoupled from the API server, the notices are now more in-line with events.
	* **Fixed:** When the site has been decoupled from the API server, the remote data will be removed from view.
* **API - Our servers:**
	* **Improved:** The API server has been moved to a dedicated server instance. This allows for more accurate measurements for upcoming features.
	* **Improved:** The API server now parses crawl requests automatically.
	* **Fixed:** The sitemap detection for more advanced sitemaps now works.
	* **Fixed:** When an HTTP error is generated when fetching the sitemap, this is now correctly handled.

### 1.0.0-beta-1

[tsfep-release time="January 1st, 2017"]

* Initial extension release.
