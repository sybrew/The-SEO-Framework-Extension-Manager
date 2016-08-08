<?php
defined( 'ABSPATH' ) and tsf_extension_manager()->verify_instance( $_instance, $bits[1] ) or die;

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
<section class="<?php echo esc_attr( $pane_class ); ?>">
	<div class="tsfem-pane-wrap">
		<?php echo '<header class="tsfem-pane-header"><h3>' . esc_html( $title ) . '</h3>' . $ajax . '</header>'; ?>
		<?php echo '<div class="tsfem-pane-content">' . $content . '</div>'; ?>
	</div>
</section>
<?php
