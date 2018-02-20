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

	private function get_focus_elements() {
		/**
		 * Applies filters 'the_seo_framework_focus_elements'.
		 *
		 * When a selector can't be found in the DOM, it's skipped and cannot be appended
		 * or be dominating.
		 *
		 * When a selector is dominating, the order is considered. When the selector is
		 * the last dominating thing on the list, it's the only thing used for the scoring.
		 *
		 * When a selector is appending, and no dominating items are available, then
		 * it's considered as an addition for scoring.
		 *
		 * The querySelector fields must be visible for highlighting. When it's
		 * not visible, highlighting is ignored.
		 *
		 * Elements can also be added dynamically in JS, for Gutengerg block support.
		 * @see JavaScript tsfem_e_focus_inpost.updateFocusRegistry();
		 *
		 * The fields must be in order of importance when dominating.
		 * Apply this filter with a high $priority value to ensure domination.
		 * @see WordPress `add_filter()`
		 * @see `array_push()`
		 * @see `array_unshift()`
		 * @since 1.0.0
		 *
		 * @param array $elements : { 'type' => [
		 *    'querySelector' => string 'append|dominate'.
		 * }
		 */
		return \apply_filters_ref_array( 'the_seo_framework_focus_elements', [
			[
				'pageTitle' => [
					'#titlewrap > input' => 'append',
				],
				'pageUrl' => [
					'#sample-permalink' => 'dominate',
				],
				'pageContent' => [
					'#content' => 'append',
				],
				'seoTitle' => [
					'#autodescription_title' => 'dominate',
				],
				'seoDescription' => [
					'#autodescription_description' => 'dominate',
				],
			],
		] );
	}

	public function _enqueue_inpost_scripts( $inpostgui ) {
		$inpostgui::register_script( [
			'type' => 'js',
			'name' => 'tsfem-focus-inpost',
			'base' => TSFEM_E_FOCUS_DIR_URL,
			'ver' => TSFEM_E_FOCUS_VERSION,
			'deps' => [ 'jquery', 'tsf' ],
			'l10n' => [
				'name' => 'tsfem_e_focusInpostL10n',
				'data' => [
					'post_ID' => $GLOBALS['post']->ID,
					'nonce' => \current_user_can( 'edit_post', $GLOBALS['post']->ID ) ? \wp_create_nonce( 'tsfem-e-focus-inpost-nonce' ) : false,
					'isPremium' => \tsf_extension_manager()->is_premium_user(),
					'locale' => \get_locale(),
					'focusElements' => $this->get_focus_elements(),
				],
			],
			'tmpl' => [
				'file' => $this->get_view_location( 'inpost/js-templates' ),
			],
		] );
		$inpostgui::register_script( [
			'type' => 'css',
			'name' => 'tsfem-focus-inpost',
			'base' => TSFEM_E_FOCUS_DIR_URL,
			'ver' => TSFEM_E_FOCUS_VERSION,
			'deps' => [ 'tsf' ],
			'colors' => [
				'.tsfem-e-focus-content-loader-bar' => [
					'background:{{$color_accent}}',
				],
			],
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

		//= If doing more than just AJAX, stop.
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
	 * Outputs focus template.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $args The focus template arguments.
	 */
	private function output_score_template( array $args ) {
		$this->get_view( 'inpost/score-template', $args );
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
