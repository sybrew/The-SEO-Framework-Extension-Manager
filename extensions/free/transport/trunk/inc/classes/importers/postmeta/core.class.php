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
	 * @var array[] A set of conversions that should take place. : [
	 *     [
	 *         (?string[]) 'from'        The database table + index key to take data from,
	 *         (?string[]) 'to'          The database table + index key to set data to,
	 *         (?callable) 'transformer' The data transformer, if any,
	 *                                   The callable requires parameters:
	 *                                   `$value`
	 *         (?string[]) 'transmuter'  The complex data transmuter, if any: {
	 *              'name'           => (string)   The name, required,
	 *              'to|from'        => (callable) The transformers, any required, both available,
	 *                                             The callable requires parameters:
	 *                                             `$type, $data, &$actions = null, &$results = null`
	 *              '(to|from)_data' => (mixed)    The pertaining callable data, custom.
	 *         }
	 *     ]
	 * ]
	 */
	protected $conversion_sets;

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

		foreach ( $this->conversion_sets as $conversion_set ) :
			prepare: {
				[ $transfer_from, $transfer_to, $transformer, $transmuter ] = array_pad( $conversion_set, 4, null );

				[ $from_table, $from_index ] = array_pad( $transfer_from ?? [], 2, null );
				[ $to_table, $to_index ]     = array_pad( $transfer_to ?? [], 2, null );
			}

			getlist:
				// Sanity is a virtue.
				$from_table = \esc_sql( $from_table ) ?: $_globals_postmeta;
				$to_table   = \esc_sql( $to_table ) ?: $_globals_postmeta;

				yield ( $transmuter ? 'nowTransmuting' : 'nowConverting' ) => [
					[ $from_table, $from_index ],
					[ $to_table, $to_index ],
					$transmuter['name'] ?? null,
				];

				if ( isset( $transmuter['from'] ) ) {
					$post_ids = \call_user_func_array(
						$transmuter['from'],
						[
							'get:post_ids',
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
				yield 'foundPosts' => [ $total_posts ];

			transmute: // phpcs:ignore, Generic.WhiteSpace.ScopeIndent.Incorrect -- https://github.com/squizlabs/PHP_CodeSniffer/issues/3571
				$post_iterator = 0;

				foreach ( $post_ids as $post_id ) :
					// Clear query cache every 25 queries when Database debugging is enabled (e.g., via Query Monitor)
					if ( ! ( $post_iterator % 25 ) )
						$wpdb->queries = [];

					$results = [];

					yield 'currentPostId' => [ $post_id, $total_posts, $post_iterator + 1 ];

					// phpcs:ignore, WordPress.PHP.YodaConditions.NotYoda -- Nani?
					$identical_index = $transmuter ? false : [ $from_table, $from_index ] === [ $to_table, $to_index ];

					$actions = [
						'transformed' => false,
						'transform'   => (bool) $transformer,
						// If the data goes nowhere there's no need to delete nor transport.
						'transport'   => ! $identical_index,
						'delete'      => ! $identical_index,
					];

					$existing_value = null;
					$old_value      = null;
					$set_value      = null;

					// Test if data already exists on new entry.
					if ( isset( $transmuter['to'] ) ) {
						$existing_value = \call_user_func_array(
							$transmuter['to'],
							[
								'get:existing_value:to',
								[
									'post_id' => $post_id,
									'to_data' => $transmuter['to_data'] ?? null,
									'to'      => [ $to_table, $to_index ],
								],
								&$actions,
								&$results,
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

					if ( \is_null( $existing_value ) ) {
						if ( isset( $transmuter['from'] ) ) {
							$old_value = /*yield from*/ \call_user_func_array(
								$transmuter['from'],
								[
									'get:old_value:from',
									[
										'post_id'   => $post_id,
										'from_data' => $transmuter['from_data'] ?? null,
										'from'      => [ $from_table, $from_index ],
									],
									&$actions,
									&$results,
								]
							);
						} else {
							$old_value = $wpdb->get_var( $wpdb->prepare(
								// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_table is escaped.
								"SELECT meta_value FROM `$from_table` WHERE post_id = %d AND meta_key = %s",
								$post_id,
								$from_index
							) );
						}
						if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
					} else {
						// If new data exists, don't overwrite with old.
						$actions['transport'] = false;
						// If new data exists and index is the same, still try to transform. Otherwise, forgo.
						$actions['transform'] = $actions['transform'] && $identical_index;
					}

					$set_value = $existing_value ?? $old_value;

					if ( $actions['transform'] ) {
						$_pre_transform_value = $set_value;

						$set_value = /*yield from*/ \call_user_func_array(
							$transformer,
							[
								$set_value,
								$post_id,
								[ $from_table, $from_index ],
								[ $to_table, $to_index ],
							]
						);

						$actions['transformed'] = $_pre_transform_value !== $set_value;
					}

					// Still allow "0" and '0'. TSF will later assess its usefulness.
					if ( \in_array( $set_value, [ null, '' ], true ) ) {
						$actions['delete']      = true;
						$actions['transformed'] = false;
						$actions['transport']   = false;
					}

					if ( isset( $transmuter['to'] ) ) {
						yield from \call_user_func_array(
							$transmuter['to'],
							[
								'transmute:set_value:to',
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
							]
						);
					} else {
						// $actions and $results are passed by reference.
						/*yield from*/$this->transmute(
							$set_value,
							$post_id,
							[ $from_table, $from_index ],
							[ $to_table, $to_index ],
							$actions,
							$results
						);
					}

					$is_lastpost = $post_iterator === $total_posts;
					yield 'results' => [ $results, $actions, $post_id, $is_lastpost ];

					// This also busts cache of caching plugins. Intended: Update the post!
					\clean_post_cache( $post_id );
					$post_iterator++;
				endforeach;
		endforeach;

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
	 * @throws \Exception On database error when WP_DEBUG is enabled.
	 */
	protected function transmute( $set_value, $post_id, $transfer_from, $transfer_to, &$actions, &$results ) {
		global $wpdb;

		[ $from_table, $from_index ] = $transfer_from;
		[ $to_table, $to_index ]     = $transfer_to;

		if ( $actions['transport'] ) {
			if ( $to_table === $from_table ) {
				if ( $actions['transformed'] ) {
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
		} elseif ( $actions['transformed'] ) {
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

		if ( $actions['delete'] ) {
			$results['deleted'] = $wpdb->delete(
				$from_table,
				[
					'post_id'  => $post_id,
					'meta_key' => $from_index,
				]
			);
			if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
		}
	}
}
