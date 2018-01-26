<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */
namespace TSF_Extension_Manager\Extension\Focus;

/**
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\InpostGUI as InpostGUI;

defined( 'ABSPATH' ) and InpostGUI::verify( $_secret ) or die;

$option_index = InpostGUI::get_option_key( $post_meta['focus']['option_index'], $post_meta['pm_index'] );
$make_option_id = function( $id, $key ) use ( $option_index ) {
	return sprintf( '%s[%s][%s]', $option_index, $id, $key );
};

create_analysis_field :;
	$focus_title = sprintf( '<div><strong>%s</strong></div>', $post_meta['focus']['label']['title'] );
	$focus_info = sprintf( '<div>%s</div>', \the_seo_framework()->make_info(
		$post_meta['focus']['label']['desc'],
		$post_meta['focus']['label']['link'],
		false
	) );
	$focus_label = $focus_title . $focus_info;

	analysis_fields_output :;
		InpostGUI::wrap_flex( 'block-open', '' );
			InpostGUI::wrap_flex( 'label', $focus_label );
			InpostGUI::wrap_flex( 'content-open', '' );
				foreach ( $post_meta['focus']['values'] as $id => $values ) :
					// if ( $values['active'] ) { // var_dump()
						call_user_func(
							$template_cb, [
								'is_premium' => $is_premium,
								'keyword' => [
									'id' => $make_option_id( $id, 'keyword' ),
									'value' => $values['keyword'],
								],
								'subject' => [
									'id' => $make_option_id( $id, 'subject' ),
									'value' => $values['subject'],
									'options' => $values['keywords'],
								],
								'subject_edit' => [
									'id' => sprintf( 'tsfem-e-local-subject-edit-%s', $id ),
								],
								'score' => [
									'id' => $make_option_id( $id, 'score' ),
									'value' => $values['score'],
								],
								'highlighter' => [
									'id' => sprintf( 'tsfem-e-local-highligher-%s', $id ),
								],
								'collapse' => [
									'id' => sprintf( 'tsfem-e-local-collapse-%s', $id ),
								],
							]
						);
					// }
				endforeach;
			InpostGUI::wrap_flex( 'content-close', '' );
		InpostGUI::wrap_flex( 'block-close', '' );
