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

$pane_classes[] = 'tsfem-pane';
$_classes       = [
	'full'     => 'tsfem-pane-full',
	'move'     => 'tsfem-pane-move',
	'collapse' => 'tsfem-pane-collapse',
	'push'     => 'tsfem-pane-push',
];
foreach ( $_classes as $_arg => $_class ) {
	$args[ $_arg ] and $pane_classes[] = $_class;
}

?>
<section class="<?php echo \esc_attr( implode( ' ', $pane_classes ) ); ?>" id="<?php echo \esc_attr( $pane_id ); ?>">
	<div class="tsfem-pane-wrap">
		<?php
		printf(
			'<header class="tsfem-pane-header tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap"><h3>%s</h3>%s</header>',
			\esc_html( $title ),
			$ajax
		); // XSS ok.
		if ( isset( $callable ) || isset( $content ) ) {
			?>
			<div class="tsfem-pane-content">
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
