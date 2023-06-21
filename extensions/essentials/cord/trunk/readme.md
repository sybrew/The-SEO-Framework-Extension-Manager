# Cord
Location: https://theseoframework.com/extensions/cord/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension helps you connect your website to third-party services, like Google Analytics and Meta Pixel.

## Overview

### Quick and easy

Manage third-party services for your WordPress website with ease. Activate the extension, fill in the connection keys, and you're done. Cord takes care of the rest.

Cord supports the Google Analytics and Facebook Pixel services. For these, you require a "Google Analytics" and "Facebook for Business" accounts, respectively.

### Sensical Google Analytics

Cord provides only the critical scripts to set up the Google Analytics connection. No tracking data is stored or processed on your website. Google Analytics provides all insights via their website; where it should be.

Cord also automatically fixes the search query URLs in WordPress before relaying them, so that Google Analytics knows how to sort these&mdash;without losing any data.

### Simplified Meta Pixel

Cord provides exactly one field for Facebook Pixel tracking. When you've filled that in, you can start tracking visitors right away for remarketing.

## Usage

[tsfep-bundled]

### Activate Cord

First, you'll need to activate the Cord extension.

### Extension settings

Underneath the extension description, you should see a settings-link appear. You can also find the link to "Extension Settings" under "SEO" in the admin sidebar, but you may need to refresh the page first.

On the Extension Settings page, you can set up the required connection information for Google Analytics and Meta Pixel.

### Enabling Google Analytics

To get started with Google Analytics, follow these steps:

