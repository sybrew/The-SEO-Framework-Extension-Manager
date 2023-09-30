<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Output
 */

namespace TSF_Extension_Manager\Extension\Monitor;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Monitor\Output
 *
 * Parses and evaluates input data.
 * TODO make this entire class consist of static functions?
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 */
final class Output {
	use \TSF_Extension_Manager\Construct_Core_Static_Final_Instance;

	/**
	 * Returns HTML pane overview based on $data input and $type.
	 *
	 * @since 1.2.8
	 * @access private
	 *
	 * @param array  $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return string The HTML pane overview. Empty string if unresolved.
	 */
	public function _build_pane_html( $data, $type ) {

		$info = '';

		foreach ( $this->generate_pane_info_list( $data, $type ) as $info_entry )
			$info .= $info_entry;

		return $info ? sprintf( '<div class="tsfem-flex tsfem-flex-row">%s</div>', $info ) : '';
	}

	/**
	 * Generates information pane data based on $data input and $type.
	 * For ajax.
	 *
	 * @since 1.2.8 Now returns an empty 'info' array when the pane info list yields nothing.
	 * @access private
	 *
	 * @param array  $data The pane data to parse.
	 * @param string $type The pane data type.
	 * @return array : {
	 *     'info' => array The HTML pane overview items (if any),
	 *     'wrap' => string The HTML items wrap,
	 * }
	 */
	public function _ajax_build_pane_html( $data, $type ) {

		$info = [];

		foreach ( $this->generate_pane_info_list( $data, $type ) as $info_entry ) {
			if ( $info_entry )
				$info[] = $info_entry;
		}

		return [
			'info' => $info,
			'wrap' => '<div class="tsfem-flex tsfem-flex-row"></div>',
		];
	}

	/**
	 * Iterates over pane slab data to generate information.
	 *
	 * @since 1.0.0
	 * @uses TSF_Extension_Manager\Extension\Monitor\Output->make_slab_info_entry()
	 * @generator
	 *
	 * @param array  $data The fetched data.
	 * @param string $type The pane-date type.
	 * @yields Interpreted data from array for the information slab.
	 */
	protected function generate_pane_info_list( $data = [], $type = '' ) {
		foreach ( $data as $key => $value )
			yield $this->make_slab_info_entry( $key, $value, $type );
	}

	/**
	 * Interprets data input and finds an appropriate content function for it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The array key.
	 * @param mixed  $value The array value attached to $key.
	 * @param string $type  The pane-data type.
	 * @return string The HTML formed data if content could be generated. Otherwise empty string.
	 */
	protected function make_slab_info_entry( $key, $value, $type ) {

		$content = $this->parse_content( $key, $value, $type );

		if ( $content ) {
			switch ( $type ) :
				case 'issues':
					$name   = $this->get_entry_title( $key, $type );
					$prefix = $this->get_entry_state_icon( $key, $type );

					$title = $prefix . $name;

					return $this->build_collapsable_entry( $title, $content, $key, $this->get_entry_state( $key, $type ) );

				default:
					return sprintf( '<div class="tsfem-flex tsfem-flex-row">%s</div>', $content );
			endswitch;
		}

		return '';
	}

	/**
	 * Creates a collapsable entry from title and content.
	 * Also known as an accordion. Without the requirement for JS.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title      The entry title. Must be escaped.
	 * @param string $content    The entry content. Must be escaped.
	 * @param string $id         The entry ID. Optional.
	 * @param string $icon_state The icon state color. Leave empty for 'unknown' (blue).
	 * @return string The HTML formed collapsable entry.
	 */
	protected function build_collapsable_entry( $title, $content, $id = '', $icon_state = '' ) {

		static $count = 0;
		$count++;

		$checkbox_id = sprintf( 'tsfem-e-monitor-collapse-checkbox-%s', $count );

		return vsprintf(
			'<div class=tsfem-e-monitor-collapse%s>%s%s%s</div>',
			[
				$id ? sprintf( ' id="tsfem-e-monitor-collapse[%s]"', \esc_attr( $id ) ) : '',
				sprintf( '<input type=checkbox id="%s" checked>', $checkbox_id ),
				vsprintf(
					'<label class="tsfem-e-monitor-collapse-header tsfem-flex tsfem-flex-row tsfem-flex-nowrap tsfem-flex-nogrow tsfem-flex-space" for="%s">%s%s</label>',
					[
						$checkbox_id,
						sprintf( '<h3 class=tsfem-e-monitor-collapse-title>%s</h3>', $title ),
						sprintf(
							'<span class="tsfem-e-monitor-collapse-icon tsfem-e-monitor-icon-%s"></span>',
							$this->parse_defined_icon_state( $icon_state )
						),
					]
				),
				sprintf( '<div class=tsfem-e-monitor-collapse-content>%s</div>', $content ),
			]
		);
	}

