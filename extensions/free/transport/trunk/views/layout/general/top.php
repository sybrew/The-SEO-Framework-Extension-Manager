<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret );

?>
<div class="tsfem-title">
	<header><h1>
		<?php
		$image = [
			'svg' => TSFEM_E_TRANSPORT_DIR_URL . 'lib/images/icon.svg',
			'1x'  => TSFEM_E_TRANSPORT_DIR_URL . 'lib/images/icon-29x29px.png',
		];
		$size  = '1em';

		printf(
			'<span class="tsfem-logo">%sTransport</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				esc_attr( $size ),
				sprintf(
					'<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" src="%2$s" width="%3$s" height="%3$s"></image>',
					esc_url( $image['svg'], [ 'https', 'http' ] ),
					esc_url( $image['1x'], [ 'https', 'http' ] ),
					esc_attr( $size )
				)
			)
		);
		?>
	</h1></header>
</div>
<?php
