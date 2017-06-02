<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Traits
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

/**
 * Local extension for The SEO Framework
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
 * Holds settings generator functions for package TSF_Extension_Manager\Extension\Local.
 *
 * Not according to DRY standards for improved performance.
 *
 * This trait has dependencies!
 *
 * @since 1.0.0
 * @access private
 * @uses trait TSF_Extension_Manager\Extension_Options
 * @see TSF_Extension_Manager\Traits\Extension_Options
 * @uses trait TSF_Extension_Manager\Traits\UI
 * @see TSF_Extension_Manager\Traits\UI
 */
trait Settings_Generator {
	// Load Instance type.. set as class...?

	private $o_key;
	private $has_o_key;

	/**
	 * @param string $o_key The key given to the option. For when you want to prevent option collision.
	 */
	public function _fields( array $fields, $type = 'echo', $o_key = '' ) {

		$this->o_key = \sanitize_key( $o_key );
		$this->has_o_key = (bool) $this->o_key;
		// $this->o_index = $o_index;

		if ( 'get' === $type )
			return $this->get_fields( $fields );

		$this->output_fields( $fields );
	}

	/**
	 *
	 * @param array $fields. Passed by reference for performance.
	 */
	private function get_fields( array &$fields ) {

		$_fields = '';

		foreach ( $this->generate_fields( $fields ) as $field ) {
			//* Already escaped.
			$_fields .= $field;
		}

		return $_fields;
	}

	/**
	 *
	 * @param array $fields. Passed by reference for performance.
	 */
	private function output_fields( array &$fields ) {
		foreach ( $this->generate_fields( $fields ) as $field ) {
			//* Already escaped.
			echo $field;
		}
	}

	private function generate_fields( array $fields ) {
		foreach ( $fields as $option => $args ) {
			yield $this->create_field( $option, $args );
		}
	}

	private function create_field( $option, array $args ) {

		if ( empty( $args['_edit'] ) )
			return '';

		$this->clean_list_index( $args );

		if ( $args['_fields'] )
			return $this->generate_fields_multi( $option, $args );

		$this->clean_desc_index( $args['_desc'] );

		switch ( $args['_type'] ) :
			case 'text' :
			case 'password' :
			case 'tel' :
			case 'url' :
			case 'search' :
			case 'time' :
			case 'week' :
			case 'month' :
			case 'datetime-local' :
			case 'date' :
			case 'number' :
			case 'range' :
			case 'color' :
			case 'hidden' :
				return $this->create_input_field_by_type( $option, $args );
				break;

			case 'textarea' :
				return $this->create_textarea_field( $option, $args );
				break;

			case 'select' :
				return $this->create_select_field( $option, $args );
				break;

			case 'checkbox' :
				return $this->create_checkbox_field( $option, $args );
				break;

			case 'radio' :
				return $this->create_radio_field( $option, $args );
				break;

			case 'image' :
				return $this->create_image_field( $option, $args );
				break;

			default :
				break;
		endswitch;

		return '';
	}

