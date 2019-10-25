<?php

defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

//* So fancy.
$color = $this->is_connected_user() || false === $this->is_plugin_activated() ? '#0ebfe9' : '#00cd98';

?>
<meta name="theme-color" content="<?php echo \esc_attr( $color ); ?>" />
<meta name="msapplication-navbutton-color" content="<?php echo \esc_attr( $color ); ?>" />
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo \esc_attr( $color ); ?>" />
<?php
