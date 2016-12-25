<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

if ( $this->is_plugin_activated() ) {
	if ( $this->is_premium_user() ) {
		$more_mottos = array( 'premium' );
	} else {
		$more_mottos = array( 'free' );
	}
} else {
	$more_mottos = array( 'free', 'premium' );
}

/**
 * Because positivity.
 *
 * Translating this would mean that:
 * a) we might cause misinterpretations, and
 * b) the mottos need to be assigned as female/male l10n and with inflections.
 * c) we stray away from what the footer is about: recognition and branding.
 */
$mottos = array(
	'better',
	'fair',
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
$mottos = array_merge( $mottos, $more_mottos );
$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto = 'A ' . $mottos[ $motto_key ] . ' Initiative';

?>
<p class="tsfem-footer-title">
	The SEO Framework Extension Manager
</p>
<p class="tsfem-footer-motto">
	<?php echo esc_html( $motto ); ?>
</p>
<?php
