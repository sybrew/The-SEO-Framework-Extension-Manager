<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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

/**
 * Sets up class loader as file is loaded.
 * This is done asynchronously, because static calls are handled prior and after.
 * @see EOF. Because of the autoloader and trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_inpostgui_class = function() {
	new InpostGUI();
};

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

	/**
	 * @since 1.5.0
	 * @param string NONCE_ACTION The nonce action.
	 * @param string NONCE_NAME   The nonce name.
	 */
	const NONCE_ACTION = 'tsfem-e-save-inpost-nonce';
	const NONCE_NAME = 'tsfem-e-inpost-settings';

	/**
	 * @since 1.5.0
	 * @param string META_PREFIX The meta prefix to be stored in the database.
	 */
	const META_PREFIX = 'tsfem-pm';

	/**
	 * @since 1.5.0
	 * @see static::_verify_nonce()
	 * @param string $save_access_state The state the save is in.
	 */
	public static $save_access_state = 0;

	/**
	 * @since 1.5.0
	 * @param string $include_secret The inclusion secret generated on tab load.
	 */
	private static $include_secret;

	/**
	 * @since 1.5.0
	 * @param array $tabs The registered tabs.
	 * @param array $active_tab_keys The activate tab keys of static::$tabs
	 * @param array $views The registered view files for the tabs.
	 */
	private static $tabs = [];
	private static $active_tab_keys = [];
	private static $views = [];

	/**
	 * Prepares the class.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 1.5.0
	 */
	public static function prepare() {}

	/**
	 * Constructor. Loads all appropriate actions asynchronously.
	 */
	private function construct() {

		$this->register_tabs();

		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_output_nonce' ], 9 );
		\add_action( 'save_post', static::class . '::_verify_nonce', 1, 2 );

		\add_filter( 'the_seo_framework_inpost_settings_tabs', [ $this, '_load_tabs' ], 10, 2 );
	}

	/**
	 * Registers available tabs.
	 *
	 * Any more than 6 tabs will cause GUI incompatibilities. Therefore, it's
	 * recommended to only use these assigned tabs.
	 *
	 * @since 1.5.0
	 * @uses static::$tabs The registered tabs that are written.
	 */
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
	 * Outputs nonces to be verified at POST.
	 *
	 * Doesn't output a referer field, as that's already outputted by WordPress,
	 * including a duplicate by The SEO Framework.
	 *
	 * @since 1.5.0
	 * @access private
	 * @uses static::NONCE_NAME
	 * @uses static::NONCE_NAME
	 * @see @package The_SEO_Framework\Classes
	 *    method singular_inpost_box() [...] add_inpost_seo_box()
	 */
	public function _output_nonce() {
		\current_user_can( 'edit_post', $GLOBALS['post']->ID )
			and \wp_nonce_field( static::NONCE_ACTION, static::NONCE_NAME, false );
	}

	/**
	 * Verifies nonce on POST and writes the class $save_access_state variable.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param integer  $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void Early when nonce or user can't be verified.
	 */
	public static function _verify_nonce( $post_id, $post ) {

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

		$data = ! empty( $_POST[ static::META_PREFIX ] ) ? $_POST[ static::META_PREFIX ] : null;

		/**
		 * Runs after nonce and possibly interfering actions have been verified.
		 *
		 * @since 1.5.0
		 *
		 * @param \WP_Post      $post              The post object.
		 * @param array|null    $data              The meta data, set through `pm_index` keys.
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
		\do_action_ref_array( 'tsfem_inpostgui_verified_nonce', [ $post, $data, static::$save_access_state ] );
	}

	/**
	 * Determines whether POST data can be safely written.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if user verification passed, and not doing autosave, cron, or ajax.
	 */
	public static function can_safely_write() {
		return ! ( static::$save_access_state ^ 0b1111 );
	}

	/**
	 * Adds registered active tabs to The SEO Framework inpost metabox.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param array  $tabs  The registered tabs.
	 * @param string $label The post type label.
	 * @return array $tabs The SEO Framework's tabs.
	 */
	public function _load_tabs( array $tabs, $label ) {

		$registered_tabs = static::$tabs;
		$active_tab_keys = static::$active_tab_keys;

		foreach ( $registered_tabs as $index => $args ) :
			empty( $active_tab_keys[ $index ] ) or
				$tabs[ $index ] = $this->append_type_arg( $args, $label );
		endforeach;

		return $tabs;
	}

	/**
	 * Appends post type label argument to the tab arguments.
	 *
	 * @since 1.5.0
	 *
	 * @param array  $tab_args The current tab arguments.
	 * @param string $label    The post type label.
	 * @return array The extended tab arguments.
	 */
	private function append_type_arg( array $tab_args, $label ) {
		$tab_args['args'] += [ 'post_type_label' => $label ];
		return $tab_args;
	}

	/**
	 * Output tabs content through loading registered tab views in order of
	 * priority or registration time.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param string $tab The tab that invoked this method call.
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

	/**
	 * Outputs tab view, whilst trying to prevent 3rd party interference on views.
	 *
	 * There's a secret key generated on each tab load. This key can be accessed
	 * in the view through `$_secret`, and be sent back to this class.
	 * @see \TSF_Extension_Manager\InpostGUI::verify( $secret )
	 *
	 * @since 1.5.0
	 * @uses static::$include_secret
	 *
	 * @param string $file The file location.
	 * @param array  $args The registered view arguments.
	 */
	private function output_view( $file, array $args ) {
		foreach ( $args as $_key => $_val )
			$$_key = $_val;

		unset( $_key, $_val, $args );

		//= Prevent private includes hijacking.
		static::$include_secret = $_secret = mt_rand() . uniqid();
		include $file;
		static::$include_secret = null;
	}

	/**
	 * Verifies view inclusion secret.
	 *
	 * @since 1.5.0
	 * @see static::output_view()
	 * @uses static::$include_secret
	 *
	 * @param string $secret The passed secret.
	 * @return bool True on success, false on failure.
	 */
	public static function verify( $secret ) {
		return static::$include_secret === $secret;
	}

	/**
	 * Activates registered tab for display.
	 *
	 * Structure: Rich/structured data controls.
	 * Audit:     Monitoring, reviewing content, analytics, etc.
	 * Advanced:  Everything else.
	 *
	 * @since 1.5.0
	 * @see static::register_tabs()
	 * @uses static::$active_tab_keys
	 *
	 * @param string $tab The tab to activate.
	 *               Either 'structure', 'audit' or 'advanced'.
	 */
	public static function activate_tab( $key ) {
		static::$active_tab_keys[ $key ] = true;
	}

	/**
	 * Registers view for tab.
	 *
	 * @since 1.5.0
	 * @see static::activate_tab();
	 * @uses static::$views
	 *
	 * @param string $file The file to include.
	 * @param array  $args The arguments to pass to the file. Each array index is
	 *               converted to a respectively named variable.
	 * @param string $tab  The tab the view is outputted in.
	 * @param int|float $priority The priority of the view. A lower value results in an earlier output.
	 */
	public static function register_view( $file, array $args = [], $tab = 'advanced', $priority = 10 ) {
		//= Prevent excessive static calls and write directly to var.
		$_views =& static::$views;

		if ( ! isset( $_views[ $tab ] ) )
			$_views[ $tab ] = [];
		if ( ! isset( $_views[ $tab ][ $priority ] ) )
			$_views[ $tab ][ $priority ] = [];

		$_views[ $tab ][ $priority ] += [ $file, $args ];
	}

	/**
	 * Builds option key index, which can later be retrieved in POST.
	 *
	 * @since 1.5.0
	 * @see trait \TSF_Extension_Manager\Extension_Post_Meta
	 * @see static::_verify_nonce();
	 *
	 * @param string $option The option.
	 * @param string $index  The post meta index (pm_index).
	 * @return string The option prefix.
	 */
	public static function get_option_key( $option, $index ) {
		return sprintf( '%s[%s][%s]', static::META_PREFIX, $index, $option );
	}

	/**
	 * Wraps and outputs content in common flex wrap for tabs.
	 *
	 * @since 1.5.0
	 * @uses static::construct_flex_wrap();
	 * @see documentation static::construct_flex_wrap();
	 *
	 * @param string $what    The type of wrap to use.
	 * @param string $content The content to wrap. Should be escaped.
	 * @param string $for     The input ID an input label is for. Should be escaped.
	 */
	public static function wrap_flex( $what, $content, $for = '' ) {
		//= Input should already be escaped.
		echo static::construct_flex_wrap( $what, $content, $for );
	}

	/**
	 * Wraps and outputs and array of content in common flex wrap for tabs.
	 *
	 * Mainly used to wrap blocks and checkboxes.
	 * Does not accept title labels directly.
	 *
	 * @since 1.5.0
	 * @uses static::construct_flex_wrap();
	 * @see documentation static::construct_flex_wrap();
	 *
	 * @param string $what    The type of wrap to use.
	 * @param array  $contents The contents to wrap. Should be escaped.
	 */
	public static function wrap_flex_multi( $what, array $contents ) {
		//= Input should already be escaped.
		echo static::contruct_flex_wrap_multi( $what, $contents );
	}

	/**
	 * Wraps an array content in common flex wrap for tabs.
	 *
	 * Mainly used to wrap blocks and checkboxes.
	 * Does not accept title labels directly.
	 *
	 * @since 1.5.0
	 * @uses static::construct_flex_wrap();
	 * @see documentation static::construct_flex_wrap();
	 *
	 * @param string $what    The type of wrap to use.
	 * @param array  $contents The contents to wrap. Should be escaped.
	 */
	public static function contruct_flex_wrap_multi( $what, array $contents ) {
		return static::construct_flex_wrap( $what, implode( PHP_EOL, $contents ) );
	}

	/**
	 * Wraps content in common flex wrap for tabs.
	 *
	 * @since 1.5.0
	 * @see static::wrap_flex();
	 *
	 * @param string $what The type of wrap to use. Accepts:
	 *               'block'        : The main wrap. Wraps a label and input/content block.
	 *               'label'        : Wraps a label.
	 *                                Be sure to wrap parts in `<div>` for alignment.
	 *               'label-input'  : Wraps an input label.
	 *                                Be sure to assign the $for parameter.
	 *                                Be sure to wrap parts in `<div>` for alignment.
	 *               'input'        : Wraps input content fields, plainly.
	 *               'content'      : Same as 'input'.
	 *               'checkbox'     : Wraps a checkbox and its label.
	 * @param string $content The content to wrap. Should be escaped.
	 * @param string $for     The input ID an input label is for. Should be escaped.
	 */
	public static function construct_flex_wrap( $what, $content, $for = '' ) {

		switch ( $what ) :
			case 'block' :
				$content = sprintf( '<div class="tsf-flex-setting tsf-flex">%s</div>', $content );
				break;

			case 'label' :
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
				$for or \the_seo_framework()->_doing_it_wrong( __METHOD__, 'Set the <code>$for</code> (3rd) parameter.' );
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

			case 'block-open' :
				$content = sprintf( '<div class="tsf-flex-setting tsf-flex">%s', $content );
				break;

			case 'input-open' :
			case 'content-open' :
				$content = sprintf( '<div class="tsf-flex-setting-input tsf-flex">%s', $content );
				break;

			case 'block-close' :
			case 'input-close' :
			case 'content-close' :
				$content = '</div>';
				break;

			//! Not used.
			// case 'checkbox' :
			// 	$content = sprintf( '<div class="tsf-checkbox-wrapper">%s</div>', $content );
			// 	break;

			default :
				break;
		endswitch;

		return $content;
	}
}

$_load_inpostgui_class();
