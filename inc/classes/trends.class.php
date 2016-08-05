<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
 * Class TSF_Extension_Manager\Trends.
 *
 * Outputs Trends pane.
 *
 * @since 1.0.0
 * @access private
 * 		You'll need to invoke the TSF_Extension_Manager\Core verification handler. Which is impossible.
 * @final Please don't extend this.
 */
final class Trends {

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Constructing is forbidden.
	 */
	private function __construct() { }

	/**
	 * Returns the trend call.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance Required. The instance key.
	 * @param int $bits Required. The instance bits.
	 * @return string The trends output.
	 */
	public static function get( $instance, $bits ) {

		tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;

		return self::prototype_trends();
	}

	/**
	 * Parses and returns Google Feed.
	 * This is a prototype. It's planned to fetch from https://premium.theseoframework.com/
	 * With a filtered list that's parsed remotely, which has a history and is loaded from more or personalized sources.
	 * I still need to get started on writing content...
	 *
	 * @since 1.0.0
	 *
	 * @return string The filtered Google Webmasters feed output.
	 */
	private static function prototype_trends() {

		if ( ! function_exists( 'simplexml_load_string' ) )
			return -1;

		$transient_name = 'latest-seo-feed-transient';
		$output = get_transient( $transient_name );

		if ( false === $output ) {
			//* Google Webmasters official blog link.
			$feed_url = 'https://www.blogger.com/feeds/32069983/posts/default';

			$http_args = array(
				'timeout' => 5,
				'httpversion' => apply_filters( 'tsf_extension_manager_http_request_version', '1.1' ),
			);

			$request = wp_safe_remote_get( $feed_url, $http_args );

			if ( 200 !== (int) wp_remote_retrieve_response_code( $request ) )
				return '';

			$xml = wp_remote_retrieve_body( $request );
			$options = LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_NSCLEAN;
			$xml = simplexml_load_string( $xml, 'SimpleXMLElement', $options );

			if ( ! isset( $xml->entry ) || empty( $xml->entry ) ) {
				set_transient( $transient_name, '', DAY_IN_SECONDS );
				return '';
			}

			$entry = $xml->entry;
			unset( $xml );

			$output = '';

			$max = 15;
			$i = 0;
			foreach ( $entry as $object ) {

				if ( $i >= $max )
					break;

				if ( ! isset( $object->category ) || ! is_object( $object->category ) )
					continue;

				$found = false;
				//* Filter terms.
				foreach ( $object->category as $category ) {
					$term = isset( $category->{0}['term'] ) ? $category->{0}['term']->__toString() : '';
					if ( ! in_array( $term, array( 'search results', 'crawling and indexing', 'general tips' ), true ) ) {
						$found = true;
						break;
					}
					continue;
				}
				unset( $category );
				if ( false === $found )
					continue;

				//* Fetch link.
				$link = '';
				foreach ( $object->link as $link_object ) {

					$type = isset( $link_object->{0}['type'] ) ? $link_object->{0}['type']->__toString() : '';
					if ( 'text/html' === $type ) {

						$rel = isset( $link_object->{0}['rel'] ) ? $link_object->{0}['rel']->__toString() : '';
						if ( 'replies' === $rel ) {

							$link = isset( $link_object->{0}['href'] ) ? $link_object->{0}['href']->__toString() : '';

							if ( $link )
								$link = strtok( $link, '#' );

							break;
						}
					}
				}
				unset( $link_object );

				//* @note: $object->updated also exists.
				$date = isset( $object->published ) ? $object->published->__toString() : '';
				$date = $date ? '<time>' . date_i18n( get_option( 'date_format' ), strtotime( $date ) ) . '</time>' : '';

				$title = isset( $object->title ) ? $object->title->__toString() : '';
				$title = $title ? the_seo_framework()->escape_title( $title ) : '';

				$content = isset( $object->content ) ? $object->content->__toString() : '';
				$content = $content ? wp_strip_all_tags( $content ) : '';
				unset( $object );

				//* Do not care for the current length. Always trim.
				$content = the_seo_framework()->trim_excerpt( $content, 251, 250 );
				$content = the_seo_framework()->escape_description( $content );

				if ( $link ) {
					//* No need for translations, it's English only.
					$title = sprintf( '<h4><a href="%s" target="_blank" rel="external nofollow" title="Read more...">%s</a></h4>', esc_url( $link ), $title );
				}

				$output .= sprintf( '<div class="tsfem-feed-entry"><div class="tsfem-feed-top">%s%s</div><div class="tsfem-feed-content">%s</div></div>', $title, $date, $content );
				$i++;
			}

			set_transient( $transient_name, $output, DAY_IN_SECONDS );
		}

		return $output;
	}
}
