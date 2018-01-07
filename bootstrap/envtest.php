<?php
/**
 * @package TSF_Extension_Manager/Bootstrap
 */

defined( 'TSF_EXTENSION_MANAGER_VERSION' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

tsf_extension_manager_pre_boot_test();
/**
 * Tests plugin upgrade.
 *
 * @since 1.5.0
 * @access private
 * @link http://php.net/eol.php
 * @link https://codex.wordpress.org/WordPress_Versions
 *
 * @return void
 */
function tsf_extension_manager_pre_boot_test() {

	$ms = is_multisite();

	if ( $ms && function_exists( 'get_network' ) ) {
		//* Try bypassing testing and deactivation gaming when the main blog has already been tested.
		$nw = get_network();
		if ( $nw instanceof WP_Network ) {
			//= Free memory. Var is not passed by reference, so it's safe.
			unset( $nw );

			if ( get_blog_option( $nw->site_id, 'tsfem_tested_environment_version' ) ) {
				update_option( 'tsfem_tested_environment_version', TSF_EXTENSION_MANAGER_DB_VERSION );
				return;
			}
		}
	}

	$_req = array(
		'php' => array(
			'5.5' => 50521,
			'5.6' => 50605,
		),
		'wp' => '37965',
	);

	   PHP_VERSION_ID < $_req['php']['5.5'] and $test = 1
	or PHP_VERSION_ID >= 50600 && PHP_VERSION_ID < $_req['php']['5.6'] and $test = 2
	or $GLOBALS['wp_db_version'] < $_req['wp'] and $test = 3
	or $test = true;

	//* All good.
	if ( true === $test ) {
		update_option( 'tsfem_tested_environment_version', TSF_EXTENSION_MANAGER_DB_VERSION );
		return;
	}

	//= Not good. Deactivate plugin and output notification in admin.

	if ( $ms ) {
		$plugins = get_site_option( 'active_sitewide_plugins' );
		$network_mode = isset( $plugins[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
	} else {
		$network_mode = false;
	}

	if ( ! function_exists( 'deactivate_plugins' ) )
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$admin = is_admin();
	$silent = ! $admin;

	deactivate_plugins( TSF_EXTENSION_MANAGER_PLUGIN_BASENAME, $silent, $network_mode );

	//* Don't die on front-end.
	if ( ! $admin )
		return;

	switch ( $test ) :
		case 1 :
		case 2 :
			//* PHP requirements not met, always count up to encourage best standards.
			$requirement = 1 === $test ? 'PHP 5.5.21 or later' : 'PHP 5.6.5 or later';
			$issue = 'PHP version';
			$version = phpversion();
			$subtitle = 'Server Requirements';
			break;

		case 3 :
			//* WordPress requirements not met.
			$requirement = 'WordPress 4.6 or later';
			$issue = 'WordPress version';
			$version = $GLOBALS['wp_version'];
			$subtitle = 'WordPress Requirements';
			break;

		default :
			wp_die();
	endswitch;

	//* network_admin_url() falls back to admin_url() on single. But networks can enable single too.
	$pluginspage = $network_mode ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );

	wp_die(
		sprintf(
			'<p><strong>The SEO Framework - Extension Manager</strong> requires <em>%s</em>. Sorry about that!<br>Your %s is: <code>%s</code></p>
			<p>Do you want to <strong><a onclick="window.history.back()" href="%s">go back</a></strong>?</p>',
			esc_html( $requirement ),
			esc_html( $issue ),
			esc_html( $version ),
			esc_url( $pluginspage, array( 'http', 'https' ) )
		),
		sprintf(
			'The SEO Framework - Extension Manager &laquo; %s',
			esc_attr( $subtitle )
		),
		array( 'response' => 500 )
	);
}
