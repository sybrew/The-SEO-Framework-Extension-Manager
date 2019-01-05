<?php
/**
 * @package TSF_Extension_Manager\Views
 * @subpackage TSF_Extension_Manager\InpostGUI\Views
 */
namespace TSF_Extension_Manager;

/**
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\InpostGUI as InpostGUI;

defined( 'ABSPATH' ) and InpostGUI::verify( $_secret ) or die;

$tsfem = tsf_extension_manager();

$message_5xx = \esc_html__( 'An informative notice should have been placed here for the error code, but the server experienced an error.', 'the-seo-framework-extension-manager' );

$a11y   = true;
$escape = false;

?>
<script type=text/html id=tmpl-tsfem-inpost-notice-5xx>
	<?php
	$tsfem->do_dismissible_notice(
		$tsfem->format_error_notice( '{{data.code}}', [ 'type' => 'error' ] )['before'] . ' ' . $message_5xx,
		'error',
		$a11y,
		$escape
	);
	?>
</script>
<?php

foreach ( [ 'success', 'warning', 'error' ] as $type ) :
	?>
<script type=text/html id=tmpl-tsfem-inpost-notice-<?php echo esc_attr( $type ); ?>>
	<?php
	$tsfem->do_dismissible_notice(
		$tsfem->format_error_notice( '{{data.code}}', [ 'type' => $type ] )['before'] . ' ' . '{{{data.msg}}}',
		$type,
		$a11y,
		$escape
	);
	?>
</script>
	<?php
endforeach;
