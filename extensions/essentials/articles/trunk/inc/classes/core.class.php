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
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/post-meta' );

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
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Core_Interface,
		\TSF_Extension_Manager\Extension_Post_Meta;

	/**
	 * Child constructor.
	 *
	 * @since 1.2.0
	 */
	private function construct() {

		$that = __NAMESPACE__ . ( \is_admin() ? '\\Admin' : '\\Front' );
		$this instanceof $that or \wp_die( -1 );

		/**
		 * Set meta index and defaults.
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 */
		$this->pm_index    = 'articles';
		$this->pm_defaults = [
			'type' => 'Article',
		];

		/**
		 * Applies filter 'the_seo_framework_articles_default_meta'
		 *
		 * @since 1.2.0
		 * @param array $pm_defaults The default post meta settings.
		 */
		$this->pm_defaults = \apply_filters_ref_array( 'the_seo_framework_articles_default_meta', [ $this->pm_defaults ] );
	}
}
