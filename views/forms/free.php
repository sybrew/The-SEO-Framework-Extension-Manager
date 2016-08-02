<?php
defined( 'ABSPATH' ) and $this->verify_instance( $_instance, $bits[1] ) or die;

$class_form = isset( $classes_form ) && count( $classes_form ) > 0 ? implode( ' ', $classes_form ) : '';
$class_submit = isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'button button-secondary';

?>
<form name="<?php echo esc_attr( $name ); ?>" action="<?php echo esc_url( $this->get_admin_page_url() ); ?>" method="post" id="<?php echo esc_attr( $id ) ?>" class="<?php echo esc_attr( $class_form ); ?>">
	<input type="hidden" name="<?php $this->field_name( 'action' ); ?>" value="go-free">
	<?php wp_nonce_field( $this->nonce_action['activate-free'], $this->nonce_name ); ?>
	<input type="submit" name="submit" id="submit" class="<?php echo esc_attr( $class_submit ); ?>" value="<?php echo esc_attr( $text ); ?>">
</form>
<?php
