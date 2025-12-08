<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Settings
 */

namespace TSF_Extension_Manager\Extension\Local;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/error' );

/**
 * Require Local POST handling trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\Extension\Local\_load_trait( 'secure-post' );

/**
 * Require Local Schema Data Packer trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\Extension\Local\_load_trait( 'schema-packer' );

/**
 * Require extension views trait.
 *
 * @since 1.2.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/views' );

/**
 * Class TSF_Extension_Manager\Extension\Local\Settings
 *
 * Holds extension settings methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 107xxxx
 */
final class Settings {
	use \TSF_Extension_Manager\Construct_Core_Static_Final_Instance,
		\TSF_Extension_Manager\UI,
		\TSF_Extension_Manager\Extension_Views,
		\TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Error,
		Secure_Post,
		Schema_Packer;

	/**
	 * @since 1.0.0
	 * @var string Settings page slug name.
	 */
	protected $slug = '';

	/**
	 * @since 1.0.0
	 * @var array The settings form architectural arguments.
	 */
	protected $form_args = [];

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param Core   $_core   Used for integrity.
	 * @param string $slug    The menu slug.
	 * @param string $hook    The menu hook.
	 * @param string $o_index The options index.
	 */
	public function _init( Core $_core, $slug, $hook, $o_index ) {

		/**
		 * @see trait TSF_Extension_Manager\Extension_Views
		 */
		$this->view_location_base = \TSFEM_E_LOCAL_DIR_PATH . 'views' . \DIRECTORY_SEPARATOR;

		/**
		 * Set options index.
		 *
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
		 *
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->ui_hook = $hook;

		/**
		 * Initialize user interface.
		 *
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
	 * @param Core   $_core Used for integrity.
	 * @param string $o_index The options index.
	 */
	public function _init_ajax( Core $_core, $o_index ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		/**
		 * Set options index.
		 *
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
		 *
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
		 *
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_local_error_notice_option';

		/**
		 * Initialize error interface.
		 *
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->init_errors();

		/**
		 * Sets nonces.
		 *
		 * @see trait TSF_Extension_Manager\Extension\Local\Secure_Post
		 */
		$this->set_nonces();

		/**
		 * Initialize POST data checks.
		 *
		 * @see trait TSF_Extension_Manager\Extension\Local\Secure_Post
		 */
		$this->init_post_checks();
	}

	/**
	 * Prepares form object.
	 *
	 * @since 1.2.0
	 */
	private function prepare_form() {
		$this->get_form();
	}

	/**
	 * Returns form object by reference.
	 *
	 * @since 1.2.0
	 *
	 * @return \TSF_Extension_Manager\FormGenerator Passed by reference.
	 */
	private function get_form() {
		static $form;
		return $form ?: $form = new \TSF_Extension_Manager\FormGenerator( $this->form_args );
	}

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param Core $_core Used for integrity.
	 */
	public function _output_settings_page( Core $_core ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		$this->prepare_form();

		\add_action( 'tsfem_header', [ $this, '_output_local_header' ] );
		\add_action( 'tsfem_content', [ $this, '_output_local_content' ] );
		\add_action( 'tsfem_footer', [ $this, '_output_local_footer' ] );

		$this->ui_wrap( 'panes' );
	}

	/**
	 * Outputs monitor header.
	 *
	 * @since 1.0.1
	 * @access private
	 */
	public function _output_local_header() {
		$this->get_view( 'layout/general/top' );
	}

	/**
	 * Outputs monitor content.
	 *
	 * @since 1.0.1
	 * @access private
	 */
	public function _output_local_content() {
		$this->get_view( 'layout/pages/local' );
	}

	/**
	 * Outputs monitor footer.
	 *
	 * @since 1.0.1
	 * @access private
	 */
	public function _output_local_footer() {
		$this->get_view( 'layout/general/footer' );
	}

	/**
	 * Initializes user interface styles, scripts and footer.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\UI
	 */
	private function init_tsfem_ui() {

		\add_filter( 'the_seo_framework_scripts', [ $this, '_register_local_scripts' ], 10, 3 );

		/**
		 * Initialize UI calls.
		 *
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_ui();
	}

	/**
	 * Registers default TSFEM Local admin scripts.
	 * Also registers TSF scripts, for TT (tooltip) support.
	 *
	 * @since 1.1.3
	 * @since 1.1.7 The extension access level for nonce generation now controlled via another constant.
	 * @access private
	 * @internal
	 *
	 * @param array  $scripts  The default CSS and JS loader settings.
	 * @param string $registry The \The_SEO_Framework\Admin\Script\Registry registry class name.
	 * @param string $loader   The \The_SEO_Framework\Admin\Script\Loader loader class name.
	 * @return array More CSS and JS loaders.
	 */
	public function _register_local_scripts( $scripts, $registry, $loader ) {

		$loader::prepare_media_scripts();

		$scripts[] = $this->get_form_scripts( $scripts );
		$scripts[] = $loader::get_media_scripts();

		// Normally, we load this as a dependency. But, Local has no CSS (that's dependent).
		// $registry::enqueue_known_script( 'tsf', 'css' );

		$scripts[] = [
			'id'       => 'tsfem-local',
			'type'     => 'js',
			'deps'     => [ 'wp-util', 'tsf', 'tsf-tt', 'tsf-media', 'tsfem-ui', 'tsfem-form' ],
			'autoload' => true,
			'name'     => 'tsfem-local',
			'base'     => \TSFEM_E_LOCAL_DIR_URL . 'lib/js/',
			'ver'      => \TSFEM_E_LOCAL_VERSION,
			'l10n'     => [
				'name' => 'tsfem_e_localL10n',
				'data' => [
					// This won't ever run when the user can't. But, sanity.
					'nonce' => \TSF_Extension_Manager\can_do_extension_settings() ? \wp_create_nonce( 'tsfem-e-local-ajax-nonce' ) : '',
					'i18n'  => [
						'fixForm'       => \esc_html__( 'Please correct the form fields before validating the markup.', 'the-seo-framework-extension-manager' ),
						'testNewWindow' => \esc_html__( 'The markup tester will be opened in a new window.', 'the-seo-framework-extension-manager' ),
					],
				],
			],
		];

		return $scripts;
	}

	/**
	 * Outputs Settings Panel overview for Local settings.
	 *
	 * @since 1.0.0
	 *
	 * @param self $_s Used for integrity.
	 */
	public function _local_settings_overview( self $_s ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$this->get_view( 'layout/pages/settings' );
	}

	/**
	 * Outputs Settings bottom wrap for Local Settings.
	 *
	 * @since 1.0.0
	 *
	 * @param self $_s Used for integrity.
	 */
	public function _get_local_settings_bottom_wrap( self $_s ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
		echo $this->get_test_button();
		echo $this->get_form()->_form_button( 'submit', \__( 'Save', 'the-seo-framework-extension-manager' ), 'get' );
		// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Outputs department fields and floating buttons.
	 *
	 * @since 1.0.0
	 * @uses \TSF_Extension_Manager\Extension\Local\Fields
	 * @uses \TSF_Extension_Manager\FormGenerator
	 *
	 * @return void
	 */
	private function output_department_fields() {

		$f = $this->get_form();

		$f->_form_wrap( 'start', \menu_page_url( $this->slug, false ), true );
		$f->_fields( Fields::get_instance()->get_departments_fields() );
		$f->_form_wrap( 'end' );
	}

	/**
	 * Returns test button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The validation button.
	 */
	private function get_test_button() {
		return \sprintf(
			'<button type=button name=tsfem-e-local-validateFormJson form="%s" class="%s">%s</button>',
			\sprintf( '%s[%s]', \TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index ),
			'hide-if-no-tsf-js tsfem-button tsfem-button-external',
			\esc_html__( 'See Markup', 'the-seo-framework-extension-manager' )
		);
	}

	/**
	 * Reprocesses stored data.
	 *
	 * Warning: Heavy.
	 *
	 * @since 1.1.2
	 */
	public function _reprocess_all_stored_data() {
		$this->_init_main();
		$this->process_all_stored_data();
	}
}
