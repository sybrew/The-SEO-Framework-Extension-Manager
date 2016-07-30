<?php
defined( 'ABSPATH' ) and $this->verify_instance( $_instance, $bits[1] ) or die;

?>
<div class="tsfem-extension-wrapper">
	<?php
	$this->do_pane_wrap(
		__( 'SEO Trends and Updates', 'the-seo-framework-extension-manager' ),
		$this->get_seo_trends_and_updates_overview(),
		array(
			'full' => false,
			'collapse' => true,
			'move' => true,
		)
	);

	$this->do_pane_wrap(
		__( 'Actions', 'the-seo-framework-extension-manager' ),
		$this->get_extensions_actions_overview(),
		array(
			'full' => false,
			'collapse' => true,
			'move' => true,
		)
	);

	$this->do_pane_wrap(
		__( 'Extensions', 'the-seo-framework-extension-manager' ),
		$this->get_extension_overview(),
		array(
			'full' => true,
			'collapse' => false,
			'move' => false,
		)
	);
	?>
</div>
<?php
