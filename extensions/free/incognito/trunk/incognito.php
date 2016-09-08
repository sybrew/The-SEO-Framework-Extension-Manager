<?php
/**
 * @package TSF_Extension_Manager_Extensions
 */
namespace TSF_Extension_Manager_Extension;

/**
 * Extension Name: Incognito
 * Extension URI: https://premium.theseoframework.com/extensions/incognito/
 * Description: The Incognito extension removes all front-end branding from The SEO Framework.
 * Version: 1.0.0
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Removes the HTML indicator that's wrapped around the SEO HTML output.
 *
 * @since 1.0.0
 */
add_filter( 'the_seo_framework_indicator', '__return_false' );
