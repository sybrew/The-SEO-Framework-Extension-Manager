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

		\add_action( 'tsfem_inpostgui_enqueue_scripts', [ $this, '_enqueue_inpost_scripts' ] );

		//= Called late because we need to access the meta object after current_screen.
		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_prepare_inpost_views' ] );

		\add_action( 'tsfem_inpostgui_verified_nonce', [ $this, '_save_meta' ], 10, 3 );
	}

	public function _enqueue_inpost_scripts( $inpostgui ) {
		$inpostgui::register_script( [
			'type' => 'js',
			'name' => 'tsfem-inpost-focus',
			'base' => TSFEM_E_FOCUS_DIR_URL,
			'ver' => TSFEM_E_FOCUS_VERSION,
			'deps' => [ 'jquery' ],
			'l10n' => null,
		] );
		$inpostgui::register_script( [
			'type' => 'css',
			'name' => 'tsfem-inpost-focus',
			'base' => TSFEM_E_FOCUS_DIR_URL,
			'ver' => TSFEM_E_FOCUS_VERSION,
			'deps' => [],
		] );
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

		\TSF_Extension_Manager\InpostGUI::activate_tab( 'audit' );

		$post_meta = [
			'pm_index' => $this->pm_index,
			'post_id' => \the_seo_framework()->get_the_real_ID(),
			'kw' => [
				'label' => [
					'title' => \__( 'Subject Analysis', 'the-seo-framework-extension-manager' ),
					'desc' => \__( 'Set subjects and learn how you can improve their focus.', 'the-seo-framework-extension-manager' ),
					'link' => 'https://theseoframework.com/extensions/focus/#usage',
				],
				//! Don't set default, it's already pre-populated.
				'values' => $this->get_post_meta( 'kw', null ),
				'option_index' => 'kw',
			],
		];

		\TSF_Extension_Manager\InpostGUI::register_view(
			$this->get_view_location( 'inpost/inpost' ),
			[
				'post_meta' => $post_meta,
				'template_cb' => [ $this, '_output_focus_template' ],
				'is_premium' => \tsf_extension_manager()->is_premium_user(),
			],
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
	 * Outputs focus template.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $args The focus template arguments.
	 */
	public function _output_focus_template( array $args ) {
		$this->get_view( 'inpost/focus-template', $args );
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 *        Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, array $args = [] ) {

		foreach ( $args as $key => $val ) {
			$$key = $val;
		}

		include $this->get_view_location( $view );
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
