# Articles
Contributors:
Location: https://theseoframework.com/extensions/articles/
Tags: blogging, news
Requires at least: 4.4.0
Required PHP: 5.5.21 or 5.6.5
Tested up to: 4.9.0
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

There is no setup required and no options are currently available.

Every WordPress post, if the rendered data is valid, will have Articles data outputted in the header through a JSON-LD script.

[tsfep-image id="3"]

### Best practices

**For Articles to render correct data, keep the following in mind:**

1. Don't use HTML code in your post titles. The theme, through CSS, should render titles correctly.
2. Do set featured images for posts.
3. Don't forget to set up basic information within the SEO Schema Settings.
4. Don't forget to set up a Site Icon in Customizer if you wish to use AMP.

### Automated output

The data for output is fetched accordingly to your post's structure.

**These fields are evaluated:**

* **Type:** The type of the article, either "Article", "Blog Posting" or "News Article".
* **Main Entity:** The article URL.
* **Headline:** The title, this defaults to the post title. It will fall back to the SEO title.
* **Images:** The images set for the article. It defaults to the post's SEO settings image, and will fall back to the "Featured Image".
* **Published date:** The date the article was published.
* **Modified date:** The date the article was last modified.
* **Author:** The article's author, set in the "Author" metabox. Defaults to the author's display name.
* **Publisher:** The organization, defaults to the "Schema.org" settings within The SEO Framework.
* **Description:** The article's description, this is the same as the description output by The SEO Framework.

### Required data

Most data are required before the Article data is outputted, for completeness.

In general, you shouldn't have to worry about the output.
However, for valid AMP output, you need to make sure the following fields are filled in.

**These fields require special attention for AMP:**

* **Image:** This image needs to be set in the in-post SEO settings' metabox. Alternatively, you can use the Featured Image.
* **Publisher:** The website must represent an Organization, and it must include a logo, which is taken from "Customizer's Site Icon" (requires theme support).

### Types and data

Articles can be defined through various types.

Google supports General Articles, Blog Postings and News Articles.

**To annotate the data, the following is used:**

1. The type, e.g. "article", "blog posting", or "news article".
2. A headline, i.e. the real title.
3. The description.
4. The main image to be displayed with the article.
5. The freshness, i.e. the date published and modified.
6. The author, i.e. Schema.org Person.
7. The publisher, i.e. Schema.org Organization, including the logo.

#### General Articles

The general article type covers all types.

**These types are covered by General articles:**

* A piece of investigative report.
* A news articles.
* A blog posting.

So, News Articles and Blog Postings are covered by General Articles. But, they can also be defined more specifically.

*N.B. User control for Article (General), News Article and Blog Posting structures will be released soon. Currently, all output defaults to Article types.*

#### News Articles

When the article information is annotated as news, and when your website is authorized as a news publisher, Google can display it in a featured carousel.

These articles mustn't be opinionated pieces, but they must display fact.

This carousel is time sensitize, which means that fresh news entries are much more likely to be displayed here.

[tsfep-image id="2"]

#### Blog Postings

Unlike News Articles, blog postings won't be displayed within carousels.

The blog entry can be based on either fact, or be an opinionated piece.

This information is mainly used to bind authors, organizations and dates to the content. By doing so, you add relevance to your content.

This helps search engines tailor displayed search results for its users. So, you increase likelihood to get returning visitors, thus increasing engagement.

## Changelog

### 1.1.0

[tsfep-release time="-1"]

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
