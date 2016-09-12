<?php
/**
 * @package TSF_Extension_Manager_Extension
 */
namespace TSF_Extension_Manager_Extension;

/**
 * Extension Name: Google Analytics
 * Extension URI: https://premium.theseoframework.com/extensions/incognito/
 * Description: The Google Analytics extension allows you to set up and interact with Google Analytics right from your dashboard.
 * Version: 1.0.0
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * @package TSF_Extension_Manager
 */
use TSF_Extension_Manager\Enclose_Master as Enclose_Master;
use TSF_Extension_Manager\Construct_Solo_Master as Construct_Solo_Master;

/**
 * Google Analytics extension for The SEO Framework
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

final class Google_Analytics_Admin {
	use Enclose_Master, Construct_Solo_Master;

	protected function construct() {

	}
}
