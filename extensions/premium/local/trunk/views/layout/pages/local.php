<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret ) or die;

tsfem()->_do_pane_wrap_callable(
	__( 'Local Settings', 'the-seo-framework-extension-manager' ),
	[ $this, '_local_settings_overview' ],
	[
		'full'       => true,
		'collapse'   => false,
		'move'       => false,
		'pane_id'    => 'tsfem-e-local-settings-pane',
		'ajax'       => true,
		'ajax_id'    => 'tsfem-e-local-settings-ajax',
		'secure_obj' => true,
		'footer'     => [ $this, '_get_local_settings_bottom_wrap' ],
	]
);
