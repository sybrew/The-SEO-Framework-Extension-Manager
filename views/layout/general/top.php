<?php
/**
 * @package TSF_Extension_Manager\Core\Views\General
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and tsfem()->_verify_instance( $_instance, $bits[1] ) or die;

$about = $actions = '';

if ( $options ) {

	if ( $this->is_plugin_activated() && $this->is_connected_user() ) {

		$account_link_args = [
			'class' => 'tsfem-button-primary tsfem-button-primary-dark tsfem-button-external',
		];

		$status = $this->get_subscription_status();

		if ( isset( $status['end_date'] ) ) {
			// UTC -- When subscription expires in 6 weeks, warn user via red button:
			if ( strtotime( $status['end_date'] ) < strtotime( '+6 week' ) ) {
				$account_link_args['class'] = 'tsfem-button tsfem-button-red tsfem-button-warning';
				$account_link_args['title'] = __( 'Manage license', 'the-seo-framework-extension-manager' );
			}
		}

		$account_link = $this->get_my_account_link( $account_link_args );
	} else {
		$account_link = $this->get_link( [
			'url'     => $this->get_activation_url( 'shop/' ),
			'target'  => '_blank',
			'class'   => 'tsfem-button-primary tsfem-button-external',
			'title'   => '',
			'content' => __( 'Get license', 'the-seo-framework-extension-manager' ),
		] );
	}

	$account = "<div class=tsfem-top-account>$account_link</div>";
	$actions = '<div class="tsfem-top-actions tsfem-flex tsfem-flex-row">' . $account . '</div>';
}

?>
<div class=tsfem-title>
	<header><h1>
		<?php
		$size = '1em';
		printf(
			'<span class=tsfem-logo>%sExtension Manager</span>',
			sprintf(
				'<svg width="%1$s" height="%1$s">%2$s</svg>',
				esc_attr( $size ),
				sprintf(
					'<image href="%1$s" width="%2$s" height="%2$s" />',
					esc_url( $this->get_image_file_location( 'tsflogo.svg', true ), [ 'https', 'http' ] ),
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
