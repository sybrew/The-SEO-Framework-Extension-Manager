=== The SEO Framework - Extension Manager ===
Contributors: Cybr
Tags: seo, extensions, local, keyword, articles, monitor, modules, schema, honeypot, amp, title, the seo framework
Requires at least: 5.5
Tested up to: 5.9
Requires PHP: 5.6.5
Requires TSF: 4.2.4
Stable tag: 2.5.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add more powerful SEO features to The SEO Framework right from your WordPress dashboard. No sign-up is required.

== Description ==

**Advanced and powerful SEO.**
**Delivered through extension for [The SEO Framework](https://wordpress.org/plugins/autodescription/).**

The Extension Manager is a WordPress plugin that gives access to more advanced and powerful SEO for The SEO Framework via extensions.

= Privacy =

View our detailed [Plugin Privacy Policy](https://theseoframework.com/privacy-policy/#pluginprivacyinformation).

Do you have questions about privacy? Feel free to [contact us](https://theseoframework.com/contact/).

= Translation credits =

* Portuguese (Brasil): [Douglas Ferraz](https://profiles.wordpress.org/douglasferraz89/)
* Russian: [Vitaliy Ralle](https://profiles.wordpress.org/vit-1/)
* Spanish: [Manuel Ballesta Ruiz](https://profiles.wordpress.org/mbrsolution/)
* Turkish: [Mehmet Genç](https://profiles.wordpress.org/gncmhmt/)

= Contributing =

Learn [how to contribute](https://tsf.fyi/contributing).

== Installation ==

Please refer to [the installation instructions on our website](https://kb.theseoframework.com/kb/how-to-install-the-extension-manager/).

== Changelog ==

= 2.6.0 =

* Touched up the interface, it's now more compact and easier on your eyes.
* Now relies on The SEO Framework's JavaScript availability test, instead of WordPress's, making unresponsive interfaces a thing of the past.
* Modernized code, especially JavaScript, improving UI responsiveness significantly.

* TODO Add index.php files to extension top-folders
* TODO require TSF 4.2+
* TODO Use tsf() insteadof the_seo_framework()
* TODO use API functions of TSF (memo, has_run, isset()?..: et al.)
* TODO refactor coalesce_var to PHP 7.0+.
* TODO Start requiring PHP 7.2+
	* We'd love to use 7.4+ but 13% of our users are on 7.3 or lower (measured 2022/05/07).
		* Otto said we'd have to learn from <https://wordpress.org/about/stats/> because it's leading. It's only off by 40%.
			* Let's henceforth rely on our data. TODO remeasure, compare change.
* Moved TSF installation hanlder to a different file.
* Improved letter spacing from logos.
TODO remove png files, all browsers support svg now.
	* TSF site already dropped support.
	* Use `<use>` like on TSF site for improved painting performance?
TODO move get_view() to trait, using prescribed base URL.
TODO instead of "defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $_class = TSF_Extension_Manager\Extension\Transport\get_active_class() and ", try a secret (for all extensions).
TODO 'a' . $b -> "a$b" (PHP)
TODO 'a' + b -> `a${b}` (JS) (.e.g. '.' + ...)
TODO implement views trait.
TODO remove typehinting
TODO introduced tsfem()
	* Migrate tsf_extension_manager() calls to tsfem().
TODO remove trends pane... we planned to add our blog items there, but that never came to fruition.
	-> We kept it there to visually balance the page.
		-> Should we let the extensions wrap side-by-side instead? flex base 400px, stretch to fit?
			-> Copy from tsf.fyi/e?
TODO <?php echo ... ?> -> <?= ?>
	also <?php print() ?> -> <?= ?>
TODO POT file. (also update related github)
TODO <el method="post"> -> <el method=post>
TODO make Traits autoloadable? -> The Construct_* part is annoying -> \Construct\?. Extension_* needs to become \Extension\
	-> `use \TSF_Extension_Manager\Traits\{Construct_Master_Once_Interface,Time,UI,Extension_Options,Extension_Forms,Error};`
TODO //= //? //* -> //
TODO de-jQueryfy?
	-> Especially form.js
TODO function(){} => ()=>{}
TODO coalesce_var() => ??
TODO /isset\( (.*?) \) \? \1/ -> ??
TODO ([a-zA-Z0-9_-]+)\s*=\s*(\1)\b\s*\|\| -> ||=

TODO add grid display to importer options...

TODO convert post metadata from double-serialized to single-serialized (with perhaps WP interfering?)
	-> We took control because WP was causing issues (which?), are those issues resolved?
TODO convert all serialized objects to JSON for future parsing, such as requesting updates. This improves security on OUR servers, not the users.
	-> Increment API version number.

TODO fix notice bounce (reintroduced for we ditched the stagnant :empty selector)
TODO fewer jQuery animations, more CSS animations.

TODO (FIXED, clean up) instead of a fancy observer on logger, we might simply just test if user is scrolled all the way to the bottom, and if so, append data and instantly scroll down.

TODO Reevaluate get_view()'s implicated extract() and the use of get_defined_vars()
	- Neither of these can be populated by the user, still, they are an exploit waiting to happen.

TODO we use hrtime(), PHP 7.3+.... ooops?

= 2.5.3 =

**Release date:**

* May 2nd, 2022

**Feature highlights:**

* Improved overall performance of the plugin by refactoring various parts using our latest coding standards.

**API Updates:**

* Jan 31st, 2022: Duplicated domain activations no longer count toward the activation limit.
* Jan 31st, 2022: Duplicated instance activations now automatically switch sites -- this helps ease migrating from staging to production, and vice versa.

**Updated extensions:**

* [Articles at version 2.2.0](https://theseoframework.com/extensions/articles/#changelog)

**Detailed log**

* **Improved:** WordPress update nags no longer cause a shadow effect on Extension Manager pages.

View the [detailed v2.5.2 changelog](https://tsf.fyi/p/3890).

= 2.5.2 =

**Release date:**

* November 9th, 2021

**Feature highlights:**

* This update addresses a regression brought in v2.5.1 where upper-case file paths were no longer recognized for.

**Detailed log:**

View the [detailed v2.5.2 changelog](https://tsf.fyi/p/3782).

= 2.5.1 =

**Release date:**

* November 8th, 2021

**Feature highlights:**

* Extension Manager is now fully prepared for the imminent release of TSF v4.2.0.
* The 2001 error is now less likely to get invoked.
* Asset URLs are now generated correctly on Windows-based servers.

**Updated extensions:**

* [Articles at version 2.1.1](https://theseoframework.com/extensions/articles/#changelog)
* [Local at version 1.1.9](https://theseoframework.com/extensions/local/#changelog)
* [Focus at version 1.5.1](https://theseoframework.com/extensions/focus/#changelog)

**Detailed log:**

View the [detailed v2.5.1 changelog](https://tsf.fyi/p/3779).

= 2.5.0 - Quick SEO =

**Release date:**

* February 9th, 2021

**Feature highlights:**

In this update, we added quick-and bulk-edit support, improved browser scripting performance, and improved various extensions. Most notably, Honeypot catches modernized spammers, Articles can now be disabled per-post, and Focus's API is vastly expanded.

**Transportant release notes:**

* Henceforth, Extension Manager requires **The SEO Framework v4.1.2 or higher**.
* This release brings support for WordPress v5.6's updated interface. Sorry for the delay!
* Honeypot might momentarily mark legitimate comments as spam because it looks for new anti-spam data. Please manually moderate your comment section for a few minutes after updating, and be sure to clear your site's caches directly after updating to reduce the number of false negatives significantly.

**Updated extensions:**

* [Articles at version 2.1.0](https://theseoframework.com/extensions/articles/#changelog)
* [Focus at version 1.5.0](https://theseoframework.com/extensions/focus/#changelog)
* [Honeypot at version 2.0.0](https://theseoframework.com/extensions/honeypot/#changelog)
* [Local at version 1.1.8](https://theseoframework.com/extensions/local/#changelog)
* [Monitor at version 1.2.7](https://theseoframework.com/extensions/monitor/#changelog)

**Detailed log:**

View the [detailed v2.5.0 changelog](https://theseoframework.com/?p=3686).

= Full changelog =

* **The full changelog can be found [here](http://theseoframework.com/?cat=19).**

== Upgrade Notice ==

= 2.5.3 =

This plugin now requires WordPress v5.5 or higher.

= 2.5.0 =

This plugin now requires WordPress v5.1 and The SEO Framework v4.1.2 or higher.
