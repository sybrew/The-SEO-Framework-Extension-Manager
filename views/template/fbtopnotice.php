<?php
/**
 * Fall-Back Top Notice.
 *
 * @package TSF_Extension_Manager\Core\Views\Template
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

use function \TSF_Extension_Manager\Transition\{
	do_dismissible_notice,
};

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

?>
<script type=text/html id=tmpl-tsfem-fbtopnotice>
	<?php
	do_dismissible_notice(
		$notice['before'] . ' ' . $message,
		[
			'type'   => 'error',
			'icon'   => $a11y,
			'escape' => $escape,
			'inline' => $inline,
		]
	);
	?>
</script>
<script type=text/html id=tmpl-tsfem-fbtopnotice-msg>
	<?php
	do_dismissible_notice(
		$notice['before'] . ' {{{data.msg}}}',
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
