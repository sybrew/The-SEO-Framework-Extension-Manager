<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Template
 * @subpackage TSF_Extension_Manager\InpostGUI\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	do_dismissible_notice,
};

$message_5xx = esc_html__( 'An informative notice should have been placed here for the error code, but the server experienced an error.', 'the-seo-framework-extension-manager' );

$a11y   = true;
$escape = false;
$inline = true;

$tsfem = tsfem();
?>
<script type=text/html id=tmpl-tsfem-inpost-notice-5xx>
	<?php
	do_dismissible_notice(
		$tsfem->format_error_notice( '{{data.code}}', [ 'type' => 'error' ] )['before'] . ' ' . $message_5xx,
		[
			'type'   => 'error',
			'icon'   => $a11y,
			'escape' => $escape,
			'inline' => $inline,
		]
	);
	?>
</script>
<?php

foreach ( [ 'updated', 'warning', 'error' ] as $type ) {
	?>
<script type=text/html id=tmpl-tsfem-inpost-notice-<?= esc_attr( $type ) ?>>
	<?php
	do_dismissible_notice(
		$tsfem->format_error_notice( '{{data.code}}', [ 'type' => $type ] )['before'] . ' {{{data.msg}}}',
		[
			'type'   => $type,
			'icon'   => $a11y,
			'escape' => $escape,
			'inline' => $inline,
		]
	);
	?>
</script>
	<?php
}
