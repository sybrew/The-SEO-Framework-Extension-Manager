<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Views
 * @subpackage TSF_Extension_Manager\Extension\Articles\Sitemap;
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret ) or die;

// TSF_EXTENSION_MANAGER_USE_MODERN_TSF: THE_SEO_FRAMEWORK_DEBUG will become always available.
defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG and $timer_start = hrtime( true );

$registry = sitemap_registry();

// $sitemap_bridge->output_sitemap_header(); // We don't want a stylesheet.
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$registry->output_sitemap_urlset_open_tag();

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
// todo use TSF 4.3.0's get_sitemap_transient_key
echo $sitemap_news->_generate_sitemap( $this->get_sitemap_transient_name() );

$registry->output_sitemap_urlset_close_tag();

if ( $sitemap_news->news_is_regenerated ) {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
} else {
	echo "\n" . '<!-- ' . esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
}

// Destruct class.
$sitemap_news = null;

// TSF_EXTENSION_MANAGER_USE_MODERN_TSF: THE_SEO_FRAMEWORK_DEBUG will become always available.
if ( defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ) {
	echo "\n" . '<!-- Site estimated current usage: ' . number_format( memory_get_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated current usage: ' . number_format( memory_get_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Site estimated peak usage: ' . number_format( memory_get_peak_usage() / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- System estimated peak usage: ' . number_format( memory_get_peak_usage( true ) / 1024 / 1024, 3 ) . ' MB -->';
	echo "\n" . '<!-- Freed memory prior to generation: ' . number_format( $registry->get_freed_memory( true ) / 1024, 3 ) . ' kB -->';
	echo "\n" . '<!-- Sitemap generation time: ' . number_format( ( hrtime( true ) - $timer_start ) / 1e9, 6 ) . ' seconds -->';
	echo "\n" . '<!-- Sitemap caching enabled: ' . ( tsf()->get_option( 'cache_sitemap' ) ? 'yes' : 'no' ) . ' -->';
	echo "\n" . '<!-- Sitemap transient key: ' . esc_html( $this->get_sitemap_transient_name() ) . ' -->';
}
