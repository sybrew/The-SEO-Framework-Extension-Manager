<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager\Extension;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\monitor_class() and $this instanceof $_class or die;

$class = 'tsfem-button-primary tsfem-button-cloud tsfem-button-ajax';
$name = \__( 'Register', 'the-seo-framework-extension-manager' );
$title = \__( 'Connect this website to SEO Monitor', 'the-seo-framework-extension-manager' );

$nonce_action = $this->_get_nonce_action_field( 'connect' );
$nonce = $this->_get_nonce_field( 'connect' );
$submit = $this->_get_submit_button( $name, $title, $class );

$args = [
	'id' => 'tsfem-e-monitor-connect-form',
	'input' => compact( 'nonce_action', 'nonce', 'submit' ),
];

$this->_action_form( \tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ), $args );
