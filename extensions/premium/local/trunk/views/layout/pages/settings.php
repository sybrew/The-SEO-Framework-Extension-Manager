<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin\Views
 */

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Local\get_layout_class() and $this instanceof $_class or die;

?>
<div class="tsfem-pane-inner-collapsable-settings-wrap tsfem-e-local-settings-wrap">
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-info-title"><?php \esc_html_e( 'JavaScript required', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class="tsfem-description"><?php \esc_html_e( 'Because of the complexity of the settings, JavaScript is required.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
	</div>
	<div class="tsfem-e-local-settings tsfem-flex tsfem-flex-row tsfem-flex-nogrow tsfem-flex-hide-if-no-js">
		<div class="tsfem-pane-inner-pad">
			<h4 class="tsfem-form-title"><?php \esc_html_e( 'Set departments', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class="tsfem-description"><?php \esc_html_e( 'Fill in these fields as accurately as possible.' ); ?></span><br>
			<span class="tsfem-description"><?php \esc_html_e( 'If a field doesn\'t allow a right answer, leave it empty.', 'the-seo-framework-extension-manager' ); ?></span>
		</div>
		<div class="tsfem-pane-inner-pad">
			<?php $this->_fields( $this->get_departments_head_fields(), 'echo' ); ?>
		</div>
		<?php
		/**
		 * Defer visual rendering of elements to prevent excessive painting if lots
		 * of elements are loaded. Shaves off 25% load time on high-end machines.
		 */
		$count = $this->get_option( 'depAmount', 1 );
		$i_defer = 20;
		$defer = $count > $i_defer;

		$defer and printf( '<div class="%s" id="tsfem-e-local-deps-loading" style=padding-top:4vh><span></span></div>', 'tsfem-flex-status-loading tsfem-flex tsfem-flex-center' );
		?>
		<div class="tsfem-e-local-collapse-wrap" id="tsfem-e-local-deps-overview" <?php $defer and print 'style=display:none'; ?>>
		<?php
		$dep_main = \__( 'Main Department', 'the-seo-framework-extension-manager' );
		$dep_sub = \__( 'Department', 'the-seo-framework-extension-manager' );

		$_fields = $this->get_global_department_fields();

		for ( $it = 0; $it < $count; $it++ ) {
			$title = $it ? $dep_sub : $dep_main;
			$id = "dep-$it";

			//* Already escaped.
			echo $this->get_collapse_wrap( 'start', $it, $title, $id );
			$this->_fields( $_fields, 'echo', $id );
			//* Already escaped.
			echo $this->get_collapse_wrap( 'end' );
		}
		?>
		</div>
		<?php
		$defer and print '<script>document.getElementById("tsfem-e-local-deps-loading").outerHTML=null;document.getElementById("tsfem-e-local-deps-overview").style=null;</script>';
		?>
	</div>
</div>
<?php
