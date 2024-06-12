<?php
/**
 * @package TSF_Extension_Manager\Functions
 *
 * This file contains helper functions to help transition to TSF v5.0.
 * This will be removed and all calls will be updated once we support TSF v5.0+ only.
 * @ignore
 */

namespace TSF_Extension_Manager\Transition;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, Squiz.Commenting.FunctionComment -- temp.

function convert_markdown( $text, $convert = [], $args = [] ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->format()->markdown()->convert( $text, $convert, $args )
		: \tsf()->convert_markdown( $text, $convert, $args );
}

function do_dismissible_notice( $message, $args = [] ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->admin()->notice()->output_notice( $message, $args )
		: \tsf()->do_dismissible_notice(
			$message,
			$args['type'] ?? 'updated',
			$args['icon'] ?? true,
			$args['escape'] ?? true,
			$args['inline'] ?? false
		);
}

function is_headless( $type ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \The_SEO_Framework\is_headless( $type )
		: \tsf()->is_headless[ $type ];
}

function redirect( $page, $query_args = [] ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->admin()->utils()->redirect( $page, $query_args )
		: \tsf()->admin_redirect( $page, $query_args );
}

function sanitize_metadata_content( $text ) {

	if ( \TSF_EXTENSION_MANAGER_USE_MODERN_TSF )
		return \tsf()->sanitize()->metadata_content( $text );

	if ( ! \is_scalar( $text ) || ! \strlen( $text ) ) return '';

	return \wptexturize( \capital_P_dangit( \tsf()->s_title_raw( $text ) ) );
}

function clamp_sentence( $sentence, $min_char_length = 1, $max_char_length = 4096 ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->format()->strings()->clamp_sentence( $sentence, $min_char_length, $max_char_length )
		: \tsf()->trim_excerpt( $sentence, $min_char_length, $max_char_length );
}

function make_info( $description = '', $link = '', $echo = true ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->admin()->layout()->html()->make_info( $description, $link, $echo )
		: \The_SEO_Framework\Interpreters\HTML::make_info( $description, $link, $echo );
}

function make_data_attributes( $data ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->admin()->layout()->html()->make_data_attributes( $data )
		: \The_SEO_Framework\Interpreters\HTML::make_data_attributes( $data );
}

function make_single_select_form( $args ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->admin()->layout()->form()->make_single_select_form( $args )
		: \The_SEO_Framework\Interpreters\Form::make_single_select_form( $args );
}

function get_image_uploader_form( $args ) {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->admin()->layout()->form()->get_image_uploader_form( $args )
		: \The_SEO_Framework\Interpreters\Form::get_image_uploader_form( $args );
}

function sitemap_registry() {
	return \TSF_EXTENSION_MANAGER_USE_MODERN_TSF
		? \tsf()->sitemap()->registry()
		: \The_SEO_Framework\Bridges\Sitemap::get_instance();
}
