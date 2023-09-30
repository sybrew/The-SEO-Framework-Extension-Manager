<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\TermMeta;

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
 * Base Term importer class.
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

	/**
	 * Obtains ids from transmutable taxonomy metadata.
	 *
	 * @since 1.1.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param array $data Any useful data pertaining to the current transmutation type.
	 * @throws \Exception On database error when \WP_DEBUG is enabled.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	protected function _get_populated_term_ids( $data ) {
		global $wpdb;

		// Redundant. If 'indexes' is a MD-array, though, we'd get 'Array', which is undesirable.
		// MD = multidimensional (we refer to that more often using MD).
		$indexes    = implode( "', '", static::esc_sql_in( $data['from_data']['indexes'] ) );
		$from_table = \esc_sql( $data['from_data']['table'] );

		$item_ids = $wpdb->get_col(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table/$indexes are escaped.
			"SELECT DISTINCT `{$this->id_key}` FROM `$from_table` WHERE meta_key IN ('$indexes')"
		);
		if ( \WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $item_ids ?: [];
	}

	/**
	 * Returns combined metadata from external for ID for transportation.
	 *
	 * @since 1.1.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param array  $actions The actions for and after transmuation, passed by reference.
	 * @param array  $results The results before and after transmuation, passed by reference.
	 * @param ?array $cleanup The extraneous database indexes to clean up, passed by reference.
	 * @throws \Exception On database error when \WP_DEBUG is enabled.
	 * @return array|null Array if existing values are present, null otherwise.
	 */
	protected function _get_congealed_transport_value( $data, &$actions, &$results, &$cleanup ) {
		global $wpdb;

		// Redundant. If 'indexes' is a MD-array, though, we'd get 'Array', which is undesirable.
		// MD = multidimensional (we refer to that more often using MD).
		$indexes    = implode( "', '", static::esc_sql_in( $data['from_data']['indexes'] ) );
		$from_table = \esc_sql( $data['from_data']['table'] );

		$metadata = $wpdb->get_results( $wpdb->prepare(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table/$indexes are escaped.
			"SELECT meta_key, meta_value FROM `$from_table` WHERE `{$this->id_key}` = %d AND meta_key IN ('$indexes')",
			$data['item_id']
		) );
		if ( \WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $metadata ? array_column( $metadata, 'meta_value', 'meta_key' ) : [];
	}

	/**
	 * Transmutes separated term metdata into a single index.
	 *
	 * @since 1.1.0
	 * @generator
	 *
	 * @param array  $data    Any useful data pertaining to the current transmutation type.
	 * @param ?array $actions The actions for and after transmuation, passed by reference.
	 * @param ?array $results The results before and after transmutation, passed by reference.
	 * @throws \Exception On database error when \WP_DEBUG is enabled.
	 */
	protected function _term_meta_transmuter( $data, &$actions, &$results ) {

		[ $from_table, $from_index ] = $data['from'];
		[ $to_table, $to_index ]     = $data['to'];

		$set_value = [];

		// Nothing to do here, TSF already has value set. Skip to next item.
		if ( ! $actions['transport'] ) goto useless;

		foreach ( $data['to_data']['pretransmute'] as $type => $pretransmutedata ) {
			\call_user_func_array(
				$pretransmutedata['cb'],
				[
					$pretransmutedata['data'],
					&$data['set_value'],
					&$actions,
					&$results,
				]
			);
		}

		foreach ( $data['to_data']['transmuters'] as $from => $to ) {
			$_set_value = $data['set_value'][ $from ] ?? null;

			// We assume here that all data without value is useless.
			// This might prove an issue later, where 0 carries significance.
			// Though, no developer in their right mind would store 0 or empty string... right?
			if ( \in_array( $_set_value, $this->useless_data, true ) ) continue;

			$_transformed = 0;

			if ( isset( $data['to_data']['transformers'][ $from ] ) ) {
				$_pre_transform_value = $_set_value;

				$_set_value = \call_user_func_array(
					$data['to_data']['transformers'][ $from ],
					[
						$_set_value,
						$data['item_id'],
						$this->type,
						[ $from_table, $from_index ],
						[ $to_table, $to_index ],
					]
				);

				// We actually only read this as boolean. Still, might be fun later.
				$_transformed = (int) ( $_pre_transform_value !== $_set_value );
			}

			if ( isset( $data['to_data']['sanitizers'][ $from ] ) ) {
				$_pre_sanitize_value   = $_set_value;
				$_set_value            = \call_user_func( $data['to_data']['sanitizers'][ $from ], $_set_value );
				$results['sanitized'] += (int) ( $_pre_sanitize_value !== $set_value );
			}

			if ( ! \in_array( $_set_value, $this->useless_data, true ) ) {
				$set_value[ $to ]        = $_set_value;
				$results['transformed'] += $_transformed;

				// If the title is not useless, assume it must remain how the user set it.
				if ( 'doctitle' === $to )
					$set_value['title_no_blog_name'] = 1;
			}
		}

		if ( \in_array( $set_value, $this->useless_data, true ) ) {
			useless:;
			$set_value              = null;
			$actions['transport']   = false;
			$results['transformed'] = 0;
		}

		$this->transmute(
			$set_value,
			$data['item_id'],
			[ $from_table, $from_index ], // Should be [ null, null ]
			[ $to_table, $to_index ],
			$actions,
			$results,
			$data['to_data']['cleanup']
		);

		yield 'transmutedResults' => [ $results, $actions ];
	}
}
