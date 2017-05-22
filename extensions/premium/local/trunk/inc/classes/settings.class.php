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
* Require user interface trait.
* @since 1.0.0
*/
\TSF_Extension_Manager\_load_trait( 'ui' );

/**
 * Require extension forms trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension-forms' );

/**
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Core_Static_Final_Instance as Construct_Core_Static_Final_Instance;
use \TSF_Extension_Manager\UI as UI;
use \TSF_Extension_Manager\Extension_Forms as Extension_Forms;
use \TSF_Extension_Manager\Error as Error;

/**
 * Require Local security trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\Extension\Local\_load_trait( 'secure-post' );

/**
 * Class TSF_Extension_Manager\Extension\Local\Settings
 *
 * Holds extension settings methods.
 *
 * @since 1.0.0
 * @access private
 */
final class Settings {
	use Enclose_Core_Final, Construct_Core_Static_Final_Instance, UI, Extension_Forms, Error, Secure_Post;

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Local\Core $_core Used for integrity.
	 * @param string $hook The menu hook.
	 */
	public function _init( Core $_core, $hook ) {

		/**
		 * Set error notice option.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_local_error_notice_option';

		/**
		 * Initialize error interface.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->init_errors();

		/**
		 * Sets nonces.
		 * @see trait TSF_Extension_Manager\Extension\Local\Security
		 */
		$this->set_nonces();

		/**
		 * Set UI hook.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->ui_hook = $hook;

		/**
		 * Initialize user interface.
		 */
		$this->init_tsfem_ui();

		//* Update POST listener.
		\add_action( 'admin_init', [ $this, '_handle_update_post' ] );

		//* Add something special for Vivaldi
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		//* Add footer output.
		\add_action( 'in_admin_footer', [ $this, '_init_local_footer_wrap' ] );

		//* AJAX update listener.
		\add_action( 'wp_ajax_tsfem_e_local_update', [ $this, '_wp_ajax_update_data' ] );

		//* AJAX API listener.
		\add_action( 'wp_ajax_tsfem_e_local_api_request', [ $this, '_wp_ajax_do_api' ] );

	}

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Local\Core $_core Used for integrity.
	 */
	public function _output_settings_page( Core $_core ) {
		$this->output_admin_page();
	}

	/**
	 * Initializes user interface styles, scripts and footer.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\UI
	 */
	private function init_tsfem_ui() {

		/**
		 * Set additional CSS file calls.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->additional_css = [
			[
				'name' => 'tsfem-local',
				'base' => TSFEM_E_LOCAL_DIR_URL,
				'ver' => TSFEM_E_LOCAL_VERSION,
			],
		];

		/**
		 * Set additional JS file calls.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->additional_js = [
			[
				'name' => 'tsfem-local',
				'base' => TSFEM_E_LOCAL_DIR_URL,
				'ver' => TSFEM_E_LOCAL_VERSION,
			],
		];

		/**
		 * Set additional l10n.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->additional_l10n = [
			[
				'dependency' => 'tsfem-local',
				'name' => 'tsfem_e_localL10n',
				'strings' => [
					'nonce' => \wp_create_nonce( 'tsfem-e-local-ajax-nonce' ),
				],
			],
		];

		/**
		 * Initialize UI calls.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_ui();
	}

	/**
	 * Outputs the admin page.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	protected function output_admin_page() {
		?>
		<div class="wrap tsfem tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">
			<?php $this->output_local_overview_wrapper(); ?>
		</div>
		<?php
	}

	/**
	 * Echos main page wrapper.
	 *
	 * @since 1.0.0
	 */
	protected function output_local_overview_wrapper() {

		$this->do_page_top_wrap();

		?>
		<div class="tsfem-panes-wrap tsfem-flex tsfem-flex-nowrap">
			<?php $this->do_local_overview(); ?>
		</div>
		<?php
	}

	/**
	 * Echos the page top wrap.
	 *
	 * @since 1.0.0
	 */
	protected function do_page_top_wrap() {
		$this->get_view( 'layout/general/top' );
	}

	/**
	 * Echos the settings overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_local_overview() {
		$this->get_view( 'layout/pages/local' );
	}

	/**
	 * Outputs Settings Panel overview for Local SEO settings.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Local\Settings $_i Used for integrity.
	 */
	public function _get_local_settings_overview( self $_i ) {
		$this->get_view( 'layout/pages/settings' );
	}

	/**
	 * Outputs the admin footer.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_local_footer_wrap() {
		?>
		<div class="tsfem-footer-wrap tsfem-flex tsfem-flex-nowrap tsfem-disable-cursor">
			<?php $this->get_view( 'layout/general/footer' ); ?>
		</div>
		<?php
	}

	/**
	 * Outputs theme color meta tag for Vivaldi and mobile browsers.
	 * Does not always work. So many browser bugs... It's just fancy.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _output_theme_color_meta() {
		$this->get_view( 'layout/general/meta' );
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

		$file = TSFEM_E_LOCAL_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

		include( $file );
	}
}