	/**
	 * Returns slab entry title based on $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key  The array key.
	 * @param string $type The pane-data type.
	 * @return string The escaped $type $key title.
	 */
	protected function get_entry_title( $key, $type ) {

		static $cache = [];

		if ( isset( $cache[ $type ][ $key ] ) )
			return $cache[ $type ][ $key ];

		return $cache[ $type ][ $key ] = \esc_html( $this->parse_title( $key, $type ) );
	}

	/**
	 * Sets entry $state for $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The array key.
	 * @param string $type  The pane-data type.
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
	 *
	 * @param string      $key  The array key.
	 * @param string      $type The pane-data type.
	 * @param string|null $set  The pane-data entry state.
	 * @return string The entry $state if set; Null otherwise.
	 */
	protected function get_entry_state( $key, $type, $set = null ) {

		static $cache = [];

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
	 * @param string $key  The array key.
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

		return sprintf(
			'<span class="tsfem-e-monitor-title-icon tsfem-e-monitor-icon-%1$s tsfem-e-monitor-title-icon-%1$s"></span>',
			\esc_attr( $state )
		);
	}

	/**
	 * Returns known CSS icon state name for $key and $type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key  The array key.
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
			case 'good':
			case 'okay':
			case 'warning':
			case 'bad':
			case 'error':
				break;

			default:
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
	 * @param string $key  The array key.
	 * @param string $type The pane-data type.
	 * @return string The $type $key title.
	 */
	protected function parse_title( $key, $type ) {

		switch ( $type ) :
			case 'issues':
				switch ( $key ) :
					case 'title':
						$title = \__( 'Titles', 'the-seo-framework-extension-manager' );
						break 2;

					case 'description':
						$title = \__( 'Descriptions', 'the-seo-framework-extension-manager' );
						break 2;

					case 'https':
						$title = \__( 'Scheme and canonical URLs', 'the-seo-framework-extension-manager' );
						break 2;

					case 'php':
						$title = \__( 'PHP errors', 'the-seo-framework-extension-manager' );
						break 2;

					case 'sitemap':
						$title = \__( 'Sitemap output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'robots':
						$title = \__( 'Robots.txt output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'favicon':
						$title = \__( 'Favicon output', 'the-seo-framework-extension-manager' );
						break 2;

					case 'moresoon':
						$title = \__( 'More coming soon!', 'the-seo-framework-extension-manager' );
						break 2;

					default:
						// Falls back to underlying default.
						break 1;
				endswitch;
				// No break to fall back to default.

			default:
				$title = ucwords( str_replace( [ '-', '_' ], ' ', $key ) );
				break 1;
		endswitch;

		return $title ?? '';
	}

	/**
	 * Returns slab data content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The array key.
	 * @param mixed  $value The array value attached to $key.
	 * @param string $type  The pane-data type.
	 * @return string The HTML formed data, or empty string if data is incompatible.
	 */
	protected function parse_content( $key, $value, $type ) {

		switch ( $type ) :
			case 'issues':
				$content = $this->parse_issues_content( $key, $value );
				break;

			default:
				break;
		endswitch;

		return $content ?? '';
	}

	/**
	 * Parses issue $key content's $value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The array key.
	 * @param mixed  $value The array value attached to $key.
	 * @return string The issue data content.
	 */
	protected function parse_issues_content( $key, $value ) {

		static $tests;

		if ( ! isset( $tests ) )
			$tests = Tests::get_instance();

		if ( isset( $value['requires'] ) && version_compare( \TSFEM_E_MONITOR_VERSION, $value['requires'], '>=' ) ) {
			if ( isset( $value['tested'] ) && version_compare( \TSFEM_E_MONITOR_VERSION, $value['tested'], '<=' ) ) {
				$output = isset( $value['data'] ) ? $tests->{"issue_$key"}( $value['data'] ) : '';
				if ( '' !== $output ) {
					$content = $output['content'];
					$this->set_entry_state( $key, 'issues', $output['state'] );
				}
			}
		} else {
			$content = $this->get_em_requires_update_notification();
		}

		return $content ?? '';
	}

	/**
	 * Returns update notification string for information parsing.
	 *
	 * @since 1.0.0
	 *
	 * @return string Notifying user the Extension Manager requires an update.
	 */
	protected function get_em_requires_update_notification() {
		static $cache;
		return $cache ?: $cache = \esc_html__( 'The Extension Manager needs to be updated to interpret this data.', 'the-seo-framework-extension-manager' );
	}
}
