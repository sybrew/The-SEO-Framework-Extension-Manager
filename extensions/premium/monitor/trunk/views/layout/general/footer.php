<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Monitor\get_active_class() and $this instanceof $_class or die;

/**
 * Because positivity.
 */
$mottos = [
	'simple',
	'prospective',
	'thorough',
	'prolonged',
	'magnified',
];
$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto = 'A ' . $mottos[ $motto_key ] . ' Solution';

?>
<p class="tsfem-footer-title">
	Monitor &ndash; The SEO Framework
</p>
<p class="tsfem-footer-motto">
	<?php echo \esc_html( $motto ); ?>
</p>
<?php
