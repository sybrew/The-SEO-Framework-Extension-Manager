<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\TermMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * Copyright (C) 2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Core importer class.
 *
 * @since 1.0.0
 * @access private
 * @abstract via extends
 */
abstract class Core extends \TSF_Extension_Manager\Extension\Transport\Importers\Base {
	use \TSF_Extension_Manager\Construct_Sub_Once_Interface;

	/**
	 * Sets up class, mainly required variables.
	 *
	 * @since 1.0.0
	 */
	protected function construct() {
		$this->type                   = 'term';
		$this->id_key                 = 'term_id';
		$this->globals_table_fallback = $GLOBALS['wpdb']->termmeta;
		$this->cache_clear_cb         = [ $this, 'clean_term_cache' ];
	}

	/**
	 * Cleans term cache.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $term_id        The term ID to clear cache for.
	 * @param bool $clean_taxonomy Whether to clean the taxonomy indexes.
	 *                             This probably isn't needed during any transportation;
	 *                             we update the terms, not add or remove them.
	 * @return ?null Might one day return something.
	 */
	protected function clean_term_cache( $term_id, $clean_taxonomy = false ) {

		$term = \get_term( $term_id );

		return isset( $term->taxonomy )
			? \clean_term_cache( $term_id, $term->taxonomy, $clean_taxonomy )
			: null;
	}
}
