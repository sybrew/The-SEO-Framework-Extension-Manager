# AMP
Location: https://theseoframework.com/extensions/amp/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension binds The SEO Framework to the AMP plugin for AMP supported articles and pages.

## Overview

### The AMP Project

The Accelerated Mobile Pages (AMP) project is an open-source initiative. Its primary goal is to create fast-loading pages, that are served directly from Google's servers.

To integrate AMP into WordPress, use Automattic's [AMP plugin](https://wordpress.org/plugins/amp/) to get started.

After the AMP plugin by Automattic is activated, you can start using the AMP extension.

### The AMP extension

Because AMP pages don't act like regular WordPress pages, many plugins, including The SEO Framework, don't work there by default.

This extension takes important SEO data from The SEO Framework and outputs them on AMP pages.

### The Articles extension

The [tsfep-extension name="articles"] also outputs its data on AMP pages when this extension is enabled. [tsfep-see-more extension="articles"]

## Usage

[tsfep-bundled]

### Activate the AMP extension

To use the AMP extension, all you'll need to do is activate this extension.

There is no setup required, and no options are available.

Before this extension does anything useful, AMP pages must be set up through Automattic's AMP plugin.

[tsfep-image id="1"]

### Activate Automattic's AMP plugin

To set up AMP pages, you'll require the AMP plugin.

For more information, please visit:
[https://wordpress.org/plugins/amp/](https://wordpress.org/plugins/amp/)

[tsfep-image id="2"]

## Developers

### Filters

Here you can find the available filters for AMP.

#### Add meta content

Add your own meta data, with either `the_seo_framework_amp_pre` (before) or `the_seo_framework_amp_pro` (after).

```php
add_filter( 'the_seo_framework_amp_pro', function( $output = '' ) {

	// Add your own meta tags. Don't overwrite $output!
	$output .= '&amp;lt;meta name="author" content="John Doe" /&amp;gt;' . PHP_EOL;

	return $output;
} );
```

## Changelog

### 1.1.0

[tsfep-release time="August 28th, 2018"]

* **Improved:** Now uses AMP v0.5+ endpoint detection when available.

### 1.0.2

[tsfep-release time="August 22nd, 2017"]

* **Added:** Output filters, respectively before and after:
	* `(string) the_seo_framework_amp_pre`
	* `(string) the_seo_framework_amp_pro`
	* Documented at [developers](#developers).

### 1.0.1

[tsfep-release time="May 15th, 2017"]

* **Added:** Indicators of where TSF output starts and ends when using TSF 2.9.2 or later.
	* The [tsfep-extension name="incognito"] removes these indicators.

### 1.0.0

[tsfep-release time="February 17th, 2017"]

* Initial extension release.
