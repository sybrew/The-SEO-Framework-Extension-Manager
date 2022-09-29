# Transport
Location: https://theseoframework.com/extensions/transport/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension migrates plugin SEO metadata from Yoast SEO and Rank Math to The SEO Framework.

## Overview

### Import with ease

Used another WordPress SEO plugin, like Yoast SEO or Rank Math, before you found that The SEO Framework is a better fit? Now you can finally migrate all your meticulously crafted post and term metadata.

#### Transform '%%currentdate%%' to ‘[tsfep-gimmick type="date" format="Y-m-d g:i a"]’

The importer not only moves your old metadata, but it also transforms: Complex and difficult to understand syntax becomes human-readable.
You can learn more about what data is transformed at the [FAQ](#faq/what-data-is-transformed).

### Logged and loaded

Transport comes with a real-time transaction logger, so you can see your data migrate and transform in real-time. Although unlikely, if there's an issue, you'll know about it immediately.

[tsfep-image id="1"]

## Usage

[tsfep-bundled]

### Activate Transport

First, you'll need to activate the Transport extension.

### Transport screen

Underneath the extension description, you should see a settings-link appear. You can also find the link to "Transport" under "SEO" in the admin sidebar, but you may need to refresh the admin page first.

On the Transport screen, you can start importing metadata.

[tsfep-image id="2"]

### Importing

First, select the plugin you want to import from. You'll then be presented with the types of data you can import. Hit "Import" and Transport will take care of the rest.

The importer keeps track of how many database items are found per data type; it will recount from 0 when it moves to the next data type. You can see this in real-time at the logger.

### Logger

The logger will display in real-time the progress of all data transactions.

Because webbrowsers become slow at about 32&nbsp;000 characters (18 book pages), the logger trims chunks of old log data to stay beneath that number.
You can  hit "Copy log" to grab what's present in the logger.

If there's an issue, feel free to send us a [support email](https://theseoframework.com/support/) with the log attached.

## FAQ

### Supported plugins for import

The importer currenlty supports Yoast SEO and Rank Math. Support for AIOSEO and SEOPress is coming soon&trade;.

### Should I create a backup before transporting?

Yes.

#### Really?

Yes. Transport will irreversibly alter and irretrievably delete metadata; both are intended features.

We recommend transporting only when you're certain you want to stay with The SEO Framework.
Otherwise, you should keep a backup ready in case you want to go back (we cannot comprehend why anyone would, though).

### What data is transformed?

All titles and descriptions pass through the transformer. This transformer takes syntax, such as `%archive_title%`, and transforms those as the plugin you're migrating from would.

After transformation, repeating separators will coalesced (`text | | | text` becomes `text | text`), and stray separators and spaces will be trimmed from either side of the title or description.

| Syntax                  | Becomes                                                                  |
|:----------------------- |:------------------------------------------------------------------------ |
| `archive_title`         | The term title                                                           |
| `author_first_name`     | The post author first name                                               |
| `author_last_name`      | The post author last name                                                |
| `caption`               | The post excerpt                                                         |
| `category`              | All post category names or the term title                                |
| `category_description`  | The term description                                                     |
| `category_title`        | The term title                                                           |
| `currentdate`           | [tsfep-gimmick type="date" format="F j, Y"]                              |
| `currentday`            | [tsfep-gimmick type="date" format="j"]                                   |
| `currentmonth`          | [tsfep-gimmick type="date" format="F"]                                   |
| `currentyear`           | [tsfep-gimmick type="date" format="Y"]                                   |
| `date`                  | The post publishing date                                                 |
| `excerpt`               | The post excerpt, trimmed via [TSF's AI](https://tsf.fyi/kb/a/65)        |
| `excerpt_only`          | The full post excerpt                                                    |
| `id`                    | The post or term ID                                                      |
| `modified`              | The post modified date                                                   |
| `name`                  | The post author display name                                             |
| `parent_title`          | The post parent title (not meta title)                                   |
| `permalink`             | The post or term permalink                                               |
| `post_content`          | The post content (why would anyone...)                                   |
| `post_year`             | The post publishing year (e.g., [tsfep-gimmick type="date" format="Y"])  |
| `post_month`            | The post publishing month (e.g., [tsfep-gimmick type="date" format="F"]) |
| `post_day`              | The post publishing day (e.g., [tsfep-gimmick type="date" format="j"])   |
| `pt_plural`             | The current post type plural name (e.g., Posts)                          |
| `pt_single`             | The current post type singular name (e.g., Post)                         |
| `sep`                   | The title separator (`&middot;`, `|`, `&gt;`, etc.)                      |
| `sitedesc`              | The blog description                                                     |
| `sitename`              | The blog name                                                            |
| `tag`                   | All post tag names, or the term title                                    |
| `tag_description`       | The term description                                                     |
| `term_description`      | The term description                                                     |
| `term_title`            | The term title                                                           |
| `title`                 | The post or term title                                                   |
| `user_description`      | The post author biography                                                |
| `userid`                | The post author user ID                                                  |
| `count*`                | _(preserved)_                                                            |
| `customfield`           | _(preserved)_                                                            |
| `customterm`            | _(preserved)_                                                            |
| `currenttime*`          | _(preserved)_                                                            |
| `focuskw`               | _(preserved)_                                                            |
| `primary_category`      | _(preserved)_                                                            |
| `page`                  | _(preserved)_                                                            |
| `pagenumber`            | _(preserved)_                                                            |
| `pagetotal`             | _(preserved)_                                                            |
| `wc_brand`              | _(preserved)_                                                            |
| `wc_price`              | _(preserved)_                                                            |
| `wc_shortdesc`          | _(preserved)_                                                            |
| `wc_sku`                | _(preserved)_                                                            |
| `ct_*`                  | _(preserved)_                                                            |
| `cf_*`                  | _(preserved)_                                                            |
| `org_name`              | _(preserved)_                                                            |
| `org_logo`              | _(preserved)_                                                            |
| `org_url`               | _(preserved)_                                                            |
| `filename`              | _(preserved)_                                                            |

#### Not everything is transformed

Only items listed in the table above will be transformed. All text is preserved, unless the text acts like syntax, that will be removed.

Some data in the table is treated as "_(preserved)_" because these types are dependent on their context. For example, `%%page%%` will stay `%%page%%`. The remaining preserved types we cannot replace reliably. The SEO Framework can hint you later for manual correction of these type.

When a meta title is transformed, importer will check the "Remove site title" option for the post or term.

### What data is transported?

Not all SEO plugins are alike. The SEO Framework (TSF) takes a straightforward approach to SEO, backed only by scientific data. It is why you find fewer features in our plugin, but each feature is far more evolved and polished.

#### Yoast SEO

| What                    | Table      | Index                                | Transform        |
|:----------------------- |:---------- |:------------------------------------ |:-----------------|
| Meta title&#x2a;        | `postmeta` | `_yoast_wpseo_title`                 | &#x2713;&#xFE0F; |
| Meta description        | `postmeta` | `_yoast_wpseo_metadesc`              | &#x2713;&#xFE0F; |
| Open Graph title        | `postmeta` | `_yoast_wpseo_opengraph-title`       | &#x2713;&#xFE0F; |
| Open Graph description  | `postmeta` | `_yoast_wpseo_opengraph-description` | &#x2713;&#xFE0F; |
| Open Graph image URL    | `postmeta` | `_yoast_wpseo_opengraph-image`       | &ndash;          |
| Open Graph image ID     | `postmeta` | `_yoast_wpseo_opengraph-image-id`    | &ndash;          |
| Twitter title           | `postmeta` | `_yoast_wpseo_twitter-title`         | &#x2713;&#xFE0F; |
| Twitter description     | `postmeta` | `_yoast_wpseo_twitter-description`   | &#x2713;&#xFE0F; |
| Canonical URL           | `postmeta` | `_yoast_wpseo_canonical`             | &ndash;          |
| Redirect URL            | `postmeta` | `_yoast_wpseo_redirect`              | &ndash;          |
| Robots noindex          | `postmeta` | `_yoast_wpseo_meta-robots-noindex`   | &#x2713;&#xFE0F; |
| Robots nofollow         | `postmeta` | `_yoast_wpseo_meta-robots-nofollow`  | &#x2713;&#xFE0F; |
| Robots noarchive        | `postmeta` | `_yoast_wpseo_meta-robots-adv`       | &#x2713;&#xFE0F; |
| Primary term ID&dagger; | `postmeta` | `_yoast_wpseo_primary_{$taxonomy}`   | &ndash;          |
| Term metadata&Dagger;   | `options`  | `wpseo_taxonomy_meta`                | &#x2713;&#xFE0F; |

_&#x2a; When transformed, the importer will set a flag for TSF to add the site title. You can remove this flag via the post edit screen._ <br>
_&dagger; To transport primary term IDs, the taxonomy must be active before Transport can detect the data. For example, WooCommerce must be active to transport Primary Product Category IDs for Products._ <br>
_&Dagger; This includes: title, description, Open Graph title, Open Graph description, Twitter title, Twitter description, Canonical URL, and Robots noindex._

#### Yoast SEO cleanup

The following data will be irretrievably deleted from your database; doing this will improve your website performance.

| What                     | Table      | Index                                         | Reason                         |
|:------------------------ |:---------- |:--------------------------------------------- |:------------------------------ |
| Twitter image URL        | `postmeta` | `_yoast_wpseo_twitter-image`                  | TSF falls back to Open Graph   |
| Twitter image ID         | `postmeta` | `_yoast_wpseo_twitter-image-id`               | TSF falls back to Open Graph   |
| Focus keyword            | `postmeta` | `_yoast_wpseo_focuskw`                        | TSF uses a different system    |
| Cornerstone content flag | `postmeta` | `_yoast_wpseo_is_cornerstone`                 | Best done via SEM software     |
| SEO score                | `postmeta` | `_yoast_wpseo_linkdex`                        | Commercial, selling your data  |
| Readability score        | `postmeta` | `_yoast_wpseo_content_score`                  | Unscientific feature           |
| WordProof timestamp      | `postmeta` | `_yoast_wpseo_wordproof_timestamp`            | Commercial, selling your data  |
| Reading time             | `postmeta` | `_yoast_wpseo_estimated-reading-time-minutes` | Meritless feature              |
| Breadcrumbs title        | `postmeta` | `_yoast_wpseo_bctitle`                        | Does not belong in SEO plugins |
| Schema.org page type     | `postmeta` | `_yoast_wpseo_schema_page_type`               | TSF uses a different system    |
| Schema.org article type  | `postmeta` | `_yoast_wpseo_schema_article_type`            | TSF uses a different system    |
| Zappier trigger flag     | `postmeta` | `_yoast_wpseo_zapier_trigger_sent`            | Commercial, selling your data  |

#### Rank Math

| What                        | Table      | Index                            | Transform        |
|:--------------------------- |:---------- |:-------------------------------- |:---------------- |
| Meta title&#x2a;            | `postmeta` | `rank_math_title`                | &#x2713;&#xFE0F; |
| Meta description            | `postmeta` | `rank_math_description`          | &#x2713;&#xFE0F; |
| Open Graph title            | `postmeta` | `rank_math_facebook_title`       | &#x2713;&#xFE0F; |
| Open Graph description      | `postmeta` | `rank_math_facebook_description` | &#x2713;&#xFE0F; |
| Open Graph image URL        | `postmeta` | `rank_math_facebook_image`       | &ndash;          |
| Open Graph image ID         | `postmeta` | `rank_math_facebook_image_id`    | &ndash;          |
| Twitter title&dagger;       | `postmeta` | `rank_math_twitter_title`        | &#x2713;&#xFE0F; |
| Twitter description&dagger; | `postmeta` | `rank_math_twitter_description`  | &#x2713;&#xFE0F; |
| Canonical URL               | `postmeta` | `rank_math_canonical_url`        | &ndash;          |
| Robots metadata             | `postmeta` | `rank_math_robots`               | &#x2713;&#xFE0F; |
| Primary term ID&Dagger;     | `postmeta` | `rank_math_primary_{$taxonomy}`  | &ndash;          |
| Meta title&#x2a;            | `termmeta` | `rank_math_title`                | &#x2713;&#xFE0F; |
| Meta description            | `termmeta` | `rank_math_description`          | &#x2713;&#xFE0F; |
| Open Graph title            | `termmeta` | `rank_math_facebook_title`       | &#x2713;&#xFE0F; |
| Open Graph description      | `termmeta` | `rank_math_facebook_description` | &#x2713;&#xFE0F; |
| Open Graph image URL        | `termmeta` | `rank_math_facebook_image`       | &ndash;          |
| Open Graph image ID         | `termmeta` | `rank_math_facebook_image_id`    | &ndash;          |
| Twitter title&dagger;       | `termmeta` | `rank_math_twitter_title`        | &#x2713;&#xFE0F; |
| Twitter description&dagger; | `termmeta` | `rank_math_twitter_description`  | &#x2713;&#xFE0F; |
| Canonical URL               | `termmeta` | `rank_math_canonical_url`        | &ndash;          |
| Robots metadata             | `termmeta` | `rank_math_robots`               | &#x2713;&#xFE0F; |

_&#x2a; When transformed, the importer will set a flag for TSF to add the site title. You can remove this flag via the post edit screen._ <br>
_&dagger; Conditional: Twitter metadata is only transported when enabled for the post or term in Rank Math._
_&Dagger; To transport primary term IDs, the taxonomy must be active before Transport can detect the data. For example, WooCommerce must be active to transport Primary Product Category IDs for Products._ <br>

#### Rank Math cleanup

The following data will be irretrievably deleted from your database; doing this will improve your website performance.

| What                     | Table      | Index                                     | Reason                            |
|:------------------------ |:---------- |:----------------------------------------- |:--------------------------------- |
| Disable Twitter input    | `postmeta` | `rank_math_twitter_use_facebook`          | TSF determines this automatically |
| Twitter image URL        | `postmeta` | `rank_math_twitter_image`                 | TSF falls back to Open Graph      |
| Twitter image ID         | `postmeta` | `rank_math_twitter_image_id`              | TSF falls back to Open Graph      |
| Twitter Card type        | `postmeta` | `rank_math_twitter_card_type`             | Not in TSF, micromanagement       |
| Focus keyword            | `postmeta` | `rank_math_focus_keyword`                 | TSF uses a different system       |
| Pillar content           | `postmeta` | `rank_math_pillar_content`                | Best done via SEM software        |
| SEO score                | `postmeta` | `rank_math_seo_score`                     | Unscientific feature              |
| Open Graph image overlay | `postmeta` | `rank_math_facebook_enable_image_overlay` | Deceptive practice                |
| Open Graph image overlay | `postmeta` | `rank_math_facebook_image_overlay`        | Deceptive practice                |
| Twitter image overlay    | `postmeta` | `rank_math_twitter_enable_image_overlay`  | Deceptive practice                |
| Twitter image overlay    | `postmeta` | `rank_math_twitter_image_overlay`         | Deceptive practice                |
| Robots copyright         | `postmeta` | `rank_math_advanced_robots`               | Broken feature                    |
| Breadcrumbs title        | `postmeta` | `rank_math_breadcrumb_title`              | Does not belong in SEO plugins    |
| Disable Twitter input    | `termmeta` | `rank_math_twitter_use_facebook`          | TSF determines this automatically |
| Twitter image URL        | `termmeta` | `rank_math_twitter_image`                 | TSF falls back to Open Graph      |
| Twitter image ID         | `termmeta` | `rank_math_twitter_image_id`              | TSF falls back to Open Graph      |
| Twitter Card type        | `termmeta` | `rank_math_twitter_card_type`             | Not in TSF, micromanagement       |
| Focus keyword            | `termmeta` | `rank_math_focus_keyword`                 | Meritless feature                 |
| Open Graph image overlay | `termmeta` | `rank_math_facebook_enable_image_overlay` | Deceptive practice                |
| Open Graph image overlay | `termmeta` | `rank_math_facebook_image_overlay`        | Deceptive practice                |
| Twitter image overlay    | `termmeta` | `rank_math_twitter_enable_image_overlay`  | Deceptive practice                |
| Twitter image overlay    | `termmeta` | `rank_math_twitter_image_overlay`         | Deceptive practice                |
| Robots copyright         | `termmeta` | `rank_math_advanced_robots`               | Broken feature                    |
| Breadcrumbs title        | `termmeta` | `rank_math_breadcrumb_title`              | Does not belong in SEO plugins    |
| Content AI score         | `termmeta` | `rank_math_contentai_score`               | Unscientific feature              |

## Changelog

### 1.0.0

[tsfep-release time="-1"]

* Initial extension beta release.
