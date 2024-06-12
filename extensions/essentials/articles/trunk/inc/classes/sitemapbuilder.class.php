<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Classes
 */

namespace TSF_Extension_Manager\Extension\Articles;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Articles extension for The SEO Framework
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, Generic.Files.OneObjectStructurePerFile.MultipleFound -- transition from TSF v4.x to 5.x

if ( \TSF_EXTENSION_MANAGER_USE_MODERN_TSF ) {
	// phpcs:ignore, Squiz.Commenting.ClassComment.Missing
	abstract class SitemapBuilderTransition extends \The_SEO_Framework\Sitemap\Optimized\Main {}
} else {
	// phpcs:ignore, Squiz.Commenting.ClassComment.Missing, Generic.Classes.DuplicateClassName.Found
	abstract class SitemapBuilderTransition extends \The_SEO_Framework\Builders\Sitemap\Main {};
}

/**
 * Class TSF_Extension_Manager\Extension\Articles\SitemapBuilder
 *
 * Builds the Google News sitemap.
 *
 * @since 2.0.0
 * @since 2.3.1 Now (temporarily) extends a transition class.
 * @access private
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class SitemapBuilder extends SitemapBuilderTransition {
	use \TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Extension_Post_Meta;

	/**
	 * @since 2.2.0
	 * @var bool
	 */
	public $news_is_regenerated = false;

	/**
	 * Sets option indexes.
	 *
	 * @since 2.0.0
	 *
	 * @param string $pm_index    The post meta index for Articles.
	 * @param array  $pm_defaults The default post meta for Articles.
	 * @param string $o_index     The option index for Articles.
	 * @param array  $o_defaults  The default options for Articles.
	 */
	public function set_option_indexes( $pm_index, $pm_defaults, $o_index, $o_defaults ) {

		/**
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 */
		$this->pm_index    = $pm_index;
		$this->pm_defaults = $pm_defaults;

		/**
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 */
		$this->o_index    = $o_index;
		$this->o_defaults = $o_defaults;
	}

	/**
	 * Returns the generated sitemap. Also stores it in the database when caching is enabled.
	 *
	 * @since 2.2.0
	 * @access private
	 * TODO Make the transient name accessible instead of passing as argument.
	 * @todo use TSF 4.3.0's get_sitemap_transient_key
	 *
	 * @param string $transient_name The sitemap transient name.
	 * @return string The sitemap content.
	 */
	public function _generate_sitemap( $transient_name ) {

		if ( \TSF_EXTENSION_MANAGER_USE_MODERN_TSF ) {
			$_caching_enabled = \tsf()->sitemap()->cache()->is_sitemap_cache_enabled();
		} else {
			$_caching_enabled = \The_SEO_Framework\Bridges\Sitemap::get_instance()->sitemap_cache_enabled();
		}

		$sitemap_content = $_caching_enabled ? \get_transient( $transient_name ) : false;

		if ( false === $sitemap_content ) {
			$this->prepare_generation();

			$sitemap_content = $this->build_sitemap();

			$this->shutdown_generation();
			$this->news_is_regenerated = true;

			if ( $_caching_enabled ) {
				\set_transient(
					$transient_name,
					$sitemap_content,
					HOUR_IN_SECONDS // Keep the sitemap for at most 1 hour. Will expire during post actions.
				);
			}
		}

		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Content should be escaped.
		return $sitemap_content;
	}

	/**
	 * Returns the sitemap content.
	 *
	 * @since 2.0.0
	 * @abstract
	 *
	 * @return string The sitemap content.
	 */
	public function build_sitemap() {

		$content = '';

		/**
		 * @since TSF 2.2.9
		 * @param bool $timestamp Whether to display the timestamp.
		 */
		$timestamp = (bool) \apply_filters( 'the_seo_framework_sitemap_timestamp', true );

		if ( $timestamp ) {
			$content .= sprintf(
				'<!-- %s -->',
				sprintf(
					/* translators: %s = timestamp */
					\esc_html__( 'Sitemap is generated on %s', 'autodescription' ),
					\current_time( 'Y-m-d H:i:s \G\M\T' )
				)
			) . "\n";
		}

		$post_type_options = $this->get_option( 'post_types' );

		$post_types = [];
		foreach ( $post_type_options as $post_type => $_data ) {
			if ( ! empty( $_data['enabled'] ) )
				$post_types[] = $post_type;
		}

		if ( ! $post_types ) return $content;

		/**
		 * @since 2.0.0
		 * @since 2.1.0 Now filters by date query, according to guidelines (see link).
		 * @link <https://support.google.com/news/publisher-center/answer/9606710>
		 * @param array $args The query arguments.
		 */
		$_args = \apply_filters(
			'the_seo_framework_sitemap_articles_news_sitemap_query_args',
			[
				'posts_per_page'   => \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
					? \tsf()->sitemap()->utils()->get_sitemap_post_limit()
					: $this->get_sitemap_post_limit(),
				'post_type'        => $post_types,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'has_password'     => false,
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => false,
				'no_found_rows'    => true,
				'date_query'       => [
					'column' => 'post_date_gmt',
					// phpcs:ignore, WordPress.DateTime.RestrictedFunctions.date_date -- Already rectified by TSF: gmdate === date
					'after'  => date( 'c', time() - ( \DAY_IN_SECONDS * 2.5 ) ),
				],
			]
		);

		$wp_query = new \WP_Query;
		$wp_query->init();

		$wp_query->query = $wp_query->query_vars = $_args;

		$post_ids = $wp_query->get_posts();

		// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- $count is passed by reference.
		foreach ( $this->generate_url_item_values( $post_ids, $count ) as $_values ) {
			// No more than 1000 complex items are allowed. (ref:https://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd)
			// Also stated here: https://support.google.com/news/publisher-center/answer/6075793
			$content .= $this->build_url_item( $_values );
			if ( $count > 999 ) break;
		}
		// phpcs:enable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

		return $content;
	}

	/**
	 * Generates front-and blog page sitemap URL item values.
	 *
	 * @since 2.0.0
	 * @uses loc
	 * @link <https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd>
	 * @uses news:publication_date, news:title
	 * @link <https://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd>
	 * @uses image:loc
	 * @link <https://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd>
	 * @generator
	 * @iterator
	 *
	 * @param iterable $post_ids The post IDs to go over.
	 * @param int      $count    The yield count.
	 * @yield array|void : {
	 *   string loc
	 *   string lastmod
	 *   string priority
	 * }
	 */
	protected function generate_url_item_values( $post_ids, &$count = 0 ) {

		foreach ( $post_ids as $post_id ) {
			if ( ! $this->is_post_eligible( $post_id ) ) continue;

			// Reset.
			$_values = [];

			// @see https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd
			$_values['loc'] = \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
				? \tsf()->uri()->get_generated_url( [ 'id' => $post_id ] )
				: \tsf()->create_canonical_url( [ 'id' => $post_id ] );
			// lastmod is redundant for news.
			// changefreq is deprecated.
			// priority is deprecated.
			// any:other are deprecated.

			if ( ! $_values['loc'] ) continue;

			// Get after <loc>. Saves some memory.
			$post = \get_post( $post_id );

			// For title, don't use `\tsf()->get_raw_custom_field_title( [ 'id' => $post_id ] )`.
			// Expect the publisher to acknowledge sane defaults.

			// @see https://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd
			$_values['news'] = [
				// publication(name/language) is inferred later. Save processing power and memory during generation here.
				// access is deprecated.
				// genres is deprecated.
				'publication_date' => $post->post_date_gmt ?? '0000-00-00 00:00:00',
				'title'            => $post->post_title ?? '',
				// keywords is deprecated.
				// stock_tickers is deprecated.
			];

			if (
				   '0000-00-00 00:00:00' === $_values['news']['publication_date']
				|| ! \strlen( $_values['news']['title'] )
			) continue;

			// FIXME this will gather all sorts of data from the image that we do not need via merge_extra_image_details
			// But, so does get_post()...
			$image_details = current( \tsf()->get_image_details( [ 'id' => $post_id ], true, 'sitemap' ) );

			// @see https://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd
			$_values['image'] = [
				'loc' => $image_details['url'] ?? '',
				// caption in inferred from post description. Tested: this field is ignored.
				// geo_location is inferred from publication.
				// title is inferred from title. Tested: this field is ignored.
				// license is implied.
			];

			++$count;
			yield $_values;
		}
	}

	/**
	 * Builds and returns a sitemap URL item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args : {
	 *   string $loc                    : The item's URI.                 Required.
	 *   string $news:publication_date  : The item's published date, GMT. Required.
	 *   string $news:title             : The item's title.               Required.
	 *   string $image:loc              : The items' image location.      Optional.
	 * }
	 * @return string The sitemap item.
	 */
	protected function build_url_item( $args ) {

		if ( empty( $args['loc'] ) ) return '';

		static $publication;
		if ( ! $publication ) {
			// @see https://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd

			/**
			 * @since 1.0.0
			 * @param string $name The articles publisher name.
			 */
			$name = (string) \apply_filters(
				'the_seo_framework_articles_name',
				\tsf()->get_option( 'knowledge_name' ) ?: (
					\TSF_EXTENSION_MANAGER_USE_MODERN_TSF
						? \tsf()->data()->blog()->get_public_blog_name()
						: \tsf()->get_blogname()
				)
			);

			$locale = str_replace( '_', '-', \get_locale() );
			$locale = preg_match( '/(zh-cn|zh-tw|[a-z]{2,3})/i', $locale, $matches ) ? $matches[1] : 'en';

			$publication = [
				'news:name'     => \esc_xml( $name ),
				'news:language' => strtolower( $locale ),
			];
		}

		$data = [
			'loc'       => $this->escape_xml_url_query( $args['loc'] ),
			'news:news' => [
				'news:publication'      => $publication, // already escaped
				'news:publication_date' => \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
					? \tsf()->format()->time()->convert_to_preferred_format( $args['news']['publication_date'] )
					: \tsf()->gmt2date( \tsf()->get_timestamp_format(), $args['news']['publication_date'] ),
				'news:title'            => \esc_xml( $args['news']['title'] ),
			],
		];

		if ( ! empty( $args['image']['loc'] ) ) {
			$data['image:image'] = [
				'image:loc' => $this->escape_xml_url_query( $args['image']['loc'] ),
			];
		}

		return $this->create_xml( [ 'url' => $data ], 1 );
	}

	/**
	 * Creates XML from array input.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data  The data to create an XML item from. Expected to be escaped!
	 * @param int   $level The iteration level. Default 1 (one level in from urlset).
	 *                     Affects non-mandatory tab indentation for readability.
	 * @return string The XML data.
	 */
	private function create_xml( $data, $level = 1 ) {

		$out = '';

		foreach ( $data as $key => $value ) {
			$tabs = str_repeat( "\t", $level );

			if ( \is_array( $value ) )
				$value = "\n" . $this->create_xml( $value, $level + 1 ) . $tabs;

			$out .= "$tabs<$key>$value</$key>\n";
		}

		return $out;
	}

	/**
	 * Escapes URL queries for XML.
	 *
	 * @since 2.3.1
	 *
	 * @param mixed $url The URL to escape.
	 * @return string A value that's safe for XML use.
	 */
	private function escape_xml_url_query( $url ) {

		$q = parse_url( $url, \PHP_URL_QUERY );

		if ( $q ) {
			parse_str( $q, $r );
			// Don't replace. Tokenize. The query part might be part of the URL (in some alien environment).
			$url = strtok( $url, '?' ) . '?' . http_build_query( $r, null, '&amp;', \PHP_QUERY_RFC3986 );
		}

		return $url;
	}

	/**
	 * Asserts whether a post is eligible for the Google News sitemap.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id The post ID to assert.
	 * @return bool
	 */
	private function is_post_eligible( $post_id ) {

		if ( \TSF_EXTENSION_MANAGER_USE_MODERN_TSF ) {
			if ( ! \tsf()->sitemap()->utils()->is_post_included_in_sitemap( $post_id ) ) return false;
		} else {
			if ( ! $this->is_post_included_in_sitemap( $post_id ) ) return false;
		}

		$this->set_extension_post_meta_id( $post_id );

		$type = $this->get_post_meta( 'type' );

		// We can collapse these 5 lines into one using PHP 7+...
		// $type = $type ?: ( ( $this->get_option( 'post_types' )[ \get_post_type( $post_id ) ] ?? [] )['default_type'] ?? 'Article' );
		if ( ! $type ) {
			$post_type_options = $this->get_option( 'post_types' );
			$post_type         = \get_post_type( $post_id );

			$type = isset( $post_type_options[ $post_type ]['default_type'] )
				? $post_type_options[ $post_type ]['default_type']
				: 'Article';
		}

		return 'NewsArticle' === $type;
	}
}
