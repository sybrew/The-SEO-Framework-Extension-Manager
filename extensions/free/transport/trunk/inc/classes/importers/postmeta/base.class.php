<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * copyright (C) 2022-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Base Post importer class.
 *
 * @since 1.0.0
 * @access private
 * @abstract via extends
 */
abstract class Base extends \TSF_Extension_Manager\Extension\Transport\Importers\Core {

	/**
	 * Sets up class, mainly required variables.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->type                   = 'post';
		$this->id_key                 = 'post_id';
		$this->globals_table_fallback = $GLOBALS['wpdb']->postmeta;
		$this->cache_clear_cb         = 'clean_post_cache';
	}

	/**
	 * Returns a list of taxonomies that could have primary term support.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] List of taxonomy names.
	 */
	final protected function get_taxonomy_list_with_pt_support() {

		$taxonomies = array_filter(
			\get_taxonomies( [], 'objects' ),
			static function ( $t ) {
				return ! empty( $t->hierarchical );
			}
		);

		return array_keys( $taxonomies );
	}
}
