<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Classes
 */
namespace TSF_Extension_Manager\Extension\Focus;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Focus\Front
 *
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin extends Core {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Master_Once_Interface,
		\TSF_Extension_Manager\Error;

	/**
	 * Constructor.
	 */
	private function construct() {
		$this->prepare_inpostgui();
	}

	/**
	 * Prepares inpost GUI.
	 *
	 * @since 1.0.0
	 */
	private function prepare_inpostgui() {

		//= Prepares InpostGUI's class for nonce checking.
		\TSF_Extension_Manager\InpostGUI::prepare();

		//= Defered because we need to access the meta object after current_screen.
		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_prepare_inpost_views' ] );

		\add_action( 'tsfem_inpostgui_verified_nonce', [ $this, '_save_meta' ], 10, 3 );
	}

	/**
	 * Prepares inpost options.
	 *
	 * Defered because we need to access meta.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _prepare_inpost_views() {

		if ( ! \the_seo_framework()->is_single_admin() )
			return;

		\TSF_Extension_Manager\InpostGUI::activate_tab( 'audit' );

		$post_meta = [
			'pm_index' => $this->pm_index,
			'post_id' => \the_seo_framework()->get_the_real_ID(),
			'type' => [
				'label' => [
					'title' => \__( 'Focus', 'the-seo-framework-extension-manager' ),
					'desc' => \__( 'Set keywords and learn how you can improve focus.', 'the-seo-framework-extension-manager' ),
					'link' => 'https://theseoframework.com/extensions/focus/#usage',
				],
				'option' => [
					// TODO
				],
			],
		];

		\TSF_Extension_Manager\InpostGUI::register_view(
			$this->get_view_location( 'inpost/inpost' ),
			[ 'post_meta' => $post_meta ],
			'audit'
		);
	}

	/**
	 * Saves or deletes post meta.
	 *
	 * @since 1.0.0
	 * @see \TSF_Extension_Manager\InpostGUI::_verify_nonce()
	 * @see action 'tsfem_inpostgui_verified_nonce'
	 *
	 * @param \WP_Post      $post              The post object.
	 * @param array|null    $data              The meta data.
	 * @param int (bitwise) $save_access_state The state the save is in.
	 */
	public function _save_meta( $post, $data, $save_access_state ) {

		if ( $save_access_state ^ 0b1111 )
			return;

		$this->process_meta( $post, $data );
	}

	/**
	 * Saves or deletes post meta on AJAX callbacks.
	 *
	 * @since 1.0.0
	 * @see \TSF_Extension_Manager\InpostGUI::_verify_nonce()
	 * @see action 'tsfem_inpostgui_verified_nonce'
	 *
	 * @param \WP_Post      $post              The post object.
	 * @param array|null    $data              The meta data.
	 * @param int (bitwise) $save_access_state The state the save is in.
	 */
	public function _wp_ajax_save_meta( $post, $data, $save_access_state ) {

		//= Nonce check failed. Show notice?
		if ( ! $save_access_state )
			return;

		//= If doing more than just AJAX, or when stop.
		if ( $save_access_state ^ 0b1111 ^ 0b0100 )
			return;

		$this->process_meta( $post, $data );
	}

	private function process_meta( $post, $data ) {

		if ( empty( $data[ $this->pm_index ] ) )
			return;

		$this->set_extension_post_meta_id( $post->ID );

		$store = [];
		/**
		 * @TODO add meta sanitation filters.
		 */
		foreach ( $data[ $this->pm_index ] as $key => $value ) :
			switch ( $key ) {
				// TODO

				default :
					break;
			}
		endforeach;

		if ( empty( $store ) ) {
			$this->delete_post_meta_index();
		} else {
			foreach ( $store as $key => $value ) {
				$this->update_post_meta( $key, $value );
			}
		}
	}

	/**
	 * Returns view location.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view The relative file location and name without '.php'.
	 * @return string The view file location.
	 */
	private function get_view_location( $view ) {
		return TSFEM_E_FOCUS_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
	}
}
