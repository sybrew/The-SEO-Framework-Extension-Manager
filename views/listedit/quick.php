<?php
/**
 * @package TSF_Extension_Manager\Core\Views\ListEdit
 * @subpackage TSF_Extension_Manager\Extensions
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * @package TSF_Extension_Manager\Classes
 */
use TSF_Extension_Manager\ListEdit as ListEdit;

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and ListEdit::verify( $_secret ) or die;

foreach ( $sections as $section ) {
	?>
	<fieldset class=inline-edit-col-left>
		<legend class=inline-edit-legend><?= esc_html( $section['name'] ) ?></legend>
		<div class=inline-edit-col>
			<?php
			call_user_func_array( $section['callback'], $section['args'] );
			?>
		</div>
	</fieldset>
	<?php
}
