<?php
/**
 * Fall-Back Top Notice.
 */
defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;

$tsfem = \tsf_extension_manager();

$message = \esc_html__( 'An informative notice should have been placed here for the error code, but the server experienced an error.', 'the-seo-framework-extension-manager' );
$notice  = $tsfem->format_error_notice(
	'{{data.code}}',
	[
		'type'    => 'error',
		'message' => '',
	]
);

?>
<script type=text/html id=tmpl-tsfem-fbtopnotice>
	<?php
	$tsfem->do_dismissible_notice(
		$notice['before'] . ' ' . $message,
		'error',
		$a11y   = true,
		$escape = false
	);
	?>
</script>
<script type=text/html id=tmpl-tsfem-fbtopnotice-msg>
	<?php
	$tsfem->do_dismissible_notice(
		$notice['before'] . ' {{{data.msg}}}',
		'error',
		$a11y   = true,
		$escape = false
	);
	?>
</script>
<?php
