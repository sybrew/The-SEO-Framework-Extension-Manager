<?php
/**
 * @package TSF_Extension_Manager\Traits
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * @since 1.0.0
	 * @var string The UI loader hook.
	 */
	private $ui_hook;

	/**
	 * @since 2.0.1
	 * @var string The UI wrap type. Accepts 'column' and 'row'.
	 */
	private $wrap_type = 'column';

	/**
	 * Initializes the UI traits.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Now is private, instead of protected.
	 */
	private function init_ui() {

		$this->ui_hook or \tsf()->_doing_it_wrong( __METHOD__, 'You need to specify property <code>ui_hook</code>' );

		// Remove WordPress footer strings.
		\add_filter( 'admin_footer_text', '__return_empty_string', \PHP_INT_MAX );
		\add_filter( 'update_footer', '__return_empty_string', \PHP_INT_MAX );

		// Prevent annoying nags (they're hidden by CSS anyway).
		\remove_action( 'admin_notices', 'update_nag', 3 );
		\remove_action( 'admin_notices', 'maintenance_nag', 10 );

		// Add body class.
		\add_filter( 'admin_body_class', [ $this, '_add_admin_body_class' ], 999, 1 );

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
		\add_action( 'tsfem_page', [ $this, "{$type}_header_wrap" ], 25 );
		\add_action( 'tsfem_page', [ $this, "{$type}_page_wrap" ], 100 );
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
		echo '<div id=tsfem-page-wrap class=wrap>';
		\do_action( 'tsfem_page' );
		echo '</div>';
	}

	/**
	 * Outputs header wrap and does callback.
	 *
	 * @since 2.6.0
	 */
	final public function panes_header_wrap() {
		echo '<div id=tsfem-sticky-top>';
			echo '<div id=tsfem-top-super-wrap><section id=tsfem-top-wrap class="tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-space">';
				\do_action( 'tsfem_header' );
			echo '</section></div>';
			$this->notice_wrap();
		echo '</div>';
	}

	/**
	 * Outputs header wrap and does callback.
	 *
	 * @since 2.6.0
	 */
	final public function connect_header_wrap() {
		echo '<div id=tsfem-sticky-top>';
			echo '<div id=tsfem-top-super-wrap><section id=tsfem-top-wrap class="tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-space connect-top-wrap">';
				\do_action( 'tsfem_header' );
			echo '</section></div>';
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
		echo '<aside id=tsfem-notice-wrap>';
		echo '<hr class=wp-header-end>'; // This is a hook WP uses to dump notices after; aptly named, of course.
		\do_action( 'tsfem_notices' );
		echo '</aside>';
	}

	/**
	 * Outputs panes wrap and does callback.
	 *
	 * @since 1.5.0
	 * @since 2.0.1 Now listens to $this->wrap_type
	 * @since 2.2.0 Is no longer a tsfem-flex-item.
	 * @since 2.6.0 1. Added a super wrap to allow a condensed layout.
	 *              2. Renamed from "panes_wrap".
	 */
	final public function panes_page_wrap() {
		printf(
			'<div id=tsfem-panes-super-wrap><main class="tsfem-panes-wrap tsfem-panes-wrap-%s">',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped
			\in_array( $this->wrap_type, [ 'column', 'row' ], true ) ? $this->wrap_type : 'column'
		);
		\do_action( 'tsfem_content' );
		echo '</main></div>';
	}

	/**
	 * Outputs panes wrap and does callback.
	 *
	 * @since 1.5.0
	 */
	final public function connect_page_wrap() {
		echo '<div id=tsfem-panes-super-wrap><main id=tsfem-connect-wrap class="tsfem-flex tsfem-flex-row">';
		\do_action( 'tsfem_content' );
		echo '</main></div>';
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
	final protected static function enqueue_admin_scripts() {

		// Enqueue default scripts.
		\add_filter( 'the_seo_framework_scripts', [ static::class, '_register_default_scripts' ] );

		// Load all modern TSF scripts.
		\add_filter( 'the_seo_framework_register_scripts', '__return_true' );

		// Load all old TSF scripts.
		if ( ! \TSF_EXTENSION_MANAGER_USE_MODERN_TSF ) {
			\The_SEO_Framework\Builders\Scripts::prepare();
			\add_action( 'admin_enqueue_scripts', [ \tsf(), 'init_admin_scripts' ], 0, 1 );
		}
	}

	/**
	 * Registers default TSFEM admin scripts.
	 * Also registers TSF scripts, for TT (tooltip) support.
	 *
	 * @since 2.0.2
	 * @since 2.6.3 Changed hook from `tsfem_before_enqueue_scripts`
	 * @access private
	 * @internal
	 *
	 * @param array $scripts The default CSS and JS loader settings.
	 * @return array More CSS and JS loaders.
	 */
	final public static function _register_default_scripts( $scripts ) {

		$tsfem = \tsfem();

		$scripts[] = [
			'id'       => 'tsfem',
			'type'     => 'js',
			'deps'     => [ 'jquery', 'wp-util', 'tsf', 'tsf-tt' ],
			'autoload' => true,
			'name'     => 'tsfem',
			'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
			'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
			'l10n'     => [
				'name' => 'tsfemL10n',
				'data' => [
					'nonce'         => \TSF_Extension_Manager\can_do_manager_settings() ? \wp_create_nonce( 'tsfem-ajax-nonce' ) : '',
					'insecureNonce' => \TSF_Extension_Manager\can_do_extension_settings() || \TSF_Extension_Manager\can_do_manager_settings() ? \wp_create_nonce( 'tsfem-ajax-insecure-nonce' ) : '',
					'i18n'          => [
						'InvalidResponse' => \esc_html__( 'Received invalid AJAX response.', 'the-seo-framework-extension-manager' ),
						'UnknownError'    => \esc_html__( 'An unknown error occurred.', 'the-seo-framework-extension-manager' ),
						'TimeoutError'    => \esc_html__( 'Timeout: Server took too long to respond.', 'the-seo-framework-extension-manager' ),
						'BadRequest'      => \esc_html__( "Bad request: The server can't handle the request.", 'the-seo-framework-extension-manager' ),
						'FatalError'      => \esc_html__( 'A fatal error occurred on the server.', 'the-seo-framework-extension-manager' ),
						'ParseError'      => \esc_html__( 'A parsing error occurred in your browser.', 'the-seo-framework-extension-manager' ),
					],
				],
			],
		];

		$scripts[] = [
			'id'       => 'tsfem-ui',
			'type'     => 'css',
			'deps'     => [ 'tsf' ],
			'autoload' => true,
			'hasrtl'   => false,
			'name'     => 'tsfem-ui',
			'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/css/',
			'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
			'inline'   => null,
		];
		$scripts[] = [
			'id'       => 'tsfem-ui',
			'type'     => 'js',
			'deps'     => [ 'tsfem', 'tsf' ],
			'autoload' => true,
			'name'     => 'tsfem-ui',
			'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
			'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
			'l10n'     => [
				'name' => 'tsfemUIL10n',
				'data' => [],
			],
			'tmpl'     => [
				'file' => $tsfem->get_template_location( 'fbtopnotice' ),
			],
		];

		$scripts[] = [
			'id'       => 'tsfem-worker',
			'type'     => 'js',
			'deps'     => [],
			'autoload' => false,
			'name'     => 'tsfem-worker',
			'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
			'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
		];

		if ( $tsfem->is_tsf_extension_manager_page() ) {
			$scripts[] = [
				'id'       => 'tsfem-manager',
				'type'     => 'css',
				'deps'     => [ 'tsfem-ui' ],
				'autoload' => true,
				'hasrtl'   => false,
				'name'     => 'tsfem-manager',
				'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/css/',
				'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
				'inline'   => null,
			];
			$scripts[] = [
				'id'       => 'tsfem-manager',
				'type'     => 'js',
				'deps'     => [ 'tsfem-ui' ],
				'autoload' => true,
				'name'     => 'tsfem-manager',
				'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
				'l10n'     => [
					'name' => 'tsfemManagerL10n',
					'data' => [
						'i18n' => [
							'Activate'   => \esc_html__( 'Activate', 'the-seo-framework-extension-manager' ),
							'Deactivate' => \esc_html__( 'Deactivate', 'the-seo-framework-extension-manager' ),
						],
					],
				],
			];
		}

		return $scripts;
	}

	/**
	 * Registers form scripts.
	 *
	 * Should only be used by extensions, not the manager!
	 *
	 * @since 1.3.0
	 * @since 2.0.2 1. Now uses TSF's Scripts module.
	 *              2. Now returns void
	 * @since 2.4.0 The access level for nonce generation now controlled via another constant.
	 * @since 2.6.3 Changed from a setter to a getter.
	 * @access protected
	 * @internal
	 */
	final protected function get_form_scripts() {
		return [
			[
				'id'       => 'tsfem-form',
				'type'     => 'css',
				'deps'     => [ 'tsfem-ui', 'tsf-tt' ],
				'autoload' => true,
				'hasrtl'   => false,
				'name'     => 'tsfem-form',
				'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/css/',
				'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
				'inline'   => null,
			],
			[
				'id'       => 'tsfem-form',
				'type'     => 'js',
				'deps'     => [ 'tsfem-ui', 'tsf-tt' ],
				'autoload' => true,
				'name'     => 'tsfem-form',
				'base'     => \TSF_EXTENSION_MANAGER_DIR_URL . 'lib/js/',
				'ver'      => \TSF_EXTENSION_MANAGER_VERSION,
				'l10n'     => [
					'name' => 'tsfemFormL10n',
					'data' => [
						'nonce'  => \TSF_Extension_Manager\can_do_extension_settings() ? \wp_create_nonce( 'tsfem-form-nonce' ) : '',
						'callee' => \get_class( $this ), // Don't use __CLASS__, we require the core instance. TODO self::class?
						'i18n'   => [
							// TODO categorize in multidimensionals
							// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment -- Alignment is fine.

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
							// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment
						],
					],
				],
				// phpcs:disable
				// Inherits from 'tsfem'
				// 'tmpl'     => [
				// 	'file' => \tsfem()->get_template_location( 'fbtopnotice' ),
				// ],
				// phpcs:enable
			],
		];
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
