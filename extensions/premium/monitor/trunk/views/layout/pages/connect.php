<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager\Extension;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\monitor_class() and $this instanceof $_class or die;

?>
<div class="tsfem-connect-option tsfem-flex tsfem-flex-nowrap tsfem-connect-highlighted">
	<div class="tsfem-connect-description tsfem-e-monitor-connect-description tsfem-flex tsfem-flex-nowrap">
		<h3><?php \esc_html_e( 'Privacy', 'the-seo-framework-extension-manager' ); ?></h3>
		<p><?php \esc_html_e( 'The SEO Monitor periodically crawls your website to scan for common issues from an external server. In order to do so, it first has to register your website.', 'the-seo-framework-extension-manager' ); ?></p>
		<p class="hide-if-no-js" id="tsfem-e-monitor-privacy-readmore-wrap"><a class="tsfem-e-monitor-readmore" id="tsfem-e-monitor-privacy-readmore"><?php \esc_html_e( 'Read more...', 'the-seo-framework-extension-manager' ); ?></a></p>
		<div class="hide-if-js" id="tsfem-e-monitor-privacy-readmore-content">
			<p><?php
				printf( \esc_html_x( 'This is a small introductorial excerpt of our privacy policy. For full details, visit our %s.', '%s = Privacy Policy', 'the-seo-framework-extension-manager' ),
					sprintf( '<a href="%s" rel="external nofollow">%s</a>', \esc_url( 'https://theseoframework.com/privacy/' ), \esc_html__( 'Privacy Policy' ) )
				);
			?></p>
			<h4 class="tsfem-form-title"><?php \esc_html_e( 'Data collection', 'the-seo-framework-extension-manager' ); ?></h4>
			<p><?php \esc_html_e( 'The crawler will act as a regular logged-out visitor and will ignore robots exclusion protocol details.', 'the-seo-framework-extension-manager' ); ?></p>
			<p><?php \esc_html_e( 'When structural non-public information is required (for example, which plugins are active) then you will be informed and prompted about it first.', 'the-seo-framework-extension-manager' ); ?></p>
			<h4 class="tsfem-form-title"><?php \esc_html_e( 'Distribution', 'the-seo-framework-extension-manager' ); ?></h4>
			<p><?php \esc_html_e( 'Potentially personally-identifying information will only be granted to you on the SEO Monitor admin pages through secure authentication. Potentially personally-identifying information will never be disclosed to third parties, for any reason whatsoever.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
	</div>
	<div class="tsfem-e-monitor-connect-action-wrap tsfem-flex tsfem-flex-row tsfem-flex-nowrap">
		<div class="tsfem-connect-description tsfem-flex">
			<h3><?php \esc_html_e( 'Connect', 'the-seo-framework-extension-manager' ); ?></h3>
			<strong><?php \esc_html_e( 'Register your website', 'the-seo-framework-extension-manager' ); ?></strong>
			<p><?php \esc_html_e( 'Get detailed information about your website. Automatically.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
		<div class="tsfem-connect-action tsfem-flex">
			<div class="tsfem-connect-fields-row tsfem-flex tsfem-flex-row">
				<?php $this->get_view( 'forms/connect' ); ?>
			</div>
		</div>
	</div>
</div>
<?php
