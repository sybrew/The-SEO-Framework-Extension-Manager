<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Classes
 */

namespace TSF_Extension_Manager\Extension\Articles;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Articles extension for The SEO Framework
 * Copyright (C) 2019-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Require extension views trait.
 *
 * @since 2.2.1
 */
\TSF_Extension_Manager\_load_trait( 'extension/views' );

/**
 * Class TSF_Extension_Manager\Extension\Articles\Sitemap
 *
 * @since 2.0.0
 * @uses TSF_Extension_Manager\Traits
 * @access private
 * @final
 */
final class Sitemap extends Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Interface,
		\TSF_Extension_Manager\Extension_Views;

	/**
	 * @since 2.0.0
	 * @var bool Whether outputting news sitemap.
	 */
	private $doing_news_sitemap = false;

	/**
	 * The constructor, initialize sitemap for plugin.
	 *
	 * @since 2.0.0
	 */
	private function construct() {

		if ( ! $this->get_option( 'news_sitemap' ) || ! static::is_organization() ) return;

		/**
		 * @see trait TSF_Extension_Manager\Extension_Views
		 */
		$this->view_location_base = \TSFEM_E_ARTICLES_DIR_PATH . 'views' . \DIRECTORY_SEPARATOR;

		\add_filter( 'the_seo_framework_sitemap_endpoint_list', [ $this, '_register_news_sitemap_endpoint' ] );
		\add_action( 'the_seo_framework_sitemap_schemas', [ $this, '_adjust_news_sitemap_schemas' ] );

		\add_action( 'the_seo_framework_cleared_sitemap_transients', [ $this, '_delete_news_sitemap_transient' ] );

		// Don't use action `the_seo_framework_ping_search_engines`; News Sitemaps don't have a ping threshold.
		// Don't allow prerendering; News Sitemaps are small for a reason!
		// Don't discern the post. For the cron callback to work, that'd be unreliable.
		if ( \tsf()->get_option( 'ping_use_cron' ) ) {
			\add_action( 'tsf_sitemap_cron_hook', [ $this, '_ping_google_news' ] );
		} else {
			\add_action( 'the_seo_framework_cleared_sitemap_transients', [ $this, '_ping_google_news' ] );
		}
	}

	/**
	 * Registers the news sitemap endpoint.
	 *
	 * @since 2.0.0
	 * @since 2.2.0 Added Yoast SEO's endpoint for cross server compatibility with broken NGINX configs (Namecheap...).
	 * @access private
	 *
	 * @param array $list The endpoints: {
	 *   'id' => array: {
	 *      'endpoint' => string   The expected "pretty" endpoint, meant for administrative display.
	 *      'epregex'  => string   The endpoint regex, following the home path regex.
	 *                             N.B. Be wary of case sensitivity. Append the i-flag.
	 *                             N.B. Trailing slashes will cause the match to fail.
	 *                             N.B. Use ASCII-endpoints only. Don't play with UTF-8 or translation strings.
	 *      'callback' => callable The callback for the sitemap output.
	 *                             Tip: You can pass arbitrary indexes. Prefix them with an underscore to ensure forward compatibility.
	 *                             Tip: In the callback, use
	 *                                  `\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_sitemap_endpoint_list()[$sitemap_id]`
	 *                                  It returns the arguments you've passed in this filter; including your arbitrary indexes.
	 *      'robots'   => bool     Whether the endpoint should be mentioned in the robots.txt file.
	 *   }
	 * }
	 * @return array
	 */
	public function _register_news_sitemap_endpoint( $list = [] ) {
		return array_merge(
			$list,
			[
				'news'     => [
					'lock_id'  => '_news', // reserved, not used
					'endpoint' => 'sitemap-news.xml',
					'regex'    => '/^sitemap-news\.xml/i',
					'callback' => [ $this, '_output_news_sitemap' ],
					'robots'   => true,
				],
				'news-alt' => [
					'lock_id'  => '_news', // reserved, not used
					'endpoint' => 'news-sitemap.xml',
					'regex'    => '/^news-sitemap\.xml/i',
					'callback' => [ $this, '_output_news_sitemap' ],
					'robots'   => false,
				],
			]
		);
	}

	/**
	 * Outputs Google News sitemap.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	public function _output_news_sitemap() {

		// No locking protection. The sitemap is (and should) remain small, so it'd be redundant; even counter-productive.
		$this->doing_news_sitemap = true;

		// Remove output, if any.
		\tsf()->clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xml; charset=utf-8', true );
		}

		// Fetch sitemap content and add trailing line. Already escaped internally.
		$this->get_view( 'sitemap/news' );
		echo "\n";

		exit;
	}

	/**
	 * Adjusts the News schema markup header.
	 *
	 * @since 2.0.0
	 * @since 2.2.0 Now overwrites the sitemap schema entirely. Use a later priority to adjust this.
	 * @access private
	 *
	 * @param array $schemas The schema list. URLs are expected to be escaped.
	 * @return array
	 */
	public function _adjust_news_sitemap_schemas( $schemas ) {

		if ( ! $this->doing_news_sitemap ) return $schemas;

		// Overwrite.
		return [
			'xmlns'              => 'http://www.sitemaps.org/schemas/sitemap/0.9',
			'xmlns:xsi'          => 'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation' => [
				'http://www.sitemaps.org/schemas/sitemap/0.9',
				'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd',
				'http://www.google.com/schemas/sitemap-news/0.9',
				'http://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd',
			],
			'xmlns:news'         => 'http://www.google.com/schemas/sitemap-news/0.9',
			'xmlns:image'        => 'http://www.google.com/schemas/sitemap-image/1.1',
		];
	}

	/**
	 * Deletes news sitemap transient.
	 *
	 * @since 2.0.0
	 * @todo use TSF 4.3.0's get_sitemap_transient_key
	 * @access private
	 */
	public function _delete_news_sitemap_transient() {
		\delete_transient( $this->get_sitemap_transient_name() );
	}

	/**
	 * Pings Google News whenever a post is updated. Albeit News Article or not.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	public function _ping_google_news() {
		$pingurl = 'https://www.google.com/ping?sitemap=' . rawurlencode(
			\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url( 'news' )
		);
		\wp_safe_remote_get( $pingurl, [ 'timeout' => 3 ] );
	}

	/**
	 * Returns the sitemap transient name.
	 *
	 * @since 2.0.0
	 * @todo use TSF 4.3.0's get_sitemap_transient_key
	 *
	 * @return string The sitemap transient name.
	 */
	public function get_sitemap_transient_name() {

		$blog_id = $GLOBALS['blog_id'];
		$locale  = strtolower( \get_locale() );

		return "tsfem_articles_news_sitemap_{$blog_id}_$locale";
	}
}
