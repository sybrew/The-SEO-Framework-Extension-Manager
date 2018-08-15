<?php
/**
 * @package TSF_Extension_Manager\Extension\Title_Fix
 */
namespace TSF_Extension_Manager\Extension\Title_Fix;

/**
 * Extension Name: Title Fix
 * Extension URI: https://theseoframework.com/extensions/title-fix/
 * Extension Description: The Title Fix extension makes sure your title output is as configured. Even if your theme is doing it wrong.
 * Extension Version: 1.2.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Title Fix extension for The SEO Framework
 * Copyright (C) 2016-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

//* Notify the existence of this extension through a lovely definition.
define( 'TSFEM_E_TITLE_FIX', true );

//* Define version, for future things.
define( 'TSFEM_E_TITLE_FIX_VERSION', '1.2.0' );

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\title_fix_init', 11 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 * @staticvar bool $loaded
 * @action 'plugins_loaded'
 * @priority 11 : The WordPress.org version has priority 10, preventing collision.
 *                Also, the loader requires 11 or later.
 *
 * @return bool True if class is loaded.
 */
function title_fix_init() {

	static $loaded = null;

	//* Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	//* Don't load if the WordPress.org version is active.
	if ( class_exists( 'The_SEO_Framework_Title_Fix', false ) )
		return $loaded = false;

	//* Backwards compatibility
	define( 'THE_SEO_FRAMEWORK_TITLE_FIX', true );

	new Core;

	return $loaded = true;
}

