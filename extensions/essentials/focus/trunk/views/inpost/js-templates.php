<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit\Templates;
 */

namespace TSF_Extension_Manager\Extension\Focus;

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\InpostGUI as InpostGUI;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \The_SEO_Framework\Builders\Scripts::verify( $_secret ) or die;

?>
<script type=text/html id=tmpl-tsfem-e-focus-nofocus>
	<div><span><?php \esc_html_e( 'No elements are found that support this feature.', 'the-seo-framework-extension-manager' ); ?></span></div>
</script>

<script type=text/html id=tmpl-tsfem-e-focus-subject-item>
	<label class=tsfem-e-focus-subject-item>
		<input type=checkbox id={{{data.id}}} class=tsfem-e-focus-subject-item {{{data.disabled}}} value=1 {{{data.checked}}}>
		<span>{{data.value}}</span>
	</label>
</script>
<?php
