<?php
/**
 * @package TSF_Extension_Manager\Traits
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
		\add_action( 'admin_footer_text', '__return_empty_string' );
		\add_action( 'update_footer', '__return_empty_string' );

		//* Add body class.
		\add_action( 'admin_body_class', [ $this, '_add_admin_body_class' ], 999, 1 );

		$this->enqueue_admin_scripts();
	}

	/**
	 * Outputs default UI wrap in logical order.
	 *
	 * @since 1.5.0
	 *
	 * @param $type The type of main content. Accepts 'panes' and 'connect'.
	 */
	final protected function ui_wrap( $type = 'panes' ) {
		\add_action( 'tsfem_page', [ $this, 'header_wrap' ], 25 );
		\add_action( 'tsfem_page', [ $this, 'notice_wrap' ], 50 );
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
		echo '<div class="wrap tsfem tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">';
		\do_action( 'tsfem_page' );
		echo '</div>';
	}

	/**
	 * Outputs header wrap and does callback.
	 *
	 * @since 1.5.0
	 */
	final public function header_wrap() {
		echo '<section class="tsfem-top-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-flex-space">';
		\do_action( 'tsfem_header' );
		echo '</section>';
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
	 */
	final public function panes_wrap() {
		printf(
			'<main class="tsfem-panes-wrap tsfem-flex tsfem-flex-%s">',
			in_array( $this->wrap_type, [ 'column', 'row' ], true ) ? $this->wrap_type : 'column'
		); // XSS ok.
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
	 * @staticvar bool $registered : Prevents Re-registering of the script.
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	final public function _register_default_scripts( $scripts ) {
		static $registered = false;
		if ( $registered ) return;

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

		$registered = true;
	}

	/**
	 * Registers form scripts.
	 *
	 * @since 1.3.0
	 * @since 2.0.0 Now uses \TSF_Extension_Manager\can_do_settings() for nonce creation.
	 * @since 2.0.2 : 1. Now uses TSF's Scripts module.
	 *                2. Now returns void
	 * @staticvar bool $registered : Prevents Re-registering of the script.
	 * @access protected
	 * @internal
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	final protected function register_form_scripts( $scripts ) {
		static $registered = false;
		if ( $registered ) return;
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
				'tmpl'     => [
					'file' => \tsf_extension_manager()->get_template_location( 'fbtopnotice' ),
				],
			],
		] );
		$registered = true;
	}

	/**
	 * Registers Media CSS and JS scripts.
	 * Also enqueues WordPress' media scripts.
	 *
	 * @since 1.3.0
	 * @since 2.0.2 : 1. Now uses TSF's Scripts module.
	 *                2. Now returns void
	 * @access protected
	 * @internal
	 *
	 * @param string $scripts The scripts builder class name.
	 */
	final protected function register_media_scripts( $scripts ) {
		static $registered = false;
		if ( $registered ) return;
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

		$registered = true;
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

	/**
	 * Checks media upload AJAX referred.
	 *
	 * @since 1.3.0
	 * @access private
	 * @uses WP Core check_ajax_referer()
	 * @see @link https://developer.wordpress.org/reference/functions/check_ajax_referer/
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid
	 *                   and generated between 0-12 hours ago, 2 if the nonce is
	 *                   valid and generated between 12-24 hours ago.
	 */
	final public function _is_media_nonce_verified() {
		return \check_ajax_referer( 'tsfem-media-nonce', 'nonce', false );
	}

	/**
	 * Handles cropping of images on AJAX request.
	 *
	 * Copied from WordPress Core wp_ajax_crop_image.
	 * Adjusted: 1. It accepts capability 'upload_files', instead of 'customize'.
	 *           2. It now only accepts TSF own AJAX nonces.
	 *           3. It now only accepts context 'tsf-image'
	 *           4. It no longer accepts a default context.
	 *
	 * @since 1.3.0
	 * @access private
	 * @see The SEO Framework's companion method `wp_ajax_crop_image()`.
	 */
	final public function _wp_ajax_crop_image() {

		if ( \wp_doing_ajax() ) :
			if ( $this->can_do_settings() ) :

				if ( ! $this->_is_media_nonce_verified() ) // This doesn't register correctly to phpcs...
					\wp_send_json_error();

				$attachment_id = \absint( $_POST['id'] ); // phpcs:ignore -- Sanitization, input var OK.

				$context = str_replace( '_', '-', \sanitize_key( $_POST['context'] ) ); // phpcs:ignore -- Sanitization, input var OK.
				$data    = array_map( 'absint', $_POST['cropDetails'] );                // phpcs:ignore -- Input var, input var OK.
				$cropped = \wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

				if ( ! $cropped || \is_wp_error( $cropped ) )
					\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'the-seo-framework-extension-manager' ) ) ] );

				switch ( $context ) :
					case 'tsfem-image':
						/**
						 * Fires before a cropped image is saved.
						 *
						 * Allows to add filters to modify the way a cropped image is saved.
						 *
						 * @since 4.3.0 WordPress Core
						 *
						 * @param string $context       The Customizer control requesting the cropped image.
						 * @param int    $attachment_id The attachment ID of the original image.
						 * @param string $cropped       Path to the cropped image file.
						 */
						\do_action( 'wp_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

						/** This filter is documented in wp-admin/custom-header.php */
						$cropped = \apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

						$parent_url = \wp_get_attachment_url( $attachment_id );
						$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );

						$size       = @getimagesize( $cropped );
						$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';

						$object = [
							'post_title'     => basename( $cropped ),
							'post_content'   => $url,
							'post_mime_type' => $image_type,
							'guid'           => $url,
							'context'        => $context,
						];

						$attachment_id = \wp_insert_attachment( $object, $cropped );
						$metadata = \wp_generate_attachment_metadata( $attachment_id, $cropped );

						/**
						 * Filters the cropped image attachment metadata.
						 *
						 * @since 4.3.0 WordPress Core
						 *
						 * @see wp_generate_attachment_metadata()
						 *
						 * @param array $metadata Attachment metadata.
						 */
						$metadata = \apply_filters( 'wp_ajax_cropped_attachment_metadata', $metadata );
						\wp_update_attachment_metadata( $attachment_id, $metadata );

						/**
						 * Filters the attachment ID for a cropped image.
						 *
						 * @since 4.3.0 WordPress Core
						 *
						 * @param int    $attachment_id The attachment ID of the cropped image.
						 * @param string $context       The Customizer control requesting the cropped image.
						 */
						$attachment_id = \apply_filters( 'wp_ajax_cropped_attachment_id', $attachment_id, $context );
						break;

					default:
						\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'the-seo-framework-extension-manager' ) ) ] );
						break;
				endswitch;

				\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );
			endif;
		endif;

		exit;
	}
}
