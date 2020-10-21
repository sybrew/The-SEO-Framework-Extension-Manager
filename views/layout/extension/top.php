<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Extension
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;

?>
<div class="tsfem-title">
	<header><h1>
		<?php
		$image = [
			'svg' => tsf_extension_manager()->get_image_file_location( 'tsflogo.svg', true ),
			'1x'  => tsf_extension_manager()->get_image_file_location( 'tsflogo-29x29px.png', true ),
		];
		$size  = '1em';

		printf(
			'<span class="tsfem-logo">%s%s</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				esc_attr( $size ),
				sprintf(
					'<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" src="%2$s" width="%3$s" height="%3$s"></image>',
					esc_url( $image['svg'], [ 'https', 'http' ] ),
					esc_url( $image['1x'], [ 'https', 'http' ] ),
					esc_attr( $size )
				)
			),
			esc_html__( 'Extension Settings', 'the-seo-framework-extension-manager' )
		);
		?>
	</h1></header>
</div>
<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">
	<?php
	// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- get_save_all_button() escapes.
	echo $this->get_save_all_button();
	?>
</div>
<?php
