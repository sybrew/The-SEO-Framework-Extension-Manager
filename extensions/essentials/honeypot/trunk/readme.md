# Honeypot
Location: https://theseoframework.com/extensions/honeypot/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This privacy-focussed extension catches comment spammers with a 99.99% catch-rate using six lightweight yet powerful methods that won't leak data from your site.

## Overview

### Stop comment spam

WordPress allows visitors to interact with your website easily. Unfortunately, it's too easy.

Because WordPress's comment system is open and known, it attracts unwanted comments. These comments are often automatically inserted to share backlinks via meticulously crafted text-templates that can fool anybody. Websites with spammy content are seen as low-quality by your visitors and search engines alike.

Now, you could waste your time moderating the comments manually... or, you could use Honeypot, which does that in less than a thousandth of a second.

### A non-intrusive, privacy-first extension

The Honeypot extension is for catching robots, not humans. So, your visitors shouldn't notice its presence.

Unlike CAPTCHA, a honeypot is hidden from visitors. So, your visitors don't need to interact with Honeypot, vastly improving the user experience; Honeypot protects your site from spam in the background.

This extension protects all WordPress themes and plugins that implement the default WordPress comment forms -- among WooCommerce product reviews -- without sharing comment data with anyone.

Honeypot also has an unmeasurably low footprint on server memory and CPU usage, it adds roughly 1kB to your pages.

### Six powerful methods, zero false positives

Robots leave spammy comments via various techniques, and Honeypot counters almost all of them by adding six powerful barriers to your site.

Only a human that uses a modern browser can pass these tests:

1. Static CSS-hidden fields using unique IDs. All bots that do not target WordPress specifically will fail this test.
1. Randomized CSS-hidden fields using HTML5 and time-limited IDs. Targets the same bots as above, but other bots that scrape comment forms for postponed abuse will also get caught.
1. Randomized JavaScript. Most bots don't use a real browser that supports JavaScript, so they'll fail this test. Humans that don't use JavaScript are asked kindly to empty a field.
1. Verification nonces. With this, bots can no longer abuse easily exposed endpoints in WordPress to leave comments.
1. GPU timer. The bot must actually render the page to pass this test, blocking spam from many emulated browsers.
1. Timestamp. Bots can speed up GPU timers, but they can't speed up this one. Fast typers will pass this enciphered timestamp test, but faster bots won't.

All six methods are entirely randomized and use secure authentication methods, so no robot can learn how to bypass Honeypot. These methods combined block a broad spectrum of robot spamming techniques. Hence, Honeypot has a **99.99% catch-rate**. Now you can finally uninstall and delete Akismet.

## FAQ

### How do the six methods help me?

Below you find an overview of each method implemented in Honeypot.

#### First method: Static CSS

The Static CSS honeypot outputs a text field that must stay empty. Many robots are likely to fill in this field, marking their comment as spam.

To prevent robots from detecting the text field, the field's marked with a unique ID. This ID differs per site and per post; but, it won't change over time, so this field is compatible with caching plugins yet remains challenging to detect.

#### Second method: Scoped Rotation CSS

The Scoped Rotation CSS honeypot works like the Static CSS honeypot. But, it requires robots to render HTML5, blocking most archaic bots automatically.

When no caching plugin is used, the field will rotate its unique ID every 60 minutes. Because of its rotation, robots can't "learn" what to target, making this field even more effective.

#### Third method: Rotating JavaScript

This honeypot uses a combination of unique ID rotation, forced entry, and JavaScript.

Almost no robot enables JavaScript for an increased spamming rate because it's resource-intensive, which makes this form field very useful.

It also outputs a 'textarea'-field which must be emptied manually by the visitor if JavaScript is disabled. However, when the visitor uses a JavaScript-enabled browser, this field remains hidden and is emptied automatically.

If the user doesn't have a JavaScript-enabled browser (or blocks JavaScript), Honeypot will show these fields:

* **Label:** "Comment for robots".
* **Input:** "Please empty this comment field to prove you're human.".
* **Placeholder:** "You are human!".

#### Fourth method: Nonce

A nonce is a number that may only be used once. Confusingly for this field, it may be used many times within an allocated timeframe.

