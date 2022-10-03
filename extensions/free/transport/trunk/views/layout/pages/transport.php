<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret ) or die;

$tsfem = tsfem();

$tsfem->_do_pane_wrap_callable(
	__( 'Importer', 'the-seo-framework-extension-manager' ),
	[ $this, '_importer_overview' ],
	[
		'full'     => false,
		'collapse' => true,
		'wide'     => false,
		'move'     => false,
		'push'     => true,
		'pane_id'  => 'tsfem-e-transport-importer-pane',
		'ajax'     => true,
		'ajax_id'  => 'tsfem-e-transport-importer-ajax',
	]
);
$tsfem->_do_pane_wrap_callable(
	__( 'Logger', 'the-seo-framework-extension-manager' ),
	[ $this, '_logger_overview' ],
	[
		'full'     => true,
		'collapse' => true,
		'move'     => false,
		'pane_id'  => 'tsfem-e-transport-log-pane',
		'ajax'     => true,
		'ajax_id'  => 'tsfem-e-transport-log-ajax',
		'footer'   => [ $this, '_logger_bottom_wrap' ],
	]
);
