# Honeypot
Location: https://theseoframework.com/extensions/honeypot/
Tags: spam
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension catches comment spammers through four lightweight yet powerful methods.

## Overview

### Reduce spam

WordPress allows visitors to easily interact with your website. Unfortunately, it's too easy.

Because WordPress' comment system is open, it attracts unwanted comments. Such comments are often automatically inserted to share backlinks.

Websites with spammy content are rendered as low-quality by users and search engines alike.

To reduce comment spam, you could manually moderate the comments, but this is very time consuming.

The Honeypot extension stops automatically inserted comments, saving you loads of time while improving SEO.

### A non-intrusive extension

Honeypot is for catching robots. Visitors shouldn't notice its presence.

Apart from other anti-spam techniques, like captcha or an answer field, a honeypot is hidden from visitors.
This means the visitors doesn't need to interact with the anti-spam technique, improving user experience.

In short, your site is protected from spam in the background.

This extension also has an unmeasurably low server memory and CPU footprint, and it only adds roughly 160 bytes to your page's source.
In other words: there's no performance difference with or without the extension.

All this makes the Honeypot extension favorable to other solutions.

### Four methods

Robots that leave spammy comments use different techniques leaving comments.
To counter various techniques, four powerful blockades will be implemented on your site when you activate the Honeypot extension.

All methods include randomization, they prevent robots programmatically bypassing the checks.

All four methods combined block a wide overlapping spectrum of robot spamming techniques. Therefore, Honeypot has a **99.98% catch-rate**.

#### First method: Static CSS

The Static CSS honeypot outputs a text field that must stay empty. Many robots are likely to fill in this field, marking their comment as spam.

To prevent robots from detecting this field, it's marked with a unique ID. This ID differs per site and per post and won't change over time.
Therefore, this field is compatible with caching plugins and is difficult to detect.

#### Second method: Scoped Rotation CSS

The Scoped Rotation CSS honeypot works like the Static CSS honeypot. But, it requires robots to use HTML5.

Also, when no caching plugin is used, it will rotate its unique ID every 60 minutes.
Because of its rotation, robots can't be taught what to target, which makes this field even more effective.

#### Third method: JS

The JS honeypot uses a combination unique ID rotation, forced entry and JavaScript.

Most robots do not enable JavaScript for improved spamming rate, making this form field very effective.

Like the second method, the unique ID rotation prevents robots from learning what to target.
It also outputs a "textarea" field which must be emptied. This field is automatically emptied and hidden when the visitor uses a JavaScript-enabled browser.

If the user doesn't have a JavaScript-enabled browser, these fields will be shown:

* **Label:** "Comments for robots".
* **Input:** "Please remove this comment to prove you're human.".
* **Placeholder:** "You are human!".

#### Fourth method: Nonce

A nonce is a number that may only be used once. For this field, it may be used many times within a preset timeframe.

The Nonce honeypot is a form field that is automatically filled in by Honeypot. The nonce must unaffectedly be presented when the comment is sent.
This prevents robots from using HTTP POST injection, which would otherwise allow them bypassing all other checks.

This field is unique per page, and is valid for 24 hours. When a caching plugin is used, this field stays valid for 10 days.
To prevent expired keys, a new key will be generated in half of the allotted time, so a visitor can always comment within at least 12 hours.

## Usage

[tsfep-bundled]

### Activate Honeypot

All you'll need to do is activate the Honeypot extension.

There is no setup required and no options are available.

[tsfep-image id="2"]

### More control

WordPress has various discussion settings, through which you can fine-tune how comments are displayed and moderated.

These settings can be found at **Settings -> Discussion**.

### Gotta catch 'em all

With Honeypot, you can be certain that each comment that comes through is written by a human.

