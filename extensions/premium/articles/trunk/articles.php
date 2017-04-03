<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles
 */
namespace TSF_Extension_Manager\Extension;

/**
 * Extension Name: Articles - *gamma*
 * Extension URI: https://premium.theseoframework.com/extensions/articles/
 * Extension Description: The Articles extension enhances your published posts by automatically adding [both AMP and non-AMP Structured Data](https://developers.google.com/search/docs/data-types/articles). Premium until γ-test is done.
 * Extension Version: 1.0.0-***γ-2***
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Articles extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface as Construct_Master_Once_Final_Interface;

/**
 * The extension version.
 * @since 1.0.0
 */
define( 'TSFEM_E_ARTICLES_VERSION', '1.0.0-gamma-2' );

/**
 * Removes AMP articles if AMP extension is active.
 * The output is erroneous on non-posts. Pages shouldn't do AMP.
 * @since 1.0.0
 */
\add_filter( 'the_seo_framework_remove_amp_articles', '\\__return_true' );

\add_action( 'the_seo_framework_do_before_output', __NAMESPACE__ . '\\_articles_init', 10 );
\add_action( 'the_seo_framework_do_before_amp_output', __NAMESPACE__ . '\\_articles_init', 10 );
/**
 * Initialize the extension.
 *
 * @since 1.0.0
 * @staticvar bool $loaded True when loaded.
 * @action 'the_seo_framework_do_before_output'
 * @action 'the_seo_framework_do_before_amp_output'
 * @priority 10
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _articles_init() {

	static $loaded = null;

	if ( isset( $loaded ) )
		return $loaded;

	$loaded = false;

	if ( \the_seo_framework()->is_single() && 'organization' === \the_seo_framework()->get_option( 'knowledge_type' ) ) {
		new \TSF_Extension_Manager\Extension\Articles;
		$loaded = true;
	}

	return $loaded;
}

/**
 * Class TSF_Extension_Manager\Extension\Articles
 *
 * @final Please don't extend extensions.
 */
final class Articles {
	use Enclose_Core_Final, Construct_Master_Once_Final_Interface;

	/**
	 * States if the output is valid.
	 * If the output is invalidated, the output should be cancelled.
	 *
	 * @since 1.0.0
	 * @var array $is_json_valid : { key => bool }
	 */
	private $is_json_valid = array();

	/**
	 * The constructor, initialize plugin.
	 */
	private function construct() {

		$this->is_json_valid = array(
			'amp' => true,
			'nonamp' => true,
		);

		$this->init();
	}

	/**
	 * Determines if the current page is AMP supported.
	 *
	 * @since 1.0.0
	 * @uses const AMP_QUERY_VAR
	 *
	 * @return bool True if AMP is enabled.
	 */
	public function is_amp() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = defined( 'AMP_QUERY_VAR' ) && \get_query_var( AMP_QUERY_VAR, false ) !== false;
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
	 *
	 * @param string $what
	 */
	private function invalidate( $what = 'both' ) {

		switch ( $what ) :
			case 'both' :
				$this->is_json_valid['amp'] = $this->is_json_valid['nonamp'] = false;
				break;

			case 'amp' :
				$this->is_json_valid['amp'] = false;
				break;

			case 'nonamp' :
				$this->is_json_valid['nonamp'] = false;
				break;
		endswitch;
	}

	/**
	 * Returns current WP_Post object.
	 *
	 * @since 1.0.0
	 * @staticvar object $post
	 *
	 * @return object WP_Post
	 */
	private function get_current_post() {

		static $post = null;

		return isset( $post ) ? $post : $post = \get_post( $this->get_current_id() );
	}

	/**
	 * Returns current WP_Query object ID.
	 *
	 * @since 1.0.0
	 * @staticvar int $id
	 *
	 * @return int Queried Object ID.
	 */
	private function get_current_id() {

		static $id = null;

		return $id ?: $id = \get_queried_object_id();
	}

