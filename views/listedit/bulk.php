<?php
/**
 * @package TSF_Extension_Manager\Core\Views\ListEdit
 * @subpackage TSF_Extension_Manager\Extensions
 */

/**
 * @package TSF_Extension_Manager\Classes
 */
use \TSF_Extension_Manager\ListEdit as ListEdit;

defined( 'ABSPATH' ) and ListEdit::verify( $_secret ) or die;

foreach ( $sections as $section ) {
	?>
	<fieldset class=inline-edit-col-left>
		<legend class=inline-edit-legend><?php echo esc_html( $section['name'] ); ?></legend>
		<div class=inline-edit-col>
			<?php
			call_user_func_array( $section['callback'], $section['args'] );
			?>
		</div>
	</fieldset>
	<?php
}
