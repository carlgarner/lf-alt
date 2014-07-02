<?php

class Autoload {

	/**
	 * Class directories
	 *
	 * @var array
	 */
	static protected $directories = array();


	/**
	 * Class Aliases
	 *
	 * @var array
	 */
	static protected $aliases = array();


	/**
	 * Class mappings
	 *
	 * @var array
	 */
	static protected $mappings = array();


	/**
	 * Has this class been registered as the autoloader?
	 *
	 * @var string
	 */
	static private $_initialized = false;


	/**
	 * File extension for classes
	 *
	 * @var string
	 */
	static private $_ext = '.php';


	/**
	 * Register this class to be used for autoloading
	 *
	 * @return  void
	 */
	static public function register() {
		// Not initialized yet
		if ( ! self::$_initialized) {
			// Check if already set up as an autoloader
			$in_spl_autoload = false;

			if (spl_autoload_functions()) {
				foreach (spl_autoload_functions() as $loader) {
					if ($loader[0] == __CLASS__ ) {
						$in_spl_autoload = true;

						continue;
					}
				}
			}

			// It's not set up as an autoloader. Set it up
			if ( ! $in_spl_autoload) {
				spl_autoload_register(array(__CLASS__, 'load'));

				self::$_initialized = true;
			}
		}
	}


	/**
	 * Unregister this class to be used for autoloading
	 *
	 * @return  void
	 */
	static public function unregister() {
		// Initialized already
		if (self::$_initialized) {
			spl_autoload_unregister(array(__CLASS__, 'load'));

			self::$_initialized = false;
		}
	}


	/**
	 * Load the class file corresponding to a given class
	 *
	 * @param   string  $class
	 * @return  mixed
	 */
	static public function load($class) {
		// This should never happen but in case this class hasn't been registered yet, register it real quick
		if ( ! self::$_initialized) {
			self::register();
		}

		// Check to see if the class has been aliased
		if (isset(self::$aliases[$class])) {
			return class_alias(self::$aliases[$class], $class);
		}

		// Load mapped class
		elseif (isset(self::$mappings[$class])) {
			return require self::$mappings[$class];
		}

		// Regular loading of class
		return self::_load($class);
	}


	/**
	 * Register a class alias
	 *
	 * @param   string  $class
	 * @param   string  $alias
	 * @return  void
	 */
	public static function alias($class = null, $alias = null) {
		self::$aliases[$alias] = $class;
	}


	/**
	 * Register an array of class to path mappings
	 *
	 * @param   string|array    $mappings
	 * @param   string          $dir
	 * @return  void
	 */
	static public function map($mappings = array(), $dir = null) {
		if ( ! is_array($mappings)) {
			$mappings = array($mappings => $dir);
		}

		self::$mappings = array_merge(self::$mappings, $mappings);
	}


	/**
	 * Register multiple directories to be used when loading classes
	 *
	 * @param   string|array    $directories
	 * @return  void
	 */
	static public function directories($directories = array()) {
		if ( ! empty($directories)) {
			$directories = self::_dirFormat($directories);

			self::$directories = array_unique(array_merge(self::$directories, $directories));
		}
	}


	/**
	 * Alias for Autoload::directories() generally for a single directory
	 *
	 * @param  string|array  $directory
	 * @return void
	 */
	static public function directory($directory = null) {
		self::directories($directory);
	}


	/**
	 * Load class class
	 *
	 * @param   string  $class
	 * @return  mixed
	 * @throws  Exception
	 */
	static private function _load($class) {
		// Replace double backslash with a forward slash. Mostly for future use
		$name = str_replace(array('\\'), '/', $class);

		// Run through known directories for the class
		foreach (self::$directories as $directory) {
			if (file_exists($path = $directory.$name.self::$_ext)) {
				return require $path;
			}
		}

		// Log and throw exception
		$message = __CLASS__.' :: Class "'.$class.'" not found';

		throw new Exception($message);
	}


	/**
	 * Format directories with the proper trailing slash
	 *
	 * @param   array   $directories
	 * @return  array
	 */
	static private function _dirFormat($directories = array()) {
		return array_map(array(__CLASS__, '_dirFormatSlashed'), (array) $directories);
	}


	/**
	 * Add a trailing slash to directory. Because PHP < 5.3 can't have anonymous functions
	 *
	 * @param   string  $directory
	 * @return  string
	 */
	static protected function _dirFormatSlashed($directory = '') {
		return rtrim($directory, '/').'/';
	}

}


if ( ! function_exists('class_alias')) {
	/**
	 * class_alias() for PHP < 5.3
	 *
	 * @param   string  $original
	 * @param   string  $alias
	 * @return  bool
	 */
	function class_alias($original, $alias) {
		eval('abstract class '.$alias.' extends '.$original.' {}');

		return true;
	}
}
