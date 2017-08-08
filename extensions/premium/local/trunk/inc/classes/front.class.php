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
	 */
	private function construct() {
		$this->init();
	}

	protected function remove_scheme( $url ) {
		return str_ireplace( [ 'http://', 'https://' ], '', \esc_url( $url, [ 'http', 'https' ] ) );
	}

	protected function get_processed_packed_data() {
		return $this->get_option( 'packed_data' );
	}

	protected function get_processed_packed_data_from_url( $url ) {

		$url = $this->remove_scheme( $url );
		$data = $this->get_processed_packed_data();
		var_dump( $data );

		if ( isset( $data[ $url ] ) )
			return $data[ $url ];

		return false;
	}

	protected function get_processed_packed_data_from_id( $id = 0 ) {

		$data = $this->get_processed_packed_data();

		if ( isset( $data[ $id ] ) )
			return $data[ $id ];

		return false;
	}

	/**
	 * Initializes front-end hooks.
	 *
	 * @since 1.0.0
	 */
	private function init() {
		if ( $this->is_amp() ) {
			//* Initialize output in The SEO Framework's front-end AMP meta object.
			\add_action( 'the_seo_framework_do_after_amp_output', [ $this, '_local_hook_amp_output' ] );
		} else {
			//* Initialize output in The SEO Framework's front-end meta object.
			\add_filter( 'the_seo_framework_after_output', [ $this, '_local_hook_output' ] );
		}
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
	public function is_amp() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		return $cache = defined( 'AMP_QUERY_VAR' ) && \get_query_var( AMP_QUERY_VAR, false ) !== false;
	}

	/**
	 * Outputs the AMP Local script.
	 *
	 * @since 1.0.0
	 */
	public function _local_hook_amp_output() {
		//= Already escaped.
		echo $this->_get_local_json_output();
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
	 * Returns the Local Business JSON-LD script output.
	 * Runs at 'the_seo_framework_after_output' filter.
	 *
	 * @since 1.0.0
	 * @link https://developers.google.com/search/docs/data-types/local-businesses
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

		//* @TODO test AMP url.
		var_dump( $url );

		//= Get data by URL.
		$json = $this->get_processed_packed_data_from_url( $url );

		//= If no data is found, and when we're on the front page, try for Main department (id=1).
		// if ( ! $json && \is_front_page() )
			// $json = $this->get_processed_packed_data_from_id( 1 );

		var_dump( $json );

		if ( $json )
			return '<script type="application/ld+json">' . $json . '</script>' . PHP_EOL;

		return '';
	}
}
