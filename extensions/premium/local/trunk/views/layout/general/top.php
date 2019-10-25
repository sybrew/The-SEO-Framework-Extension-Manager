<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Local\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-title">
	<header><h1>
		<?php
		$image = [
			'svg' => TSFEM_E_LOCAL_DIR_URL . 'lib/images/icon.svg',
			//'2x' => TSFEM_E_LOCAL_DIR_URL . 'lib/images/icon-58x58.png',
			'1x' => TSFEM_E_LOCAL_DIR_URL . 'lib/images/icon-29x29px.png',
		];
		$size = '1em';

		printf(
			'<span class="tsfem-logo">%sLocal</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				\esc_attr( $size ),
				sprintf(
					'<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" src="%2$s" width="%3$s" height="%3$s"></image>',
					\esc_url( $image['svg'], [ 'https', 'http' ] ),
					\esc_url( $image['1x'], [ 'https', 'http' ] ),
					\esc_attr( $size )
				)
			)
		);
		?>
	</h1></header>
</div>
<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">
	<?php
	echo $this->get_test_button();
	echo $this->get_form()->_form_button( 'submit', \__( 'Save', 'the-seo-framework-extension-manager' ), 'get' );
	?>
</div>
<?php
