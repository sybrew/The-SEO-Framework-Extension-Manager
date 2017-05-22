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
 * Holds Form generation functionality.
 *
 * @since 1.0.0
 * @access private
 * @uses trait TSF_Extension_Manager\Extension_Options
 * @see TSF_Extension_Manager\Traits\Extension_Options
 */
trait Extension_Forms {

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Other page implementation classes may wish to construct and use a
	 * _get_field_id() method, if the naming format needs to be different.
	 *
	 * @since 1.0.0
	 * @uses TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @access private
	 *
	 * @param string $name Field name base
	 * @return string Full field name
	 */
	public function _get_field_name( $name ) {
		return sprintf( '%s[%s][%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 1.0.0
	 * @access private
	 * @uses $this->_get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public function _field_name( $name ) {
		echo \esc_attr( $this->_get_field_name( $name ) );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 1.0.0
	 * @uses TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS
	 * @uses $this->o_index
	 * @see TSF_Extension_Manager\Traits\Extension_Options
	 * @access private
	 *
	 * @param string $id Field id base
	 * @return string Full field id
	 */
	public function _get_field_id( $id ) {
		return sprintf( '%s[%s][%s]', TSF_EXTENSION_MANAGER_EXTENSION_OPTIONS, $this->o_index, $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 1.0.0
	 * @access private
	 * @uses $this->_get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string $id Field id base
	 * @param boolean $echo echo or return
	 * @return string Full field id
	 */
	public function _field_id( $id, $echo = true ) {

		if ( $echo ) {
			echo \esc_attr( $this->_get_field_id( $id ) );
		} else {
			return $this->_get_field_id( $id );
		}
	}

	/**
	 * Outputs hidden form nonce input fields.
	 *
	 * @since 1.0.0
	 * @uses $this->nonce_action
	 * @uses $this->nonce_name
	 * @access private
	 *
	 * @param string $action_name Nonce action name.
	 */
	public function _nonce_field( $action_name ) {
		//* Already escaped.
		echo $this->_get_nonce_field( $action_name );
	}

	/**
	 * Returns hidden form nonce input fields.
	 *
	 * @since 1.0.0
	 * @uses $this->nonce_name
	 * @access private
	 *
	 * @param string $action_name Nonce action name.
	 * @return string Escaped WordPress nonce fields for $action_name.
	 */
	public function _get_nonce_field( $action_name ) {
		return \wp_nonce_field( $this->nonce_action[ $action_name ], $this->nonce_name, true, false );
	}

	/**
	 * Outputs hidden nonce-action field.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $request_name Nonce request name.
	 */
	public function _nonce_action_field( $request_name ) {
		//* Already escaped.
		echo $this->_get_nonce_action_field( $request_name );
	}

	/**
	 * Returns a hidden form nonce-action input field.
	 *
	 * @since 1.0.0
	 * @uses $this->request_name
	 * @access private
	 *
	 * @param string $request_name Nonce request name.
	 * @return string Hidden form action input.
	 */
	public function _get_nonce_action_field( $request_name ) {
		return '<input type="hidden" name="' . $this->_get_field_name( 'nonce-action' ) . '" value="' . \esc_attr( $this->request_name[ $request_name ] ) . '">';
	}

	/**
	 * Outputs a submit button for a form.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $name The submit button displayed name.
	 * @param string $title The submit button on-hover title.
	 * @param string $class The submit button class. When empty it defaults to 'tsfem-button-primary'.
	 */
	public function _submit_button( $name, $title = '', $class = '' ) {
		//* Already escaped.
		echo $this->_get_submit_button( $name, $title, $class );
	}

	/**
	 * Returns a submit button for a form.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $name The submit button displayed name.
	 * @param string $title The submit button on-hover title.
	 * @param string $class The submit button class. When empty it defaults to 'tsfem-button-primary'.
	 * @return string The input submit button.
	 */
	public function _get_submit_button( $name, $title = '', $class = '' ) {

		$title = $title ? sprintf( ' title="%s" ', \esc_attr( $title ) ) : '';
		$class = $class ? sprintf( ' class="%s"', \esc_attr( $class ) ) : ' class="tsfem-button-primary"';

		return sprintf( '<button type="submit" name="submit" id="submit" %s%s>%s</button>', $class, $title, \esc_html( $name ) );
	}

	/**
	 * Outputs a form action button from input.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $url The admin page action URL.
	 * @param array $items The form items : {
	 *    'title'   => string The form button title.
	 *    'class'   => string The form class.
	 *    'id'      => string The form ID.
	 *    'ajax'    => bool Whether to support AJAX.
	 *    'ajax-id' => string The AJAX <a> button ID.
	 *    'input'   => array The form input entry items.
	 * }
	 * @return string The input submit button.
	 */
	public function _action_form( $url = '', array $items = [] ) {
		//* Should already be escaped before input.
		echo $this->_get_action_form( $url, $items );
	}

	/**
	 * Outputs a form action button from input.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $url The admin page action URL.
	 * @param array $items The form items : {
	 *    'class'      => string The form class.
	 *    'id'         => string The form ID.
	 *    'input'      => array The form input entry items.
	 *    'ajax'       => bool Whether to support AJAX.
	 *    'ajax-id'    => string The AJAX <a> button ID.
	 *    'ajax-class' => string The AJAX <a> button class.
	 *    'ajax-name'  => string The AJAX <a> button name.
	 *    'ajax-title' => string The AJAX <a> button on-hover title.
	 * }
	 * @return string The input submit button.
	 */
	public function _get_action_form( $url = '', array $items = [] ) {

		if ( empty( $url ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'You need to supply an action URL.' );
			return '';
		}

		if ( empty( $items['input'] ) || ! is_array( $items['input'] ) ) {
			\the_seo_framework()->_doing_it_wrong( __METHOD__, 'Form input items must be in an array. Supply at least a submit button.' );
			return '';
		}

		$defaults = [
			'class'      => '',
			'id'         => '',
			'input'      => [],
			'ajax'       => false,
			'ajax-id'    => '',
			'ajax-class' => '',
			'ajax-name'  => '',
			'ajax-title' => '',
		];

		$items = \wp_parse_args( $items, $defaults );

		$form = '';
		foreach ( $items['input'] as $item ) {
			$form .= $item;
		}

		$output = '';
		if ( $items['ajax'] ) {
			if ( '' === $items['ajax-id'] ) {
				\the_seo_framework()->_doing_it_wrong( __METHOD__, 'No AJAX ID supplied.' );
				return '';
			}

			$output .= sprintf(
				'<form action="%s" method="post" id="%s" class="hide-if-js %s">%s</form>',
				\esc_url( $url ), \esc_attr( $items['id'] ), \esc_attr( $items['class'] ), $form
			);

			$output .= sprintf(
				'<a id="%s" class="hide-if-no-js %s" title="%s">%s</a>',
				\esc_attr( $items['ajax-id'] ), \esc_attr( $items['ajax-class'] ), \esc_attr( $items['ajax-title'] ), \esc_html( $items['ajax-name'] )
			);
		} else {
			$output .= sprintf(
				'<form action="%s" method="post" id="%s" class="%s">%s</form>',
				\esc_url( $url ), \esc_attr( $items['id'] ), \esc_attr( $items['class'] ), $form
			);
		}

		return $output;
	}
}
