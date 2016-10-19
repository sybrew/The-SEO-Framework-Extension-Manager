<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Monitor_Data
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Require extension options trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'extension-options' );

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use TSF_Extension_Manager\Construct_Core_Once_Interface as Construct_Core_Once_Interface;
use TSF_Extension_Manager\Extension_Options as Extension_Options;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager_Extension\Monitor_Data
 *
 * Holds extension data functions.
 *
 * @since 1.0.0
 * @access private
 */
class Monitor_Data {
	use Enclose_Stray_Private, Construct_Core_Once_Interface, Extension_Options;

	private function construct() {

		//* Verify integrity.
		$that = __NAMESPACE__ . ( is_admin() ? '\\Monitor_Admin' : '\\Monitor_Frontend' );
		$this instanceof $that or wp_die( -1 );

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'monitor';
	}

	protected function get_data( $type, $default = null ) {

		$data = $this->get_remote_data( $type, false );

		return empty( $data ) ? $default : $data;
	}

	protected function get_remote_data( $type = '', $ajax = false ) {

		if ( ! $type )
			return false;

		$data = $this->get_option( $type, array() );

		//* DEBUG.
		static $debug = true;
		$debug and $this->fetch_new_data( $ajax ) and $data = $this->get_session_data( $type ) and $debug = false;

		if ( empty( $data ) ) {
			$this->fetch_new_data( $ajax );
			$data = $this->get_session_data( $type );
		}

		return $data;
	}

	protected function fetch_new_data( $ajax = false ) {

		static $fetched = null;

		if ( isset( $fetched ) )
			return $fetched;

		$data = $this->api_get_remote_data();

		if ( is_array( $data ) ) {
			foreach ( $data as $type => $values ) {
				$this->store_session_data( $type, $values );
				$this->update_option( $type, $values );
			}
			$fetched = true;
		} else {
			$fetched = false;
		}

		return $fetched;
	}

