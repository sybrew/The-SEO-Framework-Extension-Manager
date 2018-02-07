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
		InpostGUI::wrap_flex( 'block-open', '', 'tsfem-e-focus-analysis-wrap' );
			InpostGUI::wrap_flex( 'label', $focus_label );
			InpostGUI::wrap_flex( 'content-open', '' );
				$i = 0;
				foreach ( $post_meta['kw']['values'] as $id => $values ) :
					call_user_func(
						$template_cb, [
							'supportive' => (bool) $i++,
							'is_premium' => $is_premium,
							'keyword' => [
								'id' => $make_option_id( $id, 'keyword' ),
								'value' => $values['keyword'],
							],
							'subject' => [
								'id' => $make_option_id( $id, 'subject' ),
								'value' => $values['subject'],
								'options' => $values['subjects'],
							],
							'collapse' => [
								'id' => sprintf( 'tsfem-e-local-collapse-%s', $id ),
							],
							'subject_edit' => [
								'id' => $make_option_id( $id, 'subject-edit' ),
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
			InpostGUI::wrap_flex( 'content-close', '' );
		InpostGUI::wrap_flex( 'block-close', '' );
