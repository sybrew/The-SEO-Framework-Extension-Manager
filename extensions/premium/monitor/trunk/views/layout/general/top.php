<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

$about = '';
$actions = '';

if ( $options ) {
	//* TODO
} else {
	$info = __( 'Let SEO Monitor help you improve your website. Your privacy is respected, read how below.', 'the-seo-framework-extension-manager' );
	$about = '<div class="tsfem-top-about tsfem-about-activation tsfem-flex tsfem-flex-row"><div>' . esc_html( $info ) . '</div></div>';
}

?>
<section class="tsfem-top-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-flex-space">
	<div class="tsfem-title tsfem-flex tsfem-flex-row">
		<header><h1>
			<?php printf( esc_html_x( '%1$s %2$s', '1: SEO, 2: Monitor', 'the-seo-framework-extension-manager' ), '<span class="tsfem-e-monitor-logo">SEO</span>', esc_html__( 'Monitor', 'the-seo-framework-extension-manager' ) ); ?>
			<?php echo '<em>alpha</em>'; ?>
		</h1></header>
	</div>
	<?php
	//* Already escaped.
	echo $about, $actions;
	?>
</section>
<?php
