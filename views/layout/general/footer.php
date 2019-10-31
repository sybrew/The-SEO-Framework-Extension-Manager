<?php

defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

// phpcs:disable, PHPCompatibility.Classes.NewLateStaticBinding.OutsideClassScope, VariableAnalysis.CodeAnalysis.VariableAnalysis.StaticOutsideClass -- We're stil in scope.

if ( $this->is_plugin_activated() ) {
	if ( $this->is_enterprise_user() ) {
		$more_mottos = [ 'An enterprise', 'A premium', 'An essential' ];
	} elseif ( $this->is_premium_user() ) {
		$more_mottos = [ 'A premium', 'An essential' ];
	} elseif ( $this->is_connected_user() ) {
		$more_mottos = [ 'An essential' ];
	} else {
		$more_mottos = [ 'A free' ];
	}
} else {
	$more_mottos = [ 'A free', 'An essential', 'A premium', 'An enterprise' ];
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
	'A better',
	'A fair',
	'A supreme',
	'A clean',
	'A future',
	'A prospective',
	'A stronger',
	'A sustainable',
	'A state of the art',
	'A social',
	'A fast',
	'A secure',
	'A logical',
];
$mottos = array_merge( $mottos, $more_mottos );
$motto_key = mt_rand( 0, count( $mottos ) - 1 );

?>
<p class="tsfem-footer-title">
	The SEO Framework &ndash; Extension Manager
</p>
<p class="tsfem-footer-motto">
	<?php echo \esc_html( "{$mottos[ $motto_key ]} Initiative" ); ?>
</p>
<?php
