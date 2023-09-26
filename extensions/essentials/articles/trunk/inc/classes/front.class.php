<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Classes
 */

namespace TSF_Extension_Manager\Extension\Articles;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Articles extension for The SEO Framework
 * Copyright (C) 2017-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Articles\Front
 *
 * @since 1.2.0
 * @uses TSF_Extension_Manager\Traits
 * @access private
 * @final
 */
final class Front extends Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * If the output is invalidated, the output should be cancelled.
	 *
	 * @since 1.0.0
	 * @var array Whether the JSON output is valid : { key => bool }
	 */
	private $is_json_valid = [
		'amp'    => true,
		'nonamp' => true,
	];

	/**
	 * @since 1.1.0
	 * @since 2.0.0 Value changed from 'tsfem-e-articles-logo'.
	 * @var string The image size name.
	 */
	private $image_size_name = 'tsfem-e-articles-logo-rect';

	/**
	 * The constructor, initialize plugin.
	 *
	 * @since 1.0.0
	 */
	private function construct() {
		\add_action( 'the_seo_framework_do_before_output', [ $this, '_init_articles_output' ], 10 );
		\add_action( 'the_seo_framework_do_before_amp_output', [ $this, '_init_articles_output' ], 10 );
	}

	/**
	 * Initializes Articles output.
	 *
	 * @since 2.0.0
	 * @since 2.0.4 1. No longer works when query is exploited.
	 *              2. Now fetches the correct post type on singular archives.
	 *
	 * @return void Early when query is not supported.
	 */
	public function _init_articles_output() {

		$tsf = static::$tsf;

		if ( ! $tsf->is_singular() || $tsf->is_query_exploited() ) return;

		$post_type = $tsf->get_post_type_real_ID();
		$settings  = $this->get_option( 'post_types' );

		if ( empty( $settings[ $post_type ]['enabled'] ) ) return;

		if ( $this->is_amp() ) {
			// Initialize output in The SEO Framework's front-end AMP meta object.
			\add_filter( 'the_seo_framework_amp_pro', [ $this, '_articles_hook_amp_output' ] );
		} else {
			\add_action( 'the_seo_framework_after_meta_output', [ $this, '_output_articles_json' ] );
		}
	}

	/**
	 * Registers logo image size in WordPress.
	 *
	 * Note that it takes an initial render before the URL is available in WordPress;
	 * their caches don't update as we process it.
	 *
	 * @since 1.1.0
	 * @since 2.0.0 Updated the logo guidelines.
	 */
	private function register_logo_image_size() {
		\add_image_size( $this->image_size_name, 600, 60, false );
	}

	/**
	 * Determines if the current page is AMP supported.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Now supports AMP v0.5+ endpoints.
	 * @uses const AMP_QUERY_VAR
	 *
	 * @return bool True if AMP is enabled.
	 */
	private function is_amp() {

		static $is_amp;

		if ( isset( $is_amp ) )
			return $is_amp;

		if ( \function_exists( '\\is_amp_endpoint' ) ) {
			$is_amp = \is_amp_endpoint();
		} elseif ( \defined( 'AMP_QUERY_VAR' ) ) {
			$is_amp = \get_query_var( AMP_QUERY_VAR, false ) !== false;
		} else {
			$is_amp = false;
		}

		return $is_amp;
	}

	/**
	 * Determines if the script is invalidated.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if valid, false otherwise.
	 */
	private function is_json_valid() {
		return $this->is_amp() ? $this->is_json_valid['amp'] : $this->is_json_valid['nonamp'];
	}

	/**
	 * Invalidates JSON output.
	 *
	 * @since 1.0.0
	 * @see $this->is_json_valid
	 * @see $this->is_json_valid()
	 *
	 * @param string $what What to invalidate.
	 */
	private function invalidate( $what = 'both' ) {

		switch ( $what ) :
			case 'both':
				$this->is_json_valid['amp'] = $this->is_json_valid['nonamp'] = false;
				break;

			case 'amp':
				$this->is_json_valid['amp'] = false;
				break;

			case 'nonamp':
				$this->is_json_valid['nonamp'] = false;
				break;
		endswitch;
	}

	/**
	 * Returns current WP_Post object.
	 *
	 * @since 1.0.0
	 *
	 * @return object WP_Post
	 */
	private function get_current_post() {
		static $post;
		return $post ?? $post = \get_post( $this->get_current_id() );
	}

	/**
	 * Returns current WP_Query object ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int Queried Object ID.
	 */
	private function get_current_id() {
		static $id;
		return $id ?? ( $id = \get_queried_object_id() );
	}

	/**
	 * Outputs the AMP Articles script.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Changed from action to filter output.
	 * @access private
	 *
	 * @param string $output The current AMP pro output.
	 * @return string
	 */
	public function _articles_hook_amp_output( $output = '' ) {
		return $output . $this->_get_articles_json_output();
	}

	/**
	 * Outputs Articles JSON.
	 *
	 * @since 2.1.1
	 * @access private
	 */
	public function _output_articles_json() {
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- is escaped.
		echo $this->_get_articles_json_output();
	}

	/**
	 * Returns the article JSON-LD script output.
	 * Runs at 'the_seo_framework_after_output' filter.
	 *
	 * @since 1.0.0
	 * @since 2.0.2 No longer minifies the script when script debugging is activated.
	 * @since 2.1.1 No longer rectifies the date.
	 * @link https://developers.google.com/search/docs/advanced/structured-data/article
	 * @access private
	 *
	 * @return string The additional JSON-LD Article scripts.
	 */
	public function _get_articles_json_output() {

		$data = [];

		foreach ( $this->generate_articles_json_output() as $entry ) {
			if ( $entry )
				$data[] = $entry;

			if ( ! $this->is_json_valid() )
				return '';
		}

		// Build data, fetch it later.
		array_filter( $data, [ $this, 'build_article_data' ] );

		/**
		 * @since 1.4.0
		 * @param array $data The Articles schema data.
		 */
		$data = \apply_filters(
			'the_seo_framework_articles_data',
			$this->get_article_data()
		);

		if ( $data ) {
			$options  = 0;
			$options |= \JSON_UNESCAPED_SLASHES;
			$options |= \SCRIPT_DEBUG ? \JSON_PRETTY_PRINT : 0;

			return sprintf( '<script type="application/ld+json">%s</script>', json_encode( $data, $options ) ) . "\n";
		}

		return '';
	}

	/**
	 * Generates Article data.
	 *
	 * @since 2.2.1
	 * @access private
	 * @generator
	 */
	protected function generate_articles_json_output() {
		yield $this->get_article_context();
		yield $this->get_article_type();
		yield $this->get_article_main_entity();
		yield $this->get_article_headline();
		yield $this->get_article_image();
		yield $this->get_article_published_date();
		yield $this->get_article_modified_date();
		yield $this->get_article_author();
		yield $this->get_article_publisher();
		yield $this->get_article_description();
	}

	/**
	 * Builds up article data by shifting array keys through reset.
	 *
	 * @since 1.0.0
	 * @see $this->get_article_data()
	 *
	 * @param array $array The input element
	 */
	private function build_article_data( $array ) {
		$this->get_article_data( false, $array );
	}

	/**
	 * Builds up and returns article data by shifting array keys through reset.
	 *
	 * @since 1.0.0
	 * @see $this->build_article_data()
	 *
	 * @param bool  $get   Whether to return the accumulated data.
	 * @param array $entry The input element
	 * @return array The article data.
	 */
	private function get_article_data( $get = true, $entry = [] ) {

		static $data = [];

		if ( $get )
			return $data;

		$data[ key( $entry ) ] = reset( $entry );

		return [];
	}

	/**
	 * Returns the Article Context.
	 *
	 * @since 1.0.0
	 *
	 * @requiredSchema Always
	 * @ignoredSchema Never
	 * @return array The Article context.
	 */
	private function get_article_context() {
		return [ '@context' => 'https://schema.org' ];
	}

	/**
	 * Returns the Article Type.
	 *
	 * Possibilities: 'Article', 'NewsArticle', 'BlogPosting'.
	 * 'Article' is most conventional and convinient, and covers all three types.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Now listens to post meta.
	 * @since 2.1.0 Now stops generation when type is 'disbaled'.
	 *
	 * @requiredSchema Always
	 * @ignoredSchema Never
	 * @return array The Article type.
	 */
	private function get_article_type() {

		$type = static::filter_article_type( $this->get_post_meta(
			'type',
			$this->get_option( 'post_types' )[ \get_post_type() ]['default_type'] ?? 'Article' // Default
		) );

		if ( 'disabled' === $type ) {
			$this->invalidate( 'both' );
			return [];
		}

		return [ '@type' => $type ];
	}

	/**
	 * Returns the Article Main Entity of Page.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Added TSF v3.0 compat.
	 *
	 * @requiredSchema Never
	 * @ignoredSchema nonAMP
	 * @return array The Article's main entity of the page.
	 */
	private function get_article_main_entity() {

		if ( ! $this->is_json_valid() )
			return [];

		$url = static::$tsf->get_current_permalink();

		if ( ! $url ) {
			$this->invalidate( 'amp' );
			return [];
		}

		return [
			'mainEntityOfPage' => [
				'@type' => 'WebPage',
				'@id'   => $url,
			],
		];
	}

	/**
	 * Returns the Article Headline.
	 *
	 * @NOTE If the title is above 110 chars or is empty : {
	 *   'amp'    => Will invalidate output.
	 *   'nonamp' => Will return empty.
	 * }
	 * @since 1.0.0
	 * @since 1.3.0 Added TSF v3.1 compat.
	 * @since 2.0.0 Now trims the title to 110 characters.
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema Never
	 * @return array The Article's Headline.
	 */
	private function get_article_headline() {

		if ( ! $this->is_json_valid() )
			return [];

		$id  = $this->get_current_id();
		$tsf = static::$tsf;

		$title = $tsf->get_raw_generated_title( [
			'id'       => $id,
			'taxonomy' => '',
		] );

		// Does not consider UTF-8 support. However, the regex does.
		if ( \strlen( $title ) > 110 ) {
			preg_match( '/.{0,110}([^\P{Po}\'\"]|\p{Z}|$){1}/su', trim( $title ), $matches );
			$title = isset( $matches[0] ) ? ( $matches[0] ?: '' ) : '';
			$title = trim( $title );
		}

		if ( ! $title ) {
			$this->invalidate( 'both' );
			return [];
		}

		return [
			'headline' => $tsf->escape_title( $title ),
		];
	}

	/**
	 * Returns the Article Image.
	 *
	 * @since 1.0.0
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema Never
	 * @return array The Article's Image
	 */
	private function get_article_image() {

		if ( ! $this->is_json_valid() )
			return [];

		/**
		 * @since 1.4.0
		 * @param array|string $images The URL of an image, an imageObject, or a array of each or both.
		 */
		$images = \apply_filters(
			'the_seo_framework_articles_images',
			$this->get_article_image_params()
		);

		if ( ! $images ) {
			$this->invalidate( 'amp' );
			return [];
		}

		return [
			'image' => $images,
		];
	}

	/**
	 * Returns image parameters for Article image.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Now uses the new image generator, and now returns multiple image objects.
	 * @since 2.0.1 Now accepts images as small as 696px for non-AMP.
	 *
	 * @return array The article image parameters. Unescaped.
	 */
	private function get_article_image_params() {

		$min_width = $this->is_amp() ? 1200 : 696;

		$images = [];

		// TODO: Do we want to take images from the content? Users have complained about this...
		// ... We'd have to implement (and revoke) a filter, however.
		foreach ( static::$tsf->get_image_details( null, false, 'schema', true ) as $image ) {

			if ( ! $image['url'] ) continue;

			if ( $image['width'] && $image['width'] >= $min_width ) {
				$images[] = [
					'@type'  => 'ImageObject',
					'url'    => $image['url'],
					'width'  => $image['width'],
					'height' => $image['height'],
				];
			} else {
				$images[] = $image['url'];
			}
		}

		return \count( $images ) > 1 ? $images : reset( $images );
	}

	/**
	 * Returns the Article Published Date.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 1. Now also outputs on non-AMP.
	 *              2. Now only invalidates AMP when something's wrong.
	 * @since 2.0.1 1. Now uses gmdate instead of date, to account for the timezone change in TSF 4.0.4.
	 *              2. Now uses the post gmt date.
	 *
	 * @requiredSchema AMP (docs)
	 * @ignoredSchema nonAMP
	 * @return array The Article's Published Date
	 */
	private function get_article_published_date() {

		if ( ! $this->is_json_valid() )
			return [];

		$post = $this->get_current_post();

		if ( ! $post ) {
			$this->invalidate( 'amp' );
			return [];
		}

		$i = strtotime( $post->post_date_gmt );

		return [
			'datePublished' => \esc_attr( gmdate( 'c', $i ) ),
		];
	}

	/**
	 * Returns the Article Modified Date.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Now also outputs on non-AMP.
	 * @since 2.0.1 1. Now uses gmdate instead of date, to account for the timezone change in TSF 4.0.4.
	 *              2. Now uses the post gmt date.
	 *
	 * @requiredSchema Never
	 * @ignoredSchema nonAMP
	 * @return array The Article's Published Date
	 */
	private function get_article_modified_date() {

		if ( ! $this->is_json_valid() )
			return [];

		$post = $this->get_current_post();

		if ( ! $post )
			return [];

		$i = strtotime( $post->post_modified_gmt );

		return [
			'dateModified' => \esc_attr( gmdate( 'c', $i ) ),
		];
	}

	/**
	 * Returns the Article Author.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 : 1. Now also outputs on non-AMP.
	 *                2. Now only invalidates AMP when something's wrong.
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema nonAMP
	 * @return array The Article's Author
	 */
	private function get_article_author() {

		if ( ! $this->is_json_valid() )
			return [];

		$post = $this->get_current_post();

		if ( ! $post ) {
			$this->invalidate( 'amp' );
			return [];
		}

		$author = \get_userdata( $post->post_author );
		$name   = $author->display_name ?? '';

		if ( ! $name ) {
			$this->invalidate( 'amp' );
			return [];
		}

		$data = [
			'author' => [
				'@type' => 'Person',
				'name'  => \esc_attr( $name ),
			],
		];

		$url = \esc_url( static::$tsf->get_author_canonical_url( $post->post_author ) );

		if ( $url )
			$data['author']['url'] = $url;

		return $data;
	}

	/**
	 * Returns the Article Publisher and logo.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 : 1. Now also outputs on non-AMP.
	 *                2. Now only invalidates AMP when something's wrong.
	 * @since 1.1.0 Now fetches TSF 3.0 logo ID.
	 * @since 2.0.0 Now tests for the knowledge type.
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema nonAMP
	 * @return array The Article's Publisher
	 */
	private function get_article_publisher() {

		if ( ! $this->is_json_valid() )
			return [];

		if ( ! static::is_organization() ) {
			$this->invalidate( 'amp' );
			return [];
		}

		$tsf = static::$tsf;

		/**
		 * @since 1.0.0
		 * @param string $name The articles publisher name.
		 */
		$name = (string) \apply_filters( 'the_seo_framework_articles_name', $tsf->get_option( 'knowledge_name' ) ) ?: $tsf->get_blogname();

		$_default_img_id = (int) $this->get_option( 'logo' )['id'] ?: (int) $tsf->get_option( 'knowledge_logo_id' ) ?: \get_option( 'site_icon' );
		/**
		 * @since 1.0.0
		 * @param int $img_id The image ID to use for the logo.
		 */
		$_img_id = (int) \apply_filters( 'the_seo_framework_articles_logo_id', 0 ) ?: $_default_img_id;

		if ( ! $_img_id ) {
			$this->invalidate( 'amp' );
			return [];
		}

		$this->register_logo_image_size();
		$resize = false;

		if ( $_default_img_id === $_img_id ) {
			$size   = $this->image_size_name;
			$resize = true;
		} else {
			$size = 'full';
		}

		$_src = \wp_get_attachment_image_src( $_img_id, $size );
		if ( $resize && isset( $_src[3] ) && ! $_src[3] ) {
			// Add intermediate size, so $_src[3] will return true next time.
			if ( $this->make_amp_logo( $_img_id ) )
				$_src = \wp_get_attachment_image_src( $_img_id, $size );
		}

		if ( \is_array( $_src ) && \count( $_src ) >= 3 ) {
			$url = $_src[0];
			$w   = $_src[1];
			$h   = $_src[2];
		}

		if ( empty( $url ) ) {
			$this->invalidate( 'amp' );
			return [];
		}

		return [
			'publisher' => [
				'@type' => 'Organization',
				'name'  => \esc_attr( $name ),
				'logo'  => [
					'@type'  => 'ImageObject',
					'url'    => \esc_url( $url, [ 'https', 'http' ] ),
					'width'  => abs( filter_var( $w, FILTER_SANITIZE_NUMBER_INT ) ),
					'height' => abs( filter_var( $h, FILTER_SANITIZE_NUMBER_INT ) ),
				],
			],
		];
	}

	/**
	 * Returns the Article Description.
	 *
	 * @since 1.0.0
	 * @since 1.0.0-gamma-2: Changed excerpt length to 155, from 400.
	 * @since 1.0.1 : 1. Now also outputs on non-AMP.
	 *                2. Now takes description from cache.
	 * @since 1.3.0 Added TSF v3.1 compat.
	 *
	 * @requiredSchema Never
	 * @ignoredSchema nonAMP
	 * @return array The Article's Description
	 */
	private function get_article_description() {

		if ( ! $this->is_json_valid() )
			return [];

		$description = \esc_attr( static::$tsf->get_description() );

		if ( ! $description ) {
			// Optional.
			return [];
		}

		return [
			'description' => \esc_attr( $description ),
		];
	}

	/**
	 * Makes AMP logo from attachment ID.
	 *
	 * @NOTE: The image size must be registered first.
	 * @see $this->register_logo_image_size().
	 * @since 1.1.0
	 *
	 * @param int $attachment_id The attachment to make a logo from.
	 * @return bool True on success, false on failure.
	 */
	private function make_amp_logo( $attachment_id ) {

		$success = false;

		$size = \wp_get_additional_image_sizes()[ $this->image_size_name ];

		$_file         = \get_attached_file( $attachment_id );
		$_resized_file = \image_make_intermediate_size( $_file, $size['width'], $size['height'], false );

		if ( $_resized_file ) {
			if ( ! \function_exists( '\\wp_generate_attachment_metadata' ) )
				require_once ABSPATH . 'wp-admin/includes/image.php';

			$_data   = \wp_generate_attachment_metadata( $attachment_id, $_file );
			$success = (bool) \wp_update_attachment_metadata( $attachment_id, $_data );
		}

		return $success;
	}
}
