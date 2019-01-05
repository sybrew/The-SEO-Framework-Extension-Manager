<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

//* This file can be called through public functions; destroy as much as possible.
unset( $bits, $file, $key, $val );

if ( $args['ajax'] ) {
	$a_id = $args['ajax_id'] ? ' id="' . \esc_attr( $args['ajax_id'] ) . '"' : '';
	$ajax = '<div class="tsfem-ajax"' . $a_id . '></div>';
} else {
	$ajax = '';
}

$pane_id = $args['pane_id'];

$pane_class  = $args['full'] ? 'tsfem-pane-full' : 'tsfem-pane-half';
$pane_class .= $args['move'] ? ' tsfem-pane-move' : '';
$pane_class .= $args['collapse'] ? ' tsfem-pane-collapse' : '';
$pane_class .= $args['push'] ? ' tsfem-pane-push' : '';

?>
<section class="<?php echo \esc_attr( $pane_class ); ?> tsfem-flex" id="<?php echo \esc_attr( $pane_id ); ?>">
	<div class="tsfem-pane-wrap tsfem-flex tsfem-flex-nowrap">
		<?php
		printf(
			'<header class="tsfem-pane-header tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap"><h3>%s</h3>%s</header>',
			\esc_html( $title ),
			$ajax
		); // XSS ok.
		if ( isset( $callable ) || isset( $content ) ) {
			?>
			<div class="tsfem-pane-content tsf-tooltip-boundary tsfem-flex tsfem-flex-row tsfem-flex-nowrap">
			<?php
			if ( isset( $callable ) ) {
				//* If secure, pass object.
				if ( $args['secure_obj'] ) {
					call_user_func( $callable, $callable[0] );
				} else {
					call_user_func( $callable );
				}
			} elseif ( isset( $content ) ) {
				echo $content; // XSS ok... ought to be escaped.
			}
			?>
			</div>
			<?php
		}
		?>
		<?php
		if ( isset( $args['footer'] ) ) {
			?>
			<footer class="tsfem-pane-footer-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-end">
			<?php
			if ( $args['secure_obj'] ) {
				call_user_func( $args['footer'], $args['footer'][0] );
			} else {
				call_user_func( $args['footer'] );
			}
			?>
			</footer>
			<?php
		}
		?>
	</div>
</section>
<?php