	protected function api_get_remote_data() {

		//* This dummy data does NOT represent the final outcome. It's still in very much dev-environment.
		//* Please, don't expect anything from what you see here. It's a mind-map.
		$planned_dummy_data = array(
			'issues' => array(
				// Are titles outputted as it should? Take 3 samples and test them.
				'title' => array(
					'home' => array(
						array(
							'id' => 0,
							'value' => 'My WordPress Site &mdash; Just another WordPress site',
						),
					),
					'category' => array(
						array(
							'id' => 1,
							'value' => 'Category: Uncategorized &mdash; My WordPress Site',
						),
					),
					'post' => array(
						array(
							'id' => 1,
							'value' => 'Hello world! &mdash; My WordPress Site',
						),
					),
				),
				// Are descriptions outputted as it should? Take 3 samples and test them.
				'description' => array(
					'home' => array(
						array(
							'id' => 0,
							'value' => 'Just another WordPress site on My WordPress Site',
						),
					),
					'category' => array(
						array(
							'id' => 1,
							'value' => 'Uncategorized on My WordPress Site',
						),
					),
					'post' => array(
						array(
							'id' => 1,
							'value' => 'Hello world! on My WordPress Site | Welcome to WordPress. This is your first post. Edit or delete it, then start writing!',
						),
					),
				),
				// Is canonical URL equal to page, and if not - are settings applied?
				'canonical' => array(
					'home' => array(
						array(
							'id' => 0,
							'value' => 'Just another WordPress site on My WordPress Site',
						),
					),
					'category' => array(
						array(
							'id' => 1,
							'value' => 'Uncategorized on My WordPress Site',
						),
					),
					'post' => array(
						array(
							'id' => 1,
							'value' => 'Hello world! on My WordPress Site | Welcome to WordPress. This is your first post. Edit or delete it, then start writing!',
						),
					),
				),
				// Is favicon set up? If not, mark if not static in public_html/www folder.
				'favicon' => array(
					// Test for <link rel="icon" href="http://..../cropped-icon-512x512-32x32.jpg" sizes="32x32" /> on the homepage.
					'meta' => true,
					// Test for http://example.com/favicon.ico
					'static' => false,
				),
				// Are there any duplicated pages? If so -> open submenu?? TODO.
				'duplicated' => array(),
				// Is theme mobile? If so, does it overflow? : 2 settings: ipad & iphone.
				'mobile' => array(
					'home' => array(
						array(
							'id' => 0,
							'desktop' => 1, // Good.
							'tablet' => -1, // Overflown.
							'phone' => 0, // Bad.
						),
					),
					'category' => array(
						array(
							'id' => 1,
							'desktop' => 1, // Good.
							'tablet' => -1, // Overflown.
							'phone' => 0, // Bad.
						),
					),
					'post' => array(
						array(
							'id' => 1,
							'desktop' => 1, // Good.
							'tablet' => -1, // Overflown.
							'phone' => 0, // Bad.
						),
					),
				),
				// Are there any HTML errors? If so, tell them. TODO
				'html' => array(
					// Is this even feasible?
				),
				// Is there a html closing tag, at all?
				'php' => array(
					'home' => array(
						array(
							'id' => 0,
							'closed' => 1, // Good.
						),
					),
					'category' => array(
						array(
							'id' => 1,
							'closed' => 0, // Bad.
						),
					),
					'post' => array(
						array(
							'id' => 1,
							'closed' => 1, // Good.
						),
					),
				),
				// Are images valid, and do they support mobile? Are they also optimized for performance? TODO
				'img' => array(
					// Is this even feasible?
				),
				// Is robots static or dynamic? If static, tell them. If dynamic, tell if it works.
				'robots' => array(
					'located' => true,
					'value' => "User-agent: * \nDisallow: /", // This is expected with indexing disabled. Test with internal robots.txt, or from TSF?
				),
				// Does the sitemap work? Is the sitemap valid? Is it too big?
				'sitemap' => array(
					'located' => true,
					'size' => '48234', // 48kB ish
					'index' => false, // Is it a web of sitemaps?
					'valid' => true, // Determined by XML parser (no spaces before..)
				),
				// Can the site load within a few seconds? Set margins/regions to test performance with a delta.
				'performance' => array(
					'site_loc' => array(
						'lat' => '52.127',
						'long' => '4.668',
					),
					'server_loc' => array(
						'lat' => '52.344',
						'long' => '4.484',
					),
					// Ping right before start. If delta is bigger (%) than normal, take estimated defaults.
					'ping_google_nl' => '1', // baseline0
					'ping_google_uk' => '7', // baseline1
					'ping_google_nyc' => '28', // baseline2
					'ping_site' => '4', // 4ms
					'pageload_site_excl' => '120', // 120ms excluding files
					'pageload_site_incl' => '950', // 950 inc. files
					'local_files' => '9', // 9 files, use delta baselines + ping
					'ext_files' => '4', // 4 external files, use baseline1?
				),
				// Is the website down a lot? Was there a downtime? Tell them.
				'uptime' => array(
					'1d' => '0', // 0 minutes out in the last day
					'1w' => '0', // 0 minutes out in the last week
					'1m' => '2', // 2 minutes out in the last 31 days
				),
				// Are the internal links correct? If so, is there plenty? TODO
				'linking' => array(
					'', // Is this even feasible?
				),
				// Are there enough external links present? If not, tell them.
				'external' => array(
					'', // Is this even feasible?
				),
			),
			'poi' => array(
				// Is the website too big in size? If so, is the issue HTMl, JS, CSS, img, etc.
				'size' => array(
					'home' => array(
						array(
							'id' => 0,
							'size' => '400000', // 400kB.
						),
					),
					'category' => array(
						array(
							'id' => 1,
							'size' => '700000', // 700kB.
						),
					),
					'post' => array(
						array(
							'id' => 1,
							'size' => '4000000', // 4MB.
						),
					),
				),
				// Are there too many scripts? If so, tell them.
				'scripts' => array(
					'local_files' => '9', // 9 local files.
					'ext_files' => '4', // 4 external files.
					'css' => '12', // 12 CSS files.
					'js' => '1', // 1 JS file.
					'inline' => '5', // 5 inlines.. consider exluding WP core scripts from the equation.
				),
				// Is the website AMP ready/optimized?
				'amp' => array(
					'post' => array(
						array(
							'id' => 1,
							'amp' => true,
						),
					),
				),
				// Are there any analytical scripts or pixels present? Check at home page.
				'analytics' => array(
					'found' => true,
					'type' => 'ga', // Google Analytics.
				),
				// Is the website secure enough? And if so, is it 302 or 301?
				'https' => array(
					'https' => true,
					'type' => '302', // 302 redirect.
				),
				// Is the website up to date with PHP, Apache, some security headers, etc.?
				'server' => array(
					'engine' => array(
						'http' => 'HTTP/1.1 200 OK', // HTTP/1.1
						'php' => '0', // couldn't be determined :).
						'server' => 'nginx',
					),
					'security' => array(
						'Strict-Transport-Security' => 'max-age=63072000; includeSubdomains; preload',
						'Upgrade-Insecure-Requests' => '1',
						'X-Frame-Options' => 'SAMEORIGIN',
						'X-Content-Type-Options' => 'nosniff',
						'X-XSS-Protection' => '1; mode=block',
					),
					'performance' => array(
						'Content-Encoding' => 'gzip',
						'Connection' => 'Keep-Alive',
						'Keep-Alive' => 'timeout=7, max=149',
						'Content-Length' => '6181',
					),
				),
				// Has the website recently been updated, and if so, are they visible?
				// This data has to be sent to the server. The server determines "differences" in visible content.
				'activity' => array(
					'home' => array(
						'id' => 0,
						'updated' => '1496589921', // UNIX time.
					),
					'post' => array(
						'id' => 778,
						'updated' => '1496589773', // UNIX time.
					),
					'page' => array(
						'id' => 794,
						'updated' => '1496599250', // UNIX time.
					),
				),
			),
			'stats' => array(
				// Check for uptime. Interpreted in graph?
				// 5 minute check, timeout @ 10 seconds. 30 days are held per site.
				'uptime' => array(
					array(
						//* UNIX time start of date. + every 5 minutes a 1 or 0. (288 checks a day total). Compressed to sequences.
						'1473984000' => '288x1',
						'1474070400' => '120x1,2x0,106x1',
						'1474156800' => '288x1',
						'1474243200' => '288x1',
						'1474329600' => '288x1',
						'1474416000' => '288x1',
						'1474502400' => '288x1',
						'1474588800' => '288x1',
						'1474675200' => '288x1',
						'1474761600' => '288x1',
						'1474848000' => '288x1',
						'1474934400' => '288x1',
						'1475020800' => '288x1',
						'1475107200' => '288x1',
						'1475193600' => '288x1',
						'1475280000' => '288x1',
						'1475366400' => '288x1',
						'1475452800' => '288x1',
						'1475539200' => '98x1,5x0,125x1', // = 98*5 (490) min good. 5*5 (25) min outage. 125*5 (625) min good.
						'1475625600' => '288x1',
						'1475712000' => '288x1',
						'1475798400' => '288x1',
						'1475884800' => '288x1',
						'1475971200' => '288x1',
						'1476057600' => '288x1',
						'1476144000' => '288x1',
						'1476230400' => '288x1',
						'1476316800' => '288x1',
						'1476403200' => '288x1',
						'1476489600' => '288x1',
						'1476576000' => '288x1',
					),
				),
				// home page + all links (once!) check??? TODO really?... weekly. Timeout @ 10 seconds. Take avg?
				'performance' => array(
					'site_loc' => array(
						'lat' => '52.127',
						'long' => '4.668',
					),
					'server_loc' => array(
						'lat' => '52.344',
						'long' => '4.484',
					),
					// Ping right before start. If delta is bigger (%) than normal, take estimated defaults.
					'ping_google_nl' => '1', // baseline0
					'ping_google_uk' => '7', // baseline1
					'ping_google_nyc' => '28', // baseline2
					'ping_site' => '4', // 4ms
					'results' => array(
						'home' => array(
							'time' => '1475971200', // unix timestamp
							'pageload_site_excl' => '120', // 120ms excluding files
							'pageload_site_incl' => '950', // 950ms inc. files
							'local_files' => '9', // 9 files, use delta baselines + ping
							'ext_files' => '4', // 4 external files, use baseline1?
						),
					),
				),
				// Local monitor, externally processed. Add "enable" button as in Jetpack? TODO
				'traffic' => array(
					'', // TODO
				),
			),
		);

		return $planned_dummy_data;
	}

	protected function get_session_data( $type ) {
		return $this->store_session_data( $type );
	}

	protected function store_session_data( $type = '', $data = null ) {

		static $data_cache = array();

		if ( isset( $data_cache[ $type ] ) )
			return $data_cache[ $type ];

		if ( isset( $data ) )
			return $data_cache[ $type ] = $data;

		return false;
	}
}
