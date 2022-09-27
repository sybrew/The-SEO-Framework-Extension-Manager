<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin
 */

namespace TSF_Extension_Manager\Extension\Local;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	use \TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * @since 1.0.0
	 * @var string Page hook name.
	 */
	protected $local_menu_page_hook;

	/**
	 * @since 1.0.0
	 * @var string Page ID/Slug
	 */
	protected $local_page_slug;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		// Sets local page slug.
		$this->local_page_slug = 'theseoframework-local';

		// Load admin actions.
		if ( ! \tsf()->is_headless['settings'] )
			$this->load_admin_actions();
	}

	/**
	 * Loads admin actions.
	 *
	 * @since 1.0.0
	 */
	private function load_admin_actions() {

		// Initialize menu links
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		// Initialize Local page actions. Requires $this->local_menu_page_hook to be set.
		\add_action( 'admin_init', [ $this, '_load_local_admin_actions' ], 10 );
	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @since 1.1.7 The extension access level is now controlled via another constant.
	 * @uses \TSF_Extension_Manager\can_do_extension_settings()
	 * @access private
	 */
	public function _init_menu() {
		if ( \TSF_Extension_Manager\can_do_extension_settings() )
			\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 20 );
	}

	/**
	 * Adds menu link for Local, when possible, underneath The SEO Framework
	 * SEO settings.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Added TSF v3.1 compat.
	 * @uses \tsf()->seo_settings_page_slug.
	 * @access private
	 */
	public function _add_menu_link() {

		$menu = [
			'parent_slug' => \tsf()->seo_settings_page_slug,
			'page_title'  => 'Local',
			'menu_title'  => 'Local',
			'capability'  => TSF_EXTENSION_MANAGER_EXTENSION_ADMIN_ROLE,
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
	 * Outputs Local settings page.
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

		if ( \wp_doing_ajax() ) {
			$this->do_settings_page_ajax_actions();
		} else {
			\add_action( "load-{$this->local_menu_page_hook}", [ $this, '_do_settings_page_actions' ] );
		}
	}

	/**
	 * Hooks admin actions into the Local pagehook.
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

		$this->get_local_settings_instance()->_init( $this, $this->local_page_slug, $this->local_menu_page_hook, $this->o_index );

		return true;
	}

	/**
	 * Hooks admin AJAX actions into the Local pagehook.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on actions loaded, false on second load or incorrect page.
	 */
	protected function do_settings_page_ajax_actions() {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) )
			return false;

		$this->get_local_settings_instance()->_init_ajax( $this, $this->o_index );

		return true;
	}

	/**
	 * Determines whether we're on the Local overview page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	public function is_local_page() {

		static $cache;

		// Don't load from $_GET request.
		return $cache ?? $cache = \tsf()->is_menu_page( $this->local_menu_page_hook );
	}

	/**
	 * Sets up and returns \TSF_Extension_Manager\Extension\Local\Settings.
	 *
	 * @since 1.0.0
	 *
	 * @return object \TSF_Extension_Manager\Extension\Local\Settings
	 */
	protected function get_local_settings_instance() {
		return \TSF_Extension_Manager\Extension\Local\Settings::get_instance();
	}
}
