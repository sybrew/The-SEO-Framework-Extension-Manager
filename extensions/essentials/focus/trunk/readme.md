# Focus
Location: https://theseoframework.com/extensions/focus/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension guides you through the process of writing targeted content that ranks with focus keywords, their inflections, and their synonyms.

## Overview

### Focus your content

With over 1 billion competing websites, your pages barely stand a chance ranking on the first page of Google. So, writing quality content that ranks well in search engines isn't easy. This is where Focus comes in.

This extension helps you to focus your content on a few well-targeted search phrases, significantly increasing your chances of ranking on the first page.

### Your personal guide

After defining the subject of the page you're writing, Focus rates your page on various SEO principles. These principles include subject density, linking, and more.
They help you to see how well you've increased your chances of being ranked higher.

[tsfep-image id="1"]

### More topics, more keywords

When your page covers more topics, you should utilize more keywords.
With Focus, you can use three keywords that form three subjects.

Each keyword makes up for a new subject--Focus rates each subject separately.

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
If you haven't used a synonym in your content yet, consider using it to increase your chances of being found on search engines.

[tsfep-image id="6"]

### Ratings

The ratings tell what you can improve in your content.

You should never ruin your content just to get a better rating. So, use these ratings as guidelines for focused content.

#### Meta vs Page Title

The page title is what's displayed to the user on your website.
You can change this at the top of your content editor.

The meta title is what's displayed to a potential visitor on Google.
You can change this under the General tab of the page's SEO settings.

#### Introduction

The introduction is often the first few paragraphs of text found in your content editor.
In most writing, you should use the first few paragraphs to annotate the subject of your content.

It's the best place to use your focus keyword, so users know they've landed on the right page.

A good introduction drastically improves user retention, which indirectly signals that the content is of high quality to search engines.

#### Subject Density

When you annotate keywords, inflections, and synonyms more throughout your content, you'll prominently signal search engines the subject of your content.

However, when your subject density is too high, your page might seem like spam. So, you should avoid that.

#### Linking

When you link to other pages related to your content, you help search engines "understand" the subject via relational connections. With that, they can distinguish homonyms better, like "apple" (fruit) from "Apple" (brand).
So, consider adding one or two links related to the subject.

The Focus parser will detect a link when a word is found in the URL, content, or title of an `<a>` (hyperlink) tag.

#### Meta description

Meta descriptions don't help with ranking. However, they do help potential visitors to know if the content they're looking for is on your page.

Moreover, if the subject isn't found in the meta description, Google often tries to fill it in with an (often confusing) sentence from your content.
So, to prevent this from happening, Focus notifies you via this rating.

#### Page URL

One of the best ways to be found on a keyword is by placing it in the page URL. So, be sure to include it here.

You can change your page URL in the sidebar under "Document > Permalink." On some WordPress setups, you can find it above the content editor instead.

## FAQ

### Are phrases supported?

We can't stress this enough: You should not use the keyword-entry field as a keyphrase-entry field. Phrases should flow naturally from keywords.

The parser looks for exact-matches only--when you provide a phrase, you'll likely face an incorrect analysis; so, we recommend sticking to simple or compound words only.

Compound words are made up of simple words; yet, they yield a new meaning. You can safely use compound words as a keyword.

Examples of simple words are (recommended):

- Flower
- Sun
- Care
- Child
- Law
- Son

Examples of compound words are (recommended):

- Sunflower
- Child care
- Son-in-law

Examples of phrases are (do not use):

- Improve growth of flowers -- Instead, use three separated keywords: "improve", "grow", "flower"
- How to increase your child care's business -- Instead, use two separated keywords: "business", "child care"
- Best gift for son-in-law -- Instead, use two separated keywords: "gift", "son-in-law"

### Which languages are supported?

Focus supports all languages. We worked tirelessly on making this possible.

### Which languages are supported by the dictionary API?

The dictionary API supports the following languages for **inflections**:

* English (US)
* English (GB)
* Spanish (Español) (ES)
* Latvian (Latviešu)
* Hindi (हिन्दी)
* Swahili (Kiswahili)
* Tamil (தமிழ்)
* Romanian (Română) (RO)

Non-US English dialects default to English (GB). For all other listed languages, if a dialect exists and is used, it'll default to its parent listed above.

The dictionary API supports the following languages for **synonyms**:

* English (global)

Support for other languages will be added over time. Processing a living language is difficult and time-consuming, so that can take a few years.

### What if my site's language doesn't support synonym or inflection lookup?

That's OK, neither does Google for your language then. We use the same dictionary API they use to process natural language. Therefore, using synonyms or inflections in an unsupported language won't bring a benefit.

In this case, consider throwing an inflection into your content. Even though the parser won't always recognize that inflection, it'll increase the chances of your content being found on the search-engine-result-pages.

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

#### Adjust auto-parsing interval

_Note: When you set this value lower than 5000, the auto-parser will be disabled._

```php
add_filter( 'the_seo_framework_focus_auto_interval', function( $interval ) {
	return 10000; // Set to 10 seconds. Default is 45000 (ms).
} );
```

