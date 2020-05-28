<?php
/**
 * @package TSF_Extension_Manager\Bootstrap
 */

namespace TSF_Extension_Manager;

defined( 'TSF_EXTENSION_MANAGER_PLUGIN_BASE_FILE' ) or die;

\add_action( 'tsfem_needs_the_seo_framework', __NAMESPACE__ . '\\_prepare_tsf_installer' );
/**
 * Prepares scripts for TSF "WP v4.6 Shiny Updates" installation.
 *
 * @since 2.2.0
 * @access private
 */
function _prepare_tsf_installer() {

	if ( \wp_doing_cron() ) return;
	if ( ! \is_admin() ) return; // This is implied, though.
	if ( ! \is_main_site() ) return;
	if ( ! \current_user_can( 'install_plugins' ) ) return;
	if ( 'update.php' === $GLOBALS['pagenow'] ) return;

	if ( ! function_exists( '\\get_plugins' ) )
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$plugins = \get_plugins();

	if ( isset( $plugins['autodescription/autodescription.php'] ) || isset( $plugins['the-seo-framework/autodescription.php'] ) ) return;

	$deps       = [
		'plugin-install',
		'updates',
	];
	$scriptid   = 'tsfinstaller';
	$scriptname = 'tsfinstaller';
	$suffix     = SCRIPT_DEBUG ? '' : '.min';

	$strings = [
		'slug' => 'autodescription',
	];

	\wp_register_script( $scriptid, TSF_EXTENSION_MANAGER_DIR_URL . "lib/js/{$scriptname}{$suffix}.js", $deps, TSF_EXTENSION_MANAGER_VERSION, true );
	\wp_localize_script( $scriptid, "{$scriptname}L10n", $strings );

	\add_action( 'admin_print_styles', __NAMESPACE__ . '\\_print_tsf_nag_installer_styles' );
	\add_action( 'admin_footer', '\\wp_print_request_filesystem_credentials_modal' );
	\add_action( 'admin_footer', '\\wp_print_admin_notice_templates' );

	\wp_enqueue_style( 'plugin-install' );
	\wp_enqueue_script( $scriptid );
	\add_thickbox();

	\add_action( 'admin_notices', __NAMESPACE__ . '\\_nag_install_tsf' );
}

/**
 * Outputs "button-small" "Shiny Updates" compatibility style.
 *
 * @since 2.2.0
 * @access private
 */
function _print_tsf_nag_installer_styles() {
	echo '<style type="text/css">#tsfem-tsf-install{margin-left:7px;margin-right:7px}#tsfem-tsf-install.updating-message:before{font-size:16px;vertical-align:top}</style>';
}

/**
 * Nags the site administrator to install TSF to continue.
 *
 * @since 2.2.0
 * @access private
 */
