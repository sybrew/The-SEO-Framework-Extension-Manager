<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

if ( $this->is_plugin_activated() ) {
	if ( $this->is_premium_user() ) {
		$more_mottos = [ 'premium', 'essential' ];
	} elseif ( $this->is_connected_user() ) {
		$more_mottos = [ 'essential' ];
	} else {
		$more_mottos = [ 'free' ];
	}
} else {
	$more_mottos = [ 'free', 'essential', 'premium' ];
}

/**
 * Because positivity.
 *
 * Translating this would mean that:
 * a) we might cause misinterpretations, and
 * b) the mottos need to be assigned as female/male l10n and with inflections.
 * c) we stray away from what the footer is about: recognition and branding.
 */
$mottos = [
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
];
$mottos = array_merge( $mottos, $more_mottos );
$motto_key = mt_rand( 0, count( $mottos ) - 1 );

?>
<p class="tsfem-footer-title">
	The SEO Framework Extension Manager
</p>
<p class="tsfem-footer-motto">
	<?php echo \esc_html( "A {$mottos[ $motto_key ]} Initiative" ); ?>
</p>
<?php
