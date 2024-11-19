<?php
/**
 * @package TSF_Extension_Manager\Extension\Title_Fix\Front
 */

namespace TSF_Extension_Manager\Extension\Title_Fix;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Title Fix extension for The SEO Framework
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * Class TSF_Extension_Manager\Extension\Title_Fix\Front
 *
 * @since 1.0.0
 * @since 1.3.0 Renamed from "Core".
 */
final class Front {
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * @since 1.0.0
	 * @var bool Check if title has been found, otherwise continue flushing till the bottom of plugin output.
	 */
	protected $title_found_and_flushed = false;

	/**
	 * @since 1.0.0
	 * @var bool Whether Output Buffering has started by extension.
	 */
	protected $ob_started = false;

	/**
	 * The constructor, initialize plugin.
	 */
	private function construct() {
		// Start the plugin at header, where theme support has just been initialized.
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
		 * i.e., we test if the theme has registered 'title-tag' support.
		 */
		if ( empty( $GLOBALS['_wp_theme_features']['title-tag'] ) ) {
			// Start loader.
			$this->loader();

			/**
			 * Stop OB if it's still running at shutdown.
			 * Might prevent AJAX issues, if any.
			 */
			\add_action( 'shutdown', [ $this, 'stop_ob' ], 0 );
		}
	}

	/**
	 * Loads plugin actions.
	 *
	 * @since 1.0.3
	 */
	public function loader() {

		static $_sequence = 0;

		switch ( $_sequence ) {
			case 0:
				/**
				 * First run.
				 * Start at HTTP header.
				 * Stop right at where wp_head is running.
				 */
				\add_action( 'get_header', [ $this, 'start_ob' ], 0 );
				\add_action( 'wp_head', [ $this, 'maybe_rewrite_title' ], 0 );
				break;

			case 1:
				/**
				 * Second run. Capture WP head.
				 * Scenario: \add_action( 'wp_head', 'wp_title' );.. or another callback.
				 * Start at where wp_head is run (last run left off).
				 * Stop right at the end of wp_head.
				 */
				\add_action( 'wp_head', [ $this, 'maybe_start_ob' ], 0 );
				\add_action( 'wp_head', [ $this, 'maybe_rewrite_title' ], 9999 );
				break;

			case 2:
				/**
				 * Third run. Capture the page.
				 * Start at where wp_head has ended (last run left off),
				 * or at wp_head start (first run left off).
				 * Stop at the footer.
				 */
				\add_action( 'wp_head', [ $this, 'maybe_start_ob' ], 9999 );
				\add_action( 'get_footer', [ $this, 'maybe_rewrite_title' ], -1 );
		}

		$_sequence++;
	}

	/**
	 * Start the Output Buffer.
	 *
	 * @since 1.0.0
	 */
	public function start_ob() {

		if ( ! $this->ob_started ) {
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

		// Reset the output buffer if not found.
		if ( ! $this->ob_started && ! $this->title_found_and_flushed ) {
			$this->start_ob();
		}
	}

	/**
	 * Maybe stop OB flush if title has been replaced already.
	 *
	 * @since 1.0.0
	 */
	public function maybe_stop_ob() {

		// Let's not buffer all the way down.
		if ( $this->ob_started && $this->title_found_and_flushed ) {
			$this->stop_ob();
		}
	}

	/**
	 * Maybe rewrite the title, if not rewritten yet.
	 *
	 * @since 1.0.0
	 * @since 1.0.3 Now initiates loader loop.
	 */
	public function maybe_rewrite_title() {

		if ( $this->ob_started && ! $this->title_found_and_flushed ) {
			$content = ob_get_clean();

			$this->ob_started = false;

			$this->find_title_tag( $content );
		}

		$this->maybe_stop_ob();

		if ( ! $this->title_found_and_flushed )
			$this->loader();
	}

	/**
	 * Finds the title tag and replaces it if found; will echo content from buffer otherwise.
	 *
	 * @since 1.0.0
	 * @since 1.0.2 Always echos $content.
	 *
	 * @param string $content The content with possible title tag.
	 * @return void When title is found.
	 */
	public function find_title_tag( $content ) {

		// Let's use regex.
		if ( 1 === preg_match( '/<title.*?<\/title>/is', $content, $matches ) ) {
			if ( isset( $matches[0] ) ) {
				$this->replace_title_tag( $matches[0], $content );
				$this->title_found_and_flushed = true;
				return;
			}
		}

		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Not our content.
		echo $content;
	}

	/**
	 * Replaces the title tag from buffer and outputs it.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added TSF v3.1 compat.
	 * @since 1.2.1 Dropped TSF < v3.1 compat.
	 * @TODO Use substr_replace to prevent multiple replacements? The DOM head should contain only one title tag, though.
	 *
	 * @param string $title_tag the Title tag with the title
	 * @param string $content The content containing the $title_tag
	 */
	public function replace_title_tag( $title_tag, $content ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Not our content.
		echo str_replace(
			$title_tag,
			'<title>' . \tsf()->get_title() . '</title>' . $this->indicator(),
			$content
		);
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
		 * @since 1.0.1
		 * @param bool Whether to output an indicator or not.
		 */
		$indicator = (bool) \apply_filters( 'the_seo_framework_title_fixed_indicator', true );

		if ( $indicator )
			return '<!-- fixed -->';

		return '';
	}
}