	/**
	 * Initializes hooks.
	 *
	 * @since 1.0.0
	 */
	private function init() {
		if ( $this->is_amp() ) {
			//* Initialize output in The SEO Framework's front-end AMP meta object.
			\add_action( 'the_seo_framework_do_after_amp_output', array( $this, '_articles_hook_amp_output' ) );
		} else {
			//* Initialize output in The SEO Framework's front-end meta object.
			\add_filter( 'the_seo_framework_after_output', array( $this, '_articles_hook_output' ) );
		}
	}

	/**
	 * Outputs the AMP Articles script.
	 *
	 * @since 1.0.0
	 */
	public function _articles_hook_amp_output() {
		//* Already escaped.
		echo $this->_get_articles_json_output();
	}

	/**
	 * Hooks into 'the_seo_framework_after_output' filter.
	 * This allows output object caching.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $functions The hooked functions.
	 * @return array The hooked functions.
	 */
	public function _articles_hook_output( $functions = array() ) {

		$functions[] = array(
			'callback' => array( $this, '_get_articles_json_output' ),
			'args' => array(),
		);

		return $functions;
	}

	/**
	 * Returns the article JSON-LD script output.
	 * Runs at 'the_seo_framework_after_output' filter.
	 *
	 * @since 1.0.0
	 * @link https://developers.google.com/search/docs/data-types/articles
	 *
	 * @return string The additional JSON-LD Article scripts.
	 */
	public function _get_articles_json_output() {

		\the_seo_framework()->set_timezone();

		$data = array(
			$this->get_article_context(),
			$this->get_article_type(),
			$this->get_article_main_entity(),
			$this->get_article_headline(),
			$this->get_article_image(),
			$this->get_article_published_date(),
			$this->get_article_modified_date(),
			$this->get_article_author(),
			$this->get_article_publisher(),
			$this->get_article_description(),
		);

		\the_seo_framework()->reset_timezone();

		if ( ! $this->is_json_valid() )
			return '';

		//* Build data, and fetch it.
		array_filter( array_filter( $data ), array( $this, 'build_article_data' ) );
		$data = $this->get_article_data();

		if ( ! empty( $data ) )
			return sprintf( '<script type="application/ld+json">%s</script>', json_encode( $data ) ) . PHP_EOL;

		return '';
	}

	/**
	 * Builds up article data by shifting array keys through reset.
	 *
	 * @since 1.0.0
	 * @see $this->get_article_data
	 *
	 * @param array $array The input element
	 */
	private function build_article_data( array $array ) {
		$this->get_article_data( false, $array );
	}

	/**
	 * Builds up and returns article data by shifting array keys through reset.
	 *
	 * @since 1.0.0
	 * @staticvar $data The generated data.
	 * @see $this->build_article_data
	 *
	 * @param bool $get Whether to return the accumulated data.
	 * @param array $array The input element
	 * @return array The article data.
	 */
	private function get_article_data( $get = true, array $entry = array() ) {

		static $data = array();

		if ( $get )
			return $data;

		$data[ key( $entry ) ] = reset( $entry );

		return array();
	}

	/**
	 * Returns the Article Context.
	 *
	 * @requiredSchema Always
	 * @ignoredSchema Never
	 * @return array The Article context.
	 */
	private function get_article_context() {
		return array( '@context' => 'http://schema.org' );
	}

	/**
	 * Returns the Article Type.
	 *
	 * Possibilities: 'Article', 'NewsArticle', 'BlogPosting'.
	 * 'Article' is most conventional and convinient, and covers all three types.
	 *
	 * @todo TSF allow selection of article/news/blogpost.
	 * @todo Maybe extension? i.e. News SEO.
	 *
	 * @requiredSchema Always
	 * @ignoredSchema Never
	 * @return array The Article type.
	 */
	private function get_article_type() {
		return array( '@type' => 'Article' );
	}

