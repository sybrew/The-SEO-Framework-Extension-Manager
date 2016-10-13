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
	 * Unserializing instances is forbidden.
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
	 * Unserializing instances is forbidden.
	 */
	final protected function __wakeup() { }
}

/**
 * Holds protected magic constructor method and forces a subsconstructor and
 * parent constructor.
 *
 * To be used in a Facade pattern.
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
 * The constructor may only be called once per class, otherwise the plugin will kill itself.
 *
 * Loads parent constructor.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Master {

	public function __construct() {

		static $count = 0;

		//* Don't execute this instance twice. For some reason conditional counting can't be done.
		$count < 1 or wp_die( '<code>' . esc_html( __CLASS__ . '::' . __FUNCTION__ ) . '()</code> may only be called once. See trait <code>' . esc_html( __TRAIT__ ) . '</code>.' );
		$count++;

		parent::__construct();

		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Holds public magic constructor method and forces a subsconstructor and parent constructor.
 * The constructor may only be called once per class, otherwise the plugin will kill itself.
 *
 * Does not load parent constructor.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Solo_Master {

	final public function __construct() {

		static $count = 0;

		//* Don't execute this instance twice. For some reason conditional counting can't be done.
		$count < 1 or wp_die( '<code>' . esc_html( __CLASS__ . '::' . __FUNCTION__ ) . '()</code> may only be called once. See trait <code>' . esc_html( __TRAIT__ ) . '</code>.' );
		$count++;

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
 * Holds magic destructor method which invokes cache on method _has_died().
 * To be used on final instance.
 *
 * @since 1.0.0
 * @access private
 */
trait Destruct_Final {

	final public function __destruct() {
		$this->_has_died( true );
	}

	/**
	 * Determines if the plugin instance has died or not.
	 *
	 * @since 1.0.0
	 * @access private
	 * @staticvar bool $died Determines plugin alive state.
	 *
	 * @param bool $set Whether to set death.
	 * @return false If the plugin has not died. True otherwise.
	 */
	final public function _has_died( $set = false ) {

		static $died = false;

		if ( $set )
			$died = true;

		return $died;
	}
}

/**
 * Forces all classes and subclasses to be treated as static. In essence, classes
 * that use this trait can't be used with a 'new' keyword. Otherwise they can be
 * safely called without interrupting flow.
 *
 * This is great together with Enclose_Master.
 *
 * This trait applies nicely with the following design patterns:
 * - Singleton Pattern.
 * - Prototype Pattern.
 * - Decorator Pattern.
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

/**
 * Ignores invalid class properties, instead of initiating a fatal error.
 *
 * @since 1.0.0
 * @access private
 */
trait Ignore_Properties {

	/**
	 * Runs when an inexisting property is trying to be set.
	 * Does not set invalid properties.
	 *
	 * @since 1.0.0
	 *
	 * @param $name The inexisting property name.
	 * @param $value The propertie value that ought to be set.
	 */
	final public function __set( $name = '', $val = null ) {
		the_seo_framework()->_doing_it_wrong( __METHOD__, esc_html( 'static::$' . $name . ' does not exist.' ) );
	}

	/**
	 * Runs when a inexisting property is trying to be called.
	 *
	 * @since 1.0.0
	 *
	 * @param $name The inexisting property name.
	 * @return null.
	 */
	final public function __get( $name = '' ) {

		the_seo_framework()->_doing_it_wrong( __METHOD__, esc_html( 'static::$' . $name . ' does not exist.' ) );

		return null;
	}
}
