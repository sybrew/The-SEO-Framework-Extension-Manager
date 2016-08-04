<?php
defined( 'ABSPATH' ) and $this->verify_instance( $_instance, $bits[1] ) or die;

$title = esc_html( get_admin_page_title() );
$about = '';
$actions = '';

if ( $options ) {

	$status = $this->get_subscription_status();

	if ( 'Activated' === $status['active'] && 'Premium' === $status['level'] ) {
		$account_url = $this->get_activation_url( 'my-account/' );
		$account_button_class = 'tsfem-account-active';
		$account_text = __( 'My Account', 'the-seo-framework-extension-manager' );
		$account_title = __( 'View account', 'the-seo-framework-extension-manager' );

		if ( isset( $status['data']['expire'] ) ) {
			$then = strtotime( $status['data']['expire'] );
			$in_two_weeks = strtotime( '+2 week' );
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

	$account_link = $this->get_link( array( 'url' => $account_url, 'target' => '_blank', 'class' => $account_button_class, 'title' => $account_title, 'content' => $account_text ) );
	$account = '<div class="tsfem-top-account">' . $account_link . '</div>';

	$actions = '<div class="tsfem-top-actions">' . $account . '</div>';
} else {
	$info = __( 'Add more powerful SEO features to your website. To get started, use one of the options below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation"><div>' . $info . '</div></div>';
}

?>
<section class="tsfem-top-wrap">
	<?php echo $about . $actions; ?>
	<header><h1 class="tsfem-title"><?php echo esc_html( get_admin_page_title() ); ?></h1></header>
</section>
<?php
