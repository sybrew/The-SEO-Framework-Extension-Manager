<?php
/**
 * @package TSF_Extension_Manager\Extension\AMP\Front
 */

namespace TSF_Extension_Manager\Extension\AMP;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * AMP extension for The SEO Framework
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
 * Class TSF_Extension_Manager\Extension\AMP\Front
 *
 * @since 1.0.0
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

		/**
		 * @since 1.0.0
		 */
		\do_action( 'the_seo_framework_do_before_amp_output' );

		if ( \TSF_EXTENSION_MANAGER_USE_MODERN_TSF ) {
			// Our URI outputs do not pertain to AMP. AMP takes care of this.
			\add_filter(
				'the_seo_framework_meta_generator_pools',
				static fn( $pools ) => array_diff( $pools, [ 'URI' ] ),
			);

			\tsf()->print_seo_meta_tags();
		} else {
			// phpcs:ignore -- All callbacks escape their output.
			echo "\n", $this->get_general_metadata(), $this->get_social_metadata(), $this->get_structured_metadata(), "\n";
		}

		/**
		 * @since 1.0.0
		 */
		\do_action( 'the_seo_framework_do_after_amp_output' );
	}

	/**
	 * Removes AMP generic Articles Schema.org output.
	 *
	 * @since 1.0.0
	 */
	protected function remove_amp_articles() {
		\add_filter( 'amp_post_template_metadata', '__return_empty_array', 10 );
	}

	/**
	 * Returns general metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.0.1
	 * @deprecated Remove if we only support TSF 4.3+
	 *
	 * @return string The general metadata.
	 */
	protected function get_general_metadata() {
		return \tsf()->the_description();
	}

	/**
	 * Outputs social metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.0.1
	 * @since 1.0.2 Added filters.
	 * @deprecated Remove if we only support TSF 4.3+
	 *
	 * @return string The social metadata.
	 */
	protected function get_social_metadata() {

		$tsf = \tsf(); // tsf OK

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

		return "{$before}{$output}{$after}";
	}

	/**
	 * Returns structured metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.2.0
	 * @deprecated Remove if we only support TSF 4.3+
	 *
	 * @return string The structured metadata.
	 */
	protected function get_structured_metadata() {
		return \tsf()->ld_json();
	}
}
