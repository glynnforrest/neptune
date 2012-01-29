<?php

namespace neptune\controller;

use neptune\exceptions\MethodNotFoundException;
use neptune\security\SecurityFactory;
use neptune\http\Request;
use neptune\http\Response;

/**
 * Base Controller
 */
abstract class Controller {

	protected $request;
	protected $response;

	public function __call($method, $args) {
		throw new MethodNotFoundException('Function not found: ' . $method);
	}

	public function __construct() {
		$this->request = Request::getInstance();
		$this->response = Response::getInstance();
	}

	public function callHidden($method, $args) {
		return call_user_func_array(array($this, $method), $args);
	}

	protected function security($name = null) {
		return SecurityFactory::getSecurity($name);
	}

}

?>
