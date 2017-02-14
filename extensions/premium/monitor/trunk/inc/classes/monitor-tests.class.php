<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Tests
 */
namespace TSF_Extension_Manager\Extension;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Core_Static_Final as Construct_Core_Static_Final;

/**
 * Class TSF_Extension_Manager\Extension\Monitor_Tests
 *
 * Tests Monitor Data input. With an overuse of goto statements.
 *
 * @since 1.0.0
 * @access private
 */
final class Monitor_Tests {
	use Enclose_Core_Final, Construct_Core_Static_Final;

	/**
	 * The object instance.
	 *
	 * @since 1.0.0
	 *
	 * @var object|null This object instance.
	 */
	private static $instance = null;

	/**
	 * The constructor. Does nothing.
	 */
	private function construct() { }

	/**
	 * Handles unapproachable invoked methods.
	 * Silently ignores errors on this call.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return string Empty.
	 */
	public function __call( $name, $arguments ) {
		return '';
	}

	/**
	 * Sets the class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public static function set_instance() {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return object The current instance.
	 */
	public static function get_instance() {

		if ( is_null( static::$instance ) ) {
			static::set_instance();
		}

		return static::$instance;
	}

	/**
	 * Determines if the Favicon is output.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function issue_favicon( $data ) {

		$content = '';
		$state = 'unknown';

		if ( ! isset( $data['meta'] ) && ! isset( $data['static'] ) ) {
			$state = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		$state = 'good';

		if ( isset( $data['meta'] ) && $data['meta'] ) {
			$content = $this->wrap_info( \esc_html__( 'A dynamic favicon has been found, this increases support for mobile devices.', 'the-seo-framework-extension-manager' ) );
			goto end;
		}

		if ( empty( $data['static'] ) ) {
			$content = $this->wrap_info( \esc_html__( 'No favicon has been found.', 'the-seo-framework-extension-manager' ) );
			$state = 'bad';
		}
		$content .= $this->wrap_info( \esc_html__( 'You should add a site icon through the customizer.', 'the-seo-framework-extension-manager' ) );

		end : {
			return array(
				'content' => $content,
				'state' => $state,
			);
		}
	}

	/**
	 * Determines if there are PHP errors detected.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function issue_php( $data ) {

		$content = '';
		$state = 'unknown';

		if ( ! is_array( $data ) ) {
			$state = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		$links = array();

		foreach ( $data as $value ) :
			if ( isset( $value['value'] ) && false === $value['value'] ) :
				$id = isset( $value['post_id'] ) ? (int) $value['post_id'] : false;

				if ( false !== $id ) {
					$home = isset( $value['home'] ) && $value['home'];
					$url = \the_seo_framework()->the_url( '', array( 'home' => $home, 'external' => true, 'id' => $id ) );
					$title = \the_seo_framework()->title( '', '', '', array( 'notagline' => true, 'get_custom_field' => true, 'term_id' => $id, 'page_on_front' => $home, 'escape' => true ) );

					$links[] = sprintf( '<a href="%s" target="_blank">%s</a>', $url, $title );
				}
			endif;
		endforeach;

		//* Links are filled in with erroneous pages.
		if ( empty( $links ) ) {
			$state = 'good';
			$content = $this->no_issue_found();
		} else {
			$state = 'bad';
			$content = $this->wrap_info( \esc_html__( 'Something is causing a PHP error on your website. This prevents correctly closing of HTML tags.', 'the-seo-framework-extension-manager' ) );
			$content .= sprintf( '<h4>%s</h4>', \esc_html( _n( 'Affected page:', 'Affected pages:', count( $links ), 'the-seo-framework-extension-manager' ) ) );

			$content .= '<ul class="tsfem-ul-disc">';
			foreach ( $links as $link ) {
				$content .= sprintf( '<li>%s</li>', $link );
			}
			$content .= '</ul>';
		}

		$content .= $this->small_sample_disclaimer();

		end : {
			return array(
				'content' => $content,
				'state' => $state,
			);
		}
	}

	/**
	 * Determines if the robots.txt file is correctly output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data in HTML.
	 */
	public function issue_robots( $data ) {

		$content = '';
		$state = 'unknown';

		if ( ! isset( $data['located'] ) )
			goto end;

		if ( ! $data['located'] ) {
			$state = 'error';
			$content = $this->wrap_info(
				\tsf_extension_manager()->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'No `robots.txt` file has been found. Please check your server configuration.', 'the-seo-framework-extension-manager' ),
					array( 'code' )
				)
			);
			goto end;
		}

		if ( ! \get_option( 'blog_public' ) ) {
			$state = 'bad';
			$content = $this->wrap_info( \esc_html__( 'This site is discouraging Search Engines from visiting. This means popular Search Engines are not crawling and indexing your website.', 'the-seo-framework-extension-manager' ) );
			goto end;
		}

		if ( ! isset( $data['value'] ) ) {
			$state = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		$sample_tsf = \the_seo_framework()->robots_txt();

		//* Normalize.
		$sample_tsf = \esc_html( str_replace( array( "\r\n", "\r", "\n" ), '', trim( $sample_tsf ) ) );
		$data['value'] = \esc_html( str_replace( array( "\r\n", "\r", "\n" ), '', trim( $data['value'] ) ) );

		if ( $sample_tsf === $data['value'] ) {
			$state = 'good';
			$content = $this->wrap_info(
				\tsf_extension_manager()->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'The `robots.txt` file handled correctly by The SEO Framework.', 'the-seo-framework-extension-manager' ),
					array( 'code' )
				)
			);
			goto end;
		}

		not_equal : {
			$state = 'okay';
			$content = $this->wrap_info(
				\tsf_extension_manager()->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'The `robots.txt` file is static or overwritten in another way. Consider deleting the `robots.txt` file from your home directory folder because The SEO Framework handles this appropriately.', 'the-seo-framework-extension-manager' ),
					array( 'code' )
				)
			);
			goto end;
		}

		end : {
			return array(
				'content' => $content,
				'state' => $state,
			);
		}
	}

	/**
	 * Determines if the sitemap.xml file is correctly output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data in HTML.
	 */
	public function issue_sitemap( $data ) {

		$content = '';
		$state = 'unknown';

		if ( ! isset( $data['located'] ) )
			goto end;

		if ( ! $data['located'] ) {
			$state = 'error';
			$content = $this->wrap_info( \esc_html__( 'No sitemap file has been found. Please check your server configuration.', 'the-seo-framework-extension-manager' ) );
			goto end;
		}

		$state = 'good';

		//* 10 MB, not 10 MiB
		if ( isset( $data['size'] ) && $data['size'] > 10000000 ) {
			$state = 'bad';
			$content .= $this->wrap_info( \esc_html__( 'The sitemap file is bigger than 10MB, you should make it smaller.', 'the-seo-framework-extension-manager' ) );
		}

		if ( isset( $data['index'] ) && $data['index'] ) {
			$content .= $this->wrap_info( $this->small_sample_disclaimer() );
		}

		if ( isset( $data['valid'] ) && ! $data['valid'] ) {
			$state = 'bad';
			$content .= $this->wrap_info( \esc_html__( 'The sitemap file is invalid. This could have multiple causes.', 'the-seo-framework-extension-manager' ) );
		}

		if ( empty( $content ) ) {
			$content = $this->wrap_info( $this->no_issue_found() );
		}

		end : {
			return array(
				'content' => $content,
				'state' => $state,
			);
		}
	}

	/**
	 * Returns more coming soon information with unknown state.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The input data.
	 * @return string The information string in HTML.
	 */
	public function issue_moresoon( $data ) {
		return array(
			'content' => $this->wrap_info( \esc_html__( 'More issue tests are coming soon!', 'the-seo-framework-extension-manager' ) ),
			'state' => 'unknown',
		);
	}

	/**
	 * Wraps text into an HTML info wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Escaped input text.
	 * @return string The HTML wrapped information text.
	 */
	protected function wrap_info( $text ) {
		return sprintf( '<div class="tsfem-e-monitor-info">%s</div>', $text );
	}

	/**
	 * Returns translatable string wrapped in HTML for when no issues are found.
	 *
	 * @since 1.0.0
	 * @staticvar string $cache
	 *
	 * @return string HTML wrapped no issues found.
	 */
	protected function no_issue_found() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = sprintf( '<div class="tsfem-description">%s</div>', \esc_html__( 'No issues have been found.', 'the-seo-framework-extension-manager' ) );
	}

	/**
	 * Returns translatable string wrapped in HTML for when no data is found.
	 *
	 * @since 1.0.0
	 * @staticvar string $cache
	 *
	 * @return string HTML wrapped no data found.
	 */
	protected function no_data_found() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = sprintf( '<div class="tsfem-description">%s</div>', \esc_html__( 'No data has been found on this issue.', 'the-seo-framework-extension-manager' ) );
	}

	/**
	 * Returns translatable string wrapped in HTML for when a small sample size has been used.
	 *
	 * @since 1.0.0
	 * @staticvar string $cache
	 *
	 * @return string HTML wrapped small sample size used.
	 */
	protected function small_sample_disclaimer() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = sprintf( '<div class="tsfem-description">%s</div>', \esc_html__( 'This has been evaluated with a small sample size.', 'the-seo-framework-extension-manager' ) );
	}
}
