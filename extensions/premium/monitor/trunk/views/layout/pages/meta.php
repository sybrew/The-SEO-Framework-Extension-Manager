<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $_class = TSF_Extension_Manager\Extension\Monitor\get_active_class() and $this instanceof $_class or die;

// So fancy.
$color = $this->is_api_connected() ? '#00cd98' : '#0ebfe9';

?>
<meta name="theme-color" content="<?php echo esc_attr( $color ); ?>" />
<meta name="msapplication-navbutton-color" content="<?php echo esc_attr( $color ); ?>" />
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo esc_attr( $color ); ?>" />
<?php
