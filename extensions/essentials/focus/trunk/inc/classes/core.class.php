<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus
 */

namespace TSF_Extension_Manager\Extension\Focus;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Require extension options trait.
 *
 * @since 1.0.0
 */
\TSF_Extension_Manager\_load_trait( 'extension/post-meta' );

/**
 * Class TSF_Extension_Manager\Extension\Focus\Core
 *
 * Holds extension core methods.
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 */
class Core {
	use \TSF_Extension_Manager\Construct_Core_Interface,
		\TSF_Extension_Manager\Extension_Post_Meta;

	/**
	 * Holds default lexical form value.
	 *
	 * @since 1.0.0
	 * @var string Default lexical form value in JSON.
	 */
	protected $default_lexical_form;

	/**
	 * Child constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$that = __NAMESPACE__ . '\\Admin';
		$this instanceof $that or \wp_die( -1 );

		$this->default_lexical_form = [
			[
				'value' => '',
				'name'  => \__( '&mdash; Lexical form &mdash;', 'the-seo-framework-extension-manager' ),
			],
		];

		/**
		 * Set meta index and defaults.
		 *
		 * @see trait TSF_Extension_Manager\Extension_Post_Meta
		 */
		$this->pm_index = 'focus';

		$this->pm_defaults = [
			// Fills 3 sequential array keys with these values.
			'kw' => array_fill(
				0,
				3,
				[
					'keyword'              => '',
					'lexical_form'         => '',
					'lexical_data'         => $this->default_lexical_form,
					'definition_selection' => '',
					'inflection_data'      => [],
					'synonym_data'         => [],
					'active_inflections'   => '',
					'active_synonyms'      => '',
					'score'                => 0,
					'scores'               => [],
				]
			),
		];

		/**
		 * Applies filter 'the_seo_framework_focus_default_meta'
		 *
		 * @since 1.0.0
		 * @TODO see if this is necessary.
		 * @param array $pm_defaults The default post meta settings.
		 */
		//$this->pm_defaults = \apply_filters_ref_array( 'the_seo_framework_focus_default_meta', [ $this->pm_defaults ] );
	}
}
