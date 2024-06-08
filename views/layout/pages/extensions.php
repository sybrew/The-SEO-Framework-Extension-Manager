<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Pages
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and tsfem()->_verify_instance( $_instance, $bits[1] ) or die;

$this->_do_pane_wrap(
	__( 'Extensions', 'the-seo-framework-extension-manager' ),
	$this->get_extension_overview(),
	[
		'full'     => true,
		'collapse' => false,
		'move'     => false,
		'pane_id'  => 'tsfem-extensions-pane',
		'ajax'     => true,
		'ajax_id'  => 'tsfem-extensions-ajax',
	]
);
$this->_do_pane_wrap(
	__( 'Account and Actions', 'the-seo-framework-extension-manager' ),
	$this->get_extensions_actions_overview(),
	[
		'full'     => false,
		'collapse' => true,
		'move'     => true,
		'pane_id'  => 'tsfem-actions-pane',
		'ajax'     => true,
		'ajax_id'  => 'tsfem-actions-ajax',
	]
);
