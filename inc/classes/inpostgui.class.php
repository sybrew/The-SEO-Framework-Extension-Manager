<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 *
 * @see EOF. Because of the autoloader and trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_inpostgui_class = function() {
	new InpostGUI(); // phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape -- correct scope.
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
 * @uses trait TSF_Extension_Manager\Construct_Master_Once_Final_Interface
 *       This means you shouldn't invoke new yourself.
 * @see package TSF_Extension_Manager\Traits\Overload
 *
 * @final Can't be extended.
 */
final class InpostGUI {
	use Construct_Master_Once_Final_Interface;

	/**
	 * @since 1.5.0
	 * @var string NONCE_ACTION The nonce action.
	 */
	const NONCE_ACTION = 'tsfem-save-inpost-nonce';

	/**
	 * @since 1.5.0
	 * @var string NONCE_NAME The nonce name.
	 */
	const NONCE_NAME = 'tsfem-inpost-settings';

	/**
	 * @since 1.5.0
	 * @var string JS_NONCE_ACTION The JS nonce action.
	 */
	const JS_NONCE_ACTION = 'tsfem-ajax-save-inpost-nonce';

	/**
	 * @since 1.5.0
	 * @var string JS_NONCE_NAME The JS nonce name.
	 */
	const JS_NONCE_NAME = 'nonce';

	/**
	 * @since 1.5.0
	 * @var string META_PREFIX The meta prefix to handle POST data.
	 */
	const META_PREFIX = 'tsfem-pm';

	/**
	 * @since 1.5.0
	 * @see static::_verify_nonce()
	 * @var string The state the save is in.
	 */
	public static $save_access_state = 0;

	/**
	 * @since 1.5.0
	 * @var string The inclusion secret generated on tab load.
	 */
	private static $include_secret;

	/**
	 * @since 1.5.0
	 * @var array The registered tabs.
	 */
	private static $tabs = [];

	/**
	 * @since 1.5.0
	 * @var array The activate tab keys of static::$tabs
	 */
	private static $active_tab_keys = [];

	/**
	 * @since 1.5.0
	 * @var array The registered view files for the tabs.
	 */
	private static $views = [];

	/**
	 * @since 1.5.0
	 * @var array The registered templates.
	 */
	private static $templates = [];

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 1.5.0
	 */
	public static function prepare() {}

	/**
	 * Constructor. Loads all appropriate actions asynchronously.
	 *
	 * @TODO consider running "post type supported" calls, instead of relying on failsafes in TSF.
	 * @see \tsf()->_init_admin_scripts(); this requires TSF 4.0+ dependency, however.
	 */
	private function construct() {

		$this->register_tabs();

		// Scripts.
		\add_action( 'load-post.php', [ $this, '_prepare_admin_scripts' ] );
		\add_action( 'load-post-new.php', [ $this, '_prepare_admin_scripts' ] );

		// Saving.
		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_output_nonce' ], 9 );
		\add_action( 'save_post', [ static::class, '_verify_nonce' ], 1, 2 );

		// Output.
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
				'name'     => \__( 'Structure', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_tab_content' ],
				'dashicon' => 'layout',
				'args'     => [ 'structure' ],
			],
			'audit'     => [
				'name'     => \__( 'Audit', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_tab_content' ],
				'dashicon' => 'analytics',
				'args'     => [ 'audit' ],
			],
			'advanced'  => [
				'name'     => \__( 'Advanced', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_tab_content' ],
				'dashicon' => 'list-view',
				'args'     => [ 'advanced' ],
			],
		];
	}

	/**
	 * Prepares scripts for output on post edit screens.
	 *
	 * @since 1.5.0
	 */
	public function _prepare_admin_scripts() {

		\The_SEO_Framework\Builders\Scripts::prepare();

		// Enqueue default scripts.
		\add_action( 'tsfem_inpost_before_enqueue_scripts', [ $this, '_register_default_scripts' ] );

		// Enqueue early styles & scripts.
		\add_action( 'admin_enqueue_scripts', [ $this, '_load_admin_scripts' ], 0 );

		// Enqueue late initialized styles & scripts.
		\add_action( 'admin_footer', [ $this, '_load_admin_scripts' ], 0 );
	}

	/**
	 * Registers admin scripts.
	 *
	 * @since 2.5.0
	 * @access private
	 * @internal
	 */
	public function _load_admin_scripts() {
		/**
		 * @since 2.0.2
		 * @param string $scripts The scripts builder class name.
		 */
		\do_action( 'tsfem_inpost_before_enqueue_scripts', \The_SEO_Framework\Builders\Scripts::class );
	}

	/**
	 * Registers default inpost scripts.
	 *
	 * @since 1.5.0
	 * @since 2.0.0 Added isConnected and userLocale
	 * @since 2.5.0 : 1. Is now public, marked private.
	 *                2. Now uses TSF's script loader.
	 * @access private
	 * @internal
	 *
	 * @param string $scripts Static class name: The_SEO_Framework\Builders\Scripts
	 */
	public function _register_default_scripts( $scripts ) {
		$tsfem = \tsfem();

		// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned -- it's alligned well enough.
		$scripts::register( [
			[
				'id'   => 'tsfem-inpost',
				'type' => 'js',
				'autoload' => false,
				'name' => 'tsfem-inpost',
				'base' => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'  => TSF_EXTENSION_MANAGER_VERSION,
				'deps' => [ 'jquery', 'tsf', 'tsf-tt' ],
				'l10n' => [
					'name' => 'tsfem_inpostL10n',
					'data' => [
						'post_ID'     => (int) $GLOBALS['post']->ID,
						'nonce'       => \current_user_can( 'edit_post', $GLOBALS['post']->ID ) ? \wp_create_nonce( static::JS_NONCE_ACTION ) : false,
						'isPremium'   => $tsfem->is_premium_user(),
						'isConnected' => $tsfem->is_connected_user(),
						'locale'      => \get_locale(),
						'userLocale'  => \function_exists( '\\get_user_locale' ) ? \get_user_locale() : \get_locale(),
						'debug'       => (bool) WP_DEBUG,
						'rtl'         => (bool) \is_rtl(),
						'i18n'        => [
							'InvalidResponse' => \esc_html__( 'Received invalid AJAX response.', 'the-seo-framework-extension-manager' ),
							'UnknownError'    => \esc_html__( 'An unknown error occurred.', 'the-seo-framework-extension-manager' ),
							'TimeoutError'    => \esc_html__( 'Timeout: Server took too long to respond.', 'the-seo-framework-extension-manager' ),
							'BadRequest'      => \esc_html__( "Bad request: The server can't handle the request.", 'the-seo-framework-extension-manager' ),
							'FatalError'      => \esc_html__( 'A fatal error occurred on the server.', 'the-seo-framework-extension-manager' ),
							'ParseError'      => \esc_html__( 'A parsing error occurred in your browser.', 'the-seo-framework-extension-manager' ),
						],
					],
				],
				'tmpl' => [
					'file' => $tsfem->get_template_location( 'inpostnotice' ),
				],
			],
			[
				'id'   => 'tsfem-inpost',
				'type' => 'css',
				'autoload' => false,
				'name' => 'tsfem-inpost',
				'base' => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/css/',
				'ver'  => TSF_EXTENSION_MANAGER_VERSION,
				'deps' => [ 'tsf', 'tsf-tt' ],
			],
		] );
		// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned -- it's alligned well enough.

		$scripts::register( [
			[
				'id'       => 'tsfem-worker',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => false,
				'name'     => 'tsfem-worker',
				'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'      => TSF_EXTENSION_MANAGER_VERSION,
			],
		] );
	}

	/**
	 * Determines if the current user can edit the post.
	 *
	 * @since 1.5.0
	 *
	 * @param int|null $post_id The post ID to test.
	 * @return bool True if user has acces. False otherwise.
	 */
	public static function current_user_can_edit_post( $post_id = null ) {
		$post_id = $post_id ?? $GLOBALS['post']->ID;
		return \current_user_can( 'edit_post', $post_id );
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
		static::current_user_can_edit_post()
			and \wp_nonce_field( static::NONCE_ACTION, static::NONCE_NAME, false );
	}

	/**
	 * Verifies nonce on POST and writes the class $save_access_state variable.
	 *
	 * @since 1.5.0
	 * @since 2.0.2 Added \wp_unslash to POST data.
	 * @since 2.1.0 Now tests for post revision.
	 * @access private
	 *
	 * @param integer  $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void Early when nonce or user can't be verified.
	 */
	public static function _verify_nonce( $post_id, $post ) {

		if ( ( empty( $_POST[ static::NONCE_NAME ] ) ) // Input var OK.
		|| ( ! \wp_verify_nonce( $_POST[ static::NONCE_NAME ], static::NONCE_ACTION ) ) // Input var, sanitization OK.
		|| ( ! \current_user_can( 'edit_post', $post->ID ) )
		) return;

		static::$save_access_state = TSFEM_INPOST_IS_SECURE;

		if ( ! \wp_is_post_autosave( $post ) )
			static::$save_access_state |= TSFEM_INPOST_NO_AUTOSAVE;
		if ( ! \wp_doing_ajax() )
			static::$save_access_state |= TSFEM_INPOST_NO_AJAX;
		if ( ! \wp_doing_cron() )
			static::$save_access_state |= TSFEM_INPOST_NO_CRON;
		if ( ! \wp_is_post_revision( $post ) )
			static::$save_access_state |= TSFEM_INPOST_NO_REVISION;

		$data = ! empty( $_POST[ static::META_PREFIX ] ) ? \wp_unslash( $_POST[ static::META_PREFIX ] ) : null; // Input var, sanitization OK.

		/**
		 * Runs after nonce and possibly interfering actions have been verified.
		 *
		 * @since 1.5.0
		 *
		 * @param \WP_Post      $post              The post object.
		 * @param array|null    $data              The meta data, set through `pm_index` keys.
		 * @param int (bitwise) $save_access_state The state the save is in.
		 *    Any combination of : {
		 *      1  = 00001 : Passed nonce and capability checks. Always set at this point.
		 *      2  = 00010 : Not doing autosave.
		 *      4  = 00100 : Not doing AJAX.
		 *      8  = 01000 : Not doing WP Cron.
		 *      16 = 10000 : Not creating a post revision.
		 *      |
		 *      31 = 11111 : Post is manually and securely published or updated.
		 *    }
		 */
		\do_action_ref_array( 'tsfem_inpostgui_verified_nonce', [ $post, $data, static::$save_access_state ] );
	}

	/**
	 * Determines whether POST data can be safely written.
	 *
	 * @since 1.5.0
	 * @since 2.1.0 Now tests for post revision.
	 *
	 * @return bool True if user verification passed, and not doing autosave, cron, or ajax.
	 */
	public static function can_safely_write() {
		return static::is_state_safe( static::$save_access_state );
	}

	/**
	 * Determines whether input state is safe.
	 *
	 * @since 2.1.0
	 *
	 * @param int $state (bitwise) The state to test.
	 * @return bool True if user verification passed, and not doing autosave, cron, or ajax.
	 */
	public static function is_state_safe( $state ) {
		return (bool) ( $state & (
			TSFEM_INPOST_IS_SECURE | TSFEM_INPOST_NO_AUTOSAVE | TSFEM_INPOST_NO_AJAX | TSFEM_INPOST_NO_CRON | TSFEM_INPOST_NO_REVISION
		) );
	}

	/**
	 * Adds registered active tabs to The SEO Framework inpost metabox.
	 *
	 * @since 1.5.0
	 * @since 2.1.0 Removed second parameter.
	 * @access private
	 *
	 * @param array $tabs The registered tabs.
	 * @return array $tabs The SEO Framework's tabs.
	 */
	public function _load_tabs( $tabs ) {

		$registered_tabs = static::$tabs;
		$active_tab_keys = static::$active_tab_keys;

		foreach ( $registered_tabs as $index => $args ) :
			empty( $active_tab_keys[ $index ] ) or
				$tabs[ $index ] = $args;
		endforeach;

		return $tabs;
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
			// Sort by the priority indexes. Priority values get lost in this process.
			sort( $views );

			foreach ( $views as $view )
				$this->output_view( $view[0], $view[1] );
		}
	}

	/**
	 * Outputs tab or template view, whilst trying to prevent 3rd party interference on views.
	 *
	 * There's a secret key generated on each tab load. This key can be accessed
	 * in the view through `$_secret`, and be sent back to this class.
	 *
	 * @see \TSF_Extension_Manager\InpostGUI::verify( $secret )
	 *
	 * @since 1.5.0
	 * @since 2.1.0 Enabled entropy to prevent system sleep.
	 * @uses static::$include_secret
	 *
	 * @param string $file The file location.
	 * @param array  $args The registered view arguments.
	 */
	private function output_view( $file, $args ) {

		foreach ( $args as $_key => $_val )
			$$_key = $_val;

		unset( $_key, $_val, $args );

		// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Includes.
		static::$include_secret = $_secret = mt_rand() . uniqid( '', true );
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
		return isset( $secret ) && static::$include_secret === $secret;
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
	public static function activate_tab( $tab ) {
		static::$active_tab_keys[ $tab ] = true;
	}

	/**
	 * Registers view for tab.
	 *
	 * @since 1.5.0
	 * @see static::activate_tab();
	 * @uses static::$views
	 *
	 * @param string    $file The file to include.
	 * @param array     $args The arguments to pass to the file. Each array index is
	 *                        converted to a respectively named variable.
	 * @param string    $tab  The tab the view is outputted in.
	 * @param int|float $priority The priority of the view. A lower value results in an earlier output.
	 */
	public static function register_view( $file, $args = [], $tab = 'advanced', $priority = 10 ) {
		// Prevent excessive static calls and write directly to var.
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
}

$_load_inpostgui_class();
