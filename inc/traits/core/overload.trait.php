<?php
/**
 * @package TSF_Extension_Manager\Traits\Overload
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

// phpcs:disable, Generic.Files.OneObjectStructurePerFile.MultipleFound -- Collection, not auto-loaded nor class-mapped.
// phpcs:disable, Generic.CodeAnalysis.UselessOverridingMethod.Found -- Enforced, not useless.
// phpcs:disable, Squiz.Commenting.FunctionComment.Missing -- The trait doc explains it.

/**
 * Legend/Definitions:
 *
 * - Facade Legend/Definitions:
 *      Choose one per trait.
 *
 *      - Core: Final instance.
 *         - No parents.
 *         - Maybe children.
 *         - All methods are protected.
 *
 *      - Master: First instance of facade. Calls all the shots.
 *         - Expects parent class.
 *         - Has no child class.
 *         - All methods are public.
 *            - Except for subconstructor.
 *         - Expects class to be labelled "final".
 *
 *      - Sub: Sub instance.
 *         - Expects child class.
 *         - Expects parent class.
 *
 *      - Child: Child instance.
 *         - Synonymous to "static".
 *         - Expects parent class.
 *         - Could have child class.
 *         - Prevents object calling.
 *
 *      - Stray: Expects nothing.
 *         - Maybe child.
 *         - Maybe parent.
 *
 * - Visibility Legend/Definitions:
 *      These can be combined.
 *
 *      - Final: Final instance.
 *         - Expects children classes not to contain same methods.
 *         - All methods are labelled "final".
 *         - Expects class to be labelled "final".
 *
 *      - Solo: Single object.
 *         - Expects no parents.
 *         - Expects no children.
 *         - All methods are labelled "final".
 *         - Expects class to be labelled "final".
 *         - Prevents facade pattern.
 *         - All methods could be public.
 *
 *      - Static: Expects class not to be initiated.
 *         - Synonymous to "child".
 *         - Prevents object calling.
 *         - All public methods are static.
 *
 *      - Once: Expects class to be called at most once.
 *         - Caches method calls.
 *         - Exits PHP on second call.
 *
 *      - Interface: Contains abstract methods.
 *
 *      - Private: All methods are private.
 *
 *      - <No keyword>: Expects nothing.
 *         - All methods are "protected".
 *
 *      - Public: All methods are public.
 *
 * - Type Legend/Definitions:
 *      Choose one per trait.
 *
 *      - Enclose: Prevents common hacking methods through magic method nullification.
 *
 *      - Construct: Holds constructor.
 *         - When interface: Holds subsconstructor.
 *            - Make sure the subconstructor is private. Otherwise late static binding will kick in.
 *
 *      - Destruct: Holds destructor and keeps track of destruct calling.
 *
 *      - Ignore_Properties_Core_Public_Final: Ignores invalid property calling. Prevents PHP warning messages.
 *
 *      - <No keyword>: Should not exist.
 */

