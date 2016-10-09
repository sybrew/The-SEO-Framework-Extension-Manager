<?php
defined( 'ABSPATH' ) and tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

$about = '';
$actions = '';

if ( $options ) {

	if ( $this->is_plugin_activated() && $this->is_premium_user() ) {

		$status = $this->get_subscription_status();

		$account_url = $this->get_activation_url( 'my-account/' );
		$account_button_class = 'tsfem-account-active';
		$account_text = __( 'My Account', 'the-seo-framework-extension-manager' );
		$account_title = __( 'View account', 'the-seo-framework-extension-manager' );

		if ( isset( $status['data']['end_date'] ) ) {
			//* UTC.
			$then = strtotime( $status['data']['end_date'] );
			$in_four_weeks = strtotime( '+4 week' );
			$about_to_expire = $then < $in_four_weeks;

			if ( $about_to_expire ) {
				$account_button_class = 'tsfem-account-about-to-expire';
				$account_title = __( 'Extend license', 'the-seo-framework-extension-manager' );
			}
		}
	} else {
		$account_url = $this->get_activation_url( 'get/' );
		$account_button_class = 'tsfem-account-inactive';
		$account_title = __( 'Get license', 'the-seo-framework-extension-manager' );
		$account_text = __( 'Go Premium', 'the-seo-framework-extension-manager' );
	}

	$account_link = $this->get_link( array( 'url' => $account_url, 'target' => '_blank', 'class' => 'tsfem-button-primary ' . $account_button_class, 'title' => $account_title, 'content' => $account_text ) );
	$account = '<div class="tsfem-top-account">' . $account_link . '</div>';

	$actions = '<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">' . $account . '</div>';
} else {
	$info = __( 'Add more powerful SEO features to your website. To get started, use one of the options below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation tsfem-flex tsfem-flex-row"><div>' . esc_html( $info ) . '</div></div>';
}

$extensions_i18n = __( 'Extensions', 'the-seo-framework-extension-manager' );

/**
 * Test for GD library functionality upon logo.
 *
 * This is the first step towards "true" Google pixel guidelines character count testing.
 * Let's see how this works out :).
 */
if ( extension_loaded( 'gd' ) && function_exists( 'imageftbbox' ) ) {
	//* Calculate text-width. 1.9em @ 13px body.
	$tim = imageftbbox( $this->pixel_to_points( 1.9 * 13 ), 0, $this->get_font_file_location( 'verdana.ttf' ), $extensions_i18n );

	$width_top = isset( $tim[2] ) ? $tim[2] : 0;
	$width_bot = isset( $tim[4] ) ? $tim[4] : 0;
	//* Get largest offset.
	$width = $width_top >= $width_bot ? $width_top : $width_bot;

	//* 10px margin of error.
	if ( $width )
		$flex_basis = sprintf( '%spx', intval( $width + 10 ) );
}

?>
<section class="tsfem-top-wrap tsfem-flex tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-flex-space">
	<?php
	echo $about . $actions;
	if ( ! empty( $flex_basis ) )
		printf( '<style>.tsfem-top-wrap .tsfem-title{-webkit-flex-basis:%1$s;flex-basis:%1$s}</style>', esc_html( $flex_basis ) );
	?>
	<div class="tsfem-title tsfem-flex tsfem-flex-row">
		<header><h1><?php printf( esc_html_x( '%1$s %2$s', '1: SEO, 2: Extensions', 'the-seo-framework-extension-manager' ), '<span class="tsfem-logo">SEO</span>', esc_html( $extensions_i18n ) ); ?></h1></header>
	</div>
</section>
<?php
