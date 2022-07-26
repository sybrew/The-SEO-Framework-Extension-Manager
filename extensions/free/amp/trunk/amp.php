<?php
/**
 * @package TSF_Extension_Manager\Extension\AMP
 */

namespace TSF_Extension_Manager\Extension\AMP;

/**
 * Extension Name: AMP
 * Extension URI: https://theseoframework.com/extensions/amp/
 * Extension Description: The AMP extension binds The SEO Framework to the [AMP plugin](https://wordpress.org/plugins/amp/) for [AMP](https://www.ampproject.org/) supported articles and pages.
 * Extension Version: 1.2.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * AMP extension for The SEO Framework
 * Copyright (C) 2017-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * The AMP extension version.
 *
 * @since 1.0.0
 * @param string
 */
\define( 'TSFEM_E_AMP_VERSION', '1.1.0' );

\add_action( 'wp', __NAMESPACE__ . '\\_amp_init', 11 );
/**
 * Initializes the extension. Runs after AMP plugin action 'amp_init'.
 *
 * @since 1.0.0
 * @since 1.1.0 Now uses WP-AMP's new API.
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _amp_init() {

	if ( \is_admin() ) {
		// Bail on admin. No admin dashboard yet.
		return false;
	} else {
		$is_amp = false;

		if ( \function_exists( '\\is_amp_endpoint' ) ) {
			$is_amp = \is_amp_endpoint();
		} elseif ( \defined( 'AMP_QUERY_VAR' ) ) {
			$is_amp = \get_query_var( AMP_QUERY_VAR, false ) !== false;
		}

		if ( $is_amp ) {
			new Front;
			return true;
		}
	}
	return false;
}

/**
 * Class TSF_Extension_Manager\Extension\AMP\Front
 *
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Front {
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * The constructor, initialize class actions.
	 */
	private function construct() {

		\add_action( 'amp_post_template_head', [ $this, 'do_output_hook' ], 11 );

		/**
		 * Removes the default AMP articles metadata output.
		 *
		 * @since 1.0.0
		 * @link https://theseoframework.com/extensions/articles/
		 * @param bool $remove
		 */
		if ( \apply_filters( 'the_seo_framework_remove_amp_articles', false ) )
			$this->remove_amp_articles();
	}

	/**
	 * Outputs metadata and adds hooks in the AMP HTML head.
	 *
	 * @since 1.0.0
	 * @link https://www.ampproject.org/docs/reference/spec
	 * @link https://github.com/ampproject/amphtml/tree/master/examples/metadata-examples
	 * @TODO AMP sanitizer messes with the output... annoyingly.
	 */
	public function do_output_hook() {

		\do_action( 'the_seo_framework_do_before_amp_output' );

		$output_start = hrtime( true );

		$output = '';

		$output .= $this->get_general_metadata();
		$output .= $this->get_social_metadata();
		$output .= $this->get_structured_metadata();

		$tsf = \the_seo_framework();

		$output = $tsf->get_plugin_indicator( 'before' )
				. $output
				. $tsf->get_plugin_indicator(
					'after',
					( $output_start - hrtime( true ) ) / 1e9
				);

		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
		echo PHP_EOL . $output . PHP_EOL;

		\do_action( 'the_seo_framework_do_after_amp_output' );
	}

	/**
	 * Removes AMP generic Articles Schema.org output.
	 *
	 * @since 1.0.0
	 */
	protected function remove_amp_articles() {
		\add_filter( 'amp_post_template_metadata', '\\__return_empty_array', 10 );
	}

	/**
	 * Returns general metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.0.1
	 *
	 * @return string The general metadata.
	 */
	protected function get_general_metadata() {
		return \the_seo_framework()->the_description();
	}

	/**
	 * Outputs social metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.0.1
	 * @since 1.0.2 Added filters.
	 *
	 * @return string The social metadata.
	 */
	protected function get_social_metadata() {

		$tsf = \the_seo_framework();

		/**
		 * Adds content before the output.
		 *
		 * @since 1.0.2
		 * @param string $before
		 */
		$before = (string) \apply_filters( 'the_seo_framework_amp_pre', '' );

		$output = $tsf->og_image()
				. $tsf->og_locale()
				. $tsf->og_type()
				. $tsf->og_title()
				. $tsf->og_description()
				. $tsf->og_url()
				. $tsf->og_sitename()
				. $tsf->facebook_publisher()
				. $tsf->facebook_author()
				. $tsf->facebook_app_id()
				. $tsf->article_published_time()
				. $tsf->article_modified_time()
				. $tsf->twitter_card()
				. $tsf->twitter_site()
				. $tsf->twitter_creator()
				. $tsf->twitter_title()
				. $tsf->twitter_description()
				. $tsf->twitter_image();

		/**
		 * Adds content after the output.
		 *
		 * @since 1.0.2
		 * @param string $after
		 */
		$after = (string) \apply_filters( 'the_seo_framework_amp_pro', '' );

		return $before . $output . $after;
	}

	/**
	 * Returns structured metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.2.0
	 *
	 * @return string The structured metadata.
	 */
	protected function get_structured_metadata() {
		return \the_seo_framework()->ld_json();
	}
}