/**
 * Holds protected magic constructor method that forces only a parent constructor.
 *
 * To be used in a Facade pattern.
 * Loads parent constructor.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Child {

	protected function __construct() {
		parent::__construct();
	}
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
trait Construct_Child_Interface {

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
trait Construct_Master_Once_Interface {

	public function __construct() {

		static $count = 0;

		// Don't execute this instance twice. For some reason conditional counting can't be done.
		$count < 1
			or \wp_die( '<code>' . \esc_html( __CLASS__ . '::' . __FUNCTION__ ) . '()</code> may only be called once. See trait <code>' . \esc_html( __TRAIT__ ) . '</code>.' );
		$count++;

		parent::__construct();

		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Holds public magic constructor method and forces a subsconstructor.
 * The constructor may only be called once per class, otherwise the plugin will kill itself.
 *
 * Does not load parent constructor.
 * Constructor is final.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Master_Once_Final_Interface {

	final public function __construct() {

		static $count = 0;

		// Don't execute this instance twice. For some reason conditional counting can't be done.
		$count < 1
			or \wp_die( '<code>' . \esc_html( __CLASS__ . '::' . __FUNCTION__ ) . '()</code> may only be called once. See trait <code>' . \esc_html( __TRAIT__ ) . '</code>.' );
		$count++;

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
trait Construct_Sub_Once_Interface {

	public function __construct() {

		static $count = 0;

		// Don't execute this instance twice. For some reason conditional counting can't be done.
		$count < 1
			or \wp_die( '<code>' . \esc_html( __CLASS__ . '::' . __FUNCTION__ ) . '()</code> may only be called once. See trait <code>' . \esc_html( __TRAIT__ ) . '</code>.' );
		$count++;

		parent::__construct();

		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Holds public magic constructor method and forces a subsconstructor.
 * The constructor may only be called once per class, otherwise the plugin will kill itself.
 *
 * Does not load parent constructor.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Core_Once_Interface {

	public function __construct() {

		static $count = 0;

		// Don't execute this instance twice. For some reason conditional counting can't be done.
		$count < 1
			or \wp_die( '<code>' . \esc_html( __CLASS__ . '::' . __FUNCTION__ ) . '()</code> may only be called once. See trait <code>' . \esc_html( __TRAIT__ ) . '</code>.' );
		$count++;

		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Holds protected magic constructor method and forces a subsconstructor.
 * To be used on final instance.
 *
 * Does not load parent constructor.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Core_Interface {

	protected function __construct() {
		$this->construct();
	}

	abstract protected function construct();
}

/**
 * Forces all classes and subclasses to be treated as static. In essence, classes
 * that use this trait can't be used with a 'new' keyword. They can be safely
 * called without interrupting flow.
 *
 * Does not load parent.
 *
 * This trait applies nicely with the following design patterns:
 * - Singleton Pattern.
 * - Prototype Pattern.
 * - Decorator Pattern.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Core_Static_Final {

	final protected function __construct() { }
}

/**
 * Forces the classes to be treated as a single static. In essence, classes that
 * use this trait can't be used with a 'new' keyword. They can be safely called without
 * interrupting flow.
 *
 * You cannot instantiate a child class with this on PHP 7.3 or below. 7.4 or later do
 * ignore the private variable.
 *
 * Does not load parent.
 * Does load instance methods and properties.
 *
 * This trait applies nicely with the following design patterns:
 * - Singleton Pattern.
 * - Prototype Pattern.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Core_Static_Final_Instance {

	private function __construct() {}

	/**
	 * The object instance.
	 *
	 * @since 1.3.0
	 * @since 2.6.0 Made protected from private; scope binding is ignored anyway on PHP 7.4+.
	 *
	 * @var object|null This object instance.
	 */
	protected static $instance;

	/**
	 * Sets the class instance.
	 *
	 * @since 1.3.0
	 * @access private
	 * @static
	 */
	final public static function set_instance() {
		if ( ! static::$instance )
			static::$instance = new static;
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 1.3.0
	 * @access private
	 * @static
	 *
	 * @return object The current instance.
	 */
	final public static function get_instance() {

		if ( ! static::$instance )
			static::set_instance();

		return static::$instance;
	}
}


/**
 * Forces the classes to be treated as a single static. In essence, classes that
 * use this trait can't be used with a 'new' keyword. They can be safely called without
 * interrupting flow.
 *
 * Does load instance methods and properties.
 *
 * This trait applies nicely with the following design patterns:
 * - Singleton Pattern.
 * - Prototype Pattern.
 *
 * @see Construct_Core_Static_Unique_Instance_Master.
 *      That trait allows overriding the instance so the class can be extended
 *      by various arbitrary classes, each holding a unique instance.
 * @since 2.6.0
 * @access private
 */
trait Construct_Core_Static_Unique_Instance_Core {

	private function __construct() {}

