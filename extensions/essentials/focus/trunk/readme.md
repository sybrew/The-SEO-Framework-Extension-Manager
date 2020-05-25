# Focus
Location: https://theseoframework.com/extensions/focus/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension guides you through the process of writing targeted content that ranks with focus keywords, their inflections, and their synonyms.

## Overview

### Focus your content

With over 1 billion competing websites, your pages barely stand a chance ranking on the first page of Google. So, writing quality content that ranks well in search engines isn't easy. This is where Focus comes in.

This extension helps you to focus your content to a few well-targeted search phrases, significantly increasing your chances to rank on the first page.

### Your personal guide

After defining the subject of the page you're writing, Focus rates your page on various SEO principles. These principles include subject density, linking, and more.
They help you to see how well you've increased your chances to be ranked higher.

[tsfep-image id="1"]

### More topics, more keywords

When your page covers more topics, you should utilize more keywords.
With Focus, you can use three keywords that form three subjects.

Each keyword makes up for a new subject. Focus rates each subject separately.

### It comes with a dictionary

There are many ways to write in a language. With a Premium subscription, you can embrace all Focus has to offer.

So, after you fill in a keyword, Focus connects to our API services and provides you with related synonyms and inflections.
With those, Focus can rate your content more accurately.

The synonyms provided make up for great writing suggestions. Utilizing those synonyms increases your chances to be found, too.

[tsfep-image id="2"]

## Usage

[tsfep-bundled]

### Activate Focus

First, you'll need to activate Focus via the Extension Manager's interface.

### Using Focus

On every page with SEO capabilities, you'll find the Audit menu.

Under the audit menu, you can configure Focus for each page.

#### Start getting rated

After you fill in a keyword, Focus starts rating your content.

#### Select lexical form

***(Dictionary API support required)***

If your keyword matches an entry in our dictionary, you can select a lexical form.

You should select the form that describes your subject most. The lexical forms are sorted automatically from frequent to infrequent usage.
So, choosing the first form is often correct.

[tsfep-image id="4"]

#### Select homonymous example

***(Dictionary API (synonym) support required)***

**Skip this step when no synonyms are available.**

Some words have more than one meaning. So, after selecting the lexical form, example sentences are formed.
These sentences are also sorted in order of the most common form, and the most common phrase is automatically selected.

Change the example sentence that closely describes your subject, and you could get different synonyms.

[tsfep-image id="5"]

#### Select active inflections and synonyms

***(Dictionary API (inflection or synonym) support required)***

**Skip this step when no synonyms or inflections are available.**

After all synonyms or inflections are received, click on the ones you'd like to use.

**When synonyms are available:**
If you haven't used a synonym in your content yet, consider using it as it will increase your chances of being found.

[tsfep-image id="6"]

### Ratings

The ratings tell you what should be improved in your content.

You should never ruin your content just to get a better rating. So, use these ratings as guidelines for focused content.

#### Meta vs Page Title

The page title is what's displayed to the user on your website.
You can change this at the top of your content editor.

The meta title is what's displayed to a potential visitor on Google.
You can change this under the General tab of the page's SEO settings.

#### Introduction

The introduction is often the first few paragraphs of text found in your content editor.
In writing, the first few paragraphs should be used to annotate what your content is about.

It's the best place to use your focus keyword, so users know they've landed on the right page.

A good introduction drastically improves user retention, which indirectly signals that the content is of high quality to search engines.

#### Subject Density

Annotating keywords, inflections, and synonyms more throughout your content is a signal for search engines what your content is about.

When your subject density is too high, your page might seem like spam.

#### Linking

Linking to other pages related to a subject is a powerful signal to search engines which helps them distinguish homonyms.
So, consider adding one or two links related to the subject.

The Focus extension will detect a link when a word is found in the URL, content, or title of an `<a>` tag.

#### Meta description

Meta descriptions don't help in ranking. However, they do help potential visitors to know if the content they're looking for is on your page.

If the subject isn't found in the meta description, Google often tries to fill it in with an (often confusing) sentence from your content.
So, to prevent this from happening, Focus notifies you via this rating.

#### Page URL

One of the best ways to be found on a keyword is by placing it in the page URL. So, be sure to include it here.

You can change your page URL in the sidebar under "Document > Permalink." On some WordPress setups you can find it above the content editor instead.

## FAQ

### Which languages are supported?

All languages have the requisite support for ratings.

### Which languages are supported by the dictionary API?

The following languages are supported by the dictionary API for inflections:
* English (US)
* English (GB)
* Spanish (Español) (ES)
* Latvian (Latviešu)
* Hindi (हिन्दी)
* Swasili (Kiswahili)
* Tamil (தமிழ்)
* Romanian (Română) (RO)

Non-US English dialects default to English (GB). For all other languages, if a dialect exists and is used, it'll default to the one listed above.

The following languages are supported by the dictionary API for synonyms:
* English (global)

Support for other languages will be added over time. Processing a living language is difficult and time-consuming, so that can take a few years.

### What if my site's language doesn't support synonym lookup?

That's OK, neither does Google for your language then. We use the same dictionary API they use to process natural language. Therefore, using synonyms won't be a benefit in your case.

### Why are there only one or two inflections available?

We're still waiting for our API partner to provide reverse inflection lookups. When it's ready, an update will be sent out.

### "A parsing failure occurred." What does this mean?

When the content parser experiences any error, the rater shows this generic message. Most likely, your computer may be restraint in processing power in combination with page builders. This issue resolves automatically, as the parser reassesses its data every 45 seconds, or whenever the related content is updated.

