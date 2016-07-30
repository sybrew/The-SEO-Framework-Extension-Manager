<?php
defined( 'ABSPATH' ) and $this->verify_instance( $_instance, $bits[1] ) or die;

$title = $title ? '<header class="tsfem-pane-header"><h3>' . esc_html( $title ) . '</h3></header>' : '';
$pane_class = $args['full'] ? 'tsfem-pane-full' : 'tsfem-pane-half';
$pane_class .= $args['move'] ? ' tsfem-pane-move' : '';
$pane_class .= $args['collapse'] ? ' tsfem-pane-collapse' : '';

?>
<section class="<?php echo esc_attr( $pane_class ); ?>">
	<?php echo $title; ?>
	<?php echo '<div class="tsfem-pane-content">' . $content . '</div>'; ?>
</section>
<?php