But, if even those comments are spam, consider combining this extension with an authoritative comment plugin like [Akismet](https://wordpress.org/plugins/akismet/). This plugin tracks the user's comment activity on multiple sites.

## Developers

### Filters

Here you can find the available filters for Honeypot.

#### Change input field accessibility sentences

When JavaScript is disabled on the visitor's browser, the hidden input field isn't automatically removed and cleared.

Most robots don't know that they need to clear this field. Real visitors should.

The fields are self-explanatory, translatable, and you're free to change them.

```php
add_filter( 'the_seo_framework_honeypot_label', function( $text = '' ) {
	// Text displayed above the input, as a label.
	return __( 'Comments for robots', 'the-seo-framework-extension-manager' );
} );
```

```php
add_filter( 'the_seo_framework_honeypot_input', function( $text = '' ) {
	// Text displayed that asks the visitor to clear the field.
	return __( "Please remove this comment to prove you're human.", 'the-seo-framework-extension-manager' );
} );
```

```php
add_filter( 'the_seo_framework_honeypot_placeholder', function( $text = '' ) {
	// Text displayed when the visitor clears the field.
	return __( 'You are human!', 'the-seo-framework-extension-manager' );
} );
```

#### Adjust hardcore mode

Honeypot autodetermines whether hardcore mode is available based on your site's caching settings.

When hardcore mode is enabled, field names and values are rotated more often. This catches even the smartest bots.

These values have been carefully tuned and shouldn't have to be changed.

```php
add_filter( 'the_seo_framework_honeypot_hardcore', function( $hardcore = true ) {
	// Toggle hardcore mode. Below is the default value.
	return ! WP_CACHE;
} );
```

```php
add_filter( 'the_seo_framework_honeypot_field_scale', function( $scale = 3600 ) {
	/**
	 * This filter only works when hardcore mode is enabled. Otherwise, unique
	 * IDs are created on a per-page basis, which are used indefinitely.
	 *
	 * This is the minimum time a visitor has to submit an illegal comment on your site.
	 * The maximum time is twice the value returned.
	 *
	 * When this time passes, the submitted comment bypasses some spam checks.
	 *
	 * Lower than 300 seconds (total 600 i.e. 10 minutes) is not recommended,
	 * as some bots enqueue their targets.
	 *
	 * Below are the default values.
	 */
	return 60 * MINUTE_IN_SECONDS;
} );
```

```php
add_filter( 'the_seo_framework_honeypot_nonce_scale', function( $scale = 43200, $hardcore = true ) {
	/**
	 * This is the minimum time a visitor has to submit a comment on your site.
	 * The maximum time is twice the value returned.
	 *
	 * When this time passes, the submitted comment is automatically rejected.
	 *
	 * Lower than 3600 seconds (total 7200 i.e. 2 hours) is not recommended,
	 * as some users might generously wait to comment (closing laptop and such).
	 *
	 * Below are the default values.
	 */
	if ( $hardcore ) {
		return 12 * HOUR_IN_SECONDS;
	} else {
		return 5 * DAY_IN_SECONDS;
	}
}, 10, 2 );
```

## Changelog

### 1.1.1

[tsfep-release time="March 31st, 2018"]

* **Changed:** Filter `the_seo_framework_honeypot_nonce_scale` now passes a second "hardcore" boolean parameter.
	* Documented at [developers](#developers).
* **Fixed:** An off-by-one error has been resolved generating a random first alphabetic character.

### 1.1.0

[tsfep-release time="November 10th, 2017"]

* **Changed:** This extension is now free and out of testing phase.
* **Fixed:** Generated hashes now always start with an alphabetic character. Making the scoped field always disappear for users as intended.

### 1.0.1-beta

[tsfep-release time="August 22nd, 2017"]

* **Improved:** The CSS rotation field now uses a scoped style node, rather than inline styling.
* **Changed:** Moved the honeypot above the comment form, so spammers will more easily fill it in.

### 1.0.0-beta

[tsfep-release time="May 15th, 2017"]

* Initial extension release.
