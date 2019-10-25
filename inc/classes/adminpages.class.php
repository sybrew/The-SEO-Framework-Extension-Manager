<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'core/ui' );

/**
 * Class TSF_Extension_Manager\AdminPages
 *
 * Holds plugin admin page functions.
 *
 * @since 1.0.0
 * @access private
 */
class AdminPages extends AccountActivation {
	use Enclose_Stray_Private,
		Construct_Child_Interface,
		UI;

	/**
	 * Name of the page hook when the menu is registered.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page hook.
	 */
	public $seo_extensions_menu_page_hook;

	/**
	 * The plugin page ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string Page ID/Slug
	 */
	public $seo_extensions_page_slug = 'theseoframework-extensions';

	/**
	 * The plugin settings field.
	 *
	 * @since 1.0.0
	 *
	 * @var string TSF Extension Manager Settings Field.
	 */
	const SETTINGS_FIELD = TSF_EXTENSION_MANAGER_SITE_OPTIONS;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		//* Initialize menu links. TODO add network menu.
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		//* Initialize TSF Extension Manager page actions.
		\add_action( 'admin_init', [ $this, '_load_tsfem_admin_actions' ] );
	}

	/**
	 * Initializes extension manager menu.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Now uses \TSF_Extension_Manager\can_do_manager_settings()
	 * @uses \the_seo_framework()->load_options variable. Applies filters 'the_seo_framework_load_options'
	 * @access private
	 *
	 * @todo determine network activation @see core class.
	 */
	final public function _init_menu() {

		if ( ! \TSF_Extension_Manager\can_do_manager_settings() )
			return;

		if ( $this->is_plugin_in_network_mode() ) {
			//* TODO.
			//	\add_action( 'network_admin_menu', [ $this, 'add_network_menu_link' ], 11 );
		} else {
			if ( \the_seo_framework()->load_options )
				\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 11 );
		}
	}

	/**
	 * Adds menu link for extension manager, when possible, underneath The
	 * SEO Framework SEO settings.
	 *
	 * @since 1.0.0
	 * @since 1.5.2 Added TSF v3.1 compat.
	 * @uses \the_seo_framework()->seo_settings_page_slug
	 * @access private
	 */
	final public function _add_menu_link() {

		$menu = [
			'parent_slug' => \the_seo_framework()->seo_settings_page_slug,
			'page_title'  => 'Extension Manager',
			'menu_title'  => \esc_html__( 'Extensions', 'the-seo-framework-extension-manager' ),
			'capability'  => TSF_EXTENSION_MANAGER_MAIN_ADMIN_ROLE,
			'menu_slug'   => $this->seo_extensions_page_slug,
			'callback'    => [ $this, '_init_extension_manager_page' ],
		];

		$notice_count = count( \get_option( $this->error_notice_option, false ) ?: [] );

		if ( $notice_count ) {
			/**
			 * TODO Update these when clearing them via JS.
			 *
			 * @see /wp-admin/menu.php @ $awaiting_mod & edit-comments.js' updateCountText & updateInModerationText
			 */
			$notice_i18n = \number_format_i18n( $notice_count );
			$notice_text = sprintf(
				/* translators: %s: number of notices waiting */
				_n( '%s notice waiting', '%s notices waiting', $notice_count, 'the-seo-framework-extension-manager' ),
				$notice_i18n
			);

			$menu['menu_title'] .= ' ' . sprintf(
				'<span class="tsfem-menu-notice tsfem-menu-errors count-%d"><span class="tsfem-error-count" aria-hidden="true">%s</span><span class="tsfem-error-count-text screen-reader-text">%s</span></span>',
				$notice_count,
				$notice_i18n,
				$notice_text
			);

			$this->load_menu_notice_styles();
		}

		$this->seo_extensions_menu_page_hook = \add_submenu_page(
			$menu['parent_slug'],
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);
	}

	/**
	 * Loads the menu notice script, via TSF's v3.1 script handler.
	 *
	 * @since 2.1.0
	 */
	final public function load_menu_notice_styles() {

		$_scheme = \get_user_option( 'admin_color' ) ?: 'fresh';
		/**
		 * This is inaccurate, because WordPress is combobulating these colors.
		 * We're more WordPress than WordPress.
		 *
		 * As such, we do not process this on the 'fresh', 'light', and 'blue' schemes.
		 * 'midnight' should also be excluded, but that's messed up on another level.
		 * Let's just say we got this mildly accurate on 6 out of 8 schemes.
		 */
		$inline = in_array( $_scheme, [ 'fresh', 'light', 'blue' ], true ) ? null : [
			'#adminmenu .tsfem-menu-notice' => [
				'background-color:{{$color_accent}}',
				'color:{{$rel_color_accent}}',
			],
		];

		\The_SEO_Framework\Builders\Scripts::register( [
			'id'       => 'tsfem-menu-notice',
			'type'     => 'css',
			'deps'     => [ 'admin-menu' ],
			'autoload' => true,
			'hasrtl'   => false,
			'name'     => 'tsfem-menu-notice',
			'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/css/',
			'ver'      => TSF_EXTENSION_MANAGER_VERSION,
			'inline'   => $inline,
		] );
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 *
	 * @since 1.0.0
	 * @uses $this->seo_extensions_menu_page_hook variable.
	 * @access private
	 */
	final public function _load_tsfem_admin_actions() {
		\add_action( 'load-' . $this->seo_extensions_menu_page_hook, [ $this, '_do_tsfem_admin_actions' ] );
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
	final public function _do_tsfem_admin_actions() {

		if ( false === $this->is_tsf_extension_manager_page() )
			return;

		static $run = false;

		if ( $run )
			return false;

		//* Initialize user interface.
		$this->init_tsfem_ui();

		//* Initialize error interface.
		$this->init_errors();

		/**
		 * Revalidate subscription. See \TSF_Extension_Manager\AccountActivation
		 * Requires \TSF_Extension_Manager\Error
		 */
		$this->revalidate_subscription();

		//* Add something special for Vivaldi and Android.
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		return $run = true;
	}

	/**
	 * Initializes user interface.
	 *
	 * @since 1.0.0
	 */
	final protected function init_tsfem_ui() {

		$this->ui_hook = $this->seo_extensions_menu_page_hook;

		$this->init_ui();
	}

	/**
	 * Initializes and outputs the default admin page output.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _init_extension_manager_page() {
		\add_action( 'tsfem_header', [ $this, '_output_em_header' ] );
		\add_action( 'tsfem_content', [ $this, '_output_em_content' ] );
		\add_action( 'tsfem_footer', [ $this, '_output_em_footer' ] );

		if ( $this->is_plugin_activated() ) {
			$this->wrap_type = 'row';
			$type            = 'panes';
		} else {
			$this->wrap_type = 'column';
			$type            = 'connect';
		}

		$this->ui_wrap( $type );
	}

	/**
	 * Outputs extension manager header.
	 *
	 * @since 1.5.0
	 * @access private
	 */
	final public function _output_em_header() {
		$this->get_view(
			'layout/general/top',
			[
				'options' => $this->is_plugin_activated(),
			]
		);
	}

	/**
	 * Outputs extension manager content.
	 *
	 * @since 1.5.0
	 * @access private
	 */
	final public function _output_em_content() {
		if ( $this->is_plugin_activated() ) {
			$this->get_view( 'layout/pages/extensions' );
		} else {
			$this->get_view( 'layout/pages/activation' );
		}
	}

	/**
	 * Outputs extension manager footer.
	 *
	 * @since 1.5.0
	 * @access private
	 */
	final public function _output_em_footer() {
		$this->get_view( 'layout/general/footer' );
	}

	/**
	 * Outputs theme color meta tag for Vivaldi and mobile browsers.
	 * Does not always work. So many browser bugs... It's just fancy.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final public function _output_theme_color_meta() {
		$this->get_view( 'layout/pages/meta' );
	}

	/**
	 * Echos a pane wrap.
	 *
	 * @since 1.0.0
	 * @since 2.0.1 Added the push argument.
	 * @access private
	 *
	 * @param string $title   The pane title.
	 * @param string $content The escaped pane content.
	 * @param array  $args    The output arguments : {
	 *   'full'       bool     : Whether to output a half or full pane.
	 *   'collapse'   bool     : Whether able to collapse the pane.
	 *   'move'       bool     : Whether to be able to move the pane.
	 *   'push'       bool     : Whether to push other panes away in flexibility.
	 *   'pane_id'    string   : The pane div ID.
	 *   'ajax'       bool     : Whether to use ajax.
	 *   'ajax_id'    string   : The AJAX div ID.
	 *   'footer'     callable : Whether to add a footer wrap. If set, it must be
	 *                           a callable. If secure_obj is also true, it must be an
	 *                           object and it will pass the class object.
	 *   'fcbargs'    iterable : A array of arguments to pass to the footer callback. Gets unpacked.
	 * }
	 * @param string $extra Extra header output placed between the title and ajax loader.
	 */
	final public function _do_pane_wrap( $title = '', $content = '', $args = [], $extra = '' ) {

		$args = array_merge(
			[
				'full'     => true,
				'collapse' => true,
				'move'     => false,
				'push'     => false,
				'pane_id'  => '',
				'ajax'     => false,
				'ajax_id'  => '',
				'footer'   => null,
				'fcbargs'  => [],
			],
			$args
		);

		$this->get_view( 'layout/general/pane', get_defined_vars() );
	}

	/**
	 * Echos a pane wrap with callable function, rather than passing content.
	 *
	 * @since 1.3.0
	 * @since 2.0.1 Added the push argument.
	 * @since 2.2.0 Added the cbargs and fcbargs arguments.
	 * @access private
	 *
	 * @param string $title    The pane title.
	 * @param string $callable The callable function or method that echos content.
	 * @param array  $args     The output arguments : {
	 *   'full'       bool     : Whether to output a half or full pane.
	 *   'collapse'   bool     : Whether able to collapse the pane.
	 *   'move'       bool     : Whether to be able to move the pane.
	 *   'push'       bool     : Whether to push other panes away in flexibility.
	 *   'pane_id'    string   : The pane div ID.
	 *   'ajax'       bool     : Whether to use ajax.
	 *   'ajax_id'    string   : The AJAX div ID.
	 *   'secure_obj' bool     : Whether to pass the class object for integrity checks.
	 *   'footer'     callable : Whether to add a footer wrap. If set, it must be
	 *                           a callable. If secure_obj is also true, it must be an
	 *                           object and it will pass the class object.
	 *   'cbargs'     iterable : A array of arguments to pass to the callback. Gets unpacked.
	 *   'fcbargs'    iterable : A array of arguments to pass to the footer callback. Gets unpacked.
	 * }
	 * @param string $extra Extra header output placed between the title and ajax loader.
	 */
	final public function _do_pane_wrap_callable( $title = '', $callable = '', $args = [], $extra = '' ) {

		$args = array_merge(
			[
				'full'       => true,
				'collapse'   => true,
				'move'       => false,
				'push'       => false,
				'pane_id'    => '',
				'ajax'       => false,
				'ajax_id'    => '',
				'secure_obj' => false,
				'footer'     => null,
				'cbargs'     => [],
				'fcbargs'    => [],
			],
			$args
		);

		$this->get_view( 'layout/general/pane', get_defined_vars() );
	}

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Other page implementation classes may wish to construct and use a
	 * _get_field_id() method, if the naming format needs to be different.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $name Field name base.
	 * @return string Full field name.
	 */
	final public function _get_field_name( $name ) {
		return sprintf( '%s[%s]', self::SETTINGS_FIELD, $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 1.0.0
	 * @access private
	 * @uses $this->_get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	final public function _field_name( $name ) {
		echo \esc_attr( $this->_get_field_name( $name ) );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $id Field id base.
	 * @return string Full field id.
	 */
	final public function _get_field_id( $id ) {
		return sprintf( '%s[%s]', self::SETTINGS_FIELD, $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 1.0.0
	 * @access private
	 * @uses $this->_get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string  $id   Field id base.
	 * @param boolean $echo Whether to echo or return.
	 * @return string Full field id
	 */
	final public function _field_id( $id, $echo = true ) {

		if ( $echo ) {
			echo \esc_attr( $this->_get_field_id( $id ) );
		} else {
			return $this->_get_field_id( $id );
		}
	}

	/**
	 * Outputs nonce action field.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key The action key.
	 */
	final public function _nonce_action_field( $key ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
		echo $this->_get_nonce_action_field( $key );
	}

	/**
	 * Returns nonce action field.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key The action key.
	 */
	final public function _get_nonce_action_field( $key ) {
		return '<input type="hidden" name="' . \esc_attr( $this->_get_field_name( 'nonce-action' ) ) . '" value="' . \esc_attr( $key ) . '">';
	}
}
