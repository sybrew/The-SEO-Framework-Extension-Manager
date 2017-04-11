<?php
/**
 * @package TSF_Extension_Manager\Extension\Transporter\Steps
 */
namespace TSF_Extension_Manager\Extension;

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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use \TSF_Extension_Manager\Construct_Core_Static_Final as Construct_Core_Static_Final;
use \TSF_Extension_Manager\Extension_Forms as Extension_Forms;
use \TSF_Extension_Manager\Error as Error;

/**
 * Class TSF_Extension_Manager\Extension\Transporter_Steps
 *
 * Holds extension UI steps.
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 */
final class Transporter_Steps {
	use Enclose_Stray_Private, Construct_Core_Static_Final, Extension_Forms, Error;

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
	protected $request_name = array();
	protected $nonce_action = array();

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
	public function _set_instance_properties( $vars = array() ) {

		$property_names = array(
			'nonce_name',
			'request_name',
			'nonce_action',
			'transporter_page_slug',
			'o_index',
		);

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
	 * Checks whether the variable is set and passes it back.
	 * If the value isn't set, it will set it to the fallback variable.
	 *
	 * It will also return the value so it can be used in a return statement.
	 *
	 * PHP < 7 wrapper for null coalescing.
	 * @link http://php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op
	 * @since 1.0.0
	 *
	 * @param string $v The variable that's maybe set. Passed by reference.
	 * @param mixed $f The fallback variable. Default empty string.
	 * @return string
	 */
	private function coalesce_var( &$v = null, $f = '' ) {
		return isset( $v ) ? $v : $v = $f;
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
						$output = $this->get_seo_settings_export_selection( $ajax );
						break 2;

					case 2 :
						$output = $this->get_seo_settings_export_actions( $ajax );
						break 2;

					case 3 :
						break 2;

					default :
						break 2;
				endswitch;
				break;

			case 'meta' :
				// TODO
				break;
		endswitch;

		return $this->coalesce_var( $output );
	}

	private function get_seo_settings_export_selection( $ajax = false ) {

		if ( $ajax ) {
			// TODO?
		} else {
			$left = sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_export_option_output() );
			$right = sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_import_option_output() );

			$output = sprintf( '<div class="tsfem-e-transporter-step-1 tsfem-pane-split tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>', $left . $right );
		}

