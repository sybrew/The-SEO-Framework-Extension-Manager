<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE' ) or die;

use function \TSF_Extension_Manager\Transition\{
	convert_markdown,
	do_dismissible_notice,
};

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2018-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

\add_action( 'admin_notices', __NAMESPACE__ . '\\_check_external_blocking' );
/**
 * Checks whether the WP installation blocks external requests.
 * Shows notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
 *
 * If you must, you can disable this notice by implementing this snippet:
 * `remove_action( 'admin_notices', 'TSF_Extension_Manager\\_check_external_blocking' );`
 *
 * @since 2.0.0
 * @access private
 */
function _check_external_blocking() {

	if ( ! \current_user_can( 'update_plugins' ) ) return;

	if ( \defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && true === \WP_HTTP_BLOCK_EXTERNAL ) {

		$parsed_url = \wp_parse_url( \TSF_EXTENSION_MANAGER_DL_URI );
		$host       = $parsed_url['host'] ?? '';

		if ( ! \defined( 'WP_ACCESSIBLE_HOSTS' ) || false === stristr( \WP_ACCESSIBLE_HOSTS, $host ) ) {

			// We rely on TSF here but it might not be available. Still, not outputting this notice does not harm.
			if ( ! \function_exists( 'tsf' ) ) return;

			$notice = convert_markdown(
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
			do_dismissible_notice(
				$notice,
				[
					'type'   => 'error',
					'escape' => false,
				]
			);
		}
	}
}

\add_filter( 'plugins_api', __NAMESPACE__ . '\\_hook_plugins_api', \PHP_INT_MAX, 3 );
/**
 * Filters the plugin API to bind to The SEO Framework's own updater service.
 *
 * @hook plugins_api PHP_INT_MAX
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

	if ( 'plugin_information' !== $action || \TSF_EXTENSION_MANAGER_PLUGIN_SLUG !== ( $args->slug ?? '' ) )
		return $res;

	if ( ! \wp_http_supports( [ 'ssl' ] ) ) {
		return new \WP_Error(
			'plugins_api_failed',
			\__( 'This website does not support secure connections. This means "The SEO Framework - Extension Manager" can not be updated.', 'the-seo-framework-extension-manager' )
		);
	}

	// include an unmodified $wp_version
	include ABSPATH . WPINC . '/version.php';

	$url       = \TSF_EXTENSION_MANAGER_DL_URI . 'get/info/1.0/';
	$http_args = [
		'timeout'    => 15,
		'user-agent' => "WordPress/$wp_version; " . \PHP_VERSION_ID . '; ' . \home_url( '/' ),
		'body'       => [
			'action'  => $action,
			'request' => serialize( $args ), // phpcs:ignore -- Object injection is mitigated at the request server.
		],
	];

	$request = \wp_remote_post( $url, $http_args );

	if ( \is_wp_error( $request ) ) {
		$error_message = sprintf(
			/* translators: %1$s: API url, %2$s: support URL */
			\__( 'An unexpected error occurred. Something may be wrong with %1$s or this server&#8217;s configuration. If you continue to have problems, please <a href="%2$s">contact us</a>.', 'the-seo-framework-extension-manager' ),
			\esc_url( \TSF_EXTENSION_MANAGER_DL_URI ),
			'https://theseoframework.com/contact/'
		);
		$res = new \WP_Error(
			'plugins_api_failed',
			$error_message,
			$request->get_error_message() // $data
		);
	} else {
		// aka maybe_unserialize but then without class support.
		$body = \wp_remote_retrieve_body( $request );

		$res = \is_serialized( $body )
			? unserialize( trim( $body ), [ 'allowed_classes' => [ 'stdClass' ] ] ) // phpcs:ignore -- it fine.
			: $body;

		if ( \is_array( $res ) ) {
			$res = (object) $res;
		} elseif ( ! \is_object( $res ) ) {
			$res = new \WP_Error(
				'plugins_api_failed',
				sprintf(
					/* translators: %s: support forums URL */
					\__( 'An unexpected error occurred. Something may be wrong with TheSEOFramework.com or this server&#8217;s configuration. If you continue to have problems, please <a href="%s">contact us</a>.', 'the-seo-framework-extension-manager' ),
					'https://theseoframework.com/contact/'
				),
				\wp_remote_retrieve_body( $request )
			);
		}
	}

	return $res;
}

