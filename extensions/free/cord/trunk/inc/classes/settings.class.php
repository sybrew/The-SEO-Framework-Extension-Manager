<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Settings
 */
namespace TSF_Extension_Manager\Extension\Cord;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Cord extension for The SEO Framework
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
\TSF_Extension_Manager\_load_trait( 'core/ui' );

/**
 * Require error trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/error' );

/**
 * Require Cord POST handling trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\Extension\Cord\_load_trait( 'secure-post' );

/**
 * Class TSF_Extension_Manager\Extension\Cord\Settings
 *
 * Holds extension settings methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 109xxxx
 */
final class Settings {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Core_Static_Final_Instance,
		\TSF_Extension_Manager\UI,
		\TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Error,
		Secure_Post;

	/**
	 * The settings page slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $slug
	 */
	protected $slug = '';

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Core $_core
	 *                        Used for integrity.
	 * @param string $slug    The menu slug.
	 * @param string $hook    The menu hook.
	 * @param string $o_index The options index.
	 */
	public function _init( Core $_core, $slug, $hook, $o_index ) {

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = $o_index;

		$this->_init_main();

		/**
		 * Set page slug.
		 */
		$this->slug = $slug;

		/**
		 * Set UI hook.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->ui_hook = $hook;

		/**
		 * Initialize user interface.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_tsfem_ui();
	}

	/**
	 * Initializes AJAX for Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Core $_core
	 *                        Used for integrity.
	 * @param string $o_index The options index.
	 */
	public function _init_ajax( Core $_core, $o_index ) {
		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = $o_index;

		$this->_init_main();
	}

	/**
	 * Initializes main Settings page properties and methods.
	 *
	 * Both for AJAX and HTML output.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void Early on second call.
	 */
	private function _init_main() {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) ) return;

		/**
		 * Set form arguments.
		 * @see class TSF_Extension_Manager\FormGenerator
		 */
		$this->form_args = [
			'caller'       => __CLASS__,
			'o_index'      => $this->o_index,
			'o_key'        => '',
			'use_stale'    => true,
			'levels'       => 5,
			'architecture' => null,
		];

		/**
		 * Set error notice option.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_cord_error_notice_option';

		/**
		 * Initialize error interface.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->init_errors();

		/**
		 * Sets nonces.
		 * @see trait TSF_Extension_Manager\Extension\Cord\Secure_Post
		 */
		$this->set_nonces();

		/**
		 * Initialize POST data checks.
		 * @see trait TSF_Extension_Manager\Extension\Cord\Secure_Post
		 */
		$this->init_post_checks();
	}

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Core $_core Used for integrity.
	 */
	public function _output_settings_page( Core $_core ) {
		\add_action( 'tsfem_header', [ $this, '_output_cord_header' ] );
		\add_action( 'tsfem_content', [ $this, '_output_cord_content' ] );
		\add_action( 'tsfem_footer', [ $this, '_output_cord_footer' ] );

		$this->wrap_type = 'row';
		$this->ui_wrap( 'panes' );
	}

	/**
	 * Outputs monitor header.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _output_cord_header() {
		$this->get_view( 'layout/general/top' );
	}

	/**
	 * Outputs monitor content.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _output_cord_content() {
		$this->get_view( 'layout/pages/cord' );
	}

	/**
	 * Outputs monitor footer.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _output_cord_footer() {
		$this->get_view( 'layout/general/footer' );
	}

	/**
	 * Initializes user interface styles, scripts and footer.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\UI
	 */
	private function init_tsfem_ui() {

		\add_action( 'tsfem_before_enqueue_scripts', [ $this, '_register_cord_scripts' ] );

		//* Add something special for Vivaldi
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		/**
		 * Initialize UI calls.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_ui();
	}

	/**
	 * Registers default TSFEM Cord admin scripts.
	 * Also registers TSF scripts, for TT (tooltip) support.
	 *
	 * @since 1.1.3
	 * @access private
	 * @internal
	 * @staticvar bool $registered : Prevents Re-registering of the script.
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	public function _register_cord_scripts( $scripts ) {
		static $registered = false;
		if ( $registered ) return;

		/**
		 * Registers media scripts.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->register_media_scripts( $scripts );

		$scripts::register( [
			[
				'id'       => 'tsfem-cord',
				'type'     => 'js',
				'deps'     => [ 'wp-util', 'tsf-tt', 'tsfem-form', 'tsfem-media' ],
				'autoload' => true,
				'name'     => 'tsfem-cord',
				'base'     => TSFEM_E_CORD_DIR_URL . 'lib/js/',
				'ver'      => TSFEM_E_CORD_VERSION,
				'l10n'     => [
					'name' => 'tsfem_e_cordL10n',
					'data' => [
						'nonce' => \wp_create_nonce( 'tsfem-e-cord-ajax-nonce' ),
						'i18n'  => [],
					],
				],
			],
		] );
		$registered = true;
	}

	/**
	 * Outputs Settings Panel overview for Cord settings.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Settings $_s Used for integrity.
	 */
	public function _get_cord_settings_overview( self $_s ) {
		$this->get_view( 'layout/pages/settings' );
	}

	/**
	 * Outputs Statistics Panel overview for Cord stats.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Settings $_s Used for integrity.
	 */
	public function _get_cord_stats_overview( self $_s ) {
		$this->get_view( 'layout/pages/stats' );
	}

	/**
	 * Outputs Logs Panel overview for Cord logs.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Settings $_s Used for integrity.
	 */
	public function _get_cord_logs_overview( self $_s ) {
		$this->get_view( 'layout/pages/logs' );
	}

	/**
	 * Outputs bottom wrap for Cord Settings.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Settings $_s Used for integrity.
	 */
	public function _get_cord_settings_bottom_wrap( self $_s ) {
		echo '<button class="tsfem-button tsfem-button-primary tsfem-button-flat">Save</button>';
	}

	/**
	 * Outputs bottom wrap for Cord Stats.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Settings $_s Used for integrity.
	 */
	public function _get_cord_stats_bottom_wrap( self $_s ) {
		echo '<button class="tsfem-button tsfem-button-primary tsfem-button-flat">Refresh</button>';
	}

	/**
	 * Outputs bottom wrap for Cord Logs.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Cord\Settings $_s Used for integrity.
	 */
	public function _get_cord_logs_bottom_wrap( self $_s ) {
		echo '<button class="tsfem-button tsfem-button-primary tsfem-button-flat">Refresh</button>';
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
	private function get_view( $view, array $args = [] ) {

		foreach ( $args as $key => $val ) {
			$$key = $val;
		}

		include TSFEM_E_CORD_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
	}
}
