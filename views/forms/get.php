<?php
defined( 'ABSPATH' ) and tsf_extension_manager()->verify_instance( $_instance, $bits[1] ) or die;

$class_submit = isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'tsfem-button tsfem-button-primary';
$value_redirect = isset( $redirect ) ? $redirect : 'signup';

//* @TODO nonce? nonce.

?>
<form name="<?php echo esc_attr( $name ); ?>" action="<?php echo esc_url( $action ) ?>" method="POST" target="_blank">
	<input type="hidden" name="passback_url" value="<?php echo esc_url( $this->get_admin_page_url() ); ?>"/>
	<input type="hidden" name="blog" value="<?php echo esc_url( get_bloginfo( 'url' ) ); ?>"/>
	<input type="hidden" name="redirect" value="<?php echo esc_attr( $value_redirect ); ?>"/>
	<input type="submit" class="<?php echo esc_attr( $class_submit ); ?>" value="<?php echo esc_attr( $text ); ?>"/>
</form>
<?php
