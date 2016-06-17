<?php
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
 * Facade Load class.
 *
 * Extending upon parent classes.
 *
 * @since 1.0.0
 * @final Please don't extend this extension.
 */
final class TSF_Extension_Manager_Load extends TSF_Extension_Manager_Extensions {

	/**
	 * Constructor, loads parent constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_notices', array( $this, 'do_activation_notice' ) );
	}

	/**
	 * Adds dashboard notice for when the user still needs to choose a license.
	 * The goal is to eliminate confusion, although annoying.
	 *
	 * @since 1.0.0
	 */
	public function do_activation_notice() {

		if ( $this->is_plugin_connected() || ! $this->can_do_settings() || $this->is_seo_extension_manager_page() )
			return;

		$text = esc_html__( 'Your extensions are only three clicks away', 'the-seo-framework-extension-manager' );
		$url = $this->get_admin_page_url();
		$title = esc_html__( 'Activate the SEO Extension Manager', 'the-seo-framework-extension-manager' );

		echo the_seo_framework()->generate_dismissible_notice( sprintf( '%s &mdash; <a href="%s" title="%s" target="_self" class="">%s</a>', $text, $url, $title, $title ) );

	}

	/**
	 * Determines whether we're on the SEO extension manager settings page.
	 *
	 * @since 1.0.0
	 * @staticvar bool $cache
	 *
	 * @return bool
	 */
	public function is_seo_extension_manager_page() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		if ( the_seo_framework()->can_cache_query() )
			return $cache = the_seo_framework()->is_menu_page( $this->plugin_page_id );

		return false;
	}

}
