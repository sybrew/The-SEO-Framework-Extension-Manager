<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
 * Sets up class loader as file is loaded.
 * This is done asynchronously, because static calls are handled prior and after.
 * @see EOF. Because of the autoloader, we can't do it before the class is read.
 */
function _load_inpostgui_class() {
	new InpostGUI();
}

/**
 * Registers and outputs inpost GUI elements. Auto-invokes everything the moment
 * this file is required.
 *
 * The SEO Framework 2.9.0 or later is required. All earlier versions will let this
 * remain dormant.
 *
 * @since 1.5.0
 * @requires TSF 2.9.0||^
 * @access private
 * @uses trait TSF_Extension_Manager\Enclose_Core_Final
 * @uses trait TSF_Extension_Manager\Construct_Master_Once_Final_Interface
 *       This means you shouldn't invoke new yourself.
 * @see package TSF_Extension_Manager\Traits\Overload
 *
 * @final Can't be extended.
 */
final class InpostGUI {
	use Enclose_Core_Final,
		Construct_Master_Once_Final_Interface;

	const NONCE_ACTION = 'tsfem-e-save-inpost-nonce';
	const NONCE_NAME = 'tsfem-e-inpost-settings';

	public static $save_access_state = 0;

	private static $tabs = [];
	private static $include_secret;
	private static $active_tab_keys = [];
	private static $views = [];

	/**
	 * Constructor. Loads all appropriate actions asynchronously.
	 */
	private function construct() {

		$this->register_tabs();

		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_output_nonce' ] );
		\add_action( 'save_post', [ $this, '_verify_nonce' ], 1, 2 );

		\add_filter( 'the_seo_framework_inpost_settings_tabs', [ $this, '_load_tabs' ], 10, 2 );
	}

