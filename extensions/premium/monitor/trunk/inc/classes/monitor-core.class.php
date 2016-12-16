<?php
/**
 * @package TSF_Extension_Manager_Extension\Monitor\Admin
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Require user interface trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'ui' );

/**
 * Require extension options trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'extension-options' );

/**
 * Require extension forms trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'extension-forms' );

/**
 * Require error trait.
 * @since 1.0.0
 */
_tsf_extension_manager_load_trait( 'error' );

/**
 * @package TSF_Extension_Manager\Traits
 */
use TSF_Extension_Manager\Enclose_Stray_Private as Enclose_Stray_Private;
use TSF_Extension_Manager\Construct_Master_Once_Interface as Construct_Master_Once_Interface;

/**
 * Monitor extension for The SEO Framework
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

/**
 * Class TSF_Extension_Manager_Extension\Monitor_Core
 *
 * Holds extension admin page functions.
 *
 * @since 1.0.0
 * @access private
 * @errorval 101xxxx
 */
