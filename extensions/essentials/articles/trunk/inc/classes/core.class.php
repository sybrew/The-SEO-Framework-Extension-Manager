<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Admin
 * @package TSF_Extension_Manager\Extension\Articles\Front
 */

namespace TSF_Extension_Manager\Extension\Articles;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Articles extension for The SEO Framework
 * Copyright (C) 2017-2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Require extension options trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/post-meta' );

/**
 * Require extension settings trait.
 *
 * @since 2.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/options' );

/**
 * Class TSF_Extension_Manager\Extension\Articles\Core
 *
 * Holds extension core methods.
 *
 * @since 1.2.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 */
class Core {
	use \TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Extension_Post_Meta,
		\TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Core_Interface;

	/**
	 * Child constructor.
	 *
	 * @since 1.2.0
	 */
	private function construct() {

		/**
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 */
		$this->pm_index = 'articles';
		/**
		 * @since 1.2.0
		 * @since 2.0.0 Deprecated
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 * @deprecated
		 * @param array $pm_defaults The default post meta settings.
		 */
		$this->pm_defaults = \apply_filters(
			'the_seo_framework_articles_default_meta',
			[
				'type' => 'Article',
			]
		);

		/**
		 * @since 1.4.0
		 * @since 2.0.0 Deprecated
		 * @deprecated
		 * @param array $post_types The supported post types.
		 */
		$filtered_post_types = \apply_filters(
			'the_seo_framework_articles_supported_post_types',
			[ 'post' ]
		);
		/**
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 */
		$this->o_index    = 'articles';
		$this->o_defaults = [
			'news_sitemap' => 0,  // Google's requirements need to be met first.
			'post_types'   => [],
			'logo'         => [
				'url' => '',
				'id'  => 0,
			],
		];

		foreach ( $filtered_post_types as $post_type ) {
			$this->o_defaults['post_types'][ $post_type ] = [
				'enabled'      => 1,
				'default_type' => $this->pm_defaults['type'],
			];
		}

		// Deprecated. Unset.
		unset( $this->pm_defaults['type'] );
	}
}
