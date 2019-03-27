<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Local\get_layout_class() and $this instanceof $_class or die;

/**
 * Because positivity.
 */
$mottos = [
	'centered',
	'complete',
	'conscientious',
	'focussed',
	'diligent',
];
$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto     = 'A ' . $mottos[ $motto_key ] . ' Solution';

?>
<p class="tsfem-footer-title">
	Local SEO - The SEO Framework
</p>
<p class="tsfem-footer-motto">
	<?php echo \esc_html( $motto ); ?>
</p>
<?php
