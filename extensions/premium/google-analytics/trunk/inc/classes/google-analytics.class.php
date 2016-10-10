<?php
/**
 * @package TSF_Extension_Manager_Extension\Analytics\FrontEnd
 */
namespace TSF_Extension_Manager_Extension;

defined( 'ABSPATH' ) or die;

if ( tsf_extension_manager()->_has_died() or false === ( tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * @package TSF_Extension_Manager\Traits
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

final class Google_Analytics {
	use Enclose_Master, Construct_Solo_Master;

	protected function construct() {
		add_filter( 'the_seo_framework_after_output', array( $this, 'init_output' ), 10, 1 );
	}

	public function init_output( $functions = array() ) {

		$functions[] = array(
			'callback' => array( $this, 'script' ),
		);

		return $functions;
	}

	public function script() {

		$script = $this->get_script();

		return $script;
	}

	protected function get_script() {

		$key = 'invalid';

		$script = <<<GASCRIPT
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','gaplusu');
gaplusu('create',$key,'auto',{'allowLinker':true});
gaplusu('send', 'pageview');
</script>

GASCRIPT;

		return $script;
	}
}
