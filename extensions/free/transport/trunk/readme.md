# Transport
Location: https://theseoframework.com/extensions/transport/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension migrates plugin SEO metadata from Yoast SEO, Rank Math, and SEOPress to The SEO Framework.

## Overview

### Import with ease

Used another WordPress SEO plugin, like Yoast SEO, Rank Math, or SEOPress, before you found that The SEO Framework is a better fit? Now you can finally migrate all your meticulously crafted post and term metadata.

#### Transform '%%currentdate%%' to ‘[tsfep-gimmick type="date" format="F j, Y"]’

The importer not only moves your old metadata but also transforms: Complex and difficult-to-understand syntax becomes human-readable.
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

First, select the plugin you want to import from. Transport will then present you with the types of data you can import. Hit "Import," and Transport will take care of the rest.

The importer keeps track of how many database items are found per data type; it will recount from 0 when it moves to the next data type. You can see this in real-time at the logger.

### Logger

The logger will display in real-time the progress of all data transactions.

Because web browsers become slow at about 32&nbsp;000 characters (18 book pages), the logger trims chunks of old log data to stay beneath that number.
You can hit "Copy log" to grab what's in the logger.

If you find an issue, please send us a [support email](https://theseoframework.com/support/) with the log attached.

## FAQ

### Supported plugins for import

The importer currently supports Yoast SEO, Rank Math, and SEOPress. Support for AIOSEO is coming soon&trade;.

### Should I create a backup before transporting?

Yes.

#### Really?

Yes. Transport will irreversibly alter and irretrievably delete metadata; both are intended features.

We recommend transporting only when you're sure you want to stay with The SEO Framework.
Otherwise, you should keep a backup ready in case you want to go back (we cannot comprehend why anyone would, though).

### What data is transformed?

All titles and descriptions pass through the transformer. This transformer takes syntax, such as `%archive_title%`, and transforms those as the plugin you're migrating from would.

After transformation, repeating separators will be coalesced (`text | | | text` becomes `text | text`), and stray separators and spaces will be trimmed from either side of the title or description.

#### Not everything is transformed

Only syntax listed in the transformation tables below are transformed. Simple text is unaffected and transported as-is.

Some syntax in the tables below are treated as "_(preserved)_" because these types depend on their context. For example, `%page%` will stay `%page%`. We cannot set `page 1` because that'd be wrong for page 2. The SEO Framework will hint you later via the SEO Bar so you can manually correct the preserved syntax.

Text that follows the transformation syntax rules of the plugin, but isn't listed in its table, will be removed. So, if you put `%not-exist%` in your title, that text will be gone after transporting.

Post types or terms that don't support syntax functionality will have the text removed as well. For example, categories do not support post dates; so, `%post_date%` will be removed if it's is found in a category title.

When a meta title is transformed, the importer will check the "Remove site title" option for the post or term. This prevents some further augmentation from The SEO Framework.

To summarize, `hello %sep% %not-exists% %sep% world %page% ` becomes `hello | world %page%`.

### What data is transported?

Not all SEO plugins are alike. The SEO Framework (TSF) takes a straightforward approach to SEO, backed only by scientific data. It is why you find fewer features in our plugin, but each component is far more evolved and polished.

### Yoast SEO

The following data is transported from Yoast SEO.

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
| Robots noindex          | `postmeta` | `_yoast_wpseo_meta-robots-noindex`   | &#x2713;&#xFE0F; |
| Robots nofollow         | `postmeta` | `_yoast_wpseo_meta-robots-nofollow`  | &#x2713;&#xFE0F; |
| Robots noarchive        | `postmeta` | `_yoast_wpseo_meta-robots-adv`       | &#x2713;&#xFE0F; |
| Primary term ID&dagger; | `postmeta` | `_yoast_wpseo_primary_{$taxonomy}`   | &ndash;          |
| Term metadata&Dagger;   | `options`  | `wpseo_taxonomy_meta`                | &#x2713;&#xFE0F; |

_&#x2a; When found, the importer will set a flag for TSF to remove appending of the site title. You can uncheck this flag via the post edit screen._ <br>
_&dagger; To transport primary term IDs, the taxonomy must be active before Transport can detect the data. For example, WooCommerce must be active to transport Primary Product Category IDs for Products._ <br>
_&Dagger; This includes: title, description, Open Graph title, Open Graph description, Twitter title, Twitter description, Canonical URL, and Robots noindex._

#### Yoast SEO cleanup

Transporter will irretrievably delete the following data from your database, improving your website performance.

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

#### Yoast SEO transformations

| Syntax                  | Becomes                                                                  |
|:----------------------- |:------------------------------------------------------------------------ |
| `archive_title`         | The term title                                                           |
| `author_first_name`     | The post author's first name                                             |
| `author_last_name`      | The post author's last name                                              |
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
| `post_content`          | The post content (why would anyone...)                                   |
| `post_year`             | The post publishing year (e.g., [tsfep-gimmick type="date" format="Y"])  |
| `post_month`            | The post publishing month (e.g., [tsfep-gimmick type="date" format="F"]) |
| `post_day`              | The post publishing day (e.g., [tsfep-gimmick type="date" format="j"])   |
| `pt_plural`             | The current post type plural name (e.g., Posts)                          |
| `pt_single`             | The current post type singular name (e.g., Post)                         |
| `sep`                   | The title separator (`·`, `|`, `>`, etc.)                                |
| `sitedesc`              | The blog description                                                     |
| `sitename`              | The blog name                                                            |
| `tag`                   | All post tag names or the term title                                     |
| `tag_description`       | The term description                                                     |
| `term_description`      | The term description                                                     |
| `term_title`            | The term title                                                           |
| `title`                 | The post title                                                           |
| `user_description`      | The post author biography                                                |
| `userid`                | The post author user ID                                                  |
| `currenttime`           | _(preserved)_                                                            |
| `focuskw`               | _(preserved)_                                                            |
| `primary_category`      | _(preserved)_                                                            |
| `page`                  | _(preserved)_                                                            |
| `pagenumber`            | _(preserved)_                                                            |
| `pagetotal`             | _(preserved)_                                                            |
| `permalink`             | _(preserved)_                                                            |
| `wc_brand`              | _(preserved)_                                                            |
| `wc_price`              | _(preserved)_                                                            |
| `wc_shortdesc`          | _(preserved)_                                                            |
| `wc_sku`                | _(preserved)_                                                            |
| `ct_*`                  | _(preserved)_                                                            |
| `cf_*`                  | _(preserved)_                                                            |

### Rank Math

The following data is transported from Rank Math.

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

_&#x2a; When found, the importer will set a flag for TSF to remove appending of the site title. You can uncheck this flag via the post edit screen._ <br>
_&dagger; Conditional: Twitter metadata is only transported when enabled for the post or term in Rank Math._ <br>
_&Dagger; To transport primary term IDs, the taxonomy must be active before Transport can detect the data. For example, WooCommerce must be active to transport Primary Product Category IDs for Products._

#### Rank Math cleanup

Transporter will irretrievably delete the following data from your database, improving your website performance.

| What                     | Table      | Index                                     | Reason                            |
|:------------------------ |:---------- |:----------------------------------------- |:--------------------------------- |
| Open Graph image overlay | `postmeta` | `rank_math_facebook_enable_image_overlay` | Deceptive practice                |
| Open Graph image overlay | `postmeta` | `rank_math_facebook_image_overlay`        | Deceptive practice                |
| Disable Twitter input    | `postmeta` | `rank_math_twitter_use_facebook`          | TSF determines this automatically |
| Twitter image URL        | `postmeta` | `rank_math_twitter_image`                 | TSF falls back to Open Graph      |
| Twitter image ID         | `postmeta` | `rank_math_twitter_image_id`              | TSF falls back to Open Graph      |
| Twitter Card type        | `postmeta` | `rank_math_twitter_card_type`             | Not in TSF, micromanagement       |
| Twitter image overlay    | `postmeta` | `rank_math_twitter_enable_image_overlay`  | Deceptive practice                |
| Twitter image overlay    | `postmeta` | `rank_math_twitter_image_overlay`         | Deceptive practice                |
| Robots copyright         | `postmeta` | `rank_math_advanced_robots`               | Broken feature                    |
| Breadcrumbs title        | `postmeta` | `rank_math_breadcrumb_title`              | Does not belong in SEO plugins    |
| Focus keyword            | `postmeta` | `rank_math_focus_keyword`                 | TSF uses a different system       |
| Pillar content           | `postmeta` | `rank_math_pillar_content`                | Best done via SEM software        |
| SEO score                | `postmeta` | `rank_math_seo_score`                     | Unscientific feature              |
| Content AI score         | `postmeta` | `rank_math_contentai_score`               | Unscientific feature              |
| Open Graph image overlay | `termmeta` | `rank_math_facebook_enable_image_overlay` | Deceptive practice                |
| Open Graph image overlay | `termmeta` | `rank_math_facebook_image_overlay`        | Deceptive practice                |
| Disable Twitter input    | `termmeta` | `rank_math_twitter_use_facebook`          | TSF determines this automatically |
| Twitter image URL        | `termmeta` | `rank_math_twitter_image`                 | TSF falls back to Open Graph      |
| Twitter image ID         | `termmeta` | `rank_math_twitter_image_id`              | TSF falls back to Open Graph      |
| Twitter Card type        | `termmeta` | `rank_math_twitter_card_type`             | Not in TSF, micromanagement       |
| Twitter image overlay    | `termmeta` | `rank_math_twitter_enable_image_overlay`  | Deceptive practice                |
| Twitter image overlay    | `termmeta` | `rank_math_twitter_image_overlay`         | Deceptive practice                |
| Robots copyright         | `termmeta` | `rank_math_advanced_robots`               | Broken feature                    |
| Breadcrumbs title        | `termmeta` | `rank_math_breadcrumb_title`              | Does not belong in SEO plugins    |
| Focus keyword            | `termmeta` | `rank_math_focus_keyword`                 | TSF uses a different system       |

#### Rank Math transformations

| Syntax                   | Becomes                                                           |
|:------------------------ |:----------------------------------------------------------------- |
| `category`               | The post's first category name or the term title                  |
| `categories`             | All post category names or the term title                         |
| `currentdate`            | [tsfep-gimmick type="date" format="F j, Y"]                       |
| `currentday`             | [tsfep-gimmick type="date" format="j"]                            |
| `currentmonth`           | [tsfep-gimmick type="date" format="F"]                            |
| `currentyear`            | [tsfep-gimmick type="date" format="Y"]                            |
| `date`                   | The post publishing date                                          |
| `excerpt`                | The post excerpt, trimmed via [TSF's AI](https://tsf.fyi/kb/a/65) |
| `excerpt_only`           | The full post excerpt                                             |
| `id`                     | The post or term ID                                               |
| `modified`               | The post modified date                                            |
| `name`                   | The post author display name                                      |
| `parent_title`           | The post parent title (not meta title)                            |
| `post_author`            | The post author display name                                      |
| `pt_plural`              | The current post type plural name (e.g., Posts)                   |
| `pt_single`              | The current post type singular name (e.g., Post)                  |
| `seo_title`              | The generated title (not SEO title)                               |
| `seo_description`        | The post excerpt, trimmed via [TSF's AI](https://tsf.fyi/kb/a/65) |
| `sep`                    | The title separator (`·`, `|`, `>`, etc.)                         |
| `sitedesc`               | The blog description                                              |
| `sitename`               | The blog name                                                     |
| `tag`                    | The post's first tag name or the term title                       |
| `tags`                   | All post tag names or the term title                              |
| `term`                   | The term title                                                    |
| `term_description`       | The term description                                              |
| `title`                  | The post title                                                    |
| `user_description`       | The post author biography                                         |
| `userid`                 | The post author user ID                                           |
| `currenttime`            | _(preserved)_                                                     |
| `filename`               | _(preserved)_                                                     |
| `focuskw`                | _(preserved)_                                                     |
| `group_desc`             | _(preserved)_                                                     |
| `group_name`             | _(preserved)_                                                     |
| `keywords`               | _(preserved)_                                                     |
| `org_name`               | _(preserved)_                                                     |
| `org_logo`               | _(preserved)_                                                     |
| `org_url`                | _(preserved)_                                                     |
| `page`                   | _(preserved)_                                                     |
| `pagenumber`             | _(preserved)_                                                     |
| `pagetotal`              | _(preserved)_                                                     |
| `post_thumbnail`         | _(preserved)_                                                     |
| `primary_category`       | _(preserved)_                                                     |
| `primary_taxonomy_terms` | _(preserved)_                                                     |
| `url`                    | _(preserved)_                                                     |
| `wc_brand`               | _(preserved)_                                                     |
| `wc_price`               | _(preserved)_                                                     |
| `wc_shortdesc`           | _(preserved)_                                                     |
| `wc_sku`                 | _(preserved)_                                                     |
| `categories(*)`          | _(preserved)_                                                     |
| `count(*)`               | _(preserved)_                                                     |
| `currenttime(*)`         | _(preserved)_                                                     |
| `customfield(*)`         | _(preserved)_                                                     |
| `customterm(*)`          | _(preserved)_                                                     |
| `customterm_desc(*)`     | _(preserved)_                                                     |
| `date(*)`                | _(preserved)_                                                     |
| `modified(*)`            | _(preserved)_                                                     |
| `tags(*)`                | _(preserved)_                                                     |


### SEOPress

It took us a month to comb through SEOPress's massive spaghetti code. After making sense of it all, we concluded that SEOPress is dangerous software and we are glad you are deciding to make the switch. The documentation below is more accurate than theirs.

The following data is transported from SEOPress.

| What                        | Table      | Index                                   | Transform        |
|:--------------------------- |:---------- |:--------------------------------------- |:---------------- |
| Meta title&#x2a;            | `postmeta` | `_seopress_titles_title`                | &#x2713;&#xFE0F; |
| Meta description            | `postmeta` | `_seopress_titles_desc`                 | &#x2713;&#xFE0F; |
| Open Graph title            | `postmeta` | `_seopress_social_fb_title`             | &#x2713;&#xFE0F; |
| Open Graph description      | `postmeta` | `_seopress_social_fb_desc`              | &#x2713;&#xFE0F; |
| Open Graph image URL        | `postmeta` | `_seopress_social_fb_img`               | &ndash;          |
| Open Graph image ID         | `postmeta` | `_seopress_social_fb_img_attachment_id` | &ndash;          |
| Twitter title               | `postmeta` | `_seopress_social_twitter_title`        | &#x2713;&#xFE0F; |
| Twitter description         | `postmeta` | `_seopress_social_twitter_desc`         | &#x2713;&#xFE0F; |
| Canonical URL               | `postmeta` | `_seopress_robots_canonical`            | &ndash;          |
| Robots noindex              | `postmeta` | `_seopress_robots_index`                | &#x2713;&#xFE0F; |
| Robots nofollow             | `postmeta` | `_seopress_robots_follow`               | &#x2713;&#xFE0F; |
| Robots noarchive            | `postmeta` | `_seopress_robots_archive`              | &#x2713;&#xFE0F; |
| 301 redirect&dagger;        | `postmeta` | `_seopress_redirections_value`          | &ndash;          |
| Primary term ID&Dagger;     | `postmeta` | `_seopress_robots_primary_cat`          | &ndash;          |
| Meta title&#x2a;            | `termmeta` | `_seopress_titles_title`                | &#x2713;&#xFE0F; |
| Meta description            | `termmeta` | `_seopress_titles_desc`                 | &#x2713;&#xFE0F; |
| Open Graph title            | `termmeta` | `_seopress_social_fb_title`             | &#x2713;&#xFE0F; |
| Open Graph description      | `termmeta` | `_seopress_social_fb_desc`              | &#x2713;&#xFE0F; |
| Open Graph image URL        | `termmeta` | `_seopress_social_fb_img`               | &ndash;          |
| Open Graph image ID         | `termmeta` | `_seopress_social_fb_img_attachment_id` | &ndash;          |
| Twitter title               | `termmeta` | `_seopress_social_twitter_title`        | &#x2713;&#xFE0F; |
| Twitter description         | `termmeta` | `_seopress_social_twitter_desc`         | &#x2713;&#xFE0F; |
| Canonical URL               | `termmeta` | `_seopress_robots_canonical`            | &ndash;          |
| Robots noindex              | `termmeta` | `_seopress_robots_index`                | &#x2713;&#xFE0F; |
| Robots nofollow             | `termmeta` | `_seopress_robots_follow`               | &#x2713;&#xFE0F; |
| Robots noarchive            | `termmeta` | `_seopress_robots_archive`              | &#x2713;&#xFE0F; |
| 301 redirect&dagger;        | `termmeta` | `_seopress_redirections_value`          | &ndash;          |

_&#x2a; When found, the importer will set a flag for TSF to remove appending of the site title. You can uncheck this flag via the post edit screen._ <br>
_&dagger; Conditional: Redirection is only transported when you activate the extraneous checkbox in SEOPress, and when the redirection is for everyone (both logged in and logged out visitors)._ <br>
_&Dagger; SEOPress haphazardly supports only native Categories and WooCommerce Product Categories, and allows you to assign non-existing terms. TSF supports all taxonomies and has safeguards built-in against the broken data from SEOPress._

#### SEOPress cleanup

SEOPress stores much data it doesn't even use itself. Their developers do not know how to abstract simple concepts; instead, they duplicated their metadata code over five times, acting a little differently every time you interact with it.

Transporter will irretrievably delete the following data from your database, improving your website performance.

| What                     | Table      | Index                                        | Reason                            |
|:------------------------ |:---------- |:-------------------------------------------- |:--------------------------------- |
| Open Graph image width   | `postmeta` | `_seopress_social_fb_img_width`              | Already stored by WordPress       |
| Open Graph image height  | `postmeta` | `_seopress_social_fb_img_height`             | Already stored by WordPress       |
| Twitter image URL        | `postmeta` | `_seopress_social_twitter_img`               | TSF falls back to Open Graph      |
| Twitter image ID         | `postmeta` | `_seopress_social_twitter_img_attachment_id` | TSF falls back to Open Graph      |
| Twitter image width      | `postmeta` | `_seopress_social_twitter_img_width`         | Already stored by WordPress       |
| Twitter image height     | `postmeta` | `_seopress_social_twitter_img_height`        | Already stored by WordPress       |
| Breadcrumbs title        | `postmeta` | `_seopress_robots_breadcrumbs`               | Does not belong in SEO plugins    |
| Robots nosnippet         | `postmeta` | `_seopress_robots_snippet`                   | This is the opposite of SEO       |
| Robots noodp             | `postmeta` | `_seopress_robots_odp`                       | ODP was dissolved in 2017         |
| Robots noimageindex      | `postmeta` | `_seopress_robots_imageindex`                | This is the opposite of SEO       |
| Redirection enabled      | `postmeta` | `_seopress_redirections_enabled`             | Needless friction for users       |
| Redirection conditional  | `postmeta` | `_seopress_redirections_logged_status`       | Control should be elsewhere       |
| Redirection type         | `postmeta` | `_seopress_redirections_type`                | Only 301 should be in SEO plugins |
| Redirection parameters   | `postmeta` | `_seopress_redirections_param`               | Undescribed functionality         |
| Redirection regex        | `postmeta` | `_seopress_redirections_enabled_regex`       | Needless friction for users       |
| Focus keyword            | `postmeta` | `_seopress_analysis_target_kw`               | TSF uses a different system       |
| Focus keyword data       | `postmeta` | `_seopress_analysis_data`                    | TSF uses a different system       |
| 404 count                | `postmeta` | `seopress_404_count`                         | Should be handled off-site        |
| Open Graph image width   | `termmeta` | `_seopress_social_fb_img_width`              | Already stored by WordPress       |
| Open Graph image height  | `termmeta` | `_seopress_social_fb_img_height`             | Already stored by WordPress       |
| Twitter image URL        | `termmeta` | `_seopress_social_twitter_img`               | TSF falls back to Open Graph      |
| Twitter image ID         | `termmeta` | `_seopress_social_twitter_img_attachment_id` | TSF falls back to Open Graph      |
| Twitter image width      | `termmeta` | `_seopress_social_twitter_img_width`         | Already stored by WordPress       |
| Twitter image height     | `termmeta` | `_seopress_social_twitter_img_height`        | Already stored by WordPress       |
| Breadcrumbs title        | `termmeta` | `_seopress_robots_breadcrumbs`               | Does not belong in SEO plugins    |
| Robots nosnippet         | `termmeta` | `_seopress_robots_snippet`                   | This is the opposite of SEO       |
| Robots noimageindex      | `termmeta` | `_seopress_robots_imageindex`                | This is the opposite of SEO       |
| Redirection enabled      | `termmeta` | `_seopress_redirections_enabled`             | Needless friction for users       |
| Redirection conditional  | `termmeta` | `_seopress_redirections_logged_status`       | Control should be elsewhere       |
| Redirection type         | `termmeta` | `_seopress_redirections_type`                | Only 301 should be in SEO plugins |

#### SEOPress transformations

Many duplicated tags, some of which don't work reliably in SEOPress.

| Syntax                    | Becomes                                                           |
|:------------------------- |:----------------------------------------------------------------- |
| `_category_description`   | The term description                                              |
| `_category_title`         | The term title                                                    |
| `archive_title`           | The term title                                                    |
| `author_bio`              | The post author biography                                         |
| `author_first_name`       | The post author first name                                        |
| `author_last_name`        | The post author last name                                         |
| `author_nickname`         | The post author display name                                      |
| `currentday`              | [tsfep-gimmick type="date" format="j"]                            |
| `currentmonth`            | [tsfep-gimmick type="date" format="F"]                            |
| `currentmonth_num`        | [tsfep-gimmick type="date" format="n"]                            |
| `currentmonth_short`      | [tsfep-gimmick type="date" format="M"]                            |
| `currentyear`             | [tsfep-gimmick type="date" format="Y"]                            |
| `date`                    | The post publishing date                                          |
| `excerpt`                 | The post excerpt, trimmed via [TSF's AI](https://tsf.fyi/kb/a/65) |
| `post_author`             | The post author display name                                      |
| `post_category`           | The post's first category name or the term title                  |
| `post_content`            | The post content (why would anyone...)                            |
| `post_date`               | The post publishing date                                          |
| `post_excerpt`            | The post excerpt, trimmed via [TSF's AI](https://tsf.fyi/kb/a/65) |
| `post_modified_date`      | The post modified date                                            |
| `post_tag`                | The post's first tag name or the term title                       |
| `post_title`              | The post title                                                    |
| `sep`                     | The title separator (`·`, `|`, `>`, etc.)                         |
| `sitedesc`                | The blog description                                              |
| `sitename`                | The blog name                                                     |
| `sitetitle`               | The blog name                                                     |
| `tag_description`         | The term description                                              |
| `tag_title`               | The term title                                                    |
| `tagline`                 | The blog description                                              |
| `term_description`        | The term description                                              |
| `term_title`              | The term title                                                    |
| `title`                   | The post title                                                    |
| `wc_single_cat`           | Misnomer; all product category names for current product          |
| `wc_single_short_desc`    | The post excerpt, trimmed via [TSF's AI](https://tsf.fyi/kb/a/65) |
| `wc_single_tag`           | Misnomer; all product tag names for current product               |
| `author_website`          | _(preserved)_                                                     |
| `current_pagination`      | _(preserved)_                                                     |
| `currenttime`             | _(preserved)_                                                     |
| `post_thumbnail_url`      | _(preserved)_                                                     |
| `post_url`                | _(preserved)_                                                     |
| `target_keyword`          | _(preserved)_                                                     |
| `wc_single_price`         | _(preserved)_                                                     |
| `wc_single_price_exc_tax` | _(preserved)_                                                     |
| `wc_sku`                  | _(preserved)_                                                     |
| `_cf_*`                   | _(preserved)_                                                     |
| `_ct_*`                   | _(preserved)_ (but doesn't even work in SEOPress)                 |
| `_ucf_*`                  | _(preserved)_                                                     |

## Changelog

### 1.1.1

[tsfep-release time="-1"]

* **Fixed:** Transports now won't stop when the connection drops, as was intended.
* **Fixed:** A readable transport log is now sent when the browser doesn't support event streams.
* **Fixed:** Yoast SEO's plugin name is now correctly spelled. [Props Joost de Valk](https://github.com/sybrew/The-SEO-Framework-Extension-Manager/pull/73).

### 1.1.0

[tsfep-release time="February 7th, 2023"]

* Second beta release.
* **Added**: SEOPress transport support. Including:
	* Migration of titles, descriptions, visibility options, Open Graph, Twitter, redirections, and primary terms.
	* Support for all posts, pages, custom post types, and all terms like categories and tags.
	* Transformation of titles and descriptions; faster and more accurate than their developers can ever promise.
	* Cleanup of useless data.
* **Fixed:** Added missing title and description syntax transformations for Rank Math.
	* Among these are: `categories`, `post_author`, `seo_title`, `seo_description`, `tags`, and `term`.
	* Documented at [FAQ](#faq/rank-math-transformations).
* **Fixed:** Added missing title and description syntax preservation for Rank Math.
	* Among these are: `group_desc`, `group_name`, `keywords`, `post_thumbnail`, `primary_taxonomy_terms`, `url`, `categories(*)`,  `customterm_desc(*)`, `date(*)`, `modified(*)`, `tags(*)`.
	* Documented at [FAQ](#faq/rank-math-transformations).
* **Fixed:** When an existing post title is already present of The SEO Framework, a title present in another plugin will no longer cause Transport to check the blogname removal option.
* **Fixed:** The number of transformations and deletions in titles are now correctly added to the totals in the log.
* **Fixed:** Term cache clearing now works during title and description transformation. This feature is redundant, but the bug caused PHP notices being logged after term data was requested.

### 1.0.0

[tsfep-release time="October 4th, 2022"]

* Initial extension beta release.
