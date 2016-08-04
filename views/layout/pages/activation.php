<?php
defined( 'ABSPATH' ) and $this->verify_instance( $_instance, $bits[1] ) or die;

$type = $this->is_plugin_in_network_mode() ? __( 'network', 'the-seo-framework-extension-manager' ) : __( 'website', 'the-seo-framework-extension-manager' );

?>
<div class="tsfem-connect-option tsfem-connect-highlighted">
	<div class="tsfem-connect-description">
		<h3><?php esc_html_e( 'Activate', 'the-seo-framework-extension-manager' ); ?></h3>
		<strong><?php esc_html_e( 'Log in or sign up now.', 'the-seo-framework-extension-manager' ); ?></strong>
		<p><?php esc_html_e( 'Connect your account. Fast and secure.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
	<div class="tsfem-connect-action">
		<div class="tsfem-connect-fields-row">
			<?php
			$this->get_view( 'forms/get', array( 'name' => $this->request_name['activate-external'], 'action' => $this->get_activation_url( 'get/' ), 'redirect' => 'activate', 'text' => __( 'Get your API key', 'the-seo-framework-extension-manager' ), 'classes' => array( 'button', 'button-primary' ) ) );
			$this->get_view( 'forms/get', array( 'name' => $this->request_name['activate-external'], 'action' => $this->get_activation_url( 'get/' ), 'redirect' => 'connect', 'text' => __( 'Connect', 'the-seo-framework-extension-manager' ), 'classes' => array( 'button' ) ) );
			$this->get_remote_activation_listener();
			?>
		</div>
	</div>
</div>

<div class="tsfem-connect-option">
	<div class="tsfem-connect-description">
		<h3><?php esc_html_e( 'Use key', 'the-seo-framework-extension-manager' ); ?></h3>
		<strong><?php esc_html_e( 'Manually enter an API key', 'the-seo-framework-extension-manager' ); ?></strong>
		<p><?php esc_html_e( 'Already have your key? Enter it here.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
	<div class="tsfem-connect-action">
		<?php $this->get_view( 'forms/key', array( 'name' => $this->request_name['activate-key'], 'id' => 'input-activation', 'text' => __( 'Use this key', 'the-seo-framework-extension-manager' ) ) ); ?>
	</div>
</div>

<div class="tsfem-connect-option tsfem-connect-secondary">
	<div class="tsfem-connect-description">
		<h3><?php esc_html_e( 'Go free', 'the-seo-framework-extension-manager' ); ?></h3>
		<strong><?php esc_html_e( 'Unlimited free access', 'the-seo-framework-extension-manager' ); ?></strong>
		<p><?php esc_html_e( 'Rather go for a test-drive? You can always upgrade later.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
	<div class="tsfem-connect-action">
		<?php $this->get_view( 'forms/free', array( 'name' => $this->request_name['activate-free'], 'id' => 'activate-free', 'text' => __( 'Save a few bucks', 'the-seo-framework-extension-manager' ) ) ); ?>
	</div>
</div>
<?php
