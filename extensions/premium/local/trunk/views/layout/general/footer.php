<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret ) or die;

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
<p class=tsfem-footer-title dir=ltr>
	Local &ndash; The SEO Framework
</p>
<p class=tsfem-footer-motto dir=ltr>
	<?= esc_html( $motto ) ?>
</p>
<?php
