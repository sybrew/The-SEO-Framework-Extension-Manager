<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

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
 * Importer for Rank Math.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits abstract setup_vars.
 */
final class SEO_By_Rank_Math extends Base {

	/**
	 * Sets up variables.
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	protected function setup_vars() {
		global $wpdb;

		// Construct and fetch classname.
		$transformer_class = \get_class(
			\TSF_Extension_Manager\Extension\Transport\Transformers\SEO_By_Rank_Math::get_instance()
		);

		/**
		 * [ $from_table, $from_index ]
		 * [ $to_table, $to_index ]
		 * $transformer
		 * $sanitizer
		 * $transmuter
		 */
		$this->conversion_sets = [];
	}
}
