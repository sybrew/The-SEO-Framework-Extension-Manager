<form name="<?php echo esc_attr( $name ); ?>" action="<?php echo esc_url( $action ) ?>" method="POST" target="_blank">
	<input type="hidden" name="passback_url" value="<?php echo $this->get_admin_page_url(); ?>"/>
	<input type="hidden" name="blog" value="<?php echo esc_url( get_bloginfo( 'url' ) ); ?>"/>
	<input type="hidden" name="redirect" value="<?php echo isset( $redirect ) ? $redirect : 'signup'; ?>"/>
	<input type="submit" class="<?php echo isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'button button-primary';?>" value="<?php echo esc_attr( $text ); ?>"/>
</form>
