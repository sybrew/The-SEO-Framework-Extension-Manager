<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $_class = TSF_Extension_Manager\Extension\Monitor\get_active_class() and $this instanceof $_class or die;

$about   = '';
$actions = '';

if ( $options ) { // phpcs:ignore, Generic.CodeAnalysis.EmptyStatement
	// TODO
} else {
	$info  = __( 'Let SEO Monitor help you improve your website. Your privacy is respected, read how below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation tsfem-flex tsfem-flex-row tsfem-flex-nowrap"><div>' . esc_html( $info ) . '</div></div>';
}

?>
<div class="tsfem-title">
	<header><h1>
		<?php
		$image = [
			'svg' => TSFEM_E_MONITOR_DIR_URL . 'lib/images/icon.svg',
			'1x'  => TSFEM_E_MONITOR_DIR_URL . 'lib/images/icon-29x29px.png',
		];
		$size  = '1em';

		printf(
			'<span class="tsfem-logo">%sMonitor</span>',
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

// phpcs:ignore, WordPress.Security.EscapeOutput -- Already escaped.
echo $about, $actions;
