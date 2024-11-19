<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Views
 * @subpackage TSF_Extension_Manager\Inpost\Structure;
 */

namespace TSF_Extension_Manager\Extension\Articles;

use function \TSF_Extension_Manager\Transition\{
	make_info,
};

use \TSF_Extension_Manager\{
	InpostGUI,
	InpostHTML,
};

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and InpostGUI::verify( $_secret ) or die;

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.

create_type_field:;
	$type_title = \sprintf(
		'<div><strong>%s</strong></div>',
		$post_meta['type']['label']['title']
	);
	$type_info  = \sprintf(
		'<div>%s</div>',
		make_info(
			$post_meta['type']['label']['desc'],
			$post_meta['type']['label']['link'],
			false
		)
	);
	$type_label = $type_title . $type_info;

	$type_option_key   = InpostGUI::get_option_key( $post_meta['type']['option']['name'], $post_meta['pm_index'] );
	$type_option_value = $post_meta['type']['option']['value'];

	$type_select_fields = '';
	foreach ( $post_meta['type']['option']['select_values'] as $_value => $_name ) {
		$_selected = $_value === $type_option_value ? ' selected=selected' : '';

		$type_select_fields .= \sprintf(
			'<option value="%s"%s>%s</option>',
			\esc_attr( $_value ),
			$_selected,
			\esc_html( $_name )
		);
	}

	$type_field = vsprintf(
		'<select name=%s id=%s>%s</select>',
		[
			$type_option_key,
			$type_option_key,
			$type_select_fields,
		]
	);

	type_field_output:;
		InpostHTML::wrap_flex_multi(
			'block',
			[
				InpostHTML::construct_flex_wrap( 'label-input', $type_label, '', $type_option_key ),
				InpostHTML::construct_flex_wrap( 'input', $type_field ),
			]
		);
