<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Logger
 */

namespace TSF_Extension_Manager\Extension\Transport\Logger;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Class TSF_Extension_Manager\Extension\Transport\Logger\Server
 *
 * Holds extension logging functionality.
 * Instantly sets up server when called!
 *
 * @since 1.0.0
 * @access private
 * @errorval 106xxxx
 * @final
 */
final class Store {

	/**
	 * @since 1.0.0
	 * @var string $id The unique store identifier.
	 */
	public $id;
	// public immutable $id; // :(

	/**
	 * @since 1.0.0
	 * @var array $store The current store. Shared across instances.
	 */
	private static $store = [];

	/**
	 * @since 1.0.0
	 * @param string $id The unique store identifier.
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Stores data.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data The data to store
	 */
	public function store( $data ) {
		static::$store[ $this->id ][ microtime() ] = $data;
	}

	/**
	 * Retrieves store data.
	 *
	 * @since 1.0.0
	 *
	 * @return array The current store.
	 */
	public function get_store() {
		return static::$store[ $this->id ] ?? [];
	}

	/**
	 * Clears current store and returns what's been cleared.
	 *
	 * @since 1.0.0
	 *
	 * @return array The current store.
	 */
	public function get_flush_store() {
		$store = $this->get_store();
		$this->clear_store();
		return $store;
	}

	/**
	 * Clears store.
	 */
	public function clear_store() {
		unset( static::$store[ $this->id ] );
	}

	// public function store( $data ) {
	// 	$store = \get_option( TSFEM_E_TRANSPORT_LOGSERVER_STORE, [] ) ?: [];
	// 	$store[ $this->id ][ microtime() ] = $data;
	// 	\update_option( TSFEM_E_TRANSPORT_LOGSERVER_STORE, $store );
	// }
	// public function get_store( $after ) {
	// 	$store = \get_option( TSFEM_E_TRANSPORT_LOGSERVER_STORE ) ?? [];
	// 	if ( isset( $store[ $this->id ] ) ) {
	// 		// Clear old data.
	// 		foreach ( $store[ $this->id ] as $microtime => $values ) {
	// 			if ( $microtime < $after )
	// 				unset( $data[ $this->id ][ $microtime ] );
	// 		}
	// 		\update_option( TSFEM_E_TRANSPORT_LOGSERVER_STORE, $store );
	// 	}
	// 	return $store[ $this->id ] ?? null;
	// }
	// public function clear_store() {
	// 	$store = \get_option( TSFEM_E_TRANSPORT_LOGSERVER_STORE ) ?? [];
	// 	unset( $store[ $this->id ] );
	// 	\update_option( TSFEM_E_TRANSPORT_LOGSERVER_STORE, $store );
	// }
}
