<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Monitor\get_active_class() and $this instanceof $_class or die;

?>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
	<?php
	\tsf_extension_manager()->_do_pane_wrap(
		\__( 'Control Panel', 'the-seo-framework-extension-manager' ),
		$this->get_cp_overview(),
		[
			'full'     => false,
			'collapse' => true,
			'move'     => false,
			'pane_id'  => 'tsfem-e-monitor-cp-pane',
			'ajax'     => true,
			'ajax_id'  => 'tsfem-e-monitor-cp-ajax',
		]
	);
	\tsf_extension_manager()->_do_pane_wrap(
		\__( 'Issues', 'the-seo-framework-extension-manager' ),
		$this->get_issues_overview(),
		[
			'full'     => false,
			'collapse' => true,
			'move'     => false,
			'push'     => true,
			'pane_id'  => 'tsfem-e-monitor-issues-pane',
			'ajax'     => true,
			'ajax_id'  => 'tsfem-e-monitor-issues-ajax',
		]
	);
	?>
</div>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
	<?php
	\tsf_extension_manager()->_do_pane_wrap(
		\__( 'Statistics', 'the-seo-framework-extension-manager' ),
		$this->get_stats_overview(),
		[
			'full'     => true,
			'collapse' => true,
			'move'     => false,
			'pane_id'  => 'tsfem-e-monitor-stats-pane',
			'ajax'     => true,
			'ajax_id'  => 'tsfem-e-monitor-stats-ajax',
		]
	);
	?>
</div>
<?php