Honeypot automatically fills in the nonce-honeypot field via a time-based algorithm, so this value isn't stored anywhere. The nonce-value must be sent back by the commenter to Honeypot unaltered. The nonce-value is then recalculated, and when matched, Honeypot may approve the comment.

The nonce prevents robots from sending comments to WordPress via XMLRPC or HTTP injections, one of the most common spamming techniques.

This field is unique per page, and its value is valid for up to 24 hours. When a caching plugin is used, this field's validity is extended to 10 days. There are two values in circulation at all times, an old and a new nonce. If a visitor retrieved an old key that will no longer be handed out, they could still use that to prove they're human for at least 12 hours.

#### Fifth method: GPU timer

Robots that can bypass the other four methods are most definitely using a virtualized, up-to-date browser. Almost all these robots reply within seconds; this is where Honeypot's timer can still catch them.

The timer works using JavaScript's animation frame-timers: the timer starts counting after the form is loaded and continues to do so only when the page is active. If the robot opens a new browser-tab and still sends a comment, Honeypot will block it.

This field is unique per site and per page ID. The field's value will change as it counts down using pseudorandom numbers to throw off countdown detection. The frame-timer runs sporadically between 3.33~10Hz to nullify any performance penalty.

### Sixth method: Timestamp

The Timestamp honeypot is a server-sided test that uses a ciphered timestamp, the output is the current time plus 5.33 seconds. This timestamp is created when the page is requested and is then added as a hidden field to the comment form.

When the visitor submits a comment, the timestamp is deciphered and checked against the current time. If the time hasn't passed since the page was first generated, the submitted comment is automatically rejected.

This field won't work with caching plugins because cached pages can be much older than 5.33 seconds, leading to the use of an outdated timestamp that passes the test automatically.

## Usage

[tsfep-bundled]

### Activate Honeypot

All you'll need to do is activate the Honeypot extension.

There is no setup required, and no options are available.

[tsfep-image id="2"]

### More control

WordPress has various discussion settings, through which you can fine-tune how comments are displayed and moderated. You can find these settings at **Settings > Discussion**.

### 100% privacy

Honeypot does not create or store cookies, does not track users, stores no data WordPress wouldn't, and does not share any data. Honeypot only flags a comment from 'pending/approved' to 'spam' if it catches something. You need not update your privacy policy with Honeypot.

### Gotta catch 'em all

