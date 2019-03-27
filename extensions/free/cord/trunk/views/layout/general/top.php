<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Cord\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-title tsfem-flex tsfem-flex-row">
	<header><h1>
		<?php
		$image = [
			'svg' => TSFEM_E_CORD_DIR_URL . 'lib/images/cordlogo.svg',
			//'2x' => TSFEM_E_CORD_DIR_URL . 'lib/images/cordlogo-58x58.png',
			'1x' => TSFEM_E_CORD_DIR_URL . 'lib/images/cordlogo-29x29px.png',
		];
		$size = '1em';

		printf(
			'<span class="tsfem-logo">%s%s</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				\esc_attr( $size ),
				sprintf(
					'<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" src="%2$s" width="%3$s" height="%3$s" alt="extension-icon"></image>',
					\esc_url( $image['svg'], [ 'http', 'https' ] ),
					\esc_url( $image['1x'], [ 'http', 'https' ] ),
					\esc_attr( $size )
				)
			),
			'Cord'
		);
		?>
	</h1></header>
</div>
<?php
