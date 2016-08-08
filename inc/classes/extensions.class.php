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
 * Holds i18n data functions for class TSF_Extension_Manager\Extesnsions.
 *
 * @since 1.0.0
 * @access private
 */
trait Extensions_i18n {

	/**
	 * Initializes class i18n.
	 *
	 * @since 1.0.0
	 * @staticvar array $i18n
	 *
	 * @return array $i18n The internationalization data.
	 */
	private static function obtain_i18n() {

		static $i18n = null;

		if ( isset( $i18n ) )
			return $i18n;

		return $i18n = array(
			'free'       => __( 'Free', 'the-seo-framework-extension-manager' ),
			'premium'    => __( 'Premium', 'the-seo-framework-extension-manager' ),
			'download'   => __( 'Download', 'the-seo-framework-extension-manager' ),
			'update'     => __( 'Update', 'the-seo-framework-extension-manager' ),
			'activate'   => __( 'Activate', 'the-seo-framework-extension-manager' ),
			'deactivate' => __( 'Deactivate', 'the-seo-framework-extension-manager' ),
			'delete'     => __( 'Delete', 'the-seo-framework-extension-manager' ),
		);
	}

	/**
	 * Returns i18n value from key.
	 *
	 * @since 1.0.0
	 *
	 * @return string The i18n data.
	 */
	private static function get_i18n( $key = '' ) {

		$i18n = static::obtain_i18n();

		return isset( $i18n[ $key ] ) ? $i18n[ $key ] : '';
	}
}

/**
 * Holds plugin data check functions for class TSF_Extension_Manager\Extesnsions.
 *
 * @since 1.0.0
 * @access private
 */
trait Extensions_Properties {

	/**
	 * Determines whether the input plugin is premium.
	 *
	 * @since 1.0.0
	 * @TODO
	 *
	 * @param array $plugin The plugin to check.
	 * @return bool Whether the plugin is premium.
	 */
	private static function is_plugin_premium( $plugin ) {
		return 'premium' === $plugin['type'];
	}

	/**
	 * Determines whether the input plugin is downloaded and available.
	 *
	 * @since 1.0.0
	 * @TODO
	 *
	 * @param array $plugin The plugin to check.
	 * @return bool Whether the plugin is downloaded and available.
	 */
	private static function is_plugin_downloaded( $plugin ) {
		return false;
	}

	/**
	 * Determines whether the input plugin has been modified from its source.
	 * It performs a simple ZIP package MD5 sum comparison check.
	 *
	 * @since 1.0.0
	 * @TODO
	 *
	 * @param array $plugin The plugin to check.
	 * @return bool Whether the plugin is modified.
	 */
	private static function is_plugin_modified( $plugin ) {
		return false;
	}

	/**
	 * Determines whether the input plugin is downloaded and requires an update.
	 *
	 * @since 1.0.0
	 * @TODO
	 *
	 * @param array $plugin The plugin to check.
	 * @return bool Whether the plugin requires an update.
	 */
	private static function is_plugin_out_of_date( $plugin ) {
		return false;
	}

	/**
	 * Determines whether the input plugin is premium.
	 *
	 * @since 1.0.0
	 * @TODO
	 *
	 * @param array $plugin The plugin to check.
	 * @return bool Whether the plugin is premium.
	 */
	private static function is_plugin_active( $plugin ) {
		return false;
	}
}

/**
 * Class TSF_Extension_Manager\Extensions.
 *
 * Handles extensions pane and activation.
 *
 * @since 1.0.0
 * @access private
 * 		You'll need to invoke the TSF_Extension_Manager\Core verification handler. Which is impossible.
 * @final Please don't extend this.
 */
final class Extensions extends Secure {
	use Extensions_i18n, Extensions_Properties;

	/**
	 * Holds the class header contents.
	 *
	 * @since 1.0.0
	 *
	 * @var array $header
	 */
	private static $header = array();

	/**
	 * Holds the class plugin list contents.
	 *
	 * @since 1.0.0
	 *
	 * @var array $plugins
	 */
	private static $plugins = array();

