<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Template
 * @subpackage TSF_Extension_Manager\InpostGUI\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;

$tsf   = tsf();
$tsfem = tsfem();

$message_5xx = esc_html__( 'An informative notice should have been placed here for the error code, but the server experienced an error.', 'the-seo-framework-extension-manager' );

$a11y   = true;
$escape = false;
$inline = true;

?>
<script type=text/html id=tmpl-tsfem-inpost-notice-5xx>
	<?php
	$tsf->do_dismissible_notice(
		$tsfem->format_error_notice( '{{data.code}}', [ 'type' => 'error' ] )['before'] . ' ' . $message_5xx,
		'error',
		$a11y,
		$escape,
		$inline
	);
	?>
</script>
<?php

foreach ( [ 'updated', 'warning', 'error' ] as $type ) :
	?>
<script type=text/html id=tmpl-tsfem-inpost-notice-<?= esc_attr( $type ) ?>>
	<?php
	$tsf->do_dismissible_notice(
		$tsfem->format_error_notice( '{{data.code}}', [ 'type' => $type ] )['before'] . ' {{{data.msg}}}',
		$type,
		$a11y,
		$escape,
		$inline
	);
	?>
</script>
	<?php
endforeach;
