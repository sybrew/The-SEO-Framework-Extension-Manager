<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers;

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
 * Core importer class.
 *
 * @since 1.0.0
 * @access private
 * @abstract
 */
abstract class Core {

	/**
	 * @since 1.0.0
	 * @var array A set of conversions that should take place. : [
	 *     [
	 *         (?string[]) 'from'          The database table + index key to take data from,
	 *         (?string[]) 'to'            The database table + index key to set data to,
	 *         (?callable) 'transformer'   The data transformer, if any,
	 *                                     The callable requires parameters:
	 *                                     `$value`
	 *         (?string[]) 'transmuter'    The complex data transmuter, if any: {
	 *              'name'           => (string)     The name, required,
	 *              'to'             => (\Generator) The transmuter, either to or from (or both) required.
	 *                                               The callable requires parameters:
	 *                                               `$type, $data, &$actions = null, &$results = null`
	 *                                               The callable must be of type Generator, as such,
	 *                                               it MUST contain the yield keyword, whether useful or not.
	 *              'from'           => (callable)   The transmuter, either to or from (or both) required.
	 *                                               The callable requires parameters:
	 *                                               `$type, $data, &$actions = null, &$results = null`
	 *              '(to|from)_data' => (mixed)      The pertaining callable data, custom.
	 *         }
	 *         (?callable) 'cb_after_loop' The after loop callable, if any.
	 *     ]
	 * ]
	 */
	protected $conversion_sets;

	/**
	 * @since 1.0.0
	 * @var array The useless data we shall discard.
	 */
	protected $useless_data = [ null, '', 0, '0', false, [], 's:0:"";', 'a:0:{}', '[]', '""', "''" ];

	/**
	 * @since 1.0.0
	 * @var ?string $type The data store type.
	 */
	protected $type;

	/**
	 * @since 1.0.0
	 * @var ?string $id_key The data store ID.
	 */
	protected $id_key;

	/**
	 * @since 1.0.0
	 * @var ?string $globals_table_fallback The data table to fall back to if none is assigned.
	 */
	protected $globals_table_fallback;

	/**
	 * @since 1.0.0
	 * @var null|callable The caching clearing callback. Leave null to not clear.
	 *                    First parameter will be the $id_key value.
	 */
	protected $cache_clear_cb = null;

	/**
	 * @since 1.1.0
	 * @var array The results array, zero'd. Immutable.
	 */
	protected $zero_results = [
		'updated'     => 0,
		'transformed' => 0,
		'deleted'     => 0,
		'sanitized'   => 0, // This value is recorded but useless. Also inaccurate if not updated.
		'inserted'    => 0,
		'only_end'    => 0,
		'only_delete' => 0,
	];

	/**
	 * @since 1.1.0
	 * @var array The actions array, zero'd. Immutable.
	 */
	protected $zero_actions = [
		'transform' => false,
		'transport' => false,
		'delete'    => false,
		'sanitize'  => false,
		'cleanup'   => false,
	];

	/**
	 * Sets up class, mainly required variables.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_vars();
	}

	/**
	 * Sets up variables.
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	abstract protected function setup_vars();

	/**
	 * Escapes input variable only when not set.
	 *
	 * @param ?string $var The variable to escape.
	 * @return ?string The escaped variable.
	 */
	final protected static function esc_sql_if_set( $var ) {
		return isset( $var ) ? \esc_sql( $var ) : $var;
	}

	/**
	 * Escapes input variable only when scalar for IN clause.
	 * Accepts a single dimensional array of strings.
	 *
	 * @param string|string[] $var The variable to escape.
	 * @return string|string[] The escaped variable. Returns array if array was inputted.
	 */
	final protected static function esc_sql_in( $var ) {
		if ( ! is_scalar( $var ) )
			$var = array_filter( (array) $var, 'is_scalar' );

		return \esc_sql( $var );
	}