/**
 * Class TSF_Extension_Manager\Extension\Title_Fix\Core
 *
 * @since 1.0.0
 *
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Core {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * Check if title has been found, otherwise continue flushing till the bottom of plugin output.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $title_found_and_flushed
	 */
	protected $title_found_and_flushed = false;

	/**
	 * If Output Buffering is started or not.
	 * If started, don't start again.
	 * If stopped, don't stop again.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $ob_started
	 */
	protected $ob_started = false;

	/**
	 * Determines if the title has been fixed yet.
	 *
	 * @since 1.0.3
	 *
	 * @var bool $is_fixed
	 */
	protected $is_fixed = false;

	/**
	 * The constructor, initialize plugin.
	 */
	private function construct() {

		//* Start the plugin at header, where theme support has just been initialized.
		\add_action( 'get_header', [ $this, 'start_plugin' ], -1 );

	}

	/**
	 * Start plugin at get_header. A semi constructor.
	 *
	 * @since 1.0.0
	 */
	public function start_plugin() {

		/**
		 * Don't fix the title in admin.
		 * get_header doesn't run in admin, but another plugin might init it.
		 */
		if ( \is_admin() )
			return;

		/**
		 * Only do something if the theme is doing it wrong. Or when the filter has been applied.
		 * Requires initial load after theme switch.
		 */
		if ( false === $this->current_theme_supports_title_tag() ) :
			//* Start loader.
			$this->loader();

			/**
			 * Stop OB if it's still running at shutdown.
			 * Might prevent AJAX issues, if any.
			 */
			\add_action( 'shutdown', [ $this, 'stop_ob' ], 0 );
		endif;
	}

	/**
	 * Loads plugin actions.
	 *
	 * @since 1.0.3
	 * @staticvar int $_sequence Iterates sequences for switch.
	 *
	 * @return null Early if title is fixed.
	 */
	public function loader() {

		if ( $this->is_fixed )
			return;

		static $_sequence = 0;

		$_sequence++;

		switch ( $_sequence ) :
			case 1 :
				/**
				 * First run.
				 * Start at HTTP header.
				 * Stop right at where wp_head is run.
				 */
				\add_action( 'get_header', [ $this, 'start_ob' ], 0 );
				\add_action( 'wp_head', [ $this, 'maybe_rewrite_title' ], 0 );
				break;

			case 2 :
				/**
				 * Second run. Capture WP head.
				 * Scenario: \add_action( 'wp_head', 'wp_title' );.. or another callback.
				 * Start at where wp_head is run (last run left off).
				 * Stop right at the end of wp_head.
				 */
				\add_action( 'wp_head', [ $this, 'maybe_start_ob' ], 0 );
				\add_action( 'wp_head', [ $this, 'maybe_rewrite_title' ], 9999 );
				break;

			case 3 :
				/**
				 * Third run. Capture the page.
				 * Start at where wp_head has ended (last run left off),
				 * or at wp_head start (first run left off).
				 * Stop at the footer.
				 */
				\add_action( 'wp_head', [ $this, 'maybe_start_ob' ], 9999 );
				\add_action( 'get_footer', [ $this, 'maybe_rewrite_title' ], -1 );
				break;

			default :
				break;
		endswitch;
	}

	/**
	 * Start the Output Buffer.
	 *
	 * @since 1.0.0
	 */
	public function start_ob() {

		if ( false === $this->ob_started ) {
			ob_start();
			$this->ob_started = true;
		}
	}

	/**
	 * Clean the buffer and turn off the output buffering.
	 *
	 * @since 1.0.0
	 */
	public function stop_ob() {

		if ( $this->ob_started ) {
			ob_end_clean();
			$this->ob_started = false;
		}
	}

	/**
	 * Maybe start the Output Buffer if the title is not yet replaced.
	 *
	 * @since 1.0.0
	 */
	public function maybe_start_ob() {

		//* Reset the output buffer if not found.
		if ( false === $this->ob_started && false === $this->title_found_and_flushed ) {
			$this->start_ob();
		}
	}

	/**
	 * Maybe stop OB flush if title has been replaced already.
	 *
	 * @since 1.0.0
	 */
	public function maybe_stop_ob() {

		//* Let's not buffer all the way down.
		if ( $this->ob_started && $this->title_found_and_flushed ) {
			$this->stop_ob();
		}
	}

	/**
	 * Maybe rewrite the title, if not rewritten yet.
	 *
	 * @since 1.0.0
	 * @since 1.0.3 : Now initiates loader loop.
	 */
	public function maybe_rewrite_title() {

		if ( $this->ob_started && false === $this->title_found_and_flushed ) {
			$content = ob_get_clean();
			$this->ob_started = false;

			$this->find_title_tag( $content );
		}

		$this->maybe_stop_ob();
		$this->loader();
	}

	/**
	 * Finds the title tag and replaces it if found; will echo content from buffer otherwise.
	 *
	 * @since 1.0.0
	 * @since 1.0.2: Always echos $content.
	 *
	 * @param string $content The content with possible title tag.
	 * @return void When title is found.
	 */
	public function find_title_tag( $content ) {

		//* Let's use regex.
		if ( 1 === preg_match( '/<title.*?<\/title>/is', $content, $matches ) ) {
			$title_tag = isset( $matches[0] ) ? $matches[0] : null;

			if ( isset( $title_tag ) ) {
				$this->replace_title_tag( $title_tag, $content );
				$this->title_found_and_flushed = true;
				return;
			}
		}

		echo $content; // xss OK, esque. The content is unknown.

	}

	/**
	 * Replaces the title tag.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added TSF v3.1 compat.
	 *
	 * @param string $title_tag the Title tag with the title
	 * @param string $content The content containing the $title_tag
	 * @return string the content with replaced title tag.
	 */
	public function replace_title_tag( $title_tag, $content ) {

		$tsf = \the_seo_framework();

		if ( method_exists( $tsf, 'get_title' ) ) {
			$title = $tsf->get_title();
		} else {
			$title = $tsf->title_from_cache( '', '' , '', true );
		}
		$new_title = '<title>' . $title . '</title>' . $this->indicator();

		//* Replace the title tag within the header.
		//* TODO substr_replace to prevent multiple replacements?
		$content = str_replace( $title_tag, $new_title, $content );

		echo $content; // xss OK, esque. The content is unknown. Replaced content is great.

	}

	/**
	 * Checks a theme's support for title-tag.
	 *
	 * @since 1.0.0
	 * @global array $_wp_theme_features
	 * @staticvar bool $_supports
	 *
	 * @return bool True if the theme supports the title tag, false otherwise.
	 */
	public function current_theme_supports_title_tag() {

		static $_supports = null;

		if ( isset( $_supports ) )
			return $_supports;

		global $_wp_theme_features;

		if ( false === empty( $_wp_theme_features['title-tag'] ) )
			return $_supports = true;

		return $_supports = false;
	}

	/**
	 * Returns a small indicator.
	 *
	 * @since 1.0.1
	 *
	 * @return string
	 */
	public function indicator() {

		/**
		 * Applies filters 'the_seo_framework_title_fixed_indicator'
		 * @since 1.0.1
		 * @param bool Whether to output an indicator or not.
		 */
		$indicator = (bool) \apply_filters( 'the_seo_framework_title_fixed_indicator', true );

		if ( $indicator )
			return '<!-- fixed -->';

		return '';
	}
}
