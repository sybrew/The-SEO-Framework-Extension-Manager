<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

$about = $actions = '';

if ( $options ) {

	if ( $this->is_plugin_activated() && $this->is_connected_user() ) {

		$status = $this->get_subscription_status();

		$account_url = $this->get_activation_url( 'my-account/' );
		$account_button_class = 'tsfem-button-primary-bright tsfem-button-star';
		$account_text = \__( 'My Account', 'the-seo-framework-extension-manager' );
		$account_title = \__( 'View account', 'the-seo-framework-extension-manager' );

		if ( isset( $status['end_date'] ) ) {
			//* UTC.
			$then = strtotime( $status['end_date'] );
			$in_four_weeks = strtotime( '+4 week' );
			$about_to_expire = $then < $in_four_weeks;

			if ( $about_to_expire ) {
				$account_button_class = 'tsfem-button-red tsfem-button-warning';
				$account_title = \__( 'Extend license', 'the-seo-framework-extension-manager' );
			}
		}
	} else {
		$account_url = $this->get_activation_url( 'shop/' );
		$account_button_class = 'tsfem-button-green tsfem-button-love';
		$account_title = \__( 'Get license', 'the-seo-framework-extension-manager' );
		$account_text  = \__( 'Go Premium', 'the-seo-framework-extension-manager' );
	}

	$account_link = $this->get_link( [
		'url'     => $account_url,
		'target'  => '_blank',
		'class'   => 'tsfem-button-primary ' . $account_button_class,
		'title'   => $account_title,
		'content' => $account_text,
	] );
	$account = '<div class="tsfem-top-account">' . $account_link . '</div>';

	$actions = '<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">' . $account . '</div>';
} else {
	$info = \__( 'Add more powerful SEO features to your website. To get started, use one of the options below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation tsfem-flex tsfem-flex-row"><div>' . \esc_html( $info ) . '</div></div>';
}

//* Print style.
?>
<div class="tsfem-title">
	<header><h1>
		<?php
		$image = [
			'svg' => $this->get_image_file_location( 'tsflogo.svg', true ),
			//	'2x' => $this->get_image_file_location( 'tsflogo-58x58px.png', true ),
			'1x'  => $this->get_image_file_location( 'tsflogo-29x29px.png', true ),
		];
		$size = '1em';

		printf(
			'<span class="tsfem-logo">%sExtension Manager</span>',
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
		);
		?>
	</h1></header>
</div>
<?php

echo $about, $actions; // XSS ok.
