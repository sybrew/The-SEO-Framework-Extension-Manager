<?php
/**
 * @package TSF_Extension_Manager_Extension\Multilang
 */
namespace TSF_Extension_Manager_Extension;

/**
 * Extension Name: MultiLang
 * Extension URI: https://premium.theseoframework.com/extensions/multilang/
 * Description: The MultiLang extension allows you to tell Search Engines where your macrolinguistic websites are located.
 * Version: 1.0.2
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv3
 */

defined( 'ABSPATH' ) and tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or die;

/**
 * @package TSF_Extension_Manager
 */
use TSF_Extension_Manager\Enclose_Master as Enclose_Master;
use TSF_Extension_Manager\Construct_Solo_Master as Construct_Solo_Master;

/**
 * Multilang extension for The SEO Framework
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

//* Notify the existence of this extension through a lovely definition.
define( 'THE_SEO_FRAMEWORK_MULTILANG', true );

//* Define version, for future things.
define( 'THE_SEO_FRAMEWORK_MULTILANG_VERSION', '1.0.0' );

add_action( 'plugins_loaded', __NAMESPACE__ . '\the_seo_framework_multilang_init', 11 );
/**
 * Initialize the extension.
 *
 * @since 1.0.0
 *
 * @return bool True if class is loaded
 */
function the_seo_framework_multilang_init() {

	static $loaded = null;

	//* Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	new Multilang();

	return $loaded = true;
}

/**
 * Class TSF_Extension_Manager_Extension\Multilang
 *
 * @since 1.0.0
 *
 * @final Please don't extend this extension.
 */
final class Multilang {
	use Enclose_Master, Construct_Solo_Master;

	/**
	 * The constructor, initialize plugin.
	 */
	private function construct() { }
}
