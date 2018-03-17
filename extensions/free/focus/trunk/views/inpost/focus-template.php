<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Admin\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */
namespace TSF_Extension_Manager\Extension\Focus;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Focus\get_active_class() and $this instanceof $_class or die;

/**
* Imports HTML.
*/
// use \TSF_Extension_Manager\HTML as HTML;

/*  data-active="<?php echo (int) $is_active; ?>" */

//! START TEMP.

// TODO: use this.
// $max_score = 0;
// $current_score = 0;
// foreach ( $_scores as $type => $data ) {
// 	$max_score += $data['maxScore'];
// 	$current_score += $get_score_value( $type );
// }

// /**
//  * @param array $a
//  * @param int $value
//  */
// $_get_nearest_numeric_index_value = function( array $a, $value ) {
// 	ksort( $a, SORT_NATURAL );
// 	$ret = null;
// 	foreach ( $a as $k => $v ) {
// 		if ( is_numeric( $k ) ) {
// 			if ( $k <= $value ) {
// 				$ret = $v;
// 			} else {
// 				break;
// 			}
// 		}
// 	}
// 	return isset( $ret ) ? $ret : array_values( $array )[0];
// };
// $_get_icon_class = function( array $ratings, $value ) use ( $_get_nearest_numeric_index_value ) {
// 	$index = $_get_nearest_numeric_index_value( $ratings, $value );
// 	$classes = [
// 		-1 => 'tsfem-e-inpost-icon-error', // reserved, unused.
// 		0  => 'tsfem-e-inpost-icon-unknown',
// 		1  => 'tsfem-e-inpost-icon-bad',
// 		2  => 'tsfem-e-inpost-icon-warning',
// 		3  => 'tsfem-e-inpost-icon-okay',
// 		4  => 'tsfem-e-inpost-icon-good',
// 	];
// 	return isset( $classes[ $index ] ) ? $classes[ $index ] : $classes[0];
// };
// $_get_score_percentage = function( $max, $current ) {
// 	return round( $current / $max * 100 );
// };
// $_score_ratings = [
// 	0 => 1,
// 	20 => 1,
// 	25 => 2,
// 	50 => 3,
// 	75 => 4,
// ];
// $score_icon_class = $keyword['value']
// 	? $_get_icon_class( $_score_ratings, $_get_score_percentage( $score['max'] ?? 1200, $score['value'] ) )
// 	: 'tsfem-e-inpost-icon-unknown';

// Leftover score accumulator:
// printf(
// 	'<span class="tsfem-e-focus-score-wrap tsf-tooltip-wrap">%s%s</span>',
// 	sprintf(
// 		'<span class="%1$s" title="%2$s" data-desc="%2$s"></span>',
// 		\esc_attr( implode( ' ', [ 'tsfem-e-focus-score-item', 'tsfem-e-inpost-icon', $score_icon_class, 'tsf-tooltip-item' ] ) ),
// 		\esc_attr__( 'Subject rating.', 'the-seo-framework-extension-manager' )
// 	),
// 	sprintf(
// 		'<input type=hidden id=%s value="%s">',
// 		\esc_attr( $score['id'] ),
// 		\esc_attr( $score['value'] )
// 	)
// );

//! END TEMP.

// TODO create group for input / buttons, so they nicely collapse.

