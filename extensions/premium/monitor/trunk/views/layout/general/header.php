<?php
/**
 * @package TSF_Extension_Manager_Extension
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

$title = esc_html( get_admin_page_title() );

$update_url = $this->get_update_url();
$update_text = __( 'Update', 'the-seo-framework-extension-manager' );
$update_title = __( 'Get latest data', 'the-seo-framework-extension-manager' );

//* @TODO set tsfem-button ajax with internal loader icon. + Cloud icon (f176?)
$update_link = tsf_extension_manager()->get_link( array( 'url' => $update_url, 'target' => '_blank', 'class' => 'tsfem-button-primary tsfem-button-primary-bright tsfem-button-ajax tsfem-button-cloud', 'title' => $update_title, 'content' => $update_text ) );
$update = '<div class="tsfem-top-refresh">' . $update_link . '</div>';

$actions = '<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">' . $update . '</div>';

?>
<section class="tsfem-top-wrap tsfem-flex tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-flex-space">
	<?php echo $actions; ?>
	<div class="tsfem-title tsfem-flex tsfem-flex-row">
		<header><h1><?php printf( esc_html_x( '%1$s %2$s', '1: SEO, 2: Extensions', 'the-seo-framework-extension-manager' ), '<span class="tsfem-monitor-logo">SEO</span>', esc_html__( 'Monitor', 'the-seo-framework-extension-manager' ) ); ?></h1></header>
	</div>
</section>
<?php
