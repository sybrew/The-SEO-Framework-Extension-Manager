<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Facade Class TSF_Extension_Manager\LoadAdmin.
 *
 * Initializes plugin classes.
 *
 * @since 1.0.0
 * @access private
 * @final Please don't extend this plugin.
 */
final class LoadAdmin extends AdminPages {
	use Construct_Master_Once_Interface, Enclose_Stray_Private;

	/**
	 * Constructor, initializes WordPress actions.
	 *
	 * @since 1.0.0
	 */
	private function construct() {
		add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );
		add_action( 'admin_notices', array( $this, 'do_activation_notice' ) );
	}

	/**
	 * Checks whether the WP installation blocks external requests.
	 * Shows notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
	 *
	 * @since 1.0.0
	 */
	public function check_external_blocking() {

		if ( false === $this->is_tsf_extension_manager_page() || false === $this->can_do_settings() )
			return;

		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && true === WP_HTTP_BLOCK_EXTERNAL ) {

			$parsed_url = wp_parse_url( $this->get_activation_url() );
			$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

			if ( false === defined( 'WP_ACCESSIBLE_HOSTS' ) || false === stristr( WP_ACCESSIBLE_HOSTS, $host ) ) {

				$warning = '<strong>' . esc_html__( 'Warning!', 'the-seo-framework-extension-manager' ) . '</strong>';
				/* translators: %1$s: Warning!. %2$s Plugin API host URL. %3$s, WordPress PHP constant. */
				$notice = sprintf(
					esc_html__( '%1$s Your website is blocking external requests. This means you will not be able to download software. Please add %2$s to %3$s', 'the-seo-framework-extension-manager' ),
					$warning, esc_html( $host ), '<code>WP_ACCESSIBLE_HOSTS</code>'
				);

				//* Already escaped.
				the_seo_framework()->do_dismissible_notice( $notice, 'error', true, false );
			}
		}
	}

	/**
	 * Adds dashboard notice for when the user still needs to choose a license type.
	 * The goal is to eliminate confusion, although slightly annoying.
	 *
	 * @since 1.0.0
	 */
	public function do_activation_notice() {

		if ( $this->is_plugin_activated() || false === $this->can_do_settings() || $this->is_tsf_extension_manager_page() )
			return;

		$text = __( 'Your extensions are only three clicks away', 'the-seo-framework-extension-manager' );
		$url = $this->get_admin_page_url();
		$title = __( 'Activate the SEO Extension Manager', 'the-seo-framework-extension-manager' );

		$notice_link = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '" target="_self">' . esc_html( $title ) . '</a>';
		$notice = esc_html( $text ) . ' &mdash; ' . $notice_link;

		//* No a11y icon. Already escaped.
		the_seo_framework()->do_dismissible_notice( $notice, 'updated', false, false );
	}
}
