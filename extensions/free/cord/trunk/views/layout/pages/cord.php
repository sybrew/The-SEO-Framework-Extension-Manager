<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Cord\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
	<?php
	\tsf_extension_manager()->_do_pane_wrap_callable(
		\__( 'Settings', 'the-seo-framework-extension-manager' ),
		[ $this, '_get_cord_settings_overview' ],
		[
			'full'       => false,
			'collapse'   => true,
			'move'       => false,
			'pane_id'    => 'tsfem-e-cord-settings-pane',
			'ajax'       => true,
			'ajax_id'    => 'tsfem-e-cord-settings-ajax',
			'secure_obj' => true,
			'footer'     => [ $this, '_get_cord_settings_bottom_wrap' ],
		]
	);
	\tsf_extension_manager()->_do_pane_wrap_callable(
		\__( 'Statistics', 'the-seo-framework-extension-manager' ),
		[ $this, '_get_cord_stats_overview' ],
		[
			'full'       => false,
			'collapse'   => true,
			'move'       => false,
			'push'       => true,
			'pane_id'    => 'tsfem-e-cord-stats-pane',
			'ajax'       => true,
			'ajax_id'    => 'tsfem-e-cord-stats-ajax',
			'secure_obj' => true,
			'footer'     => [ $this, '_get_cord_stats_bottom_wrap' ],
		]
	);
	?>
</div>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
	<?php
	\tsf_extension_manager()->_do_pane_wrap_callable(
		\__( 'Logs', 'the-seo-framework-extension-manager' ),
		[ $this, '_get_cord_logs_overview' ],
		[
			'full'       => true,
			'collapse'   => true,
			'move'       => false,
			'pane_id'    => 'tsfem-e-cord-logs-pane',
			'ajax'       => true,
			'ajax_id'    => 'tsfem-e-cord-logs-ajax',
			'secure_obj' => true,
			'secure_obj' => true,
			'footer'     => [ $this, '_get_cord_logs_bottom_wrap' ],
		]
	);
	?>
</div>
<?php
