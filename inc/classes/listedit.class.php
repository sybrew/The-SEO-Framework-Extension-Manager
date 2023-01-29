<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2020-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Instead of affixing/prefixing everything with bulk/quick, I should've abstracted this class... oh well.
 *
 * @see EOF. Because of the autoloader and trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_listedit_class = function() {
	new ListEdit(); // phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape -- correct scope.
};

/**
 * Registers and outputs inpost GUI elements. Auto-invokes everything the moment
 * this file is required.
 *
 * The SEO Framework 4.0.5 or later is required. All earlier versions will let this
 * remain dormant.
 *
 * @since 2.5.0
 * @requires TSF 4.0.5||^
 * @access private
 * @uses trait TSF_Extension_Manager\Construct_Master_Once_Final_Interface
 *       This means you shouldn't invoke new yourself.
 * @see package TSF_Extension_Manager\Traits\Overload
 *
 * @final Can't be extended.
 */
final class ListEdit {
	use Construct_Master_Once_Final_Interface;

	/**
	 * @since 2.5.0
	 * @var string META_PREFIX_QUICK The meta prefix to handle POST data for quick-edit.
	 */
	const META_PREFIX_QUICK = 'tsfem-pm-quick';

	/**
	 * @since 2.5.0
	 * @var string META_PREFIX_QUICK The meta prefix to handle POST data for quick-edit.
	 */
	const META_PREFIX_BULK = 'tsfem-pm-bulk';

	/**
	 * @since 2.5.0
	 * @var string The inclusion secret generated on section load.
	 */
	private static $include_secret;

	/**
	 * @since 2.5.0
	 * @var array The registered quick-edit sections.
	 */
	private static $quick_sections = [];

	/**
	 * @since 2.5.0
	 * @var array The registered bulk-edit sections.
	 */
	private static $bulk_sections = [];

	/**
	 * @since 2.5.0
	 * @var array The activate section keys of static::$quick_sections.
	 */
	private static $active_quick_section_keys = [];

	/**
	 * @since 2.5.0
	 * @var array The activate section keys of static::$bulk_sections.
	 */
	private static $active_bulk_section_keys = [];

	/**
	 * @since 2.5.0
	 * @var array The registered quick-edit view files for the sections.
	 */
	private static $quick_views = [];

	/**
	 * @since 2.5.0
	 * @var array The registered bulk-edit view files for the sections.
	 */
	private static $bulk_views = [];

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 2.5.0
	 */
	public static function prepare() {}

	/**
	 * Constructor. Loads all appropriate actions asynchronously.
	 *
	 * @TODO consider running "post type supported" calls, instead of relying on failsafes in TSF.
	 * @see \tsf()->_init_admin_scripts(); this requires TSF 4.0+ dependency, however.
	 */
	private function construct() {

		$this->register_quick_sections();
		$this->register_bulk_sections();

		// Scripts.
		\add_action( 'admin_enqueue_scripts', [ $this, '_prepare_admin_scripts' ], 1 );

		// Saving.
		\add_action( 'save_post', [ static::class, '_verify_nonce_post' ], 1, 2 );
		// phpcs:ignore -- No extension supports this.
		// \add_action( 'edit_term', [ static::class, '_verify_nonce_term' ], 1, 3 );

		// Output.
		\add_action( 'the_seo_framework_after_quick_edit', [ $this, '_load_quick_sections' ], 10, 2 );
		\add_action( 'the_seo_framework_after_bulk_edit', [ $this, '_load_bulk_sections' ], 10, 2 );
	}

