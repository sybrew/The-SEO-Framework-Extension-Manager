<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret ) or die;

?>
<div class=tsfem-title>
	<header><h1>
		<?php
		$size = '1em';
		printf(
			'<span class=tsfem-logo>%sLocal</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				esc_attr( $size ),
				sprintf(
					'<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" width="%2$s" height="%2$s"></image>',
					esc_url( TSFEM_E_LOCAL_DIR_URL . 'lib/images/icon.svg', [ 'https', 'http' ] ),
					esc_attr( $size )
				)
			)
		);
		?>
	</h1></header>
</div>
<div class="tsfem-top-actions tsfem-flex tsfem-flex-row"><?=
	// phpcs:ignore, WordPress.Security.EscapeOutput -- Already escaped.
	$this->get_test_button(), $this->get_form()->_form_button( 'submit', __( 'Save', 'the-seo-framework-extension-manager' ), 'get' )
?></div>
<?php
