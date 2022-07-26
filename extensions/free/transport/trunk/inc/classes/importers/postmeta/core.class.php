<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Core importer class.
 *
 * @since 1.0.0
 * @access private
 * @abstract
 */
abstract class Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * @since 1.0.0
	 * @var array A set of conversions that should take place. : [
	 *     [
	 *         (?string[]) 'from'        The database table + index key to take data from,
	 *         (?string[]) 'to'          The database table + index key to set data to,
	 *         (?callable) 'transformer' The data transformer, if any,
	 *                                   The callable requires parameters:
	 *                                   `$value`
	 *         (?string[]) 'transmuter'  The complex data transmuter, if any: {
	 *              'name'           => (string)   The name, required,
	 *              'to'             => (\Generator) The transmuter, either to or from (or both) required.
	 *                                               The callable requires parameters:
	 *                                               `$type, $data, &$actions = null, &$results = null`
	 *                                               The callable must be of type Generator, as such,
	 *                                               it MUST contain the yield keyword, whether useful or not.
	 *              'from'           => (callable)   The transmuter, either to or from (or both) required.
	 *                                               The callable requires parameters:
	 *                                               `$type, $data, &$actions = null, &$results = null`
	 *              '(to|from)_data' => (mixed)    The pertaining callable data, custom.
	 *         }
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
	 * Sets up class, mainly required variables.
	 *
	 * @since 1.0.0
	 */
	protected function construct() {
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
	 * Returns a list of taxonomies that could have primary term support.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] List of taxonomy names.
	 */
	final protected function get_taxonomy_list_with_pt_support() {

		$taxonomies = array_filter(
			\get_taxonomies( [], 'objects' ),
			static function( $t ) {
				return ! empty( $t->hierarchical );
			}
		);

		return array_keys( $taxonomies );
	}

	/**
	 * Walks the postdata.
	 *
	 * @since 1.0.0
	 * @generator
	 * @abstract Feel free to overwrite this method.
	 * @global \wpdb $wpdb WordPress Database handler.
	 * @throws \Exception With WP_DEBUG enabled, exceptions are sent to the user.
	 *
	 * @yield string $key => mixed $data On sporadic intervals that could be pinged.
	 * @return array Results the transformation results.
	 */
	final public function import() {
		global $wpdb;

		// Assume that we don't need to keep track of how much data is transported?
		// Assume we do not need to transport in batches? -> Tackle when we need to? -> Tell user to try again, and again...?
		// Let analyser find that "hey, are you sure you want to transport _so_much_data_?"
		// Test if large_network()?

		// Offset needless FETCH_OBJ_R opcodes by using a single extra ASSIGN.
		$_globals_postmeta = $wpdb->postmeta;

		// phpcs:disable, Generic.WhiteSpace.ScopeIndent -- https://github.com/squizlabs/PHP_CodeSniffer/issues/3571

		foreach ( $this->conversion_sets as $conversion_set ) :
			prepare: {
				[ $transfer_from, $transfer_to, $transformer, $sanitizer, $transmuter ] = array_pad( $conversion_set, 5, null );

				[ $from_table, $from_index ] = array_pad( $transfer_from ?? [], 2, null );
				[ $to_table, $to_index ]     = array_pad( $transfer_to ?? [], 2, null );

				// Syntax only: The callable must work otherwise we might cause exploits by storing unsanitized values.
				// So, call these callables even when not strictly callable, let them cause errors.
				$has_transformer     = \is_callable( $transformer, true );
				$has_transmuter_from = \is_callable( $transmuter['from'][0] ?? null, true ) && \is_callable( $transmuter['from'][1] ?? null, true );
				$has_transmuter_to   = \is_callable( $transmuter['to'][0] ?? null, true ) && \is_callable( $transmuter['to'][1] ?? null, true );
				$has_sanitizer       = \is_callable( $sanitizer, true );
				$is_deletion_only    = ! $has_transmuter_to && ! $to_index;
			}

			getlist: {
				// Sanity is a virtue.
				$from_table = \esc_sql( $from_table ) ?: ( $from_index ? $_globals_postmeta : null );
				$to_table   = \esc_sql( $to_table ) ?: ( $to_index ? $_globals_postmeta : null );

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

				if ( $has_transmuter_from ) {
					$post_ids = \call_user_func_array(
						$transmuter['from'][0],
						[
							[
								'from_data' => $transmuter['from_data'] ?? null,
								'from'      => [ $from_table, $from_index ],
							],
						]
					);
				} else {
					$post_ids = $wpdb->get_col( $wpdb->prepare(
						// "SELECT DISTINCT post_id FROM `$from_table` WHERE meta_key = %s",
						// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table is escaped.
						"SELECT post_id FROM `$from_table` WHERE meta_key = %s", // No "DISTINCT", show "skipped" and explain in FAQ what it means.
						$from_index
					) ) ?: [];
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				}
				$total_posts = \count( $post_ids );
				yield 'foundPosts' => [
					$total_posts,
					$is_deletion_only
						? 'delete'
						: ( $has_transmuter_from || $has_transmuter_to
							? 'transmute'
							: 'import'
						),
				];
			}

			transmute: {
				$post_iterator = 0;

				foreach ( $post_ids as $post_id ) :
					// Clear query cache every 25 queries when Database debugging is enabled (e.g., via Query Monitor)
					if ( ! ( $post_iterator % 25 ) )
						$wpdb->queries = [];

					yield 'currentPostId' => [ $post_id, $total_posts, $post_iterator + 1 ];

					$identical_index = $has_transmuter_from || $has_transmuter_to
						? false
						: [ $from_table, $from_index ] === [ $to_table, $to_index ]; // phpcs:ignore, WordPress.PHP.YodaConditions.NotYoda -- Nani?

					$results = [
						'updated'     => false,
						'transformed' => false,
						'deleted'     => false,
						'only_end'    => $has_transmuter_to,
						'only_delete' => $is_deletion_only,
					];
					$actions = [
						'transform' => $has_transformer,
						// If the data goes nowhere there's no need to delete nor transport.
						'transport' => ! $identical_index,
						// With no old handle, we must delete manually. TODO fixme?
						'delete'    => $from_index && ! $identical_index,
						'sanitize'  => $has_sanitizer,
						'sanitized' => false,
					];
					$cleanup = [];

					$existing_value  = null; // Value in place by new plugin.
					$transport_value = null; // Value from old plugin.
					$set_value       = null; // Value to put for new plugin.

					// Test if data already exists on new entry.
					if ( ! $is_deletion_only ) {
						if ( $has_transmuter_to ) {
							// This won't allow merging when data comes from multiple places.
							// Transmuter must handle this early, and regard "stored" value as overwritable.
							$existing_value = \call_user_func_array(
								$transmuter['to'][0],
								[
									[
										'post_id' => $post_id,
										'to_data' => $transmuter['to_data'] ?? null,
										'to'      => [ $to_table, $to_index ],
										'from'    => [ $from_table, $from_index ],
									],
								]
							);
						} else {
							$existing_value = $wpdb->get_var( $wpdb->prepare(
								// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $to_table is escaped.
								"SELECT meta_value FROM `$to_table` WHERE post_id = %d AND meta_key = %s",
								$post_id,
								$to_index
							) );
							if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
						}
					}

					// If existing new data exists, don't overwrite with old.
					// Updating is still tried if $results['transformed'] is true.
					if ( isset( $existing_value ) )
						$actions['transport'] = false;

					if ( $has_transmuter_from ) {
						$transport_value = \call_user_func_array(
							$transmuter['from'][1],
							[
								[
									'post_id'           => $post_id,
									'from_data'         => $transmuter['from_data'] ?? null,
									'from'              => [ $from_table, $from_index ],
									'existing_value'    => $existing_value,
									'has_transmuter_to' => $has_transmuter_to,
								],
								&$actions,
								&$results,
								&$cleanup,
							]
						);
					} else {
						if ( \is_null( $existing_value ) ) {
							$transport_value = $wpdb->get_var( $wpdb->prepare(
								// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table is escaped.
								"SELECT meta_value FROM `$from_table` WHERE post_id = %d AND meta_key = %s",
								$post_id,
								$from_index
							) );
							if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
						} else {
							// If new data exists and index is the same, still try to transform (update). Otherwise, forgo.
							$actions['transform'] = $actions['transform'] && $identical_index;
						}
					}

					$set_value = $existing_value ?? $transport_value;

					if ( $actions['transform'] ) {
						$_pre_transform_value = $set_value;

						$set_value = \call_user_func_array(
							$transformer,
							[
								$set_value,
								$post_id,
								'post',
							]
						);

						$results['transformed'] = $_pre_transform_value !== $set_value;
					}

					if ( $actions['sanitize'] ) {
						$_pre_sanitize_value = $set_value;

						$set_value = \call_user_func( $sanitizer, $set_value );

						$actions['sanitized'] = $_pre_sanitize_value !== $set_value;
					}

					if ( \in_array( $set_value, $this->useless_data, true ) ) {
						$set_value              = null;
						$actions['delete']      = true;
						$results['transformed'] = false;
						$actions['transport']   = false;
					}

					if ( $has_transmuter_to ) {
						yield from \call_user_func_array(
							$transmuter['to'][1],
							[
								[
									'post_id'   => $post_id,
									'set_value' => $set_value,
									'from_data' => $transmuter['from_data'] ?? null,
									'from'      => [ $from_table, $from_index ],
									'to_data'   => $transmuter['to_data'] ?? null,
									'to'        => [ $to_table, $to_index ],
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
							$post_id,
							[ $from_table, $from_index ],
							[ $to_table, $to_index ],
							$actions,
							$results,
							$cleanup
						);
					}

					$is_lastpost = $post_iterator === $total_posts;
					yield 'results' => [ $results, $actions, $post_id, $is_lastpost, $has_transmuter_to ];

					// This also busts cache of caching plugins. Intended: Update the post!
					\clean_post_cache( $post_id );
					$post_iterator++;
				endforeach;
			}
		endforeach;

		// phpcs:enable, Generic.WhiteSpace.ScopeIndent -- https://github.com/squizlabs/PHP_CodeSniffer/issues/3571

		return true;
	}

	/**
	 * Transmute data from and to index.
	 *
	 * @since 1.0.0
	 *
	 * @param ?mixed    $set_value     The value to set (if any).
	 * @param int       $post_id       The post ID to transmute.
	 * @param ?string[] $transfer_from The table+index to transfer from.
	 * @param ?string[] $transfer_to   The table+index to transfer to.
	 * @param array     $actions       The actions for and after transmuation, passed by reference.
	 * @param array     $results       The results before and after transmuation, passed by reference.
	 * @param ?array    $cleanup       The extraneous database indexes to clean up.
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function transmute( $set_value, $post_id, $transfer_from, $transfer_to, &$actions, &$results, &$cleanup = null ) {
		global $wpdb;

		[ $from_table, $from_index ] = $transfer_from;
		[ $to_table, $to_index ]     = $transfer_to;

		if ( ! $to_index ) goto delete;

		if ( $actions['transport'] ) {
			if ( $from_index && $to_table === $from_table ) {
				if ( $results['transformed'] ) {
					$results['updated'] = $wpdb->update(
						$to_table,
						[
							'meta_key'   => $to_index,
							'meta_value' => $set_value,
						],
						[
							'post_id'  => $post_id,
							'meta_key' => $from_index,
						]
					);
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				} else {
					$results['updated'] = $wpdb->update(
						$to_table,
						[ 'meta_key' => $to_index ],
						[
							'post_id'  => $post_id,
							'meta_key' => $from_index,
						]
					);
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				}
				// Simply moved, mustn't be deleted. Should've already been false. Just in case:
				$actions['delete'] = false;
			} else {
				// We don't care whether it's transformed here.
				$results['updated'] = $wpdb->insert(
					$to_table,
					[
						'post_id'    => $post_id,
						'meta_key'   => $to_index,
						'meta_value' => $set_value,
					]
				);
				if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
			}
		} elseif ( $results['transformed'] ) {
			$results['updated'] = $wpdb->update(
				$to_table,
				[ 'meta_value' => $set_value ],
				[
					'post_id'  => $post_id,
					'meta_key' => $to_index,
				]
			);
			if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
			// Altered, mustn't be deleted. Should've already been false. Just in case:
			$actions['delete'] = false;
		}

		delete: if ( $actions['delete'] ) {
			$results['deleted'] += $from_index ? $wpdb->delete(
				$from_table,
				[
					'post_id'  => $post_id,
					'meta_key' => $from_index,
				]
			) : false;
			if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
		}

		// This is also "deleting", but then willfully.
		cleanup: foreach ( (array) $cleanup as [ $_from_table, $_from_index ] ) {
			$results['deleted'] += $wpdb->delete(
				$_from_table,
				[
					'post_id'  => $post_id,
					'meta_key' => $_from_index,
				]
			);
		}
	}
}
