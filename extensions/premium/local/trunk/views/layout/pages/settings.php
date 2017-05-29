<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Local\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-pane-inner-collapsable-settings-wrap tsfem-e-local-settings-wrap tsfem-flex">
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-no-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-form-title"><?php \esc_html_e( 'Set departments', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class="tsfem-description"><?php \esc_html_e( 'Fill in these fields as accurately as possible. Abuse might lead to a Structured Data penalty, that can negate these options.', 'the-seo-framework-extension-manager' ); ?></span>
		</div>

		<div class="tsfem-pane-inner-pad">
			<?php $this->_fields( $this->get_departments_head_fields(), 'echo' ); ?>
		</div>

		<div class="tsfem-flex tsfem-flex-row">
		<?php
			$count = $this->get_option( 'depAmount', 1 );
			$dep_main = \__( 'Main Department', 'the-seo-framework-extension-manager' );
			$dep_sub = \__( 'Department', 'the-seo-framework-extension-manager' );

			for ( $it = 0; $it < $count; $it++ ) {
				$title = $it ? $dep_sub : $dep_main;
				$id = "dep-$it";

				//* Already escaped.
				echo $this->get_collapse_wrap( 'start', $it, $title, $id );
				$this->_fields( $this->get_global_department_fields(), 'echo', $id );
				echo $this->get_collapse_wrap( 'end' );
			}
		?>
		</div>
	</div>
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-noshrink tsfem-flex-hide-if-js">
		<h4 class="tsfem-status-title"><?php \esc_html_e( 'JavaScript required', 'the-seo-framework-extension-manager' ); ?></h4>
		<p class="tsfem-description"><?php \esc_html_e( 'Because of the complexity of the settings, JavaScript is required.', 'the-seo-framework-extension-manager' ); ?></p>
	</div>
</div>
<?php
