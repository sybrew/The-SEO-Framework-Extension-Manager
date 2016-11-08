<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Tests
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use TSF_Extension_Manager\Construct_Core_Static_Final as Construct_Core_Static_Final;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager_Extension\Monitor_Tests
 *
 * Tests Monitor Data input.
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
	 * @return void.
	 */
	public function __call( $name, $arguments ) {
		return;
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
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function favicon( $data ) {

		$content = '';
		$state = 'unknown';

		if ( isset( $data['meta'] ) || isset( $data['static'] ) ) {
			if ( empty( $data['meta'] ) ) {

				$state = 'good';

				if ( empty( $data['static'] ) ) {
					$content .= $this->wrap_info( esc_html__( 'No favicon has been found.', 'the-seo-framework-extension-manager' ) );
					$state = 'bad';
				}

				$content .= $this->wrap_info( esc_html__( 'You should add a site icon through the customizer.', 'the-seo-framework-extension-manager' ) );
			} else {
				$state = 'good';
				$content .= $this->wrap_info( esc_html__( 'A dynamic favicon has been found, this increases support for mobile devices.', 'the-seo-framework-extension-manager' ) );
			}
		} else {
			$state = 'unknown';
			$content = $this->no_data_found();
		}

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
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function php( $data ) {

		$content = '';
		$state = 'unknown';

		$links = array();

		if ( is_array( $data ) ) {
			foreach ( $data as $value ) :
				if ( isset( $value['value'] ) && false === $value['value'] ) {
					$id = isset( $value['post_id'] ) ? $value['post_id'] : false;

					if ( false !== $id ) {
						$home = isset( $value['home'] ) && $value['home'];
						$url = the_seo_framework()->the_url( '', array( 'home' => $home, 'external' => true, 'id' => $id ) );
						$title = the_seo_framework()->title( '', '', '', array( 'notagline' => true, 'term_id' => $id, 'is_front_page' => $home, 'escape' => true ) );

						$links[] = sprintf( '<a href="%s" target="_blank">%s</a>', $url, $title );
					}
				}
			endforeach;
		} else {
			$state = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		if ( empty( $links ) ) {
			$state = 'good';
			$content = $this->no_issue_found();
		} else {
			$state = 'bad';
			$content = $this->wrap_info( esc_html__( 'Something is causing a PHP error on your website. This prevents correctly closing of HTML tags.', 'the-seo-framework-extension-manager' ) );
			$content .= sprintf( '<h4>%s</h4>', esc_html( _n( 'Affected page:', 'Affected pages:', count( $links ), 'the-seo-framework-extension-manager' ) ) );
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

	protected function wrap_info( $text ) {
		return sprintf( '<div class="tsfem-monitor-info">%s</div>', $text );
	}

	protected function no_issue_found() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = sprintf( '<p class="tsfem-description">%s</p>', esc_html__( 'No issues have been found.', 'the-seo-framework-extension-manager' ) );
	}

	protected function no_data_found() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = sprintf( '<p class="tsfem-description">%s</p>', esc_html__( 'No data has been found on this issue.', 'the-seo-framework-extension-manager' ) );
	}

	protected function small_sample_disclaimer() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = sprintf( '<p class="tsfem-description">%s</p>', esc_html__( 'This has been evaluated with a small sample size.', 'the-seo-framework-extension-manager' ) );
	}
}
