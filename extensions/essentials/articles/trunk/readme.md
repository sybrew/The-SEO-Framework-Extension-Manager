# Articles
Location: https://theseoframework.com/extensions/articles/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension enhances your published posts by automatically adding both AMP and non-AMP Structured Data.

## Overview

### Annotate your posts

With the Articles extension, your posts are understood better by search engines.

For news articles, it allows them to be displayed prominently within Google's search carousel.

For blog postings or investigative reports, it improves search presence for recurring visitors.

This is all achieved through Structured Data with Articles.

[tsfep-image id="1"]

### Structured Data

Structured Data helps search engines understand how your website and its content are set up.
It tells search engines what current page and website is generally or specifically about.

When the information is found and processed, this information can be used for the Search Engine Results Page (SERP).
So, search engines (Google in particular) use this information to annotate your page's information within their search results.

Potential visitors then know more about your pages before visiting them. When correctly set up, this will increase visitor engagement.

### How it works

The Articles extension works for all WordPress posts.
It also works for AMP posts, if the [tsfep-extension name="amp"] is enabled.

When a post is being displayed, this extension will fetch all data required from various SEO settings.
Then, if the data is evaluated as valid, it will be compressed into a Schema.org JSON-LD script.
This script will be outputted in the header, for search engines to consume.

When the script is found by search engines, it will be used for their search results.

## AMP supported

When the [tsfep-extension name="amp"] is active, this extension will replace default Articles output with an improved and tailored version.

The data outputted is the same as on the non-AMP pages. However, requires more fields to be present.
This is because Google wants to conform to a stricter standard for AMP pages.

Most prominently, an image and the publisher needs to be present. Read more on these fields at [usage](#usage).

## Usage

[tsfep-bundled]

### Activate Articles

All you'll need to do is activate the Articles extension.

There is no additional setup required.

On every post, you can set the Article Type under the Structure tab.

Every WordPress post, if the rendered data is valid, will have Articles data outputted in the header through a JSON-LD script.

[tsfep-image id="3"]

### Best practices

**For Articles to render correct data, keep the following in mind:**

1. Don't use HTML code in your post titles. The theme, through CSS, should render titles correctly.
2. Do set featured images for posts.
3. Don't forget to set up basic information at the global SEO Schema settings.
4. Don't forget to set up a Site Icon at the global SEO Schema settings or within Customizer.

### Automated output

The data used for output is fetched automatically from your post's structure.

**These fields are evaluated:**

* **Type:** The type of the article, either "Article", "Blog Posting" or "News Article".
* **Main Entity:** The article URL.
* **Headline:** The title, this defaults to the post title. It will fall back to the SEO title.
* **Images:** The images set for the article. This defaults to the post's SEO settings image, and will fall back to the "Featured Image".
* **Published date:** The date the article was published.
* **Modified date:** The date the article was last modified.
* **Author:** The article's author, set in the "Author" meta box. Defaults to the author's display name.
* **Publisher:** The organization, which defaults to the "Schema.org" settings within The SEO Framework.
* **Description:** The article's description, which is the same as the description output by The SEO Framework.

### Required fields

Most of the fields above are required before the Article type data is outputted.

In general, you shouldn't have to worry about the output with Articles.
The extension automatically checks whether the output is valid.

Google's [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool) might annotate the Articles data as invalid.
This is because there are two different requirement standards for AMP and non-AMP pages. The testing tool enforces the AMP standard.

#### Special attention for AMP

For valid AMP output, you need to make sure the following two fields are available.
These fields aren't required for non-AMP, but they are recommended.

* **Image:** This image needs to be set in the in-post SEO social settings.
	* Alternatively, you can use the Featured Image.
* **Publisher:** The website must represent an Organization and it must include a logo.
	* The logo is taken from **"SEO Settings -> Schema Settings -> Presence -> Website logo"**.
	* Alternatively, you can set a site icon in Customizer, this requires theme support.

### Types

Articles can be defined through various types.

Google supports generic Articles, Blog Postings and News Articles.

#### Articles (generic)

The general article type covers all types.

**These types are covered by General articles:**

* A piece of investigative report.
* A news articles.
* A blog posting.

So, News Articles and Blog Postings are covered by General Articles. But, they can also be defined more specifically.

#### News Articles

When the article information is annotated as news, and when your website is authorized as a news publisher, Google can display it in a featured carousel.

These articles mustn't be opinionated pieces, but they must display fact.

This carousel is time sensitize, which means that fresh news entries are much more likely to be displayed here.

[tsfep-image id="2"]

#### Blog Postings

Unlike News Articles, blog postings won't be displayed within carousels.

The blog entry can be based on either fact, or be an opinionated piece.

This information is mainly used to bind authors, organizations, and dates to the content. By doing so, you add relevance to your content.

This helps search engines tailor displayed search results for its users. So, you increase likelihood to get returning visitors, thus increasing engagement.

## Developers

### Filters

Here you can find the available filters for Articles.

#### Adjust default post meta

Specifically, the article type.

```php
add_filter( 'the_seo_framework_articles_default_meta', function( $meta = [] ) {

	// Change default 'type' setting from 'Article' to 'NewsArticle'
	$meta['type'] = 'NewsArticle';

	return $meta;
} );
```

## Changelog

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
* **Changed:** This extension is now free and out of testing phase.

### 1.0.1-gamma

[tsfep-release time="August 22nd, 2017"]

* **Changed:** Google states that [some output is ignored](https://developers.google.com/search/docs/data-types/articles), but that doesn't mean the output is overlooked. So:
	* Published date is now also outputted on non-AMP.
	* Modified date is now also outputted on non-AMP.
	* Post Author is now also outputted on non-AMP.
	* Publisher (Organization name) is now also outputted on non-AMP.
	* Description is now also outputted on non-AMP.
	* **Note:** The data may still be marked invalid by the [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool), although far less likely.
		* The data will always be checked for validity on both AMP and non-AMP, while adhering to Google's guidelines.
		* The data should never be marked invalid on the AMP version.
* **Improved:** The description is now taken from cache, rather than being regenerated.
	* This can yield a large beneficial performance effect when parsing large texts.

### 1.0.0-gamma

[tsfep-release time="February 17th, 2017"]

* Initial extension release.
