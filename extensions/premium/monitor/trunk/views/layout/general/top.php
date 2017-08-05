<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Monitor\get_active_class() and $this instanceof $_class or die;

$about = '';
$actions = '';

if ( $options ) {
	//* TODO
} else {
	$info = \__( 'Let SEO Monitor help you improve your website. Your privacy is respected, read how below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation tsfem-flex tsfem-flex-row"><div>' . \esc_html( $info ) . '</div></div>';
}

?>
<section class="tsfem-top-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-flex-space">
	<div class="tsfem-title tsfem-flex tsfem-flex-row">
		<header><h1>
			<?php
			$image = [
				'svg' => TSFEM_E_MONITOR_DIR_URL . 'lib/images/monitorlogo.svg',
				//'2x' => TSFEM_E_MONITOR_DIR_URL . 'lib/images/monitorlogo-58x58.png',
				'1x' => TSFEM_E_MONITOR_DIR_URL . 'lib/images/monitorlogo-29x29px.png',
			];
			$size = '1em';

			printf(
				\esc_html_x( '%1$s %2$s', '1: SEO, 2: Monitor', 'the-seo-framework-extension-manager' ),
				sprintf(
					'<span class="tsfem-logo">%sSEO</span>',
					sprintf(
						'<svg width="%1$s" height="%1$s">%2$s</svg>',
						\esc_attr( $size ),
						sprintf(
							'<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" src="%2$s" width="%3$s" height="%3$s" alt="extension-icon"></image>',
							\esc_url( $image['svg'], [ 'http', 'https' ] ),
							\esc_url( $image['1x'], [ 'http', 'https' ] ),
							\esc_attr( $size )
						)
					)
				),
				\esc_html__( 'Monitor', 'the-seo-framework-extension-manager' ) . ' <em>beta</em>'
			);
			?>
		</h1></header>
	</div>
	<?php
	//* Already escaped.
	echo $about, $actions;
	?>
</section>
<?php

$this->after_top_wrap();
