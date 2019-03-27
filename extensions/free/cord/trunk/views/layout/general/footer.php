<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Cord\get_layout_class() and $this instanceof $_class or die;

/**
 * Because positivity.
 */
$mottos = [
	'connected',
	'joined',
	'bridged',
	'secured',
	'chained',
];
$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto     = 'A ' . $mottos[ $motto_key ] . ' Solution';

?>
<p class="tsfem-footer-title">
	Cord - The SEO Framework
</p>
<p class="tsfem-footer-motto">
	<?php echo \esc_html( $motto ); ?>
</p>
<?php
