<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Focus\get_active_class() and $this instanceof $_class or die;

/**
* Imports HTML.
*/
use \TSF_Extension_Manager\HTML as HTML;

?>
<div class=tsfem-e-local-collapse-wrap>
	<div class="tsfem-e-local-collapse-header tsfem-e-local-focus-header">
		<?php
		printf(
			'<input type=text name=%1$s id=%1$s value="%2$s" placeholder="%3$s" autocomplete=off>',
			\esc_attr( $keyword['id'] ),
			\esc_attr( $keyword['value'] ),
			\esc_attr__( 'Enter keyword...', 'the-seo-framework-extension-manager' )
		);
		if ( $is_premium ) {
			//= TODO make these visible for non-premium users regardless?
			// It's useless as they require API; aside from showing capabilities.
			printf(
				sprintf(
					'%s%s',
					'<select name=%1$s id=%1$s value="%2$s" class=tsfem-e-local-enable-if-js disabled>%3$s</select>',
					'<input type=hidden class=tsfem-e-local-disable-if-js name=%1$s value="%2$s">'
				),
				\esc_attr( $subject['id'] ),
				\esc_attr( $subject['value'] ),
				HTML::make_dropdown_option_list( $subject['options'], $subject['value'] ?: '' )
			);
			printf(
				'<button class="hide-if-no-js tsfem-e-local-edit-subject-button" type=button id=%s>%s</button>',
				\esc_attr( $subject_edit['id'] ),
				sprintf(
					'<span class="tsf-tooltip-item tsfem-tooltip" title="%1$s" data-desc="%1$s"></span>',
					\esc_attr__( 'Edit subject', 'the-seo-framework-extension-manager' )
				)
			);
		}
		printf(
			'<span class=tsfem-e-local-score id=%s>%s</span>',
			\esc_attr( $score['id'] ),
			\esc_attr( $score['value'] )
		);
		printf(
			'<input class=tsfem-e-local-highlighter type=checkbox id=%s value="1">',
			\esc_attr( $highlighter['id'] )
		);
		printf(
			'<label class="tsfem-e-local-focus-arrow tsf-tooltip-wrap" for=%s>%s</label>',
			\esc_attr( $collapse['id'] ), // @see checkbox below.
			sprintf(
				'<span class="tsf-tooltip-item tsfem-tooltip" title="%1$s" data-desc="%1$s"></span>',
				\esc_attr__( 'View analysis.', 'the-seo-framework-extension-manager' )
			)
		);
		?>
	</div>
	<?php
	printf(
		'<input type="checkbox" id=%s value="1">',
		\esc_attr( $collapse['id'] )
	);
	?>
	<div class="tsfem-e-local-collapse-content">
		Content here.
	</div>
</div>
<?php
