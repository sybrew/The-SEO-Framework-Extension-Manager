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
		\__( 'Transport', 'the-seo-framework-extension-manager' ),
		$this->get_transport_overview(),
		array(
			'full' => false,
			'collapse' => true,
			'move' => false,
			'pane_id' => 'tsfem-e-transporter-transport-pane',
			'ajax' => true,
			'ajax_id' => 'tsfem-e-monitor-transport-ajax',
		)
	);
	\tsf_extension_manager()->_do_pane_wrap(
		\__( 'Validate', 'the-seo-framework-extension-manager' ),
		$this->get_validate_overview(),
		array(
			'full' => false,
			'collapse' => true,
			'move' => false,
			'pane_id' => 'tsfem-e-monitor-validate-pane',
			'ajax' => true,
			'ajax_id' => 'tsfem-e-monitor-validate-ajax',
		)
	);
?>
</div>
<?php
