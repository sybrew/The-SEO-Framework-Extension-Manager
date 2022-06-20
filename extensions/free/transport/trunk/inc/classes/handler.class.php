<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Ajax
 */

namespace TSF_Extension_Manager\Extension\Transport;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

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

// phpcs:disable, WordPress.Security.NonceVerification -- This file expects that to have been done.

/**
 * Class TSF_Extension_Manager\Extension\Transport\Handler
 *
 * Handles callbacks for Transport.
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Handler {
	use \TSF_Extension_Manager\Error;

	/**
	 * @since 1.0.0
	 * @var string The transporter lock key (as stored in the database).
	 */
	const LOCK_SETTING = 'tsfem_e_transporter.lock';

	/**
	 * @since 1.0.0
	 * @var array A map of log IDs.
	 */
	const LOG_ID = [
		'import' => 'import',
	];

	/**
	 * Stops importing request/stream.
	 *
	 * @since 1.0.0
	 *
	 * @TODO add better comment... this is private scope, so eh.
	 * @param array $args Data containing reason et al.
	 */
	private function _halt_server( $args ) {
		if ( $args['server']->is_streaming() ) {
			$args['server']->send( $args['poll_data'], $args['logger_uid'], $args['event'] );
			$args['server']->flush();
			exit;
		} else {
			\tsf_extension_manager()->send_json( $args['poll_data'], $args['type'] );
		}
	}

	/**
	 * Handles Transport AJAX POST requests.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $supported_importers A map of supported importers.
	 * @return void If nonce failed.
	 */
	public function _import( $supported_importers ) {

		server : {
			$logger_uid = uniqid( static::LOG_ID['import'], true );

			$store  = new Logger\Store( $logger_uid );
			$server = new Logger\Server;

			$streaming = $server->start();
		}

		verify: {
			parse_str( $_REQUEST['data'] ?? '', $import_settings );

			$import_settings = $import_settings['tsfem-e-transport-importer'] ?? null;

			if ( ! $import_settings )
				$this->_halt_server( [
					'server'     => $server,
					'poll_data'  => [ 'results' => $this->get_ajax_notice( false, 1060200 ) ],
					'logger_uid' => $logger_uid,
					'event'      => 'tsfem-e-transport-failure',
					'type'       => 'failure',
				] );

			if ( ! isset( $supported_importers[ $import_settings['choosePlugin'] ?? '' ] ) )
				$this->_halt_server( [
					'server'     => $server,
					'poll_data'  => [ 'results' => $this->get_ajax_notice( false, 1060201 ) ],
					'logger_uid' => $logger_uid,
					'event'      => 'tsfem-e-transport-failure',
					'type'       => 'failure',
				] );
		}

		prepare : {
			$timeout = 5 * MINUTE_IN_SECONDS;

			if ( ! $this->lock_transport( $timeout ) )
				$this->_halt_server( [
					'server'     => $server,
					'poll_data'  => [ 'results' => $this->get_ajax_notice( false, 1060202 ) ],
					'logger_uid' => $logger_uid,
					'event'      => 'tsfem-e-transport-locked',
					'type'       => 'failure',
				] );

			// Register this AFTER the lock is set. Otherwise, it may clear the lock in another thread.
			register_shutdown_function( [ $this, 'release_transport_lock' ] );

			\wp_raise_memory_limit( 'tsfem_e_transport_import' );

			$ini_max_execution_time = (int) ini_get( 'max_execution_time' );

			if ( 0 !== $ini_max_execution_time )
				set_time_limit( max( $ini_max_execution_time, $timeout ) );

			$time_limit = ini_get( 'max_input_time' );
			$time_limit = $time_limit < 1 ? ini_get( 'max_execution_time' ) : $time_limit;
			$time_limit = $time_limit < 1 ? $timeout : $time_limit;

			// Add current time to start rolling, 5 seconds penalty for startup/shutdown time (allows graceful shutdown).
			$time_limit += time() - 5;
		}

		// TODO gauge memory usage and timeouts? -> We should!
		// TODO start timer + memory usage
		// TODO keep track of timer (timeout) + memory usage and kill if exceed.
		try {
			if ( \in_array( 'postmeta', $import_settings['selectType'], true ) ) :
				$_class = __NAMESPACE__ . "\\Importers\\PostMeta\\{$import_settings['choosePlugin']}";

				foreach ( ( new $_class )->import() as $handle => $data ) :
					switch ( $handle ) :
						case 'currentPostId':
							[ $post_id, $total_posts, $post_iterator ] = $data;
							$streaming and $store->store(
								sprintf(
									/* translators: 1 = post number, 2 = totalposts, 3 = post ID */
									\esc_html__( 'Importing post %1$d of %2$d. (ID: %3$d)', 'the-seo-framework-extension-manager' ),
									$post_iterator,
									$total_posts,
									$post_id
								)
							);
							break;
						case 'results':
							[ $results, $actions, $post_id, $is_lastpost ] = $data;
							if ( empty( $results['updated'] ) ) {
								if ( $actions['transport'] ) {
									$streaming and $store->store(
										sprintf(
											/* translators: %d = post ID */
											\esc_html__( 'Post ID %d failed import.', 'the-seo-framework-extension-manager' ),
											$post_id
										)
									);
								} else {
									$streaming and $store->store(
										sprintf(
											/* translators: %d = post ID */
											\esc_html__( 'Skipped import for Post ID %d.', 'the-seo-framework-extension-manager' ),
											$post_id
										)
									);
								}
							} else {
								if ( $actions['transformed'] ) {
									$streaming and $store->store(
										sprintf(
											/* translators: %d = post ID */
											\esc_html__( 'Post ID %d transformed succesfully.', 'the-seo-framework-extension-manager' ),
											$post_id
										)
									);
								} else {
									$streaming and $store->store(
										sprintf(
											/* translators: %d = post ID */
											\esc_html__( 'Post ID %d imported succesfully.', 'the-seo-framework-extension-manager' ),
											$post_id,
										)
									);
								}
							}
							if ( $is_lastpost ) {
								$store->store( '===============' );
							} else {
								$store->store( '---------------' );
							}
							break;

						// These below are hit less often, thus later in the switch.
						case 'nowConverting':
							[ $from_index, $to_index, $from_database, $to_database ] = $data;
							$streaming and $store->store(
								vsprintf(
									/* translators: 1,2 = database location. */
									\esc_html__( 'Starting import from "%1$s" to "%2$s".', 'the-seo-framework-extension-manager' ),
									[
										\esc_html( $from_index ),
										\esc_html( $to_index ),
									]
								)
							);
							break;
						case 'foundPosts':
							[ $total_posts, $post_ids ] = $data;
							$streaming and $store->store(
								sprintf(
									\esc_html(
										/* translators: %d = number of posts found. */
										\_n(
											'Found %d post to import.',
											'Found %d posts to import.',
											$total_posts,
											'the-seo-framework-extension-manager'
										)
									),
									$total_posts
								)
							);
							if ( $total_posts ) {
								$store->store( '= = = = = = = =' );
							} else {
								$store->store( '===============' );
							}
							break;
						default:
							break;
					endswitch;

					// TODO FIXME: This needs to output after every post, not yield.
					if ( $time_limit < time() ) {
						$this->release_transport_lock();
						$this->_halt_server( [
							'server'     => $server,
							'poll_data'  => [
								'results' => $this->get_ajax_notice( false, 1060203 ),
								'logMsg'  => $streaming
									? \esc_html__( 'Transporting time limit reached. Automatically restarting (total numbers might decrease)&hellip;', 'the-seo-framework-extension-manager' )
									: \esc_html__( 'Transporting time limit reached. Please try again to resume.', 'the-seo-framework-extension-manager' ),
							],
							'logger_uid' => $logger_uid,
							'event'      => 'tsfem-e-transport-timeout',
							'type'       => 'failure',
						] );
					}
					$streaming = $server->poll( $store );
				endforeach;
			endif;
			// if ( \in_array( 'termmeta', $import_settings['selectType'], true ) ) {
			// 	$_class = __NAMESPACE__ . "\\Importers\\TermMeta\\{$import_settings['choosePlugin']}";
			// 	$importer = new $_class;
			// }
		} catch ( \Exception $e ) {

			$server->poll( $store );
			$this->release_transport_lock();

			$this->_halt_server( [
				'server'     => $server,
				'poll_data'  => [
					'results' => $this->get_ajax_notice( false, 1060204 ),
					'logMsg'  =>
						sprintf(
							( $streaming
								/* translators: %s = Unknown error reason */
								? \esc_html__( 'Server stopped execution. Reason: %s. Automatically restarting (total numbers might decrease)&hellip;', 'the-seo-framework-extension-manager' )
								/* translators: %s = Unknown error reason */
								: \esc_html__( 'Server stopped execution: Reason: %s. Please try again to resume.', 'the-seo-framework-extension-manager' )
							),
							$e->getMessage() ?: 'undefined'
						)
				],
				'logger_uid' => $logger_uid,
				'event'      => 'tsfem-e-transport-crash',
				'type'       => 'failure',
			] );
		}

		$server->poll( $store );
		$this->release_transport_lock();

		$this->_halt_server( [
			'server'     => $server,
			'poll_data'  => [
				'results' => $this->get_ajax_notice( true, 1060205 ),
				'logMsg'  => \esc_html__( 'Completed transport.', 'the-seo-framework-extension-manager' ),
			],
			'logger_uid' => $logger_uid,
			'event'      => 'tsfem-e-transport-done',
			'type'       => 'success',
		] );
		exit;
	}

	/**
	 * Locks the transporter.
	 *
	 * @since 1.0.0
	 *
	 * @param int $release_timeout The time (in seconds) a lock should regarded as valid.
	 * @return bool False if already locked, true if new lock is placed.
	 */
	protected function lock_transport( $release_timeout = 60 ) {
		global $wpdb;

		$lock_result = $wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */",
				static::LOCK_SETTING,
				time()
			)
		);

		if ( ! $lock_result ) {
			$lock_result = \get_option( static::LOCK_SETTING );

			// If a lock couldn't be created, and there isn't a lock, bail.
			if ( ! $lock_result )
				return false;

			// Check to see if the lock is still valid. If it is, bail.
			if ( $lock_result > ( time() - $release_timeout ) )
				return false;

			// There must exist an expired lock, clear it...
			$this->release_transport_lock();

			// ...and re-gain it.
			return $this->lock_transport( $release_timeout );
		}

		// Update the lock, as by this point we've definitely got a lock, just need to fire the actions.
		\update_option( static::LOCK_SETTING, time() );

		return true;
	}

	/**
	 * Releases transporter lock set in lock_transport().
	 *
	 * @since 1.0.0
	 */
	public function release_transport_lock() {
		\delete_option( static::LOCK_SETTING );
	}
}
