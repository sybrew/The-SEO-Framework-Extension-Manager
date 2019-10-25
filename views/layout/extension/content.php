<?php

defined( 'ABSPATH' ) and \TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;

// phpcs:disable, PHPCompatibility.Classes.NewLateStaticBinding.OutsideClassScope, VariableAnalysis.CodeAnalysis.VariableAnalysis.StaticOutsideClass -- We're stil in scope.

$_tsfem = \tsf_extension_manager();

foreach ( static::$settings as $index => $params ) {
	$_tsfem->_do_pane_wrap_callable(
		$params['title'],
		static::class . '::_output_pane_settings',
		[
			'logo'     => $params['logo'],
			'full'     => in_array( 'full', $params['pane'], true ),
			'wide'     => in_array( 'wide', $params['pane'], true ),
			'tall'     => in_array( 'tall', $params['pane'], true ),
			'collapse' => true,
			'move'     => true,
			'pane_id'  => 'tsfem-extension-settings-pane-' . $index,
			'ajax'     => true,
			'ajax_id'  => 'tsfem-extension-settings-ajax-' . $index,
			'footer'   => static::class . '::_output_pane_settings_footer',
			'cbargs'   => [ $index, $params['settings'] ],
			'fcbargs'  => [ $index ],
		]
	);
}
