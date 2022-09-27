<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret );

?>
<div class="tsfem-pane-inner-wrap tsfem-pane-inner-collapsable-settings-wrap tsfem-e-local-settings-wrap">
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow hide-if-tsf-js">
		<div class=tsfem-pane-inner-pad>
			<h4 class=tsfem-info-title><?= esc_html__( 'JavaScript required', 'the-seo-framework-extension-manager' ) ?></h4>
			<p class=tsfem-description><?= esc_html__( 'Because of the complexity of the settings, JavaScript is required.', 'the-seo-framework-extension-manager' ) ?></p>
		</div>
	</div>
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow hide-if-no-tsf-js">
		<div class=tsfem-pane-inner-pad>
			<h4 class=tsfem-form-title><?= esc_html__( 'Set departments', 'the-seo-framework-extension-manager' ) ?></h4>
			<span class=description><?= esc_html__( 'With these options, you can annotate the location and practice of the business. This does not impact search ranking outside of the region, unless the region is part of the search query.', 'the-seo-framework-extension-manager' ) ?></span><br>
			<span class=description><?= esc_html__( 'Fill in these fields as accurately as possible. If a field doesn\'t allow a suitable and correct answer, leave it empty.', 'the-seo-framework-extension-manager' ) ?></span><br>
			<span class=description><?= esc_html__( 'All input data will be disclosed. So, if you wish to withold any data from the public, do not fill it in.', 'the-seo-framework-extension-manager' ) ?></span>
		</div>
		<div class=tsfem-pane-inner-pad>
			<?php $this->output_department_fields(); ?>
		</div>
	</div>
</div>
<?php
