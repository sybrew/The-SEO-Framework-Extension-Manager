<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Extension
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;

// phpcs:disable, PHPCompatibility.Classes.NewLateStaticBinding.OutsideClassScope, VariableAnalysis.CodeAnalysis.VariableAnalysis.StaticOutsideClass -- We're stil in scope.

$f = static::get_settings_form( $index );

?>
<div class="tsfem-pane-inner-wrap tsfem-pane-inner-collapsable-settings-wrap">
	<div class="tsfem-flex tsfem-flex-row tsfem-flex-nogrow hide-if-tsf-js">
		<div class=tsfem-pane-inner-pad>
			<h4 class=tsfem-info-title><?= esc_html__( 'JavaScript required', 'the-seo-framework-extension-manager' ) ?></h4>
			<p class=tsfem-description><?= esc_html__( 'Because of the complexity of the settings, JavaScript is required.', 'the-seo-framework-extension-manager' ) ?></p>
		</div>
	</div>
	<div class="tsfem-flex tsfem-flex-row tsfem-flex-nogrow hide-if-no-tsf-js">
		<div class=tsfem-pane-inner-pad>
			<?php
			$f->_form_wrap( 'start', \menu_page_url( static::$settings_page_slug, false ), true );
			$f->_fields( $settings );
			$f->_form_wrap( 'end' );
			?>
		</div>
	</div>
</div>
<?php
