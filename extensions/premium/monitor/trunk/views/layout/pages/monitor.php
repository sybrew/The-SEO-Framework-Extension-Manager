<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin\Views
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) and $_class = monitor_class() and $this instanceof $_class or die;

?>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
<?php
	tsf_extension_manager()->_do_pane_wrap(
		__( 'Issues', 'the-seo-framework-extension-manager' ),
		$this->get_issues_overview(),
		array(
			'full' => false,
			'collapse' => true,
			'move' => false,
			'ajax' => true,
			'ajax_id' => 'tsfem-e-monitor-issues-ajax',
		)
	);
	tsf_extension_manager()->_do_pane_wrap(
		__( 'Points of Interest', 'the-seo-framework-extension-manager' ),
		$this->get_poi_overview(),
		array(
			'full' => false,
			'collapse' => true,
			'move' => false,
			'ajax' => true,
			'ajax_id' => 'tsfem-e-monitor-poi-ajax',
		)
	);
?>
</div>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
<?php
	tsf_extension_manager()->_do_pane_wrap(
		__( 'Statistics', 'the-seo-framework-extension-manager' ),
		$this->get_stats_overview(),
		array(
			'full' => true,
			'collapse' => true,
			'move' => false,
			'ajax' => true,
			'ajax_id' => 'tsfem-e-monitor-stats-ajax',
		)
	);
?>
</div>
<?php
