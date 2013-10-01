<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;

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

	public function set($name, $function) {
		$this->components[$name] = $function;
	}

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
				"Component not registered: $component"
			);
		}
		if(isset($this->singletons[$name])) {
			return $this->singletons[$name];
		}
		if(!is_callable($this->components[$name])) {
			throw new ComponentException(
				"Registered component is not a callable function:
				$component"
			);
		}
		$component = $this->components[$name]();
		if(array_key_exists($name, $this->singletons) && is_null($this->singletons[$name])) {
			$this->singletons[$name] = $component;
		}
		return $component;
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
