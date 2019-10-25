<?php

defined( 'ABSPATH' ) and \TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;

// phpcs:disable, PHPCompatibility.Classes.NewLateStaticBinding.OutsideClassScope, VariableAnalysis.CodeAnalysis.VariableAnalysis.StaticOutsideClass -- We're stil in scope.

$f = static::get_settings_form( $index );

// TODO When the time comes... set TSF v4.0 JS check instead.

?>
<div class="tsfem-pane-inner-wrap tsfem-pane-inner-collapsable-settings-wrap">
	<div class="tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-info-title"><?php \esc_html_e( 'JavaScript required', 'the-seo-framework-extension-manager' ); ?></h4>
			<p class="tsfem-description"><?php \esc_html_e( 'Because of the complexity of the settings, JavaScript is required.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
	</div>
	<div class="tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-no-js">
		<div class="tsfem-pane-inner-pad">
			<?php
			$f->_form_wrap( 'start', \tsf_extension_manager()->get_admin_page_url( static::$settings_page_slug ), true );
			$f->_fields( $settings );
			$f->_form_wrap( 'end' );
			?>
		</div>
	</div>
</div>
<?php
