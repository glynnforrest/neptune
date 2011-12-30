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

}

?>
