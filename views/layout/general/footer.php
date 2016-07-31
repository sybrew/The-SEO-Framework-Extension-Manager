<?php
defined( 'ABSPATH' ) and $this->verify_instance( $_instance, $bits[0] ) or die;

//* Placeholder.
$extra ? '' : '';

$mottos = array(
	'better',
	'fair',
	'free',
	'premium',
	'supreme',
	'clean',
	'future',
	'prospective',
	'stronger',
	'sustainable',
	'state of the art',
	'social',
	'fast',
	'secure',
	'logical',
);

$motto_key = array_rand( $mottos );
$motto = 'A ' . $mottos[ $motto_key ] . ' Initiative';

?>
<p class="tsfem-footer-title">
	The SEO Framework Extension Manager
</p>
<p class="tsfem-footer-motto">
	<?php echo esc_html( $motto ); ?>
</p>
<?php
