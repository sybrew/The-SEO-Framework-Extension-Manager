<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds User Interface functionality.
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
	 * CSS script name identifier to be used with enqueuing.
	 *
	 * @since 1.0.0
	 *
	 * @var string CSS name identifier.
	 */
	private $css_name;

	/**
	 * JavaScript name identifier to be used with enqueuing.
	 *
	 * @since 1.0.0
	 *
	 * @var string JavaScript name identifier.
	 */
	private $js_name;

	/**
	 * Main JS script to be loaded.
	 *
	 * @since 1.3.0
	 *
	 * @var array Main JS script containing name and location.
	 */
	private $main_js = [];

	/**
	 * Main CSS script to be loaded.
	 *
	 * @since 1.3.0
	 *
	 * @var array Main CSS script containing name and location.
	 */
	private $main_css = [];

	/**
	 * Additional CSS scripts to be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var array Additional CSS scripts containing name and location.
	 */
	private $additional_css = [];

	/**
	 * Additional JS scripts to be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var array Additional JS scripts containing name and location.
	 */
	private $additional_js = [];

	/**
	 * Additional l10n strings to be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @var array Additional l10n strings containing name, dependency and strings.
	 */
	private $additional_l10n = [];

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

		//* Enqueue admin scripts.
		\add_action( 'admin_enqueue_scripts', [ $this, '_enqueue_admin_scripts' ], 0, 1 );

	}

	/**
	 * Performs after-top actions.
	 *
	 * Must be called after top wrap.
	 *
	 * @since 1.3.0
	 */
	final protected function after_top_wrap() {
		echo '<div class="tsfem-notice-wrap">';
		\do_action( 'tsfem_notices' );
		echo '</div>';
	}

	/**
	 * Enqueues styles and scripts in the admin area on the extension manager page.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Now runs an additional JS script loader.
	 * @access private
	 *
	 * @param string $hook The current page hook.
	 */
	final public function _enqueue_admin_scripts( $hook ) {

		if ( $this->ui_hook === $hook ) {
			//* Enqueue styles
			\add_action( 'admin_print_styles-' . $this->ui_hook, [ $this, '_enqueue_admin_css' ], 11 );
			//* Enqueue scripts
			\add_action( 'admin_print_scripts-' . $this->ui_hook, [ $this, '_enqueue_admin_javascript' ], 11 );
			//* Enqueue late initialized scripts.
			\add_action( 'admin_footer', [ $this, '_enqueue_admin_javascript' ], 0 );
			//* Enqueue localizations.
			\add_action( 'admin_footer', [ $this, '_localize_admin_javascript' ] );
		}
	}

	/**
	 * Enqueues required CSS for the plugin.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Now is marked private.
	 * @access private
	 *
	 * @param string $hook The current page hook
	 */
	final public function _enqueue_admin_css( $hook ) {

		//* Register the script.
		$this->register_admin_css();

		\wp_enqueue_style( $this->main_css['name'] );

		if ( ! empty( $this->additional_css ) ) {
			foreach ( $this->additional_css as $script ) {
				\wp_enqueue_style( $script['name'] );
			}
		}
	}

	/**
	 * Enqueues required JS for the plugin.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Now is marked private and can register scripts JIT.
	 * @staticvar array $enqueued List of enqueued scripts.
	 * @access private
	 *
	 * @param string $hook The current page hook
	 */
	final public function _enqueue_admin_javascript( $hook ) {

		static $enqueued = [];

		//* Register the main scripts.
		$this->register_admin_javascript();

		//* Merge scripts.
		$_items = array_merge( [ $this->main_js ], $this->additional_js );

		//* Find new scripts.
		$to_enqueue = array_diff( array_column( $_items, 'name' ), $enqueued );

		if ( ! empty( $to_enqueue ) ) {
			foreach ( $to_enqueue as $i => $script ) {
				if ( ! \wp_script_is( $script, 'registered' ) ) {
					//= Register JIT.
					$this->register_additional_script( $_items[ $i ], 'js' );
				}
				\wp_enqueue_script( $script );
			}
		}

		//* Cache all called scripts.
		$enqueued += $to_enqueue;
	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $registered : Prevents Re-registering of the style.
	 */
	final protected function register_admin_css() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$this->main_css = [
			'name' => 'tsfem',
			'base' => TSF_EXTENSION_MANAGER_DIR_URL,
			'ver' => TSF_EXTENSION_MANAGER_VERSION,
		];

		\wp_register_style(
			$this->main_css['name'],
			$this->generate_file_url( $this->main_css, 'css' ),
			[ 'dashicons' ],
			$this->main_css['ver'],
			'all'
		);

		if ( ! empty( $this->additional_css ) ) :
			foreach ( $this->additional_css as $script ) {
				$this->register_additional_script( $script, 'css' );
			}
		endif;

		$registered = true;

	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $registered : Prevents Re-registering of the script.
	 */
	final protected function register_admin_javascript() {

		static $registered = null;

		if ( isset( $registered ) )
			return;

		$this->main_js = [
			'name' => 'tsfem',
			'base' => TSF_EXTENSION_MANAGER_DIR_URL,
			'ver' => TSF_EXTENSION_MANAGER_VERSION,
		];

		\wp_register_script(
			$this->main_js['name'],
			$this->generate_file_url( $this->main_js, 'js' ),
			[ 'jquery' ],
			$this->main_js['ver'],
			true
		);

		if ( ! empty( $this->additional_js ) ) :
			foreach ( $this->additional_js as $script ) {
				$this->register_additional_script( $script, 'js' );
			}
		endif;

		$registered = true;

	}

	/**
	 * Registers scripts and styles.
	 *
	 * @since 1.3.0
	 *
	 * @param array $script The script arguments.
	 * @param array $type Either 'js' or 'css'.
	 */
	final private function register_additional_script( array $script, $type = 'js' ) {
		switch ( $type ) :
			case 'js' :
				\wp_register_script(
					$script['name'],
					$this->generate_file_url( $script, 'js' ),
					[ $this->main_js['name'] ],
					$script['ver'],
					true
				);
				break;

			case 'css' :
				\wp_register_style(
					$script['name'],
					$this->generate_file_url( $script, 'css' ),
					[ $this->main_css['name'] ],
					$script['ver'],
					'all'
				);
				break;
		endswitch;
	}

	/**
	 * Generates file URL.
	 *
	 * @since 1.3.0
	 * @staticvar string $suffix
	 * @staticvar string $rtl
	 *
	 * @param array $script The script arguments.
	 * @param array $type Either 'js' or 'css'.
	 * @return string The file URL.
	 */
	final private function generate_file_url( array $script, $type = 'js' ) {

		static $suffix, $rtl;

		if ( ! $suffix ) {
			$suffix = \the_seo_framework()->script_debug ? '' : '.min';
			$rtl = \is_rtl() ? '-rtl' : '';
		}

		if ( 'js' === $type )
			return $script['base'] . "lib/js/{$script['name']}{$suffix}.js";

		return $script['base'] . "lib/css/{$script['name']}{$rtl}{$suffix}.css";
	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0.0
	 * @staticvar bool $l7d : Prevents relocalizing of the scripts.
	 * @access private
	 *
	 * @return void early If run twice or more.
	 */
	final public function _localize_admin_javascript() {

		//* Localized.
		static $l7d = null;

		if ( isset( $l7d ) )
			return;

		$strings = [
			'nonce' => \wp_create_nonce( 'tsfem-ajax-nonce' ),
			'debug' => (bool) WP_DEBUG,
			'i18n'  => [
				'Activate'        => \esc_html__( 'Activate', 'the-seo-framework-extension-manager' ),
				'Deactivate'      => \esc_html__( 'Deactivate', 'the-seo-framework-extension-manager' ),
				'InvalidResponse' => \esc_html__( 'Received invalid AJAX response.', 'the-seo-framework-extension-manager' ),
				'UnknownError'    => \esc_html__( 'An unknown error occurred.', 'the-seo-framework-extension-manager' ),
				'TimeoutError'    => \esc_html__( 'Timeout: Server took too long to respond.', 'the-seo-framework-extension-manager' ),
				'FatalError'      => \esc_html__( 'A fatal error occurred on the server.', 'the-seo-framework-extension-manager' ),
				'ParseError'      => \esc_html__( 'A parsing error occurred in your browser.', 'the-seo-framework-extension-manager' ),
			],
			'rtl' => (bool) \is_rtl(),
		];

		\wp_localize_script( $this->main_js['name'], 'tsfemL10n', $strings );

		if ( ! empty( $this->additional_l10n ) ) :
			foreach ( $this->additional_l10n as $l10n ) {
				\wp_localize_script( $l10n['dependency'], $l10n['name'], $l10n['strings'] );
			}
		endif;

		$l7d = true;

	}

	/**
	 * Registers form CSS.
	 *
	 * @since 1.3.0
	 * @staticvar $set Wehther the dependency has been set.
	 * @access protected
	 *
	 * @return bool True on set, false otherwise.
	 */
	final protected function register_form_scripts() {

		static $set = false;

		if ( $set )
			return false;

		$this->additional_css[] = [
			'name' => 'tsfem-form',
			'base' => TSF_EXTENSION_MANAGER_DIR_URL,
			'ver' => TSF_EXTENSION_MANAGER_VERSION,
		];

		$this->additional_js[] = [
			'name' => 'tsfem-form',
			'base' => TSF_EXTENSION_MANAGER_DIR_URL,
			'ver' => TSF_EXTENSION_MANAGER_VERSION,
		];

		$this->additional_l10n[] = [
			'dependency' => 'tsfem-form',
			'name' => 'tsfemFormL10n',
			'strings' => [
				'nonce' => \current_user_can( 'manage_options' ) ? \wp_create_nonce( 'tsfem-form-nonce' ) : '',
				'callee' => get_class( $this ), //! Don't use __CLASS__, we require the core instance.
				'i18n' => [
					//* TODO categorize + maybe put correctly into externs.

					// General.
					'proceed' => \esc_html__( 'Proceed', 'the-seo-framework-extension-manager' ),
					'select' => \esc_html__( 'Select', 'the-seo-framework-extension-manager' ),
					'cancel' => \esc_html__( 'Cancel', 'the-seo-framework-extension-manager' ),
					'dismiss' => \esc_html__( 'Dismiss', 'the-seo-framework-extension-manager' ),

					// Iteration.
					'performanceWarning' => \esc_html__( 'Performance warning', 'the-seo-framework-extension-manager' ),
					'itLargeConfirm' => \esc_html__( "You're about to load a large number of elements. This might degredate browser performance.", 'the-seo-framework-extension-manager' ),
					'itHugeConfirm' => \esc_html__( "You're about to load a huge number of elements. This might crash your browser.", 'the-seo-framework-extension-manager' ),
					'aysProceed' => \esc_html__( 'Are you sure you want to proceed?', 'the-seo-framework-extension-manager' ),

					// Geo
					'validate' => \esc_html__( 'Validate', 'the-seo-framework-extension-manager' ),
					'selectAddressTitle' => \esc_html__( 'Select address', 'the-seo-framework-extension-manager' ),
					'selectAddressText' => \esc_html__( 'Select one of the addresses below.', 'the-seo-framework-extension-manager' ),
					'reverseGeoWarning' => \esc_html__( 'Validation will be done only using Latitude and Longitude.', 'the-seo-framework-extension-manager' ),
				],
			],
		];

		return $set = true;
	}

	/**
	 * Registers Media CSS and JS scripts.
	 *
	 * @since 1.3.0
	 * @staticvar $set Whether the dependency has been set.
	 * @access protected
	 *
	 * @return bool True on set, false otherwise.
	 */
	final protected function register_media_scripts() {

		static $set = false;

		if ( $set )
			return false;

		$this->additional_js[] = [
			'name' => 'tsfem-media',
			'base' => TSF_EXTENSION_MANAGER_DIR_URL,
			'ver' => TSF_EXTENSION_MANAGER_VERSION,
		];

		$this->register_media_l10n();

		\wp_enqueue_media();

		return $set = true;
	}

	/**
	 * Registers Media L10n dependencies.
	 *
	 * @since 1.3.0
	 */
	final private function register_media_l10n() {

		$this->additional_l10n[] = [
			'dependency' => 'tsfem-media',
			'name' => 'tsfemMediaData',
			'strings' => [
				'nonce'          => \current_user_can( 'upload_files' ) ? \wp_create_nonce( 'tsfem-media-nonce' ) : '',
				'imgSelect'      => \esc_attr__( 'Select Image', 'the-seo-framework-extension-manager' ),
				'imgSelectTitle' => \esc_attr_x( 'Select image', 'Button hover', 'the-seo-framework-extension-manager' ),
				'imgChange'      => \esc_attr__( 'Change Image', 'the-seo-framework-extension-manager' ),
				'imgRemove'      => \esc_attr__( 'Remove Image', 'the-seo-framework-extension-manager' ),
				'imgRemoveTitle' => \esc_attr__( 'Remove selected image', 'the-seo-framework-extension-manager' ),
				'imgFrameTitle'  => \esc_attr_x( 'Select Image', 'Frame title', 'the-seo-framework-extension-manager' ),
				'imgFrameButton' => \esc_attr__( 'Use this image', 'the-seo-framework-extension-manager' ),
				'mediaEnqueued'  => \wp_style_is( 'media', 'enqueued' ),
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

	/**
	 * Checks ajax referred set by set_js_nonces based on capability.
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

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
			if ( $this->can_do_settings() ) :

				if ( ! $this->_is_media_nonce_verified() )
					\wp_send_json_error();

				$attachment_id = \absint( $_POST['id'] );

				$context = \sanitize_key( str_replace( '_', '-', $_POST['context'] ) );
				$data    = array_map( 'absint', $_POST['cropDetails'] );
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

					default :
						\wp_send_json_error( [ 'message' => \esc_js( \__( 'Image could not be processed.', 'the-seo-framework-extension-manager' ) ) ] );
						break;
				endswitch;

				\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );
			endif;
		endif;

		exit;
	}
}
