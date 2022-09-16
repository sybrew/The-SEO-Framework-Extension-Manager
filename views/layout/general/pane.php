<?php
/**
 * @package TSF_Extension_Manager\Core\Views\General
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

// This file can be called through public functions; destroy as much as possible.
unset( $bits, $file, $key, $val );

if ( $args['ajax'] ) {
	$a_id = $args['ajax_id'] ? ' id="' . esc_attr( $args['ajax_id'] ) . '"' : '';
	$ajax = '<div class="tsfem-ajax"' . $a_id . '></div>';
} else {
	$ajax = '';
}

$pane_id = $args['pane_id'];

$pane_classes[] = 'tsfem-pane';
$_classes       = [
	'full'     => 'tsfem-pane-full',
	'wide'     => 'tsfem-pane-wide',
	'tall'     => 'tsfem-pane-tall',
	'move'     => 'tsfem-pane-move',
	'collapse' => 'tsfem-pane-collapse',
	'push'     => 'tsfem-pane-push',
];
foreach ( $_classes as $_arg => $_class ) {
	$args[ $_arg ] and $pane_classes[] = $_class;
}

if ( $args['logo'] ) {
	$logo_size = '1.4em';

	$logo = sprintf(
		'<svg width="%1$s" height="%1$s">%2$s</svg>',
		esc_attr( $logo_size ),
		sprintf(
			'<image xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="%1$s" width="%2$s" height="%2$s"></image>',
			esc_url( $args['logo'], [ 'https', 'http' ] ),
			esc_attr( $logo_size )
		)
	);
} else {
	$logo = '';
}

?>
<section class="<?php echo esc_attr( implode( ' ', $pane_classes ) ); ?>" id="<?php echo esc_attr( $pane_id ); ?>">
	<div class="tsfem-pane-wrap">
		<?php
		printf(
			'<header class="tsfem-pane-header tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap"><h3>%s%s</h3>%s</header>',
			$logo, // phpcs:ignore, WordPress.Security.EscapeOutput -- already escaped.
			esc_html( $title ),
			$ajax // phpcs:ignore, WordPress.Security.EscapeOutput -- already escaped.
		);
		if ( isset( $callable ) || isset( $content ) ) {
			?>
			<div class="tsfem-pane-content">
				<?php
				if ( isset( $callable ) ) {
					// If secure, pass object.
					if ( $args['secure_obj'] ) {
						call_user_func( $callable, $callable[0], ...$args['cbargs'] );
					} else {
						call_user_func( $callable, ...$args['cbargs'] );
					}
				} elseif ( isset( $content ) ) {
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- ...ought to be escaped.
					echo $content;
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
					call_user_func( $args['footer'], $args['footer'][0], ...$args['fcbargs'] );
				} else {
					call_user_func( $args['footer'], ...$args['fcbargs'] );
				}
				?>
			</footer>
			<?php
		}
		?>
	</div>
</section>
<?php
