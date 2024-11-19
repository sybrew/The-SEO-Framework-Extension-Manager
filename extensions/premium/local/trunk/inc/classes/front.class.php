<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Front
 */

namespace TSF_Extension_Manager\Extension\Local;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	use \TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * The constructor, initialize plugin.
	 *
	 * @since 1.0.0
	 */
	private function construct() {
		\add_action( 'the_seo_framework_after_meta_output', [ $this, '_output_local_json' ] );

		if ( ! \TSF_EXTENSION_MANAGER_USE_MODERN_TSF )
			\add_action( 'the_seo_framework_do_after_amp_output', [ $this, '_output_local_json' ] );
	}

	/**
	 * Outputs Local JSON.
	 *
	 * @since 1.1.1
	 * @access private
	 */
	public function _output_local_json() {
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- is escaped.
		echo $this->_get_local_json_output();
	}

	/**
	 * Determines if the current page is AMP supported.
	 *
	 * @since 1.0.0
	 * @uses const AMP_QUERY_VAR
	 *
	 * @return bool True if AMP is enabled.
	 */
	protected function is_amp() {
		static $memo;
		return $memo ?? (
			$memo = \defined( 'AMP_QUERY_VAR' ) && \get_query_var( \AMP_QUERY_VAR, false ) !== false
		);
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
		return str_ireplace( [ 'https://', 'http://' ], '', \sanitize_url( $url, [ 'https', 'http' ] ) );
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

		$url  = $this->remove_scheme( $url );
		$data = $this->get_processed_packed_data();

		if ( isset( $data[ $url ] ) )
			return $data[ $url ];

		return false;
	}

	/**
	 * Gets packed data from URL.
	 *
	 * @since 1.0.0
	 * @ignore Not used.
	 *
	 * @param string $id The data key to get from the pack.
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
	 * @link https://developers.google.com/search/docs/advanced/structured-data/local-business
	 * @access private
	 *
	 * @return string The additional JSON-LD Article script.
	 */
	public function _get_local_json_output() {

		if ( \is_front_page() ) {
			$url = \get_home_url();
		} elseif ( \is_singular() ) {
			$url = \get_permalink();
		} elseif ( \is_category() || \is_tag() || \is_tax() ) {
			$term     = \get_queried_object();
			$taxonomy = $term->taxonomy ?? null;

			if ( ! $taxonomy )
				return '';

			$url = \get_term_link( $term, $taxonomy );
		}

		if ( empty( $url ) )
			return '';

		// Get data by URL.
		$json = $this->get_processed_packed_data_from_url( $url );

		// Empty JSON is only 2 characters long.
		if ( $json && \strlen( $json ) > 2 )
			return sprintf( '<script type="application/ld+json">%s</script>', $json ) . "\n"; // Keep XHTML Valid!

		return '';
	}
}
