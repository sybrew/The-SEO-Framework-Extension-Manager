<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Admin\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */
namespace TSF_Extension_Manager\Extension\Focus;

/**
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\InpostGUI as InpostGUI;
use \TSF_Extension_Manager\InpostHTML as InpostHTML;

defined( 'ABSPATH' ) and InpostGUI::verify( $_secret ) or die;

$option_index = InpostGUI::get_option_key( $post_meta['kw']['option_index'], $post_meta['pm_index'] );
$make_option_id = function( $id, $key ) use ( $option_index ) {
	return sprintf( '%s[%s][%s]', $option_index, $id, $key );
};

create_analysis_field :;
	$focus_title = sprintf( '<div><strong>%s</strong></div>', $post_meta['kw']['label']['title'] );
	$focus_info = sprintf( '<div>%s</div>', \the_seo_framework()->make_info(
		$post_meta['kw']['label']['desc'],
		$post_meta['kw']['label']['link'],
		false
	) );
	$focus_label = $focus_title . $focus_info;

	analysis_fields_output :;
		InpostHTML::wrap_flex( 'block-open', '', 'tsfem-e-focus-analysis-wrap' );
			InpostHTML::wrap_flex( 'label', $focus_label );
			InpostHTML::wrap_flex( 'content-open', '' );
				InpostHTML::notification_area( 'tsfem-e-focus-analysis-notification-area' );
				$i = 0;
				foreach ( $post_meta['kw']['values'] as $id => $values ) :
					call_user_func(
						$template_cb, [
							'supportive' => (bool) $i++, // true if 2nd or later iteration.
							'is_premium' => $is_premium,
							'wrap' => [
								'id' => $make_option_id( $id, 'wrap' ),
							],
							'collapser' => [
								'id' => sprintf( 'tsfem-e-local-collapse-%s', $id ),
							],
							'keyword' => [
								'id' => $make_option_id( $id, 'keyword' ),
								'value' => $values['keyword'],
							],
							'definition' => [
								'id' => $make_option_id( $id, 'definition' ),
								'selector_id' => $make_option_id( $id, 'definition_selector' ),
								'value' => $values['definition'],
							],
							'definition_data' => [
								'id' => $make_option_id( $id, 'definition_data' ),
								'value' => $values['definition_data'],
							],
							'subject_edit' => [
								'id' => $make_option_id( $id, 'subject_edit' ),
							],
							'inflection_data' => [
								'id' => $make_option_id( $id, 'inflection_data' ),
								'value' => $values['inflection_data'],
							],
							'synonym_data' => [
								'id' => $make_option_id( $id, 'synonym_data' ),
								'value' => $values['synonym_data'],
							],
							'score' => [
								'id' => $make_option_id( $id, 'score' ),
								'value' => $values['score'],
							],
							'sub_scores' => [
								'key' => $make_option_id( $id, 'scores' ),
								'values' => $values['scores'],
							],
							'highlighter' => [
								'id' => sprintf( 'tsfem-e-local-highlighter-%s', $id ),
							],
						]
					);
				endforeach;
			InpostHTML::wrap_flex( 'content-close', '' );
		InpostHTML::wrap_flex( 'block-close', '' );
