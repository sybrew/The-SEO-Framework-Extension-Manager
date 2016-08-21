<?php
defined( 'ABSPATH' ) and tsf_extension_manager()->verify_instance( $_instance, $bits[1] ) or die;

$title = esc_html( get_admin_page_title() );
$about = '';
$actions = '';

if ( $options ) {

	if ( $this->is_plugin_activated() && $this->is_premium_user() ) {

		$status = $this->get_subscription_status();

		$account_url = $this->get_activation_url( 'my-account/' );
		$account_button_class = 'tsfem-account-active';
		$account_text = __( 'My Account', 'the-seo-framework-extension-manager' );
		$account_title = __( 'View account', 'the-seo-framework-extension-manager' );

		if ( isset( $status['data']['expire'] ) ) {
			$then = strtotime( $status['data']['expire'] );
			$in_two_weeks = strtotime( '+4 week' );
			$about_to_expire = $then < $in_two_weeks;

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

	$account_link = $this->get_link( array( 'url' => $account_url, 'target' => '_blank', 'class' => 'tsfem-button ' . $account_button_class, 'title' => $account_title, 'content' => $account_text ) );
	$account = '<div class="tsfem-top-account">' . $account_link . '</div>';

	$actions = '<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">' . $account . '</div>';
} else {
	$info = __( 'Add more powerful SEO features to your website. To get started, use one of the options below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation tsfem-flex tsfem-flex-row"><div>' . esc_html( $info ) . '</div></div>';
}

?>
<section class="tsfem-top-wrap tsfem-flex tsfem-flex-nogrowshrink tsfem-flex-nowrap">
	<?php echo $about . $actions; ?>
	<div class="tsfem-title tsfem-flex tsfem-flex-row"><span class="tsfem-logo"></span><header><h1><?php echo esc_html( get_admin_page_title() ); ?></h1></header></div>
</section>
<?php
