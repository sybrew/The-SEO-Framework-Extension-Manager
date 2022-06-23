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
	 *         (string)    'from'          The database index key to take data from,
	 *         (string)    'to'            The database index to set data to,
	 *         (?callable) 'transformer'   The data transformer, if any.
	 *         (?string)   'from_database' The database to take the data from, defaults to $wpdb->postmeta
	 *         (?string)   'to_database'   The database to set the data to, defaults to $wpdb->postmeta
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
		// var_dump() dummy wpdb.
		// $wpdb = new class {
		// 	public $postmeta   = '';
		// 	public $last_error = '';
		// 	function __construct() {
		// 		$this->postmeta = $GLOBALS['wpdb']->postmeta;
		// 	}
		// 	public function update() {
		// 		usleep( random_int( 2900, 5800 ) );
		// 		// usleep( random_int( 290000, 580000 ) );
		// 		return true;
		// 	}
		// 	public function insert() {
		// 		usleep( random_int( 2500, 5000 ) );
		// 		// usleep( random_int( 250000, 500000 ) );
		// 		return true;
		// 	}
		// 	public function get_var(...$args) { return $GLOBALS['wpdb']->get_var(...$args); }
		// 	public function get_col(...$args) { return $GLOBALS['wpdb']->get_col(...$args); }
		// 	public function prepare(...$args) {return $GLOBALS['wpdb']->prepare(...$args);}
		// };

		// Assume that we don't need to keep track of how much data is transported?
		// Assume we do not need to transport in batches? -> Tackle when we need to? -> Tell user to try again, and again...?
		// Let analyser find that "hey, are you sure you want to transport _so_much_data_?"
		// Test if large_network()?

		$_globals_postmeta = $wpdb->postmeta;

		foreach ( $this->conversion_sets as $conversion_set ) :
			[ $from_index, $to_index, $transformer, $from_database, $to_database ] = array_pad( $conversion_set, 5, null );

			// Sanity is a virtue.
			$from_database = \esc_sql( $from_database ) ?: $_globals_postmeta;
			$to_database   = \esc_sql( $to_database ) ?: $_globals_postmeta;

			yield 'nowConverting' => [ $from_index, $to_index, $from_database, $to_database ];

			$post_ids = $wpdb->get_col( $wpdb->prepare(
				// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_database is escaped.
				"SELECT post_id FROM `$from_database` WHERE meta_key = %s",
				$from_index
			) ) ?: [];

			if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

			// 300,000 posts. var_dump() debug
			// $post_ids = array_fill( \count( $post_ids ), 50000, $post_ids[ array_key_first( $post_ids ) ] );

			$total_posts = \count( $post_ids );
			yield 'foundPosts' => [ $total_posts, $post_ids ];

			$post_iterator = 1;

			foreach ( $post_ids as $post_id ) :
				// Clear query cache every 25 queries when Database debugging is enabled (e.g. Query Monitor)
				if ( ! ( $post_iterator % 25 ) )
					$wpdb->queries = [];

				$results = [];

				yield 'currentPostId' => [ $post_id, $total_posts, $post_iterator++ ];

				$identical_index = $from_index === $to_index && $from_database === $to_database;

				$actions = [
					'transformed' => false,
					'transform'   => (bool) $transformer,
					// If the data goes nowhere there's no need to delete nor transport.
					'transport'   => ! $identical_index,
					'delete'      => ! $identical_index,
				];

				$old_value = null;
				$new_value = $wpdb->get_var( $wpdb->prepare(
					// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $to_database is escaped.
					"SELECT meta_value FROM `$to_database` WHERE post_id = %d AND meta_key = %s",
					$post_id,
					$to_index
				) );
				if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );

				if ( ! \is_null( $new_value ) ) {
					// If new data exists, don't overwrite with old.
					$actions['transport'] = false;
					// If new data exists and index is the same, still try to transform. Otherwise, forgo.
					$actions['transform'] = $actions['transform'] && $identical_index;
				} else {
					$old_value = $wpdb->get_var( $wpdb->prepare(
						// phpcs:ignore, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $from_database is escaped.
						"SELECT meta_value FROM `$from_database` WHERE post_id = %d AND meta_key = %s",
						$post_id,
						$from_index
					) );
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				}

				if ( $actions['transform'] ) {
					$old_value = $new_value ?? $old_value;
					$new_value = \call_user_func_array( $transformer, [ $from_index, $from_database, $old_value ] );

					$actions['transformed'] = $old_value !== $new_value;
				}

				if ( $actions['transport'] ) {
					if ( $to_database === $from_database ) {
						if ( $actions['transformed'] ) {
							$results['updated'] = $wpdb->update(
								$to_database,
								[
									'meta_key'   => $to_index,
									'meta_value' => $new_value,
								],
								[
									'post_id'  => $post_id,
									'meta_key' => $from_index,
								]
							);
							if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
						} else {
							$results['updated'] = $wpdb->update(
								$to_database,
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
							$to_database,
							[
								'post_id'    => $post_id,
								'meta_key'   => $to_index,
								'meta_value' => $new_value,
							]
						);
						if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
					}
				} elseif ( $actions['transformed'] ) {
					$results['updated'] = $wpdb->update(
						$to_database,
						[ 'meta_value' => $new_value ],
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
						$from_database,
						[
							'post_id'  => $post_id,
							'meta_key' => $from_index,
						]
					);
					if ( WP_DEBUG && $wpdb->last_error ) throw new \Exception( $wpdb->last_error );
				}

				$is_lastpost = $post_iterator - 1 === $total_posts;
				yield 'results' => [ $results, $actions, $post_id, $is_lastpost ];

				// This also busts cache of caching plugins. Intended: Update the post!
				\clean_post_cache( $post_id );
			endforeach;
		endforeach;

		return true;
	}
}
