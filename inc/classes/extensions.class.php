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
 * Class TSF_Extension_Manager_Extensions
 *
 * Holds plugin extension handlers.
 *
 * @since 1.0.0
 */
class TSF_Extension_Manager_Extensions extends TSF_Extension_Manager_AdminPages {

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Constructor. Loads parent constructor and initializes actions.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Echos the extension overview.
	 *
	 * @since 1.0.0
	 */
	protected function output_extensions_overview( $network = false ) {

		$this->get_extensions();

		?>
		<div class="extension-wrapper">
			<?php
			$this->get_view( 'extension', array(
					'args' => array(
						'extensions' => $this->get_extensions(),
						'auth' => $this->get_subscription_auth(),
						'network' => $this->is_plugin_in_network_mode()
					)
				)
			);
			?>
		</div>
		<?php

	}

	/**
	 * Fetches and caches the available extensions.
	 *
	 * @since 1.0.0
	 */
	protected function get_extensions() {

		// @TODO handle 12h transient. ? or 12 min?
		// @TODO handle two types of remote_get timeout (one with 5 sec, and backup server with 10 sec)

		//* Most likely syntax, converted from json.
		return array(
			'test-plugin-free' => array(
				'slug' => 'test-plugin-free',
				'name' => 'Free Plugin',
				'network' => '0',
				'type' => 'free',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/0.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/1.jpg',
				'auth' => '0',
				'dl-type' => 'worg',
				'dl-url' => 'https://downloads.wordpress.org/plugin/the-seo-framework-title-fix.zip',
				'short_description' => 'This is a free testing plugin.',
				'version' => '1.0.0',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-3 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '80',
				'num_ratings' => '30',
				'requires' => '4.5.2',
				'active_installs' => '0',
			),
			'test-plugin-free2' => array(
				'slug' => 'test-plugin-free2',
				'name' => 'Free Plugin 2',
				'network' => '0',
				'type' => 'free',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/2.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/3.jpg',
				'auth' => '0',
				'dl-type' => 'worg',
				'dl-url' => 'https://downloads.wordpress.org/plugin/the-seo-framework-title-fix.zip',
				'short_description' => 'This is the seconds free testing plugin.',
				'version' => '1.0.0beta',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-5 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '100',
				'num_ratings' => '40',
				'requires' => '4.5.6',
				'active_installs' => '50',
			),
			'test-plugin-premium' => array(
				'slug' => 'test-plugin-premium',
				'name' => 'Premium Plugin',
				'network' => '0',
				'type' => 'premium',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/0.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/1.jpg',
				'auth' => '1',
				'dl-type' => 's3',
				'dl-url' => 'https://theseoframework.com/share/promimetypes.zip',
				'short_description' => 'This is a premium testing plugin.',
				'version' => '1-20160504',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-8 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '90',
				'num_ratings' => '50',
				'requires' => '4.5.0',
				'active_installs' => '500',
			),
			'test-plugin-premium2' => array(
				'slug' => 'test-plugin-premium2',
				'name' => 'Premium Plugin 2',
				'network' => '0',
				'type' => 'premium',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/2.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/3.jpg',
				'auth' => '1',
				'dl-type' => 's3',
				'dl-url' => 'https://theseoframework.com/share/pro-sites-extras.zip',
				'short_description' => 'This is the second premium testing plugin.',
				'version' => '2.4.4',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "-50 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '60',
				'num_ratings' => '20',
				'requires' => '4.4.8',
				'active_installs' => '5000',
			),
			'test-network' => array(
				'slug' => 'test-network',
				'name' => 'Network Plugin',
				'network' => '1',
				'type' => 'free',
				'image' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/0.jpg',
				'image2x' => 'https://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2016/04/1.jpg',
				'auth' => '0',
				'dl-type' => 's3',
				'dl-url' => 'https://theseoframework.com/share/custom-css.zip',
				'short_description' => 'This is a free newtwork testing plugin.',
				'version' => '1.0.5',
				'author' => 'Sybre Waaijer',
				'last_updated' => date( 'c', strtotime( "+50 days" ) ),
				'icons' => array(
					'svg' => '',
					'2x' => '',
					'1x' => '',
					'default' => 'http://theseoframework.hostmijnpagina.nl/wp-content/uploads/sites/16/2015/09/icon-256x256.jpg',
				),
				'rating' => '40',
				'num_ratings' => '80',
				'requires' => '4.3.8',
				'active_installs' => '4200',
			),
		);

	}

	protected function plugin_api( $args = array() ) {

		static $api = null;

		if ( isset( $api ) )
			return $api( $args );

		require_once( TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'api/api.class.php' );

		return $api = new TSF_Extension_Manager_API_Connect( $args );
	}

	protected function set_plugin_api_instance( $file, $software_title, $software_version, $api_url, $text_domain = '', $extra = '' ) {

		include( TSF_EXTENSION_MANAGER_DIR_PATH_CLASS . 'api/api.class.php' );

		TSF_Extension_Manager_API_Connect::instance( $file, $software_title, $software_version, $api_url, $text_domain, $extra );
	}

}
