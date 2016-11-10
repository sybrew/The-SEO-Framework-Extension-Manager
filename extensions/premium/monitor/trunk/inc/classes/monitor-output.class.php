<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Output
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use TSF_Extension_Manager\Construct_Core_Static_Final as Construct_Core_Static_Final;

/**
 * Monitor extension for The SEO Framework
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
 * Class TSF_Extension_Manager_Extension\Monitor_Output
 *
 * Parses and evaluates input data.
 *
 * @since 1.0.0
 * @access private
 */
final class Monitor_Output {
	use Enclose_Core_Final, Construct_Core_Static_Final;

	/**
	 * The object instance.
	 *
	 * @since 1.0.0
	 *
	 * @var object|null This object instance.
	 */
	private static $instance = null;

	/**
	 * The constructor. Does nothing.
	 */
	private function construct() { }

	/**
	 * Sets the class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public static function set_instance() {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return object The current instance.
	 */
	public static function get_instance() {

		if ( is_null( static::$instance ) ) {
			static::set_instance();
		}

		return static::$instance;
	}

	/**
	 * Generates pane graph overview.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return string The pane graph overview.
	 */
	public function generate_pane_graph_data( $data, $type ) {
		return $this->generate_pane_graph_info_data( $data, $type );
	}

	protected function generate_pane_graph_info_data( $data, $type ) {

		$info = '';

		foreach ( $this->render_pane_slab_graph_data( $data, $type ) as $info_entry )
			$info .= $info_entry;

		return sprintf( '<div class="tsfem-flex tsfem-flex-row">%s</div>', $info );
	}

	protected function render_pane_slab_graph_data( $data, $type ) {

		foreach ( $data as $key => $value ) :
			yield $this->make_slab_graph_entry( $key, $value, $type );
		endforeach;

	}

	protected function make_slab_graph_entry( $key, $value, $type ) {

		$output = $this->parse_content( $key, $value, $type );

		if ( $output ) {
			$this->slab_nav_key_has_content( $key, $type, true );

			$title = $this->get_entry_title( $key, $type );
			$prefix = $this->get_entry_state_sign( $key, $type );

			$title = sprintf( '<h3 class="tsfem-flex tsfem-flex-row">%s%s</h3>', $prefix, $title );
			$output = sprintf( '<div class="tsfem-flex">%s</div>', $output );

			return sprintf( '<div id="tsfem-e-monitor-%s-graph-output" class="tsfem-e-monitor-nav-output tsfem-flex">%s%s</div>', esc_attr( $key ), $title, $output );
		}

		return '';
	}

	/**
	 * Generates pane slab overview. With navigation on the side when JS is available.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @param string $navpos Determines the navigation position. Accepts 'left' and 'right'.
	 * @return string The pane slab overview.
	 */
	public function generate_pane_slab_data( $data, $type, $navpos = 'left' ) {

		$info = $this->generate_pane_slab_info_data( $data, $type );
		$nav = $this->generate_pane_slab_nav_data( $data, $type );

		return 'left' === $navpos ? $nav . $info : $info . $nav;
	}

	/**
	 * Generates information pane slab.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return string The information pane slab.
	 */
	protected function generate_pane_slab_info_data( $data, $type ) {

		$info = '';

		foreach ( $this->render_pane_slab_info_data( $data, $type ) as $info_entry )
			$info .= $info_entry;

		return sprintf( '<div class="tsfem-flex tsfem-flex-row">%s</div>', $info );
	}

	/**
	 * Generates navigation pane slab.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return string The navigation pane slab.
	 */
	protected function generate_pane_slab_nav_data( $data, $type ) {

		$nav = '';

		foreach ( $this->render_pane_slab_nav_data( $data, $type ) as $nav_entry )
			$nav .= $nav_entry;

		return sprintf( '<div class="tsfem-flex">%s</div>', $nav );
	}

	/**
	 * Iterates over pane slab data to generate information.
	 *
	 * @since 1.0.0
	 * @uses TSF_Extension_Manager_Extension\Monitor_Output->make_slab_info_entry()
	 * @generator
	 *
	 * @param array $data The fetched data.
	 * @param string $type The pane-date type.
	 * @yields Interpreted data from array for the information slab.
	 */
	protected function render_pane_slab_info_data( $data = array(), $type = '' ) {

		foreach ( $data as $key => $value ) :
			yield $this->make_slab_info_entry( $key, $value, $type );
		endforeach;

	}

