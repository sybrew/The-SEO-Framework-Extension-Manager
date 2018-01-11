# Title Fix
Contributors:
Location: https://theseoframework.com/extensions/title-fix/
Tags: theme
Requires at least: 4.4.0
Required PHP: 5.5.21 or 5.6.5
Tested up to: 4.9.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension makes sure your title output is as configured. Even if your theme is doing it wrong.

## Overview

### Broken WordPress standards

WordPress themes have final control of the title. Quite often, some theme authors opted in for "pretty" titles, rather than semantic titles.

This created various issues.

Firstly, titles shouldn't be pretty; they should be unique. This is a fundamental part of SEO as it creates distinction.
Secondly, the way WordPress used to handle titles was vague at best. This prevents plugins, among The SEO Framework, from altering the title correctly.

To prevent these issues from happening, new WordPress themes in the WordPress.org repository must follow [a newer standard](https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/).

Not all themes have been updated accordingly, and the Title Fix extension will solve that for you.

### Is this extension for you?

If you notice your page titles aren't what you set it to be, then yes.

Otherwise, this extension won't have any effect.

### How it works

The SEO Framework can detect incorrect usage of the title output. It tries to resolve this automatically, but sometimes that isn't enough.

When The SEO Framework detects incorrect usage, it will set up a flag. This extension looks for this flag before it operates.

When the flag is found, this extension will look for the title tag in the source.
When found, it will overwrite the title tag with a correct version.

All this happens before it's being sent to the page visitor or crawler in less than 2 miliseconds.

## Usage

[tsfep-bundled]

### Activate Title Fix

All you'll need to do is activate the Title Fix extension.

There is no setup required and no options are available.

[tsfep-image id="1"]

### Still not fixed?

In unlikely occassions, the wrong title detection flag might not be set up.

To force a fix of the title, add the following filter to your theme's `functions.php` file:

`add_filter( 'the_seo_framework_force_title_fix', '__return_true' );
`

## Changelog

### 1.1.0

[tsfep-release time="-1"]

* **Removed:** filter `the_seo_framework_force_title_fix`.
	* In preparation for TSF 3.1.
	* This was added to add multisite support for the plugin, but this now an extension.

### 1.0.3

[tsfep-release time="February 17th, 2017"]

* **Improved:** Removed redundant UTF-8 check.
* **Improved:** The extension now enqueues fewer actions when the title is fixed early.
* **Improved:** The extension also enqueues fewer WordPress actions overall. Which reduces memory usage marginally.
* **Fixed:** The SEO Framework can now recognize this extension's presence, re-enabling otherwise disabled features.

### 1.0.2

[tsfep-release time="January 1st, 2017"]

* Initial extension release.

### 1.0.2-1.0.0

[tsfep-release time="August 27th, 2016"]

* This extension used to be a WordPress plugin.
