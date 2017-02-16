<?php
/**
 * @package TSF_Extension_Manager\Extension\AMP
 */
namespace TSF_Extension_Manager\Extension;

/**
 * Extension Name: AMP
 * Extension URI: https://premium.theseoframework.com/extensions/amp/
 * Extension Description: The AMP extension binds The SEO Framework to the [AMP plugin](https://wordpress.org/plugins/amp/) for [AMP](https://www.ampproject.org/) supported articles and pages.
 * Extension Version: 1.0.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * AMP extension for The SEO Framework
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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface as Construct_Master_Once_Final_Interface;

/**
 * The AMP extension version.
 * @since 1.0.0
 * @param string
 */
define( 'TSFEM_E_AMP_VERSION', '1.0.0' );

\add_action( 'wp', __NAMESPACE__ . '\\_amp_init', 11 );
/**
 * Initialize the extension.
 *
 * @since 1.0.0
 * @action 'wp'
 * @priority 11
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _amp_init() {

	if ( false === defined( 'AMP_QUERY_VAR' ) )
		return;

	if ( \is_admin() ) {
		//* Bail on admin. No admin dashboard yet.
		return;
	} else {
		$is_amp = \get_query_var( AMP_QUERY_VAR, false ) !== false;

		if ( $is_amp ) {
			new \TSF_Extension_Manager\Extension\AMP;
		}
	}
}

/**
 * Class TSF_Extension_Manager\Extension\AMP_Frontend
 *
 * @since 1.0.0
 *
 * @final Please don't extend this extension.
 */
final class AMP {
	use Enclose_Core_Final, Construct_Master_Once_Final_Interface;

	/**
	 * The constructor, initialize class actions.
	 */
	private function construct() {

		\add_action( 'amp_post_template_head', array( $this, 'do_output_hook' ), 11 );

		/**
		 * Applies filters 'the_seo_framework_remove_amp_articles' : bool
		 * @since 1.0.0
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
	 */
	public function do_output_hook() {

		\do_action( 'the_seo_framework_do_before_amp_output' );

		$this->output_general_metadata();
		$this->output_social_metadata();

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
	 * Outputs general metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.0.0
	 */
	protected function output_general_metadata() {

		$tsf = \the_seo_framework();

		$output = $tsf->the_description();

		//* Already escaped.
		echo $output;
	}

	/**
	 * Outputs social metadata in the AMP HTML head.
	 * Data is taken from The SEO Framework.
	 *
	 * @since 1.0.0
	 */
	protected function output_social_metadata() {

		$tsf = \the_seo_framework();

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

		//* Already escaped.
		echo $output;
	}
}