	/**
	 * Iterates over pane slab data to generate navigation.
	 *
	 * @since 1.0.0
	 * @uses TSF_Extension_Manager_Extension\Monitor_Output->make_slab_nav_entry()
	 * @generator
	 *
	 * @param array $data The fetched data.
	 * @param string $type The pane-date type.
	 * @yields Interpreted data from array for the navigation slab (js only).
	 */
	protected function render_pane_slab_nav_data( $data = array(), $type = '' ) {

		foreach ( $data as $key => $value ) :
			yield $this->make_slab_nav_entry( $key, $type );
		endforeach;

	}

	/**
	 * Activates and determines if slab nav key is active.
	 *
	 * To be used for navigation, where is determined if the key has content.
	 *
	 * @since 1.0.0
	 * @staticvar array $cache Maintains cache for data.
	 *
	 * @param string $key The entry key.
	 * @param string $type The pane-data type.
	 * @param bool $set Whether to activate the key or determine its existence.
	 * @return bool True when set, false otherwise.
	 */
	protected function slab_nav_key_has_content( $key, $type, $set = false ) {

		static $cache = array();

		return $set ? $cache[ $type ][ $key ] = true : isset( $cache[ $type ][ $key ] );
	}

	/**
	 * Interprets data input and finds an appropriate content function for it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param mixed $value The array value attached to $key.
	 * @param string $type The pane-data type.
	 * @return string The HTML formed data if content could be generated. Otherwise empty string.
	 */
	protected function make_slab_info_entry( $key, $value, $type ) {

		$output = $this->parse_content( $key, $value, $type );

		if ( $output ) {
			$this->slab_nav_key_has_content( $key, $type, true );

			$title = $this->get_entry_title( $key, $type );
			$prefix = $this->get_entry_state_sign( $key, $type );

			$title = sprintf( '<h3 class="tsfem-flex tsfem-flex-row">%s%s</h3>', $prefix, $title );
			$output = sprintf( '<div class="tsfem-flex">%s</div>', $output );

			return sprintf( '<div id="tsfem-e-monitor-%s-nav-output" class="tsfem-e-monitor-nav-output tsfem-flex">%s%s</div>', esc_attr( $key ), $title, $output );
		}

		return '';
	}

	/**
	 * Makes slab entry title from input $key and $type for when no JS is present.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @return string The HTML formed data.
	 */
	protected function make_slab_nav_entry( $key, $type ) {

		if ( $this->slab_nav_key_has_content( $key, $type ) )
			return $this->get_slab_nav_entry( $key, $type );

		return '';
	}

	/**
	 * Returns slab nav entry title from input $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @return string The HTML formed data.
	 */
	protected function get_slab_nav_entry( $key, $type ) {

		$title = $this->get_entry_title( $key, $type );
		$prefix = $this->get_entry_state_sign( $key, $type );

		return sprintf( '<h3 id="tsfem-e-monitor-%s-nav-entry" class="tsfem-e-monitor-nav-entry">%s%s</h3>', esc_attr( $key ), $prefix, $title );
	}

	/**
	 * Returns slab entry title based on $key and $type.
	 *
	 * @since 1.0.0
	 * @staticvar array $cache Maintains the titles cache.
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @return string The escaped $type $key title.
	 */
	protected function get_entry_title( $key, $type ) {

		static $cache = array();

		if ( isset( $cache[ $type ][ $key ] ) )
			return $cache[ $type ][ $key ];

		$title = $this->parse_title( $key, $type );

		return $cache[ $type ][ $key ] = esc_html( $title );
	}

	public function set_entry_state_sign( $key, $type, $state ) {

		if ( $state )
			return $this->get_entry_state_sign( $key, $type, $state );

		return '';
	}

	protected function get_entry_state_sign( $key, $type, $set = null ) {

		static $cache = array();

		if ( isset( $cache[ $type ][ $key ] ) )
			return $this->parse_state_sign( $cache[ $type ][ $key ] );

		if ( isset( $set ) )
			return $cache[ $type ][ $key ] = $set;

		return $this->parse_state_sign();
	}

	protected function parse_state_sign( $type = '' ) {

		switch ( $type ) :
			case 'good' :
			case 'okay' :
			case 'warning' :
			case 'bad' :
			case 'error' :
				$icon = $type;
				break;

			default :
				$icon = 'unknown';
				break;
		endswitch;

		return sprintf( '<span class="tsfem-title-icon tsfem-title-icon-%s">_X_</span>', $icon );
	}

