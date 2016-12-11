<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

?>
<div class="tsfem-connect-option tsfem-flex tsfem-flex-nowrap tsfem-connect-highlighted">
	<div class="tsfem-connect-description tsfem-e-monitor-connect-description tsfem-flex tsfem-flex-nowrap">
		<h3><?php esc_html_e( 'Privacy', 'the-seo-framework-extension-manager' ); ?></h3>
		<p><?php esc_html_e( 'The SEO Monitor periodically crawls your website to scan for common issues from an external server. In order to do so, it first has to register your website.', 'the-seo-framework-extension-manager' ); ?></p>
		<p class="hide-if-no-js" id="tsfem-e-monitor-privacy-readmore-wrap"><a class="tsfem-e-monitor-readmore" id="tsfem-e-monitor-privacy-readmore"><?php esc_html_e( 'Read more...', 'the-seo-framework-extension-manager' ); ?></a></p>
		<div class="hide-if-js" id="tsfem-e-monitor-privacy-readmore-content">
			<p><?php
				printf( esc_html_x( 'This is a small introductorial excerpt targetted at this extension of our privacy policy. For full details, visit our %s.', '%s = Privacy Policy', 'the-seo-framework-extension-manager' ),
					sprintf( '<a href="%s" rel="external nofollow">%s</a>', esc_url( 'https://theseoframework.com/privacy/' ), esc_html__( 'Privacy Policy' ) )
				);
			?></p>
			<h4 class="tsfem-form-title"><?php esc_html_e( 'Data collection', 'the-seo-framework-extension-manager' ); ?></h4>
			<p><?php esc_html_e( 'The crawler will act as a regular logged-out visitor and will ignore robots exclusion protocol details.', 'the-seo-framework-extension-manager' ); ?></p>
			<p><?php esc_html_e( 'When secure or private information is required (for example, which plugins are active) then you will be informed and prompted about it first.', 'the-seo-framework-extension-manager' ); ?></p>
			<h4 class="tsfem-form-title"><?php esc_html_e( 'Distribution', 'the-seo-framework-extension-manager' ); ?></h4>
			<p><?php esc_html_e( 'The obtained and processed data of this website will only be granted to you on the Monitor SEO option pages through secure authentication. The data will never be disclosed to third parties, for any reason whatsoever.', 'the-seo-framework-extension-manager' ); ?></p>
			<h4 class="tsfem-form-title"><?php esc_html_e( 'Aggregated Statistics', 'the-seo-framework-extension-manager' ); ?></h4>
			<p><?php esc_html_e( 'Statistical data of this website can be used for in-house research purposes, like detecting which plugins or themes cause the most common (performance) issues. When such data is released, the sources will be omitted from the details.', 'the-seo-framework-extension-manager' ); ?></p>
			<h4 class="tsfem-form-title"><?php esc_html_e( 'Details', 'the-seo-framework-extension-manager' ); ?></h4>
			<p><?php printf( esc_html__( 'The user agent of the crawler is: %s', 'the-seo-framework-extension-manager' ), '<code>TheSEOFramework/Monitor;tsfmonitor.com</code>' ); ?></p>
			<p><?php esc_html_e( 'When scaling is required, multiple IP addresses can be used to crawl your website.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
	</div>
	<div class="tsfem-connect-description tsfem-flex tsfem-flex-nowrap">
		<h3><?php esc_html_e( 'Connect', 'the-seo-framework-extension-manager' ); ?></h3>
		<strong><?php esc_html_e( 'Register your website', 'the-seo-framework-extension-manager' ); ?></strong>
		<p><?php esc_html_e( 'Get detailed information about your website. Automatically.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
	<div class="tsfem-connect-action tsfem-e-monitor-connect-action tsfem-flex tsfem-flex-nowrap">
		<?php $this->get_view( 'forms/connect' ); ?>
	</div>
</div>
<?php
