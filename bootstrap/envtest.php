<?php
/**
 * @package TSF_Extension_Manager/Bootstrap
 */

defined( 'TSF_EXTENSION_MANAGER_DB_VERSION' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @NOTE This file MUST be written according to WordPress' minimum PHP requirements.
 *       Which is PHP 5.2.
 */

tsf_extension_manager_pre_boot_test();
/**
 * Tests plugin upgrade.
 *
 * @since 1.5.0
 * @since 2.1.0 Now requires WordPress 4.8+, from 4.7+.
 * @since 2.2.0: 1. Now requires WordPress 4.9+, from 4.7+.
 *               2. Now requires PHP 5.6+, from 5.5+.
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
			if ( get_blog_option( $nw->site_id, 'tsfem_tested_environment_version' ) ) {
				update_option( 'tsfem_tested_environment_version', TSF_EXTENSION_MANAGER_DB_VERSION );
				return;
			}
		}
		//= Free memory.
		unset( $nw );
	}

	$requirements = array(
		'php' => 50605,
		'wp'  => 38590,
	);

	// phpcs:disable, Generic.Formatting.MultipleStatementAlignment, WordPress.WhiteSpace.PrecisionAlignment
	   ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < $requirements['php'] and $test = 1
	or $GLOBALS['wp_db_version'] < $requirements['wp'] and $test = 2
	or $test = true;
	// phpcs:enable, Generic.Formatting.MultipleStatementAlignment, WordPress.WhiteSpace.PrecisionAlignment

	//* All good.
	if ( true === $test ) {
		update_option( 'tsfem_tested_environment_version', TSF_EXTENSION_MANAGER_DB_VERSION );
		return;
	}

	//= Not good. Deactivate plugin and output notification in admin.

	if ( $ms ) {
		$plugins      = get_site_option( 'active_sitewide_plugins' );
		$network_mode = isset( $plugins[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
	} else {
		$network_mode = false;
	}

	if ( ! function_exists( 'deactivate_plugins' ) )
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$admin  = is_admin();
	$silent = ! $admin;

	deactivate_plugins( TSF_EXTENSION_MANAGER_PLUGIN_BASENAME, $silent, $network_mode );

	//* Don't die on front-end.
	if ( ! $admin )
		return;

	switch ( $test ) :
		case 1:
			//* PHP requirements not met, always count up to encourage best standards.
			$requirement = 'PHP 5.6.5 or later';
			$issue       = 'PHP version';
			$version     = phpversion();
			$subtitle    = 'Server Requirements';
			break;

		case 2:
			//* WordPress requirements not met.
			$requirement = 'WordPress 4.9 or later';
			$issue       = 'WordPress version';
			$version     = $GLOBALS['wp_version'];
			$subtitle    = 'WordPress Requirements';
			break;

		default:
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
			esc_url( $pluginspage, array( 'https', 'http' ) )
		),
		sprintf(
			'The SEO Framework - Extension Manager &laquo; %s',
			esc_attr( $subtitle )
		),
		array( 'response' => 500 )
	);
}
