<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds TSFEM admin-page User Interface functionality.
 *
 * @since 1.0.0
 * @access private
 */
trait UI {

	/**
	 * The User Interface hook where all scripts should be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var string The UI loader hook.
	 */
	private $ui_hook;

	/**
	 * The UI wrap type.
	 *
	 * @since 2.0.1
	 *
	 * @var string The UI wrap type. Accepts 'column' and 'row'.
	 */
	private $wrap_type = 'column';

	/**
	 * Initializes the UI traits.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Now is private, instead of protected.
	 */
	final private function init_ui() {

		$this->ui_hook or \the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to specify property <code>ui_hook</code>' );

		//* Remove WordPress footer strings.
		\add_action( 'admin_footer_text', '__return_empty_string', PHP_INT_MAX );
		\add_action( 'update_footer', '__return_empty_string', PHP_INT_MAX );

		//* Add body class.
		\add_action( 'admin_body_class', [ $this, '_add_admin_body_class' ], 999, 1 );

		$this->enqueue_admin_scripts();
	}

	/**
	 * Outputs default UI wrap in logical order.
	 *
	 * @since 1.5.0
	 * @since 2.2.0 Moved notice wrap into header_wrap.
	 *
	 * @param string $type The type of main content. Accepts 'panes' and 'connect'.
	 */
	final protected function ui_wrap( $type = 'panes' ) {
		\add_action( 'tsfem_page', [ $this, 'header_wrap' ], 25 );
		\add_action( 'tsfem_page', [ $this, $type . '_wrap' ], 100 );
		\add_action( 'tsfem_page', [ $this, 'footer_wrap' ], 200 );

		\do_action( 'tsfem_before_page' );
		$this->page_wrap();
		\do_action( 'tsfem_after_page' );
	}

	/**
	 * Outputs page wrap and does callback.
	 *
	 * @since 1.5.0
	 */
	final protected function page_wrap() {
		echo '<div class="wrap tsfem">';
		\do_action( 'tsfem_page' );
		echo '</div>';
	}

	/**
	 * Outputs header wrap and does callback.
	 *
	 * @since 1.5.0
	 */
	final public function header_wrap() {
		echo '<div class="tsfem-sticky-top">';
			echo '<section class="tsfem-top-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-space">';
				\do_action( 'tsfem_header' );
			echo '</section>';
			$this->notice_wrap();
		echo '</div>';
	}

	/**
	 * Outputs notice wrap and does callback.
	 *
	 * Must be called directly after header wrap.
	 *
	 * @since 1.3.0
	 */
	final public function notice_wrap() {
		echo '<aside class="tsfem-notice-wrap">';
		\do_action( 'tsfem_notices' );
		echo '</aside>';
	}

	/**
	 * Outputs panes wrap and does callback.
	 *
	 * @since 1.5.0
	 * @since 2.0.1 Now listens to $this->wrap_type
	 * @since 2.2.0 Is no longer a tsfem-flex-item.
	 */
	final public function panes_wrap() {
		printf(
			'<main class="tsfem-panes-wrap tsfem-panes-wrap-%s">',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped
			in_array( $this->wrap_type, [ 'column', 'row' ], true ) ? $this->wrap_type : 'column'
		);
		\do_action( 'tsfem_content' );
		echo '</main>';
	}

	/**
	 * Outputs panes wrap and does callback.
	 *
	 * @since 1.5.0
	 */
	final public function connect_wrap() {
		echo '<main class=tsfem-connect-wrap>';
		\do_action( 'tsfem_content' );
		echo '</main>';
	}

	/**
	 * Performs footer actions.
	 *
	 * Must be called in footer.
	 *
	 * @since 1.5.0
	 */
	final public function footer_wrap() {
		echo '<footer class="tsfem-footer-wrap tsfem-disable-cursor">';
		\do_action( 'tsfem_footer' );
		echo '</footer>';
	}

	/**
	 * Enqueues styles and scripts in the admin area on the extension manager page.
	 *
	 * @since 2.0.2
	 */
	final protected function enqueue_admin_scripts() {

		\The_SEO_Framework\Builders\Scripts::prepare();

		//* Enqueue default scripts.
		\add_action( 'tsfem_before_enqueue_scripts', [ $this, '_register_default_scripts' ] );

		//* Enqueue early styles & scripts.
		\add_action( 'admin_enqueue_scripts', [ $this, '_load_admin_scripts' ], 0 );

		//* Enqueue late initialized styles & scripts.
		\add_action( 'admin_footer', [ $this, '_load_admin_scripts' ], 0 );
	}

