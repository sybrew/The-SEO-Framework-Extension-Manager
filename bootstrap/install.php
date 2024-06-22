<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * copyright (C) 2022 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\add_action( 'tsfem_needs_the_seo_framework', __NAMESPACE__ . '\\_prepare_tsf_installer' );
/**
 * Prepares scripts for TSF "WP v4.6 Shiny Updates" installation.
 *
 * @since 2.2.0
 * @access private
 */
function _prepare_tsf_installer() {

	if ( ! \is_main_site() ) return;
	if ( ! \current_user_can( 'install_plugins' ) ) return;
	if ( 'update.php' === $GLOBALS['pagenow'] ) return;

	if ( ! \function_exists( 'get_plugins' ) )
		require_once \ABSPATH . 'wp-admin/includes/plugin.php';

	$plugins = \get_plugins();

	if ( isset( $plugins['autodescription/autodescription.php'] ) || isset( $plugins['the-seo-framework/autodescription.php'] ) ) return;

	\add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\_prepare_tsf_nag_installer_scripts' );
	\add_action( 'admin_notices', __NAMESPACE__ . '\\_nag_install_tsf' );
}

/**
 * Registers and enqueues the TSF installer-nag required scripts.
 *
 * @since 2.5.0
 * @access private
 */
function _prepare_tsf_nag_installer_scripts() {
	$deps     = [
		'plugin-install',
		'updates',
	];
	$scriptid = 'tsfinstaller';
	$suffix   = \SCRIPT_DEBUG ? '' : '.min';
	$strings  = [
		'slug' => 'autodescription',
	];

	\wp_register_script( $scriptid, \TSF_EXTENSION_MANAGER_DIR_URL . "lib/js/{$scriptid}{$suffix}.js", $deps, \TSF_EXTENSION_MANAGER_VERSION, true );
	\wp_localize_script( $scriptid, "{$scriptid}L10n", $strings );

	\add_action( 'admin_print_styles', __NAMESPACE__ . '\\_print_tsf_nag_installer_styles' );
	\add_action( 'admin_footer', 'wp_print_request_filesystem_credentials_modal' );
	\add_action( 'admin_footer', 'wp_print_admin_notice_templates' );

	\wp_enqueue_style( 'plugin-install' );
	\wp_enqueue_script( $scriptid );
	\add_thickbox();
}

/**
 * Outputs "button-small" "Shiny Updates" compatibility style.
 *
 * @since 2.2.0
 * @access private
 */
function _print_tsf_nag_installer_styles() {
	echo '<style>#tsfem-tsf-tb,#tsfem-tsf-install{margin-left:7px}#tsfem-tsf-install.updating-message:before{font-size:16px;vertical-align:top}</style>';
}

/**
 * Nags the site administrator to install TSF to continue.
 *
 * @since 2.2.0
 * @access private
 */
function _nag_install_tsf() {

	$plugin_slug = 'autodescription';
	$tsf_text    = 'The SEO Framework';

	/**
	 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/import.php#L162-L178
	 * @uses Spaghetti.
	 * @see WP Core class Plugin_Installer_Skin
	 */
	$details_url      = \add_query_arg(
		[
			'tab'       => 'plugin-information',
			'plugin'    => $plugin_slug,
			'from'      => 'plugins',
			'TB_iframe' => 'true',
			'width'     => '600',
			'height'    => '550',
		],
		\network_admin_url( 'plugin-install.php' )
	);
	$tsf_details_link = sprintf(
		'<a href="%1$s" id=tsfem-tsf-tb class="thickbox open-plugin-details-modal button button-small" aria-label="%2$s">%3$s</a>',
		\esc_url( $details_url ),
		/* translators: %s: Plugin name */
		\esc_attr( sprintf( \__( 'Learn more about %s', 'the-seo-framework-extension-manager' ), $tsf_text ) ),
		\esc_html__( 'View plugin details', 'the-seo-framework-extension-manager' )
	);
	$nag = sprintf(
		/* translators: 1 = Extension Manager, 2 = The SEO Framework, 3 = View plugin details. */
		\esc_html__( '%1$s requires %2$s plugin to function. %3$s', 'the-seo-framework-extension-manager' ),
		sprintf( '<strong>%s</strong>', 'Extension Manager' ),
		sprintf( '<strong>%s</strong>', \esc_html( $tsf_text ) ),
		$tsf_details_link
	);

	/**
	 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/import.php#L125-L138
	 * @uses Bolognese sauce.
	 * @see The closest bowl of spaghetti. Or WordPress\Administration\wp.updates/updates.js
	 * This joke was brought to you by the incomplete API of WP Shiny Updates, where
	 * WP's import.php has been directly injected into, rather than "calling" it via its API.
	 * Therefore, leaving the incompleteness undiscovered internally.
	 * @TODO Open core track ticket.
	 */
	$install_nonce_url = \wp_nonce_url(
		\add_query_arg(
			[
				'action' => 'install-plugin',
				'plugin' => $plugin_slug,
				'from'   => 'plugins',
			],
			\self_admin_url( 'update.php' )
		),
		"install-plugin_$plugin_slug"
	);
	$install_action    = sprintf(
		'<a href="%1$s" id=tsfem-tsf-install class="install-now button button-small button-primary" data-slug="%2$s" data-name="%3$s" aria-label="%4$s">%5$s</a>',
		\esc_url( $install_nonce_url ),
		\esc_attr( $plugin_slug ),
		\esc_attr( $tsf_text ),
		/* translators: %s: The SEO Framework */
		\esc_attr( sprintf( \__( 'Install %s', 'the-seo-framework-extension-manager' ), $tsf_text ) ),
		\esc_html__( 'Install Now', 'the-seo-framework-extension-manager' )
	);

	// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- it is.
	printf(
		'<div class="notice notice-info"><p>%s</p></div>',
		\is_rtl() ? "$install_action $nag" : "$nag $install_action"
	);
	// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
}
