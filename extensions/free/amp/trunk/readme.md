# AMP
Contributors:
Location: https://theseoframework.com/extensions/amp/
Tags: general
Requires at least: 4.4.0
Required PHP: 5.5.21 or 5.6.5
Tested up to: 4.8.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension binds The SEO Framework to the AMP plugin for AMP supported articles and pages.

## Overview

### The AMP Project

The Accelerated Mobile Pages (AMP) project is an open-source initiative. Its primary goal is to create fast-loading pages, that are served directly from Google's servers.

To integrate AMP into WordPress, Automattic has provisioned [a plugin](https://wordpress.org/plugins/amp/) to get you started.

### The AMP extension

Because AMP pages don't act like regular WordPress pages, many plugins, including The SEO Framework, don't work there by default.

This extension takes important SEO data from The SEO Framework, and outputs them on AMP pages.

### The Articles extension

The [tsfep-extension name="articles"] also outputs its data on AMP pages when this extension is enabled. [tsfep-see-more extension="articles"]

## Usage

[tsfep-bundled]

### Activate the AMP extension

To use the AMP extension, all you'll need to do is activate this extension.

There is no setup required and no options are available.

Before this extension does anything useful, AMP pages must be set up through Automattic's AMP plugin.

[tsfep-image id="1"]

### Activate Automattic's AMP plugin

To set up AMP pages, you'll require the AMP plugin.

For more information, please visit:
[https://wordpress.org/plugins/amp/](https://wordpress.org/plugins/amp/)

[tsfep-image id="2"]

## Changelog

### 1.0.2

[tsfep-release time="January 1st, 2017"]

* **Added:** Output filters, respectively before and after:
	* `(string) the_seo_framework_amp_pre`
	* `(string) the_seo_framework_amp_pro`

### 1.0.1

[tsfep-release time="May 15th, 2017"]

* **Added:** Indicators of where TSF output starts and ends when using TSF 2.9.2 or later.
	* The [tsfep-extension name="incognito"] removes these indicators.

### 1.0.0

[tsfep-release time="February 17th, 2017"]

* Initial extension release.
