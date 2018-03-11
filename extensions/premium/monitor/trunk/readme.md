# Monitor
Contributors:
Location: https://theseoframework.com/extensions/monitor/
Tags: spam
Requires at least: 4.4.0
Required PHP: 5.5.21 or 5.6.5
Tested up to: 4.9.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension keeps track of your website's SEO, optimization, uptime, and statistics.

## Overview

### Let us look

Maintaining a WordPress website isn't easy.
Before you start, you must find a suitable theme, good plugins, and then set them all up.

You might not be wary of your website's issues, its performance, and sometimes plugins conflict with the theme or eachother.

This is where our Monitor services come in handy.
Monitor looks at your website as a real visitor would. Directly from our servers.
Without WordPress' obstruction, it checks if all the basics have correctly been set up.

### An inspection for WordPress

Like other web inspectors, Monitor looks for basic website errors.
Unlike other web inspectors, it compares found data to your WordPress environment, through a two-phase check.

The first phase happens on our servers, where we crawl several URLs on your website to find common issues. All information we find is sent back to the Monitor extension.

The second phase happens in your dashboard, where the information is compared to your WordPress environement, like SEO settings.

This cooperation creates personalized information, like no other inspector could.

### Statistical data, bundled with your license

*N.B. These features are under construction. ETA early 2018.*

Website statistics, like uptime and performance, can cost you unnecessarily amounts.

With Monitor, it's included in your Premium license.

#### A fast website

Website performance is a ranking factor. Not directly, but eventually so.

This is because us humans like responsive interaction. If a website is too slow, then we'll bounce back to the search results.
Thus increasing your website's bounce rate.

Increased bounce rate indicates to search engines that your content wasn't relevant to the search query.
When this happens more often than not, your website will fall in search ranking position.

Monitor will, in set intervals, check your website's performance.

#### High uptime

Website uptime is another ranking factor. This one is directly measured.

Naturally, a website that cannot be reached won't sell anything and will miss out on user engagement.
A website that's down will also increase bounce rate. As described earlier, your website will then fall in search ranking position.

When a website is down often, a page can be seen as missing. This can eventually deindex your pages from the search results.

Monitor will, also in set intervals, check your website's uptime. So you can be certain that your website is available.

### Privacy first

Monitor is a web service running on our servers.
To operate, our servers only require your website's URL and a key.

The key is generated when you first connect the Extension Manager to our API services.
This helps us validate your website's integrity, so we know it's your website contacting us.

When crawling, Monitor looks at your website as any other visitor would, or could.
And, when you disconnect your site from our services, all crawled data will be erased.

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

This is done in scheduled one-minute intervals. When many sites are in the queue, some might be moved upfront at the next interval.

If you wish to enqueue a crawl, click on the "Request Crawl" button in the Control Panel.

[tsfep-image id="3"]

### Updating

To receive the latest Monitor data, hit "Update Data".

If you just initiated a crawl request, you might receive outdated or incorrect data. It takes time to crawl a website, after all.

[tsfep-image id="4"]

## Changelog

*Because Monitor is a heavily dependent two-part system, these changes are annotated through Extension and API nodes.*

### 1.1.0-beta-5

[tsfep-release time="-1"]

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
	* **Improved:** The "invalid sitemap" notification now suggest you contacting premium support, rather than stating the inconvinient obvious.
	* **Fixed:** The robots.txt file got marked as static whilst being dynamic when another plugin or theme affects the output.
	* **Fixed:** Requesting a crawl through AJAX now works correctly when debugging is disabled.
	* **Fixed:** Updating data through AJAX now works correctly when debugging is disabled.
* **API - Extension:**
	* **Fixed:** A PHP warning was output when making the first connection.
	* **Fixed:** The disconnection button on decoupled sites now works.
	* **Fixed:** When the site has been decoupled from the API server, the notices are now more in-line with events.
	* **Fixed:** When the site has been decoupled from the API server, the remote data will be removed from view.
* **API - Our servers:**
	* **Improved:** The API server has been moved to a dedicated server instance. This allows for more accurate measurements for upcoming features.
	* **Improved:** The API server now parses crawl requests automatically.
	* **Fixed:** The sitemap detection for more advanced sitemaps now works.
	* **Fixed:** When an HTTP error is generated when fetching the sitemap, this is now correctly handled.

### 1.0.0-beta-1

[tsfep-release time="January 1st, 2017"]

* Initial extension release.
