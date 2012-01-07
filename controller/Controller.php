<?php

namespace neptune\controller;

use neptune\exceptions\MethodNotFoundException;

/**
 * Base Controller
 */
abstract class Controller {

	public function __call($method, $args) {
		throw new MethodNotFoundException('Function not found: ' . $method);
	}

	public function callHidden($method, $args) {
		return call_user_func_array(array($this, $method), $args);
	}

}

?>
