<?php
/**
 * @package TSF_Extension_Manager\Extension\Transporter\Steps
 */
namespace TSF_Extension_Manager\Extension\Transporter;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Transporter extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Require extension forms trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension-forms' );

/**
 * Require extension forms trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'error' );

/**
 * Class TSF_Extension_Manager\Extension\Transporter\Steps
 *
 * Holds extension UI steps.
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Steps {
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Core_Static_Final,
		\TSF_Extension_Manager\Extension_Forms,
		\TSF_Extension_Manager\Error;

	/**
	 * The object instance.
	 *
	 * @since 1.0.0
	 *
	 * @var object|null This object instance.
	 */
	private static $instance = null;

	/**
	 * Sets the class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 */
	public static function set_instance() {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @return object The current instance.
	 */
	public static function get_instance() {

		if ( is_null( static::$instance ) ) {
			static::set_instance();
		}

		return static::$instance;
	}

	/**
	 * The POST nonce validation name, action and name.
	 *
	 * @since 1.0.0
	 * @TODO really use this shadow??
	 *
	 * @var string The validation nonce name.
	 * @var string The validation request name.
	 * @var string The validation nonce action.
	 */
	protected $nonce_name;
	protected $request_name = [];
	protected $nonce_action = [];

	/**
	 * The extension page ID/slug.
	 *
	 * @since 1.0.0
	 * @TODO really use this shadow??
	 *
	 * @var string Page ID/Slug
	 */
	protected $transporter_page_slug;

	/**
	 * Current Extension index field. Likely equal to extension slug.
	 *
	 * @since 1.0.0
	 * @TODO really use this shadow??
	 *
	 * @param string $o_index The current extension settings base index field.
	 */
	protected $o_index = '';

	/**
	 * Sets instance properties.
	 *
	 * @since 1.0.0
	 * @access private
	 * @TODO really use this shadow??
	 *
	 * @param array $vars : Required : {
	 *    'nonce_name'            => string,
	 *    'request_name'          => array,
	 *    'nonce_action'          => array,
	 *    'transporter_page_slug' => string,
	 *    'o_index'               => string
	 * }
	 */
	public function _set_instance_properties( $vars = [] ) {

		$property_names = [
			'nonce_name',
			'request_name',
			'nonce_action',
			'transporter_page_slug',
			'o_index',
		];

		foreach ( $vars as $property => $value ) {
			if ( in_array( $property, $property_names, true ) ) :
				$this->$property = $value;
			else :
				\the_seo_framework()->_doing_it_wrong( __METHOD__, sprintf( 'Property %s does not exist.', \esc_html( $property ) ) );
				\wp_die();
			endif;
		}
	}

	/**
	 * Returns the step output.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $which The step number.
	 * @param string $what The step type.
	 * @param bool $ajax Whether the step is fetched through AJAX.
	 * @return string The step output.
	 */
	public function _get_step( $which = 0, $what = '', $ajax = false ) {

		switch ( $what ) :
			case 'settings' :
				switch ( $which ) :
					case 1 :
						$output = $this->get_transport_settings_selection( $ajax );
						break 2;

					default :
						break 2;
				endswitch;
				break;

			case 'settings-export' :
				switch ( $which ) :
					case 2 :
						$output = $this->get_transport_settings_export_actions( $ajax );
						break 2;

					case 3 :
						break 2;

					default :
						break 2;
				endswitch;
				break;

			case 'settings-import' :
				switch ( $which ) :
					case 2 :
						$output = $this->get_transport_settings_import_actions( $ajax );
						break 2;

					default :
						break 2;
				endswitch;
				break;

			case 'meta' :
				// TODO
				break;

			default :
				break;
		endswitch;

		return \tsf_extension_manager()->coalesce_var( $output, '' );
	}

	/**
	 * @TODO use $ajax?
	 */
	private function get_transport_settings_selection( $ajax = false ) {

		$left = sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_settings_export_option_wrap() );
		$right = sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_settings_import_option_wrap() );

		$actions = $left . $right;
		$hide = $ajax ? '' : 'tsfem-flex-hide-if-no-js';

		$output = sprintf( '<div class="tsfem-e-transporter-step-1 tsfem-pane-split tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink %s">%s</div>', $hide, $actions );

		return $output;
	}

	/**
	 */
	private function get_transport_settings_export_actions( $ajax = false ) {

		$export_data = \TSF_Extension_Manager\Extension\Transporter_Admin::get_the_seo_framework_options_export_data( false );
		$transport_id = 'tsfem-e-transporter-export-settings-data-text';

		$download_button = $this->get_settings_download_button_wrap();
		$download_button_wrap = sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $download_button );

		if ( $ajax ) {
			$clipboard_button = $this->get_settings_clipboard_button_wrap( 'copy', $transport_id );
			$clipboard_button_wrap = sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap tsfem-flex-hide-if-no-js">%s</div>', $clipboard_button );

			$buttons = $download_button_wrap . $clipboard_button_wrap;
		} else {
			$buttons = $download_button_wrap;
		}

		$buttons_wrap = sprintf( '<div class="tsfem-e-transporter-steps-split tsfem-pane-split tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>', $buttons );

		$textarea = sprintf(
			'<textarea rows="5" class="%1$s" id="%1$s" readonly="readonly">%2$s</textarea>',
			$transport_id,
			json_encode( $export_data, JSON_PRETTY_PRINT )
		);
		$textarea_wrap = sprintf( '<div class="tsfem-flex tsfem-flex-textarea-wrap">%s</div>', $textarea );

		$actions = $buttons_wrap . $textarea_wrap;
		$hide_js = $ajax ? '' : 'tsfem-flex-hide-if-js';

		$output = sprintf( '<div class="tsfem-e-transporter-transport-data tsfem-flex tsfem-flex-nogrowshrink %s">%s<div>', $hide_js, $actions );

		return $output;
	}

	/**
	 */
	private function get_transport_settings_import_actions( $ajax = false ) {

		$transport_id = 'tsfem-e-transporter-import-settings-data-text';

		$upload_button = $this->get_settings_upload_button_wrap();
		$upload_button_wrap = sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $upload_button );

		if ( $ajax ) {
			$clipboard_button = $this->get_settings_clipboard_button_wrap( 'paste', $transport_id );
			$clipboard_button_wrap = sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap tsfem-flex-hide-if-no-js">%s</div>', $clipboard_button );

			$buttons = $upload_button_wrap . $clipboard_button_wrap;
		} else {
			$buttons = $upload_button_wrap;
		}

		$buttons_wrap = sprintf( '<div class="tsfem-e-transporter-steps-split tsfem-pane-split tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>', $buttons );

		$textarea = sprintf(
			'<textarea rows="5" class="%1$s" id="%1$s" placeholder="%2$s"></textarea>',
			$transport_id,
			\esc_attr__( 'Paste SEO options here.', 'the-seo-framework-extension-manager' )
		);
		$textarea_wrap = sprintf( '<div class="tsfem-flex tsfem-flex-textarea-wrap">%s</div>', $textarea );

		$actions = $buttons_wrap . $textarea_wrap;
		$hide_js = $ajax ? '' : 'tsfem-flex-hide-if-js';

		$output = sprintf( '<div class="tsfem-e-transporter-transport-data tsfem-flex tsfem-flex-nogrowshrink %s">%s<div>', $hide_js, $actions );

		return $output;
	}

	private function get_settings_export_option_wrap() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Export SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_settings_export_button();

		return sprintf( '<div class="tsfem-e-transporter-export-option">%s</div>', $title . $button );
	}

	private function get_settings_export_button() {

		$class = 'tsfem-button-primary tsfem-button-blue tsfem-button-upload tsfem-button-ajax';
		$name = \__( 'Export SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Export SEO Settings to text or file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'export' );
		$nonce = $this->_get_nonce_field( 'export' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'    => 'tsfem-e-transporter-export-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-export-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		];

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}

	private function get_settings_clipboard_button_wrap( $type = 'copy', $textarea_id = '' ) {

		switch ( $type ) {
			case 'copy' :
				$title = \__( 'Copy SEO Settings', 'the-seo-framework-extension-manager' );
				break;

			case 'paste' :
				$title = \__( 'Paste SEO Settings', 'the-seo-framework-extension-manager' );
				break;

			default :
				\the_seo_framework()->_doing_it_wrong( __METHOD__, 'Use either copy or paste for first parameter.' );
				return '';
				break;
		}

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html( $title ) );
		$button = $this->get_settings_clipboard_button( $type, $textarea_id );

		return sprintf( '<div class="tsfem-e-transporter-clipboard-option">%s</div>', $title . $button );
	}

	private function get_settings_clipboard_button( $type = 'copy', $textarea_id = '' ) {

		switch ( $type ) {
			case 'copy' :
				$name = \__( 'Copy SEO Settings', 'the-seo-framework-extension-manager' );
				$title = \__( 'Copy the SEO Settings to clipboard', 'the-seo-framework-extension-manager' );
				$clipboard_action = 'copy';
				break;

			case 'paste' :
				$name = \__( 'Paste SEO Settings', 'the-seo-framework-extension-manager' );
				$title = \__( 'Paste the SEO Settings from clipboard', 'the-seo-framework-extension-manager' );
				$clipboard_action = 'paste';
				break;

			default :
				\the_seo_framework()->_doing_it_wrong( __METHOD__, 'Use either copy or paste for first parameter.' );
				return '';
				break;
		}

		$class = 'tsfem-button-primary tsfem-button-primary-bright tsfem-button-clipboard';
		$textarea_id = \esc_js( $textarea_id );

		$args = [
			'url'   => '#',
			'class' => $class,
			'title' => $title,
			'content' => $name,
			'id'    => $textarea_id . '-clipboard-button',
			'data'  => [
				'clipboardid' => $textarea_id,
				'clipboardtype' => 'application/json',
				'clipboardaction' => $clipboard_action,
			],
		];

		return \tsf_extension_manager()->get_link( $args );
	}

	/**
	 * Returns SEO settings download button and header.
	 *
	 * @since 1.0.0
	 *
	 * @return string The button output and header.
	 */
	private function get_settings_download_button_wrap() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Download SEO Settings', 'the-seo-framework-extension-manager' ) );

		$form = $this->get_settings_download_button_form();

		return sprintf( '<div class="tsfem-e-transporter-download-option">%s</div>', $title . $form );
	}

	private function get_settings_download_button_form() {

		$class = 'tsfem-button-primary tsfem-button-green tsfem-button-download';
		$name = \__( 'Download SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Download the SEO Settings file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'download' );
		$nonce = $this->_get_nonce_field( 'download' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'    => 'tsfem-e-transporter-download-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-download-settings-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		];

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}

	private function get_settings_import_option_wrap() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Import SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_settings_import_button();

		return sprintf( '<div class="tsfem-e-transporter-import-option">%s</div>', $title . $button );
	}

	private function get_settings_import_button() {

		$class = 'tsfem-button-primary tsfem-button-blue tsfem-button-download tsfem-button-ajax';
		$name = \__( 'Import SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Import SEO Settings from text or file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'import' );
		$nonce = $this->_get_nonce_field( 'import' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'    => 'tsfem-e-transporter-import-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-import-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		];

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}

	/**
	 * Returns SEO settings download button and header.
	 *
	 * @since 1.0.0
	 *
	 * @return string The button output and header.
	 */
	private function get_settings_upload_button_wrap() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Upload SEO Settings', 'the-seo-framework-extension-manager' ) );

		$form = $this->get_settings_upload_button_form();

		return sprintf( '<div class="tsfem-e-transporter-upload-option">%s</div>', $title . $form );
	}

	private function get_settings_upload_button_form() {

		$class = 'tsfem-button-primary tsfem-button-green tsfem-button-upload';
		$name = \__( 'Upload SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Upload an SEO Settings file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'upload' );
		$nonce = $this->_get_nonce_field( 'upload' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = [
			'id'    => 'tsfem-e-transporter-upload-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-upload-settings-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		];

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}
}