	/**
	 * Fetches all plugins remotely.
	 *
	 * @since 1.0.0
	 */
	private static function get_plugins() {
		//* EXAMPLE. @TODO FETCH LIST EXTERNALLY.
		//* @TODO SET LINKS BEHIND FIREWALL LINK THROUGH WC API [account validation] (even if free?).
		//* @TODO use transient cache (expire 1 hour + refresh cache button (with transient 5 minutes)?)
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

	/**
	 * Sets up class variables.
	 *
	 * @since 1.0.0
	 */
	private function set_up_variables() {

		switch ( self::get_property( '_type' ) ) :
			case 'overview' :

				static::$header = array();
				static::$plugins = static::get_plugins();

				break;

		endswitch;

	}

	/**
	 * Initializes class variables. Always use reset when done with this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Required. The instance type.
	 * @param string $instance Required. The instance key.
	 * @param int $bit Required. The instance bit.
	 */
	public static function initialize( $type = '', $instance = '', $bits = null ) {

		self::reset();

		if ( empty( $type ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an initialization type.' );
		} else {

			self::set( '_wpaction' );

			switch ( $type ) :
				case 'overview' :
					tsf_extension_manager()->verify_instance( $instance, $bits[1] ) or die;
					self::set( '_type', 'overview' );
					static::set_up_variables();
					break;

				case 'reset' :
					self::reset();
					break;

				default :
					self::reset();
					the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct initialization type.' );
					break;
			endswitch;
		}
	}

	/**
	 * Returns the trend call.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Determines what to get.
	 * @return string
	 */
	public static function get( $type = '' ) {

		self::verify_instance() or die;

		if ( empty( $type ) ) {
			the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify an get type.' );
			return false;
		}

		switch ( $type ) :
			case 'header' :
				return static::get_header();
				break;

			case 'content' :
				return static::get_content();
				break;

			default :
				the_seo_framework()->_doing_it_wrong( __METHOD__, 'You must specify a correct get type.' );
				break;
		endswitch;

		return false;
	}

	/**
	 * Outputs extensions overview header.
	 *
	 * @since 1.0.0
	 * @todo all of it
	 * @todo add refresh AJAX button with transient 5 min.
	 * @todo add filter tabs based on plugin tags
	 *
	 * @return string The extensions overview header.
	 */
	private static function get_header() {

		$output = '';

		if ( 'overview' === self::get_property( '_type' ) ) {
			foreach ( static::$header as $id => $item ) {
				$output .= '';
			}
		}

		return $output;
	}

	/**
	 * Outputs extensions overview content.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions overview content.
	 */
	private static function get_content() {

		$output = '';

		if ( 'overview' === self::get_property( '_type' ) ) {
			$output = static::get_plugins_list();
		}

		return $output;
	}

	/**
	 * Generates a list of the available plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugins list in HTML.
	 */
	private static function get_plugins_list() {

		$plugins = static::filter_plugins( static::$plugins, 'maybe_network' );

		$output = '';

		foreach ( $plugins as $id => $plugin ) {

			if ( ! ( isset( $plugin['slug'] ) && isset( $plugin['type'] ) && isset( $plugin['name'] ) ) )
				continue;

			$wrap = '<div class="tsfem-plugin-icon-wrap">' . static::make_plugin_list_icon( $plugin ) . '</div>';
			$wrap .= '<div class="tsfem-plugin-about-wrap">' . static::make_plugin_list_about( $plugin ) . '</div>';
			$wrap .= '<div class="tsfem-plugin-description-wrap">' . static::make_plugin_list_description( $plugin ) . '</div>';

			$output .= sprintf( '<div class="tsfem-plugin-entry" id="%s">%s</div>', esc_attr( $id ), $wrap );
		}

		return $output;
	}

	/**
	 * Builds plugin image icon tag. Images must be square.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The plugin to make icon from.
	 * @param array $size The icon height and width.
	 * @return string HTML image.
	 */
	private static function make_plugin_list_icon( $plugin, $size = '100' ) {

		//* @TODO set default image.
		$fallback = array( 'svg' => '', '2x' => '', '1x' => '', 'default' => '' );
		$items = null;

		if ( ! empty( $plugin['icons'] ) ) {
			$default = isset( $plugin['icons']['default'] ) ? $plugin['icons']['default'] : '';

			if ( $default ) {
				$svg = isset( $plugin['icons']['svg'] ) ? $plugin['icons']['svg'] : '';
				$one = isset( $plugin['icons']['1x'] ) ? $plugin['icons']['1x'] : '';
				$two = isset( $plugin['icons']['2x'] ) ? $plugin['icons']['2x'] : '';

				$items = array(
					'svg' => $svg,
					'2x' => $two,
					'1x' => $one,
					'default' => $default,
				);
			}
		}

		$items = isset( $items ) ? $items : $fallback;
		$size = esc_attr( $size );

		if ( $items['svg'] ) {
			$image = '<img src="' . esc_url( $items['svg'] ) . '" alt="plugin-icon" height="' . $size . '" width="' . $size . '">';
		} else {
			if ( $items['2x'] ) {
				$image = '<img src="' . esc_url( $items['default'] ) . '" srcset="' . esc_url( $items['1x'] ) . ' 1x, ' . esc_url( $items['2x'] ) . ' 2x" alt="plugin-icon" height="' . $size . '" width="' . $size . '">';
			} else {
				$image = '<img src="' . esc_url( $items['default'] ) . '" alt="plugin-icon" height="' . $size . '" width="' . $size . '">';
			}
		}

		return $image;
	}

	/**
	 * Builds plugin about section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The plugin to make section from.
	 * @return string HTML plugin section with actions and nonce.
	 */
	private static function make_plugin_list_about( $plugin ) {

		$type = 'free' === $plugin['type'] ? static::get_i18n( 'free' ) : static::get_i18n( 'premium' );

		$title = '<h4 class="tsfem-extension-title">' . esc_html( $plugin['name'] ) . '</h4>';
		$type = '<h5 class="tsfem-extension-type">' . esc_html( $type ) . '</h5>';
		$header = '<div class="tsfem-extension-header">' . $title . $type . '</div>';

		$button = static::make_plugin_button( $plugin );

		$buttons = '<div class="tsfem-extension-actions-wrap">' . $button . '</div>';

		return $header . $buttons;
	}

	/**
	 * Builds plugin activation/update/deactivate buttons based on plugin and
	 * account type. Also initializes nonces for those buttons.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The plugin to make button from.
	 * @return string HTML plugin button with nonce.
	 */
	private static function make_plugin_button( $plugin ) {

		$buttons = array();

		if ( static::is_plugin_downloaded( $plugin ) ) {
			if ( static::is_plugin_out_of_date( $plugin ) ) {
				$disabled = self::is_premium_account() || ! static::is_plugin_premium( $plugin ) ? false : true;
				$buttons[] = array(
					'type' => 'update',
					'disabled' => $disabled,
				);
			}

			if ( static::is_plugin_active( $plugin ) ) {
				$buttons[] = array(
					'type' => 'deactivate',
					'disabled' => false,
				);
			} else {
				$buttons[] = array(
					'type' => 'activate',
					'disabled' => false,
				);

				$buttons[] = array(
					'type' => 'delete',
					'disabled' => false,
				);
			}
		} else {
			$disabled = self::is_premium_account() || ! static::is_plugin_premium( $plugin ) ? false : true;
			$buttons[] = array(
				'type' => 'download',
				'disabled' => $disabled,
			);
		}

		$output = '';

		foreach ( $buttons as $button ) {
			$output = static::get_plugin_button_form( $plugin['slug'], $button['type'], $button['disabled'] );
		}

		return $output;
	}

	/**
	 * Builds extension button form and builds nonce. Supports both JS and no-JS.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The plugin/extension slug.
	 * @param string $type The button type.
	 * @param bool $disabled Whether the button is disabled and holds no action.
	 * @return string The button form.
	 */
	private static function get_plugin_button_form( $slug = '', $type = '', $disabled = false ) {

		$key = '';

		//* This pattern can't be unseen. Let's just keep it this way until further notice.
		switch ( $type ) :
			case 'download' :
				$nonce_key = 'download-ext';
				$text = static::get_i18n( 'download' );
				$class = 'tsfem-button-extension-download';
				break;
			case 'update' :
				$nonce_key = 'update-ext';
				$text = static::get_i18n( 'update' );
				$class = 'tsfem-button-extension-update';
				break;
			case 'activate' :
				$nonce_key = 'activate-ext';
				$text = static::get_i18n( 'activate' );
				$class = 'tsfem-button-extension-activate';
				break;
			case 'deactivate' :
				$nonce_key = 'deactivate-ext';
				$text = static::get_i18n( 'deactivate' );
				$class = 'tsfem-button-extension-deactivate';
				break;
			case 'delete' :
				$nonce_key = 'delete-ext';
				$text = static::get_i18n( 'delete' );
				$class = 'tsfem-button-extension-delete';
				break;
			default :
				return '';
				break;
		endswitch;

		$nonce_action = tsf_extension_manager()->get_nonce_action_field( self::$request_name[ $nonce_key ] );
		$nonce = wp_nonce_field( self::$nonce_action[ $nonce_key ], self::$nonce_name, true, false );
		$submit = sprintf( '<input type="submit" name="submit" id="submit" class="tsfem-button-primary %s" value="%s">', esc_attr( $class ), esc_attr( $text ) );
		$form = $nonce_action . $nonce . $submit;

		$nojs = sprintf( '<form action="%s" method="post" id="tsfem-activate-form[%s]" class="hide-if-js">%s</form>', esc_url( tsf_extension_manager()->get_admin_page_url() ), esc_attr( $slug ), $form );
		$js = sprintf( '<a id="tsfem-activate[%s]" class="tsfem-button-primary hide-if-no-js %s">%s</a>', esc_attr( $slug ), esc_attr( $class ), esc_html( $text ) );

		return sprintf( '<div class="tsfem-extension-actions">%s</div>', $nojs . $js );
	}

	private static function make_plugin_list_description( $plugin ) {

	}

	/**
	 * Removes items from plugin list based on $what and website conditions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugins The plugin list.
	 * @param string|array $what What to filter out of the list.
	 * @return array The leftover plugins.
	 */
	private static function filter_plugins( array $plugins = array(), $what = 'maybe_network' ) {

		if ( is_array( $what ) ) {
			foreach ( $what as $filter )
				$plugins = static::filter_plugins( $plugins, $filter );

			return $plugins;
		}

		if ( 'maybe_network' === $what ) {
			$network_mode = tsf_extension_manager()->is_plugin_in_network_mode();

			if ( $network_mode )
				return $plugins;

			$filters = array( 'network' => '0' );
		} elseif ( 'network' === $what ) {
			$filters = array( 'network' => '1' );
		} elseif ( 'single' === $what ) {
			$filters = array( 'network' => '0' );
		}

		return wp_list_filter( $plugins, $filters, 'AND' );
	}
}
