# Transport
Location: https://theseoframework.com/extensions/transport/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This extension migrates plugin SEO metadata from Yoast SEO and Rank Math to The SEO Framework.

## Overview

### Import with ease

Used another WordPress SEO plugin, like Yoast SEO or Rank Math, before you found that The SEO Framework is a better fit? Now you can finally migrate all your old metadata.

### Transform '%%currentdate%%' to ‘[tsfep-gimmick type="date" format="Y-m-d g:i a"]’

The Transport extension does not only move your old metadata, but it also transforms: Complex and difficult to understand syntax becomes human-readable.
You can learn more about what data is transformed at [FAQ](#faq/what-data-is-transported).

### Logged and loaded

Transport comes with a real-time transaction logger, so you can see your data migrate and transform in real-time. If there's an issue, you'll know about it immediately.

[tsfep-image id="1"]

## Usage

[tsfep-bundled]

### Activate Transport

First, you'll need to activate the Transport extension.

### Transport screen

Underneath the extension description, you should see a settings-link appear. You can also find the link to "Transport" under "SEO" in the admin sidebar, but you may need to refresh the admin page first.

On the Transport screen, you can start importing metadata.

## FAQ

### Should I create a backup before transporting?

Yes.

#### Really?

Yes. Transport can irreversibly alter and irretrievably delete metadata.

We recommend transporting only when you're certain you want to stay with The SEO Framework.
Otherwise, you should keep a backup ready in case you want to go back (we cannot comprehend why anyone would, though).

### What data is transported?

Not all SEO plugins are alike. The SEO Framework (TSF) takes a straightforward approach to SEO, backed only by scientific data. It is why you find fewer features in our plugin, but each feature is far more evolved and polished.

#### Yoast SEO

| What                    | Table      | Index                                | Transformed      | Reversible       |
|:----------------------- |:---------- |:------------------------------------ |:-----------------|:---------------- |
| Meta title              | `postmeta` | `_yoast_wpseo_title`                 | &#x2713;&#xFE0F; | &Dagger;         |
| Meta description        | `postmeta` | `_yoast_wpseo_metadesc`              | &#x2713;&#xFE0F; | &Dagger;         |
| Open Graph title        | `postmeta` | `_yoast_wpseo_opengraph-title`       | &#x2713;&#xFE0F; | &Dagger;         |
| Open Graph description  | `postmeta` | `_yoast_wpseo_opengraph-description` | &#x2713;&#xFE0F; | &Dagger;         |
| Open Graph image URL    | `postmeta` | `_yoast_wpseo_opengraph-image`       | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Open Graph image ID     | `postmeta` | `_yoast_wpseo_opengraph-image-id`    | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Twitter title           | `postmeta` | `_yoast_wpseo_twitter-title`         | &#x2713;&#xFE0F; | &Dagger;         |
| Twitter description     | `postmeta` | `_yoast_wpseo_twitter-description`   | &#x2713;&#xFE0F; | &Dagger;         |
| Canonical URL           | `postmeta` | `_yoast_wpseo_canonical`             | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Redirect URL            | `postmeta` | `_yoast_wpseo_redirect`              | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Robots noindex          | `postmeta` | `_yoast_wpseo_meta-robots-noindex`   | &#x2713;&#xFE0F; | &#x2713;&#xFE0F; |
| Robots nofollow         | `postmeta` | `_yoast_wpseo_meta-robots-nofollow`  | &#x2713;&#xFE0F; | &#x2713;&#xFE0F; |
| Robots noarchive        | `postmeta` | `_yoast_wpseo_meta-robots-adv`       | &#x2713;&#xFE0F; | &#x2717;&#xFE0F; |
| Primary term ID&#x2a;   | `postmeta` | `_yoast_wpseo_primary_{$taxonomy}`   | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Term metadata&dagger;   | `options`  | `wpseo_taxonomy_meta`                | &#x2717;&#xFE0F; | &#x2717;&#xFE0F; |

_&#x2a; To transport primary term IDs, the taxonomy must be active before Transport can detect the data. For example, WooCommerce must be active to transport Primary Product Category IDs for Products._ <br>
_&dagger; This includes: title, description, Open Graph title, Open Graph description, Twitter title, Twitter description, Canonical URL, and Robots noindex._ <br>
_&Dagger; This data can still be interpreted after transporting back, but it will lose its syntax._

#### Yoast SEO cleanup

The following data will be irretrievably deleted from your database; doing this will improve your website performance.

| What                | Table      | Index                                         | Reason                         |
|:------------------- |:---------- |:--------------------------------------------- |:------------------------------ |
| Twitter image URL   | `postmeta` | `_yoast_wpseo_twitter-image`                  | TSF falls back to Open Graph   |
| Twitter image ID    | `postmeta` | `_yoast_wpseo_twitter-image-id`               | TSF falls back to Open Graph   |
| Content score       | `postmeta` | `_yoast_wpseo_focuskw`                        | TSF uses different system      |
| Content score       | `postmeta` | `_yoast_wpseo_is_cornerstone`                 | Best done via SEM software     |
| Content score       | `postmeta` | `_yoast_wpseo_content_score`                  | Unscientific feature           |
| WordProof timestamp | `postmeta` | `_yoast_wpseo_wordproof_timestamp`            | Deceptive commercial feature   |
| Reading time        | `postmeta` | `_yoast_wpseo_estimated-reading-time-minutes` | Meritless feature              |
| Breadcrumbs title   | `postmeta` | `_yoast_wpseo_bctitle`                        | Does not belong in SEO plugins |

#### Rank Math

| What                      | Table      | Index                            | Transformed      | Reversible       |
|:------------------------- |:---------- |:-------------------------------- |:---------------- |:---------------- |
| Meta title                | `postmeta` | `rank_math_title`                | &#x2713;&#xFE0F; | &dagger;         |
| Meta description          | `postmeta` | `rank_math_description`          | &#x2713;&#xFE0F; | &dagger;         |
| Open Graph title          | `postmeta` | `rank_math_facebook_title`       | &#x2713;&#xFE0F; | &dagger;         |
| Open Graph description    | `postmeta` | `rank_math_facebook_description` | &#x2713;&#xFE0F; | &dagger;         |
| Open Graph image URL      | `postmeta` | `rank_math_facebook_image`       | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Open Graph image ID       | `postmeta` | `rank_math_facebook_image_id`    | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Twitter title&#x2a;       | `postmeta` | `rank_math_twitter_title`        | &#x2713;&#xFE0F; | &dagger;         |
| Twitter description&#x2a; | `postmeta` | `rank_math_twitter_description`  | &#x2713;&#xFE0F; | &dagger;         |
| Canonical URL             | `postmeta` | `rank_math_canonical_url`        | &#x2717;&#xFE0F; | &#x2713;&#xFE0F; |
| Robots metadata           | `postmeta` | `rank_math_robots`               | &#x2713;&#xFE0F; | &#x2713;&#xFE0F; |

_&#x2a; Conditional: Only Twitter metadata is transported when allowed for the post or term in Rank Math._ <br>
_&dagger; This data can still be interpreted after transporting back, but it loses its syntax._

#### Rank Math cleanup

The following data will be irretrievably deleted from your database; doing this will improve your website performance.

| What                  | Table      | Index                                     | Reason                            |
|:--------------------- |:---------- |:----------------------------------------- |:--------------------------------- |
| Disable Twitter input | `postmeta` | `rank_math_twitter_use_facebook`          | TSF determines this automatically |
| Twitter image URL     | `postmeta` | `rank_math_twitter_image`                 | TSF falls back to Open Graph      |
| Twitter image ID      | `postmeta` | `rank_math_twitter_image_id`              | TSF falls back to Open Graph      |
| Twitter Card type     | `postmeta` | `rank_math_twitter_card_type`             | Not in TSF, micromanagement       |
| Focus keyword         | `postmeta` | `rank_math_focus_keyword`                 | TSF uses different system         |
| Pillar content        | `postmeta` | `rank_math_pillar_content`                | Best done via SEM software        |
| SEO score             | `postmeta` | `rank_math_seo_score`                     | Unscientific feature              |
| Twitter image ID      | `postmeta` | `rank_math_facebook_enable_image_overlay` | Deceptive practice                |
| Content score         | `postmeta` | `rank_math_facebook_image_overlay`        | Deceptive practice                |
| WordProof timestamp   | `postmeta` | `rank_math_twitter_enable_image_overlay`  | Deceptive practice                |
| Reading time          | `postmeta` | `rank_math_twitter_image_overlay`         | Deceptive practice                |
| Robots copyright      | `postmeta` | `rank_math_advanced_robots`               | Broken feature                    |
| Breadcrumbs title     | `postmeta` | `rank_math_breadcrumb_title`              | Does not belong in SEO plugins    |

## Changelog

### 1.0.0

[tsfep-release time="-1"]

* Initial extension beta release.
