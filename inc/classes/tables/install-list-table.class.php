<?php
/**
 * Class used to implement displaying extensions to install in a list table.
 * Copied from WordPress core class WP_{Plugin_Install_}List_Table, and adjusted accordingly.
 *
 * @since 1.0.0
 * @access private
 */
final class TSF_Extension_Manager_Install_List_Table {

	/**
	 * The current list of items.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $items;

	/**
	 * Various information about the current table.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $_args;

	/**
	 * Various information needed for displaying the pagination.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $_pagination_args = array();

	/**
	 * Determines in which order the items are listed.
	 *
	 * @since 1.0.0
	 *
	 * @var string Accepts ASC & DESC
	 */
	public $order = 'ASC';

	/**
	 * Determines the order key.
	 *
	 * @since 1.0.0
	 *
	 * @var string|null
	 */
	public $orderby = null;

	/**
	 * Holds the list groups.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $groups = array();

	/**
	 * The current screen.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	protected $screen;
	/**
	 * The view switcher modes.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $modes = array();

	/**
	 * Holds errors.
	 *
	 * @since 1.0.0
	 *
	 * @var array.
	 */
	private $error;

	/**
	 * Constructor.
	 *
	 * The child class should call this constructor from its own constructor to override
	 * the default $args.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array|string $args {
	 * 		Array or string of arguments.
	 *
	 * 		@type bool   $ajax 		Whether the list table supports AJAX. This includes loading
	 * 								and sorting data, for example. If true, the class will call
	 * 								the _js_vars() method in the footer to provide variables
	 * 								to any scripts handling AJAX events. Default false.
	 * 		@type string $screen	String containing the hook name used to determine the current
	 * 								screen. If left null, the current screen will be automatically set.
	 * 								Default null.
	 * 		@type bool network		Whether the plugin is in network mode. Default false.
	 * 		@type array extensions	Array containing all plugin assets. Default empty.
	 * 		@type array auth		Array containing all authentication information. Default empty.
	 * }
	 */
	public function __construct( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'ajax' => false,
			'screen' => null,
			'network' => false,
			'extensions' => array(),
			'auth' => array(),
		) );

		$this->screen = convert_to_screen( $args['screen'] );
		$this->_args = $args;

		if ( $args['ajax'] ) {
			wp_enqueue_script( 'list-table' );
			add_action( 'admin_footer', array( $this, '_js_vars' ) );
		}

		if ( empty( $this->modes ) ) {
			$this->modes = array(
				'list'    => esc_html__( 'List View', 'the-seo-framework-extension-manager' ),
				'excerpt' => esc_html__( 'Excerpt View', 'the-seo-framework-extension-manager' )
			);
		}

	}

	/**
	 * Determines if the AJAX request can be run by capability.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function can_do_ajax() {
		return can_do_tsf_extension_manager_settings();
	}

	/**
	 * Returns a list of slugs of installed plugins, if known.
	 *
	 * Uses the transient data from the updates API to determine the slugs of
	 * known installed plugins. This might be better elsewhere, perhaps even
	 * within get_plugins().
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_installed_plugin_slugs() {

		$slugs = array();

		$plugin_info = get_site_transient( 'tsf-extension-manager-installed-extensions' );

		if ( isset( $plugin_info->no_update ) ) {
			foreach ( $plugin_info->no_update as $plugin ) {
				$slugs[] = $plugin->slug;
			}
		}

		if ( isset( $plugin_info->response ) ) {
			foreach ( $plugin_info->response as $plugin ) {
				$slugs[] = $plugin->slug;
			}
		}

		return $slugs;
	}

	/**
	 * Prepares list items.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items( $type = '', $items ) {

		$args = array(
			'page' => 1,
			'per_page' => 0,
			'fields' => array(
				'last_updated' => true,
				'icons' => true,
				'active_installs' => true,
				'group' => true
			),
			// Send the locale and installed plugin slugs to the API so it can provide context-sensitive results.
			'locale' => get_locale(),
			'installed_plugins' => $this->get_installed_plugin_slugs(),
			'type' => $type,
		);

		$this->orderby = 'group';

		switch ( $type ) {
			case 'featured':
			case 'network':
			case 'new':
			case 'beta':
			case 'recommended':
			break;
			default:
				$args = false;
				$this->orderby = '';
				break;
		}

		if ( ! $args )
			return;

		include( TSF_EXTENSION_MANAGER_DIR_PATH_FUNCTION . 'plugins-api.php' );

		$api = tsf_extension_manager_filter_extensions( 'query_plugins', $args );

		if ( is_wp_error( $api ) ) {
			$this->error = $api;
			return;
		}

		$this->items = $api->plugins;
		if ( $this->orderby ) {
			uasort( $this->items, array( $this, 'order_callback' ) );
		}

		if ( isset( $api->info['groups'] ) ) {
			$this->groups = $api->info['groups'];
		}

	}

	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 1.0.0
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Outputs no-items message.
	 * @since 1.0.0
	 */
	public function no_items() {
		if ( isset( $this->error ) ) {
			$message = $this->error->get_error_message() . '<p class="hide-if-no-js"><a href="#" class="button" onclick="document.location.reload(); return false;">' . __( 'Try again' ) . '</a></p>';
		} else {
			$message = __( 'No plugins match your request.' );
		}
		echo '<div class="no-plugin-results">' . $message . '</div>';
	}

	/**
	 * Returns the plugin list order.
	 *
	 * @since 1.0.0
	 *
	 * @param object $plugin_a
	 * @param object $plugin_b
	 * @return int
	 */
	private function order_callback( $plugin_a, $plugin_b ) {
		$orderby = $this->orderby;
		if ( ! isset( $plugin_a->$orderby, $plugin_b->$orderby ) ) {
			return 0;
		}
		$a = $plugin_a->$orderby;
		$b = $plugin_b->$orderby;
		if ( $a == $b ) {
			return 0;
		}
		if ( 'DESC' === $this->order ) {
			return ( $a < $b ) ? 1 : -1;
		} else {
			return ( $a < $b ) ? -1 : 1;
		}
	}

	/**
	 * @global string $wp_version
	 */
	public function display_rows() {

		$plugins_allowedtags = array(
			'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ),
			'abbr' => array( 'title' => array() ), 'acronym' => array( 'title' => array() ),
			'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
			'ul' => array(), 'ol' => array(), 'li' => array(), 'p' => array(), 'br' => array()
		);

		$plugins_group_titles = array(
			'Performance' => _x( 'Performance', 'Plugin installer group title' ),
			'Social'      => _x( 'Social',      'Plugin installer group title' ),
			'Tools'       => _x( 'Tools',       'Plugin installer group title' ),
		);
		$group = null;

		foreach ( (array) $this->items as $plugin ) {

			if ( is_object( $plugin ) ) {
				$plugin = (array) $plugin;
			}

			// Display the group heading if there is one
			if ( isset( $plugin['group'] ) && $plugin['group'] != $group ) {
				if ( isset( $this->groups[ $plugin['group'] ] ) ) {
					$group_name = $this->groups[ $plugin['group'] ];
					if ( isset( $plugins_group_titles[ $group_name ] ) ) {
						$group_name = $plugins_group_titles[ $group_name ];
					}
				} else {
					$group_name = $plugin['group'];
				}
				// Starting a new group, close off the divs of the last one
				if ( ! empty( $group ) ) {
					echo '</div></div>';
				}
				echo '<div class="plugin-group"><h3>' . esc_html( $group_name ) . '</h3>';
				// needs an extra wrapping div for nth-child selectors to work
				echo '<div class="plugin-items">';
				$group = $plugin['group'];
			}

			$title = wp_kses( $plugin['name'], $plugins_allowedtags );
			// Remove any HTML from the description.
			$description = strip_tags( $plugin['short_description'] );
			$version = wp_kses( $plugin['version'], $plugins_allowedtags );
			$name = strip_tags( $title . ' ' . $version );
			$author = wp_kses( $plugin['author'], $plugins_allowedtags );

			if ( ! empty( $author ) )
				$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';

			$action_links = array();

			if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {

				$status = install_plugin_install_status( $plugin );

				switch ( $status['status'] ) {
					case 'install':
						if ( $status['url'] ) {
							/* translators: 1: Plugin name and version. */
							$action_links[] = '<a class="install-now button" data-slug="' . esc_attr( $plugin['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Install %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Install Now' ) . '</a>';
						}
						break;
					case 'update_available':
						if ( $status['url'] ) {
							/* translators: 1: Plugin name and version */
							$action_links[] = '<a class="update-now button aria-button-if-js" data-plugin="' . esc_attr( $status['file'] ) . '" data-slug="' . esc_attr( $plugin['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Update Now' ) . '</a>';
						}
						break;
					case 'latest_installed':
					case 'newer_installed':
						if ( is_plugin_active( $status['file'] ) ) {
							$action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . _x( 'Active', 'plugin' ) . '</button>';
						} elseif ( current_user_can( 'activate_plugins' ) ) {
							$button_text  = __( 'Activate' );
							$activate_url = add_query_arg( array(
								'_wpnonce'    => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
								'action'      => 'activate',
								'plugin'      => $status['file'],
							), network_admin_url( 'plugins.php' ) );
							if ( is_network_admin() ) {
								$button_text  = __( 'Network Activate' );
								$activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
							}
							$action_links[] = sprintf(
								'<a href="%1$s" class="button activate-now button-secondary" aria-label="%2$s">%3$s</a>',
								esc_url( $activate_url ),
								/* translators: %s: Plugin name */
								esc_attr( sprintf( __( 'Activate %s' ), $plugin['name'] ) ),
								$button_text
							);
						} else {
							$action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . _x( 'Installed', 'plugin' ) . '</button>';
						}
						break;
				}
			}

			$details_link = self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] . '&amp;TB_iframe=true&amp;width=600&amp;height=550' );

			/* translators: 1: Plugin name and version. */
			$action_links[] = '<a href="' . esc_url( $details_link ) . '" class="thickbox open-plugin-details-modal" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';

			if ( ! empty( $plugin['icons']['svg'] ) ) {
				$plugin_icon_url = $plugin['icons']['svg'];
			} elseif ( ! empty( $plugin['icons']['2x'] ) ) {
				$plugin_icon_url = $plugin['icons']['2x'];
			} elseif ( ! empty( $plugin['icons']['1x'] ) ) {
				$plugin_icon_url = $plugin['icons']['1x'];
			} else {
				$plugin_icon_url = $plugin['icons']['default'];
			}

			$last_updated_timestamp = strtotime( $plugin['last_updated'] );

		?>
		<div class="plugin-card plugin-card-<?php echo sanitize_html_class( $plugin['slug'] ); ?>">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="<?php echo esc_url( $details_link ); ?>" class="thickbox open-plugin-details-modal">
						<?php echo $title; ?>
						<img src="<?php echo esc_attr( $plugin_icon_url ) ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<?php
						if ( $action_links ) {
							echo '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
						}
					?>
				</div>
				<div class="desc column-description">
					<p><?php echo $description; ?></p>
					<p class="authors"><?php echo $author; ?></p>
				</div>
			</div>
			<div class="plugin-card-bottom">
				<div class="vers column-rating">
					<?php wp_star_rating( array( 'rating' => $plugin['rating'], 'type' => 'percent', 'number' => $plugin['num_ratings'] ) ); ?>
					<span class="num-ratings" aria-hidden="true">(<?php echo number_format_i18n( $plugin['num_ratings'] ); ?>)</span>
				</div>
				<div class="column-updated">
					<strong><?php _e( 'Last Updated:' ); ?></strong> <?php printf( __( '%s ago' ), human_time_diff( $last_updated_timestamp ) ); ?>
				</div>
				<div class="column-downloaded">
					<?php
					if ( $plugin['active_installs'] >= 1000000 ) {
						$active_installs_text = _x( '1+ Million', 'Active plugin installs' );
					} else {
						$active_installs_text = number_format_i18n( $plugin['active_installs'] ) . '+';
					}
					printf( __( '%s Active Installs' ), $active_installs_text );
					?>
				</div>
				<div class="column-compatibility">
					<?php
					if ( ! empty( $plugin['tested'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['tested'] ) ), $plugin['tested'], '>' ) ) {
						echo '<span class="compatibility-untested">' . __( 'Untested with your version of WordPress' ) . '</span>';
					} elseif ( ! empty( $plugin['requires'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['requires'] ) ), $plugin['requires'], '<' ) ) {
						echo '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of WordPress' ) . '</span>';
					} else {
						echo '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of WordPress' ) . '</span>';
					}
					?>
				</div>
			</div>
		</div>
		<?php
		}

		// Close off the group divs of the last one
		if ( ! empty( $group ) ) {
			echo '</div></div>';
		}

	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 1.0.0
	 */
	public function ajax_response() {

		$this->prepare_items();

		ob_start();

		if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}

		$rows = ob_get_clean();
		$response = array( 'rows' => $rows );

		if ( isset( $this->_pagination_args['total_items'] ) ) {
			$response['total_items_i18n'] = sprintf(
				_n( '%s item', '%s items', $this->_pagination_args['total_items'] ),
				number_format_i18n( $this->_pagination_args['total_items'] )
			);
		}

		if ( isset( $this->_pagination_args['total_pages'] ) ) {
			$response['total_pages'] = $this->_pagination_args['total_pages'];
			$response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
		}

		die( wp_json_encode( $response ) );
	}

	/**
	 * Send required variables to JavaScript land
	 * @since 1.0.0
	 */
	public function _js_vars() {

		$args = array(
			'class'  => get_class( $this ),
			'screen' => array(
				'id'   => $this->screen->id,
				'base' => $this->screen->base,
			)
		);

		printf( "<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode( $args ) );
	}

}
