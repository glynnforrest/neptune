<?php

namespace neptune\controller;

use neptune\exceptions\MethodNotFoundException;
use neptune\security\SecurityFactory;
use neptune\http\Request;
use neptune\http\Response;
use neptune\assets\AssetsManager;

/**
 * Base Controller
 */
abstract class Controller {

	protected $request;
	protected $response;
	protected $before_called;

	public function __call($method, $args) {
		throw new MethodNotFoundException('Method not found: ' . $method);
	}

	public function __construct() {
		$this->request = Request::getInstance();
		$this->response = Response::getInstance();
	}

	public function _runMethod($method, $args = array()) {
		if(substr($method, 0, 1) === '_') {
			return false;
		}
		if(!$this->before_called) {
			$this->before_called = true;
			try {
				if(!$this->_before()) {
					return false;
				}
			} catch (MethodNotFoundException $e) {}
		}
		return call_user_func_array(array($this, $method), $args);
	}

	protected function _security($name = null) {
		return SecurityFactory::getDriver($name);
	}

	protected function _assets() {
		return AssetsManager::getInstance();
	}

}

?>
