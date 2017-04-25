<?php
/**
 * @package TSF_Extension_Manager\Extension\Transporter\Admin\Views
 */
namespace TSF_Extension_Manager\Extension;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\transporter_class() and $this instanceof $_class or die;

?>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
<?php
	\tsf_extension_manager()->_do_pane_wrap(
		\__( 'Transport SEO Settings', 'the-seo-framework-extension-manager' ),
		$this->get_transport_settings_overview(),
		array(
			'full' => false,
			'collapse' => false,
			'move' => false,
			'pane_id' => 'tsfem-e-transporter-settings-pane',
			'ajax' => true,
			'ajax_id' => 'tsfem-e-transporter-settings-ajax',
		)
	);
	/*
	\tsf_extension_manager()->_do_pane_wrap(
		\__( 'Transport SEO Meta', 'the-seo-framework-extension-manager' ),
		$this->get_transport_meta_overview(),
		array(
			'full' => false,
			'collapse' => false,
			'move' => false,
			'pane_id' => 'tsfem-e-transporter-meta-pane',
			'ajax' => true,
			'ajax_id' => 'tsfem-e-transporter-meta-ajax',
		)
	);
	*/
?>
</div>
<?php
