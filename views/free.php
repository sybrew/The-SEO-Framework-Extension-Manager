<form name="<?php echo esc_attr( $name ); ?>" action="<?php echo $this->get_admin_page_url(); ?>" method="post" id="<?php echo esc_attr( $id ) ?>" class="<?php echo isset( $classes_form ) && count( $classes_form ) > 0 ? implode( ' ', $classes_form ) : ''; ?>">
	<input type="hidden" name="action" value="go-free">
	<?php wp_nonce_field( $nonce ? $nonce : $this->plugin_page_id ); ?>
	<input type="submit" name="submit" id="submit" class="<?php echo isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'button button-secondary'; ?>" value="<?php echo esc_attr( $text ); ?>">
</form>