With Honeypot, you can be confident that each comment that comes through is written by a human. But, if even those human comments are spam, consider combining this extension with an authoritative comment plugin like [Akismet](https://wordpress.org/plugins/akismet/). That plugin tracks the user's comment activity over many websites; so, using that might be unfavorable since it sends private user-data to third-parties.

## Developers

### Filters

Here you can find the available filters for Honeypot.

#### Change input field accessibility sentences

When JavaScript is disabled on the visitor's browser, the hidden input field isn't automatically removed and cleared.

Most robots don't know that they need to clear this field. Real visitors should.

The fields are self-explanatory, translatable, and you're free to change them.

```php
add_filter( 'the_seo_framework_honeypot_label', function ( $text = '' ) {
	// Text displayed above the input, as a label.
	return __( 'Comments for robots', 'the-seo-framework-extension-manager' );
} );
```

```php
add_filter( 'the_seo_framework_honeypot_input', function ( $text = '' ) {
	// Text displayed that asks the visitor to clear the field.
	return __( "Please remove this comment to prove you're human.", 'the-seo-framework-extension-manager' );
} );
```

```php
add_filter( 'the_seo_framework_honeypot_placeholder', function ( $text = '' ) {
	// Text displayed when the visitor clears the field.
	return __( 'You are human!', 'the-seo-framework-extension-manager' );
} );
```

#### Adjust hardcore mode

Honeypot automatically determines whether the "hardcore"-mode is available based on your site's caching settings.

When the "hardcore"-mode is enabled, field names and values are rotated more often. This catches even the smartest bots.

These values have been carefully tuned and shouldn't have to be changed.

```php
add_filter( 'the_seo_framework_honeypot_hardcore', function ( $hardcore = true ) {
	// Toggle hardcore mode. Below is the default value.
	return ! WP_CACHE;
} );
```

```php
add_filter( 'the_seo_framework_honeypot_field_scale', function ( $scale = 3600 ) {
	/**
	 * This filter only works when hardcore-mode is enabled. Otherwise, unique
	 * IDs are created on a per-page basis, which is used indefinitely.
	 *
	 * This is the minimum time a visitor has to submit an illegal comment on your site.
	 * The maximum time is twice the value returned.
	 *
	 * When this time passes, the submitted comment bypasses some spam checks.
	 *
	 * Lower than 300 seconds (total 600, i.e., 10 minutes) is not recommended,
	 * as some bots enqueue their targets.
	 *
	 * Below is the default value.
	 */
	return 60 * MINUTE_IN_SECONDS;
} );
```

```php
add_filter( 'the_seo_framework_honeypot_nonce_scale', function ( $scale = 43200, $hardcore = true ) {
	/**
	 * This is the minimum time a visitor has to submit a comment on your site.
	 * The maximum time is twice the value returned.
	 *
	 * When this time passes, the submitted comment is automatically rejected.
	 *
	 * Lower than 3600 seconds (total 7200, i.e. 2 hours) is not recommended,
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

#### Adjust submit delay timer

Honeypot blocks bots that leave comments within seconds of loading the page.

The timer only runs when the comment-field is loaded while the window is active because it's attached to the browser's frame-timer.
This means the timer should not need tweaking to accommodate slow servers or theme-loading time.

This value has been carefully tuned and shouldn't need changing.

```php
add_filter( 'the_seo_framework_honeypot_countdown_time', function ( $time = 5.33 ) {
	/**
	 * This is the minimum time a visitor has to wait before submitting a comment on your site.
	 * A random floating-point number between 0 and 1 is added to this number. The number
	 * then gets randomly increased (up to 16 bits) to mitigate countdown detection.
	 *
	 * If the timer is still running, the submitted comment is automatically rejected.
	 *
	 * Higher than 10 seconds is not recommended, as advanced users might copy and paste
	 * the comment from a failed (crashed) page-state.
	 *
	 * Values above 16 minus 8 bits (hex 0xFF00 or dec 65,280) may cause unexpected results.
	 * This value shouldn't be used to fend off human commenters.
	 *
	 * Below is the default value. Floating points up to two decimals are recognized.
	 */
	return 5.33; // seconds
} );
```

There's also another timestamp that uses this tuned delay value.

```php
add_filter( 'the_seo_framework_honeypot_timestamp_wait', function ( $time = 5.33 ) {
	/**
	 * This is the minimum time a visitor has to wait before submitting a comment on your site.
	 * This will then be ciphered and added as a value to a hidden comment field.
	 *
	 * If the time hasn't passed since the page was first generated, the submitted comment is automatically rejected.
	 *
	 * Higher than 10 seconds is not recommended, as advanced users might copy and paste
	 * the comment from a failed (crashed) page-state.
	 *
	 * Floating points merely add entropy based on microtime: 5.33 may result in 5 or 6 seconds wait time.
	 * This value shouldn't be used to fend off human commenters.
	 *
	 * Below is the default value.
	 */
	return 5.33; // seconds
} );
```

## Changelog

### 2.1.0

[tsfep-release time="November 19th, 2024"]

* **Added:** Added a new ciphered server-sided timestamp test to counter speedhacks bypassing the GPU timer.

### 2.0.1

[tsfep-release time="February 7th, 2023"]

* **Other:** Removed the browser-deprecated "scoped" attribute from the CSS rotation method.

### 2.0.0

[tsfep-release time="February 9th, 2021"]

* **Added:** Processing power is affordable now thanks to AMD. So, Honeypot must now catch fast robots that use browser emulators.
	* Basically, Honeypot now features a countdown timer. Don't comment too fast!
	* This comes with a filter, `the_seo_framework_honeypot_countdown_time`, that's documented at [developers](#developers).
* **Changed:** Some static honeypot's input fields are now pseudo-random, and Honeypot knows which part is usable.
	* This throws off bots that look for permanent values to forward to your server.
* **Changed:** Honeypot now uses Base36 and pseudo-Base62 instead of Base16, which makes nonce-hashes harder to detect by bots.
* **Changed:** The output order of the honeypots are now randomized.

### 1.1.3

[tsfep-release time="December 4th, 2018"]

* **Fixed:** The textarea no longer has an invalid tag, and its label is now more accessible.

### 1.1.2

[tsfep-release time="November 9th, 2018"]

* **Changed:** This is now an Essentials extension.

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
