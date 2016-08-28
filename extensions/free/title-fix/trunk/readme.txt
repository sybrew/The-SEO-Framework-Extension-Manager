=== The SEO Framework - Title Fix ===
Contributors: Cybr
Donate link: http://theseoframework.com/
Tags: seo, framework, rewrite, theme, doing it wrong, doing it right, multisite, automated, automatic, the seo framework, tsf
Requires at least: 3.9.0
Tested up to: 4.6.0
Stable tag: 1.0.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The Title Fix extension for The SEO Framework makes sure your title output is as configured. Even if your theme is doing it wrong.

== Description ==

***The SEO Framework relies on the required Title Tag support within the theme files. This can cause issues because not all themes do it right.***

This free extension plugin fixes those issues by replacing the title tag within the output buffer prior to outputting your website's content.

There are no options, only (super fast) detection. Activate and go!

> This plugin uses The SEO Framework title detection features.
>
> If the title is detected to be output wrong, this plugin fixes it.
> If you wish to have a more forceful override, then a filter is available. See Other Notes.
>
> This plugin is fundamentally built to perform fast and to keep its memory usage low.

= Compatibility =

* This plugin requires **The SEO Framework 2.7.0 and up**.
* This plugin works on multisite.

**If The SEO Framework is not active:**

* This plugin will do nothing much other than just checking to see if The SEO Framework is active.

**PHP:**

* Uses PHP Output Buffering Control (which is in PHP Core).
* If PCRE (Perl Compatible Regular Expressions) is installed with PHP (which is very likely), this plugin will use regular expresions to find the title.
* If not, it uses legacy PHP find and replace, which is also great!

== Installation ==

1. Install The SEO Framework either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate that plugin or activate it on a single site.
1. Install this extension either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. Now the title is fixed automatically when detected.

***You can also use this plugin as a mu-plugin.***

== Frequently Asked Questions ==

= What is this? =

This free extension is made because many themes are rendering the title wrong. This causes issues, which this plugin resolves.

= What is The SEO Framework? =

A free SEO plugin, for everyone! Download it [from WordPress.org](https://wordpress.org/plugins/autodescription/).

= How does this plugin work? =

This plugin is only run when the title output **has been detected to be wrong** by The SEO Framework.
From there, this plugin will scan your HTML code for the title and will replace it if found.

= Does this plugin impact my website's performance? =

Nope! Okay.. very, very slightly!

We're talking about 0.001 seconds of runtime here on PHP7 when your theme is doing it wrong.
And 0.0005 seconds if your theme is doing it somewhat right :).
And 0.0002 seconds if your theme has the required title-tag support!

= Does this plugin work on multisite? =

Absolutely!

== Changelog ==

= 1.0.2 - The Force =

**For everyone:**

* Fixed: The title will now always be fixed when the theme is likely to do it wrong when The SEO Framework 2.6.6.2 or lower is installed.
	* This changes back to default behavior when The SEO Framework 2.7.0 or later is installed.
	* This is because when (once every three days) the detection transient could be wrong when a correct title is being output in specific cases.
* Fixed: When no title tag can be found (although unlikely), the content will no longer be destroyed.
* Fixed: The title tag can now be found on multiple lines when using Regular Expressions (default behavior).
	* This always worked correctly on alternative behavior (legacy PHP find and replace).
* Changed: Plugin minimum WordPress requirement is bumped up to 3.9.0, in par with The SEO Framework's (actual) requirement.

**For developers:**

* Improved: The SEO Framework's main class object is no longer cached within this plugin's class memory, this saves more than 11 times its memory allocation.
* Improved: Version comparison PHP functions have improved by about tenfold in performance (microseconds).
* Changed: filter `the_seo_framework_force_title_fix` now defaults to whether there's no presence of The SEO Framework 2.7.0.
	* Explained: This means that if the installed The SEO Framework version is at or above 2.7.0, the filter defaults to `false`; otherwise to `true`.
* Note: It's possible, although very unlikely, that themes and other plugins could prevent this plugin from working as intended by incorrectly adding or destroying buffers.
	* If you notice that the title isn't fixed when it should be, please open a support topic so we can pinpoint the cause of this issue.

= 1.0.1.2 - The Fease =

**For developers:**

* Removed: Performance profiling, to improve performance.
* Cleaned up code.

= 1.0.1.1 - The Frame =

**For everyone:**

* Fixed: Plugin description within the plugin activation page.

= 1.0.1 - The False =

**For everyone:**

* Added: HTML comment "fixed" indicator.
* Added: Filter to remove the indicator.
* Improved: Reduced chance of PHP notice when the title tag has been reversed or has never been closed when PCRE is not supported.

**For developers:**

* Added: GPLv3 license file.
* Improved: Further bytecode optimization within the code. Perfect for Opcode caching.

= 1.0.0 - The Flush =

**For everyone:**

* Initial Release.
* Added: Up to three flush checks if the theme is doing it wrong.
* Added: Filter to make the plugin run regardless, and only if no title-tag support has been found.

== Other Notes ==

= Filter: Force title fix =

You can force the title fix through a filter.

***When using The SEO Framework 2.6.0+:***
This filter will only work if the theme doesn't support the title-tag, to improve performance.
Otherwise, it will override the title at all times.

`add_filter( 'the_seo_framework_force_title_fix', '__return_true' );`

= Filter: Remove fixed indicator =

When the title is fixed, a small indicator will be output to let you know it's fixed (in the page HTML source).
If you wish to remove this indicator, use the following filter:

`add_filter( 'the_seo_framework_title_fixed_indicator', '__return_false' );`
