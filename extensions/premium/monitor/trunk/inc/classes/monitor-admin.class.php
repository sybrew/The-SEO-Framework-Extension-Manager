<?php
/**
 * @package TSF_Extension_Manager_Extension
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * @package TSF_Extension_Manager
 */
use TSF_Extension_Manager\Enclose_Master as Enclose_Master;
use TSF_Extension_Manager\Construct_Solo_Master as Construct_Solo_Master;
use TSF_Extension_Manager\UI as UI;

/**
 * Require user interface trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'ui' );

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

final class Monitor_Admin {
	use Enclose_Master, Construct_Solo_Master, UI;

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page hook.
	 */
	public $monitor_menu_page_hook;

	/**
	 * The extension page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	public $monitor_page_slug;

	/**
	 * The extensions settings field.
	 *
	 * @since 1.0.0
	 *
	 * @var string TSF Extension Manager Settings Field.
	 */
	public $settings_field;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	protected function construct() {

		$this->monitor_page_slug = 'theseoframework-monitor';
		$this->settings_field = TSF_EXTENSION_MANAGER_SITE_OPTIONS;

		//* Initialize menu links
		add_action( 'admin_menu', array( $this, 'init_menu' ) );

		//* Initialize TSF Extension Manager page actions.
		add_action( 'admin_init', array( $this, 'load_monitor_admin_actions' ) );
	}

	/**
	 * Initializes extension menu.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework()->load_options variable. Applies filters 'the_seo_framework_load_options'
	 * @uses tsf_extension_manager()->can_do_settings().
	 */
	public function init_menu() {

		if ( tsf_extension_manager()->can_do_settings() && the_seo_framework()->load_options )
			add_action( 'admin_menu', array( $this, 'add_menu_link' ), 11 );
	}

	/**
	 * Adds menu link for extension manager, when possible, underneath The
	 * SEO Framework SEO settings.
	 *
	 * @since 1.0.0
	 * @uses the_seo_framework()->page_id variable.
	 * @access private
	 */
	public function add_menu_link() {

		$menu = array(
			'parent_slug'	=> the_seo_framework_options_page_slug(),
			'page_title'	=> esc_html__( 'SEO Monitor', 'the-seo-framework-extension-manager' ),
			'menu_title'	=> esc_html__( 'Monitor', 'the-seo-framework-extension-manager' ),
			'capability'	=> 'install_plugins',
			'menu_slug'		=> $this->monitor_page_slug,
			'callback'		=> array( $this, 'init_monitor_page' ),
		);

		$this->monitor_menu_page_hook = add_submenu_page(
			$menu['parent_slug'],
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 *
	 * @since 1.0.0
	 * @uses $this->monitor_menu_page_hook variable.
	 * @access private
	 */
	public function load_monitor_admin_actions() {

		add_action( 'load-' . $this->monitor_menu_page_hook, array( $this, 'do_monitor_admin_actions' ) );

	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 * Early enough for admin_notices and admin_head :).
	 *
	 * @since 1.0.0
	 * @staticvar bool $run
	 * @access private
	 *
	 * @return bool True on actions loaded, false on second load.
	 */
	public function do_monitor_admin_actions() {

		if ( false === $this->is_monitor_page() )
			return;

		static $run = false;

		if ( $run )
			return false;

		//* Initialize user interface.
		$this->init_tsfem_ui();

		//* Add something special for Vivaldi
		add_action( 'admin_head', array( $this, 'output_theme_color_meta' ), 0 );

		//* Add footer output.
		add_action( 'in_admin_footer', array( $this, 'init_monitor_footer_wrap' ) );

		return $run = true;
	}

	/**
	 * Initializes user interface.
	 *
	 * @since 1.0.0
	 */
	protected function init_tsfem_ui() {

		$this->ui_hook = $this->monitor_menu_page_hook;

		$this->init_ui();
	}

	/**
	 * Determines whether we're on the monitor overview page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function is_monitor_page() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		//* Don't load from $_GET request.
		return $cache = the_seo_framework()->is_menu_page( $this->monitor_menu_page_hook );
	}

	/**
	 * Initializes the admin page output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function init_monitor_page() {
		?>
		<div class="wrap tsfem tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">
			<?php
				$this->output_monitor_overview_wrapper();
			?>
		</div>
		<?php
	}

	/**
	 * Echos main page wrapper for monitor.
	 *
	 * @since 1.0.0
	 */
	protected function output_monitor_overview_wrapper() {

		$this->do_page_header_wrap( true );

		?>
		<div class="tsfem-panes-wrap tsfem-flex tsfem-flex-nowrap">
			<?php
			$this->do_monitor_overview();
			?>
		</div>
		<?php
	}

	/**
	 * Echos the page title wrap.
	 *
	 * @since 1.0.0
	 */
	protected function do_page_header_wrap() {
		$this->get_view( 'layout/general/header' );
	}

	/**
	 * Echos the monitor overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_monitor_overview() {
		$this->get_view( 'layout/pages/monitor' );
	}

	/**
	 * Initializes the admin footer output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function init_monitor_footer_wrap() {
		?>
		<div class="tsfem-footer-wrap tsfem-flex tsfem-flex-nowrap tsfem-disable-cursor">
			<?php
			$this->do_page_footer_wrap();
			?>
		</div>
		<?php
	}

	/**
	 * Echos the page title wrap.
	 *
	 * @since 1.0.0
	 */
	protected function do_page_footer_wrap() {
		$this->get_view( 'layout/general/footer' );
	}

	/**
	 * Outputs theme color meta tag for Vivaldi and mobile browsers.
	 * Does not always work. So many browser bugs... It's just fancy.
	 *
	 * @since 1.0.0
	 */
	public function output_theme_color_meta() {
		$this->get_view( 'layout/pages/meta' );
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
	protected function get_view( $view, array $args = array() ) {

		foreach ( $args as $key => $val )
			$$key = $val;

		$file = TSFEM_E_MONITOR_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

		include( $file );
	}

	protected function get_update_url() {
		return 'http://example.com/';
	}
}
