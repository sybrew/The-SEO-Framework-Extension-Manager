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
 * @since 1.0.0
 * @access private
 */
trait Settings_Generator {

	protected function output_fields( array $fields ) {
		foreach ( $this->generate_fields( $fields ) as $field ) {
			//* Already escaped.
			echo $field . PHP_EOL;
		}
	}

	protected function generate_fields( array $fields ) {
		foreach ( $fields as $option => $args ) {
			yield $this->create_field( $option, $args );
		}
	}

	protected function create_field( $option, array $args ) {

		$option = \sanitize_key( $option );
		$this->clean_list_variables( $args );

		if ( ! $args['_edit'] && 'hidden' !== $args['_type'] ) :
			return '';
		else :
			switch ( $args['_type'] ) :
				case 'input' :
					return $this->create_input_field( $option, $args );
					break;

				case 'textarea' :
					return $this->create_text_field( $option, $args );
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

				case 'address' :
					return $this->create_address_field( $option, $args );
					break;

				case 'image' :
					return $this->create_image_field( $option, $args );
					break;

				case 'hidden' :
					return $this->create_hidden_field( $option, $args );
					break;

				default :
					break;
			endswitch;
		endif;
	}

	protected function clean_list_variables( array &$args ) {
		$args['_default'] = isset( $args['_default'] ) ? (string) $args['_default'] : '';
		$args['_ph']      = isset( $args['_ph'] )      ? (string) $args['_ph']      : '';
		$args['_ret']     = isset( $args['_ret'] )     ? (string) $args['_ret']     : '';
		$args['_req']     = isset( $args['_req'] )     ? (bool) $args['_req']       : false;
		$args['_desc']    = isset( $args['_desc'] )    ? (array) $args['_desc']     : [];
		$args['_fields']  = isset( $args['_fields'] )  ? (array) $args['_fields']   : [];
		$args['_dd']      = isset( $args['_dd'] )      ? (array) $args['_dd']       : [];
	}

	protected function clean_desc_variables( array &$desc ) {
		$desc[0] = isset( $desc[0] ) ? (string) $desc[0] : '';
		$desc[1] = isset( $desc[1] ) ? (string) $desc[1] : '';
		$desc[2] = isset( $desc[2] ) ? (string) $desc[2] : '';
	}

	protected function create_input_field( $option, array $args ) {

		$this->clean_desc_variables( $args['_desc'] );

		$title = $args['_desc'][0];
		$desc  = $args['_desc'][1];
		$more  = $args['_desc'][2];
		$s_name = \esc_attr( $this->_get_field_name( $option ) );
		$s_id   = \esc_attr( $this->_get_field_id( $option ) );
		$s_ph   = \esc_attr( $args['_ph'] );

		if ( $more ) {
			$s_more = vsprintf(
				'<span class="tsfem-has-hover-balloon" title="%s" data-desc="%s"><span>%s</span></span>',
				[
					\esc_attr( $more ),
					\esc_html( $more ),
					'<span class="tsfem-extension-description-icon tsfem-dashicon tsfem-unknown"></span>',
				]
			);
		} else {
			$s_more = '';
		}

		return vsprintf( '<div class="tsfem-flex tsfem-flex-noshrink">%s%s%s</div>',
			[
				vsprintf( '<label for="%s">%s</label>',
					[
					$s_id,
					\esc_html( $title ),
					]
				),
				vsprintf( '<input id="%s" name="%s" type="text" size="40" value="" class="regular-text code tsfem-flex tsfem-flex-row" placeholder="%s">',
					[
					$s_id,
					$s_name,
					$s_ph,
					]
				),
				vsprintf( '<div class="tsfem-flex tsfem-flex-row"><span>%s %s</span></div>',
					[
					\esc_html( $desc ),
					$s_more,
					]
				),
			]
		);
	}
	protected function create_text_field( $field ) {}
	protected function create_select_field( $field ) {}
	protected function create_checkbox_field( $field ) {}
	protected function create_radio_field( $field ) {}
	protected function create_address_field( $field ) {}
	protected function create_image_field( $field ) {}
	protected function create_hidden_field( $field ) {}
}
