<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Local\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-pane-inner-collapsable-settings-wrap tsfem-e-local-settings-wrap">
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-info-title"><?php \esc_html_e( 'JavaScript required', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class="tsfem-description"><?php \esc_html_e( 'Because of the complexity of the settings, JavaScript is required.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
	</div>
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-no-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-form-title"><?php \esc_html_e( 'Set departments', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class="tsfem-description"><?php \esc_html_e( 'Fill in these fields as accurately as possible.' ); ?></span><br>
			<span class="tsfem-description"><?php \esc_html_e( 'If a field doesn\'t allow a suitable and correct answer, leave it empty.', 'the-seo-framework-extension-manager' ); ?></span>
		</div>
		<div class="tsfem-pane-inner-pad">
			<?php $this->_fields( $this->get_departments_fields(), 'echo' ); ?>
		</div>
	</div>
</div>
<?php
