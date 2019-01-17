# Focus
Location: https://theseoframework.com/extensions/focus/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension guides you through the process of writing targeted content that ranks with focus keywords, their inflections, and their synonyms.

## Overview

### Focus your content

With over 1 billion competing websites, your pages barely stand a chance ranking on the first page of Google.
So, writing quality content that ranks well in search engines isn't easy. This is where Focus comes in.

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

There are many ways to write in a language. And with a Premium subscription, you can embrace all Focus has to offer.

So, after you fill in a keyword, Focus will connect to our API services and will provide you with related synonyms and inflections.
With those, Focus is able to rate your content more accurately.

The synonyms provided make up for great writing suggestions. Utilizing those synonyms will increase your chances to be found, too.

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

***(Premium only)***

If your keyword matches an entry in our dictionary, you can select a lexical form.

You should select the form that describes your subject most, these forms are automatically sorted from frequent to infrequent usage.
So, choosing the first form is often correct.

[tsfep-image id="4"]

#### Select homonymous example

***(Premium only)***

Some words have more than one meaning. So, after selecting the lexical form, example sentences are formed.
These sentences are also sorted in order of the most common form, and the most common phrase is automatically selected.

Change the example sentence that closely describes your subject, and you could get different synonyms.

[tsfep-image id="5"]

#### Select active inflections and synonyms

***(Premium only)***

After all synonyms and inflections are received, just click on the ones you'd like to use.

If you haven't used a synonym in your content yet, consider using it as it will increase your chances of being found.

[tsfep-image id="6"]

### Ratings

The ratings tell you what should be improved in your content.

Use these ratings as guidelines for focused content.
You shouldn't ruin your content just to get a better rating.

#### Meta vs Page Title

The page title is what's displayed to the user on your website.
You can change this at the top of your content editor.

The meta title is what's displayed to a potential visitor on Google.
You can change this under the General tab of the page's SEO settings.

#### First Paragraph

The first paragraph is the first block of text found in your content editor.
In writing, the first paragraph is used to annotate what your content is about.

It's the best place to use your focus keyword.

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

You can change your page URL right above the content editor.

## FAQ

# Which languages are supported?

All languages have the requisite support for ratings.

# Which languages are supported by the dictionary API?

The API currently supports English only. Support for other languages will be added over time.

# Why are there only one or two inflections available?

We're currently working on inflection lookups. When it's ready, an update will be sent out.

## Changelog

### 1.3.0

[tsfep-release time="-1"]

* **Added:** The rater now spawns a new processing thread to calculate scores asynchronously.
	* This means you can write more content uninterrupted as the parser is caclulating its scores.
	* This also means that the thread isn't interfering with tedious and heavy content painting, as such, the performance increased tremendously.
* **Added:** The plugin now tests for and informs on parsing failures.
	* **Note:** A failure fires automatically if the rater is stuck for longer than 30 seconds. This allows your OS to allocate a better core affinity when reattempting.
* **Improved:** General parsing performance by refactoring HTML tag exclusions, you'll now get results up to ten times quicker, relatively.
* **Improved:** Upgraded the JS version requirements, and as such, all known unicode punctuation can be excluded correctly.

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
