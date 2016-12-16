<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

$class = 'tsfem-button-primary tsfem-button-cloud tsfem-button-ajax';
$name = __( 'Request Crawl', 'the-seo-framework-extension-manager' );
$title = __( 'Request Monitor to re-crawl this website', 'the-seo-framework-extension-manager' );

$nonce_action = $this->_get_nonce_action_field( 'crawl' );
$nonce = $this->_get_nonce_field( 'crawl' );
$submit = $this->_get_submit_button( $name, $title, $class );

$args = array(
	'id'         => 'tsfem-e-monitor-crawl-form',
	'input'      => compact( 'nonce_action', 'nonce', 'submit' ),
	'ajax'       => true,
	'ajax-id'    => 'tsfem-e-monitor-crawl-js-button',
	'ajax-class' => $class,
	'ajax-name'  => $name,
	'ajax-title' => $title,
);

$this->_action_form( tsf_extension_manager()->get_admin_page_url( $this->monitor_page_slug ), $args );
