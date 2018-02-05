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

?>
<div class=tsfem-e-focus-collapse-wrap>
	<?php
	printf(
		'<input type="checkbox" id=%s value="1" checked class=tsfem-e-focus-collapse-checkbox>',
		\esc_attr( $collapse['id'] )
	);
	?>
	<div class="tsfem-e-focus-collapse-header tsfem-e-focus-header">
		<?php
		printf(
			'<input type=text name=%1$s id=%1$s value="%2$s" class=tsfem-e-focus-keyword-entry placeholder="%3$s" autocomplete=off>',
			\esc_attr( $keyword['id'] ),
			\esc_attr( $keyword['value'] ),
			$supportive
				? \esc_attr__( 'Enter supportive keyword...', 'the-seo-framework-extension-manager' )
				: \esc_attr__( 'Enter keyword...', 'the-seo-framework-extension-manager' )
		);
		if ( $is_premium ) {
			//?! TODO make these visible for non-premium users regardless?
			//? It's useless as they require API; aside from showing capabilities.
			printf(
				sprintf(
					'%s%s',
					'<select name=%1$s id=%1$s value="%2$s" class=tsfem-e-focus-enable-if-js disabled>%3$s</select>',
					'<input type=hidden class=tsfem-e-focus-disable-if-js name=%1$s value="%2$s">'
				),
				\esc_attr( $subject['id'] ),
				\esc_attr( $subject['value'] ),
				\TSF_Extension_Manager\HTML::make_dropdown_option_list( $subject['options'], $subject['value'] ?: '' )
			);
			printf(
				'<button class="hide-if-no-js tsf-tooltip-wrap tsfem-e-focus-edit-subject-button" type=button id=%s>%s</button>',
				\esc_attr( $subject_edit['id'] ),
				sprintf(
					'<span class="tsf-tooltip-item" title="%1$s" data-desc="%1$s"></span>',
					\esc_attr__( 'Edit subject', 'the-seo-framework-extension-manager' )
				)
			);
		}
		printf(
			'<span class=tsfem-e-focus-score id=%s>%s</span>',
			\esc_attr( $score['id'] ),
			\esc_attr( $score['value'] )
		);
		printf(
			'<label class="tsfem-e-focus-highligher tsfem-e-focus-highlighter-disabled" title="%s"><input type=checkbox id=%s value="1"></label>',
			\esc_attr__( 'Highlighting requires JavaScript', 'the-seo-framework-extension-manager' ),
			\esc_attr( $highlighter['id'] )
		);
		printf(
			'<label class="tsfem-e-focus-arrow-label tsf-tooltip-wrap" for=%s>%s</label>',
			\esc_attr( $collapse['id'] ), // @see first checkbox
			sprintf(
				'<span class="tsf-tooltip-item tsfem-e-focus-arrow-wrap" title="%1$s" data-desc="%1$s">%2$s</span>',
				\esc_attr__( 'View analysis.', 'the-seo-framework-extension-manager' ),
				'<span class=tsfem-e-focus-arrow-item></span>'
			)
		);
		?>
	</div>
	<div class="tsfem-e-focus-collapse-content-wrap">
		<div class="tsfem-e-focus-collapse-content">
			<?php
			$this->output_score_template( compact( 'is_premium', 'is_active', 'sub_scores' ) );
			?>
		</div>
	</div>
</div>
<?php