	/**
	 * Returns slab entry title based on $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @return string The $type $key title.
	 */
	protected function parse_title( $key, $type ) {

		switch ( $type ) :
			case 'issues' :
				switch ( $key ) :
					case 'title' :
						$title = __( 'Titles', 'the-seo-framework-extension-manager' );
						break 2;

					case 'description' :
						$title = __( 'Descriptions', 'the-seo-framework-extension-manager' );
						break 2;

					case 'canonical' :
						$title = __( 'Canonical URLs', 'the-seo-framework-extension-manager' );
						break 2;

					case 'favicon' :
						$title = __( 'Favicon output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'duplicated' :
						$title = __( 'Duplicated content', 'the-seo-framework-extension-manager' );
						break 2;

					case 'mobile' :
						$title = __( 'Mobile friendliness', 'the-seo-framework-extension-manager' );
						break 2;

					case 'html' :
						$title = __( 'HTML output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'php' :
						$title = __( 'PHP errors', 'the-seo-framework-extension-manager' );
						break 2;

					case 'img' :
						$title = __( 'Image sizes', 'the-seo-framework-extension-manager' );
						break 2;

					case 'robots' :
						$title = __( 'Robots.txt output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'sitemap' :
						$title = __( 'Sitemap output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'external' :
						$title = __( 'External links', 'the-seo-framework-extension-manager' );
						break 2;

					default :
						break 1;
				endswitch;

			default :
				$title = ucwords( str_replace( array( '-', '_' ), ' ', $key ) );
				break 1;
		endswitch;

		return $title;
	}

	/**
	 * Returns slab data content.
	 *
	 * @since 1.0.0
	 * @staticvar object $tests The Monitor_Tests class isntance.
	 *
	 * @param string $key The array key.
	 * @param mixed $value The array value attached to $key.
	 * @param string $type The pane-data type.
	 * @return string The HTML formed data, or empty string if data is incompatible.
	 */
	protected function parse_content( $key, $value, $type ) {

		$content = '';

		switch ( $type ) :
			case 'issues' :
				$content = $this->parse_issues_content( $key, $value );
				break;

			case 'stats' :
				$content = $this->parse_stats_content( $key, $value );
				break;

			default :
				break;
		endswitch;

		return $content;
	}

	protected function parse_issues_content( $key, $value ) {

		static $tests = null;

		if ( is_null( $tests ) )
			$tests = Monitor_Tests::get_instance();

		$content = '';

		if ( isset( $value['requires'] ) && version_compare( TSFEM_E_MONITOR_VERSION, $value['requires'], '>=' ) ) {
			if ( isset( $value['tested'] ) && version_compare( TSFEM_E_MONITOR_VERSION, $value['tested'], '<=' ) ) {
				$output = isset( $value['data'] ) ? $tests->{"issue_$key"}( $value['data'] ) : '';
				if ( '' !== $output ) {
					$content = $output['content'];
					$this->set_entry_state_sign( $key, $type, $output['state'] );
				}
			}
		} else {
			$content = $this->get_em_requires_update_notification();
		}

		return $content;
	}

	protected function parse_stats_content( $key, $value ) {

		static $graph = null;

		if ( is_null( $graph ) )
			$graph = Monitor_Graph::get_instance();

		$content = '';

		if ( isset( $value['requires'] ) && version_compare( TSFEM_E_MONITOR_VERSION, $value['requires'], '>=' ) ) {
			if ( isset( $value['tested'] ) && version_compare( TSFEM_E_MONITOR_VERSION, $value['tested'], '<=' ) ) {
				$output = isset( $value['data'] ) ? $graph->{"stats_$key"}( $value['data'] ) : '';
				if ( '' !== $output ) {
					$content = $output['content'];
				}
			}
		} else {
			$content = $this->get_em_requires_update_notification();
		}

		return $content;
	}

	/**
	 * Returns update notification string for information parsing.
	 *
	 * @since 1.0.0
	 * @staticvar string $cache
	 *
	 * @return string Notifying user the Extension Manager requires an update.
	 */
	protected function get_em_requires_update_notification() {

		static $cache = null;

	 	return isset( $cache ) ? $cache : $cache = esc_html__( 'The Extension Manager needs to be updated in order to interpret this data.', 'the-seo-framework-extension-manager' );
	}
}