### Are page builders supported?
Focus supports most page builders. However, page builders that rely on shortcodes may give incorrect assessments for the "introduction" and "subject density" ratings.
This is because shortcodes are parsed as readable content, instead of HMTL constructors; the parser is yet unable to discern the shortcode's behavior without context.

These page builders may be affected by this issue:

- Divi Builder
- WPBakery Page Builder (Visual Composer)

These builders work as intended:

- Beaver Builder
- Elementor
- Page Builder by SiteOrigin
- Gutenberg
- WordPress 5.0+ Block Editor

We're working on an update to improve accuracy for the affected page builders.

### (Legacy) What does "Essentials+" stand for?

The Focus extension requires an Essentials (legacy) subscription to use. The plus indicates that there's additional API support for our Premium (legacy, current) subscribers. In the case of Focus, there's added dictionary API support for lexical lookup.

## Developers

### Filters

Here you can find the available filters for Focus.

#### Adjust supported focus elements

```php
add_filter( 'the_seo_framework_focus_elements', function( $elements ) {

	// Add an overriding (dominating) check for pageTitle.
	$elements['pageTitle'] = [ '#my-element > input' => 'dominate' ];

	// Add an extra (appending) pageContent for parsing.
	$elements['pageContent'] = [ '#my-element > input' => 'append' ];

	return $elements;
} );
```

## Changelog

### 1.4.0

[tsfep-release time="-1"]

* **Added:** Finally, we're introducing reverse inflection lookup, which makes this the most accurate subject parser.
	* **Supported languages:** English (US), English (GB), Spanish (Español) (ES), Latvian (Latviešu), Hindi (हिन्दी), Swasili (Kiswahili), Tamil (தமிழ்), Romanian (Română) (RO).
	* Non-US or GB English language types default to English (GB).
	* Non-ES Spanish language types default to Spanish (ES).
	* **API request change:** When the word you enter is found in the dictionary, extra API requests will be consumed to fetch its inflections for each homonymous example.
	* **Note:** Dictionary data stored before this update used on your pages isn't retroactively filled with inflections.
		 * To parse old content with the new inflection lookup, you must first clear your old keyword, and then refill your selections.
* **Fixed:** When a synonym matches an inflection, the synonym is now stripped.
	* This works retroactively, so your old content parser is affected.

### 1.3.2

[tsfep-release time="December 18th, 2019"]

* **Improved:** The lexical selector is now hidden when the current language isn't supported for the API.
* **Fixed:** The spacing of the keyword inputs is now less likely to misalign when the words differ in length.

### 1.3.1

[tsfep-release time="November 5th, 2019"]

* **Improved:** Addressed Gutenberg 6.4 (WP 5.3) changes (by fixing `wp.data.select( 'core/editor' ).isTyping` deprecation).
* **Updated:** Removed backward compatibility checks. The extension now requires TSF v4.0 and later.
* **API changes:**
	* *June 1st, 2019*: The API has been updated to be more performant and secure. Although unlikely, you may see different dictionary results henceforth.

### 1.3.0

[tsfep-release time="January 28th, 2019"]

* **Added:** The rater now spawns a new processing thread to calculate scores asynchronously.
	* This means you can write more content uninterrupted as the parser is calculating its scores.
	* This also means that the thread isn't interfering with tedious and heavy content painting, as such, the performance increased tremendously.
* **Added:** The plugin now tests for and informs on parsing failures.
	* **Note:** A failure fires automatically if the rater is stuck for longer than 30 seconds; this allows your computer to allocate a better core affinity when reattempting. For more information, see the [FAQ](#faq).
* **Improved:** General parsing performance by refactoring HTML tag exclusions, you'll now get results up to ten times quicker, relatively.
* **Improved:** The JS version requirements are upgraded, and as such, all known Unicode punctuation can be excluded correctly.
* **Improved:** The subject density rater is now more accurate, as it removes more redundant information than before.
* **Fixed:** Purposeless keywords are no longer (incorrectly) parsed, like an emoji or a dot.
* **Fixed:** The rater is no longer affected by race conditions, which might cause it to display old information.

### 1.2.0

[tsfep-release time="December 4th, 2018"]

* **Added:** The new WordPress 5.0 editor (Gutenberg) support. However, for this to function, you must update to The SEO Framework 3.2.0 or greater.
* **Changed:** The first paragraph lookup's logic is vastly improved in both performance and accuracy.
	* With this, it's now dubbed "introduction".
	* It can now grab more than just the first paragraph when the parser deems the content useless.
* **Improved:** General parsing performance by simplifying HTML tag exclusions.
* **Fixed:** On the classic editor, the old slug is no longer considered for page URL ratings.
* **Fixed:** On the classic editor, updating the slug more than once will no longer block assessing of it.

### 1.1.1

[tsfep-release time="November 9th, 2018"]

* **Improved:** When no homonymous example can be found, the option now displays "No example available.".
* **Changed:** This is now an Essentials+ extension.

### 1.1.0

[tsfep-release time="August 28th, 2018"]

* **Added:** TSF v3.1 support.
* **Improved:** Links are now matched at least 900 times quicker and more accurately; so, there's no more notable lag when editing large texts.
* **Fixed:** When using TSF v3.1, interacting with this extensions' UI elements won't trigger an "unsaved changes" warning when leaving the post-edit screen.
* **Fixed:** Empty or unmatched lexical form selectors are no longer cleared, or auto-activated on save.

### 1.0.0

[tsfep-release time="March 31st, 2018"]

* Initial extension release.
