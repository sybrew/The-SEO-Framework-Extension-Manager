<?php
$this->verify_instance( $_instance ) or die;

$type = $this->is_plugin_in_network_mode() ? __( 'network', 'the-seo-framework-extension-manager' ) : __( 'website', 'the-seo-framework-extension-manager' );

?>
<p><?php printf( esc_html__( 'Add more powerful SEO features to your %s. To get started, use one of the options below.', 'the-seo-framework-extension-manager' ), esc_html( $type ) ); ?></p>

<div class="connect-option connect-highlighted">
	<div class="connect-description">
		<h3><?php esc_html_e( 'Activate', 'the-seo-framework-extension-manager' ); ?></h3>
		<strong><?php esc_html_e( 'Log in or sign up now.', 'the-seo-framework-extension-manager' ); ?></strong>
		<p><?php esc_html_e( 'Connect your account. Fast and secure.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
	<div class="connect-action">
		<div class="connect-fields-row">
			<?php
			$this->get_view( 'forms/get', array( 'name' => $this->activation_type['external'], 'action' => $this->get_activation_url( 'get/' ), 'redirect' => 'activate', 'text' => __( 'Get your API key', 'the-seo-framework-extension-manager' ), 'classes' => array( 'button', 'button-primary' ) ) );
			$this->get_view( 'forms/get', array( 'name' => $this->activation_type['external'], 'action' => $this->get_activation_url( 'get/' ), 'redirect' => 'connect', 'text' => __( 'Connect', 'the-seo-framework-extension-manager' ), 'classes' => array( 'button' ) ) );
			$this->get_remote_activation_listener();
			?>
		</div>
	</div>
</div>

<div class="connect-option">
	<div class="connect-description">
		<h3><?php esc_html_e( 'Use key', 'the-seo-framework-extension-manager' ); ?></h3>
		<strong><?php esc_html_e( 'Manually enter an API key', 'the-seo-framework-extension-manager' ); ?></strong>
		<p><?php esc_html_e( 'Already have your key? Enter it here.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
	<div class="connect-action">
		<?php $this->get_view( 'forms/key', array( 'name' => $this->activation_type['input'], 'id' => 'input-activation', 'text' => __( 'Use this key', 'the-seo-framework-extension-manager' ) ) ); ?>
	</div>
</div>

<div class="connect-option connect-secondary">
	<div class="connect-description">
		<h3><?php esc_html_e( 'Go free', 'the-seo-framework-extension-manager' ); ?></h3>
		<strong><?php esc_html_e( 'Unlimited free access', 'the-seo-framework-extension-manager' ); ?></strong>
		<p><?php esc_html_e( 'Rather go for a test-drive? You can always upgrade later.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
	<div class="connect-action">
		<?php $this->get_view( 'forms/free', array( 'name' => $this->activation_type['free'], 'id' => 'activate-free', 'text' => __( 'Save a few bucks', 'the-seo-framework-extension-manager' ) ) ); ?>
	</div>
</div>
<?php
