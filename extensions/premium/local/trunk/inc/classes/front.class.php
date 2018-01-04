<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Front
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017-2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Front
 *
 * Holds extension front-end methods.
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Front extends Core {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * The constructor, initialize plugin.
	 *
	 * @since 1.0.0
	 */
	private function construct() {
		\add_action( 'the_seo_framework_do_before_output', [ $this, '_init' ], 10 );
		\add_action( 'the_seo_framework_do_before_amp_output', [ $this, '_init' ], 10 );
	}

	/**
	 * Initializes front-end hooks.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init() {
		if ( $this->is_amp() ) {
			//* Initialize output in The SEO Framework's front-end AMP meta object.
			\add_action( 'the_seo_framework_amp_pro', [ $this, '_local_hook_amp_output' ] );
		} else {
			//* Initialize output in The SEO Framework's front-end meta object.
			\add_filter( 'the_seo_framework_after_output', [ $this, '_local_hook_output' ] );
		}
	}

	/**
	 * Outputs the AMP Local script.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $output The current AMP pro output.
	 * @return string The added local script.
	 */
	public function _local_hook_amp_output( $output = '' ) {
		return $output .= $this->_get_local_json_output();
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
	public function _local_hook_output( $functions = [] ) {

		$functions[] = [
			'callback' => [ $this, '_get_local_json_output' ],
			'args' => [],
		];

		return $functions;
	}

	/**
	 * Determines if the current page is AMP supported.
	 *
	 * @since 1.0.0
	 * @uses const AMP_QUERY_VAR
	 * @staticvar bool $cache
	 *
	 * @return bool True if AMP is enabled.
	 */
	protected function is_amp() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		return $cache = defined( 'AMP_QUERY_VAR' ) && \get_query_var( AMP_QUERY_VAR, false ) !== false;
	}

	/**
	 * Removes schema from input $url.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url URL with or without scheme.
	 * @return string URL without scheme.
	 */
	protected function remove_scheme( $url ) {
		return str_ireplace( [ 'http://', 'https://' ], '', \esc_url( $url, [ 'http', 'https' ] ) );
	}

	/**
	 * Gets packed data.
	 *
	 * @since 1.0.0
	 *
	 * @return array The packed data.
	 */
	protected function get_processed_packed_data() {
		return $this->get_option( 'packed_data' );
	}

	/**
	 * Gets packed data from URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The URL where the data might be for.
	 * @return array The packed data.
	 */
	protected function get_processed_packed_data_from_url( $url ) {

		$url = $this->remove_scheme( $url );
		$data = $this->get_processed_packed_data();

		if ( isset( $data[ $url ] ) )
			return $data[ $url ];

		return false;
	}

	/**
	 * Gets packed data from URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The URL where the data might be for.
	 * @return array The packed data.
	 */
	protected function get_processed_packed_data_from_id( $id = 0 ) {

		$data = $this->get_processed_packed_data();

		if ( isset( $data[ $id ] ) )
			return $data[ $id ];

		return false;
	}

	/**
	 * Returns the Local Business JSON-LD script output.
	 * Runs at 'the_seo_framework_after_output' filter.
	 *
	 * @since 1.0.0
	 * @link https://developers.google.com/search/docs/data-types/local-businesses
	 * @access private
	 *
	 * @return string The additional JSON-LD Article script.
	 */
	public function _get_local_json_output() {

		if ( \is_front_page() ) {
			$url = \get_home_url();
		} elseif ( \is_singular() ) {
			$url = \get_permalink();
		} else {
			$term = \get_queried_object();
			$taxonomy = isset( $term->taxonomy ) ? $term->taxonomy : null;

			if ( ! $taxonomy )
				return '';

			$url = \get_term_link( $term, $taxonomy );
		}

		if ( ! $url )
			return '';

		//= Get data by URL.
		$json = $this->get_processed_packed_data_from_url( $url );

		if ( $json )
			return '<script type="application/ld+json">' . $json . '</script>' . PHP_EOL;

		return '';
	}
}
