<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

$class_form = isset( $classes_form ) && count( $classes_form ) > 0 ? implode( ' ', $classes_form ) : '';
$submit_class = isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'tsfem-button tsfem-button-primary';

?>
<form name="<?php echo esc_attr( $name ); ?>" action="<?php echo esc_url( $this->get_admin_page_url() ); ?>" method="post" id="<?php echo esc_attr( $id ) ?>" class="<?php echo esc_attr( $class_form ); ?>">
	<input id="<?php $this->_field_id( 'key' ); ?>" name="<?php $this->_field_name( 'key' ); ?>" type="text" size="15" value="" class="regular-text code tsfem-flex tsfem-flex-row tsfem-ltr" placeholder="<?php esc_attr_e( 'License key', 'the-seo-framework-extension-manager' ); ?>">
	<input id="<?php $this->_field_id( 'email' ); ?>" name="<?php $this->_field_name( 'email' ); ?>" type="email" size="15" value="" class="regular-text code tsfem-flex tsfem-flex-row tsfem-ltr" placeholder="<?php esc_attr_e( 'License email', 'the-seo-framework-extension-manager' ); ?>">
	<?php $this->_nonce_action_field( $this->request_name['activate-key'] ); ?>
	<?php wp_nonce_field( $this->nonce_action['activate-key'], $this->nonce_name ); ?>
	<input type="submit" name="submit" id="submit" class="<?php echo esc_attr( $submit_class ); ?>" value="<?php echo esc_attr( $text ); ?>">
</form>
<?php
