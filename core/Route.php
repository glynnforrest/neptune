<?php

namespace neptune\core;

use neptune\http\Request;
use neptune\validate\Validator;

/**
 * Route
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Route {

	const VARIABLE = '`:([a-zA-Z][a-zA-Z0-9]+)`';
	const PATTERN_VARIABLE = '(?P<\1>[^/]+)';
	const PATTERN_ARGS = '(?P<args>.+)';
	const ARGS_EXPLODE = 0;
	const ARGS_SINGLE = 1;

	protected $regex, $controller, $method;
	protected $format, $catch_all, $args_format, $url;
	protected $args = array();
	protected $transforms = array();
	protected $rules = array();
	protected $default_args = array();
	protected $http_methods = array();
	protected $request;
	protected $passed;

	public function __construct($url, $controller = null, $method = null, $args = null) {
		$this->url($url);
		$this->controller = $controller;
		$this->method = $method;
		$this->args = $args;
		$this->request = Request::getInstance();
	}

	protected function generateRegex($regex) {
		$regex = str_replace('(', '(?:', $regex);
		$regex = str_replace(')', ')?', $regex);
		$regex = preg_replace('`:args`', self::PATTERN_ARGS, $regex);
		$regex = preg_replace(self::VARIABLE, self::PATTERN_VARIABLE, $regex);
		return '`^' . $regex . '$`';
	}

	public function url($url) {
		$this->regex = $this->generateRegex($url);
		$this->url = $url;
		return $this;
	}

	public function controller($controller) {
		$this->controller = $controller;
		return $this;
	}

	public function method($method) {
		$this->method = $method;
		return $this;
	}

	public function args($args) {
		$this->args = $args;
		return $this;
	}

	public function httpMethod($http_methods) {
		$http_methods = (array) $http_methods;
		foreach($http_methods as $method) {
			$this->http_methods[] = strtoupper($method);
		}
		return $this;
	}

	public function format($format) {
		$this->format = (array) $format;
		return $this;
	}

	public function transforms($name, $function) {
		$this->transforms[$name] = $function;
		return $this;
	}

	public function rules($rules) {
		$this->rules = (array) $rules;
		return $this;
	}

	public function defaultArgs($default_args) {
		$this->default_args = (array) $default_args;
		return $this;
	}

	public function catchAll($catch_all) {
		$this->catch_all = $catch_all;
		return $this;
	}

	public function argsFormat($args_format) {
		$this->args_format = $args_format;
		return $this;
	}

	public function name($name) {
		Dispatcher::getInstance()->setRouteName($name, $this->url);
		return $this;
	}

	public function getUrl() {
		return $this->url;
	}

	public function test($source) {
		if (!preg_match($this->regex, $source, $vars)) {
			return false;
		}
		//Check if the request method is supported by this route.
		if ($this->http_methods) {
			if (!in_array($this->request->method(), $this->http_methods)) {
				return false;
			}
		}
		//Check if the format requested is supported by this route.
		if ($this->format) {
			if (!in_array($this->request->format(), $this->format)) {
				if (!in_array('any', $this->format)) {
					return false;
				}
			}
		} else {
			if ($this->request->format() !== 'html') {
				return false;
			}
		}
		//get controller and method from either matches or supplied defaults.
		if (!isset($vars['controller'])) {
			$vars['controller'] = $this->controller;
		}
		if (!isset($vars['method'])) {
			$vars['method'] = $this->method;
		}
		//should have a controller and function by now.
		if (!$vars['controller'] | !$vars['method']) {
			return false;
		}
		//process the transforms.
		foreach ($this->transforms as $k => $v) {
			if (isset($vars[$k])) {
				$vars[$k] = $v($vars[$k]);
			}
		}

		$this->controller = $vars['controller'];
		unset($vars['controller']);
		$this->method = $vars['method'];
		unset($vars['method']);
		//get args
		$args = array();
		//gather named variables from regex
		foreach ($vars as $k => $v) {
			if (!is_numeric($k)) {
				// unset($vars[$k]);
				$args[$k] = $vars[$k];
			}
		}
		//add default variables if they don't exist.
		foreach ($this->default_args as $name => $value) {
			if (!isset($args[$name])) {
				$args[$name] = $value;
			}
		}

		//Gather numerically indexed args for auto rules
		if (isset($vars['args'])) {
			switch ($this->args_format) {
			case self::ARGS_EXPLODE:
				$vars['args'] = explode('/', $vars['args']);
				foreach ($vars['args'] as $k => $v) {
					$args[$k] = $v;
				}
				break;
			case self::ARGS_SINGLE:
				$args[] = $vars['args'];
			default:
				break;
			}
			unset($args['args']);
		}
		//test the variables using validator
		if (!empty($this->rules)) {
			$v = new Validator($args, $this->rules);
			$v->controller = $this->controller;
			$v->method = $this->method;
			if (!$v->validate())
				return false;
		}
		if(!empty($args)) {
			$this->args = $args;
		}
		$this->passed = true;
		return true;
	}

	public function getAction() {
		return $this->passed ? array($this->controller, $this->method, (array) $this->args) : null;
	}

}

?>
