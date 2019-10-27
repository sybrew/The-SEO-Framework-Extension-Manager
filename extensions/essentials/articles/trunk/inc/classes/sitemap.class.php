<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Classes
 */

namespace TSF_Extension_Manager\Extension\Articles;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Class TSF_Extension_Manager\Extension\Articles\Sitemap
 *
 * @since 2.0.0
 * @uses TSF_Extension_Manager\Traits
 * @access private
 * @final
 */
final class Sitemap extends Core {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * @since 2.0.0
	 * @var string $sitemap_id
	 */
	private $sitemap_id = 'news';

	/**
	 * @since 2.0.0
	 * @var bool $doing_news_sitemap
	 */
	private $doing_news_sitemap = false;

	/**
	 * The constructor, initialize sitemap for plugin.
	 *
	 * @since 2.0.0
	 */
	private function construct() {

		if ( ! $this->get_option( 'news_sitemap' ) ) return;

		\add_filter( 'the_seo_framework_sitemap_endpoint_list', [ $this, '_register_news_sitemap_endpoint' ] );
		\add_action( 'the_seo_framework_sitemap_header', [ $this, '_do_news_sitemap_header' ] );
		\add_action( 'the_seo_framework_sitemap_schemas', [ $this, '_adjust_news_sitemap_schemas' ] );

		\add_action( 'the_seo_framework_delete_cache_sitemap', [ $this, '_delete_news_sitemap_transient' ] );
	}

	/**
	 * Registers the news sitemap endpoint.
	 *
	 * @since 2.0.0
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

		$list['news'] = [
			'endpoint' => 'siteamp-news.xml',
			'regex'    => '/^sitemap-news.xml/i',
			'callback' => [ $this, '_output_news_sitemap' ],
			'robots'   => true,
		];

		return $list;
	}

	/**
	 * Adjusts the HTML header for the news sitemap.
	 *
	 * @since 2.0.0
	 * @access private
	 * @see `The_SEO_Framework\Bridges\Sitemap::get_sitemap_endpoint_list()`.
	 * @see `$this->_register_news_sitemap_endpoint()`
	 *
	 * @param string $sitemap_id The sitemap ID.
	 */
	public function _do_news_sitemap_header( $sitemap_id = '' ) {

		if ( $this->sitemap_id !== $sitemap_id ) return;

		$this->doing_news_sitemap = true;

		\the_seo_framework()->clean_response_header();

		if ( ! headers_sent() ) {
			\status_header( 200 );
			header( 'Content-type: text/xml; charset=utf-8', true );
		}
	}

	/**
	 * Outputs Google News sitemap.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $sitemap_id The sitemap ID.
	 */
	public function _output_news_sitemap( $sitemap_id ) {

		include TSFEM_E_ARTICLES_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . 'sitemap' . DIRECTORY_SEPARATOR . 'news.php';

		echo PHP_EOL;
		exit;
	}

	/**
	 * Adjusts the News schema markup header.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param array $schemas The schema list. URLs are expected to be escaped.
	 * @return array
	 */
	public function _adjust_news_sitemap_schemas( $schemas ) {

		if ( ! $this->doing_news_sitemap ) return $schemas;

		// TODO this appends; should we overwrite the schema completely for sanity?
		$schemas['xmlns:news']           = 'http://www.google.com/schemas/sitemap-news/0.9';
		$schemas['xsi:schemaLocation'][] = 'http://www.google.com/schemas/sitemap-news/0.9';
		$schemas['xsi:schemaLocation'][] = 'http://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd';
		$schemas['xmlns:image']          = 'http://www.google.com/schemas/sitemap-image/1.1';

		// We don't want a stylesheet.
		unset( $schemas['xmlns:xhtml'] );

		return $schemas;
	}

	/**
	 * Deletes news sitemap transient.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	public function _delete_news_sitemap_transient() {
		\delete_transient( $this->get_sitemap_transient_name() );
	}

	/**
	 * Returns the sitemap transient name.
	 *
	 * @since 2.0.0
	 *
	 * @return string The sitemap transient name.
	 */
	public function get_sitemap_transient_name() {

		$blog_id = $GLOBALS['blog_id'];
		$locale  = strtolower( \get_locale() );

		return "tsfem_articles_news_sitemap_{$blog_id}_$locale";
	}
}
