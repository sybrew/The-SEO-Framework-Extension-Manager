<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret );

?>
<div class=tsfem-pane-inner-wrap id=tsfem-e-transport-logger-wrap>
	<div class="tsfem-pane-section">
		<pre class=tsfem-logger id=tsfem-e-transport-logger><?= esc_html__( 'Waiting for transport&hellip;', 'the-seo-framework-extension-manager' ) ?></pre>
	</div>
	<div class="tsfem-pane-section">
		<em><?= esc_html__( 'Logger will stop reporting if you lose connection. Transactions may still continue on the server.', 'the-seo-framework-extension-manager' ) ?></em>
	</div>
</div>