1. Go to [Google Analytics](https://www.google.com/analytics/). Sign up with Google or log into your existing account.
2. Set up a property. The property will represent your website.
3. You will now see your Measurement ID at the top of the page. It should start with `G-`.

If you cannot find the Measurement ID, please follow [Google's instructions](https://support.google.com/analytics/answer/12270356).

[tsfep-image id="1"]

4. Copy that ID and paste it in Cord's corresponding field on the Extension Settings page. Don't forget to hit save.

[tsfep-image id="2"]

When fully set up, Google Analytics will start tracking visitors and provide you event, performance, and other insights.

### Enabling Meta Pixel

To get started with Meta Pixel, you first need a Meta for Business account.

1. Go to Facebook's [Event Manager](https://www.facebook.com/events_manager2/list/pixel/). Sign up with Facebook or log into your existing account tied to the business.
2. Select "Connect Data Source" in the side menu.
3. Select "Web."
4. Provide the pixel name. The other fields are optional.
5. Close or cancel the installation dialog -- Cord isn't listed there.
6. At the top of the page, you should see your pixel ID.

[tsfep-image id="3"]

7. Click on the ID to copy it, and paste it in Cord's corresponding field on the Extension Settings page. Don't forget to hit save.

[tsfep-image id="4"]

When fully set up, Meta Pixel will start tracking visitors and provide you with various insights for advertising.

### Privacy

Your website and visitors may be subject to General Data Protection Regulation (GDPR), California Consumer Privacy Act (CCPA), or other legislations. Under these regulations, you may be required to inform your visitors about cookies, data collection, and data processing. You may also be required to anonymize the IP address.

## FAQ

### Where does Cord output the analytical scripts?

Cord outputs the scripts on every non-administrative WordPress page of your website.

The login, server-error, REST, feed, and WordPress-admin pages aren't tracked.

### Where can I view the analytical data?

You can find live and aggregated data visualized on the provider's website. Follow the links below to view your dashboard. Make sure you're logged into the right account.

- [Visit Google Analytics dashboard](https://analytics.google.com/analytics/web/).
- [Visit Facebook pixel analytics dashboard](https://www.facebook.com/analytics/).

### Why can't I just view this data in my WordPress dashboard?

The analytics providers have created a dashboard that works perfectly well. We can't just take that as-is, and we see no use in recreating something else from scratch. Cord is an elegant and simple solution without any bulk. There are other solutions out there that visualize the data into your dashboard, might you fancy that.

### Does Cord track everyone?

Cord outputs the tracking script for all users--including logged-in users. However, there are browser extensions and services, like uBlock Origin and Pi-hole, which can halt the scripts. They make these visitors invisible for these tracking services.

### Can we bypass that?

Yes. But we won't tell you how. Privacy is sacred.

### Why would I choose this extension over Google's official Site Kit plugin?

[Site Kit by Google](https://wordpress.org/plugins/google-site-kit/) provides advanced integration for some other plugins that can help you with tracking, conversion, and advertisement campaigns. It's inefficient, tracks the plugin usage, and will slow down your site tremendously. If you just want a simple integration for tracking visitors, then this extension provides the lightweight alternative.

### Why would I choose this extension over Facebook's official plugin?

[Facebook's official Meta Pixel plugin](https://wordpress.org/plugins/official-facebook-pixel/) provides advanced integration for some other plugins that can help you with advertisement campaigns. If you see no use in that, and you just want a simple integration, then this extension provides a lightweight alternative--as is such with all our solutions.

### What about cookie consent?

There is no cookie-consent control in the Cord extension. However, there are ways to implement these [via filters](#developers/filters).

The extension does not create cookies itself. However, the third-party scripts Cord integrates, do. These cookies are:

| Option           | Cookie name | Purpose     | Privacy                                                            | Expiry     |
|:---------------- |:----------- |:----------- |:------------------------------------------------------------------ |:---------- |
| Google Analytics | `_ga`       | Analytics   | [View policy](https://support.google.com/analytics/answer/6004245) | 2 years    |
| Facebook Pixel   | `_fbp`      | Remarketing | [View policy](https://www.facebook.com/about/privacy)              | Session    |

Whether this information is useful to you depends on the laws you must (or should) follow. We're not lawyers, so we're not going to provide you a most annoying cookie consent banner. Integrate it yourself, and please make it small enough for us to ignore easily. Thank you.

## Developers

### Filters

Here you can find the available filters for Cord.

#### Disable scripts until cookie consent is granted

N.B. This does not work with page-caching plugins. When a page-caching plugin is used, this script may lead to sporadic disabling or enabling tracking for all visitors. Some caching plugins can provide different pages based on the cookies provided, however.

```php
add_action( 'init', function() {

	// This is an arbitrary example cookie.
	$consented = ! empty( $_COOKIE['_example_cookie_consent'] );

	if ( ! $consented ) {
		// No Cookie consent has been given. Disable tracking.
		add_filter( 'the_seo_framework_cord_ga_enabled', '__return_false' );
		add_filter( 'the_seo_framework_cord_fbp_enabled', '__return_false' );
	}
}, 9 );
```

## Changelog

### 1.1.0

[tsfep-release time="June 22nd, 2023"]

**Manual migration required:** Universal Analytics 3 is now Google Analytics 4. To migrate, please [follow these instructions](https://support.google.com/analytics/answer/10110290). To use your new Google Analytics property with Cord, you'll need to [obtain the Measurement ID](https://support.google.com/analytics/answer/12270356), and paste that ID into Extension Settings.

* **Added:** Google Analytics 4 (GA4) Measurement ID is now supported.
	* This is the most basic form; you cannot track special events via Cord, such as sale conversions. You can get those via the all-involving [Site Kit plugin](https://wordpress.org/plugins/google-site-kit/).
	* IP Anonymization is no longer optional; but it's enabled by default for all GA4 users by Google.
	* Enhanced Link Attribution is no longer available, this feature is no longer supported by Google.
	* Many more features are customizable now, but they're all controlled via your Google Analytics dashboard now.
* **Removed:** This extension will stop outputting Google Universal Analytics 3 (UA3) script on July 1st, 2023, at 7:00 AM GMT, which is when Google stops supporting UA3.
* **Removed:** You can no longer set up UA3 via this extension.
* **Note:** Resaving the Extension Settings will purge old Universal Analytics settings: IP Anonymization and Enhanced Link Attribution.
* **Note:** In the next update, we'll remove all residual checks and filters from UA3.

### 1.0.0

[tsfep-release time="December 18th, 2019"]

* Initial extension release.
