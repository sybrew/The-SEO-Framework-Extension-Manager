<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Classes
 */
namespace TSF_Extension_Manager\Extension\Articles;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Local extension for The SEO Framework
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
 * Class TSF_Extension_Manager\Extension\Articles\Front
 *
 * @since 1.2.0
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin extends Core {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * Constructor.
	 */
	private function construct() {
		$this->prepare_inpostgui();
	}

	/**
	 * Prepares inpost GUI.
	 *
	 * @since 1.2.0
	 */
	private function prepare_inpostgui() {

		\TSF_Extension_Manager\InpostGUI::prepare();

		//= This also initiates InpostGUI nonce check action.
		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_prepare_inpost_views' ] );

		// Defered because we need to access the meta object after current_screen.
		\add_action( 'tsfem_inpostgui_verified_nonce', [ $this, '_save_meta' ], 10, 2 );
	}

	/**
	 * Prepares inpost options.
	 *
	 * Defered because we need to access meta.
	 *
	 * @since 1.2.0
	 * @access private
	 */
	public function _prepare_inpost_views() {

		\TSF_Extension_Manager\InpostGUI::activate_tab( 'structure' );

		$post_meta = [
			'pm_index' => $this->pm_index,
			'type' => [
				'title' => \__( 'Article type', 'the-seo-framework-extension-manager' ),
				'option' => [
					'name' => 'type',
					'input' => 'select',
					'default' => $this->pm_defaults['type'],
					'value' => $this->get_post_meta( 'type' ),
					'select_values' => [
						'Article' => \__( 'Article', 'the-seo-framework-extension-manager' ),
						'NewsArticle' => \__( 'News Article', 'the-seo-framework-extension-manager' ),
						'BlogPosting' => \__( 'Blog Posting', 'the-seo-framework-extension-manager' ),
					],
				],
			],
		];

		\TSF_Extension_Manager\InpostGUI::register_view(
			$this->get_view_location( 'inpost/inpost' ),
			[ 'post_meta' => $post_meta ],
			'structure'
		);
	}

	private function save_meta( $post, $save_access_state ) {

	}

	/**
	 * Returns view location.
	 *
	 * @since 1.2.0
	 *
	 * @param string $view The relative file location and name without '.php'.
	 * @return string The view file location.
	 */
	private function get_view_location( $view ) {
		return TSFEM_E_ARTICLES_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
	}
}
