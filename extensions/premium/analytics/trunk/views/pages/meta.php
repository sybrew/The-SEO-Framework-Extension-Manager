<?php
/**
 * @package TSF_Extension_Manager_Extension\Analytics\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = analytics_class() and $this instanceof $_class or die;

$google_colors = (
	'#0266c8', // Blue
	'#f90101', // Red
	'#f2b50f', // Yellow
	'#00933b', // Green
);
//* So fancy.
$color = mt_rand( 0, count( $google_colors ) - 1 );

?>
<meta name="theme-color" content="<?php echo esc_attr( $color ) ?>" />
<meta name="msapplication-navbutton-color" content="<?php echo esc_attr( $color ) ?>" />
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo esc_attr( $color ) ?>" />
<?php
