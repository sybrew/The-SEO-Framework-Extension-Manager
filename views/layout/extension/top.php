<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Extension
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;

?>
<div class=tsfem-title>
	<header><h1>
		<?php
		$size = '1em';

		printf(
			'<span class=tsfem-logo>%s%s</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				esc_attr( $size ),
				sprintf(
					'<image href="%1$s" width="%2$s" height="%2$s" />',
					esc_url( tsfem()->get_image_file_location( 'tsflogo.svg', true ), [ 'https', 'http' ] ),
					esc_attr( $size )
				)
			),
			esc_html__( 'Extension Settings', 'the-seo-framework-extension-manager' )
		);
		?>
	</h1></header>
</div>
<?php
/* TODO
<div class="tsfem-top-actions tsfem-flex tsfem-flex-row"><?=
	// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- get_save_all_button() escapes.
	$this->get_save_all_button();
?></div>
<?php
*/
