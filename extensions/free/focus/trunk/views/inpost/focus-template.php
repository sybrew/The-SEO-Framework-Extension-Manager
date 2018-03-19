<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Admin\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */
namespace TSF_Extension_Manager\Extension\Focus;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Focus\get_active_class() and $this instanceof $_class or die;

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
								'tsfem-e-focus-lexical-selector',
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
					'<input type=hidden id=%s value="%s">',
					\esc_attr( $post_input['active_inflections']['id'] ),
					\esc_attr( $post_input['active_inflections']['value'] )
				);
				printf(
					'<input type=hidden id=%s value="%s">',
					\esc_attr( $post_input['active_synonyms']['id'] ),
					\esc_attr( $post_input['active_synonyms']['value'] )
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
						'%s<label for=%s class="%s" title="%s" data-desc="%s"></label>',
						[
							sprintf(
								'<input type=checkbox id=%s class=tsfem-e-focus-edit-subject-checkbox value="1" disabled>',
								\esc_attr( $action_ids['subject_edit'] )
							),
							\esc_attr( $action_ids['subject_edit'] ),
							\esc_attr( implode( ' ', [
								'tsfem-e-focus-edit-subject',
								'tsfem-e-inpost-icon',
								'tsfem-e-inpost-icon-edit',
								'tsf-tooltip-item',
							] ) ),
							\esc_attr__( 'Adjusting the subject requires JavaScript', 'the-seo-framework-extension-manager' ),
							\esc_attr__( 'Adjust subject inflections and synonyms.', 'the-seo-framework-extension-manager' ),
						]
					)
				);
			} else {
				//= TEMP until highligher comes.
				print '<span class=tsfem-e-focus-pusher></span>';
			}
			// TODO: upcoming version.
			// printf(
			// 	'<span class="%s">%s</span>',
			// 	\esc_attr( implode( ' ', [
			// 		'tsfem-e-focus-highlight-subject-button-wrap',
			// 		'tsfem-e-focus-highlight-subject-button-wrap-disabled',
			// 		'tsfem-e-focus-requires-javascript',
			// 		'tsf-tooltip-wrap',
			// 	] ) ),
			// 	vsprintf(
			// 		'%s<label for=%s class="%s" title="%s" data-desc="%s"></label>',
			// 		[
			// 			sprintf(
			// 				'<input type=checkbox id=%s class=tsfem-e-focus-highlight-subject-checkbox value="1" disabled>',
			// 				\esc_attr( $action_ids['highlighter'] )
			// 			),
			// 			\esc_attr( $action_ids['highlighter'] ),
			// 			\esc_attr( implode( ' ', [
			// 				'tsfem-e-focus-highlight-subject',
			// 				'tsfem-e-focus-requires-javascript',
			// 				'tsf-tooltip-item',
			// 			] ) ),
			// 			\esc_attr__( 'Highlighting requires JavaScript', 'the-seo-framework-extension-manager' ),
			// 			\esc_attr__( 'Highlight evaluated keyword, inflections, and synonyms.', 'the-seo-framework-extension-manager' ),
			// 		]
			// 	)
			// );
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
			<?php
			if ( $is_premium ) :
			?>
			<div class=tsfem-e-focus-subject id=<?php echo \esc_attr( $wrap_ids['edit'] ); ?> style=display:none>
				<?php
				printf(
					vsprintf(
						'<div id=%s class="tsfem-e-focus-definition-selection-holder tsf-flex" data-option-id=%%1$s %s>%s%s</div>',
						[
							\esc_attr( $action_ids['definition_selector'] ),
							'style=display:none;',
							sprintf(
								'<strong class=tsfem-e-focus-definition-selection-title>%s</strong>',
								\esc_html__( 'Homonymous example:', 'the-seo-framework-extension-manager' )
							),
							sprintf(
								'<div class=tsfem-e-focus-definition-selection-tool>%s%s</div>',
								'<span class="tsfem-e-focus-definition-editor tsfem-e-inpost-icon tsfem-e-inpost-icon-edit" data-for="%1$s" tabindex=0></span>',
								'<select id=%1$s name=%1$s class="hide-if-js" value="%2$s"></select>'
							),
						]
					),
					\esc_attr( $post_input['definition_selection']['id'] ),
					\esc_attr( $post_input['definition_selection']['value'] )
				);
				?>
				<div class="tsfem-e-focus-subject-selections-wrap tsf-flex" id=<?php echo \esc_attr( $wrap_ids['inflections'] ); ?>>
					<h2 class=tsfem-e-focus-subject-selection-title><?php \esc_html_e( 'Inflections', 'the-seo-framework-extension-manager' ); ?></h2>
					<div class=tsfem-e-focus-subject-selection></div>
				</div>
				<div class="tsfem-e-focus-subject-selections-wrap tsf-flex" id=<?php echo \esc_attr( $wrap_ids['synonyms'] ); ?>>
					<h2 class=tsfem-e-focus-subject-selection-title><?php \esc_html_e( 'Synonyms', 'the-seo-framework-extension-manager' ); ?></h2>
					<div class=tsfem-e-focus-subject-selection></div>
				</div>
			</div>
			<?php
			endif;
			?>
			<div class=tsfem-e-focus-evaluation id=<?php echo \esc_attr( $wrap_ids['evaluate'] ); ?>>
				<?php
				$this->output_score_template( compact( 'is_premium', 'is_active', 'has_keyword', 'sub_scores' ) );
				?>
			</div>
		</div>
	</div>
</div>
<?php
