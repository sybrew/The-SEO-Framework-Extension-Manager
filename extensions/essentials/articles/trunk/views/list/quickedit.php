<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles\Views
 * @subpackage TSF_Extension_Manager\ListEdit\Structure;
 */

namespace TSF_Extension_Manager\Extension\Articles;

/**
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\ListEdit as ListEdit;

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and ListEdit::verify( $_secret ) or die;

// NOTE: 'type' is the option name, confusingly so.

?>
<div class="inline-edit-group wp-clearfix">
	<label class=clear>
		<?php
		// This is bad accessibility, but it's exactly as bad as WP is, and we don't want to stray away from their standards.
		printf( '<span class=title>%s</span>', \esc_html( $post_meta['type']['label'] ) );
		// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
		echo \the_seo_framework()->make_single_select_form( [
			'id'      => ListEdit::get_quick_option_key( 'type', $pm_index ),
			'name'    => ListEdit::get_quick_option_key( 'type', $pm_index ),
			'options' => $post_meta['type']['options'],
			'default' => '',
		] );
		// phpcs:enable, WordPress.Security.EscapeOutput
		?>
</label>
</div>
