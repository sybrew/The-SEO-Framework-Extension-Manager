<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

$class = 'tsfem-button-primary tsfem-button-cloud tsfem-button-ajax';
$name = __( 'Register', 'the-seo-framework-extension-manager' );
$title = __( 'Connect this website to SEO Monitor', 'the-seo-framework-extension-manager' );

$nonce_action = '<input type="hidden" name="tsfem-e-monitor-connect-action" value="' . esc_attr( $this->request_name['connect'] ) . '">';
$nonce = wp_nonce_field( $this->nonce_action['connect'], $this->nonce_name, true, false );
$submit = sprintf( '<input type="submit" name="submit" id="submit" class="tsfem-button-primary %s" title="%s" value="%s">', esc_attr( $class ), esc_attr( $title ), esc_attr( $name ) );
$form = $nonce_action . $nonce . $submit;

//* No-JS form. Already escaped.
printf( '<form action="%s" method="post" id="tsfem-e-monitor-connect-form">%s</form>', esc_url( tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ) ), $form );
//* JS button.
//printf( '<a id="tsfem-e-monitor-update-button" class="tsfem-button-primary hide-if-no-js %s" title="%s">%s</a>', esc_attr( $class ), esc_attr( $title ), esc_html( $name ) );
