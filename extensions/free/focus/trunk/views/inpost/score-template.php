<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Admin\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */
namespace TSF_Extension_Manager\Extension\Focus;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Focus\get_active_class() and $this instanceof $_class or die;

$scoring = Scoring::get_instance();

$scoring->key = $sub_scores['key'];
$scoring->values = $sub_scores['values'];

printf(
	'<span class="hide-if-js attention">%s %s</span>',
	\esc_html__( 'JavaScript is required to perform a subject analysis.', 'the-seo-framework-extension-manager' ),
	$has_keyword ? \esc_html__( 'Below you find the previous assessments.', 'the-seo-framework-extension-manager' ) : ''
);
printf(
	'<span class="tsfem-e-focus-no-keyword-wrap hide-if-no-js attention" %s>%s</span>',
	$has_keyword ? 'style=display:none' : '',
	\esc_html__( 'No keyword has been set, so no analysis can be made.', 'the-seo-framework-extension-manager' )
);
printf(
	'<span class="tsfem-e-focus-something-wrong-wrap hide-if-no-js attention" %s>%s</span>',
	'style=display:none',
	\esc_html__( 'Something went wrong evaluating the subject.', 'the-seo-framework-extension-manager' )
);

output_scores :;
	printf(
		'<div class="tsfem-e-focus-scores-wrap tsfem-flex" id=%s %s>',
		\esc_attr( $scoring->key ),
		$has_keyword ? '' : 'style="display:none"'
	);
	foreach ( $scoring->get_template() as $type => $args ) :
		//! All output below should already be escaped.
		vprintf(
			'<span id=%s class="%s" %s>%s%s</span>',
			[
				\esc_attr( $scoring->get_id( $type ) ),
				'tsfem-e-focus-assessment-wrap tsf-flex',
				$scoring->get_data_attributes( $type ), // already escaped.
				sprintf(
					'<span class=tsfem-e-focus-assessment-title-wrap>%s%s</span>',
					sprintf(
						'<span class="tsfem-e-focus-assessment-rating tsfem-e-inpost-icon %s"></span>',
						\esc_attr( $scoring->get_icon_class( $type ) )
					),
					sprintf(
						'<strong class=tsfem-e-focus-assessment-title>%s</strong>',
						\esc_html( $scoring->get_title( $type ) )
					)
				),
				sprintf(
					'<span class=tsfem-e-focus-assessment-description>%s</span>',
					\esc_html( $scoring->get_description( $type ) )
				),
			]
		);
		//= Data capturer.
		printf(
			'<input type=hidden name=%s value="%s">',
			\esc_attr( $scoring->get_id( $type ) ),
			\esc_attr( $scoring->get_value( $type ) )
		);
	endforeach;
	echo '</div>'; //= END tsfem-e-focus-scores-wrap;
