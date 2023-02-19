<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	use Construct_Child_Interface,
		UI;

	/**
	 * @since 1.0.0
	 * @var string Page hook name.
	 */
	public $seo_extensions_menu_page_hook;

	/**
	 * @since 1.0.0
	 * @var string Page ID/Slug.
	 */
	public $seo_extensions_page_slug = 'theseoframework-extensions';

	/**
	 * @since 1.0.0
	 * @var string TSF Extension Manager Settings Field.
	 */
	const SETTINGS_FIELD = TSF_EXTENSION_MANAGER_SITE_OPTIONS;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		// Nothing to do here...
		if ( \tsf()->is_headless['settings'] ) return;

		// Initialize menu links. TODO add network menu.
		\add_action( 'admin_menu', [ $this, '_init_menu' ] );

		// Initialize TSF Extension Manager page actions.
		\add_action( 'admin_init', [ $this, '_load_tsfem_admin_actions' ] );
	}

	/**
	 * Initializes extension manager menu.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Now uses \TSF_Extension_Manager\can_do_manager_settings()
	 * @since 2.4.0 Removed security check, and offloads it to WordPress.
	 * @uses \tsf()->is_headless
	 * @access private
	 *
	 * @todo determine network activation @see core class.
	 */
	final public function _init_menu() {

		// phpcs:ignore, Generic.CodeAnalysis.EmptyStatement -- TODO?
		if ( $this->is_plugin_in_network_mode() ) {
			// phpcs:ignore, Squiz.PHP.CommentedOutCode -- TODO?
			// \add_action( 'network_admin_menu', [ $this, 'add_network_menu_link' ], 11 );
		} else {
			\add_action( 'admin_menu', [ $this, '_add_menu_link' ], 11 );
			\add_filter( 'the_seo_framework_top_menu_issue_count', [ $this, '_increment_tsf_issue_count' ] );
		}
	}

	/**
	 * Adds menu link for extension manager, when possible, underneath The
	 * SEO Framework SEO settings.
	 *
	 * @since 1.0.0
	 * @since 1.5.2 Added TSF v3.1 compat.
	 * @since 2.4.0 Added menu access control check for notification display.
	 * @uses \tsf()->seo_settings_page_slug
	 * @access private
	 */
	final public function _add_menu_link() {

		$menu = [
			'parent_slug' => \tsf()->seo_settings_page_slug,
			'page_title'  => 'Extension Manager',
			'menu_title'  => 'Extension Manager',
			'capability'  => TSF_EXTENSION_MANAGER_MAIN_ADMIN_ROLE,
			'menu_slug'   => $this->seo_extensions_page_slug,
			'callback'    => [ $this, '_init_extension_manager_page' ],
		];

		if ( \TSF_Extension_Manager\can_do_manager_settings() ) {
			$notice_count = $this->get_error_notice_count();

			if ( $notice_count )
				$menu['menu_title'] .= \tsf()->get_admin_menu_issue_badge( $notice_count );
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
	 * Filters TSF's issue count.
	 *
	 * @since 2.6.2
	 *
	 * @param int $issue_count The issue count.
	 * @return int The total issue count.
	 */
	final public function _increment_tsf_issue_count( $issue_count ) {

		if ( \TSF_Extension_Manager\can_do_manager_settings() )
			$issue_count += $this->get_error_notice_count();

		return $issue_count;
	}

	/**
	 * Returns the error notice count.
	 *
	 * @since 2.6.2
	 *
	 * @return int The error notice coun.t
	 */
	final public function get_error_notice_count() {

		// We're displaying the notices now -- assume 0 are left.
		// 'false' because we need to know this before registering the menu.
		// No sensitive data is processed here.
		if ( $this->is_tsf_extension_manager_page( false ) )
			return 0;

		return \count( \get_option( $this->error_notice_option, false ) ?: [] );
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 *
	 * @since 1.0.0
	 * @uses $this->seo_extensions_menu_page_hook variable.
	 * @access private
	 */
	final public function _load_tsfem_admin_actions() {
		\add_action( "load-{$this->seo_extensions_menu_page_hook}", [ $this, '_do_tsfem_admin_actions' ] );
	}

	/**
	 * Hooks admin actions into the TSF Extension Manager pagehook.
	 * Early enough for admin_notices and admin_head :).
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool True on actions loaded, false on second load.
	 */
	final public function _do_tsfem_admin_actions() {

		if ( ! $this->is_tsf_extension_manager_page() )
			return;

		static $run = false;

		if ( $run )
			return false;

		// Initialize user interface.
		$this->init_tsfem_ui();

		// Initialize error interface.
		$this->init_errors();

		/**
		 * Revalidate subscription. See \TSF_Extension_Manager\AccountActivation
		 * Requires \TSF_Extension_Manager\Error
		 */
		$this->revalidate_subscription();

		// Add something special for Vivaldi and Android.
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		// We don't want other plugins crashing this... Output early.
		\add_action( 'tsfem_content', [ $this, '_output_symbols' ], 0 );

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
		$this->get_view( 'layout/general/meta' );
	}

	/**
	 * Outputs symbols and icons for use in browser.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	final public function _output_symbols() {
		$this->get_view( 'layout/pages/symbols' );
	}

	/**
	 * Echos a pane wrap.
	 *
	 * @since 1.0.0
	 * @since 2.0.1 Added the push argument.
	 * @since 2.2.0 Added the logo, wide, tall, and fcbargs arguments.
	 * @since 2.7.0 Logo is now a string from associative array; preferably SVG.
	 * @access private
	 *
	 * @param string $title   The pane title.
	 * @param string $content The escaped pane content.
	 * @param array  $args    The output arguments : {
	 *   'logo'       string   : An string with svg logo link.
	 *   'full'       bool     : Whether to output a two wide and high pane.
	 *   'wide'       bool     : Whether to output a two wide pane.
	 *   'tall'       bool     : Whether to output a two tall pane. Quite useless and ugly.
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
				'logo'     => '',
				'full'     => true,
				'wide'     => false,
				'tall'     => false,
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
	 * @since 2.2.0 Added the logo, wide, tall, cbargs, and fcbargs arguments.
	 * @access private
	 *
	 * @param string $title    The pane title.
	 * @param string $callable The callable function or method that echos content.
	 * @param array  $args     The output arguments : {
	 *   'logo'       array    : An array with svg, 1x, and 2x logo links. 1x is required. svg is preferred.
	 *   'full'       bool     : Whether to output a two wide and high pane.
	 *   'wide'       bool     : Whether to output a two wide pane.
	 *   'tall'       bool     : Whether to output a two tall pane. Quite useless and ugly.
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
				'logo'       => [],
				'full'       => true,
				'wide'       => false,
				'tall'       => false,
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
		return '<input type=hidden name="' . \esc_attr( $this->_get_field_name( 'nonce-action' ) ) . '" value="' . \esc_attr( $key ) . '">';
	}

	/**
	 * Outputs nonce field, without a DOM ID.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @param string $action  The action key.
	 * @param string $name    The nonce name.
	 * @param bool   $referer Whether to set the referer field for validation.
	 */
	final public function _nonce_field( $action, $name, $referer = true ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
		echo $this->_get_nonce_field( $action, $name, $referer );
	}

	/**
	 * Returns nonce field, without a DOM ID.
	 *
	 * @since 2.5.0
	 * @access private
	 * @source <https://developer.wordpress.org/reference/functions/wp_nonce_field/>
	 *
	 * @param string $action  The action key.
	 * @param string $name    The nonce name.
	 * @param bool   $referer Whether to set the referer field for validation.
	 */
	final public function _get_nonce_field( $action, $name, $referer = true ) {

		$name        = \esc_attr( $name );
		$nonce_field = '<input type=hidden name="' . $name . '" value="' . \wp_create_nonce( $action ) . '" />';

		if ( $referer ) {
			$nonce_field .= \wp_referer_field( false );
		}

		return $nonce_field;
	}
}