	/**
	 * Registers available sections for quick-edit.
	 *
	 * @since 2.5.0
	 * @uses static::$quick_sections The registered sections that are written.
	 */
	private function register_quick_sections() {
		static::$quick_sections = [
			'structure' => [
				'name'     => \__( 'Structure SEO Settings', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_quick_section_content' ],
				'args'     => [ 'structure' ],
			],
			'audit'     => [
				'name'     => \__( 'Audit SEO Settings', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_quick_section_content' ],
				'args'     => [ 'audit' ],
			],
			'advanced'  => [
				'name'     => \__( 'Advanced SEO Settings', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_quick_section_content' ],
				'args'     => [ 'advanced' ],
			],
		];
	}

	/**
	 * Registers available sections for bulk-edit.
	 *
	 * @since 2.5.0
	 * @uses static::$bulk_sections The registered sections that are written.
	 */
	private function register_bulk_sections() {
		static::$bulk_sections = [
			'structure' => [
				'name'     => \__( 'Structure SEO Settings', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_bulk_section_content' ],
				'args'     => [ 'structure' ],
			],
			'audit'     => [
				'name'     => \__( 'Audit SEO Settings', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_bulk_section_content' ],
				'args'     => [ 'audit' ],
			],
			'advanced'  => [
				'name'     => \__( 'Advanced SEO Settings', 'the-seo-framework-extension-manager' ),
				'callback' => [ $this, '_output_bulk_section_content' ],
				'args'     => [ 'advanced' ],
			],
		];
	}

	/**
	 * Prepares scripts for output on post edit screens.
	 *
	 * @since 2.5.0
	 *
	 * @param string $hook The current admin hook.
	 */
	public function _prepare_admin_scripts( $hook ) {

		if ( ! \in_array( $hook, [ 'edit.php', 'edit-tags.php' ], true ) )
			return;

		$this->register_default_scripts();

		/**
		 * Does action 'tsfem_listedit_enqueue_scripts'
		 *
		 * Use this hook to enqueue scripts on the post edit screens.
		 *
		 * @since 2.5.0
		 * @param string $class The static class caller name.
		 * @param string $hook  The current page hook.
		 */
		\do_action_ref_array( 'tsfem_listedit_enqueue_scripts', [ static::class, $hook ] );
	}

	/**
	 * Registers default inpost scripts.
	 *
	 * @since 2.5.0
	 * @since 2.0.0 Added isConnected and userLocale
	 */
	private function register_default_scripts() {
		\The_SEO_Framework\Builders\Scripts::register(
			[
				'id'       => 'tsfem-listedit',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'tsf', 'tsf-tt', 'tsf-le' ],
				'autoload' => true,
				'name'     => 'tsfem-listedit',
				'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'      => TSF_EXTENSION_MANAGER_VERSION,
				'l10n'     => [
					'name' => 'tsfem_listeditL10n',
					'data' => [],
				],
			]
		);
	}

	/**
	 * Verifies nonces on POST for posts and evokes secured hooks.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @param integer  $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void Early when nonce or user can't be verified.
	 */
	public static function _verify_nonce_post( $post_id, $post ) {

		if ( empty( $_REQUEST[ static::META_PREFIX_BULK ] ) && empty( $_POST[ static::META_PREFIX_QUICK ] ) ) return;

		$post = \get_post( $post );
		if ( empty( $post->ID ) ) return;

		// Check again against ambiguous injection...
		// Note, however: function wp_ajax_inline_save()/bulk_edit_posts() already performs all these checks for us before firing this callback's action.
		if ( ! \current_user_can( 'edit_post', $post->ID ) ) return;

		if ( isset( $_POST[ static::META_PREFIX_QUICK ] ) ) {
			if ( ! \check_ajax_referer( 'inlineeditnonce', '_inline_edit', false ) ) return;

			/**
			 * Runs after nonce and possibly interfering actions have been verified.
			 *
			 * @since 2.5.0
			 *
			 * @param \WP_Post   $post The post object.
			 * @param array|null $data The meta data, set through `pm_index` keys.
			 */
			\do_action_ref_array( 'tsfem_quick_edit_verified_nonce', [ $post, \wp_unslash( $_POST[ static::META_PREFIX_QUICK ] ) ] );
		} else {
			static $verified_referer = false;
			// Memoize the referer check--if it passes (and doesn't exit/die PHP), we're good to execute subsequently.
			if ( ! $verified_referer ) {
				\check_admin_referer( 'bulk-posts' );
				$verified_referer = true;
			}

			// Memoize 'sanitized' bulk data, since that won't change over the loop.
			static $data_bulk;
			if ( ! isset( $data_bulk ) )
				$data_bulk = \wp_unslash( $_REQUEST[ static::META_PREFIX_BULK ] );

			/**
			 * Runs after nonce and possibly interfering actions have been verified.
			 *
			 * @since 2.5.0
			 *
			 * @param \WP_Post   $post The post object.
			 * @param array|null $data The meta data, set through `pm_index` keys.
			 */
			\do_action_ref_array( 'tsfem_bulk_edit_verified_nonce', [ $post, $data_bulk ] );
		}
	}

	/**
	 * Verifies nonces on POST for terms and evokes secured hooks.
	 *
	 * @since 2.5.0
	 * @access private
	 * @ignore unused
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public static function _verify_nonce_term( $term_id, $tt_id, $taxonomy ) { // phpcs:ignore
		// phpcs:ignore
		return;
	}

	/**
	 * Outputs registered active sections to The SEO Framework quick-edit fields.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @param string $post_type The current post type.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	public function _load_quick_sections( $post_type, $taxonomy ) {

		$registered_sections = static::$quick_sections;
		$active_section_keys = static::$active_quick_section_keys;

		$sections = [];

		foreach ( $registered_sections as $index => $args ) :
			empty( $active_section_keys[ $index ] ) or
				$sections[ $index ] = $args;
		endforeach;

		// TODO We need to differentiate between post and term!
		$this->output_view( \tsfem()->get_view_location( 'listedit/quick' ), compact( 'sections', 'post_type', 'taxonomy' ) );
	}

	/**
	 * Outputs registered active sections to The SEO Framework bulk-edit fields.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @param string $post_type The current post type.
	 * @param string $taxonomy  The current taxonomy type (if any).
	 */
	public function _load_bulk_sections( $post_type, $taxonomy ) {

		$registered_sections = static::$bulk_sections;
		$active_section_keys = static::$active_bulk_section_keys;

		$sections = [];

		foreach ( $registered_sections as $index => $args ) :
			empty( $active_section_keys[ $index ] ) or
				$sections[ $index ] = $args;
		endforeach;

		$this->output_view( \tsfem()->get_view_location( 'listedit/bulk' ), compact( 'sections', 'post_type', 'taxonomy' ) );
	}

	/**
	 * Output sections content through loading registered quick-edit section views
	 * in order of priority or registration time.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @param string $section The section that invoked this method call.
	 */
	public function _output_quick_section_content( $section ) {

		if ( isset( static::$quick_views[ $section ] ) ) {
			$views = static::$quick_views[ $section ];

			// Sort by the priority indexes. Priority values get lost in this process.
			sort( $views );

			foreach ( $views as $view )
				$this->output_view( $view[0], $view[1] );
		}
	}

	/**
	 * Output sections content through loading registered bulk-edit section views
	 * in order of priority or registration time.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @param string $section The section that invoked this method call.
	 */
	public function _output_bulk_section_content( $section ) {

		if ( isset( static::$bulk_views[ $section ] ) ) {
			$views = static::$bulk_views[ $section ];
			// Sort by the priority indexes. Priority values get lost in this process.
			sort( $views );

			foreach ( $views as $view )
				$this->output_view( $view[0], $view[1] );
		}
	}

	/**
	 * Outputs section or template view, whilst trying to prevent 3rd party interference on views.
	 *
	 * There's a secret key generated on each section load. This key can be accessed
	 * in the view through `$_secret`, and be sent back to this class.
	 *
	 * @see \TSF_Extension_Manager\InpostGUI::verify( $secret )
	 *
	 * @since 2.5.0
	 * @uses static::$include_secret
	 *
	 * @param string $file The file location.
	 * @param array  $args The registered view arguments.
	 */
	private function output_view( $file, $args ) {

		foreach ( $args as $_key => $_val )
			$$_key = $_val;

		unset( $_key, $_val, $args );

		// Prevent private includes hijacking.
		static::$include_secret = $_secret = mt_rand() . uniqid( '', true ); // phpcs:ignore, VariableAnalysis.CodeAnalysis -- includes
		include $file;
		static::$include_secret = null;
	}

	/**
	 * Verifies view inclusion secret.
	 *
	 * @since 2.5.0
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
	 * Activates registered section for quick-edit display.
	 *
	 * Structure: Rich/structured data controls.
	 * Audit:     Monitoring, reviewing content, analytics, etc.
	 * Advanced:  Everything else.
	 *
	 * @since 2.5.0
	 * @see static::register_sections()
	 * @uses static::$active_quick_section_keys
	 *
	 * @param string $section The section to activate.
	 *               Either 'structure', 'audit' or 'advanced'.
	 */
	public static function activate_quick_section( $section ) {
		static::$active_quick_section_keys[ $section ] = true;
	}

	/**
	 * Activates registered section for bulk-edit display.
	 *
	 * Structure: Rich/structured data controls.
	 * Audit:     Monitoring, reviewing content, analytics, etc.
	 * Advanced:  Everything else.
	 *
	 * @since 2.5.0
	 * @see static::register_sections()
	 * @uses static::$active_bulk_section_keys
	 *
	 * @param string $section The section to activate.
	 *               Either 'structure', 'audit' or 'advanced'.
	 */
	public static function activate_bulk_section( $section ) {
		static::$active_bulk_section_keys[ $section ] = true;
	}

	/**
	 * Registers view for quick edit section.
	 *
	 * @since 2.5.0
	 * @see static::activate_quick_section();
	 * @uses static::$quick_views
	 *
	 * @param string    $file The file to include.
	 * @param array     $args The arguments to pass to the file. Each array index is
	 *                        converted to a respectively named variable.
	 * @param string    $section  The section the view is outputted in.
	 * @param int|float $priority The priority of the view. A lower value results in an earlier output.
	 */
	public static function register_quick_view( $file, $args = [], $section = 'advanced', $priority = 10 ) {
		// Prevent excessive static calls and write directly to var.
		$_views =& static::$quick_views;

		if ( ! isset( $_views[ $section ] ) )
			$_views[ $section ] = [];

		if ( ! isset( $_views[ $section ][ $priority ] ) )
			$_views[ $section ][ $priority ] = [];

		$_views[ $section ][ $priority ] += [ $file, $args ];
	}

	/**
	 * Registers view for bulk edit section.
	 *
	 * @since 2.5.0
	 * @see static::activate_bulk_section();
	 * @uses static::$bulk_views
	 *
	 * @param string    $file The file to include.
	 * @param array     $args The arguments to pass to the file. Each array index is
	 *                        converted to a respectively named variable.
	 * @param string    $section  The section the view is outputted in.
	 * @param int|float $priority The priority of the view. A lower value results in an earlier output.
	 */
	public static function register_bulk_view( $file, $args = [], $section = 'advanced', $priority = 10 ) {
		// Prevent excessive static calls and write directly to var.
		$_views =& static::$bulk_views;

		if ( ! isset( $_views[ $section ] ) )
			$_views[ $section ] = [];

		if ( ! isset( $_views[ $section ][ $priority ] ) )
			$_views[ $section ][ $priority ] = [];

		$_views[ $section ][ $priority ] += [ $file, $args ];
	}

	/**
	 * Builds option key index for quick-edit, which can later be retrieved in POST.
	 *
	 * @since 2.5.0
	 * @see trait \TSF_Extension_Manager\Extension_Post_Meta
	 * @see static::_verify_nonce_{post|term}();
	 *
	 * @param string $option The option.
	 * @param string $index  The post/term meta index (pm_index) (NOTE: term index is unavailable).
	 * @return string The option prefix.
	 */
	public static function get_quick_option_key( $option, $index ) {
		return sprintf( '%s[%s][%s]', static::META_PREFIX_QUICK, $index, $option );
	}

	/**
	 * Builds option key index for bulk-edit, which can later be retrieved in POST.
	 *
	 * @since 2.5.0
	 * @see trait \TSF_Extension_Manager\Extension_Post_Meta
	 * @see static::_verify_nonce_{post|term}();
	 *
	 * @param string $option The option.
	 * @param string $index  The post/term meta index (pm_index) (NOTE: term index is unavailable).
	 * @return string The option prefix.
	 */
	public static function get_bulk_option_key( $option, $index ) {
		return sprintf( '%s[%s][%s]', static::META_PREFIX_BULK, $index, $option );
	}
}

$_load_listedit_class();
