<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;
use Neptune\Core\ComponentException;

class Neptune {

	protected static $instance;
	protected $components = array();
	protected $singletons = array();

	protected function __construct() {
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Create a component of $name, instantiated with $function.
	 */
	public function set($name, $function) {
		$this->components[$name] = $function;
	}

	/**
	 * Create a component of $name, instantiated with $function. A
	 * single instance of the function result will be returned every
	 * time.
	 */
	public function setSingleton($name, $function) {
		$this->components[$name] = $function;
		$this->singletons[$name] = null;
	}

	/**
	 * Get an instance of component $name. If $name was set as a
	 * singleton the same instance will be returned, otherwise a new
	 * instance will be created.
	 */
	public function get($name) {
		if(!isset($this->components[$name])) {
			throw new ComponentException(
				"Component not registered: $name"
			);
		}
		if(isset($this->singletons[$name])) {
			return $this->singletons[$name];
		}
		if(!is_callable($this->components[$name])) {
			throw new ComponentException(
				"Registered component is not a callable function:
				$name"
			);
		}
		$component = $this->components[$name]();
		if(array_key_exists($name, $this->singletons) && is_null($this->singletons[$name])) {
			$this->singletons[$name] = $component;
		}
		return $component;
	}

	/**
	 * Remove all registered components.
	 */
	public function reset() {
		$this->components = array();
		$this->singletons = array();
	}

	/**
	 * Load the environment $env. This will include the file
	 * app/env/$env.php and call Config::loadEnv($env). If $env is not
	 * defined, the value of the config key 'env' in
	 * config/neptune.php will be used.
	 */
	public function loadEnv($env = null) {
		$c = Config::load('neptune');
		if(!$env) {
			$env = $c->getRequired('env');
		}
		include $c->getRequired('dir.root') . 'app/env/' . $env . '.php';
		Config::loadEnv($env);
		return true;
	}

	public static function handleErrors() {
		set_error_handler('\Neptune\Core\Neptune::dealWithError');
		set_exception_handler('\Neptune\Core\Neptune::dealWithException');
	}

	public static function dealWithError($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new NeptuneError($errno, $errstr, $errfile, $errline, $errcontext);
	}

	public static function dealWithException($exception) {
		Events::getInstance()->send($exception);
	}

}