function _nag_install_tsf() {

	$plugin_slug = 'autodescription';
	$tsf_text    = 'The SEO Framework';

	/**
	 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/import.php#L162-L178
	 * @uses Spaghetti.
	 * @see WP Core class Plugin_Installer_Skin
	 */
	$details_url      = \add_query_arg(
		[
			'tab'       => 'plugin-information',
			'plugin'    => $plugin_slug,
			'from'      => 'plugins',
			'TB_iframe' => 'true',
			'width'     => 600,
			'height'    => 550,
		],
		\network_admin_url( 'plugin-install.php' )
	);
	$tsf_details_link = sprintf(
		'<a href="%1$s" id=tsfem-tsf-tb class="thickbox open-plugin-details-modal" aria-label="%2$s">%3$s</a>',
		\esc_url( $details_url ),
		/* translators: %s: Plugin name */
		\esc_attr( sprintf( __( 'Learn more about %s', 'the-seo-framework-extension-manager' ), $tsf_text ) ),
		\esc_html__( 'View plugin details', 'the-seo-framework-extension-manager' )
	);
	$nag = sprintf(
		/* translators: 1 = Extension Manager, 2 = The SEO Framework, 3 = View plugin details. */
		\esc_html__( '%1$s requires %2$s plugin to function. %3$s.', 'the-seo-framework-extension-manager' ),
		sprintf( '<strong>%s</strong>', 'Extension Manager' ),
		sprintf( '<strong>%s</strong>', \esc_html( $tsf_text ) ),
		$tsf_details_link
	);

	/**
	 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/import.php#L125-L138
	 * @uses Bolognese sauce.
	 * @see The closest bowl of spaghetti. Or WordPress\Administration\wp.updates/updates.js
	 * This joke was brought to you by the incomplete API of WP Shiny Updates, where
	 * WP's import.php has been directly injected into, rather than "calling" it via its API.
	 * Therefore, leaving the incompleteness undiscovered internally.
	 * @TODO Open core track ticket.
	 */
	$install_nonce_url = \wp_nonce_url(
		\add_query_arg(
			[
				'action' => 'install-plugin',
				'plugin' => $plugin_slug,
				'from'   => 'plugins',
			],
			\self_admin_url( 'update.php' )
		),
		'install-plugin_' . $plugin_slug
	);
	$install_action    = sprintf(
		'<a href="%1$s" id=tsfem-tsf-install class="install-now button button-small" data-slug="%2$s" data-name="%3$s" aria-label="%4$s">%5$s</a>',
		\esc_url( $install_nonce_url ),
		\esc_attr( $plugin_slug ),
		\esc_attr( $tsf_text ),
		/* translators: %s: The SEO Framework */
		\esc_attr( sprintf( \__( 'Install %s', 'the-seo-framework-extension-manager' ), $tsf_text ) ),
		\esc_html__( 'Install Now', 'the-seo-framework-extension-manager' )
	);

	// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- it is.
	printf(
		'<div class="notice notice-info"><p>%s</p></div>',
		\is_rtl() ? $install_action . ' ' . $nag : $nag . ' ' . $install_action
	);
	// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
}

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
		$host       = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

		if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || false === stristr( WP_ACCESSIBLE_HOSTS, $host ) ) {
			$notice = \the_seo_framework()->convert_markdown(
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

	$url       = TSF_EXTENSION_MANAGER_DL_URI . 'get/info/1.0/';
	$http_args = [
		'timeout'    => 15,
		'user-agent' => 'WordPress/' . $wp_version . '; ' . PHP_VERSION_ID . '; ' . \home_url( '/' ),
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
			\esc_url( TSF_EXTENSION_MANAGER_DL_URI ),
			'https://theseoframework.com/contact/'
		);
		$res = new WP_Error( 'plugins_api_failed',
			$error_message,
			$request->get_error_message() // $data
		);
	} else {
		$res = \maybe_unserialize( \wp_remote_retrieve_body( $request ) ); // phpcs:ignore -- No objects are sent.
		if ( ! is_object( $res ) && ! is_array( $res ) ) {
			$res = new WP_Error( 'plugins_api_failed',
				sprintf(
					/* translators: %s: support forums URL */
					\__( 'An unexpected error occurred. Something may be wrong with TheSEOFramework.com or this server&#8217;s configuration. If you continue to have problems, please <a href="%s">contact us</a>.', 'the-seo-framework-extension-manager' ),
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
 * @since 2.0.2 Added more cache, because some sites disable transients completely...
 * @since 2.4.0 Can now fetch required (and available) locale files.
 * @access private
 * @see WP Core \wp_update_plugins()
 * @staticvar $runtimecache.
 *
 * @param mixed  $value     Site transient value.
 * @param string $transient Transient name.
 * @return mixed $value
 */
function _push_update( $value, $transient ) {

	unset(
		$value->checked[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ],
		$value->response[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ],
		$value->no_update[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ]
	);

	static $runtimecache = null;

	$this_plugin = \get_plugins()[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];

	if ( isset( $runtimecache ) ) {
		$cache =& $runtimecache;
	} else {
		$cache_timeout = MINUTE_IN_SECONDS * 20;
		$cache         = \get_site_transient( TSF_EXTENSION_MANAGER_UPDATER_CACHE );

		if ( false === $cache ) {
			// include an unmodified $wp_version
			include ABSPATH . WPINC . '/version.php';

			$url = TSF_EXTENSION_MANAGER_DL_URI . 'get/update/1.1/';

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

			$plugins      = [ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME => $this_plugin ];
			$translations = \wp_get_installed_translations( 'plugins' );

			// This is set at the plugin's base PHP file plugin-header.
			$text_domain = isset( $this_plugin['TextDomain'] ) ? $this_plugin['TextDomain'] : '';

			if ( $text_domain && isset( $translations[ $text_domain ] ) ) {
				$translations = array_intersect_key( $translations, array_flip( [ $text_domain ] ) );
			} else {
				$translations = [];
			}

			$http_args = [
				'timeout'    => 7, // WordPress generously sets 30 seconds when doing cron to check all plugins, but we only check 1 plugin.
				'user-agent' => 'WordPress/' . $wp_version . '; ' . PHP_VERSION_ID . '; ' . \home_url( '/' ),
				'body'       => [
					'plugins'      => \wp_json_encode( $plugins ),
					'translations' => \wp_json_encode( $translations ),
					'locales'      => \wp_json_encode( $locales ),
				],
			];

			$raw_response = \wp_remote_post( $url, $http_args );

			if ( \is_wp_error( $raw_response )
			|| 200 != \wp_remote_retrieve_response_code( $raw_response ) // phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison
			) {
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

		$runtimecache = $cache;
	}

	//? We're only checking this plugin. This type of merge needs expansion in a bulk-updater.
	$value->checked[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $this_plugin['Version'];
	if ( isset( $cache['no_update'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] ) ) {
		// TODO Core considers changing this. @see \wp_update_plugins().
		$value->no_update[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $cache['no_update'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];
	}
	if ( isset( $cache['plugins'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] ) ) {
		$value->response[ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ] = $cache['plugins'][ TSF_EXTENSION_MANAGER_PLUGIN_BASENAME ];
	}
	if ( ! empty( $cache['translations'] ) ) {
		if ( isset( $value->translations ) ) {
			$value->translations = array_merge( $value->translations, $cache['translations'] );
		} else {
			// Somehow, the API server sent back an empty response...? This shouldn't be possible, maybe a bug at api.w.org?
			$value->translations = $cache['translations'];
		}
	}

	return $value;
}
