<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Classes
 */

namespace TSF_Extension_Manager\Extension\Focus;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Focus\Admin
 *
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin extends Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Interface,
		\TSF_Extension_Manager\Extension_Views;

	/**
	 * Constructor.
	 */
	private function construct() {

		/**
		 * @see trait TSF_Extension_Manager\Extension_Views
		 */
		$this->view_location_base = TSFEM_E_FOCUS_DIR_PATH . 'views' . DIRECTORY_SEPARATOR;

		$this->prepare_ajax();
		$this->prepare_inpostgui();
	}

	/**
	 * Prepares inpost AJAX callbacks.
	 *
	 * @since 1.0.0
	 */
	private function prepare_ajax() {
		\wp_doing_ajax() and Ajax::_init( $this );
	}

	/**
	 * Prepares inpost GUI.
	 *
	 * @since 1.0.0
	 */
	private function prepare_inpostgui() {

		// Prepares InpostGUI's class for nonce checking.
		\TSF_Extension_Manager\InpostGUI::prepare();

		\add_action( 'tsfem_inpost_before_enqueue_scripts', [ $this, '_enqueue_inpost_scripts' ] );

		// Called late because we need to access the meta object after current_screen.
		\add_action( 'the_seo_framework_pre_page_inpost_box', [ $this, '_prepare_inpost_views' ] );

		\add_action( 'tsfem_inpostgui_verified_nonce', [ $this, '_save_meta' ], 10, 3 );
	}

	/**
	 * Returns active focus elements.
	 *
	 * @since 1.0.0
	 *
	 * @return array The active focus elements.
	 */
	private function get_focus_elements() {
		/**
		 * Applies filters 'the_seo_framework_focus_elements'.
		 *
		 * When a selector can't be found in the DOM, it's skipped and cannot be appended
		 * or be dominating.
		 *
		 * When a selector is dominating, the order is considered. When the selector is
		 * the last dominating thing on the list, it's the only thing used for the scoring.
		 *
		 * When a selector is appending, and no dominating items are available, then
		 * it's considered as an addition for scoring.
		 *
		 * The querySelector fields must be visible for highlighting. When it's
		 * not visible, highlighting is ignored.
		 *
		 * Elements can also be added dynamically in JS, for Gutengerg block support.
		 *
		 * @see JavaScript tsfem_e_focus_inpost.updateFocusRegistry();
		 *
		 * The fields must be in order of importance when dominating.
		 * Apply this filter with a high $priority value to ensure domination.
		 * @see WordPress `add_filter()`
		 * @see PHP `array_push()`
		 * @see PHP `array_unshift()`
		 * @since 1.0.0
		 * @NOTE: No longer reliably works with Gutenberg.
		 *
		 * @param array $elements : { 'type' => [
		 *    'querySelector' => string 'append|dominate'.
		 * }
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_focus_elements',
			[
				[
					'pageTitle'      => [
						'#titlewrap > input'     => 'append',
						'#tsfem-focus-gbc-title' => 'dominate',
						// NOTE: Can't reliably fetch Gutenberg's from DOM.
					],
					'pageUrl'        => [
						'#sample-permalink'     => 'dominate',
						'#tsfem-focus-gbc-link' => 'dominate',
						// NOTE: Can't reliably fetch Gutenberg's from DOM.
					],
					'pageContent'    => [
						'#content'                 => 'append',
						'#tsfem-focus-gbc-content' => 'append',
						// NOTE: Can't reliably fetch Gutenberg's from DOM.
					],
					'seoTitle'       => [
						'#tsf-title-reference_autodescription_title' => 'dominate',
					],
					'seoDescription' => [
						'#tsf-description-reference_autodescription_description' => 'dominate',
					],
				],
			]
		);
	}

	/**
	 * Determines if the API supports the language.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added language support type selection.
	 *
	 * @param string $type The type of supoport, either 'any', 'synonyms', or 'inflections'.
	 * @return bool True if supported, false otherwise.
	 */
	private function is_language_supported( $type ) {

		// This works for now, but in the future, we may want to be more specific.
		$locale    = substr( \get_locale(), 0, 2 );
		$supported = false;

		if ( \in_array( $type, [ 'any', 'synonyms' ], true ) ) {
			$supported = 'en' === $locale;
		}
		if ( \in_array( $type, [ 'any', 'inflections' ], true ) ) {
			$supported = $supported || \in_array( $locale, [ 'en', 'es', 'lv', 'hi', 'sw', 'ta', 'ro' ], true );
		}

		return $supported;
	}

	/**
	 * Enqueues in-post scripts, dependencies, colors, etc.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Now passes the isGutenbergPage data property.
	 * @since 1.5.0 No longer passes isGutenbergPage property; rely on tsfPost instead.
	 * @access private
	 * @uses The_SEO_Framework\Builders\Scripts
	 *
	 * @param string $scripts Static class name: The_SEO_Framework\Builders\Scripts
	 */
	public function _enqueue_inpost_scripts( $scripts ) {

		if ( \TSF_Extension_Manager\has_run( __METHOD__ ) ) return;

		/**
		 * @since 1.5.0
		 * @param int $interval The auto-parsing interval in ms. Default 45 seconds.
		 *                      Set this to 4999 or lower (preferred: -1) to disable this feature.
		 */
		$interval = (int) \apply_filters( 'the_seo_framework_focus_auto_interval', 45000 );

		$scripts::register( [
			[
				'id'       => 'tsfem-focus-inpost',
				'type'     => 'js',
				'name'     => 'tsfem-focus-inpost',
				'base'     => TSFEM_E_FOCUS_DIR_URL . 'lib/js/',
				'ver'      => TSFEM_E_FOCUS_VERSION,
				'deps'     => [ 'jquery', 'tsf', 'tsf-tt', 'tsfem-inpost', 'tsfem-worker' ],
				'autoload' => true,
				'l10n'     => [
					'name' => 'tsfem_e_focusInpostL10n',
					'data' => [
						'nonce'              => \current_user_can( 'edit_post', $GLOBALS['post']->ID )
							? \wp_create_nonce( 'tsfem-e-focus-inpost-nonce' )
							: false,
						'focusElements'      => $this->get_focus_elements(),
						'defaultLexicalForm' => json_encode( $this->default_lexical_form ),
						'languageSupported'  => [
							'any'         => $this->is_language_supported( 'any' ),
							'inflections' => $this->is_language_supported( 'inflections' ),
							'synonyms'    => $this->is_language_supported( 'synonyms' ),
						],
						'language'           => \get_locale(),
						'i18n'               => [
							'noExampleAvailable' => \__( 'No example available.', 'the-seo-framework-extension-manager' ),
							'parseFailure'       => \__( 'A parsing failure occurred.', 'the-seo-framework-extension-manager' ),
						],
						'scripts'            => [
							'parserWorker' => $this->get_worker_file_location(),
						],
						'settings'           => [
							'analysisInterval' => $interval,
						],
					],
				],
				'tmpl'     => [
					'file' => $this->_get_view_location( 'inpost/js-templates' ),
				],
			],
			[
				'id'       => 'tsfem-focus-inpost',
				'type'     => 'css',
				'name'     => 'tsfem-focus-inpost',
				'base'     => TSFEM_E_FOCUS_DIR_URL . 'lib/css/',
				'ver'      => TSFEM_E_FOCUS_VERSION,
				'deps'     => [ 'tsf', 'tsf-tt', 'tsfem-inpost' ],
				'autoload' => true,
				'hasrtl'   => false,
				'inline'   => [
					'.tsfem-e-focus-content-loader-bar' => [
						'background:{{$color_accent}}',
					],
					'.tsfem-e-focus-collapse-header:hover .tsfem-e-focus-arrow-item' => [
						'color:{{$color}}',
					],
				],
			],
		] );
	}

	/**
	 * Returns the Focus Parser worker file location.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	private function get_worker_file_location() {

		$min = \tsf()->script_debug ? '' : '.min';

		return \esc_url( \set_url_scheme( TSFEM_E_FOCUS_DIR_URL . "lib/js/tsfem-focus-parser.worker{$min}.js" ) );
	}

	/**
	 * Prepares inpost options for the 'audit' tab.
	 *
	 * Defered because we need to access meta.
	 *
	 * @since 1.0.0
	 * @uses class \TSF_Extension_Manager\InpostGUI
	 * @uses trait \TSF_Extension_Manager\Extensions_Post_Meta_Cache
	 * @access private
	 */
	public function _prepare_inpost_views() {

		\TSF_Extension_Manager\InpostGUI::activate_tab( 'audit' );

		$post_meta = [
			'pm_index' => $this->pm_index,
			'post_id'  => \tsf()->get_the_real_ID(),
			'kw'       => [
				'label'        => [
					'title' => \__( 'Subject Analysis', 'the-seo-framework-extension-manager' ),
					'desc'  => \__( 'Set subjects and learn how you can improve their focus.', 'the-seo-framework-extension-manager' ),
					'link'  => 'https://theseoframework.com/extensions/focus/#usage',
				],
				//! Don't set default, it's already pre-populated.
				'values'       => $this->get_post_meta( 'kw', null ),
				'option_index' => 'kw',
			],
		];

		\TSF_Extension_Manager\InpostGUI::register_view(
			$this->_get_view_location( 'inpost/inpost' ),
			[
				'post_meta'          => $post_meta,
				'defaults'           => $this->pm_defaults,
				'template_cb'        => [ $this, '_output_focus_template' ],
				'is_premium'         => \tsfem()->is_premium_user(),
				'language_supported' => $this->is_language_supported( 'any' ),
			],
			'audit'
		);
	}

	/**
	 * Saves or deletes post meta.
	 *
	 * @since 1.0.0
	 * @see \TSF_Extension_Manager\InpostGUI::_verify_nonce()
	 * @see action 'tsfem_inpostgui_verified_nonce'
	 *
	 * @param \WP_Post      $post              The post object.
	 * @param array|null    $data              The meta data.
	 * @param int (bitwise) $save_access_state The state the save is in.
	 */
	public function _save_meta( $post, $data, $save_access_state ) {

		if ( ! \TSF_Extension_Manager\InpostGUI::is_state_safe( $save_access_state ) )
			return;

		$this->process_meta( $post, $data );
	}

	/**
	 * Processes post metdata after validation.
	 *
	 * @since 1.0.0
	 * @uses trait \TSF_Extension_Manager\Extensions_Post_Meta_Cache
	 *
	 * @param \WP_Post   $post The post object.
	 * @param array|null $data The meta data.
	 */
	private function process_meta( $post, $data ) {

		if ( empty( $data[ $this->pm_index ] ) )
			return;

		$this->set_extension_post_meta_id( $post->ID );

		$store = [];
		foreach ( $data[ $this->pm_index ] as $key => $value ) :
			switch ( $key ) {
				case 'kw':
					$store[ $key ] = $this->sanitize_keyword_data( (array) $value );
					break;

				default:
					break;
			}
			if ( \is_null( $store[ $key ] ) ) unset( $store[ $key ] );
		endforeach;

		if ( $store ) {
			foreach ( $store as $key => $value ) {
				$this->update_post_meta( $key, $value );
			}
		} else {
			$this->delete_post_meta_index();
		}
	}

	/**
	 * Sanitizes all keyword data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $values The keyword data.
	 * @return array|null The sanitized keyword data.
	 */
	private function sanitize_keyword_data( $values ) {

		$output = [];
		foreach ( $values as $id => $items ) {
			// Don't store when no keyword is set.
			if ( ! \strlen( $items['keyword'] ?? '' ) )
				continue;

			foreach ( (array) $items as $key => $value ) {
				$out = $this->sanitize_keyword_data_by_type( $key, $value );

				if ( isset( $out ) )
					$output[ $id ][ $key ] = $out;
			}
		}

		// When all entries are emptied, clear meta data.
		if ( ! $output )
			return null;

		// Fills missing data to maintain consistency.
		foreach ( [ 0, 1, 2 ] as $k ) {
			// PHP 7.4: ??=
			if ( ! isset( $output[ $k ] ) ) {
				$output[ $k ] = $this->pm_defaults['kw'][ $k ];
			}
		}
		return $output ?: null;
	}

	/**
	 * Sanitizes keyword data by type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type  The keyword entry type.
	 * @param string $value The corresponding value.
	 * @return string|null The sanitized value.
	 */
	private function sanitize_keyword_data_by_type( $type, $value ) {
		switch ( $type ) :
			case 'keyword':
				$value = \sanitize_text_field( $value );
				break;

			case 'lexical_form':
			case 'definition_selection':
				$value = (string) \absint( $value );
				break;

			case 'lexical_data':
			case 'inflection_data':
			case 'synonym_data':
				// An empty array will be returned on failure. This will be refilled in the UI.
				$value = json_decode( $value, true ) ?: [];
				break;

			case 'scores':
				if ( ! \is_array( $value ) ) {
					$value = [];
				} else {
					foreach ( $value as $_t => $_v ) {
						// Convert to float, have 2 f decimals, trim trailing zeros, trim trailing dots, convert to string.
						// 2x rtrim: first trim trailing 0's, then trim remainder . (if any);
						// don't trim both at the same time, otherwise 90.0 -> 9, instead of 90.0 -> 90
						$value[ $_t ] = (string) ( rtrim( rtrim( sprintf( '%.2F', (float) $_v ), '0' ), '.' ) ?: 0 );
					}
				}
				break;

			case 'active_inflections':
			case 'active_synonyms':
				$patterns = [
					'/[^0-9,]+/', // Remove everything but "0-9,"
					'/(?=(,,)),|,[^0-9]?+$/', // Fix ",,,"->"," and remove trailing ","
				];

				$value = preg_replace( $patterns, '', $value );
				break;

			default:
				unset( $value );
				break;
		endswitch;

		return $value ?? null;
	}

	/**
	 * Outputs focus template.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $args The focus template arguments.
	 */
	public function _output_focus_template( $args ) {
		$this->get_view( 'inpost/focus-template', $args );
	}

	/**
	 * Outputs focus template.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $args The focus template arguments.
	 */
	private function output_score_template( $args ) {
		$this->get_view( 'inpost/score-template', $args );
	}
}