\add_action( 'upgrader_process_complete', __NAMESPACE__ . '\\_clear_update_cache' );
/**
 * Clears the updater cache after a plugin's been updated.
 * This prevents incorrect updater version storing.
 *
 * @hook upgrader_process_complete 10
 * @since 2.0.0
 * @access private
 */
function _clear_update_cache() {
	\update_site_option( \TSF_EXTENSION_MANAGER_UPDATER_CACHE, [] );
}

\add_filter( 'pre_set_site_transient_update_plugins', __NAMESPACE__ . '\\_push_update', \PHP_INT_MAX, 2 );
/**
 * Push values into the update_plugins site transient.
 * This allows for multisite network updates.
 *
 * We use pre_* because WP's object cache would otherwise prevent the second run
 * as it won't detect changes.
 *
 * @hook pre_set_site_transient_update_plugins PHP_INT_MAX
 * @since 2.0.0
 * @since 2.0.2 Added more cache, because some sites disable transients completely...
 * @since 2.4.0 Can now fetch required (and available) locale files.
 * @since 2.5.1 1. Now uses site options instead of transients. We still have far too many update-spammers.
 *              2. We may now collect a list of active extensions.
 * @access private
 * @see WP Core \wp_update_plugins()
 *
 * @param mixed $value Site transient value. Expected to be \stdClass.
 * @return mixed $value
 */
