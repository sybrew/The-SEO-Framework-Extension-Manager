<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Views
 * @subpackage TSF_Extension_Manager\Extension\Articles\Sitemap;
 */

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this instanceof TSF_Extension_Manager\Extension\Articles\Sitemap or die;

$tsf = the_seo_framework();

$sitemap_bridge = \The_SEO_Framework\Bridges\Sitemap::get_instance();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
// $sitemap_bridge->output_sitemap_header(); // We don't want a stylesheet.

$sitemap_bridge->output_sitemap_urlset_open_tag();

$sitemap_generated = false;
$sitemap_content   = $tsf->get_option( 'cache_sitemap' ) ? get_transient( $this->get_sitemap_transient_name() ) : false;

if ( false === $sitemap_content ) {
	$sitemap_generated = true;

	if ( version_compare( THE_SEO_FRAMEWORK_VERSION, '4.2', '<' ) )
		class_alias( '\The_SEO_Framework\Builders\Sitemap', '\The_SEO_Framework\Builders\Sitemap\Main', true );

	$sitemap_builder = new TSF_Extension_Manager\Extension\Articles\SitemapBuilder;

	/**
	 * @see \TSF_Extension_Manager\Extension\Articles\Core::__construct()
	 */
	$sitemap_builder->set_option_indexes(
		$this->pm_index,
		$this->pm_defaults,
		$this->o_index,
		$this->o_defaults
	);

	$sitemap_builder->prepare_generation();
	$sitemap_content = $sitemap_builder->build_sitemap();
	$sitemap_builder->shutdown_generation();
	$sitemap_builder = null; // destroy class.

	// Keep the sitemap for at most 1 hour. Will expire during post actions.
	$expiration = HOUR_IN_SECONDS;

	if ( $tsf->get_option( 'cache_sitemap' ) )
		set_transient( $this->get_sitemap_transient_name(), $sitemap_content, $expiration );
}

// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Content should be escaped.
echo $sitemap_content;

$sitemap_bridge->output_sitemap_urlset_close_tag();

if ( $sitemap_generated ) {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
} else {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
}
