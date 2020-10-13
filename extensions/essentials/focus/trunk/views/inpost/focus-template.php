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
		'<input type=checkbox id=%s value="1" checked class="tsfem-e-focus-collapse-checkbox tsf-input-not-saved">',
		\esc_attr( $action_ids['collapser'] )
	);
	?>
	<div class="tsfem-e-focus-collapse-header tsfem-e-focus-header tsf-flex" id=<?php echo \esc_attr( $wrap_ids['header'] ); ?>>
		<div class="tsfem-e-focus-collapse-header-row tsf-flex">
			<?php
			printf(
				'<input type=text id=%1$s name=%1$s value="%2$s" class=tsfem-e-focus-keyword-entry placeholder="%3$s" autocomplete=off>',
				\esc_attr( $post_input['keyword']['id'] ),
				\esc_attr( $post_input['keyword']['value'] ),
				$supportive
					? \esc_attr__( 'Supporting keyword...', 'the-seo-framework-extension-manager' )
					: \esc_attr__( 'Keyword...', 'the-seo-framework-extension-manager' )
			);
			if ( $is_premium && $language_supported ) {
				// This field isn't POSTed, so we can safely remove it.
				printf(
					'<select id=%s value="%s" class="%s" disabled>%s</select>',
					\esc_attr( $post_input['lexical_form']['selector_id'] ),
					\esc_attr( $post_input['lexical_form']['value'] ),
					\esc_attr( implode( ' ', [
						'tsfem-e-focus-lexical-selector',
						'tsfem-e-focus-enable-if-js',
						'tsfem-e-focus-requires-javascript',
					] ) ),
					\TSF_Extension_Manager\HTML::make_sequential_dropdown_option_list(
						$post_input['lexical_data']['value'],
						$post_input['lexical_form']['value'] ?: ''
					)
				);
			}
			/**
			 * Fields that need resaving and reprocessing as the user engages.
			 * This ensures a smooth "premium -> free -> premium" experience without
			 * data loss.
			 */
			foreach (
				\tsf_extension_manager()->filter_keys( $post_input, [ 'lexical_data', 'inflection_data', 'synonym_data' ] )
				as $hidden_input
			) {
				vprintf(
					'<input type=hidden id="%s" name=%s value="%s">',
					[
						$is_premium ? \esc_attr( $hidden_input['id'] ) : '',
						\esc_attr( $hidden_input['id'] ),
						\esc_attr( json_encode( $hidden_input['value'] ) ),
					]
				);
			}
			foreach (
				\tsf_extension_manager()->filter_keys( $post_input, [ 'lexical_form', 'active_inflections', 'active_synonyms', 'definition_selection' ] )
				as $hidden_input
			) {
				vprintf(
					'<input type=hidden id="%s" name=%s value="%s">',
					[
						$is_premium ? \esc_attr( $hidden_input['id'] ) : '',
						\esc_attr( $hidden_input['id'] ),
						\esc_attr( $hidden_input['value'] ),
					]
				);
			}
			?>
		</div>
		<div class="tsfem-e-focus-collapse-header-row tsf-flex">
			<?php
			if ( $is_premium ) {
				$_tooltip = $language_supported
					? \__( 'Adjust subject inflections and synonyms.', 'the-seo-framework-extension-manager' )
					: \__( 'The current site language has no support for lexical adjustments.', 'the-seo-framework-extension-manager' );

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
								'<input type=checkbox id=%s class="tsfem-e-focus-edit-subject-checkbox tsf-input-not-saved" value="1" disabled>',
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
							\esc_attr( $_tooltip ),
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
			// 				'<input type=checkbox id=%s class="tsfem-e-focus-highlight-subject-checkbox tsf-input-not-saved" value="1" disabled>',
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
			<?php if ( $is_premium ) : ?>
			<div class=tsfem-e-focus-subject id=<?php echo \esc_attr( $wrap_ids['edit'] ); ?> style=display:none>
				<?php
				printf(
					vsprintf(
						'<div id=%s class="tsfem-e-focus-definition-selection-holder tsf-flex" data-option-id=%%1$s>%s%s</div>',
						[
							\esc_attr( $action_ids['definition_selector'] ),
							sprintf(
								'<strong class=tsfem-e-focus-definition-selection-title>%s</strong>',
								//! It's syntactically homographic, but that might trigger complications due to the pronouncedly nature...
								\esc_html__( 'Choose homonymous example:', 'the-seo-framework-extension-manager' )
							),
							sprintf(
								'<div class=tsfem-e-focus-definition-selection-tool>%s%s</div>',
								'<span class="tsfem-e-focus-definition-editor tsfem-e-inpost-icon tsfem-e-inpost-icon-edit" data-for="%1$s" tabindex=0></span>',
								'<select id=%1$s name=%1$s class="hide-if-js" value="%2$s"></select>'
							),
						]
					),
					\esc_attr( $post_input['definition_selection']['selector_id'] ),
					\esc_attr( $post_input['definition_selection']['value'] )
				);
				?>
				<div class="tsfem-e-focus-subject-selections-wrap tsf-flex" id=<?php echo \esc_attr( $wrap_ids['inflections'] ); ?>>
					<h2 class=tsfem-e-focus-subject-selection-title><?php \esc_html_e( 'Choose inflections', 'the-seo-framework-extension-manager' ); ?></h2>
					<div class=tsfem-e-focus-subject-selection></div>
				</div>
				<div class="tsfem-e-focus-subject-selections-wrap tsf-flex" id=<?php echo \esc_attr( $wrap_ids['synonyms'] ); ?>>
					<h2 class=tsfem-e-focus-subject-selection-title><?php \esc_html_e( 'Choose synonyms', 'the-seo-framework-extension-manager' ); ?></h2>
					<div class=tsfem-e-focus-subject-selection></div>
				</div>
			</div>
			<?php endif; ?>
			<div class=tsfem-e-focus-evaluation id=<?php echo \esc_attr( $wrap_ids['evaluate'] ); ?>>
				<?php
				$this->output_score_template( compact( 'is_premium', 'has_keyword', 'sub_scores' ) );
				?>
			</div>
		</div>
	</div>
</div>
<?php
