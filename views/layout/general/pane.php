<?php
defined( 'ABSPATH' ) and tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

//* This file can be called through public functions; destroy as much as possible.
unset( $_instance, $bits, $file, $key, $val );

if ( $args['ajax'] ) {
	$a_id = $args['ajax_id'] ? ' id="' . esc_attr( $args['ajax_id'] ) . '"' : '';
	$ajax = '<div class="tsfem-ajax"' . $a_id . '></div>';
} else {
	$ajax = '';
}

$pane_class = $args['full'] ? 'tsfem-pane-full' : 'tsfem-pane-half';
$pane_class .= $args['move'] ? ' tsfem-pane-move' : '';
$pane_class .= $args['collapse'] ? ' tsfem-pane-collapse' : '';

?>
<section class="<?php echo esc_attr( $pane_class ); ?> tsfem-flex">
	<div class="tsfem-pane-wrap tsfem-flex tsfem-flex-nowrap">
		<?php
		//* $ajax is already escaped.
		printf( '<header class="tsfem-pane-header tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap"><h3>%s</h3>%s</header>', esc_html( $title ), $ajax );
		//* $content should already have been escaped.
		printf( '<div class="tsfem-pane-content tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap">%s</div>', $content );
		?>
	</div>
</section>
<?php