	/**
	 * @see $this->create_field()
	 */
	private function generate_fields_multi( $option, array $args ) {

		$this->clean_desc_index( $args['_desc'] );
		$title = $args['_desc'][0];
		$desc  = $args['_desc'][1];

		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		$_fields = '';
		foreach ( $this->generate_fields( $args['_fields'] ) as $field ) {
			//* Already escaped.
			$_fields .= $field . PHP_EOL;
		}

		return vsprintf(
			'<div class="tsfem-e-local-multi-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-e-local-multi-setting-label tsfem-flex" id="%s">%s</div>',
					$this->create_field_id( $option ),
					vsprintf(
						'<div class="tsfem-e-local-multi-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<div class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></div>',
								[
									sprintf( 'tsfem-e-local-option-title%s', ( $s_desc ? ' tsfem-e-local-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-e-local-multi-setting-input tsfem-flex">%s</div>',
					$_fields
				),
			]
		);
	}

	/**
	 * Creates a JS and no-JS compatible description mark.
	 *
	 * @since 1.0.0
	 *
	 * @param string $description The description.
	 * @return string The escaped inline HTML description output.
	 */
	private function create_fields_sub_description( $description ) {
		return vsprintf(
			'<span class="tsfem-has-hover-balloon" title="%s" data-desc="%s"><span>%s</span></span>',
			[
				\esc_attr( $description ),
				\esc_html( $description ),
				'<span class="tsfem-extension-description-icon tsfem-dashicon tsfem-unknown"></span>',
			]
		);
	}

	/**
	 * Creates a description block.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $description The description field(s).
	 * @return string The escaped flex HTML description output.
	 */
	private function create_fields_description( $description ) {

		if ( is_scalar( $description ) ) {
			return sprintf(
				'<span class="tsfem-e-local-option-description">%s</span>',
				\esc_html( $description )
			);
		} else {
			$ret = '';
			foreach ( $description as $desc ) {
				$ret .= $this->create_fields_description( $desc );
			}
			return $ret;
		}
	}

	/**
	 * Returns field name and ID attributes for form fields.
	 *
	 * @since 1.0.0
	 * @uses TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @uses $this->o_key
	 * @access private
	 *
	 * @param string $option The option the field is for.
	 * @return string Full field ID/name attribute.
	 */
	private function create_field_id( $option ) {

		$option = \sanitize_key( $option );

		if ( $this->has_o_key ) {
			return sprintf( '%s[%s][%s][%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $this->o_key, $option );
		}

		return sprintf( '%s[%s][%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $option );
	}

	//* TEMP. Simply put, all args need to be filled in correctly prior to running this to improve performance.
	private function clean_list_index( array &$args ) {
		$args['_type']    = isset( $args['_type'] )    ? (string) $args['_type']    : '';
		$args['_default'] = isset( $args['_default'] ) ? (string) $args['_default'] : '';
		$args['_ph']      = isset( $args['_ph'] )      ? (string) $args['_ph']      : '';
		$args['_ret']     = isset( $args['_ret'] )     ? (string) $args['_ret']     : '';
		$args['_req']     = isset( $args['_req'] )     ? (bool) $args['_req']       : false;
		$args['_edit']    = isset( $args['_edit'] )    ? (bool) $args['_edit']      : false;
		$args['_desc']    = isset( $args['_desc'] )    ? (array) $args['_desc']     : [];
		$args['_range']   = isset( $args['_range'] )   ? (array) $args['_range']    : [];
		$args['_fields']  = isset( $args['_fields'] )  ? (array) $args['_fields']   : [];
		$args['_dd']      = isset( $args['_dd'] )      ? (array) $args['_dd']       : [];
	}

	private function clean_desc_index( array &$desc ) {
		$desc[0] = isset( $desc[0] ) ? $desc[0] : '';
		$desc[1] = isset( $desc[1] ) ? $desc[1] : '';
		$desc[2] = isset( $desc[2] ) ? $desc[2] : '';
	}

	//* Cleans range, including steps @ 1e-10
	private function clean_range_index( array &$range ) {
		$range[0] = isset( $range[0] ) ? (string) $range[0] : '';
		$range[1] = isset( $range[1] ) ? (string) $range[1] : '';
		$range[2] = isset( $range[2] ) ? (string) rtrim( sprintf( '%.10F', $range[2] ), '.0' ) : '';
	}

	/**
	 * Accepted types... TODO
	 */
	private function create_input_field_by_type( $option, array $args ) {

		switch ( $args['_type'] ) :

			case 'date' :
			case 'number' :
			case 'range' :
				$this->clean_range_index( $args['_range'] );

				$s_range = '';
				$s_range .= '' !== $args['_range'][0] ? sprintf( 'min=%s', $args['_range'][0] ) : '';
				$s_range .= '' !== $args['_range'][1] ? sprintf( ' max=%s', $args['_range'][1] ) : '';
				$s_range .= '' !== $args['_range'][2] ? sprintf( ' step=%s', $args['_range'][2] ) : '';
				break;

			case 'color' :
				// TODO
				break;

			default :
			case 'text' :
			case 'password' :
			case 'tel' :
			case 'url' :
			case 'search' :
			case 'time' :
			case 'week' :
			case 'month' :
			case 'datetime-local' :
			case 'hidden' :
				//= Look behind you.
				break;
		endswitch;

		//* Not escaped.
		$title = $args['_desc'][0];

		// Escaped.
		$s_type = \esc_attr( $args['_type'] );
		$s_name = $s_id = $this->create_field_id( $option );
		$s_ph   = $args['_ph'] ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';
		$s_range = isset( $s_range ) ? $s_range : '';

		return vsprintf(
			'<div class="tsfem-%s-field-wrapper tsfem-e-local-flex-setting tsfem-flex">%s%s</div>',
			[
				$s_type,
				sprintf(
					'<div class="tsfem-e-local-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-e-local-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf( 'tsfem-e-local-option-title%s', ( $s_desc ? ' tsfem-e-local-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-e-local-flex-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<input type=%s id="%s" name=%s value="%s" %s %s>',
						[
							$s_type,
							$s_id,
							$s_name,
							\esc_attr( $args['_default'] ),
							$s_range,
							$s_ph,
						]
					)
				),
			]
		);
	}

	private function create_textarea_field( $option, array $args ) {}
	private function create_select_field( $option, array $args ) {

		//* Not escaped.
		$title = $args['_desc'][0];

		$s_name = $s_id = $this->create_field_id( $option );
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-select-field-wrapper tsfem-e-local-flex-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-e-local-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-e-local-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf( 'tsfem-e-local-option-title%s', ( $s_desc ? ' tsfem-e-local-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				sprintf(
					'<div class="tsfem-e-local-flex-setting-input tsfem-flex">%s</div>',
					vsprintf(
						'<select id="%s" name=%s>%s</select>',
						[
							$s_id,
							$s_name,
							$this->get_select_options( $args['_select'], $args['_default'] ),
						]
					)
				),
			]
		);
	}

	private function get_select_options( array $options, $default = '' ) {

		$_fields = '';

		foreach ( $this->generate_select_fields( $options, $default ) as $field ) {
			//* Already escaped.
			$_fields .= $field;
		}

		return $_fields;
	}

	/**
	 * Heavily optimized for performance.
	 */
	private function generate_select_fields( array $fields, $default = '' ) {

		static $_level = 0;

		if ( '' !== $default ) :
			foreach ( $fields as $args ) :

				if ( $_level ) {
					//* Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '― ' . $args[1] : str_repeat( '― ', $_level ) . $args[1];

					//= `&8213; `... gets escaped otherwise.
					$args[1] = '― ' . $args[1];
				}

				if ( isset( $args[2] ) ) {
					yield sprintf( '<optgroup label=%s>', $args[1] );
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					$_level++;
					yield $this->get_select_options( $args[2] );
					$_level--;
					yield '</optgroup>';
				} else {
					if ( $args[0] === $default ) {
						yield sprintf( '<option value="%s" selected>%s</option>', $args[0], $args[1] );
					} else {
						yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					}
				}
			endforeach;
		else :
			foreach ( $fields as $args ) :

				if ( $_level ) {
					//* Multilevel isn't supported by Chrome, for instance, yet.
					// $args[1] = 1 === $_level ? '― ' . $args[1] : str_repeat( '― ', $_level ) . $args[1];

					//= `&8213; `... gets escaped otherwise.
					$args[1] = '― ' . $args[1];
				}

				if ( isset( $args[2] ) ) {
					yield sprintf( '<optgroup label="%s">', $args[1] );
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
					$_level++;
					yield $this->get_select_options( $args[2] );
					$_level--;
					yield '</optgroup>';
				} else {
					yield sprintf( '<option value="%s">%s</option>', $args[0], $args[1] );
				}
			endforeach;
		endif;

		$level = 0;
	}

	private function create_checkbox_field( $option, array $args ) {}
	private function create_radio_field( $option, array $args ) {}

	/**
	 *
	 * @see _wp_ajax_crop_image() The AJAX cropper callback.
	 */
	private function create_image_field( $option, array $args ) {

		/**
		 * @uses trait TSF_Extension_Manager\UI
		 * @package TSF_Extension_Manager\Traits
		 */
		$this->register_media_scripts();

		//* Not escaped.
		$title = $args['_desc'][0];

		// Escaped.
		$s_type = \esc_attr( $args['_type'] );
		$s_name = $s_id = $this->create_field_id( $option );
		$s_ph   = $args['_ph'] ? sprintf( 'placeholder="%s"', \esc_attr( $args['_ph'] ) ) : '';
		$s_desc = $args['_desc'][1] ? $this->create_fields_description( $args['_desc'][1] ) : '';
		$s_more = $args['_desc'][2] ? $this->create_fields_sub_description( $args['_desc'][2] ) : '';

		return vsprintf(
			'<div class="tsfem-image-field-wrapper tsfem-e-local-flex-setting tsfem-flex">%s%s</div>',
			[
				sprintf(
					'<div class="tsfem-e-local-flex-setting-label tsfem-flex">%s</div>',
					vsprintf(
						'<div class="tsfem-e-local-flex-setting-label-inner-wrap tsfem-flex">%s%s</div>',
						[
							vsprintf(
								'<label for="%s" class="tsfem-e-local-flex-setting-label-item tsfem-flex"><div class="%s">%s</div></label>',
								[
									$s_id,
									sprintf( 'tsfem-e-local-option-title%s', ( $s_desc ? ' tsfem-e-local-option-has-description' : '' ) ),
									sprintf( '<strong>%s</strong>%s', \esc_html( $title ), $s_more ),
								]
							),
							$s_desc,
						]
					)
				),
				vsprintf(
					'<div class="tsfem-e-local-flex-setting-input tsfem-flex">%s<div class="tsfem-e-local-image-buttons-wrap tsfem-flex tsfem-flex-row tsfem-hide-if-no-js">%s</div></div>',
					[
						vsprintf(
							'<input type=url id="%s" name=%s value="%s" %s>',
							[
								$s_id,
								$s_name,
								\esc_attr( $args['_default'] ),
								$s_ph,
							]
						),
						vsprintf(
							'<button class="tsfem-set-image-button tsfem-button-primary tsfem-button-primary-bright tsfem-button-small" data-href="%1$s" title="%2$s" id="%3$s-select" data-inputid="%3$s">%4$s</button>',
							[
								\esc_url( \get_upload_iframe_src( 'image', -1, null ) ),
								\esc_attr_x( 'Select image', 'Button hover title', '' ),
								$s_id,
								\esc_html__( 'Select Image', '' ),
							]
						),
					]
				),
			]
		);
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

		if ( ! \check_ajax_referer( 'tsfem-upload-files', 'nonce', false ) || ! \current_user_can( 'upload_files' ) )
			\wp_send_json_error();

		$attachment_id = \absint( $_POST['id'] );

		$context = \sanitize_key( str_replace( '_', '-', $_POST['context'] ) );
		$data    = array_map( 'absint', $_POST['cropDetails'] );
		$cropped = \wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

		if ( ! $cropped || \is_wp_error( $cropped ) )
			\wp_send_json_error( array( 'message' => \esc_js__( 'Image could not be processed.', 'the-seo-framework-extension-manager' ) ) );

		switch ( $context ) :
			case 'tsf-image':

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

				$object = array(
					'post_title'     => basename( $cropped ),
					'post_content'   => $url,
					'post_mime_type' => $image_type,
					'guid'           => $url,
					'context'        => $context,
				);

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
				\wp_send_json_error( array( 'message' => \esc_js__( 'Image could not be processed.', 'the-seo-framework-extension-manager' ) ) );
				break;
		endswitch;

		\wp_send_json_success( \wp_prepare_attachment_for_js( $attachment_id ) );
	}
}
