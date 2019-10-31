<?php
/**
 * @package TSF_Extension_Manager\Extension\Articles
 */

namespace TSF_Extension_Manager\Extension\Articles;

/**
 * Extension Name: Articles
 * Extension URI: https://theseoframework.com/extensions/articles/
 * Extension Description: The Articles extension enhances your published posts by automatically adding important [Structured Data](https://developers.google.com/search/docs/data-types/article). Great for bloggers, news publishers, and scientific pieces. It comes with a Google News sitemap, as well.
 * Extension Version: 2.0.0
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 * Extension Menu Slug: theseoframework-extension-settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Articles extension for The SEO Framework
 * Copyright (C) 2017-2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

/**
 * The extension version.
 *
 * @since 1.0.0
 * NOTE: The presence does NOT guarantee the extension is loaded!!!
 */
define( 'TSFEM_E_ARTICLES_VERSION', '2.0.0' );

/**
 * The extension file, absolute unix path.
 *
 * @since 1.1.0
 */
define( 'TSFEM_E_ARTICLES_BASE_FILE', __FILE__ );

/**
 * The extension map URL. Used for calling browser files.
 *
 * @since 2.2.0
 */
define( 'TSFEM_E_ARTICLES_DIR_URL', \TSF_Extension_Manager\extension_dir_url( TSFEM_E_ARTICLES_BASE_FILE ) );

/**
 * The extension file relative to the plugins dir.
 *
 * @since 1.2.0
 */
define( 'TSFEM_E_ARTICLES_DIR_PATH', \TSF_Extension_Manager\extension_dir_path( TSFEM_E_ARTICLES_BASE_FILE ) );

/**
 * The plugin class map absolute path.
 *
 * @since 1.2.0
 */
define( 'TSFEM_E_ARTICLES_PATH_CLASS', TSFEM_E_ARTICLES_DIR_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );

/**
 * Verify integrity and sets up autoloader.
 *
 * @since 1.2.0
 */
if ( false === \tsf_extension_manager()->_init_early_extension_autoloader( TSFEM_E_ARTICLES_PATH_CLASS, 'Articles', $_instance, $bits ) )
	return;

/**
 * Removes AMP articles if AMP extension is active.
 * The output is erroneous on non-posts. Pages shouldn't do AMP.
 *
 * @since 1.0.0
 */
\add_filter( 'the_seo_framework_remove_amp_articles', '\\__return_true' );

\add_action( 'the_seo_framework_init', __NAMESPACE__ . '\\_articles_init', 10 );
/**
 * Initializes the extension.
 *
 * @since 1.0.0
 * @since 1.2.0 Now also loads Admin class.
 * @since 1.4.0 Now always loads when the knowledge type is organization.
 * @since 2.0.0 No longer tests for the knowledge type option before launching.
 * @staticvar bool $loaded True when loaded.
 * @action 'the_seo_framework_do_before_output'
 * @action 'the_seo_framework_do_before_amp_output'
 * @action 'admin_init'
 * @priority 10
 * @access private
 *
 * @return bool True if class is loaded.
 */
function _articles_init() {

	static $loaded;

	if ( isset( $loaded ) )
		return $loaded;

	if ( \is_admin() ) {
		new Admin;
		$loaded = true;
	} else {
		new Front;
		$loaded = true;
	}

	new Sitemap;

	return $loaded;
}
