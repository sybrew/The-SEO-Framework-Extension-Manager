<?php
/**
 * @package TSF_Extension_Manager\Traits
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
 * Holds private overloading functions to prevent injection or abstraction.
 *
 * @since 1.0.0
 * @access private
 */
trait Enclose {

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances are forbidden.
	 */
	private function __wakeup() { }
}

/**
 * Forces all classes and subclasses to prevent injection or abstraction.
 *
 * @since 1.0.0
 * @access private
 */
trait Enclose_Master {

	/**
	 * Cloning is forbidden.
	 */
	final protected function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	final protected function __wakeup() { }
}

/**
 * Holds protected magic constructor method and forces a subsconstructor and parent constructor.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Sub {

	protected function __construct() {
		parent::__construct();

		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Holds public magic constructor method and forces a subsconstructor and parent constructor.
 * May only be called once.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Master {

	public function __construct() {
		//* Don't execute this instance twice.
		static $count = 0; $count < 1 or die; $count++;

		parent::__construct();

		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Holds protected magic constructor method and forces a subsconstructor.
 * To be used on final instance.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Final {

	protected function __construct() {
		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Forces all classes and subclasses to be static.
 *
 * @since 1.0.0
 * @access private
 */
trait Force_Static_Master {

	/**
	 * Constructing is forbidden.
	 */
	final protected function __construct() { }
}
