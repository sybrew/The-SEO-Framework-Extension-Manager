# Local
Location: https://theseoframework.com/extensions/local/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension lets you set up important local business information for search engines to consume.

## Overview

### Higher ranking with local search

For most businesses, local listings are a must. Once listed, a potential visitor can then find more about your business directly from search engines.

A local listing within Google will also be placed upfront and sometimes above all other search results.

With the Local extension, you can list your local business departments in Google and other search engines.

[tsfep-image id="1"]

### An advanced SEO solution

We present you an advanced and leading Local solution.

**This software package includes:**

* Support for up to 4096 locations.
* (Reverse) Geocoding through our API services.
* Options for opening hours per area.
* Internal caching, for significantly faster execution.
* Reservation, menu, and cuisine support for food establishments.
* Multisite support.

The output of your business information is fully automated through Schema.org header scripts.
This means there's no signup required at Google or Bing. This also leads to higher exposure for search engines and visitors alike.

[tsfep-image id="2"]

### Is Local for your business?

**This extension is for your business, if:**

* Your business is physically established on one or more locations.
* You want to inform search engine users nearby about your business before they enter your site.
* You want your business to be easily found on specific keywords, like "Italian Restaurant".
* You want your business website to have more authority and search presence.

### How does Local SEO work?

After you've filled in all required information (and saved it), Local will automatically output all that information through Schema.org scripts in your website's header.
These scripts aren't seen on your website by general visitors, but search engine crawlers can interpret this information naturally.

Schema.org is a standard set through joint efforts of Google, Bing, Yahoo! and Yandex.

### Fully automated, no sign-up required

When you've acquired a premium subscription for the Extension Manager, the Local extension can connect to our services in the admin area.

We took care of all the API connections with geocoding services and packaged it within the user interface for a smooth admin experience.

In the end, this saves you a lot of time.

## Usage

[tsfep-bundled]

### Activate Local

First, you'll need to activate the extension.

After activating the extension, you'll find a menu link displayed next to the activation button.
That link will direct you to the Local settings page.

On the Local settings page, you'll find a brief introductory, and right below that, you'll see the number of departments options.

### Set the number of departments

When your business has one physical location, leave this option set as `1`.

If your business has, for example, a shop inside the restaurant, you might find it useful to annotate them through multiple departments.

If your business has multiple locations, you should also increase the number of departments.

Each of the departments requires almost all information to be set.

[tsfep-image id="3"]

### Set up local business information

To begin filling in the required information, start by clicking the department block.
This will expand all the available settings.

What you'll notice directly is that the header will be annotated by a red cross. This means not all required data is set up.
All fields are checked as you fill them in. If they're red, you need to fix the input.

*If you require color vision support, don't worry! The extension will walk you through all incorrect fields when you try to view the markup or save the settings.*

[tsfep-image id="4"]

If you are in need of any information regarding a field, hover your mouse over the nearby question mark.

### View markup

If you wish to test out your markup first, you can do so through the **See Markup** button found at the bottom.
A new window will open, with your markup in Google's Structured Data Testing tool.

Through Google's Structured Data Testing Tool, you can see a clear view of the markup.
In the middle, there's a big button. When pressed, Google will parse the data and will highlight mistakes.

When the input is compatible with Google's previewer, you can preview the markup through the **Preview** button.

*Note: The preview button might not always be available, depending on the data filled in or your current location.*

[tsfep-image id="5"]

### Saving the data

Finally, when you're all set, you can save the data through the **Save** button found at the bottom.
This will parse and cache all input data for front-end output.

All data will automatically be outputted on the homepage, and will automatically link to any department URLs set.
Also, when department URLs are filled in, then each specific department's data will be outputted accordingly on those pages.

## Changelog

### 1.3.0

[tsfep-release time="February 7th, 2023"]

* **Added:** The price range of departments can now be specified.
* **Added:** Opening hours can now be scheduled; for example, if you have a specific summer schedule.
	* These hours won't disappear until you unset them.
	* To support this, from 21 schedules (3 times over 7 days), up to 50 schedules can now be made per department. This new number is quite arbitrary, but allows for enough room without breaking the performance bank.
	* These fields should be adjusted manually every year according to your business' schedule.
* **Removed:** The auto-generated `@id` field is no longer required. We removed it because it offers no benefit, and the way it was built only confused our users.
	* Changes are only visible after you update Local's fields.

### 1.2.0

[tsfep-release time="October 4th, 2022"]

* **Improved:** Modernized code with a focus on improving performance.

### 1.1.9

[tsfep-release time="November 8th, 2021"]

* **Updated:** This extension now is fully compatible with The SEO Framework v4.2.0.
* **Fixed:** Resolved an issue that caused the output not to run on assigned URLs when the main department is disabled.

### 1.1.8

[tsfep-release time="February 9th, 2021"]

* **Changed:** The markup preview button now leads to Google's new Rich Results Test platform, instead of their now deprecated Structured Data Testing Tool.

### 1.1.7

[tsfep-release time="June 2nd, 2020"]

* **Changed:** This extension's admin access can now be controlled via the global constant `TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE`.

### 1.1.6

[tsfep-release time="May 15th, 2020"]

* **Fixed:** Two cuisines had typos in their name. Namely, Sudanese and Brazilian.
	* If your restaurant offers these, be sure to reselect them and save the Local settings again.

### 1.1.5

[tsfep-release time="November 5th, 2019"]

* **Improved:** To honor the new Extension Manager interface, for accessibility, the actionable buttons have been added to the header.

### 1.1.4

[tsfep-release time="August 20th, 2019"]

* **Fixed:** Apostrophes entered in the settings no longer get backslashed on save or preview. However, sequential backslashes are now unpaired.
	* It means that `&#39;` no longer becomes `&#92;&#39;`.
	* However, it also means that `&#92;&#92;&#92;` becomes `&#92;&#92;`, and then `&#92;`, and then stays `&#92;`. This is consistent with the rest of WordPress.

### 1.1.3

[tsfep-release time="January 28th, 2019"]

* **Changed:** This extension now uses TSF's script loader.

### 1.1.2

[tsfep-release time="December 4th, 2018"]

* **Added:** This extension will try reparsing the values in the admin area to fix the openinghours specification issues below, only once.
* **Updated:** The department type list is updated with these items:
	* **Now with verified support:**
		* MedicalBusiness
		* MedicalClinic
		* Optician
		* Distillery
	* **Newly added:**
		* ProfessionalService
			* Note that subtypes including Dentist, Accounting Service, Attorney, Notary, Electrician, House Painter, etc., etc., are also available.
			* Due to great diversity, the types above are not listed as subtypes.
* **Changed:** You can now specify up to 21 opening hours per department, instead of 7.
	* This change most prominently adds support for siestas.
* **Fixed:** The openinghours specification is now correctly set when the department is closed or open all day.
* **Fixed:** The openinghours specification now correctly purges when the values are incorrect.

### 1.1.1

[tsfep-release time="November 9th, 2018"]

* **Fixed:** When a non-ASCII department name is filled in, a department ID is now generated by using the department number instead.

### 1.1.0

[tsfep-release time="August 28th, 2018"]

* **Added:** TSF v3.1 support.

### 1.0.1

[tsfep-release time="March 31st, 2018"]

* **Changed:** The street address can now have the number omitted.
* **Improved:** The admin layout matches the new and modernized standard.

### 1.0.0

[tsfep-release time="August 22nd, 2017"]

* Initial extension release.
