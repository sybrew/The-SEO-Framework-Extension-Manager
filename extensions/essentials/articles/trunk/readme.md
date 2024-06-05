# Articles
Location: https://theseoframework.com/extensions/articles/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension automatically enhances your published posts by adding essential Structured Data.

## Overview

### Annotate your posts

With the Articles extension, your posts are understood better by search engines.

For news articles, Articles allows them to be displayed prominently within Google's search carousel.

For blog postings and investigational reports, Articles improves search presence for recurring visitors.

All this is achieved via structured data with Articles, without a hassle.

[tsfep-image id="1"]

### Structured data

Structured data helps search engines understand how your website and your content are set up. It removes the guesswork they usually conduct when parsing a plain webpage.

When the structured data is found and processed, search engines can annotate your page's information within their search results. This helps your pages stand out, and it can significantly increase their exposure.

### How it works

The Articles extension works for all your public WordPress pages. It also works for AMP posts when the [tsfep-extension name="amp"] is enabled.

Articles automatically fetches all inferred information from your pages and outputs the structured data in a hidden machine-readable script on your page. When search engines find this script, they'll process it.

### It comes with a Google News Sitemap

Get your news articles indexed ASAP with the Google News sitemap brought by Articles. To get started, visit the [Google News Publishing Center](https://publishercenter.google.com/publications).

This special sitemap is small, populates automatically, its location is added to the `/robots.txt` output, and Google crawls it periodically. Be sure to [submit it to Google Search Console](https://support.google.com/webmasters/answer/7451001).

## Usage

[tsfep-bundled]

### Activate Articles

First, you'll need to activate the Articles extension.

### Extension settings

Underneath the extension description, you should see a settings-link appear. You can also find this link under "SEO" in the admin sidebar, but you may need to refresh the page first.

On the Extension Settings page, you can assign which post types should support the Articles markup, and what the default Articles type should be.

Moreover, you can enable the Google News sitemap and select a publisher logo. Restrictions apply. For more information, see the [Articles FAQ](#faq).

### Post settings

On every supported post type edit-screen, you can set the Article Type under the Structure tab.

[tsfep-image id="3"]

### Best practices

**For Articles to render correct data, keep the following in mind:**

1. Don't use HTML code in your post titles. The theme, through CSS, should render titles correctly.
2. Do set a featured image. Alternatively, set a social image via the SEO settings for the page.
3. Don't forget to set up the necessary information at the global SEO Schema settings.
4. Don't forget to set up a logo via the Extension Settings.

### Automated output

The data used for Articles' output is fetched automatically from your post's structure.

**These fields are evaluated:**

* **Type:** The type of the article, either "Article," "Blog Posting," or "News Article."
* **Main Entity:** The article URL.
* **Headline:** The title, this defaults to the post title. It may be trimmed to 110 characters when it exceeds that limit.
* **Images:** The images set for the article. It defaults to the post's SEO settings image, and can fall back to the "Featured Image."
* **Published date:** The date the article was published.
* **Modified date:** The date the article was last modified.
* **Author:** The article's author, set in the "Author" meta box. This defaults to the author's display name.
* **Publisher:** When applicable, the organization. This defaults to the "Schema.org" settings from The SEO Framework.
* **Description:** The article's description, which is the same as the description output by The SEO Framework.

#### Testing the structured data

In general, you shouldn't have to worry about the output with Articles.
The extension automatically checks whether the output is valid.

Google's [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool) might annotate the Articles data as invalid. This is because there are two different requirement standards for AMP and non-AMP pages. The testing tool enforces the AMP standard.

#### Special attention for AMP

When the [tsfep-extension name="amp"] is active, this extension will replace default Articles output with an improved and tailored version.

For the correct AMP output, you need to make sure the post contains an image, and the site must represent an organization with a defined logo.

### Types

Articles can be defined through various types.

Google supports generic Articles, Blog Postings, and News Articles.

#### Articles (generic)

The generic article type covers all types.

**These types are covered by generic articles:**

* A piece of an investigative report.
* News articles.
* Blog posting.

So, generic Articles cover News Articles and Blog Postings. But you might want to be more specific.

#### News articles

_This type is unavailable when your website represent a person, instead of an organization._

When you annotate a page as a News Article, and when your website is verified as a news publisher, Google can display it in a featured carousel. This carousel is time-sensitive, which means that recent news entries are much more likely to be displayed here. These articles mustn't be opinionated pieces, but they must display fact and adhere by [Google News' content policies](https://support.google.com/news/publisher-center/answer/6204050).

[tsfep-image id="2"]

#### Blog postings

Unlike News Articles, Blog Postings won't be displayed within carousels. The blog entry can be based on either fact or be an opinionated piece.

This article type is mainly used to bind authors, organizations, and dates to the content. This adds recognition to your content, which helps search engines tailor the search results for their users. So, you increase the likelihood to get returning visitors, thus increasing engagement.

## FAQ

### Which type is best for my posts?

Please consult the [Articles types reference](#usage/types).

### I don't see all settings.

Some settings are hidden when your website represents a person, instead of an organization. You can adjust this via The SEO Framework's Schema.org settings, under "Presence."

### Where can I find the Google News sitemap?

When enabled, you can find your news sitemap at `/sitemap-news.xml`. For example: `https://example.com/sitemap-news.xml`. If that endpoint doesn't work, try `https://example.com/news-sitemap.xml`.
Please note that your site must be verified with Google News before you can benefit from the sitemap. You can get started at the [Google News Publishing Center](https://publishercenter.google.com/publications).

This feature uses The SEO Framework's sitemap loader; therefore, **The SEO Framework's optimized sitemap must be enabled.**

### Where do I submit a Google News sitemap?

You should submit your Google News sitemap via Google Search Console. For more information, see Google's publisher documentation on [Google News Sitemaps](https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap).

### The Google News sitemap is empty!

The Google News sitemap will only be populated with Articles assigned the "News Article" Article Type. You can adjust the Article Type on a per-post basis for supported post types under "Structure."

[Google News sitemaps documentation](https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap) states that articles older than two days should be removed. Articles extension uses a grace period of 2.5 days.

If you have not published a News Article in the past two and a half days, the sitemap will be empty.

### Does Bing support the Google News sitemap?

No. Please visit the [Bing PubHub Guidelines for Publishers](https://www.bing.com/webmasters/help/pubhub-publisher-guidelines-32ce5239) for more information.

## Developers

### Filters

Here you can find the available filters for Articles.

#### Adjust the output data

```php
add_filter( 'the_seo_framework_articles_data', function( $data ) {

	// Overwrite the author input.
	$data['author'] = [
		'@type' => 'Person',
		'name'  => 'J. Doe',
		'url'   => 'https://facebook.com/profile.php?id=2147483647',
	];

	// Remove the description input.
	unset( $data['description'] );

	/**
	 * Setup paywalled content.
	 * Please contact your subscription/paywall plugin provider if you find issues.
	 * See: https://developers.google.com/search/docs/advanced/structured-data/paywalled-content
	 */
	$data['isAccessibleForFree'] = 'False';
	$data['hasPart']             = [
		[
			'@type'               => 'WebPageElement',
			'isAccessibleForFree' => 'False',
			'cssSelector'         => '.section1',
		],
		[
			'@type'               => 'WebPageElement',
			'isAccessibleForFree' => 'False',
			'cssSelector'         => '.section2',
		]
	];

	return $data;
} );
```

#### Adjust the image data

*Note that images are required for AMP.*

```php
add_filter( 'the_seo_framework_articles_images', function( $images ) {

	// Add an image. Make sure it's an array first!
	$images   = (array) $images;
	$images[] = 'https://example.com/path/to/image.jpg';

	// Or, only use a single image.
	$images = 'https://example.com/path/to/image.jpg';

	// Or, define an array of images.
	$images = [
		[
			'@type'  => 'ImageObject',
			'url'    => 'https://example.com/path/to/complex-image.jpg',
			'width'  => 1920,
			'height' => 1080,
		],
		'https://example.com/path/to/image.jpg',
	];

	return $images;
} );
```

### Adjust the sitemap generation arguments

*The `$args` parameter is an array that should be compatible with `WP_Query`. [View all parameters properties](https://developer.wordpress.org/reference/classes/wp_query/#parameters).*

```php
add_filter( 'the_seo_framework_sitemap_articles_news_sitemap_query_args', function( $args ) {

	// Remove the date query, forcing all posts to be considered, instead of just from the past 2.5 days.
	unset( $args['date_query'] );

	// Allow inclusion of password-protected posts.
	$args['has_password'] = true;

	return $args;
} );
```

## Changelog

### 2.3.1

[tsfep-release time="-1"]

* **Improved:** The article time notation now honors TSF's timestamp settings. This requires TSF v5.0 or later.
* **Improved:** Removed redundant output validity checks; in turn, this improves performance.
* **Removed:** Pinging of the Google News sitemap is no longer supported. They now crawl your sitemap periodically.
	* Google can find your News Sitemap's location via your `/robots.txt` file. But, to be certain they catch it, also [submit the sitemap to Google Search Console](https://support.google.com/webmasters/answer/7451001).
	* To learn more, see https://developers.google.com/search/blog/2023/06/sitemaps-lastmod-ping.
* **Fixed:** Resolved outstanding deprecation notices with TSF v5.0+ when accessing the Google News sitemap.

### 2.3.0

[tsfep-release time="November 2nd, 2023"]

* **Changed:** The `mainEntityOfPage` is now the canonical URL (user adjustable), instead of the permalink (default URL).
* **Removed:** The following filters have been removed; use the options instead:
	* `the_seo_framework_articles_supported_post_types`.
	* `the_seo_framework_articles_default_meta`.
* **Note:** The structured data generated by this plugin is still compatible with the new structured data of The SEO Framework v5.0.0. Once we drop support for older TSF versions, we'll integrate Articles into the new Schema.org graph.

### 2.2.1

[tsfep-release time="October 4th, 2022"]

* **Improved:** Modernized code with a focus on improving performance.

### 2.2.0

[tsfep-release time="May 2nd, 2022"]

* **Added:**
	* The Article markup author object now has a URL provided, as suggested by Google. This URL points to the author archive page of the website.
	* A compatible endpoint for the Google News sitemap supporting misconfigured NGINX profiles has been added (`/news-sitemap.xml`).
		* Namecheap, EasyWP, SpinupWP, etc. blindly implemented the broken NGINX script from Yoast SEO; this change makes Articles compatible with witless hosting providers.
* **Improved:**
	* A News Sitemap URL has been added to the Extensions Settings page.
		* This is only visible after the sitemap has been enabled and the settings page refreshed.

### 2.1.1

[tsfep-release time="November 8th, 2021"]

* **Improved:**
	* This extension now uses a coroutine to generate the Schema.org markup; now it can stop processing at any point the data is not valid.
* **Changed:**
	* Now relies on WordPress's timesystem, instead of The SEO Framework's.
* **Updated:**
	* This extension now is fully compatible with The SEO Framework v4.2.0.

### 2.1.0

[tsfep-release time="February 9th, 2021"]

* **Added:** You can now select a default 'disabled' Articles type for a post type. With that, you can enable support for Articles on a per-post basis.
* **Added:** You can now select a 'disabled' Articles type on a per-post basis via the drop-down selection in the post-edit screen. With that, you can disable support for Articles on a per-post basis.
* **Added:** You can now select an Articles type via the quick-and bulk-edit interface.
* **Improved:** The Articles headline attribute is now generated faster.
* **Changed:** The post type support label has changed from 'Enabled' to 'Supported', to avert confusion with the new 'disabled' article type selection.
* **Changed:** The Google News sitemap now only shows posts published in the last two days (plus a half day, for some rounding leeway). This is according to [Google's Publishing guidelines](https://support.google.com/news/publisher-center/answer/9606710).
	* The sitemap query can be modified; this is documented at [developers](#developers).
* **Fixed:** Addressed an issue where after interacting with the quick-edit or bulk-edit UI, the post state declaration would disappear.
* **Other:** We received multiple requests regarding the output of the Google News sitemap in combination with WordPress's new native sitemap: This combination is not possible. The Google News sitemap brought by Articles relies completely on The SEO Framework's sitemap API. To learn more, please see [The SEO Framework v4.0.0 changelog](https://theseoframework.com/?p=3268), under header "For developers: About the sitemap". We put thousands of hours perfecting that sitemap, and we're deeply saddened to learn that SEOs all over the world are still proselytising outdated tactics.

### 2.0.4

[tsfep-release time="June 2nd, 2020"]

* **Added:** Support for The SEO Framework's advanced query protection has been added, so that the output won't be erroneously shown.
* **Changed:** This extension now requires TSF v4.0.5 or higher.
* **Fixed:** The correct post type is fetched for evaluation on "singular posts as archives", like the shop and blog pages.

### 2.0.3

[tsfep-release time="May 15th, 2020"]

* **Fixed:** The post state declaration of the Article type now fetches the post data of each post correctly.
* **Fixed:** Added missing translation for the post type settings header.
* **Fixed:** Strict XML entities in URL queries are now escaped in the Google News sitemap `<loc>` and `<image:loc>` tags, so you won't face an "invalid" document with certain CDNs.
	* We could not confirm whether Google sees the use of these entities as invalid prior this update.
	* This was not a security issue--the XML document is dormant, and XML entity generation (via `<` and `>`) was already properly mitigated (inferred from HTML escaping).

### 2.0.2

[tsfep-release time="December 18th, 2019"]

* **Fixed:** The filter deprecation notice now fires as intended when only the `the_seo_framework_articles_supported_post_types` filter is used on the site.
	* This was an oversight as we flipped a bit for testing purposes.
* **Other:** The Schema.org JSON script is no longer minified when WordPress script debugging is enabled.

### 2.0.1

[tsfep-release time="November 21st, 2019"]

* **Improved:** Images smaller than 1200px are now allowed on non-AMP pages, for as low as 696px.
* **Changed:** This extension now enforces UTC time to work around a permalink bug in WordPress 5.3.

### 2.0.0

[tsfep-release time="November 5th, 2019"]

* **Added:** A brand-new default-options panel, which is integrated into the new global extension-options page.
	* You can set the supported post types and their default article types, as well.
	* You can enable a Google News sitemap.
	* You can set a custom publisher logo.
* **Added:** A Google News sitemap. When you mark pages as `NewsArticle`, they'll be included.
	* The query limit from The SEO Framework applies.
	* At most, 1000 news articles are included in the sitemap.
* **Added:** Sites that represent a `Person` are now supported for Articles markup.
* **Added:** A Post State in the post overview, next to each post title, now hints the Articles type.
* **Improved:** Articles now intelligently trims titles, instead of invalidating the output when a title is over 110 characters.
* **Changed:** This extension now requires TSF v4.0.2 or higher.
* **Info:** Some post-meta and extension settings are hidden when the website represents a `Person`, instead of an `Organization`.
* **Deprecated:**
	* These filters have been converted to the new options, somewhat gracefully. You should remove them from your site if you have it installed. Use the new options, instead.
		* `the_seo_framework_articles_supported_post_types`.
		* `the_seo_framework_articles_default_meta`.

### 1.4.0

[tsfep-release time="August 20th, 2019"]

* **Added:** TSF v4.0 support.
* **Added:** New filter: `the_seo_framework_articles_supported_post_types`.
	* Documented at [developers](#developers).
* **Added:** New filter: `the_seo_framework_articles_data`.
	* Documented at [developers](#developers).
* **Added:** New filter: `the_seo_framework_articles_images`.
	* Documented at [developers](#developers).
* **Added:** Multiple valid images are now used.
* **Changed:** Image width requirements went up to 1200 from 696 pixels.
* **Changed:** This extension now requires TSF v3.1 or later.
* **Fixed:** Structured data is no longer appended to archives that are of the `post` post type.
* **Fixed:** Now uses AMP v0.5+ endpoint detection when available.

### 1.3.1

[tsfep-release time="November 9th, 2018"]

* **Changed:** This is now an Essentials extension.

### 1.3.0

[tsfep-release time="August 28th, 2018"]

* **Added:** TSF v3.1 support.
* **Fixed:** A PHP notice no longer occurs when the registered article image is missing.
* **Other:** The correct version of this extension is now displayed in the dashboard.

### 1.2.0

[tsfep-release time="March 31st, 2018"]

* **Added:** Article Type selection is now available on every post.
	* You can select **"Article"**, **"NewsArticle"** and **"BlogPosting"**.
* **Added:** New filter: `the_seo_framework_articles_default_meta`.
	* Documented at [developers](#developers).

### 1.1.0

[tsfep-release time="November 10th, 2017"]

* **Added:** TSF 3.0 URL compatibility.
* **Added:** http://schema.org/publisher output now uses the new TSF 3.0 logo. It is resized correctly on-the-fly for AMP, once.
* **Changed:** This extension is now free and out of the testing phase.

### 1.0.1-gamma

[tsfep-release time="August 22nd, 2017"]

* **Changed:** Google states that [some output is ignored](https://developers.google.com/search/docs/advanced/structured-data/article), but that doesn't mean the data is overlooked. So:
	* Published date is now also outputted on non-AMP.
	* Modified date is now also outputted on non-AMP.
	* Post Author is now also outputted on non-AMP.
	* Publisher (Organization name) is now also outputted on non-AMP.
	* Description is now also outputted on non-AMP.
	* **Note:** The data may still be marked invalid by the [Structured Data Testing Tool](https://developers.google.com/search/docs/advanced/structured-data), although far less likely.
		* The data will always be checked for validity on both AMP and non-AMP while adhering to Google's guidelines.
		* The data should never be marked invalid on the AMP version.
* **Improved:** The description is now taken from the cache, rather than being regenerated.
	* This can yield a sizeable beneficial performance effect when parsing large texts.

### 1.0.0-gamma

[tsfep-release time="February 17th, 2017"]

* Initial extension release.
