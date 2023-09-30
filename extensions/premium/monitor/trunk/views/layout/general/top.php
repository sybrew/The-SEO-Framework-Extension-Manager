<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret ) or die;

$about   = '';
$actions = '';

?>
<div class=tsfem-title>
	<header><h1>
		<?php
		$size = '1em';
		printf(
			'<span class=tsfem-logo>%sMonitor</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				esc_attr( $size ),
				sprintf(
					'<image href="%1$s" width="%2$s" height="%2$s" />',
					esc_url( \TSFEM_E_MONITOR_DIR_URL . 'lib/images/icon.svg', [ 'https', 'http' ] ),
					esc_attr( $size )
				)
			)
		);
		?>
	</h1></header>
</div>
<?php

// phpcs:ignore, WordPress.Security.EscapeOutput -- Already escaped.
echo $about, $actions;
