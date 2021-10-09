<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Admin\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */

namespace TSF_Extension_Manager\Extension\Focus;

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\InpostGUI as InpostGUI;
use \TSF_Extension_Manager\InpostHTML as InpostHTML;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and InpostGUI::verify( $_secret ) or die;

$option_index = InpostGUI::get_option_key( $post_meta['kw']['option_index'], $post_meta['pm_index'] );

$make_option_id = function( $id, $key ) use ( $option_index ) {
	return sprintf( '%s[%s][%s]', $option_index, $id, $key );
};

create_analysis_field :;
	$focus_title = sprintf( '<div><strong>%s</strong></div>', $post_meta['kw']['label']['title'] );
	$focus_info  = sprintf(
		'<div>%s</div>',
		\The_SEO_Framework\Interpreters\HTML::make_info(
			$post_meta['kw']['label']['desc'],
			$post_meta['kw']['label']['link'],
			false
		)
	);
	$focus_label = $focus_title . $focus_info;

	//= Fix array if data is missing.
	$keyword_meta = \array_slice(
		array_merge_recursive(
			$post_meta['kw']['values'],
			$defaults
		),
		0,
		3
	);

	// phpcs:disable, Generic.WhiteSpace.ScopeIndent.IncorrectExact -- we're templating.
	analysis_fields_output :;
		InpostHTML::wrap_flex( 'block-open', '', 'tsfem-e-focus-analysis-wrap' );
			InpostHTML::wrap_flex( 'label', $focus_label );
			InpostHTML::wrap_flex( 'content-open', '' );
				InpostHTML::notification_area( 'tsfem-e-focus-analysis-notification-area' );
				$i = 0;
				foreach ( $keyword_meta as $id => $values ) :
					//= TEMP: PHP 7 null coalescing.
					$get_value = function( $id ) use ( $values ) {
						return isset( $values[ $id ] ) ? $values[ $id ] : '';
					};

					\call_user_func(
						$template_cb,
						[
							'supportive'         => (bool) $i++, // true if 2nd or later iteration.
							'is_premium'         => $is_premium,
							'language_supported' => $language_supported,
							'has_keyword'        => (bool) \strlen( $values['keyword'] ),
							'sub_scores'         => [
								'key'    => $make_option_id( $id, 'scores' ),
								'values' => $values['scores'],
							],
							'wrap_ids'           => [
								/* These shouldn't be saved. */
								'collapse'    => $make_option_id( $id, 'collapse' ),
								'header'      => $make_option_id( $id, 'header' ),
								'content'     => $make_option_id( $id, 'content' ),
								'edit'        => $make_option_id( $id, 'edit' ),
								'evaluate'    => $make_option_id( $id, 'evaluate' ),
								'inflections' => $make_option_id( $id, 'inflections' ),
								'synonyms'    => $make_option_id( $id, 'synonyms' ),
							],
							'action_ids'         => [
								/* These shouldn't be saved. */
								'collapser'           => $make_option_id( $id, 'collapser' ),
								'highlighter'         => $make_option_id( $id, 'highlighter' ),
								'subject_edit'        => $make_option_id( $id, 'subject_edit' ),
								'definition_selector' => $make_option_id( $id, 'definition_selector' ),
							],
							'post_input'         => [
								'keyword'              => [
									'id'    => $make_option_id( $id, 'keyword' ),
									'value' => $get_value( 'keyword' ),
								],
								'lexical_form'         => [
									'id'          => $make_option_id( $id, 'lexical_form' ),
									'selector_id' => $make_option_id( $id, 'lexical_selector' ),
									'value'       => $get_value( 'lexical_form' ),
								],
								'lexical_data'         => [
									'id'    => $make_option_id( $id, 'lexical_data' ),
									'value' => $get_value( 'lexical_data' ) ?: [],
								],
								'active_inflections'   => [
									'id'    => $make_option_id( $id, 'active_inflections' ),
									'value' => $get_value( 'active_inflections' ),
								],
								'active_synonyms'      => [
									'id'    => $make_option_id( $id, 'active_synonyms' ),
									'value' => $get_value( 'active_synonyms' ),
								],
								'definition_selection' => [
									'id'          => $make_option_id( $id, 'definition_selection' ),
									'selector_id' => $make_option_id( $id, 'definition_dropdown' ),
									'value'       => $get_value( 'definition_selection' ),
								],
								'inflection_data'      => [
									'id'    => $make_option_id( $id, 'inflection_data' ),
									'value' => $get_value( 'inflection_data' ) ?: [],
								],
								'synonym_data'         => [
									'id'    => $make_option_id( $id, 'synonym_data' ),
									'value' => $get_value( 'synonym_data' ) ?: [],
								],
								'score'                => [
									'id'    => $make_option_id( $id, 'score' ),
									'value' => $get_value( 'score' ) ?: [],
								],
							],
						]
					);
				endforeach;
			InpostHTML::wrap_flex( 'content-close', '' );
		InpostHTML::wrap_flex( 'block-close', '' );
	// phpcs:enable, Generic.WhiteSpace.ScopeIndent.IncorrectExact
