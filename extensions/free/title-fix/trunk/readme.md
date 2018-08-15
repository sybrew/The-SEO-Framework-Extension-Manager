# Title Fix
Location: https://theseoframework.com/extensions/title-fix/
Tags: theme
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

When the extension is active, it automatically looks for the title in your page's source and overwrites it when found.
All this happens before your pages are sent to the page visitor or crawler, in less than 2 milliseconds.

## Usage

[tsfep-bundled]

### Activate Title Fix

All you'll need to do is activate the Title Fix extension.

There is no setup required and no options are available.

[tsfep-image id="1"]

## Changelog

### 1.2.0

[tsfep-release time="-1"]

* **Added:** TSF v3.1 support.

### 1.1.0

[tsfep-release time="March 31st, 2018"]

* **Removed:** filter `the_seo_framework_force_title_fix`.
	* In preparation for TSF 3.1.
	* This was added to include multisite support for the plugin, but as this now an extension it's automatically supported.

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
