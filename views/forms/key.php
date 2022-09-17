<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Forms
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and tsfem()->_verify_instance( $_instance, $bits[1] ) or die;

$class_form   = isset( $classes_form ) && count( $classes_form ) > 0 ? implode( ' ', $classes_form ) : '';
$submit_class = isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'tsfem-button-primary';

?>
<form name="<?= esc_attr( $name ) ?>" action="<?= esc_url( $this->get_admin_page_url(), [ 'https', 'http' ] ) ?>" method=post id="<?= esc_attr( $id ) ?>" class="<?= esc_attr( $class_form ) ?>" autocomplete=off data-form-type=other>
	<input id="<?php $this->_field_id( 'key' ); ?>" name="<?php $this->_field_name( 'key' ); ?>" type=text size=15 value class="regular-text code tsfem-ltr" placeholder="<?= esc_attr__( 'License key', 'the-seo-framework-extension-manager' ) ?>">
	<input id="<?php $this->_field_id( 'email' ); ?>" name="<?php $this->_field_name( 'email' ); ?>" type=email size=15 value class="regular-text code tsfem-ltr" placeholder="<?= esc_attr__( 'License email', 'the-seo-framework-extension-manager' ) ?>">
	<?php $this->_nonce_action_field( $this->request_name['activate-key'] ); ?>
	<?php wp_nonce_field( $this->nonce_action['activate-key'], $this->nonce_name ); ?>
	<input type=submit name=submit id=submit class="<?= esc_attr( $submit_class ) ?>" value="<?= esc_attr( $text ) ?>">
</form>
<?php
