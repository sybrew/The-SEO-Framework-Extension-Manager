<form name="<?php echo esc_attr( $name ); ?>" action="<?php echo $this->get_admin_page_url(); ?>" method="post" id="<?php echo esc_attr( $id ) ?>" class="<?php echo isset( $classes_form ) && count( $classes_form ) > 0 ? implode( ' ', $classes_form ) : ''; ?>">
	<input id="<?php $this->field_id( 'key' ); ?>" name="<?php $this->field_name( 'key' ); ?>" type="text" size="15" value="" class="regular-text code"  placeholder="<?php esc_html_e( 'License key', 'the-seo-framework-extension-manager' ); ?>">
	<input id="<?php $this->field_id( 'email' ); ?>" name="<?php $this->field_name( 'email' ); ?>" type="email" size="15" value="" class="regular-text code" placeholder="<?php esc_html_e( 'License email', 'the-seo-framework-extension-manager' ); ?>">
	<input type="hidden" name="<?php $this->field_name( 'action' ); ?>" value="validate-key">
	<?php wp_nonce_field( $this->activation_nonce_action, $this->activation_nonce_name ); ?>
	<input type="submit" name="submit" id="submit" class="<?php echo isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'button button-primary'; ?>" value="<?php echo esc_attr( $text ); ?>">
</form>
