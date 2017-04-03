<?php
/**
 * @package TSF_Extension_Manager\Extension\Transporter\Admin\Views
 */
namespace TSF_Extension_Manager\Extension;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\transporter_class() and $this instanceof $_class or die;

/**
 * Because positivity.
 */
$mottos = array(
	'elementary',
	'evolutionary',
	'advancing',
	'adaptive',
);
$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto = 'An ' . $mottos[ $motto_key ] . ' Movement';

?>
<p class="tsfem-footer-title">
	The SEO Framework Transporter
</p>
<p class="tsfem-footer-motto">
	<?php echo \esc_html( $motto ); ?>
</p>
<?php
