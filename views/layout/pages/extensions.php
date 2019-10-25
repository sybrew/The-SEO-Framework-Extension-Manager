<?php

defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

$this->_do_pane_wrap(
	\__( 'Extensions', 'the-seo-framework-extension-manager' ),
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
	\__( 'Account and Actions', 'the-seo-framework-extension-manager' ),
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

if ( function_exists( 'simplexml_load_string' ) ) {
	$this->_do_pane_wrap(
		\__( 'SEO Trends and Updates', 'the-seo-framework-extension-manager' ),
		$this->get_seo_trends_and_updates_overview(),
		[
			'full'     => false,
			'collapse' => true,
			'move'     => true,
			'push'     => true,
			'pane_id'  => 'tsfem-feed-pane',
			'ajax'     => true,
			'ajax_id'  => 'tsfem-feed-ajax',
		]
	);
}