?>
<div class=tsfem-e-focus-collapse-wrap id=<?php echo \esc_attr( $wrap_ids['collapse'] ); ?>>
	<?php
	printf(
		'<input type=checkbox id=%s value="1" checked class=tsfem-e-focus-collapse-checkbox>',
		\esc_attr( $action_ids['collapser'] )
	);
	?>
	<div class="tsfem-e-focus-collapse-header tsfem-e-focus-header tsf-flex" id=<?php echo \esc_attr( $wrap_ids['header'] ); ?>>
		<div class="tsfem-e-focus-collapse-header-row tsf-flex">
			<?php
			printf(
				'<input type=text name=%1$s id=%1$s value="%2$s" class=tsfem-e-focus-keyword-entry placeholder="%3$s" autocomplete=off>',
				\esc_attr( $post_input['keyword']['id'] ),
				\esc_attr( $post_input['keyword']['value'] ),
				$supportive
					? \esc_attr__( 'Supporting keyword...', 'the-seo-framework-extension-manager' )
					: \esc_attr__( 'Keyword...', 'the-seo-framework-extension-manager' )
			);
			if ( $is_premium ) {
				//?! TODO make these visible for non-premium users regardless?
				//? It's useless as they require API; aside from showing capabilities.
				printf(
					sprintf(
						'%s%s',
						'<input type=hidden id=%1$s name=%1$s value="%2$s" id=%3$s>',
						sprintf(
							'<select value="%%2$s" id=%%3$s class="%s" disabled>%%4$s</select>',
							\esc_attr( implode( ' ', [
								'tsfem-e-focus-definition-selection',
								'tsfem-e-focus-enable-if-js',
								'tsfem-e-focus-requires-javascript',
							] ) )
						)
					),
					\esc_attr( $post_input['lexical_form']['id'] ),
					\esc_attr( $post_input['lexical_form']['value'] ),
					\esc_attr( $post_input['lexical_form']['selector_id'] ),
					\TSF_Extension_Manager\HTML::make_dropdown_option_list( json_decode( $post_input['lexical_data']['value'], true ), $post_input['lexical_form']['value'] ?: '' )
				);
				printf(
					'<input type=hidden id=%s value="%s">',
					\esc_attr( $post_input['lexical_data']['id'] ),
					\esc_attr( $post_input['lexical_data']['value'] )
				);
			}
			?>
		</div>
		<div class="tsfem-e-focus-collapse-header-row tsf-flex">
			<?php
			if ( $is_premium ) {
				printf(
					'<span class="%s">%s</span>',
					\esc_attr( implode( ' ', [
						'tsfem-e-focus-edit-subject-button-wrap',
						'tsfem-e-focus-edit-subject-button-wrap-disabled',
						'tsfem-e-focus-requires-javascript',
						'tsf-tooltip-wrap',
					] ) ),
					vsprintf(
						'<label class="%s" title="%s" data-desc="%s">%s</label>',
						[
							\esc_attr( implode( ' ', [
								'tsfem-e-focus-edit-subject',
								'tsfem-e-inpost-icon',
								'tsfem-e-inpost-icon-edit',
								'tsf-tooltip-item',
							] ) ),
							\esc_attr__( 'Adjusting the subject requires JavaScript', 'the-seo-framework-extension-manager' ),
							\esc_attr__( 'Adjust subject inflections and synonyms.', 'the-seo-framework-extension-manager' ),
							sprintf(
								'<input type=checkbox id=%s class="tsfem-e-focus-edit-subject-checkbox" value="1" disabled>',
								\esc_attr( $action_ids['subject_edit'] )
							),
						]
					)
				);
			}
			printf(
				'<span class="%s">%s</span>',
				\esc_attr( implode( ' ', [
					'tsfem-e-focus-highlight-subject-wrap',
					'tsfem-e-focus-highlight-subject-wrap-disabled',
					'tsfem-e-focus-requires-javascript',
					'tsf-tooltip-wrap',
				] ) ),
				vsprintf(
					'<label class="%s" title="%s" data-desc="%s">%s</label>',
					[
						\esc_attr( implode( ' ', [
							'tsfem-e-focus-highlight-subject',
							'tsfem-e-focus-requires-javascript',
							'tsf-tooltip-item',
						] ) ),
						\esc_attr__( 'Highlighting requires JavaScript', 'the-seo-framework-extension-manager' ),
						\esc_attr__( 'Highlight evaluated keyword, inflections, and synonyms.', 'the-seo-framework-extension-manager' ),
						sprintf(
							'<input type=checkbox class="tsfem-e-focus-highlight-subject-checkbox" value="1" disabled>',
							\esc_attr( $action_ids['highlighter'] )
						),
					]
				)
			);
			printf(
				'<label class="tsfem-e-focus-arrow-label" for=%s title="%s">%s</label>',
				\esc_attr( $action_ids['collapser'] ), //* @see first checkbox
				\esc_attr__( 'View analysis', 'the-seo-framework-extension-manager' ),
				'<span class="tsf-tooltip-item tsfem-e-focus-arrow-item"></span>'
			);
			?>
		</div>
	</div>
	<div class=tsfem-e-focus-collapse-content-wrap id=<?php echo \esc_attr( $wrap_ids['content'] ); ?>>
		<div class=tsfem-e-focus-content-loader><div class=tsfem-e-focus-content-loader-bar></div></div>
		<div class=tsfem-e-focus-collapse-content>
			<div class=tsfem-e-focus-subject id=<?php echo \esc_attr( $wrap_ids['edit'] ); ?> style=display:none>
				<?php
				printf(
					'<input type=hidden id=%s value="%s">',
					\esc_attr( $post_input['inflection_data']['id'] ),
					\esc_attr( $post_input['inflection_data']['value'] )
				);
				printf(
					'<input type=hidden id=%s value="%s">',
					\esc_attr( $post_input['synonym_data']['id'] ),
					\esc_attr( $post_input['synonym_data']['value'] )
				);
				printf(
					vsprintf(
						'<div id=%s class="tsfem-e-focus-definition-selection-holder tsf-flex" data-option-id=%%1$s %s>%s%s</div>',
						[
							\esc_attr( $action_ids['definition_selector'] ),
							'style=display:none;',
							sprintf(
								'<strong class=tsfem-e-focus-definition-selection-title>%s</strong>',
								\esc_html__( 'Definition:', 'the-seo-framework-extension-manager' )
							),
							sprintf(
								'<div class=tsfem-e-focus-definition-selection-tool>%s%s</div>',
								'<span class="tsfem-e-focus-definition-editor tsfem-e-inpost-icon tsfem-e-inpost-icon-edit" data-for="%1$s"></span>',
								'<select id=%1$s name=%1$s class="hide-if-js" value="%2$s"></select>'
							),
						]
					),
					\esc_attr( $post_input['definition_selection']['id'] ),
					\esc_attr( $post_input['definition_selection']['value'] )
				);
				?>
				<section class="tsfem-e-focus-subject-selections-wrap tsf-flex" id=<?php echo \esc_attr( $wrap_ids['inflections'] ); ?>>
					<header><h2><?php \esc_html_e( 'Inflections', 'the-seo-framework-extension-manager' ); ?></h2></header>
					<div></div>
				</section>
				<section class="tsfem-e-focus-subject-selections-wrap tsf-flex" id=<?php echo \esc_attr( $wrap_ids['synonyms'] ); ?>>
					<header><h2><?php \esc_html_e( 'Synonyms', 'the-seo-framework-extension-manager' ); ?></h2></header>
					<div></div>
				</section>
			</div>
			<div class=tsfem-e-focus-evaluation id=<?php echo \esc_attr( $wrap_ids['evaluate'] ); ?>>
				<?php
				$this->output_score_template( compact( 'is_premium', 'is_active', 'has_keyword', 'sub_scores' ) );
				?>
			</div>
		</div>
	</div>
</div>
<?php
