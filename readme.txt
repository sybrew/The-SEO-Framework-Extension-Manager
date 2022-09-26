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

* Extension Manager now requires PHP 7.3.0 or higher, from PHP 5.6.5.
* The SEO Framework v4.2 or higher is now required, from TSF v4.1.4.
* Touched up the interface, it's now centered, more compact, and easier on the eyes.
* Now relies on The SEO Framework's JavaScript availability test, instead of WordPress's, making unresponsive interfaces less likely when a faulty plugin or theme is installed.
* Modernized PHP code, making the plugin up to 30% faster.
	* TODO test.
* Modernized some JavaScript code, improving UI responsiveness significantly.
* Reduced plugin file size relatively by no longer packing rendered vector images for archaic browser support.
	* TODO Use `<use>` like on TSF site for improved painting performance?
* Introduced a new API alias for `tsf_extension_manager()`: `tsfem()`.
TODO POT file. (also update related github)

* TODO Add index.php files to extension top-folders (and fill in empty index.php files)

* TODO consider cleaning unused functions? e.g. pixels_to_points
* TODO consider removing dependency on /trunk/lib/images/icon.svg and rely on /assets/icon.svg
* TODO use API functions of TSF (memo, has_run, isset()?..: et al.)
* TODO move get_view() to trait, using prescribed base URL.

* TODO instead of "defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $_class = TSF_Extension_Manager\Extension\Transport\get_active_class() and ", try a secret (for all extensions).
TODO implement views trait.
TODO remove trends pane... we planned to add our blog items there, but that never came to fruition.
	-> We kept it there to visually balance the page.
		-> Should we let the extensions wrap side-by-side instead? flex base 400px, stretch to fit?
			-> Copy from tsf.fyi/e?
TODO make Traits autoloadable? -> The Construct_* part is annoying -> \Construct\?. Extension_* needs to become \Extension\
	-> `use \TSF_Extension_Manager\Traits\{Construct_Master_Once_Interface,Time,UI,Extension_Options,Extension_Forms,Error};`
TODO use :where() css instead of the avalange of entries.
TODO use `use` for SVG logos? -> Is this feasible? -> tsfem_ui()->register_logo( id, svg );
	-> Don't register logo, just have a symbols output file and use that. At most, tsfem_ui()->output_logo( id, [ 'defaultColor' => false ] );

TODO convert post metadata from double-serialized to single-serialized (with perhaps WP interfering?)
	-> We took control because WP was causing issues with backslashes...

TODO fix notice bounce (reintroduced for we ditched the stagnant :empty selector)
TODO fewer jQuery animations, more CSS animations.
	-> Planned for future update. 3.0?

TODO Reevaluate get_view()'s implicated extract() and the use of get_defined_vars()
	- Neither of these can be populated by the user, still, they are an exploit waiting to happen.

TODO Add "mark as spam, put in trash, or discard/permanently delete the comment."
TODO Add method used to mark as spam as comment-meta? -> Is this possible, I don't want to add more rows.
	-> Otherwise, simply add a counter for each type. Store as array?

TODO When transporter is in session, maybe we can set a flag in the database which is checked every 50 items or something and if it exists, it aborts the current run and "continues (restarts)" on the next one?
	-> Ergo, store 50 (or 250) transactions in memory, when 0==$trans%50, then store blob in database, continue to next 50.
	-> Store end also in database.

TODO "^%sitetitle% %sep%" -> ""?
TODO "%sep% %sitetitle%$" -> ""?

TODO addslashes( serialize( $c_meta ) )  -> $c_meta + maybe_unserialize()?
	-> The maybe_unserialize() will gradually translate metadata back to what it's supposed to be. We can keep this indefinitely, with a NOTE that removing this would incur data loss.
		-> There's no need for a safer maybe_unserialize, for future data won't be serialized, so it at most can be a self-resolving stored issue, not reflective.
	-> Figure out if we can maintain slashes like TSF does by converting them via tsf()->bsol()
		-> Break this feature first by spamming slashes without the serialization feature on a new post.
		-> Then, test if migration is seamless. Test on our own sites, prominently, if the focus keywords stay intact.

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
