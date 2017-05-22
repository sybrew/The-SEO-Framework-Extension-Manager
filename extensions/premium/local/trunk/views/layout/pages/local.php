<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Local\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-panes-row tsfem-flex tsfem-flex-row">
<?php
	\tsf_extension_manager()->_do_pane_wrap_callable(
		\__( 'Local SEO Settings', 'the-seo-framework-extension-manager' ),
		[ $this, '_get_local_settings_overview' ],
		[
			'full' => false,
			'collapse' => false,
			'move' => false,
			'pane_id' => 'tsfem-e-local-settings-pane',
			'ajax' => true,
			'ajax_id' => 'tsfem-e-local-settings-ajax',
			'secure_obj' => true,
		]
	);
?>
</div>
<?php
