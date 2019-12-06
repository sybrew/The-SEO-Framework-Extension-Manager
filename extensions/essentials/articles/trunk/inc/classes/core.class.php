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
		 * @since 2.0.2 The default value is emptied, as it conflicted with the settings.
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 * @deprecated
		 * @param array $pm_defaults The default post meta settings.
		 */
		$this->pm_defaults = (array) \apply_filters( 'the_seo_framework_articles_default_meta', [] );

		/**
		 * @since 1.4.0
		 * @since 2.0.0 Deprecated
		 * @since 2.0.2 The default value is emptied, as it conflicted with the settings.
		 * @deprecated
		 * @param array $post_types The supported post types.
		 */
		$filtered_post_types = (array) \apply_filters( 'the_seo_framework_articles_supported_post_types', [] );

		/**
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index    = 'articles';
		$this->o_defaults = [
			'news_sitemap' => 0, // Google's requirements need to be met first.
			'post_types'   => [
				'post' => [
					'enabled'      => 1,
					'default_type' => 'Article',
				],
			],
			'logo'         => [
				'url' => '',
				'id'  => 0,
			],
		];

		foreach ( $filtered_post_types as $post_type ) {
			$this->o_defaults['post_types'][ $post_type ] = [
				'enabled'      => 1,
				'default_type' => static::filter_article_type( \tsf_extension_manager()->coalesce_var( $this->pm_defaults['type'], 'Article' ) ),
			];
		}

		// Deprecated. Unset.
		unset( $this->pm_defaults['type'] );
	}

	/**
	 * Determines if the current site is representing an organization.
	 *
	 * @since 2.0.0
	 * @staticvar bool $is
	 *
	 * @return bool
	 */
	protected static function is_organization() {
		static $is;
		return isset( $is ) ? $is : $is = 'organization' === \the_seo_framework()->get_option( 'knowledge_type' );
	}

	/**
	 * Filters article type, so an available will return.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The selected Article type.
	 * @return string The filtered Article type.
	 */
	protected static function filter_article_type( $type ) {

		if ( ! in_array( $type, static::get_available_article_types(), true ) ) {
			$type = 'Article';
		}

		return $type;
	}

	/**
	 * Filters article type array, so an available list will return.
	 *
	 * @since 2.0.0
	 *
	 * @param array $items An array with Article types as keys.
	 * @return string The filtered array.
	 */
	protected static function filter_article_keys( array $items ) {
		return array_intersect_key(
			$items,
			array_flip( static::get_available_article_types() )
		);
	}

	/**
	 * Returns the available Article types.
	 *
	 * @since 2.0.0
	 * @todo allow filtering the types?
	 *
	 * @return array
	 */
	protected static function get_available_article_types() {

		if ( static::is_organization() ) {
			$types = [ 'Article', 'NewsArticle', 'BlogPosting' ];
		} else {
			$types = [ 'Article', 'BlogPosting' ];
		}

		return $types;
	}
}
