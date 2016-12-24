<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager_Extension\monitor_class() and $this instanceof $_class or die;

//* So fancy.
$color = $this->is_api_connected() ? '#00cd98' : '#0ebfe9';

?>
<meta name="theme-color" content="<?php echo esc_attr( $color ); ?>" />
<meta name="msapplication-navbutton-color" content="<?php echo esc_attr( $color ); ?>" />
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo esc_attr( $color ); ?>" />
<?php
