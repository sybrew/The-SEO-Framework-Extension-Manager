<?php
/**
 * @package TSF_Extension_Manager/Bootstrap
 */
namespace TSF_Extension_Manager;

defined( 'TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE' ) or die;

\add_action( 'admin_notices', __NAMESPACE__ . '\\_check_external_blocking' );
/**
 * Checks whether the WP installation blocks external requests.
 * Shows notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
 *
 * @since 2.0.0
 * @access private
 */
function _check_external_blocking() {

	if ( ! \current_user_can( 'update_plugins' ) ) return;

	if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && true === WP_HTTP_BLOCK_EXTERNAL ) {

		$parsed_url = \wp_parse_url( TSF_EXTENSION_MANAGER_DL_URI );
		$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

		if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || false === stristr( WP_ACCESSIBLE_HOSTS, $host ) ) {
			$notice = \tsf_extension_manager()->convert_markdown(
				sprintf(
					/* translators: Markdown. %s = Update API URL */
					\esc_html__(
						'This website is blocking external requests, this means it will not be able to connect to The SEO Framework update services. Please add `%s` to `WP_ACCESSIBLE_HOSTS` to keep the Extension Manager plugin up-to-date and secure.',
						'the-seo-framework-extension-manager'
					),
					\esc_html( $host )
				),
				[ 'code' ]
			);
			//* Already escaped.
			\the_seo_framework()->do_dismissible_notice( $notice, 'error', true, false );
		}
	}
}

\add_filter( 'plugins_api', __NAMESPACE__ . '\\_hook_plugins_api', PHP_INT_MAX, 3 );
/**
 * Filters the plugin API to bind to The SEO Framework's own updater service.
 *
 * @since 2.0.0
 * @access private
 * @see WP Core plugins_api()
 *
 * @param false|object|array $res    The result object or array. Default false.
 * @param string             $action The type of information being requested from the Plugin Installation API.
 * @param object             $args   Plugin API arguments.
 * @return object|\WP_Error  The result object on success, \WP_Error on failure.
 */
function _hook_plugins_api( $res, $action, $args ) {

	if ( 'plugin_information' !== $action
	|| empty( $args->slug )
	|| TSF_EXTENSION_MANAGER_PLUGIN_SLUG !== $args->slug
	) return $res;

	if ( ! \wp_http_supports( [ 'ssl' ] ) ) {
		return new \WP_Error( 'plugins_api_failed',
			\__( 'This website does not support secure connections. This means "The SEO Framework - Extension Manager" can not be updated.', 'the-seo-framework-extension-manager' )
		);
	}

	// include an unmodified $wp_version
	include ABSPATH . WPINC . '/version.php';

	$url = TSF_EXTENSION_MANAGER_DL_URI . 'get/info/1.0/';

	$http_args = [
		'timeout'    => 15,
		'user-agent' => 'WordPress/' . $wp_version . '; ' . \home_url( '/' ),
		'body'       => [
			'action'  => $action,
			'request' => serialize( $args ),
		],
	];
	$request = \wp_remote_post( $url, $http_args );

	if ( \is_wp_error( $request ) ) {
		$error_message = sprintf(
			/* translators: %1$s: API url, %2$s: support URL */
			\__( 'An unexpected error occurred. Something may be wrong with %1$s or this server&#8217;s configuration. If you continue to have problems, please <a href="%2$s">contact us</a>.', 'the-seo-framework-extension-manager' ),
			\esc_url( TSF_EXTENSION_MANAGER_DL_URI ),
			'https://theseoframework.com/contact/'
		);
		$res = new WP_Error( 'plugins_api_failed',
			$error_message,
			$request->get_error_message() // $data
		);
	} else {
		$res = \maybe_unserialize( \wp_remote_retrieve_body( $request ) );
		if ( ! is_object( $res ) && ! is_array( $res ) ) {
			$res = new WP_Error( 'plugins_api_failed',
				sprintf(
					/* translators: %s: support forums URL */
					\__( 'An unexpected error occurred. Something may be wrong with TheSEOFramework.com or this server&#8217;s configuration. If you continue to have problems, please <a href="%s">contact us</a>.' ),
					'https://theseoframework.com/contact/'
				),
				\wp_remote_retrieve_body( $request )
			);
		} elseif ( is_array( $res ) ) {
			$res = (object) $res;
		}
	}

	return $res;
}

\add_action( 'upgrader_process_complete', __NAMESPACE__ . '\\_clear_update_cache' );
/**
 * Clears the updater cache after a plugin's been updated.
 * This prevents incorrect updater version storing.
 *
 * @since 2.0.0
 * @access private
 */
function _clear_update_cache() {
	\delete_site_transient( TSF_EXTENSION_MANAGER_UPDATER_CACHE );
}

\add_filter( 'pre_set_site_transient_update_plugins', __NAMESPACE__ . '\\_push_update', PHP_INT_MAX, 2 );
/**
 * Push values into the update_plugins site transient.
 * This allows for multisite network updates.
 *
 * We use pre_* because WP's object cache would otherwise prevent the second run
 * as it won't detect changes.
 *
 * @since 2.0.0
 * @access private
 * @see WP Core wp_update_plugins()
 *
 * @param mixed  $value      Site transient value.
 * @param string $transient  Transient name.
 * @return mixed $value
 */
function _push_update( $value, $transient ) {

	// Check's still booting...
	// if ( ! isset( $value->checked ) ) return $value;

	unset( $value->checked[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
	unset( $value->response[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );
	unset( $value->no_update[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] );

	$cache_timeout = MINUTE_IN_SECONDS * 5;
	$cache = \get_site_transient( TSF_EXTENSION_MANAGER_UPDATER_CACHE );

	$this_plugin = \get_plugins()[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];

	if ( false === $cache ) {
		// include an unmodified $wp_version
		include ABSPATH . WPINC . '/version.php';

		$url = TSF_EXTENSION_MANAGER_DL_URI . 'get/update/1.0/';

		$http_args = [
			'timeout'    => 4,
			'user-agent' => 'WordPress/' . $wp_version . '; ' . \home_url( '/' ),
			'body'       => [
				'plugins' => [
					TSF_EXTENSION_MANAGER_PLUGIN_BASENAME => $this_plugin,
				],
				// 'translations' => [], // maybe later.
				// 'locale' => [], // maybe later.
			],
		];

		$raw_response = \wp_remote_post( $url, $http_args );

		if ( \is_wp_error( $raw_response ) || 200 != \wp_remote_retrieve_response_code( $raw_response ) ) {
			return $value;
		}

		$response = json_decode( \wp_remote_retrieve_body( $raw_response ), true );
		foreach ( $response['plugins'] as &$plugin ) {
			$plugin = (object) $plugin;
			if ( isset( $plugin->compatibility ) ) {
				$plugin->compatibility = (object) $plugin->compatibility;
				foreach ( $plugin->compatibility as &$data ) {
					$data = (object) $data;
				}
			}
		}
		unset( $plugin, $data );
		foreach ( $response['no_update'] as &$plugin ) {
			$plugin = (object) $plugin;
		}
		unset( $plugin );

		$cache =& $response;
		\set_site_transient( TSF_EXTENSION_MANAGER_UPDATER_CACHE, $cache, $cache_timeout );
	}

	//? We're only checking this plugin. This type of merge needs expansion in a bulk-updater.
	$value->checked[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $this_plugin['Version'];
	if ( isset( $cache['no_update'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] ) ) {
		$value->no_update[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $cache['no_update'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];
	}
	if ( isset( $cache['plugins'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] ) ) {
		$value->response[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $cache['plugins'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];
	}

	return $value;
}
