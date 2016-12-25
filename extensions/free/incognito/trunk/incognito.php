<?php
/**
 * @package TSF_Extension_Manager_Extension\Incognito
 */
namespace TSF_Extension_Manager_Extension;

/**
 * Extension Name: Incognito
 * Extension URI: https://premium.theseoframework.com/extensions/incognito/
 * Extension Description: The Incognito extension removes all front-end branding from The SEO Framework.
 * Extension Version: 1.0.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Incognito extension for The SEO Framework
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
 * Removes the HTML indicator that's wrapped around the SEO HTML output.
 *
 * @since 1.0.0
 */
add_filter( 'the_seo_framework_indicator', '__return_false' );