		return $this->coalesce_var( $output );
	}

	/**
	 * @TODO use $ajax?
	 */
	private function get_seo_settings_export_actions( $ajax = false ) {

		$export_data = \TSF_Extension_Manager\Extension\Transporter_Admin::get_the_seo_framework_options_export_data( false );
		$transport_id = 'tsfem-e-transporter-transport-data-text';

		$download_button = $this->get_seo_settings_download_button_output();
		$download_button_wrap = sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $download_button );
		$clipboard_button = $this->get_seo_settings_clipboard_button_output( $transport_id );
		$clipboard_button_wrap = sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $clipboard_button );
		$buttons_wrap = sprintf(
			'<div class="tsfem-e-transporter-steps-split tsfem-pane-split tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>',
			$download_button_wrap . $clipboard_button_wrap
		);

		$textarea = sprintf(
			'<textarea rows="5" class="%1$s" id="%1$s" readonly="readonly">%2$s</textarea>',
			$transport_id,
			json_encode( $export_data )
		);

		$output = sprintf( '<div class="tsfem-e-transporter-transport-data tsfem-flex tsfem-flex-nogrowshrink">%s<div>', $buttons_wrap . $textarea );

		return $this->coalesce_var( $output );
	}

	private function get_seo_settings_export_output( $ajax = false ) {

		if ( $ajax ) {
			$left = sprintf( '<div class="tsfem-actions-left-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_export_option_output() );
			$right = sprintf( '<div class="tsfem-actions-right-wrap tsfem-flex tsfem-flex-nowrap">%s</div>', $this->get_import_option_output() );

			$output = sprintf( '<div class="tsfem-e-transporter-step-1 tsfem-pane-split tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink">%s</div>', $left . $right );
		} else {
			// TODO?
		}

		return $this->coalesce_var( $output );
	}

	private function get_export_option_output() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Export SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_export_button();

		return sprintf( '<div class="tsfem-e-transporter-export-option">%s</div>', $title . $button );
	}

	private function get_export_button() {

		$class = 'tsfem-button-primary tsfem-button-blue tsfem-button-upload tsfem-button-ajax';
		$name = \__( 'Export SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Export SEO Settings to text or file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'export' );
		$nonce = $this->_get_nonce_field( 'export' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = array(
			'id'    => 'tsfem-e-transporter-export-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-export-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		);

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}

	private function get_seo_settings_clipboard_button_output( $textarea_id = '' ) {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Copy SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_seo_settings_clipboard_button( $textarea_id );

		return sprintf( '<div class="tsfem-e-transporter-clipboard-option">%s</div>', $title . $button );
	}

	private function get_seo_settings_clipboard_button( $textarea_id ) {

		$class = 'tsfem-button-primary tsfem-button-primary-bright tsfem-button-clipboard';
		$name = \__( 'Copy SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Copy the SEO Settings to clipboard', 'the-seo-framework-extension-manager' );

		$args = array(
			'url'   => '#',
			'class' => $class,
			'title' => $title,
			'content' => $name,
			'id'    => $textarea_id . '-clipboard-button',
			'data'  => array(
				'clipboardid' => $textarea_id,
				'clipboardtype' => 'application/json',
			),
		);

		return \tsf_extension_manager()->get_link( $args );
	}

	/**
	 * Returns SEO settings download button and header.
	 *
	 * @since 1.0.0
	 *
	 * @return string The button output and header.
	 */
	private function get_seo_settings_download_button_output() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Download SEO Settings', 'the-seo-framework-extension-manager' ) );

		//* TODO clean this
		$button = $this->get_seo_settings_download_button_a();
		$form = $this->get_seo_settings_download_button_form();

	//	return sprintf( '<div class="tsfem-e-transporter-download-option">%s</div>', $title . $button );
		return sprintf( '<div class="tsfem-e-transporter-download-option">%s</div>', $title . $form );
	}

	/**
	 * Returns SEO settings download a href button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The a href download button output.
	 */
	private function get_seo_settings_download_button_a() {

		$class = 'tsfem-button-primary tsfem-button-green tsfem-button-download';
		$name = \__( 'Download SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Download the SEO Settings file', 'the-seo-framework-extension-manager' );

		$url_args = array(
			'action' => $this->nonce_action['download'],
			$this->nonce_name => \wp_create_nonce( $this->request_name['download'] ),
		);
		$location = \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug, $url_args );

		$filename = sprintf( 'SEO-Settings-%s.json.txt', str_replace( array( ' ', '_', "\r\n", "\r", '\\', "\n" ), '-', trim( \get_bloginfo( 'name', 'raw' ) ) ) );

		$args = array(
			'url'   => $location,
			'class' => $class,
			'title' => $title,
			'content' => $name,
			'id'    => 'tsfem-e-transporter-download-form',
			'input' => $filename,
		);

		return $this->get_download_link( $args );
	}

	private function get_seo_settings_download_button_form() {

		$class = 'tsfem-button-primary tsfem-button-green tsfem-button-download';
		$name = \__( 'Download SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Download the SEO Settings file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'download' );
		$nonce = $this->_get_nonce_field( 'download' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = array(
			'id'    => 'tsfem-e-transporter-download-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-download-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		);

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}

	/**
	 * Creates a download button link from input arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The button arguments.
	 * @return string The download button.
	 */
	private function get_download_link( array $args = array() ) {

		$defaults = array(
			'url'     => '',
			'target'  => '_self',
			'class'   => '',
			'title'   => '',
			'content' => '',
			'download' => true,
			'filename' => '',
		);

		return \tsf_extension_manager()->get_link( \wp_parse_args( $args, $defaults ) );
	}

	private function get_import_option_output() {

		$title = sprintf( '<h4 class="tsfem-action-title">%s</h4>', \esc_html__( 'Import SEO Settings', 'the-seo-framework-extension-manager' ) );

		$button = $this->get_import_button();

		return sprintf( '<div class="tsfem-e-transporter-import-option">%s</div>', $title . $button );
	}

	private function get_import_button() {

		$class = 'tsfem-button-primary tsfem-button-blue tsfem-button-download tsfem-button-ajax';
		$name = \__( 'Import SEO Settings', 'the-seo-framework-extension-manager' );
		$title = \__( 'Import SEO Settings from text or file', 'the-seo-framework-extension-manager' );

		$nonce_action = $this->_get_nonce_action_field( 'import' );
		$nonce = $this->_get_nonce_field( 'import' );
		$submit = $this->_get_submit_button( $name, $title, $class );

		$args = array(
			'id'    => 'tsfem-e-transporter-import-form',
			'input' => compact( 'nonce_action', 'nonce', 'submit' ),
			'ajax'  => true,
			'ajax-id'    => 'tsfem-e-transporter-import-button',
			'ajax-class' => $class,
			'ajax-name'  => $name,
			'ajax-title' => $title,
		);

		return $this->_get_action_form( \tsf_extension_manager()->get_admin_page_url( $this->transporter_page_slug ), $args );
	}
}
