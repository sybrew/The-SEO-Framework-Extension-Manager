<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

?>
<section class="tsfem-top-wrap tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-flex-space">
	<div class="tsfem-title tsfem-flex tsfem-flex-row">
		<header><h1>
			<?php printf( esc_html_x( '%1$s %2$s', '1: SEO, 2: Monitor', 'the-seo-framework-extension-manager' ), '<span class="tsfem-monitor-logo">SEO</span>', esc_html__( 'Monitor', 'the-seo-framework-extension-manager' ) ); ?>
			<?php echo '<em>beta</em>'; ?>
		</h1></header>
	</div>
	<?php $this->output_update_button(); ?>
</section>
<?php