	private function register_tabs() {
		static::$tabs = [
			'structure' => [
				'name' => \__( 'Structure', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_tab_content' ],
				'dashicon' => 'layout',
				'args' => [ 'structure' ],
			],
			'audit' => [
				'name' => \__( 'Audit', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_tab_content' ],
				'dashicon' => 'analytics',
				'args' => [ 'audit' ],
			],
			'advanced' => [
				'name' => \__( 'Advanced', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_tab_content' ],
				'dashicon' => 'list-view',
				'args' => [ 'advanced' ],
			],
		];
	}

	/**
	 * @since 1.5.0
	 * @access private
	 * @uses static::NONCE_NAME
	 * @uses static::NONCE_NAME
	 * @see @package The_SEO_Framework\Classes
	 *    method singular_inpost_box() [...] add_inpost_seo_box()
	 *
	 */
	public function _output_nonce() {
		\current_user_can( 'edit_post', $GLOBALS['post']->ID )
			and \wp_nonce_field( static::NONCE_ACTION, static::NONCE_NAME );
	}

	/**
	 *
	 * @since 1.5.0
	 *
	 * @param integer  $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function _verify_nonce( $post_id, $post ) {

		if ( ( empty( $_POST[ static::NONCE_NAME ] ) )
		|| ( ! \wp_verify_nonce( \wp_unslash( $_POST[ static::NONCE_NAME ] ), static::NONCE_ACTION ) )
		|| ( ! \current_user_can( 'edit_post', $post->ID ) )
		   ) return;

		static::$save_access_state = 0b0001;

		if ( ! defined( 'DOING_AUTOSAVE' ) || ! DOING_AUTOSAVE )
			static::$save_access_state |= 0b0010;
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
			static::$save_access_state |= 0b0100;
		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON )
			static::$save_access_state |= 0b1000;

		/**
		 *
		 * @since 1.5.0
		 * @param int (bitwise) $save_access_state The state the save is in.
		 *    Any combination of : {
		 *      1 = 0001 : Passed nonce and capability checks. Always set at this point.
		 *      2 = 0010 : Not doing autosave.
		 *      4 = 0100 : Not doing AJAX.
		 *      8 = 1000 : Not doing WP Cron.
		 *      |
		 *     15 = 1111 : Post is manually published or updated.
		 *    }
		 */
		\do_action_ref_array( 'tsfem_inpostgui_verified_nonce', [ &static::$save_access_state ] );

		// var_dump()
		// if ( ! ( $save_access_state ^ 0b1111 ) ) // passed all tests.
	}

	/**
	 * @since 1.5.0
	 * @access private
	 *
	 * @param string $label The post type label.
	 */
	public function _load_tabs( $tabs, $label ) {

		$registered_tabs = static::$tabs;
		$active_tab_keys = static::$active_tab_keys;

		foreach ( $registered_tabs as $index => $args ) :
			! empty( $active_tab_keys[ $index ] ) and
				$tabs[ $index ] = $this->append_type_arg( $args, $label );
		endforeach;

		return $tabs;
	}

	private function append_type_arg( array $tab_args, $label ) {
		$tab_args['args'] += [ 'post_type_label' => $label ];
		return $tab_args;
	}

	/**
	 * @since 1.5.0
	 * @access private
	 */
	public function _output_tab_content( $tab ) {

		if ( isset( static::$views[ $tab ] ) ) {
			$views = static::$views[ $tab ];
			//= Sort by the priority indexes. Priority values get lost in this process.
			sort( $views );

			foreach ( $views as $view )
				$this->output_view( $view[0], $view[1] );
		}
	}

	private function output_view( $file, array $args ) {
		foreach ( $args as $_key => $_val )
			$$_key = $_val;

		unset( $_key, $_val, $args );

		//= Prevent private includes hijacking.
		static::$include_secret = $_secret = mt_rand( -2073011, 2072977 );
		include $file;
		static::$include_secret = null;
	}

	public static function verify( $secret ) {
		return static::$include_secret === $secret;
	}

	public static function activate_tab( $key ) {
		static::$active_tab_keys[ $key ] = true;
	}

	public static function register_view( $file, array $args = [], $tab = 'advanced', $priority = 10 ) {
		$_views = static::$views;

		if ( ! isset( $_views[ $tab ] ) ) {
			$_views[ $tab ] = [];
		}
		if ( ! isset( $_views[ $tab ][ $priority ] ) ) {
			$_views[ $tab ][ $priority ] = [];
		}

		$_views[ $tab ][ $priority ] += [ $file, $args ];
		static::$views = $_views;
	}

	public static function wrap_flex( $what, $content ) {
		//= Input should already be escaped.
		echo static::construct_flex_wrap( $what, $content );
	}

	public static function construct_flex_wrap( $what, $content, $for = '' ) {

		switch ( $what ) :
			case 'block' :
				$content = sprintf( '<div class="tsf-flex-setting tsf-flex">%s</div>', $content );
				break;

			case 'label' :
			case 'label-static' :
				$content = sprintf(
					'<div class="tsf-flex-setting-label tsf-flex">
						<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
							<div class="tsf-flex-setting-label-item tsf-flex">
								%s
							</div>
						</div>
					</div>',
					$content
				);
				break;

			case 'label-input' :
				//= Soft fail if $for is omitted.
				// TEST. var_dump();
				false and $for or \the_seo_framework()->_doing_it_wrong( __METHOD__, sprintf( 'Use %s::wrap_flex_settings_label() instead.', \esc_html( static::class ) ) );
				$content = sprintf(
					'<div class="tsf-flex-setting-label tsf-flex">
						<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
							<label for="%s" class="tsf-flex-setting-label-item tsf-flex">
								%s
							</label>
						</div>
					</div>',
					$for,
					$content
				);
				break;

			case 'input' :
			case 'content' :
				$content = sprintf( '<div class="tsf-flex-setting-input tsf-flex">%s</div>', $content );
				break;

			default;
		endswitch;

		return $content;
	}
}

\TSF_Extension_Manager\_load_inpostgui_class();