	/**
	 * Walks the post/term/item metadata.
	 *
	 * @since 1.0.0
	 * @generator
	 * @abstract Feel free to overwrite this method.
	 * @global \wpdb $wpdb WordPress Database handler.
	 * @throws \Exception With WP_DEBUG enabled, exceptions are sent to the user.
	 *
	 * @yield string $key => mixed $data On sporadic intervals that could be pinged.
	 * @return boolean True on success, false on failure.
	 */
	final public function import() {
		global $wpdb;

		// Assume that we don't need to keep track of how much data is transported?
		// Assume we do not need to transport in batches? -> Tackle when we need to? -> Tell user to try again, and again...?
		// Let analyser find that "hey, are you sure you want to transport _so_much_data_?"
		// Test if large_network()?

		// Offset needless FETCH_OBJ_R opcodes by using a single extra ASSIGN.
		$_id_key                 = static::esc_sql_if_set( $this->id_key );
		$_type                   = $this->type;
		$_globals_table_fallback = static::esc_sql_if_set( $this->globals_table_fallback );
		$_cache_clear_cb         = $this->cache_clear_cb;
		$_has_cache_clear_cb     = \is_callable( $_cache_clear_cb );

		if ( ! isset( $_id_key, $_type, $_globals_table_fallback ) ) {
			yield 'debug' => "Must assign import class 'type', 'id_key', and 'globals_table_fallback'.";
			return false;
		}

		// phpcs:disable, Generic.WhiteSpace.ScopeIndent -- https://github.com/squizlabs/PHP_CodeSniffer/issues/3571

		foreach ( $this->conversion_sets as $conversion_set ) {
			prepare: {
				[ $transfer_from, $transfer_to, $transformer, $sanitizer, $transmuter, $cb_after_loop ]
					= array_pad( $conversion_set, 6, null );

				[ $from_table, $from_index ] = array_map(
					[ static::class, 'esc_sql_if_set' ],
					array_pad( $transfer_from ?? [], 2, null )
				);

				[ $to_table, $to_index ] = array_map(
					[ static::class, 'esc_sql_if_set' ],
					array_pad( $transfer_to ?? [], 2, null )
				);

				// Syntax only: The callable must work otherwise we might cause exploits by storing unsanitized values.
				// So, call these callables even when not strictly callable, let them cause errors.
				$has_transformer     = \is_callable( $transformer, true );
				$has_transmuter_from = \is_callable( $transmuter['from'][1] ?? null, true );
				$has_transmuter_to   = \is_callable( $transmuter['to'][1] ?? null, true );
				$has_sanitizer       = \is_callable( $sanitizer, true );
				$is_deletion_only    = ( ! $has_transmuter_to && ! $to_index )      // Doesn't go anywhere.
									|| ( ! $has_transmuter_from && ! $from_index ); // Came from nowhere.
			}

			getitems: {
				// Sanity is a virtue.
				$from_table = $from_table ?: ( $from_index ? $_globals_table_fallback : null );
				$to_table   = $to_table ?: ( $to_index ? $_globals_table_fallback : null );

				if ( $is_deletion_only ) {
					yield 'nowDeleting' => [ [ $from_table, $from_index ] ];
				} elseif ( $has_transmuter_from || $has_transmuter_to ) {
					yield 'nowTransmuting' => [ $transmuter['name'] ];
				} else {
					yield 'nowConverting' => [
						[ $from_table, $from_index ],
						[ $to_table, $to_index ],
					];
				}

				$item_ids = \call_user_func_array(
					$transmuter['from'][0] ?? [ $this, 'get_item_ids' ],
					[
						[
							'from_data' => $transmuter['from_data'] ?? null,
							'from'      => [ $from_table, $from_index ],
						],
					]
				);

				$total_items = \count( $item_ids );
				yield 'foundItems' => [
					$total_items,
					( $is_deletion_only
						? 'delete'
						: ( $has_transmuter_from || $has_transmuter_to
							? 'transmute'
							: 'import'
						)
					),
				];
			}

			transmute: {
				$item_iterator = 0;

				foreach ( $item_ids as $item_id ) {

					$item_iterator++;

					// Clear query cache every 25 queries when Database debugging is enabled (e.g., via Query Monitor)
					if ( ! ( $item_iterator % 25 ) )
						$wpdb->queries = [];

					yield 'currentItemId' => [ $item_id, $total_items, $item_iterator ];

					$identical_index = $has_transmuter_from || $has_transmuter_to
						? false
						: [ $from_table, $from_index ] === [ $to_table, $to_index ]; // phpcs:ignore, WordPress.PHP.YodaConditions.NotYoda -- Nani?

					$results = array_merge(
						$this->zero_results,
						[
							'only_end'    => (int) $has_transmuter_to,
							'only_delete' => (int) $is_deletion_only,
						]
					);
					$actions = array_merge(
						$this->zero_actions,
						[
							'transform' => $has_transformer,
							// If the data goes nowhere there's no need to delete nor transport.
							'transport' => ! $identical_index,
							'delete'    => $from_index && ! $identical_index,
							'sanitize'  => $has_sanitizer,
						]
					);
					$cleanup = [];

					$existing_value  = null; // Value already in put by new plugin.
					$transport_value = null; // Value from old plugin.
					$set_value       = null; // Value to put for new plugin.

					if ( ! $is_deletion_only ) {
						// Test if data already exists on new entry.
						// This won't allow merging when data comes from multiple places.
						// Transmuter must handle this early, and regard "stored" value as overwritable.
						$existing_value = \call_user_func_array(
							$transmuter['to'][0] ?? [ $this, 'get_existing_meta' ],
							[
								[
									'item_id' => $item_id,
									'to_data' => $transmuter['to_data'] ?? null,
									'to'      => [ $to_table, $to_index ],
									'from'    => [ $from_table, $from_index ],
								],
							]
						);

						// If existing new data exists, don't overwrite with old.
						// Updating is still tried if $results['transformed'] is true.
						if ( isset( $existing_value ) )
							$actions['transport'] = false;

						// If a transmuter is found, identical index is always false.
						// If no transmuter is found, and the $identical_index is true, then this is needless.
						if ( \is_null( $existing_value ) && ! $identical_index ) {
							$transport_value = \call_user_func_array(
								$transmuter['from'][1] ?? [ $this, 'get_transport_value' ],
								[
									[
										'item_id'        => $item_id,
										'from_data'      => $transmuter['from_data'] ?? null,
										'from'           => [ $from_table, $from_index ],
										'existing_value' => $existing_value,
										'has_transmuter_to' => $has_transmuter_to,
									],
									&$actions,
									&$results,
									&$cleanup,
								]
							);
						} elseif ( ! $identical_index ) {
							// If data exists but shares the index, still allow transforming.
							// Otherwise, we might as well skip transforming altogether.
							$actions['transform'] = false;
						}

						$set_value = $existing_value ?? $transport_value;

						if ( $actions['transform'] ) {
							$_pre_transform_value = $set_value;

							$set_value = \call_user_func_array(
								$transformer,
								[
									$set_value,
									$item_id,
									$_type,
								]
							);

							$results['transformed'] += (int) ( $_pre_transform_value !== $set_value );
						}

						if ( $actions['sanitize'] ) {
							$_pre_sanitize_value   = $set_value;
							$set_value             = \call_user_func( $sanitizer, $set_value );
							$results['sanitized'] += (int) ( $_pre_sanitize_value !== $set_value );
						}

						if ( \in_array( $set_value, $this->useless_data, true ) ) {
							$set_value              = null;
							$actions['delete']      = true; // Force delete also identical index.
							$actions['transport']   = false;
							$results['transformed'] = 0;
						}
					}
					if ( isset( $transmuter['to'][1] ) ) {
						yield from \call_user_func_array(
							$transmuter['to'][1],
							[
								[
									'set_value' => $set_value,
									'item_id'   => $item_id,
									'from'      => [ $from_table, $from_index ],
									'to'        => [ $to_table, $to_index ],
									'from_data' => $transmuter['from_data'] ?? null,
									'to_data'   => $transmuter['to_data'] ?? null,
								],
								&$actions,
								&$results,
								$cleanup,
							]
						);
					} else {
						// $actions and $results are passed by reference.
						$this->transmute(
							$set_value,
							$item_id,
							[ $from_table, $from_index ],
							[ $to_table, $to_index ],
							$actions,
							$results,
							$cleanup
						);
					}

					$is_lastitem = $item_iterator === $total_items;
					yield 'results' => [ $results, $actions, $is_lastitem ];

					// This also busts cache of caching plugins. Intended: Update the post/term/item!
					$_has_cache_clear_cb and \call_user_func( $_cache_clear_cb, $item_id );
				}
			}

			$cb_after_loop and yield from \call_user_func( $cb_after_loop, $item_ids );
		}

		// phpcs:enable, Generic.WhiteSpace.ScopeIndent -- https://github.com/squizlabs/PHP_CodeSniffer/issues/3571

		return true;
	}

