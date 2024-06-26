<?php
/**
 * @package TSF_Extension_Manager\Core\Views\Extension
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.
// phpcs:disable, PHPCompatibility.Classes.NewLateStaticBinding, VariableAnalysis.CodeAnalysis.VariableAnalysis -- we're in scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and TSF_Extension_Manager\ExtensionSettings::verify( $_secret ) or die;

$_settings = static::$settings;

uasort(
	$_settings,
	fn ( $a, $b ) => $a['priority'] <=> $b['priority']
);

$_tsfem = tsfem();

foreach ( $_settings as $index => $params ) {
	$_tsfem->_do_pane_wrap_callable(
		$params['title'],
		[ static::class, '_output_pane_settings' ],
		[
			'logo'     => $params['logo'],
			'full'     => in_array( 'full', $params['pane'], true ),
			'wide'     => in_array( 'wide', $params['pane'], true ),
			'tall'     => in_array( 'tall', $params['pane'], true ),
			'collapse' => true,
			'move'     => true,
			'pane_id'  => "tsfem-extension-settings-pane-$index",
			'ajax'     => true,
			'ajax_id'  => "tsfem-extension-settings-ajax-$index",
			'footer'   => [ static::class, '_output_pane_settings_footer' ],
			'cbargs'   => [ $index, $params['settings'] ],
			'fcbargs'  => [ $index ],
		]
	);
}