	/**
	 * Sets the class instance.
	 *
	 * @since 2.6.0
	 * @access private
	 * @static
	 */
	final public static function reset_instance() {
		self::class === static::class
			and \wp_die( '<code>' . \esc_html( __CLASS__ ) . '()</code> must be extended. See trait <code>' . \esc_html( __TRAIT__ ) . '</code>.' );

		static::$instance = new static;
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 2.6.0
	 * @access private
	 * @static
	 *
	 * @return object The current instance.
	 */
	final public static function get_instance() {

		// static::$instance::class is PHP 8.0+
		if ( ! static::$instance || ( \get_class( static::$instance ) !== static::class ) )
			static::reset_instance();

		return static::$instance;
	}
}

/**
 * @see Construct_Core_Static_Unique_Instance_Core
 * @since 2.6.0
 * @access private
 */
trait Construct_Core_Static_Unique_Instance_Master {

	/**
	 * The object instance.
	 *
	 * @since 2.6.0
	 *
	 * @var object|null This object instance.
	 */
	protected static $instance;
}

/**
 * Forces the classes to be treated as a single static. In essence, classes that
 * use this trait can't be used with a 'new' keyword. They may have public functions that
 * can kick off private instantiation.
 *
 * Does not load parent.
 * Does load private instance methods and properties.
 *
 * This trait applies nicely with the following design patterns:
 * - Builder Pattern.
 * - Prototype Pattern.
 *
 * @since 2.2.0
 * @access private
 */
trait Construct_Core_Static_Stray_Private_Instance {

	private function __construct() {}

	/**
	 * The object instance.
	 *
	 * @since 2.2.0
	 *
	 * @var object|null This object instance.
	 */
	private static $instance;

	/**
	 * Sets the class instance.
	 *
	 * @since 2.2.0
	 * @access private
	 * @static
	 */
	private static function set_instance() {

		if ( ! static::$instance )
			static::$instance = new static;
	}

	/**
	 * Gets the class instance. It's set when it's null.
	 *
	 * @since 2.2.0
	 * @access private
	 * @static
	 *
	 * @return object The current instance.
	 */
	private static function get_instance() {

		if ( ! static::$instance )
			static::set_instance();

		return static::$instance;
	}
}

/**
 * Holds private magic constructor method.
 *
 * Does not load parent.
 *
 * @since 1.0.0
 * @access private
 */
trait Construct_Stray_Private {
	private function __construct() { }
}

/**
 * Holds magic destructor method which invokes cache on method _has_died().
 * To be used on final instance.
 *
 * @since 1.0.0
 * @access private
 */
trait Destruct_Core_Public_Final_Interface {

	final public function __destruct() {
		$this->_has_died( true );
	}

	/**
	 * Determines if the plugin instance has died or not.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param bool $set Whether to set death.
	 * @return false If the plugin has not died. True otherwise.
	 */
	final public function _has_died( $set = false ) {

		static $died = false;

		return $died || $set && $died = true;
	}
}

/**
 * Warns invalid class property calls, instead of instating a fatal error.
 *
 * @since 1.0.0
 * @access private
 */
trait Ignore_Properties_Core_Public_Final {

	/**
	 * Runs when an inexisting property is trying to be set.
	 * Does not set invalid properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  The inexisting property name.
	 * @param mixed  $value The propertie value that ought to be set.
	 */
	final public function __set( $name = '', $value = null ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		\tsf()->_doing_it_wrong( __METHOD__, \esc_html( __CLASS__ . '::$' . $name . ' does not exist.' ) );
	}

	/**
	 * Runs when a inexisting property is trying to be called.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The inexisting property name.
	 * @return null.
	 */
	final public function __get( $name = '' ) {

		\tsf()->_doing_it_wrong( __METHOD__, \esc_html( __CLASS__ . '::$' . $name . ' does not exist.' ) );

		return null;
	}
}