	/**
	 * Gets item IDs for transport.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param mixed $data Any useful data pertaining to the current transmutation type.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return array Array of item ids with existing data.
	 */
	public function get_item_ids( $data ) {
		global $wpdb;

		[ $from_table, $from_index ] = $data['from'];

		$item_ids = $wpdb->get_col( $wpdb->prepare(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table is escaped.
			"SELECT `{$this->id_key}` FROM `$from_table` WHERE meta_key = %s", // No "DISTINCT", show "skipped" and explain in FAQ what it means.
			$from_index
		) );
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $item_ids ?: [];
	}

	/**
	 * Gets existing metadata.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param mixed $data Any useful data pertaining to the current transmutation type.
	 * @return mixed Any data if existing values are present, null otherwise.
	 */
	public function get_existing_meta( $data ) {
		return $this->get_var( $data['to'][0], $data['to'][1], $data['item_id'] );
	}

	/**
	 * Gets transporting value.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param mixed $data Any useful data pertaining to the current transmutation type.
	 * @return mixed Any data if transport values are present, null otherwise.
	 */
	public function get_transport_value( $data ) {
		return $this->get_var( $data['from'][0], $data['from'][1], $data['item_id'] );
	}

	/**
	 * Gets value from database.
	 *
	 * @since 1.1.0
	 * @global \wpdb $wpdb WordPress Database handler.
	 *
	 * @param ?string $table The database table.
	 * @param ?string $index The table index key.
	 * @param ?int    $id    The table item ID for `$this->id_key`.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 * @return mixed Any data if transport values are present, null otherwise.
	 */
	protected function get_var( $table, $index, $id ) {

		if ( ! isset( $table, $index ) )
			return null;

		global $wpdb;

		$var = $wpdb->get_var( $wpdb->prepare(
			// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table is escaped.
			"SELECT meta_value FROM `$table` WHERE `{$this->id_key}` = %d AND meta_key = %s",
			$id,
			$index
		) );
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

		return $var;
	}

