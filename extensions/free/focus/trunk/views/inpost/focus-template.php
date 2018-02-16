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
// 		-1 => 'tsfem-e-focus-icon-error', // reserved, unused.
// 		0  => 'tsfem-e-focus-icon-unknown',
// 		1  => 'tsfem-e-focus-icon-bad',
// 		2  => 'tsfem-e-focus-icon-warning',
// 		3  => 'tsfem-e-focus-icon-okay',
// 		4  => 'tsfem-e-focus-icon-good',
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
// 	: 'tsfem-e-focus-icon-unknown';

// Leftover score accumulator:
// printf(
// 	'<span class="tsfem-e-focus-score-wrap tsf-tooltip-wrap">%s%s</span>',
// 	sprintf(
// 		'<span class="%1$s" title="%2$s" data-desc="%2$s"></span>',
// 		\esc_attr( implode( ' ', [ 'tsfem-e-focus-score-item', 'tsfem-e-focus-icon', $score_icon_class, 'tsf-tooltip-item' ] ) ),
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
<div class=tsfem-e-focus-collapse-wrap id="<?php echo \esc_attr( $wrap['id'] ); ?>">
	<?php
	printf(
		'<input type=checkbox id=%s value="1" checked class=tsfem-e-focus-collapse-checkbox>',
		\esc_attr( $collapser['id'] )
	);
	?>
	<div class="tsfem-e-focus-collapse-header tsfem-e-focus-header tsf-flex">
		<div class="tsfem-e-focus-collapse-header-row tsf-flex">
			<?php
			printf(
				'<input type=text name=%1$s id=%1$s value="%2$s" class=tsfem-e-focus-keyword-entry placeholder="%3$s" autocomplete=off>',
				\esc_attr( $keyword['id'] ),
				\esc_attr( $keyword['value'] ),
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
						sprintf(
							'<select name=%%1$s id=%%1$s value="%%2$s" class="%s" disabled>%%3$s</select>',
							\esc_attr( implode( ' ', [
								'tsfem-e-focus-subject-selection',
								'tsfem-e-focus-enable-if-js',
								'tsfem-e-focus-requires-javascript',
							] ) )
						),
						'<input type=hidden class=tsfem-e-focus-disable-if-js name=%1$s value="%2$s">'
					),
					\esc_attr( $subject['id'] ),
					\esc_attr( $subject['value'] ),
					\TSF_Extension_Manager\HTML::make_dropdown_option_list( $subject['options'], $subject['value'] ?: '' )
				);
			}
			?>
		</div>
		<div class="tsfem-e-focus-collapse-header-row tsf-flex">
			<?php
			if ( $is_premium ) {
				printf(
					'<span class="%s" id=%s>%s</span>',
					\esc_attr( implode( ' ', [
						'tsfem-e-focus-edit-subject-wrap',
						'tsfem-e-focus-edit-subject-wrap-disabled',
						'tsfem-e-focus-requires-javascript',
						'tsf-tooltip-wrap',
					] ) ),
					\esc_attr( $subject_edit['id'] ),
					sprintf(
						'<span class="%s" title="%s" data-desc="%s"></span>',
						\esc_attr( implode( ' ', [
							'tsfem-e-focus-edit-subject',
							'tsfem-e-focus-icon',
							'tsfem-e-focus-icon-edit',
							'tsf-tooltip-item',
						] ) ),
						\esc_attr__( 'Adjusting synonyms requires JavaScript', 'the-seo-framework-extension-manager' ),
						\esc_attr__( 'Adjust synonyms.', 'the-seo-framework-extension-manager' )
					)
				);
			}
			printf(
				'<label class="%s">%s%s</label>',
				\esc_attr( implode( ' ', [
					'tsfem-e-focus-highlight-subject-wrap',
					'tsfem-e-focus-highlight-subject-wrap-disabled',
					'tsfem-e-focus-requires-javascript',
					'tsf-tooltip-wrap',
				] ) ),
				sprintf(
					'<input type=checkbox id=%s class="tsfem-e-focus-highlight-subject-checkbox" value="1">',
					\esc_attr( $highlighter['id'] )
				),
				sprintf(
					'<span class="%s" title="%s" data-desc="%s"></span>',
					\esc_attr( implode( ' ', [
						'tsfem-e-focus-highlight-subject',
						'tsfem-e-focus-requires-javascript',
						'tsf-tooltip-item',
					] ) ),
					\esc_attr__( 'Highlighting requires JavaScript', 'the-seo-framework-extension-manager' ),
					\esc_attr__( 'Highlight evaluated keywords and synonyms.', 'the-seo-framework-extension-manager' )
				)
			);
			printf(
				'<label class="tsfem-e-focus-arrow-label tsf-tooltip-wrap" for=%s>%s</label>',
				\esc_attr( $collapser['id'] ), // @see first checkbox
				sprintf(
					'<span class="tsf-tooltip-item tsfem-e-focus-arrow-item" title="%1$s" data-desc="%1$s"></span>',
					\esc_attr__( 'View analysis.', 'the-seo-framework-extension-manager' )
				)
			);
			?>
		</div>
	</div>
	<div class=tsfem-e-focus-collapse-content-wrap>
		<div class=tsfem-e-focus-content-loader><div class=tsfem-e-focus-content-loader-bar></div></div>
		<div class=tsfem-e-focus-collapse-content>
			<?php
			$this->output_score_template( compact( 'is_premium', 'is_active', 'keyword', 'sub_scores' ) );
			?>
		</div>
	</div>
</div>
<?php
