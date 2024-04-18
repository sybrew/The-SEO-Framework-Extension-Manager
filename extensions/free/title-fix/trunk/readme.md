# Title Fix
Location: https://theseoframework.com/extensions/title-fix/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension makes sure your meta title output is as configured. Even if your theme is doing it wrong.

## Overview

### Broken WordPress standards

10 years ago, [WordPress introduced a modern title tag standard](https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/). Still, not all themes have implemented this.

Themes without the modern standard may opt in for "pretty" titles, rather than semantic titles. This creates various issues.

First, titles shouldn't be pretty; they should be unique. This is a fundamental part of SEO as it creates distinction.
Second, the way WordPress used to handle titles was vague at best. This prevents plugins, among The SEO Framework, from altering the title correctly.

The Title Fix extension will solve these issues by finding and replacing the title tag.

### Is this extension for you?

If you notice your page titles aren't what you set it to be, then yes.

Otherwise, this extension won't have any effect.

### How it works

The SEO Framework can detect incorrect usage of the title output. It tries to resolve this automatically, but sometimes that isn't enough.

Title Fix scans the HTML response output buffer for the title. It overwrites the title when found before the page is sent to the visitor, all within a millisecond.

This extension doesn't have settings and doesn't store anything. Deactivation will resume default title output behavior.

## Usage

[tsfep-bundled]

### Activate Title Fix

All you'll need to do is activate the Title Fix extension.

There is no setup required, and no options are available.

[tsfep-image id="1"]

## Changelog

### 1.2.1

[tsfep-release time="November 5th, 2019"]

* **Updated:** Removed backward compatibility checks. The extension now requires TSF v4.0 and later.

### 1.2.0

[tsfep-release time="August 28th, 2018"]

* **Added:** TSF v3.1 title-fetching support.

### 1.1.0

[tsfep-release time="March 31st, 2018"]

* **Removed:** filter `the_seo_framework_force_title_fix`.
	* In preparation for TSF 3.1.
	* This was added to include multisite support for the plugin; but, as this is now an extension, it's automatically supported.

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