	/**
	 * Transmute data from and to index.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb The WordPress database instance.
	 *
	 * @param ?mixed    $set_value     The value to set (if any).
	 * @param int       $item_id       The post/term/item ID to transmute.
	 * @param ?string[] $transfer_from The table+index to transfer from.
	 * @param ?string[] $transfer_to   The table+index to transfer to.
	 * @param array     $actions       The actions for and after transmuation, passed by reference.
	 * @param array     $results       The results before and after transmuation, passed by reference.
	 * @param ?array    $cleanup       The extraneous database indexes to clean up.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function transmute( $set_value, $item_id, $transfer_from, $transfer_to, &$actions, &$results, $cleanup = null ) {
		global $wpdb;

		[ $from_table, $from_index ] = $transfer_from;
		[ $to_table, $to_index ]     = $transfer_to;

		$_id_key = $this->id_key;

		if ( ! $to_index ) goto delete;

		$set_value = \maybe_serialize( $set_value );

		if ( $actions['transport'] ) {
			if ( $from_index && $to_table === $from_table ) {
				if ( $results['transformed'] ) {
					$results['updated'] += (int) $wpdb->update(
						$to_table,
						[
							'meta_key'   => $to_index,
							'meta_value' => $set_value,
						],
						[
							$_id_key   => $item_id,
							'meta_key' => $from_index,
						]
					);
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				} else {
					$results['updated'] += (int) $wpdb->update(
						$to_table,
						[ 'meta_key' => $to_index ],
						[
							$_id_key   => $item_id,
							'meta_key' => $from_index,
						]
					);
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				}
				// Simply moved, mustn't be deleted. Should've already been false. Just in case:
				$actions['delete'] = false;
			} else {
				// We don't care whether it's transformed here.
				// Use 'replace' instead of 'insert', for 'replace' won't create duplicates,
				// replaces "bad" data, and inserts if data doesn't exist.
				$_results = (int) $wpdb->replace(
					$to_table,
					[
						$_id_key     => $item_id,   // Shared Key
						'meta_key'   => $to_index,  // Local "unique" Key
						'meta_value' => $set_value,
					]
				);
				if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

				$results['updated']  += $_results;
				$results['inserted'] += $_results;
			}
		} else {
			if ( $results['transformed'] ) {
				$results['updated'] += (int) $wpdb->update(
					$to_table,
					[ 'meta_value' => $set_value ],
					[
						$_id_key   => $item_id,
						'meta_key' => $to_index,
					]
				);
				if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				// Altered, mustn't be deleted. Should've already been false. Just in case:
				$actions['delete'] = false;
			}
		}

		delete: if ( $actions['delete'] ) {
			if ( $from_index ) {
				$results['deleted'] += (int) $wpdb->delete(
					$from_table,
					[
						$_id_key   => $item_id,
						'meta_key' => $from_index,
					]
				);
				if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
			}
		}

		if ( $cleanup ) $actions['cleanup'] = true;

		// This is also "deleting", but then assigned manually by developer. Ignores other tests.
		cleanup: foreach ( (array) $cleanup as [ $_from_table, $_from_index ] ) {
			$results['deleted'] += (int) $wpdb->delete(
				$_from_table,
				[
					$_id_key   => $item_id,
					'meta_key' => $_from_index,
				]
			);
			if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
		}
	}

	/**
	 * Deletes single data from index.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb The WordPress database instance.
	 *
	 * @param int       $item_id The post/term/item ID to transmute.
	 * @param ?string[] $from    The table+index to transfer from.
	 * @param array     $results The results before and after transmuation, passed by reference.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function delete( $item_id, $from, &$results ) {
		global $wpdb;

		[ $from_table, $from_index ] = $from;

		$results['deleted'] += (int) $wpdb->delete(
			$from_table,
			[
				$this->id_key => $item_id,
				'meta_key'    => $from_index,
			]
		);
		if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
	}

	/**
	 * Unserializes data a bit safer than WordPress does.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data Expected to be serialized.
	 * @return ?mixed The unserialized data without classes. Null on failure.
	 */
	protected function maybe_unserialize_no_class( $data ) {
		return \is_serialized( $data )
			? unserialize( trim( $data ), [ 'allowed_classes' => [ 'stdClass' ] ] ) // phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions -- it fine.
			: $data;
	}
}
