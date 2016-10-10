<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

/**
 * Because positivity.
 */
$mottos = array(
	'simple',
	'prospective',
	'thorough',
	'prolonged',
	'magnified',
);
$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto = 'A ' . $mottos[ $motto_key ] . ' Solution';

?>
<p class="tsfem-footer-title">
	The SEO Framework Monitor
</p>
<p class="tsfem-footer-motto">
	<?php echo esc_html( $motto ); ?>
</p>
<?php
