<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Extension
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;

/**
 * Because positivity.
 *
 * Translating this would mean that:
 * a) we might cause misinterpretations, and
 * b) the mottos need to be assigned as female/male l10n and with inflections.
 * c) we stray away from what the footer is about: recognition and branding.
 */
$mottos = [
	'A compiled',
	'A summed',
	'A summarized',
	'A simplified',
	'A summated',
	'A synopsized',
	'A united',
	'A bonded',
	'A joint',
];

$motto_key = mt_rand( 0, count( $mottos ) - 1 );

?>
<p class=tsfem-footer-title dir=ltr>
	The SEO Framework &ndash; Extension Settings
</p>
<p class=tsfem-footer-motto dir=ltr>
	<?= esc_html( "{$mottos[ $motto_key ]} Overview" ) ?>
</p>
<?php
