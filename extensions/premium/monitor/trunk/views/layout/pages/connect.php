<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $_class = TSF_Extension_Manager\Extension\Monitor\get_active_class() and $this instanceof $_class or die;

?>
<div class=tsfem-connect-option>
	<div class=tsfem-connect-text>
		<div class=tsfem-connect-description>
			<h3><?php esc_html_e( 'Privacy', 'the-seo-framework-extension-manager' ); ?></h3>
			<p><?php esc_html_e( 'The SEO Monitor periodically crawls your website to scan for common issues from an external server. In order to do so, it first has to register your website.', 'the-seo-framework-extension-manager' ); ?></p>
			<div class="hide-if-no-js" id="tsfem-e-monitor-privacy-readmore-wrap"><p><a class="tsfem-e-monitor-readmore" id="tsfem-e-monitor-privacy-readmore" href=javascript:;><?php esc_html_e( 'Read more...', 'the-seo-framework-extension-manager' ); ?></a></p></div>
			<div class="hide-if-js" id="tsfem-e-monitor-privacy-readmore-content">
				<p>
				<?php
				// phpcs:disable, WordPress.Security.EscapeOutput -- Already escaped.
				echo the_seo_framework()->convert_markdown(
					sprintf(
						/* translators: %s = URL to privacy policy */
						esc_html__( 'This is a small introductory excerpt of our privacy policy. For full details, view our [Privacy Policy](%s).', 'the-seo-framework-extension-manager' ),
						'https://theseoframework.com/privacy/'
					),
					[ 'a' ]
				);
				// phpcs:enable, WordPress.Security.EscapeOutput
				?>
				</p>
				<h4 class="tsfem-form-title"><?php esc_html_e( 'Data collection', 'the-seo-framework-extension-manager' ); ?></h4>
				<p><?php esc_html_e( 'The crawler will act as a regular logged-out visitor and will ignore robots exclusion protocol details.', 'the-seo-framework-extension-manager' ); ?></p>
				<p><?php esc_html_e( 'When structural non-public information is required (for example, which plugins are active) then you will be informed and prompted about it first.', 'the-seo-framework-extension-manager' ); ?></p>
				<h4 class="tsfem-form-title"><?php esc_html_e( 'Distribution', 'the-seo-framework-extension-manager' ); ?></h4>
				<p><?php esc_html_e( 'Potentially personally-identifying information will only be granted to you on the SEO Monitor admin pages through secure authentication. Potentially personally-identifying information will never be disclosed to third parties, for any reason whatsoever.', 'the-seo-framework-extension-manager' ); ?></p>
			</div>
		</div>
	</div>
</div>
<div class=tsfem-connect-option>
	<div class="tsfem-connect-row tsfem-flex tsfem-flex-row">
		<div class="tsfem-connect-text tsfem-flex">
			<div class=tsfem-connect-description>
				<h3><?php esc_html_e( 'Connect', 'the-seo-framework-extension-manager' ); ?></h3>
				<strong><?php esc_html_e( 'Register your website', 'the-seo-framework-extension-manager' ); ?></strong>
				<p><?php esc_html_e( 'Get detailed information about your website. Automatically.', 'the-seo-framework-extension-manager' ); ?></p>
			</div>
		</div>
		<div class="tsfem-connect-action tsfem-flex">
			<?php $this->get_view( 'forms/connect' ); ?>
		</div>
	</div>
</div>
<?php
