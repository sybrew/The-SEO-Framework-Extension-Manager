<?php
/**
 * @package TSF_Extension_Manager\Extension\Origin
 */
namespace TSF_Extension_Manager\Extension\Origin;

/**
 * Extension Name: Origin
 * Extension URI: https://theseoframework.com/extensions/origin/
 * Extension Description: The Origin extension redirects attachment-page visitors back to the parent post.
 * Extension Version: 1.1.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Origin extension for The SEO Framework
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

\add_action( 'template_redirect', __NAMESPACE__ . '\\_go', 0 );
/**
 * Redirects visitor on an attachment page back to the parent post.
 *
 * @since 1.0.0
 * @since 1.1.0 Now redirects to the attachment when no parent is found.
 * @global \WP_Post $post The current post object.
 *
 * @return void
 */
function _go() {
	global $post;

	if ( $post && \is_attachment() ) {
		if ( ! empty( $post->post_parent ) ) {
			\wp_safe_redirect( \get_permalink( $post->post_parent ), 301 );
			exit;
		} else {
			$url = \wp_get_attachment_url( $post->ID );
			if ( $url ) {
				\wp_safe_redirect( $url, 301 );
				exit;
			}
		}
	}
}
