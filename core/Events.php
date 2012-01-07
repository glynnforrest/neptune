<?php

namespace neptune\core;

/**
 * Events
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Events {

	protected static $instance;
	protected $handlers = array();

	protected function __construct() {
	
	}

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function addHandler($name, $function) {
		if(is_callable($function)) {
			$this->handlers[$name] = $function;
		}
	}

	public function send($name, $args = null) {
		if(is_object($name)) {
			foreach($this->handlers as $k => $v) {
				if($name instanceof $k) {
					$v($name);
				}
			}
		} else {
			$name = (string) $name;
			if(isset($this->handlers[$name])) {
				$args = (array) $args;
				return call_user_func_array($this->handlers[$name], $args);
			}
		}
	}


}
?>