	/**
	 * Returns the Article Main Entity of Page.
	 *
	 * @requiredSchema Never
	 * @ignoredSchema nonAMP
	 * @return array The Article's main entity of the page.
	 */
	private function get_article_main_entity() {

		if ( ! $this->is_amp() || ! $this->is_json_valid() )
			return array();

		return array(
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id' => \the_seo_framework()->the_url_from_cache(),
			),
		);
	}

	/**
	 * Returns the Article Headline.
	 *
	 * @NOTE If the title is above 110 chars or is empty : {
	 *   'amp'    => Will invalidate output.
	 *   'nonamp' => Will return empty.
	 * }
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema Never
	 * @return array The Article's Headline.
	 */
	private function get_article_headline() {

		if ( ! $this->is_json_valid() )
			return array();

		$id = $this->get_current_id();
		$title = \the_seo_framework()->post_title_from_ID( $id ) ?: \the_seo_framework()->title_from_custom_field( '', false, $id );
		$title = trim( \the_seo_framework()->s_title_raw( $title ) );

		if ( ! $title || mb_strlen( $title ) > 110 ) {
			$this->invalidate( 'amp' );
			return array();
		}

		return array(
			'headline' => \the_seo_framework()->escape_title( $title ),
		);
	}

	/**
	 * Returns the Article Image.
	 *
	 * @NOTE If the image is smaller than 696 pixels width : {
	 *   'amp'    => Will invalidate output.
	 *   'nonamp' => Will return empty.
	 * }
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema Never
	 * @return array The Article's Image
	 */
	private function get_article_image() {

		if ( ! $this->is_json_valid() )
			return array();

		$image = $this->get_article_image_params();

		if ( empty( $image['url'] ) ) {
			$this->invalidate( 'amp' );
			return array();
		}

		return array(
			'image' => array(
				'@type' => 'ImageObject',
				'url' => \esc_url( $image['url'] ),
				'height' => abs( filter_var( $image['height'], FILTER_SANITIZE_NUMBER_INT ) ),
				'width' => abs( filter_var( $image['width'], FILTER_SANITIZE_NUMBER_INT ) ),
			),
		);
	}

	/**
	 * Returns image parameters for Article image.
	 *
	 * @since 1.0.0
	 *
	 * @return array The article image parameters. Unescaped.
	 */
	private function get_article_image_params() {

		$id = $this->get_current_id();
		$w = $h = 0;

		if ( $url = \the_seo_framework()->get_social_image_url_from_post_meta( $id, true ) ) {

			//* TSF 2.9+
			$dimensions = \the_seo_framework()->image_dimensions;

			$d = ! empty( $dimensions[ $id ] ) ? $dimensions[ $id ] : false;
			if ( $d ) {
				$w = $d['width'];
				$h = $d['height'];
			}

			if ( $w >= 696 )
				goto retvals;
		}

		//* Don't use `\the_seo_framework()->get_image_from_post_thumbnail` because it will overwrite vars.
		if ( $_img_id = \get_post_thumbnail_id( $id ) ) {

			$_src = \wp_get_attachment_image_src( $_img_id, 'full', false );

			if ( count( $_src ) >= 3 ) {
				$url = $_src[0];
				$w   = $_src[1];
				$h   = $_src[2];

				if ( $w >= 696 )
					goto retvals;
			}
		}

		retempty :;

		return array();

		retvals :;

		return array(
			'url' => $url,
			'width' => $w,
			'height' => $h,
		);
	}

	/**
	 * Returns the Article Published Date.
	 *
	 * @since 1.0.0
	 *
	 * @requiredSchema AMP (docs)
	 * @ignoredSchema nonAMP
	 * @return array The Article's Published Date
	 */
	private function get_article_published_date() {

		if ( ! $this->is_amp() || ! $this->is_json_valid() )
			return array();

		if ( ! $post = $this->get_current_post() ) {
			$this->invalidate( 'both' );
			return array();
		}

		$i = strtotime( $post->post_date );

		return array(
			'datePublished' => \esc_attr( date( 'c', $i ) ),
		);
	}

	/**
	 * Returns the Article Modified Date.
	 *
	 * @since 1.0.0
	 *
	 * @requiredSchema Never
	 * @ignoredSchema nonAMP
	 * @return array The Article's Published Date
	 */
	private function get_article_modified_date() {

		if ( ! $this->is_amp() || ! $this->is_json_valid() )
			return array();

		if ( ! $post = $this->get_current_post() )
			return array();

		$i = strtotime( $post->post_modified );

		return array(
			'dateModified' => \esc_attr( date( 'c', $i ) ),
		);
	}

	/**
	 * Returns the Article Author.
	 *
	 * @since 1.0.0
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema nonAMP
	 * @return array The Article's Author
	 */
	private function get_article_author() {

		if ( ! $this->is_amp() || ! $this->is_json_valid() )
			return array();

		if ( ! $post = $this->get_current_post() ) {
			$this->invalidate( 'both' );
			return array();
		}

		$author = \get_userdata( $post->post_author );
		$name = $author->display_name;

		return array(
			'author' => array(
				'@type' => 'Person',
				'name' => \esc_attr( $name ),
			),
		);
	}

	/**
	 * Returns the Article Publisher and logo.
	 *
	 * @since 1.0.0
	 *
	 * @requiredSchema AMP
	 * @ignoredSchema nonAMP
	 * @return array The Article's Publisher
	 */
	private function get_article_publisher() {

		if ( ! $this->is_amp() || ! $this->is_json_valid() )
			return array();

		/**
		 * Applies filters the_seo_framework_articles_name : string
		 * @since 1.0.0
		 */
		$name = (string) \apply_filters( 'the_seo_framework_articles_name', \the_seo_framework()->get_option( 'knowledge_name' ) ) ?: \the_seo_framework()->get_blogname();

		$_default_img_id = (int) \get_option( 'site_icon' );
		/**
		 * Applies filters the_seo_framework_articles_logo_id : int
		 * @since 1.0.0
		 * @todo make option.
		 */
		$_img_id = (int) \apply_filters( 'the_seo_framework_articles_logo_id', 0 ) ?: $_default_img_id;

		if ( ! $_img_id ) {
			$this->invalidate( 'both' );
			return array();
		}

		if ( $_default_img_id === $_img_id ) {
			$size = array( 60, 60 );
		} else {
			$size = 'full';
		}

		$_src = \wp_get_attachment_image_src( $_img_id, $size );

		if ( count( $_src ) >= 3 ) {
			$url = $_src[0];
			$w   = $_src[1];
			$h   = $_src[2];
		}

		if ( empty( $url ) ) {
			$this->invalidate( 'both' );
			return array();
		}

		return array(
			'publisher' => array(
				'@type' => 'Organization',
				'name' => \esc_attr( $name ),
				'logo' => array(
					'@type' => 'ImageObject',
					'url' => \esc_url( $url ),
					'width' => abs( filter_var( $w, FILTER_SANITIZE_NUMBER_INT ) ),
					'height' => abs( filter_var( $h, FILTER_SANITIZE_NUMBER_INT ) ),
				),
			),
		);
	}

	/**
	 * Returns the Article Description.
	 *
	 * @since 1.0.0
	 * @since 1.0.0-gamma-2: Changed excerpt length to 155, from 400.
	 *
	 * @requiredSchema Never
	 * @ignoredSchema nonAMP
	 * @return array The Article's Description
	 */
	private function get_article_description() {

		if ( ! $this->is_amp() || ! $this->is_json_valid() )
			return array();

		$id = $this->get_current_id();

		//* 155 length is an arbitrary guess, because there's no documentation on this.
		$description = \the_seo_framework()->description_from_custom_field( array( 'id' => $id ) ) ?: \the_seo_framework()->generate_excerpt( $id, '', 155 );

		return array(
			'description' => \esc_attr( $description ),
		);
	}
}
