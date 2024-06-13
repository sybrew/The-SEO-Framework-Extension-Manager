<?php
/**
 * @package TSF_Extension_Manager\Extension\Incognito
 */

namespace TSF_Extension_Manager\Extension\Incognito;

/**
 * Extension Name: Incognito
 * Extension URI: https://theseoframework.com/extensions/incognito/
 * Extension Description: The Incognito extension hides all development-comments from The SEO Framework.
 * Extension Version: 1.1.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Incognito extension for The SEO Framework
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
\add_filter( 'the_seo_framework_indicator', '__return_false' );

/**
 * Removes "Fixed" indicator from the Title Fix extension.
 *
 * @since 1.0.0
 */
\add_filter( 'the_seo_framework_title_fixed_indicator', '__return_false' );

/**
 * Removes "Generated by The SEO Framework" from the Sitemap output.
 *
 * @since 1.1.0
 */
\add_filter( 'the_seo_framework_indicator_sitemap', '__return_false' );
