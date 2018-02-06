<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Local extension for The SEO Framework
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
 * Class TSF_Extension_Manager\Extension\Local\Admin
 *
 * Holds extension admin page methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 107xxxx
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin extends Core {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page hook.
	 */
	protected $local_menu_page_hook;

	/**
	 * The extension page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	protected $local_page_slug;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Sets local page slug.
		$this->local_page_slug = 'theseoframework-local';

		//* Load admin actions.
		$this->load_admin_actions();
	}

	/**
	 * Loads admin actions.
	 *
	 * @since 1.0.0
	 */
	private function load_admin_actions() {

		//* Initialize menu links
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		//* Initialize Local page actions. Requires $this->local_menu_page_hook to be set.
		\add_action( 'admin_init', [ $this, '_load_local_admin_actions' ], 10 );
	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @uses \the_seo_framework()->load_options variable. Applies filters 'the_seo_framework_load_options'
	 * @uses \tsf_extension_manager()->can_do_settings()
	 * @access private
	 */
	public function _init_menu() {

		if ( \tsf_extension_manager()->can_do_settings() && \the_seo_framework()->load_options )
			\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 11 );
	}

	/**
	 * Adds menu link for Local SEO, when possible, underneath The SEO Framework
	 * SEO settings.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework_options_page_slug().
	 * @access private
	 */
	public function _add_menu_link() {

		$menu = [
			'parent_slug' => \the_seo_framework_options_page_slug(),
			'page_title'  => \esc_html__( 'Local SEO', 'the-seo-framework-extension-manager' ),
			'menu_title'  => \esc_html__( 'Local', 'the-seo-framework-extension-manager' ),
			'capability'  => 'manage_options',
			'menu_slug'   => $this->local_page_slug,
			'callback'    => [ $this, '_output_local_settings_page' ],
		];

		$this->local_menu_page_hook = \add_submenu_page(
			$menu['parent_slug'],
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);
	}

	/**
	 * Outputs Local SEO settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _output_local_settings_page() {
		$this->get_local_settings_instance()->_output_settings_page( $this );
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 *
	 * @since 1.0.0
	 * @uses $this->local_menu_page_hook variable.
	 * @access private
	 */
	public function _load_local_admin_actions() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->do_settings_page_ajax_actions();
		} else {
			\add_action( 'load-' . $this->local_menu_page_hook, [ $this, '_do_settings_page_actions' ] );
		}
	}

	/**
	 * Hooks admin actions into the Local SEO pagehook.
	 * Early enough for admin_notices and admin_head :).
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool True on actions loaded, false on second load or incorrect page.
	 */
	public function _do_settings_page_actions() {

		if ( false === $this->is_local_page() )
			return false;

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) )
			return false;

		$this->get_local_settings_instance()->_init( $this, $this->local_page_slug, $this->local_menu_page_hook );

		return true;
	}

	/**
	 * Hooks admin AJAX actions into the Local SEO pagehook.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on actions loaded, false on second load or incorrect page.
	 */
	protected function do_settings_page_ajax_actions() {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) )
			return false;

		$this->get_local_settings_instance()->_init_ajax( $this );

		return true;
	}

	/**
	 * Determines whether we're on the Local overview page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 * @access private
	 *
	 * @return bool
	 */
	public function is_local_page() {

		static $cache;

		//* Don't load from $_GET request.
		return isset( $cache ) ? $cache : $cache = \the_seo_framework()->is_menu_page( $this->local_menu_page_hook );
	}

	/**
	 * Sets up and returns Transporter_Steps.
	 *
	 * @since 1.0.0
	 *
	 * @return object \TSF_Extension_Manager\Extension\Local\Settings
	 */
	protected function get_local_settings_instance() {
		return \TSF_Extension_Manager\Extension\Local\Settings::get_instance();
	}
}
