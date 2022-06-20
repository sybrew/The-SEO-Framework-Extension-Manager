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
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Server {
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * @since 1.0.0
	 * @var bool Whether streaming is supported.
	 */
	public static $supports_stream = false;

	/**
	 * @since 1.0.0
	 * @var bool Whether streaming is started.
	 */
	public static $streaming = false;

	/**
	 * @since 1.0.0
	 * @var int The last time padding occurred in unix time.
	 */
	public static $lastpadstamp = 0;

	/**
	 * @since 1.0.0
	 * @var int The padding interval in seconds.
	 */
	public static $padinterval = 1;

	/**
	 * @since 1.0.0
	 * @var int The output buffer size since last padding.
	 */
	public static $buffersize = 0;

	/**
	 * @since 1.0.0
	 * @var int The chunk size in bytes.
	 */
	public static $chunksize = 4096;

	/**
	 * @since 1.0.0
	 * @var int The maximum chunk size acceptable, we don't want chonks.
	 */
	// public static $maxchunksize = 4096;

	/**
	 * Constructor, tests if stream is supported.
	 *
	 * @source <https://github.com/hoaproject/Eventsource/blob/master/Server.php>
	 * @since 1.0.0
	 */
	private function construct() {

		foreach (
			preg_split( '/\s*,\s*/', $this->get_headers()['accept'] ?? '' )
			as $mime
		) {
			if ( 0 !== preg_match( '/^(\*\/\*|text\/event-stream;?)/', $mime ) ) {
				static::$supports_stream = true;
				break;
			}
		}

		// Debug: var_dump()
		// static::$supports_stream = false;
	}

	/**
	 * Whether we're streaming
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if streaming, false otherwise.
	 */
	public function is_streaming() {
		return static::$supports_stream && static::$streaming;
	}

	/**
	 * Initializes stream by setting up headers and starts output buffer.
	 *
	 * @since 1.0.0
	 *
	 * @param int $interval The polling interval in seconds.
	 * @return bool false on failure, true otherwise.
	 */
	public function start( $interval = 1000 ) {

		if ( ! static::$supports_stream ) return false;

		\tsf()->clean_response_header();

		// Disable. With it enabled it would otherwise cause double-output.
		ob_implicit_flush( false );

		foreach ( [
			'Content-Type'      => 'text/event-stream',
			'Cache-Control'     => 'no-store, no-cache, no-transform', // Discourages all caching.
			'X-Accel-Buffering' => 'no', // Required for Comet/HTTPStreaming on NGINX.
			'Vary'              => '*',
		] as $header => $value )
			header( "$header: $value" );

		static::$streaming = true;

		ob_start();

		// phpcs:ignore, WordPress.Security.EscapeOutput -- unsigned int.
		printf( "retry: %u\n\n", $interval );

		static::$lastpadstamp = time();

		return $this->flush();
	}

	/**
	 * Sends stream from store in polled chunks.
	 * Cleans everything sent before streaming.
	 *
	 * @since 1.0.0
	 *
	 * @param Store $store    The store to poll.
	 * @return bool false on failure, true otherwise.
	 */
	public function poll( $store ) {

		if ( ! static::$supports_stream ) return false;
		if ( connection_aborted() ) return false;

		while ( ob_get_length() ) ob_end_clean();

		foreach ( $store->get_flush_store() as $data )
			$this->send( $data, $store->id );

		return $this->flush();
	}

	/**
	 * Flushes buffer.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $forcepad Whether to force padding.
	 * @return bool true on success, false on failure.
	 */
	public function flush( $forcepad = false ) {

		if ( ! static::$supports_stream ) return false;
		if ( connection_aborted() ) return false;

		static::$buffersize += ob_get_length();

		if ( static::$buffersize ) {
			if ( static::$buffersize > static::$chunksize ) {
				if ( $forcepad ) {
					$this->padchunk();
					static::$buffersize = 0;
				} else {
					static::$buffersize = static::$buffersize % static::$chunksize;
				}
				static::$lastpadstamp = time();
			} elseif ( ( static::$lastpadstamp + static::$padinterval ) < time() ) {
				$this->padchunk();
				static::$buffersize   = 0;
				static::$lastpadstamp = time();
			}
		}

		ob_flush();
		flush();

		return true;
	}

	/**
	 * Sends stream.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $data  The data to send. Expected to be escaped.
	 *                            Strings will be converted to array at index 'content'.
	 * @param string       $id    The sender unique ID.
	 * @param string       $event The event to send, which can read via a listener.
	 */
	public function send( $data, $id, $event = 'tsfem-e-transport-log' ) {

		if ( ! static::$supports_stream ) return false;

		if ( \is_string( $data ) )
			$data = [ 'content' => $data ];

		printf( "event: %s\nid: %s\n", \esc_html( $event ), \esc_html( $id ) );
		echo 'data: ', json_encode( $data, JSON_FORCE_OBJECT ), "\n\n";
	}

	/**
	 * Pads the response chunk buffer.
	 *
	 * @since 1.0.0
	 */
	private function padchunk() {

		$length = static::$chunksize - ( static::$buffersize % static::$chunksize );

		if ( $length <= 2 ) {
			// phpcs:ignore, WordPress.Security.EscapeOutput -- it's just newline..., man, chill.
			echo str_repeat( "\n", $length );
		} else {
			// phpcs:ignore, WordPress.Security.EscapeOutput -- it's just null..., man, chill.
			echo ':' . str_repeat( "\x00", $length - 3 ) . "\n\n";
		}
	}

	/**
	 * Returns a normalized list of headers.
	 *
	 * @since 1.0.0
	 * @source <https://github.com/hoaproject/Http/blob/master/Source/Runtime.php>
	 *
	 * @return array Headers.
	 */
	private function get_headers() {

		$headers = [];

		if ( \function_exists( 'apache_request_headers' ) ) {
			foreach ( \apache_request_headers() as $header => $value )
				$headers[ strtolower( $header ) ] = $value;
		} else {
			if ( isset( $_SERVER['CONTENT_TYPE'] ) )
				$headers['content-type'] = $_SERVER['CONTENT_TYPE'];

			if ( isset( $_SERVER['CONTENT_LENGTH'] ) )
				$headers['content-length'] = $_SERVER['CONTENT_LENGTH'];

			foreach ( $_SERVER as $key => $value ) {
				if ( 'HTTP_' === substr( $key, 0, 5 ) ) {
					$headers[ strtolower( str_replace( '_', '-', substr( $key, 5 ) ) ) ]
						= $value;
				}
			}
		}

		return $headers;
	}
}
