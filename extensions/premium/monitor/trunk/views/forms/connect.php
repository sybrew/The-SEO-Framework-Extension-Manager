<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret );

$class = 'tsfem-button-primary tsfem-button-cloud';
$name  = __( 'Register', 'the-seo-framework-extension-manager' );
$title = __( 'Connect this website to SEO Monitor', 'the-seo-framework-extension-manager' );

$nonce_action = $this->_get_nonce_action_field( 'connect' );
$nonce        = $this->_get_nonce_field( 'connect' );
$submit       = $this->_get_submit_button( $name, $title, $class );

$args = [
	'id'    => 'tsfem-e-monitor-connect-form',
	'input' => compact( 'nonce_action', 'nonce', 'submit' ),
];

$this->_action_button( menu_page_url( $this->monitor_page_slug, false ), $args );
