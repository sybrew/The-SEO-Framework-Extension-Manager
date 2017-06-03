<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

$about = '';
$actions = '';

if ( $options ) {

	if ( $this->is_plugin_activated() && $this->is_premium_user() ) {

		$status = $this->get_subscription_status();

		$account_url = $this->get_activation_url();
		$account_button_class = 'tsfem-button-primary-bright tsfem-button-star';
		$account_text = \__( 'My Account', 'the-seo-framework-extension-manager' );
		$account_title = \__( 'View account', 'the-seo-framework-extension-manager' );

		if ( isset( $status['data']['end_date'] ) ) {
			//* UTC.
			$then = strtotime( $status['data']['end_date'] );
			$in_four_weeks = strtotime( '+4 week' );
			$about_to_expire = $then < $in_four_weeks;

			if ( $about_to_expire ) {
				$account_button_class = 'tsfem-button-red tsfem-button-warning';
				$account_title = \__( 'Extend license', 'the-seo-framework-extension-manager' );
			}
		}
	} else {
		$account_url = $this->get_activation_url( 'shop/premium-subscription/' );
		$account_button_class = 'tsfem-button-green tsfem-button-love';
		$account_title = \__( 'Get license', 'the-seo-framework-extension-manager' );
		$account_text = \__( 'Go Premium', 'the-seo-framework-extension-manager' );
	}

	$account_link = $this->get_link( [
		'url' => $account_url,
		'target' => '_blank',
		'class' => 'tsfem-button-primary ' . $account_button_class,
		'title' => $account_title,
		'content' => $account_text,
	] );
	$account = '<div class="tsfem-top-account">' . $account_link . '</div>';

	$actions = '<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">' . $account . '</div>';
} else {
	$info = \__( 'Add more powerful SEO features to your website. To get started, use one of the options below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation tsfem-flex tsfem-flex-row"><div>' . \esc_html( $info ) . '</div></div>';
}

$extensions_i18n = \__( 'Extensions', 'the-seo-framework-extension-manager' );

/**
 * Test for GD library functionality upon logo.
 *
 * Only runs on activation page.
 *
 * This is the first step towards "true" Google pixel guidelines character count testing.
 * Let's see how this works out :).
 */
if ( false === $this->is_plugin_activated() && extension_loaded( 'gd' ) && function_exists( 'imageftbbox' ) ) :
	$font = $this->get_font_file_location( 'LiberationSans-Regular.ttf' );
	if ( file_exists( $font ) ) :
		//* Calculate text-width. 1.9em @ 13px body. Verdana is 1.0884x Arial (LiberationSans) size.
		$tim = imageftbbox( $this->pixels_to_points( 1.9 * 13 * 1.0884 ), 0, $font, $extensions_i18n );

		$width_top = isset( $tim[2] ) ? $tim[2] : 0;
		$width_bot = isset( $tim[4] ) ? $tim[4] : 0;
		//* Get largest offset.
		$width = $width_top >= $width_bot ? $width_top : $width_bot;

		//* 10px margin of error.
		if ( $width ) {
			$flex_basis = sprintf( '%spx', intval( $width + 10 ) );
		}
	endif;
endif;

?>
<section class="tsfem-top-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-flex-space">
	<?php
	//* Print style.
	isset( $flex_basis ) and printf( '<style>.tsfem-top-wrap .tsfem-title{-webkit-flex-basis:%1$s;flex-basis:%1$s}</style>', \esc_html( $flex_basis ) );
	?>
	<div class="tsfem-title tsfem-flex tsfem-flex-row">
		<header><h1>
			<?php
			$image = [
				'svg' => $this->get_image_file_location( 'tsflogo.svg', true ),
				//	'2x' => $this->get_image_file_location( 'tsflogo-58x58px.png', true ),
				'1x' => $this->get_image_file_location( 'tsflogo-29x29px.png', true ),
			];
			$size = '1em';

			printf( \esc_html_x( '%1$s %2$s', '1: SEO, 2: Extensions', 'the-seo-framework-extension-manager' ),
				sprintf( '<span class="tsfem-logo">%sSEO</span>',
					sprintf( '<svg width="%1$s" height="%1$s">%2$s</svg>',
						\esc_attr( $size ),
						sprintf( '<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" src="%2$s" width="%3$s" height="%3$s" alt="extension-icon"></image>',
							\esc_url( $image['svg'] ), \esc_url( $image['1x'] ), \esc_attr( $size )
						)
					)
				), \esc_html( $extensions_i18n )
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

$this->_after_top_wrap();
