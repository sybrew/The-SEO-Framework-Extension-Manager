<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Forms
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

// phpcs:disable -- TODO use this file.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and tsfem()->_verify_instance( $_instance, $bits[1] ) or die;

$class_submit   = isset( $classes ) && count( $classes ) > 0 ? implode( ' ', $classes ) : 'tsfem-button-primary';
$value_redirect = $redirect ?? 'signup';

// @TODO nonce? nonce.
exit; // UNSAFE (and unused...) SCRIPT! Needs nonce.

?>
<form name="<?= esc_attr( $name ) ?>" action="<?= esc_url( $action, [ 'https', 'http' ] ) ?>" method=post target=_blank  autocomplete=off data-form-type=other>
	<input type=hidden name=passback_url value="<?= esc_url( $this->get_admin_page_url(), [ 'https', 'http' ] ) ?>"/>
	<input type=hidden name=blog value="<?= esc_url( get_bloginfo( 'url' ), [ 'https', 'http' ] ) ?>"/>
	<input type=hidden name=redirect value="<?= esc_attr( $value_redirect ) ?>"/>
	<input type=submit class="<?= esc_attr( $class_submit ) ?>" value="<?= esc_attr( $text ) ?>"/>
</form>
<?php
