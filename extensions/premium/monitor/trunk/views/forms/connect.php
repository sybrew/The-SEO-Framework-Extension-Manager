<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

$class = 'tsfem-button-primary tsfem-button-cloud tsfem-button-ajax';
$name = __( 'Register', 'the-seo-framework-extension-manager' );
$title = __( 'Connect this website to SEO Monitor', 'the-seo-framework-extension-manager' );

$nonce_action = $this->_get_nonce_action_field( 'connect' );
$nonce = $this->_get_nonce_field( 'connect' );
$submit = $this->_get_submit_button( $name, $title, $class );

$form_items = $nonce_action . $nonce . $submit;

//* Already escaped.
printf( '<form action="%s" method="post" id="tsfem-e-monitor-connect-form">%s</form>', esc_url( tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ) ), $form_items );

//* False = no-JS form only. Use string for AJAX ID. TODO maybe.
//$this->_form( tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ), compact( 'nonce_action', 'nonce', 'submit' ), false );
//* JS button.
//printf( '<a id="tsfem-e-monitor-update-button" class="tsfem-button-primary hide-if-no-js %s" title="%s">%s</a>', esc_attr( $class ), esc_attr( $title ), esc_html( $name ) );
