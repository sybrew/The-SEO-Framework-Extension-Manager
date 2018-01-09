<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

//* This file can be called through public functions; destroy as much as possible.
unset( $bits, $file, $key, $val );

$message = \esc_html__( 'An informative notice should have been placed here for the error code, but the server experienced an error.', 'the-seo-framework-extension-manager' );
$notice = $this->format_error_notice(
	'{{data.code}}',
	[
		'type' => 'error',
		'message' => '',
	]
);

?>
<script type="text/html" id="tmpl-tsfem-fbtopnotice">
	<?php
	$this->do_dismissible_notice(
		$notice['before'] . ' ' . $message,
		'error',
		$a11y = true,
		$escape = false
	);
	?>
</script>
<?php
