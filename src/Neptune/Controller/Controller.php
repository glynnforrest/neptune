<?php

namespace Neptune\Controller;

use Neptune\Exceptions\MethodNotFoundException;
use Neptune\Security\SecurityFactory;
use Neptune\Assets\Assets;
use Neptune\Core\Neptune;

use Symfony\Component\HttpFoundation\Request;

/**
 * Controller
 * @author Glynn Forrest me@glynnforrest.com
 */
abstract class Controller {

	protected $request;
	protected $before_called;
	protected $neptune;

	public function __call($method, $args) {
		throw new MethodNotFoundException('Method not found: ' . $method);
	}

	public function __construct(Request $request) {
		$this->request = $request;
		$this->neptune = Neptune::getInstance();
	}

	public function runMethod($method, $args = array()) {
		$method .= 'Action';
		if(!$this->before_called) {
			$this->before_called = true;
			try {
				if(!$this->before()) {
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
		return Assets::getInstance();
	}

}
