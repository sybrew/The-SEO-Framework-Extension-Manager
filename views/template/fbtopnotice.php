<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Template
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * Fall-Back Top Notice.
 */
defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;


$message = esc_html__( 'An informative notice should have been placed here for the error code, but the server experienced an error.', 'the-seo-framework-extension-manager' );
$notice  = tsfem()->format_error_notice(
	'{{data.code}}',
	[
		'type'    => 'error',
		'message' => '',
	]
);

$a11y   = true;
$escape = false;
$inline = true;

$tsf = tsf();
?>
<script type=text/html id=tmpl-tsfem-fbtopnotice>
	<?php
	$tsf->do_dismissible_notice(
		$notice['before'] . ' ' . $message,
		'error',
		$a11y,
		$escape,
		$inline
	);
	?>
</script>
<script type=text/html id=tmpl-tsfem-fbtopnotice-msg>
	<?php
	$tsf->do_dismissible_notice(
		$notice['before'] . ' {{{data.msg}}}',
		'error',
		$a11y,
		$escape,
		$inline
	);
	?>
</script>
<?php
