<?php
defined( 'ABSPATH' ) and \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

// Todo do something with network mode here. Remove backslashes (preventing POT generation).
//$type = $this->is_plugin_in_network_mode() ? _\_( 'network', 'the-seo-framework-extension-manager' ) : _\_( 'website', 'the-seo-framework-extension-manager' );

if ( ! $this->is_auto_activated() ) :
?>
<div class=tsfem-connect-option>
	<div class="tsfem-connect-row tsfem-flex tsfem-flex-row">
		<div class="tsfem-connect-text tsfem-flex">
			<div class=tsfem-connect-description>
				<h3><?php \esc_html_e( 'Get your key', 'the-seo-framework-extension-manager' ); ?></h3>
				<strong><?php \esc_html_e( 'Log in or sign up now', 'the-seo-framework-extension-manager' ); ?></strong>
				<p><?php \esc_html_e( 'Get your key. Easy and secure.', 'the-seo-framework-extension-manager' ); ?></p>
			</div>
		</div>
		<div class="tsfem-connect-action tsfem-flex">
			<?php
			//* TODO activation listener. Version 3.x
			// $this->get_view( 'forms/get', [ 'name' => $this->request_name['activate-external'], 'action' => $this->get_activation_url( 'get/' ), 'redirect' => 'activate', 'text' => \__( 'Get your API key', 'the-seo-framework-extension-manager' ), 'classes' => [ 'tsfem-button', 'tsfem-button-primary' ] ] );
			// $this->get_view( 'forms/get', [ 'name' => $this->request_name['activate-external'], 'action' => $this->get_activation_url( 'get/' ), 'redirect' => 'connect', 'text' => \__( 'Connect', 'the-seo-framework-extension-manager' ), 'classes' => [ 'tsfem-button' ] ] );
			// $this->get_remote_activation_listener();

			//* Already escaped.
			echo $this->get_link( [
				'url'     => $this->get_activation_url( 'shop/' ),
				'target'  => '_blank',
				'class'   => 'tsfem-button-primary',
				'title'   => '',
				'content' => \__( 'Get your API key', 'the-seo-framework-extension-manager' ),
			] );
			?>
		</div>
	</div>
</div>

<div class=tsfem-connect-option>
	<div class="tsfem-connect-row tsfem-flex tsfem-flex-row">
		<div class="tsfem-connect-text tsfem-flex">
			<div class=tsfem-connect-description>
				<h3><?php \esc_html_e( 'Use your key', 'the-seo-framework-extension-manager' ); ?></h3>
				<strong><?php \esc_html_e( 'Get access to premium extensions', 'the-seo-framework-extension-manager' ); ?></strong>
				<p><?php \esc_html_e( 'Already have your key? Enter it here.', 'the-seo-framework-extension-manager' ); ?></p>
			</div>
		</div>
		<div class="tsfem-connect-action tsfem-flex">
			<?php
			$this->get_view( 'forms/key', [
				'name' => $this->request_name['activate-key'],
				'id' => 'input-activation',
				'classes' => [
					'tsfem-button-primary',
				],
				'classes_form' => [
					'tsfem-flex',
					'tsfem-flex-nowrap',
				],
				'text' => \__( 'Use this key', 'the-seo-framework-extension-manager' ),
			] );
			?>
		</div>
	</div>
</div>
<?php
endif; // End if ( ! $this->is_auto_activated() )
?>
<div class=tsfem-connect-option>
	<div class="tsfem-connect-row tsfem-flex tsfem-flex-row">
		<div class="tsfem-connect-text tsfem-flex">
			<div class=tsfem-connect-description>
				<h3><?php \esc_html_e( 'Go free', 'the-seo-framework-extension-manager' ); ?></h3>
				<strong><?php \esc_html_e( 'Get access to free extensions', 'the-seo-framework-extension-manager' ); ?></strong>
				<p><?php \esc_html_e( 'You can always upgrade later.', 'the-seo-framework-extension-manager' ); ?></p>
			</div>
		</div>
		<div class="tsfem-connect-action tsfem-flex">
			<?php
			$this->get_view( 'forms/free', [
				'name' => $this->request_name['activate-free'],
				'id' => 'activate-free',
				'text' => \__( 'Save a few bucks', 'the-seo-framework-extension-manager' ),
			] );
			?>
		</div>
	</div>
</div>
<?php