	/**
	 * Registers admin scripts.
	 *
	 * @since 2.0.2
	 * @access private
	 * @internal
	 */
	final public function _load_admin_scripts() {
		/**
		 * @since 2.0.2
		 * @param string $scripts The scripts builder class name.
		 */
		\do_action( 'tsfem_before_enqueue_scripts', \The_SEO_Framework\Builders\Scripts::class );
	}

	/**
	 * Registers default TSFEM admin scripts.
	 * Also registers TSF scripts, for TT (tooltip) support.
	 *
	 * @since 2.0.2
	 * @access private
	 * @internal
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	final public function _register_default_scripts( $scripts ) {

		if ( has_run( __METHOD__ ) ) return;

		\the_seo_framework()->init_admin_scripts();

		$scripts::register( [
			[
				'id'       => 'tsfem',
				'type'     => 'css',
				'deps'     => [ 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => true,
				'name'     => 'tsfem',
				'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/css/',
				'ver'      => TSF_EXTENSION_MANAGER_VERSION,
				'inline'   => null,
			],
			[
				'id'       => 'tsfem',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'wp-util', 'tsf-tt' ],
				'autoload' => true,
				'name'     => 'tsfem',
				'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'      => TSF_EXTENSION_MANAGER_VERSION,
				'l10n'     => [
					'name' => 'tsfemL10n',
					'data' => [
						'nonce' => \wp_create_nonce( 'tsfem-ajax-nonce' ),
						'debug' => (bool) WP_DEBUG,
						'rtl'   => (bool) \is_rtl(),
						'i18n'  => [
							'Activate'        => \esc_html__( 'Activate', 'the-seo-framework-extension-manager' ),
							'Deactivate'      => \esc_html__( 'Deactivate', 'the-seo-framework-extension-manager' ),
							'InvalidResponse' => \esc_html__( 'Received invalid AJAX response.', 'the-seo-framework-extension-manager' ),
							'UnknownError'    => \esc_html__( 'An unknown error occurred.', 'the-seo-framework-extension-manager' ),
							'TimeoutError'    => \esc_html__( 'Timeout: Server took too long to respond.', 'the-seo-framework-extension-manager' ),
							'BadRequest'      => \esc_html__( "Bad request: The server can't handle the request.", 'the-seo-framework-extension-manager' ),
							'FatalError'      => \esc_html__( 'A fatal error occurred on the server.', 'the-seo-framework-extension-manager' ),
							'ParseError'      => \esc_html__( 'A parsing error occurred in your browser.', 'the-seo-framework-extension-manager' ),
						],
					],
				],
				'tmpl'     => [
					'file' => \tsf_extension_manager()->get_template_location( 'fbtopnotice' ),
				],
			],
		] );
	}

	/**
	 * Registers form scripts.
	 *
	 * @since 1.3.0
	 * @since 2.0.0 Now uses \TSF_Extension_Manager\can_do_settings() for nonce creation.
	 * @since 2.0.2 : 1. Now uses TSF's Scripts module.
	 *                2. Now returns void
	 * @access protected
	 * @internal
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	final protected function register_form_scripts( $scripts ) {

		if ( has_run( __METHOD__ ) ) return;

		$scripts::register( [
			[
				'id'       => 'tsfem-form',
				'type'     => 'css',
				'deps'     => [ 'tsfem', 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => true,
				'name'     => 'tsfem-form',
				'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/css/',
				'ver'      => TSF_EXTENSION_MANAGER_VERSION,
				'inline'   => null,
			],
			[
				'id'       => 'tsfem-form',
				'type'     => 'js',
				'deps'     => [ 'tsfem', 'tsf-tt' ],
				'autoload' => true,
				'name'     => 'tsfem-form',
				'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'      => TSF_EXTENSION_MANAGER_VERSION,
				'l10n'     => [
					'name' => 'tsfemFormL10n',
					'data' => [
						'nonce'  => \TSF_Extension_Manager\can_do_settings() ? \wp_create_nonce( 'tsfem-form-nonce' ) : '',
						'callee' => get_class( $this ), //! Don't use __CLASS__, we require the core instance.
						'i18n'   => [
							//* TODO categorize

							// Validation.
							'requiredSelectAny' => \esc_html__( 'Please select any of the fields to proceed.', 'the-seo-framework-extension-manager' ),

							// General.
							'proceed' => \esc_html__( 'Proceed', 'the-seo-framework-extension-manager' ),
							'select'  => \esc_html__( 'Select', 'the-seo-framework-extension-manager' ),
							'cancel'  => \esc_html__( 'Cancel', 'the-seo-framework-extension-manager' ),
							'dismiss' => \esc_html__( 'Dismiss', 'the-seo-framework-extension-manager' ),

							// Iteration.
							'performanceWarning' => \esc_html__( 'Performance warning', 'the-seo-framework-extension-manager' ),
							'itLargeConfirm'     => \esc_html__( "You're about to load a large number of elements. This might degredate browser performance.", 'the-seo-framework-extension-manager' ),
							'itHugeConfirm'      => \esc_html__( "You're about to load a huge number of elements. This might crash your browser.", 'the-seo-framework-extension-manager' ),
							'aysProceed'         => \esc_html__( 'Are you sure you want to proceed?', 'the-seo-framework-extension-manager' ),

							// Collapse
							'collapseValidate' => \esc_html__( 'Please fix the required fields before saving.', 'the-seo-framework-extension-manager' ),

							// Geo
							'validate'           => \esc_html__( 'Validate', 'the-seo-framework-extension-manager' ),
							'selectAddressTitle' => \esc_html__( 'Select address', 'the-seo-framework-extension-manager' ),
							'selectAddressText'  => \esc_html__( 'Select an address below.', 'the-seo-framework-extension-manager' ),
							'reverseGeoWarning'  => \esc_html__( 'Validation will be done only using Latitude and Longitude.', 'the-seo-framework-extension-manager' ),
						],
					],
				],
				// Inherits from 'tsfem'
				// 'tmpl'     => [
				// 	'file' => \tsf_extension_manager()->get_template_location( 'fbtopnotice' ),
				// ],
			],
		] );
	}

	/**
	 * Registers Media CSS and JS scripts.
	 * Also enqueues WordPress's media scripts.
	 *
	 * @since 1.3.0
	 * @since 2.0.2 : 1. Now uses TSF's Scripts module.
	 *                2. Now returns void.
	 * @access protected
	 * @internal
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	final protected function register_media_scripts( $scripts ) {

		if ( has_run( __METHOD__ ) ) return;

		// TODO use TSF v4.0 media handler, instead.
		$scripts::register( [
			[
				'id'       => 'tsfem-media',
				'type'     => 'js',
				'deps'     => [ 'media-editor' ],
				'autoload' => true,
				'name'     => 'tsfem-media',
				'base'     => TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'      => TSF_EXTENSION_MANAGER_VERSION,
				'l10n'     => [
					'name' => 'tsfemMediaData',
					'data' => [
						'nonce'          => \current_user_can( 'upload_files' ) ? \wp_create_nonce( 'tsfem-media-nonce' ) : '',
						'imgSelect'      => \esc_attr__( 'Select Image', 'the-seo-framework-extension-manager' ),
						'imgSelectTitle' => \esc_attr_x( 'Select image', 'Button hover', 'the-seo-framework-extension-manager' ),
						'imgChange'      => \esc_attr__( 'Change Image', 'the-seo-framework-extension-manager' ),
						'imgRemove'      => \esc_attr__( 'Remove Image', 'the-seo-framework-extension-manager' ),
						'imgRemoveTitle' => \esc_attr__( 'Remove selected image', 'the-seo-framework-extension-manager' ),
						'imgFrameTitle'  => \esc_attr_x( 'Select Image', 'Frame title', 'the-seo-framework-extension-manager' ),
						'imgFrameButton' => \esc_attr__( 'Use this image', 'the-seo-framework-extension-manager' ),
						// 'mediaEnqueued'  => \wp_style_is( 'media', 'enqueued' ),
					],
				],
			],
		] );

		\wp_enqueue_media();
	}

	/**
	 * Adds an extra body class on the extensions manager page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $classes The current body classes.
	 * @return string The expanded body classes.
	 */
	final public function _add_admin_body_class( $classes = '' ) {
		return trim( $classes ) . ' tsfem ';
	}
}
