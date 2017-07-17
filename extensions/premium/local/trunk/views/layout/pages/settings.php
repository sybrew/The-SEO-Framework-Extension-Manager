<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Local\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-pane-inner-wrap tsfem-pane-inner-collapsable-settings-wrap tsfem-e-local-settings-wrap">
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-info-title"><?php \esc_html_e( 'JavaScript required', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class="tsfem-description"><?php \esc_html_e( 'Because of the complexity of the settings, JavaScript is required.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
	</div>
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-no-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-form-title"><?php \esc_html_e( 'Set departments', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class="description"><?php \esc_html_e( 'With these options, you can annotate the location and practice of the business. This does not impact search ranking outside of the region, unless the region is part of the search query.', 'the-seo-framework-extension-manager' ); ?></span><br>
			<span class="description"><?php \esc_html_e( 'Fill in these fields as accurately as possible. If a field doesn\'t allow a suitable and correct answer, leave it empty.', 'the-seo-framework-extension-manager' ); ?></span><br>
			<span class="description"><?php \esc_html_e( 'All input data will be disclosed. So, if you wish to withold any data from the public, do not fill it in.', 'the-seo-framework-extension-manager' ); ?></span>
		</div>
		<div class="tsfem-pane-inner-pad">
			<?php $this->output_department_fields(); ?>
		</div>
	</div>
</div>
<?php
