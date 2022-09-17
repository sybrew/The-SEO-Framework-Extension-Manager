<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret );

/**
 * Because positivity.
 */
$mottos = [
	'automated',
	'airy',
	'open',
	'orderly',
	'elegant',
	'agile',
];

$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto     = "An {$mottos[ $motto_key ]} Future";

?>
<p class=tsfem-footer-title dir=ltr>
	Transport &ndash; The SEO Framework
</p>
<p class=tsfem-footer-motto dir=ltr>
	<?= esc_html( $motto ) ?>
</p>
<?php
