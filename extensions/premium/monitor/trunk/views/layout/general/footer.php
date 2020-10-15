<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $_class = TSF_Extension_Manager\Extension\Monitor\get_active_class() and $this instanceof $_class or die;

/**
 * Because positivity.
 */
$mottos = [
	'simple',
	'prospective',
	'thorough',
	'prolonged',
	'magnified',
];

$motto_key = mt_rand( 0, count( $mottos ) - 1 );
$motto     = "A {$mottos[ $motto_key ]} Solution";

?>
<p class="tsfem-footer-title" dir=ltr>
	Monitor &ndash; The SEO Framework
</p>
<p class="tsfem-footer-motto" dir=ltr>
	<?php echo esc_html( $motto ); ?>
</p>
<?php