Considering increasing the value when parser loaders spin indefinitely.

### JavaScript events

Here you can find an example of the available JavaScript event listeners for Focus.

#### Adjust content parser

```js
/**
 * Listen for store setup. Runs once early during the page load-sequence.
 *
 * Details are documented at `tsfem_e_focus_inpost.blockEditorStore()`.
 */
document.addEventListener( 'tsfem-focus-gutenberg-content-store-setup', () => {
	const contentStore = tsfem_e_focus_inpost.blockEditorStore( 'content' );

	/**
	 * This callback function must run synchronously.
	 *
	 * If you need to fill the store via a slow method, such as via a REST request,
	 * then we recommend memoizing the content in another function asynchronously, and
	 * fill its memoized content both in that memoizing function as well as here. In the
	 * memoizing function, you can reparse the content via `contentStore.triggerAnalysis()`.
	 * With `tsfem_e_focus_inpost.setAllRatersOf( 'pageContent', 'loading' )`, you can
	 * convey the content is being retrieved. Focus will automatically update the rater.
	 */
	document.addEventListener( 'tsfem-focus-gutenberg-content-store-fill', event => {
		// Mutate store with any content:
		contentStore.fill( 'my custom content' );

		// Read Focus's original store:
		console.log( event.detail.data );

		// Read active store:
		console.log( contentStore.read() );
	} );
} );
```

## Changelog

### 1.5.0

[tsfep-release time="-1"]

* **Added:** Filter `the_seo_framework_focus_auto_interval`.
	* Documented at [developers](#developers).
* **Added:** (JavaScript) `tsfem_e_focus_inpost.setAllRatersOf()` is now public.
* **Added:** (JavaScript) `tsfem_e_focus_inpost.blockEditorStore()` is now available. It returns various methods:
	* `getId`
	* `getElement`
	* `setup`
	* `create`
	* `empty`
	* `fill`
	* `read`
	* `triggerAnalysis`
	* Tip: Wrap a bind to the `.-store-setup` event documented below to assure the store is available.
	* Glossed over at [developers](#developers).
* **Added:** (JavaScript) Various synchronous events related to the Block Editor's storage for content assessment parsing, where `$type` is the type of storage:
	* `tsfem-focus-gutenberg-${type}-store-setup`
	* `tsfem-focus-gutenberg-${type}-store-create`
	* `tsfem-focus-gutenberg-${type}-store-empty`
	* `tsfem-focus-gutenberg-${type}-store-fill`
	* `tsfem-focus-gutenberg-${type}-store-trigger-read`
	* Tip: Via `tsfem_e_focus_inpost.blockEditorStore()` you can mutate the storage content before it's read.
	* Awesome: You don't have to worry about infinite loops; if you call any event, the extension prevents invoking subsequent events until the event is resolved synchronously.
	* Laconicly documented at [developers](#developers). These API features are for extreme edge-cases. Hire a developer at [our partners at Codable](https://codeable.io/partners/the-seo-framework/).
* **Improved:** The primary subject's analyzer is now opened if you load the editor when a keyword is stored for it.
* **Fixed:** Addressed various race condition issues with the upcoming jQuery 3.5.1.

### 1.4.0

[tsfep-release time="June 2nd, 2020"]

* **Added:** Finally, we're introducing reverse inflection lookup, which makes this the most accurate subject parser.
	* **Supported languages:** English (US), English (GB), Spanish (Español) (ES), Latvian (Latviešu), Hindi (हिन्दी), Swahili (Kiswahili), Tamil (தமிழ்), Romanian (Română) (RO).
	* Non-US or GB English language types default to English (GB).
	* Non-ES Spanish language types default to Spanish (ES).
	* **API request change:** When the word you enter is found in the dictionary, extra API requests will be consumed to fetch its inflections for each homonymous example.
	* **Note:** Dictionary data stored before this update used on your pages isn't retroactively filled with inflections.
		 * To parse old content with the new inflection lookup, you must first clear your old keyword, and then refill your selections.
* **Added:** Broad Unicode support is now available.
	* You can now use Focus with diacritic characters much more accurately, and almost all non-alphabetical characters are now recognized as well.
	* We initially waited for browsers to catch up; alas, Firefox and Edge are still slacking behind. So, we've implemented a contemporary in-house solution.
* **Fixed:** When a synonym matches an inflection, the synonym is now stripped.
	* This works retroactively, so your old content parser data will be affected.
* **Fixed:** The active word list cache is now functional. Now, your input values won't be reassessed more than once after making changes.

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
	* This also means that the thread isn't interfering with tedious and heavy content painting; as such, the performance increased tremendously.
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
* **Fixed:** When using TSF v3.1, interacting with this extension's UI elements won't trigger an "unsaved changes" warning when leaving the post-edit screen.
* **Fixed:** Empty or unmatched lexical form selectors are no longer cleared, or auto-activated on save.

### 1.0.0

[tsfep-release time="March 31st, 2018"]

* Initial extension release.
