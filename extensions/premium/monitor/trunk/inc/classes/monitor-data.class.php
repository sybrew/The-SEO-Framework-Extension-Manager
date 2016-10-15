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

		$planned_dummy_data = array(
			'issues' => array(
				'title' => array(), // Are titles outputted as it should?
				'description' => array(), // Are descriptions outputted as it should?
				'canonical' => array(), // Is canonical URL equal to page, and if not - are settings applied?
				'social' => array(), // Is social meta data visible? Might also be from other plugins.
				'favicon' => array(), // Is favicon set up? If not, mark if not static in public_html/www folder.
				'duplicated' => array(), // Are there any duplicated pages? If so -> open submenu??
				'mobile' => array(), // Is theme mobile? If so, does it overflow? : 2 settings: ipad & iphone.
				'html' => array(), // Are there any HTML errors? If so, tell them.
				'php' => array(), // Is there a html closing tag, at all?
				'js' => array(), // Is JS valid? If not, tell them... ugh.
				'css' => array(), // Is CSS valid? If not, tell them... ugh.
				'img' => array(), // Are images valid, and do they support mobile? Are they also optimized for performance?
				'permalinks' => array(), // Are permalinks correctly set up? Are they too complex? Does it matter?
				'robots' => array(), // Is robots static or dynamic? If static, tell them. If dynamic, tell if it works.
				'sitemap' => array(), // Does the sitemap work? Is the sitemap valid? Is it too big?
				'performance' => array(), // Can the site load within a few seconds? Set margins/regions to test performance with a delta.
				'uptime' => array(), // Is the website down a lot? Was there a downtime? Tell them.
				'linking' => array(), // Are the internal links correct? If so, is there plenty?
				'external' => array(), // Are there enough extenral links present? If not, tell them.
			),
			'poi' => array(
				'size' => array(), // Is the website too big in size? If so, is the issue HTMl, JS, CSS, img, etc.
				'scripts' => array(), // Are there too many scripts? If so, tell them.
				'theme' => array( // Is the theme up to the latest standards? Evaluate externally by sendings "current_theme_supports" data.
					'html5' => array( true, '1476492104' ),
					'headings' => array(),
				),
				'local' => array(), // Is the business registered in Google businesses? If not, tell them.
				'amp' => array(), // Is the website AMP compatible/optimized?
				'wordcount' => array(), // Is the avg. word count OK?
				'social' => array(), // Is there any social activity? Is there a social site, and if not, tell them.
				'readability' => array(), // Is the website readable by avg people? If not, estimate level.
				'analytics' => array(), // Are there any analytical scripts or pixels present?
				'https' => array(), // Is the website secure enough? And if so, is it 302 or 301?
				'htaccess' => array(), // If there important redirects, are they fast enough?
				'server' => array(), // Is the website up to date with PHP, Apache, some security headers, etc.?
				'activity' => array(), // Has the website recently been updated, and if so, are they visible?
			),
			'stats' => array(
				'uptime' => array(), // minute check, timeout @ 10 seconds
				'performance' => array(), // home page + all links (once!) check, weekly. Timeout @ 10 seconds. Take avg?
				'traffic' => array(), // Local monitor, externally processed.
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
