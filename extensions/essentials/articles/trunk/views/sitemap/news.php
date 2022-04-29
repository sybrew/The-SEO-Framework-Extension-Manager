<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Views
 * @subpackage TSF_Extension_Manager\Extension\Articles\Sitemap;
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this instanceof TSF_Extension_Manager\Extension\Articles\Sitemap or die;

$tsf = the_seo_framework();

$tsf->the_seo_framework_debug and $timer_start = microtime( true );

$sitemap_bridge = \The_SEO_Framework\Bridges\Sitemap::get_instance();

// $sitemap_bridge->output_sitemap_header(); // We don't want a stylesheet.
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$sitemap_bridge->output_sitemap_urlset_open_tag();

if ( version_compare( THE_SEO_FRAMEWORK_VERSION, '4.2', '<' ) )
	class_alias( '\The_SEO_Framework\Builders\Sitemap', '\The_SEO_Framework\Builders\Sitemap\Main', true );

$sitemap_news = new TSF_Extension_Manager\Extension\Articles\SitemapBuilder;
/**
 * @see \TSF_Extension_Manager\Extension\Articles\Core::__construct()
 */
$sitemap_news->set_option_indexes(
	$this->pm_index,
	$this->pm_defaults,
	$this->o_index,
	$this->o_defaults
);

// phpcs:ignore, WordPress.Security.EscapeOutput
echo $sitemap_news->_generate_sitemap( $this->get_sitemap_transient_name() );

$sitemap_bridge->output_sitemap_urlset_close_tag();

if ( $sitemap_news->news_is_regenerated ) {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
} else {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
}

// Destruct class.
$sitemap_news = null;

if ( $tsf->the_seo_framework_debug ) {
	echo "\n" . '<!-- Site estimated current usage: ' . number_format( memory_get_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated current usage: ' . number_format( memory_get_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Site estimated peak usage: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated peak usage: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Freed memory prior to generation: ' . number_format( $sitemap_bridge->get_freed_memory( true ) / 1024, 3 ) . ' kB -->';
	echo "\n" . '<!-- Sitemap generation time: ' . number_format( microtime( true ) - $timer_start, 6 ) . ' seconds -->';
	echo "\n" . '<!-- Sitemap caching enabled: ' . ( $tsf->get_option( 'cache_sitemap' ) ? 'yes' : 'no' ) . ' -->';
	echo "\n" . '<!-- Sitemap transient key: ' . esc_html( $this->get_sitemap_transient_name() ) . ' -->';
}
