<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Admin
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
* Require user interface trait.
* @since 1.0.0
*/
\TSF_Extension_Manager\_load_trait( 'ui' );

/**
 * Require extension forms trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension-forms' );

/**
 * Require error trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'error' );

/**
 * Require Local security trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\Extension\Local\_load_trait( 'secure-post' );

/**
 * Require Local options template trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\Extension\Local\_load_trait( 'options-template' );

/**
 * Require Local settings generator trait.
 * @since 1.0.0
 */
\TSF_Extension_Manager\Extension\Local\_load_trait( 'settings-generator' );

/**
 * Class TSF_Extension_Manager\Extension\Local\Settings
 *
 * Holds extension settings methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 107xxxx
 */
final class Settings {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Core_Static_Final_Instance,
		\TSF_Extension_Manager\UI,
		\TSF_Extension_Manager\Extension_Forms,
		\TSF_Extension_Manager\Extension_Options,
		\TSF_Extension_Manager\Error,
		Secure_Post,
		Options_Template,
		Settings_Generator;

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Local\Core $_core Used for integrity.
	 * @param string $hook The menu hook.
	 */
	public function _init( Core $_core, $hook ) {

		/**
		 * Set options index.
		 * @see trait TSF_Extension_Manager\Extension_Options
		 */
		$this->o_index = 'local';

		/**
		 * Set error notice option.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->error_notice_option = 'tsfem_e_local_error_notice_option';

		/**
		 * Initialize error interface.
		 * @see trait TSF_Extension_Manager\Error
		 */
		$this->init_errors();

		/**
		 * Sets nonces.
		 * @see trait TSF_Extension_Manager\Extension\Local\Secure_Post
		 */
		$this->set_nonces();

		/**
		 * Initialize POST data checks.
		 * @see trait TSF_Extension_Manager\Extension\Local\Secure_Post
		 */
		$this->init_post_checks();

		/**
		 * Set UI hook.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->ui_hook = $hook;

		/**
		 * Initialize user interface.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_tsfem_ui();
	}

	/**
	 * Initializes and outputs Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object \TSF_Extension_Manager\Extension\Local\Core $_core Used for integrity.
	 */
	public function _output_settings_page( Core $_core ) {
		$this->output_admin_page();
	}

	/**
	 * Initializes user interface styles, scripts and footer.
	 *
	 * @since 1.0.0
	 * @see trait TSF_Extension_Manager\UI
	 */
	private function init_tsfem_ui() {

		/**
		 * Set additional CSS file calls.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->additional_css[] = [
			'name' => 'tsfem-local',
			'base' => TSFEM_E_LOCAL_DIR_URL,
			'ver' => TSFEM_E_LOCAL_VERSION,
		];

		/**
		 * Set additional JS file calls.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->additional_js[] = [
			'name' => 'tsfem-local',
			'base' => TSFEM_E_LOCAL_DIR_URL,
			'ver' => TSFEM_E_LOCAL_VERSION,
		];

		/**
		 * Set additional l10n.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->additional_l10n[] = [
			'dependency' => 'tsfem-local',
			'name' => 'tsfem_e_localL10n',
			'strings' => [
				'nonce' => \wp_create_nonce( 'tsfem-e-local-ajax-nonce' ),
			],
		];

		//* Add something special for Vivaldi
		\add_action( 'admin_head', [ $this, '_output_theme_color_meta' ], 0 );

		//* Add footer output.
		\add_action( 'in_admin_footer', [ $this, '_init_local_footer_wrap' ] );

		/**
		 * Initialize UI calls.
		 * @see trait TSF_Extension_Manager\UI
		 */
		$this->init_ui();
	}

	/**
	 * Outputs the admin page.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	protected function output_admin_page() {
		?>
		<div class="wrap tsfem tsfem-flex tsfem-flex-nowrap tsfem-flex-nogrowshrink">
			<?php $this->output_local_overview_wrapper(); ?>
		</div>
		<?php
	}

	/**
	 * Echos main page wrapper.
	 *
	 * @since 1.0.0
	 */
	protected function output_local_overview_wrapper() {

		$this->do_page_top_wrap();

		?>
		<div class="tsfem-panes-wrap tsfem-flex tsfem-flex-nowrap">
			<?php $this->do_local_overview(); ?>
		</div>
		<?php
	}

	/**
	 * Echos the page top wrap.
	 *
	 * @since 1.0.0
	 */
	protected function do_page_top_wrap() {
		$this->get_view( 'layout/general/top' );
	}

	/**
	 * Echos the settings overview.
	 *
	 * @since 1.0.0
	 */
	protected function do_local_overview() {
		$this->get_view( 'layout/pages/local' );
	}

	/**
	 * Outputs Settings Panel overview for Local SEO settings.
	 *
	 * @since 1.0.0
	 *
	 * @param object \TSF_Extension_Manager\Extension\Local\Settings $_i Used for integrity.
	 */
	public function _get_local_settings_overview( self $_i ) {
		$this->get_view( 'layout/pages/settings' );
	}

	/**
	 * Outputs the admin footer.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _init_local_footer_wrap() {
		?>
		<div class="tsfem-footer-wrap tsfem-flex tsfem-flex-nowrap tsfem-disable-cursor">
			<?php $this->get_view( 'layout/general/footer' ); ?>
		</div>
		<?php
	}

	/**
	 * Outputs theme color meta tag for Vivaldi and mobile browsers.
	 * Does not always work. So many browser bugs... It's just fancy.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function _output_theme_color_meta() {
		$this->get_view( 'layout/general/meta' );
	}

	protected function get_collapse_wrap( $what, $it = 0, $title = '', $id = '' ) {

		if ( 'start' === $what ) {
			$id = $id ? sprintf( 'id="tsfem-e-local-collapse[%s]"', \esc_attr( $id ) ) : '';

			$checkbox_id = sprintf( 'tsfem-e-local-collapse-checkbox-%s', $it );
			$checkbox = sprintf( '<input type="checkbox" id="%s" checked>', $checkbox_id );

			//* Requires JS to edit. Always start with warning.
			$state = 'warning';
			$title = $this->get_state_icon( $state ) . \esc_html( $title );

			$title = sprintf( '<h3 class="tsfem-e-local-collapse-title">%s</h3>', $title );
			$icon = sprintf( '<span class="tsfem-e-local-collapse-icon tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-e-local-icon-%s"></span>', $state );

			$header = vsprintf( '<label class="tsfem-e-local-collapse-header tsfem-flex tsfem-flex-row tsfem-flex-nowrap tsfem-flex-nogrow tsfem-flex-space" for="%s">%s%s</label>',
				[
					$checkbox_id,
					$title,
					$icon,
				]
			);

			$content_start = '<div class="tsfem-e-local-collapse-content tsfem-flex">';

			//* Already escaped.
			return sprintf( '<div class="tsfem-e-local-collapse tsfem-flex tsfem-flex-noshrink tsfem-flex-row" %s>%s%s%s', $id, $checkbox, $header, $content_start );
		} elseif ( 'end' === $what ) {
			//* ok.
			return '</div></div>';
		}

		return '';
	}

	/**
	 * Parses entry state HTMl icon.
	 *
	 * @since 1.0.0
	 *
	 * @param string $state The icon state.
	 * @return string The HTML formed entry state icon.
	 */
	protected function get_state_icon( $state = '' ) {
		return sprintf( '<span class="tsfem-e-local-title-icon tsfem-e-local-icon-%1$s tsfem-e-local-title-icon-%s"></span>', $this->parse_defined_icon_state( $state ) );
	}

	/**
	 * Parses known CSS icon states.
	 *
	 * @since 1.0.0
	 *
	 * @param string $state The could-be unknown state.
	 * @return string The known state.
	 */
	protected function parse_defined_icon_state( $state = '' ) {

		switch ( $state ) :
			case 'good' :
			case 'okay' :
			case 'warning' :
			case 'bad' :
			case 'error' :
				break;

			default :
				$state = 'unknown';
				break;
		endswitch;

		return $state;
	}

	/**
	 * Fetches files based on input to reduce memory overhead.
	 * Passes on input vars.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view The file name.
	 * @param array $args The arguments to be supplied within the file name.
	 *        Each array key is converted to a variable with its value attached.
	 */
	protected function get_view( $view, array $args = [] ) {

		foreach ( $args as $key => $val ) {
			$$key = $val;
		}

		$file = TSFEM_E_LOCAL_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

		include( $file );
	}
}
