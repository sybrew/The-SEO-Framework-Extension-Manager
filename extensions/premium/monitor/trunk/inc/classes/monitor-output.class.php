<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Output
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
 * Class TSF_Extension_Manager_Extension\Monitor_Output
 *
 * Parses and evaluates input data.
 *
 * @since 1.0.0
 * @access private
 */
final class Monitor_Output {
	use Enclose_Core_Final, Construct_Core_Static_Final;

	/**
	 * Returns slab entry title based on $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @return string The $type $key title.
	 */
	public static function parse_title( $key, $type ) {

		switch ( $type ) :
			case 'issues' :
				switch ( $key ) :
					case 'title' :
						$title = __( 'Titles', 'the-seo-framework-extension-manager' );
						break 2;

					case 'description' :
						$title = __( 'Descriptions', 'the-seo-framework-extension-manager' );
						break 2;

					case 'canonical' :
						$title = __( 'Canonical URLs', 'the-seo-framework-extension-manager' );
						break 2;

					case 'favicon' :
						$title = __( 'Favicon output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'duplicated' :
						$title = __( 'Duplicated content', 'the-seo-framework-extension-manager' );
						break 2;

					case 'mobile' :
						$title = __( 'Mobile friendliness', 'the-seo-framework-extension-manager' );
						break 2;

					case 'html' :
						$title = __( 'HTML output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'php' :
						$title = __( 'PHP errors', 'the-seo-framework-extension-manager' );
						break 2;

					case 'img' :
						$title = __( 'Image sizes', 'the-seo-framework-extension-manager' );
						break 2;

					case 'robots' :
						$title = __( 'Robots.txt output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'sitemap' :
						$title = __( 'Sitemap output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'external' :
						$title = __( 'External links', 'the-seo-framework-extension-manager' );
						break 2;

					default :
						break 1;
				endswitch;

			default :
				$title = ucwords( str_replace( array( '-', '_' ), ' ', $key ) );
				break 1;
		endswitch;

		return $title;
	}

	/**
	 * Returns slab data content.
	 *
	 * @since 1.0.0
	 * @staticvar object $tests The Monitor_Tests class isntance.
	 *
	 * @param string $key The array key.
	 * @param mixed $value The array value attached to $key.
	 * @param string $type The pane-data type.
	 * @return string The HTML formed data.
	 */
	public static function parse_content( $key, $value, $type ) {

		$content = '';

		switch ( $type ) :
			case 'issues' :
				static $tests = null;

				if ( is_null( $tests ) )
					$tests = Monitor_Tests::get_instance();

				if ( isset( $value['requires'] ) && version_compare( TSFEM_E_MONITOR_VERSION, $value['requires'], '>=' ) )
					$content = isset( $value['data'] ) ? $tests->$key( $value['data'] ) : '';
				break;

			default :
				break;
		endswitch;

		return $content;
	}
}
