<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Classes
 */

namespace TSF_Extension_Manager\Extension\Cord;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Local extension for The SEO Framework
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

/**
 * Class TSF_Extension_Manager\Extension\Cord\Front
 *
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @access private
 * @final
 */
final class Front extends Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * The constructor, initialize plugin.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$a_options = $this->get_option( 'analytics' );

		if ( ! empty( $a_options['google_analytics']['tracking_id'] ) ) {
			/**
			 * This filter is great for GDPR cookie consent, where you can dynamically enable
			 * the analytics script based on visitor preferences.
			 *
			 * @since 1.0.0
			 * @param bool $enabled
			 */
			if ( \apply_filters( 'the_seo_framework_cord_ga_enabled', true ) ) {
				// TODO use filter 'wp_resource_hints' instead? This allows collapsing to unique resource URIs.
				\add_action( 'wp_head', [ $this, '_output_google_analytics_preconnect_links' ], 0 );

				\add_action( 'wp_body_open', [ $this, '_output_google_analytics_tracking' ], 0 );
				\add_action( 'wp_footer', [ $this, '_output_google_analytics_tracking' ], 0 );
			}
		}

		if ( ! empty( $a_options['facebook_pixel']['pixel_id'] ) ) {
			/**
			 * This filter is great for GDPR cookie consent, where you can dynamically enable
			 * the analytics script based on visitor preferences.
			 *
			 * @since 1.0.0
			 * @param bool $enabled
			 */
			if ( \apply_filters( 'the_seo_framework_cord_fbp_enabled', true ) ) {
				// Don't set preconnection. The Facebook pixel script is based on a connection and cookie.

				// It must be outputted in the header, because the Facebook script needs to initialize itself before first paint.
				\add_action( 'wp_head', [ $this, '_output_facebook_pixel_tracking' ], 99 );
			}
		}
	}

	/**
	 * Outputs Google Analytics prefetch and preconnect links.
	 * This resolves the asynchronous script quicker, making for more accurate analytics.
	 *
	 * The prefetch is redundant, but it adds support to browsers who do not support preconnecting.
	 *
	 * @since 1.0.0
	 */
	public function _output_google_analytics_preconnect_links() {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) ) return;

		$tracking_id = \esc_js( trim( $this->get_option( 'analytics' )['google_analytics']['tracking_id'] ) );

		// Get first few chars before '-'. Only G-tag (GA4) is supported now.
		if ( 'G' !== strtok( $tracking_id, '-' ) ) return;

		echo '<link rel="dns-prefetch" href="https://www.googletagmanager.com/" />', "\n"; // Keep XHTML valid!
		echo '<link rel="preconnect" href="https://www.googletagmanager.com/" crossorigin="anonymous" />', "\n"; // Keep XHTML valid!
		// gtag.js collects via google-analytics.com and `analytics.google.com`.
		echo '<link rel="dns-prefetch" href="https://www.google-analytics.com/" />', "\n"; // Keep XHTML valid!
		echo '<link rel="preconnect" href="https://www.google-analytics.com/" crossorigin="anonymous" />', "\n"; // Keep XHTML valid!
	}

	/**
	 * Outputs the Google Anlytics tracking code.
	 * Includes enhanced search display.
	 *
	 * @since 1.0.0
	 *
	 * @return void Early if already run. This method tries to run twice for old theme compat.
	 */
	public function _output_google_analytics_tracking() {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) ) return;

		$options     = $this->get_option( 'analytics' )['google_analytics'];
		$tracking_id = \esc_js( trim( $options['tracking_id'] ) );

		// Get first few chars before '-'. Only G-tag (GA4) is supported now.
		if ( 'G' !== strtok( $tracking_id, '-' ) ) return;

		// https://developers.google.com/analytics/devguides/collection/ga4/reference/config
		$config = [];

		// TODO add field to allow multiple domains? This seems exaggeratingly redundant.
		// https://developers.google.com/analytics/devguides/collection/gtagjs/cross-domain#automatically_link_domains
		$page_location = '';
		$home_domain   = \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
			? \tsf()->uri()->utils()->get_site_host()
			: parse_url( \tsf()->get_raw_home_canonical_url(), \PHP_URL_HOST );

		// Fix and normalize search link recognition.
		// Is this still needed? Maybe Google will add support for this again with GA4 -- keep it.
		if ( \is_search() && $GLOBALS['wp_rewrite']->get_search_permastruct() ) {
			$search_query  = \get_search_query();
			$page_location = \esc_js( \add_query_arg(
				[ 's' => rawurlencode( $search_query ) ],
				\get_search_link( $search_query )
			) );
		}

		if ( $home_domain )
			$config['linker']['domains'] = [ $home_domain ];

		if ( $page_location )
			$config['page_location'] = $page_location;

		// Don't array_filter -- false may also be a value.
		$config = json_encode( $config );

		// GA4
		$script = <<<JS
			window.dataLayer = window.dataLayer || [];
			function gtag() {
				dataLayer.push( arguments )
			}
			gtag( 'js', new Date );

			gtag( 'config', '{$tracking_id}', $config );
			JS;

		$script = $this->minify_script( $script );

		// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.EnqueuedResources.NonEnqueuedScript
		// Keep XHTML valid!
		echo <<<HTML
			<script async="async" src="https://www.googletagmanager.com/gtag/js?id={$tracking_id}"></script>
			<script>$script</script>
			HTML;
		// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}

	/**
	 * Outputs the Facebook pixel scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void Early if already run. This method tries to run twice for old theme compat.
	 */
	public function _output_facebook_pixel_tracking() {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) ) return;

		$options = $this->get_option( 'analytics' )['facebook_pixel'];

		// Essentially, these are the same. Sanity.
		$pixel_id_js   = \esc_js( trim( $options['pixel_id'] ) );
		$pixel_id_attr = \esc_attr( trim( $options['pixel_id'] ) );

		$version = '2.0';

		/**
		 * The connect Facebook link COULD be internationalized. But, we're not outputting visual elements.
		 *
		 * @link <https://developers.facebook.com/docs/internationalization/>
		 */
		$script = <<<JS
			!function(f,b,e,v,n,t,s) {
				if ( f.fbq ) return;
				n = f.fbq = function () {
					n.callMethod ? n.callMethod.apply( n, arguments ) : n.queue.push( arguments )
				};
				t = b.createElement( e );

				if ( ! f._fbq )
					f._fbq = n;

				n.push    = n;
				n.loaded  = !0;
				n.version = '{$version}';
				n.queue   = [];
				t.async   = !0;
				t.src     = v;

				s = b.getElementsByTagName( e )[0];
				s.parentNode.insertBefore( t, s )
			}( window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js' );

			fbq( 'init', '{$pixel_id_js}' );
			fbq( 'track', 'PageView' );
			JS;

		$script = $this->minify_script( $script );

		// Keep XHTML valid!
		$noscript = <<<NOJS
			<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={$pixel_id_attr}&ev=PageView&noscript=1" />
			NOJS;

		$noscript = str_replace( [ "\n", "\t" ], '', $noscript );

		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<script>$script</script>\n";
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<noscript>$noscript</noscript>\n";
	}

	/**
	 * Minifies script based on our coding standards.
	 * Pretty straightforward, does not consider contextual stuff. Only to be used in controlled environments.
	 *
	 * @since 1.0.0
	 *
	 * @param string $script The non-minified script.
	 * @return string The minified script.
	 */
	private function minify_script( $script ) {

		// Get omni-spaced first!
		$s_and_r = [
			' ? '  => '?',
			' ! '  => '!',
			' :'   => ':',
			': '   => ':',
			' = '  => '=',
			' || ' => '||',
			' && ' => '&&',
			' =+ ' => '=+',
			' )'   => ')',
			') '   => ')',
			' ('   => '(',
			'( '   => '(',
			'{ '   => '{',
			' }'   => '}',
			', '   => ',',
			'; '   => ';',
		];

		$script = str_replace( [ "\n", "\t" ], '', $script );
		$script = str_replace( [ '  ', '  ' ], ' ', $script ); // odd, even
		$script = str_replace( array_keys( $s_and_r ), array_values( $s_and_r ), $script );

		return $script;
	}
}