function _push_update( $value ) {

	// $value is broken by some plugin. We can't fix this. Bail.
	if ( ! \is_object( $value ) )
		return $value;

	// Clear old data from w.org update API, even if we bail early, this plugin won't be fetching a outdated w.org files.
	unset(
		$value->checked[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ],
		$value->response[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ],
		$value->no_update[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ]
	);

	$update_data = get_plugin_update_data();

	if ( ! $update_data )
		return $value;

	// The filter may be
	$value->checked      ??= [];
	$value->no_update    ??= [];
	$value->response     ??= [];
	$value->translations ??= [];

	$this_plugin = \get_plugins()[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];

	// We're only checking this plugin. This type of merge needs expansion in a bulk-updater.
	$value->checked[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $this_plugin['Version'];

	if ( isset( $update_data['no_update'][ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] ) )
		$value->no_update[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $update_data['no_update'][ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];

	// This should be an "else" of "no_update"--but our server already mitigates that.
	if ( isset( $update_data['plugins'][ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] ) )
		$value->response[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $update_data['plugins'][ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];

	if ( ! empty( $update_data['translations'] ) )
		$value->translations = array_merge( $value->translations, $update_data['translations'] );

	return $value;
}

/**
 * Gets the plugin update according to the "update_plugins_{$hostname}" filter.
 *
 * @since 2.6.4
 *
 * @return array|false {
 *     The plugin update data with the latest details. Default false.
 *
 *     @type string $id           Optional. ID of the plugin for update purposes, should be a URI
 *                                specified in the `Update URI` header field.
 *     @type string $slug         Slug of the plugin.
 *     @type string $version      The version of the plugin.
 *     @type string $url          The URL for details of the plugin.
 *     @type string $package      Optional. The update ZIP for the plugin.
 *     @type string $tested       Optional. The version of WordPress the plugin is tested against.
 *     @type string $requires_php Optional. The version of PHP which the plugin requires.
 *     @type bool   $autoupdate   Optional. Whether the plugin should automatically update.
 *     @type array  $icons        Optional. Array of plugin icons.
 *     @type array  $banners      Optional. Array of plugin banners.
 *     @type array  $banners_rtl  Optional. Array of plugin RTL banners.
 *     @type array  $translations {
 *         Optional. List of translation updates for the plugin.
 *
 *         @type string $language   The language the translation update is for.
 *         @type string $version    The version of the plugin this translation is for.
 *                                  This is not the version of the language file.
 *         @type string $updated    The update timestamp of the translation file.
 *                                  Should be a date in the `YYYY-MM-DD HH:MM:SS` format.
 *         @type string $package    The ZIP location containing the translation update.
 *         @type string $autoupdate Whether the translation should be automatically installed.
 *     }
 * }
 */
function get_plugin_update_data() {

	static $runtimecache;

	$this_plugin = \get_plugins()[ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];

	if ( isset( $runtimecache ) ) {
		$cache =& $runtimecache;
	} else {
		$cache = \get_site_option( \TSF_EXTENSION_MANAGER_UPDATER_CACHE, [] );

		if ( isset( $cache['_failure_timeout'] ) ) {
			if ( $cache['_failure_timeout'] > time() )
				return false;

			$cache = [];
		}

		if ( empty( $cache['_tsfem_delay_updater'] ) || $cache['_tsfem_delay_updater'] < time() ) {
			// include an unmodified $wp_version
			include ABSPATH . WPINC . '/version.php';

			$url = \TSF_EXTENSION_MANAGER_DL_URI . 'get/update/1.1/';

			$locales = array_values( \get_available_languages() );
			/**
			 * Filters the locales requested for plugin translations.
			 *
			 * @since WP Core 3.7.0
			 * @since WP Core 4.5.0 The default value of the `$locales` parameter changed to include all locales.
			 * @source WP Core \wp_update_plugins()
			 *
			 * @param array $locales Plugin locales. Default is all available locales of the site.
			 */
			$locales = \apply_filters( 'plugins_update_check_locales', $locales );
			$locales = array_unique( $locales );

			$plugins      = [ \TSF_EXTENSION_MANAGER_PLUGIN_BASENAME => $this_plugin ];
			$translations = \wp_get_installed_translations( 'plugins' );

			// This is set at the plugin's base PHP file plugin-header.
			$text_domain = $this_plugin['TextDomain'] ?? '';

			if ( $text_domain && isset( $translations[ $text_domain ] ) ) {
				$translations = array_intersect_key( $translations, array_flip( [ $text_domain ] ) );
			} else {
				$translations = [];
			}

			$options    = \get_option( \TSF_EXTENSION_MANAGER_SITE_OPTIONS, [] );
			$extensions = $options['active_extensions'] ?? [];

			$http_args = [
				'timeout'    => 7, // WordPress generously sets 30 seconds when doing cron to check all plugins, but we only check 1 plugin.
				'user-agent' => "WordPress/$wp_version; " . \PHP_VERSION_ID . '; ' . \home_url( '/' ), // phpcs:ignore, VariableAnalysis
				'body'       => [
					'plugins'      => json_encode( $plugins ),
					'translations' => json_encode( $translations ),
					'locales'      => json_encode( $locales ),
					'extensions'   => json_encode( $extensions ),
				],
			];

			$raw_response = \wp_remote_post( $url, $http_args );

			if (
				   \is_wp_error( $raw_response )
				|| 200 != \wp_remote_retrieve_response_code( $raw_response ) // phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison
			) {
				$_cache = [
					'_failure_timeout' => time() + ( \MINUTE_IN_SECONDS * 10 ),
				];
				\update_site_option( \TSF_EXTENSION_MANAGER_UPDATER_CACHE, $_cache );

				return false;
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

			$cache                         =& $response;
			$cache['_tsfem_delay_updater'] = time() + ( \MINUTE_IN_SECONDS * 30 );

			\update_site_option( \TSF_EXTENSION_MANAGER_UPDATER_CACHE, $cache );
		}

		$runtimecache = $cache;
	}

	return $cache;
}
