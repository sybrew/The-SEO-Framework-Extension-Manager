<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	clamp_sentence,
};

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 *         You'll need to invoke the TSF_Extension_Manager\Core verification handler. Which is impossible.
 * @final
 */
final class Trends {
	use Construct_Core_Static_Final;

	/**
	 * Returns the trend call.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @param string $instance Required. The instance key.
	 * @param int    $bits Required. The instance bits.
	 * @return mixed The trends output.
	 */
	public static function get( $type, $instance, $bits ) {

		\tsfem()->_verify_instance( $instance, $bits[1] ) or die;

		switch ( $type ) {
			case 'feed':
				return static::prototype_trends();
			case 'ajax_feed':
				return static::prototype_trends( true );
		}

		return '';
	}

	/**
	 * Parses and returns Google Feed.
	 * This is a prototype. It's planned to fetch from https://premium.theseoframework.com/
	 * With a filtered list that's parsed remotely, which has a history and is loaded from more or rather personalized sources.
	 * I still need to get started on writing content...
	 *
	 * @since 1.0.0
	 *
	 * @param bool $ajax Whether to get the AJAX feed.
	 * @return string|array|int : {
	 *       string : The filtered Google Webmasters feed output. Empty on failure.
	 *       array  : The filtered Google Webmasters feed output on AJAX.
	 *       int    : On missing PHP functionality.
	 */
	private static function prototype_trends( $ajax = false ) {

		if ( ! \function_exists( 'simplexml_load_string' ) )
			return -1;

		$transient_name = 'tsfem_latest_seo_feed';
		$output         = \get_transient( $transient_name );

		// Bypass cache on AJAX as multi-admin can interfere.
		if ( ! $ajax && false !== $output )
			return $output;

		// Google Webmasters official blog feed.
		$feed_url = 'https://feeds.feedburner.com/blogspot/amDG';

		$http_args = [
			'timeout'     => 7,
			/**
			 * @since 1.0.0
			 * @param string $httpversion HTTP 1.1 is used for improved performance.
			 *                            WP default is '1.0'
			 */
			'httpversion' => \apply_filters( 'tsf_extension_manager_http_request_version', '1.1' ),
		];

		$request = \wp_safe_remote_get( $feed_url, $http_args );

		if ( 200 !== (int) \wp_remote_retrieve_response_code( $request ) )
			return '';

		$xml = \wp_remote_retrieve_body( $request );
		// Add bitwise operators.
		$options = \LIBXML_NOCDATA | \LIBXML_NOBLANKS | \LIBXML_NOWARNING | \LIBXML_NONET | \LIBXML_NSCLEAN;
		$xml     = simplexml_load_string( $xml, 'SimpleXMLElement', $options );

		if ( empty( $xml->channel->item ) ) {
			// Retry in hour when server is down.
			\set_transient( $transient_name, '', \HOUR_IN_SECONDS );
			return '';
		}

		$output   = '';
		$a_output = [];

		$max = 6;
		$i   = 0;
		foreach ( $xml->channel->item as $obj ) {
			// phpcs:disable, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- XML, not my fault.
			if ( $i >= $max ) break;

			// A little too many defence clauses -- I tried to combine them as best I could.
			if ( ! isset( $obj->title, $obj->link, $obj->description, $obj->pubDate ) )
				continue;

			$link = \esc_url( strtok( $obj->link->__toString(), '#' ) );
			if ( ! $link ) continue;

			$title = $obj->title->__toString();
			if ( ! $title ) continue;

			// Let's not advertise.
			if (
				   false !== stripos( "$link$title", 'conference' )
				|| false !== stripos( "$link$title", 'thanks' )
				|| false !== stripos( "$link$title", 'live' )
				|| false !== stripos( "$link$title", 'highlights' )
			) continue;

			$date = strtotime( $obj->pubDate->__toString() );
			if ( ! $date ) continue;

			$description = clamp_sentence( $obj->description->__toString(), 0, 234 ); // Magic number, because why not.
			if ( ! $description ) continue;

			// No need for translations, it's English only.
			$_output = sprintf(
				'<div class="tsfem-feed-entry tsfem-flex tsfem-flex-nowrap"><div class="tsfem-feed-top tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-space tsfem-flex-nowrap">%s%s</div><div class=tsfem-feed-content>%s</div></div>',
				sprintf(
					'<h4><a href="%s" target=_blank rel="nofollow noopener noreferrer" title="Read more...">%s</a></h4>',
					\esc_url( $link, [ 'https', 'http' ] ),
					\esc_html( $title ),
				),
				'<time>' . \date_i18n( \get_option( 'date_format' ), $date ) . '</time>',
				\esc_html( $description ),
			);

			// Maintain full list for transient / non-AJAX.
			$output .= $_output;
			// Maintain list of output for AJAX.
			$ajax and $a_output[] = $_output;

			unset( $_output );
			$i++;
			// phpcs:enable, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		\set_transient( $transient_name, $output, \DAY_IN_SECONDS * 2 );

		return $ajax ? $a_output : $output;
	}
}
