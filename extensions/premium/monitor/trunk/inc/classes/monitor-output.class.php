<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Output
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Core_Static_Final as Construct_Core_Static_Final;

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
	 * Returns HTML pane overview based on $data input and $type.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return string The HTML pane overview.
	 */
	public function _get_data( $data, $type ) {
		return $this->get_pane_data( $data, $type );
	}

	/**
	 * Generates information pane data based on $data input and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return string The HTML pane overview.
	 */
	protected function get_pane_data( $data, $type ) {

		$info = '';

		foreach ( $this->generate_pane_info_list( $data, $type ) as $info_entry )
			$info .= $info_entry;

		return sprintf( '<div class="tsfem-flex tsfem-flex-row">%s</div>', $info );
	}

	/**
	 * Generates information pane data based on $data input and $type.
	 * For ajax.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return array : {
	 *     'info' => array The HTML pane overview items,
	 *     'wrap' => string The HTML items wrap,
	 * }
	 */
	public function _ajax_get_pane_data( $data, $type ) {

		$info = array();

		foreach ( $this->generate_pane_info_list( $data, $type ) as $info_entry )
			$info[] = $info_entry;

		$wrap = '<div class="tsfem-flex tsfem-flex-row"></div>';

		return array(
			'info' => $info,
			'wrap' => $wrap,
		);
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
	protected function generate_pane_info_list( $data = array(), $type = '' ) {
		foreach ( $data as $key => $value ) :
			yield $this->make_slab_info_entry( $key, $value, $type );
		endforeach;
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

		$content = $this->parse_content( $key, $value, $type );

		if ( $content ) {
			switch ( $type ) :
				case 'issues' :
					//$this->slab_nav_key_has_content( $key, $type, true );
					$title = $this->get_entry_title( $key, $type );
					$prefix = $this->get_entry_state_icon( $key, $type );

					$title = $prefix . $title;

					return $this->build_collapsable_entry( $title, $content, $key, $this->get_entry_state( $key, $type ) );
					break;

				case 'stats' :
				default :
					return sprintf( '<div class="tsfem-flex tsfem-flex-row">%s</div>', $content );
					break;
			endswitch;
		}

		return '';
	}

	/**
	 * Creates a collapsable entry from title and content.
	 * Also known as an accordion. Without the requirement for JS.
	 *
	 * @since 1.0.0
	 * @staticvar int $count Couples label and checkbox IDs.
	 *
	 * @param string $title The entry title. Must be escaped.
	 * @param string $content The entry content. Must be escaped.
	 * @param string $id The entry ID. Optional.
	 * @param string $icon_state The icon state color. Leave empty for 'unknown' (blue).
	 * @return string The HTML formed collapsable entry.
	 */
	protected function build_collapsable_entry( $title, $content, $id = '', $icon_state = '' ) {

		static $count = 0;
		$count++;

		$id = $id ? sprintf( ' id="tsfem-e-monitor-collapse[%s]"', esc_attr( $id ) ) : '';
		$icon_state = $this->parse_defined_icon_state( $icon_state );

		$checkbox_id = sprintf( 'tsfem-e-monitor-collapse-checkbox-%s', $count );
		$checkbox = sprintf( '<input type="checkbox" id="%s" checked>', $checkbox_id );

		$title = sprintf( '<h3 class="tsfem-e-monitor-collapse-title">%s</h3>', $title );
		$icon = sprintf( '<span class="tsfem-e-monitor-collapse-icon tsfem-flex tsfem-flex-row tsfem-flex-nogrowshrink tsfem-flex-nowrap tsfem-monitor-icon-%s"></span>', $icon_state );

		$header = sprintf( '<label class="tsfem-e-monitor-collapse-header tsfem-flex tsfem-flex-row tsfem-flex-nowrap tsfem-flex-nogrow tsfem-flex-space" for="%s">%s%s</label>', $checkbox_id, $title, $icon );
		$content = sprintf( '<div class="tsfem-e-monitor-collapse-content">%s</div>', $content );

		return sprintf( '<div class="tsfem-e-monitor-collapse tsfem-flex"%s>%s%s%s</div>', $id, $checkbox, $header, $content );
	}

	/**
	 * Returns pane graph overview.
	 *
	 * @since 1.0.0
	 * @access private
	 * @TODO unused?
	 *
	 * @param array $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return string The pane graph overview.
	 */
	public function get_pane_graph_data( $data, $type ) {

		$info = '';

		foreach ( $this->generate_pane_graph_data( $data, $type ) as $info_entry )
			$info .= $info_entry;

		return sprintf( '<div class="tsfem-flex tsfem-flex-row">%s</div>', $info );
	}

	/**
	 * Iterates over graph data to generate information.
	 *
	 * @since 1.0.0
	 * @uses TSF_Extension_Manager_Extension\Monitor_Output->make_slab_graph_entry()
	 * @generator
	 * @TODO unused?
	 *
	 * @param array $data The fetched data.
	 * @param string $type The data type.
	 * @yields Interpreted data from array for the information slab.
	 */
	protected function generate_pane_graph_data( $data, $type ) {

		foreach ( $data as $key => $value ) :
			yield $this->make_slab_graph_entry( $key, $value, $type );
		endforeach;

	}

	/**
	 * @TODO document
	 * @TODO unused?
	 */
	protected function make_slab_graph_entry( $key, $value, $type ) {

		$output = $this->parse_content( $key, $value, $type );

		if ( $output ) {
			//$this->slab_nav_key_has_content( $key, $type, true );

			$title = $this->get_entry_title( $key, $type );
			$prefix = $this->get_entry_state_icon( $key, $type );

			$title = sprintf( '<h3 class="tsfem-flex tsfem-flex-row">%s%s</h3>', $prefix, $title );
			$output = sprintf( '<div class="tsfem-flex">%s</div>', $output );

			return sprintf( '<div id="tsfem-e-monitor-%s-graph-output" class="tsfem-e-monitor-nav-output tsfem-flex">%s%s</div>', esc_attr( $key ), $title, $output );
		}

		return '';
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

	/**
	 * Sets entry $state for $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @param string $state The pane-data entry state.
	 * @return string The entry $state if set; Empty string otherwise.
	 */
	protected function set_entry_state( $key, $type, $state ) {

		if ( $state )
			return $this->get_entry_state( $key, $type, $state );

		return '';
	}

	/**
	 * Returns entry $state for $key and $type.
	 *
	 * @since 1.0.0
	 * @staticvar array $cache Maintains state strings for $key and $type.
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @param string|null $set The pane-data entry state.
	 * @return string The entry $state if set; Null otherwise.
	 */
	protected function get_entry_state( $key, $type, $set = null ) {

		static $cache = array();

		if ( isset( $cache[ $type ][ $key ] ) )
			return $cache[ $type ][ $key ];

		if ( isset( $set ) )
			return $cache[ $type ][ $key ] = $set;

		return '';
	}

	/**
	 * Returns entry state icon for $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @return string The HTML formed entry state icon.
	 */
	protected function get_entry_state_icon( $key, $type ) {
		return $this->parse_state_icon( $this->get_entry_state( $key, $type ) );
	}

	/**
	 * Parses entry state HTMl icon.
	 *
	 * @since 1.0.0
	 *
	 * @param string $state The icon state.
	 * @return string The HTML formed entry state icon.
	 */
	protected function parse_state_icon( $state = '' ) {

		$state = $this->parse_defined_icon_state( $state );

		return sprintf( '<span class="tsfem-e-monitor-title-icon tsfem-monitor-icon-%1$s tsfem-e-monitor-title-icon-%1$s"></span>', esc_attr( $state ) );
	}

	/**
	 * Returns known CSS icon state name for $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param string $type The pane-data type.
	 * @return string The known entry state name.
	 */
	protected function get_defined_icon_state( $key, $type ) {
		return $this->parse_defined_icon_state( $this->get_entry_state( $key, $type ) );
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

					case 'security' :
						$title = __( 'Security', 'the-seo-framework-extension-manager' );
						break 2;

					case 'moresoon' :
						$title = __( 'More coming soon!', 'the-seo-framework-extension-manager' );
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

	/**
	 * Parses issue $key content's $value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param mixed $value The array value attached to $key.
	 * @return string The issue data content.
	 */
	protected function parse_issues_content( $key, $value ) {

		static $tests = null;

		if ( is_null( $tests ) )
			$tests = \TSF_Extension_Manager_Extension\Monitor_Tests::get_instance();

		$content = '';

		if ( isset( $value['requires'] ) && version_compare( TSFEM_E_MONITOR_VERSION, $value['requires'], '>=' ) ) {
			if ( isset( $value['tested'] ) && version_compare( TSFEM_E_MONITOR_VERSION, $value['tested'], '<=' ) ) {
				$output = isset( $value['data'] ) ? $tests->{"issue_$key"}( $value['data'] ) : '';
				if ( '' !== $output ) {
					$content = $output['content'];
					$this->set_entry_state( $key, 'issues', $output['state'] );
				}
			}
		} else {
			$content = $this->get_em_requires_update_notification();
		}

		return $content;
	}

	/**
	 * Parses statistics $key content's $value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The array key.
	 * @param mixed $value The array value attached to $key.
	 * @return string The statistics data content.
	 */
	protected function parse_stats_content( $key, $value ) {

		static $graph = null;

		if ( is_null( $graph ) )
			$graph = \TSF_Extension_Manager_Extension\Monitor_Graph::get_instance();

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
