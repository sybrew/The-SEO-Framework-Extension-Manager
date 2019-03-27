<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Cord\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-pane-inner-wrap tsfem-pane-inner-collapsable-settings-wrap tsfem-e-cord-logs-wrap">
	<div class="tsfem-e-cord-logs tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-no-js">
		<div class="tsfem-pane-inner-pad">
			<?php echo 'Hello logs!'; ?>
		</div>
	</div>
</div>
<?php
