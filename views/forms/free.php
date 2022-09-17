<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Forms
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and tsfem()->_verify_instance( $_instance, $bits[1] ) or die;

$class_form   = isset( $classes_form ) && count( $classes_form ) > 0 ? implode( ' ', $classes_form ) : '';
$class_submit = isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'tsfem-button tsfem-button-secondary';

?>
<form name="<?php echo esc_attr( $name ); ?>" action="<?php echo esc_url( $this->get_admin_page_url(), [ 'https', 'http' ] ); ?>" method=post id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class_form ); ?>" autocomplete=off data-form-type=other>
	<?php $this->_nonce_action_field( $this->request_name['activate-free'] ); ?>
	<?php wp_nonce_field( $this->nonce_action['activate-free'], $this->nonce_name ); ?>
	<input type=submit name=submit id=tsfem-submit-activate-free class="<?php echo esc_attr( $class_submit ); ?>" value="<?php echo esc_attr( $text ); ?>">
</form>
<?php
