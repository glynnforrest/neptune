<?php

namespace neptune\core;

use neptune\http\Request;

/**
 * Route
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Route {

	const VARIABLE = '`:([a-zA-Z][a-zA-Z0-9]+)`';
	const VARIABLE_PATTERN = '(?P<\1>[^/]+)';
	const ARGS_PATTERN = '(?P<args>.+)';

	protected $regex, $controller, $func, $method;
	protected $format, $catchAll, $callHidden, $argsFormat, $url;
	protected $args = array();
	protected $transforms = array();
	protected $rules = array();
	protected $defaults = array(); //do i need this??
	protected $request;
	protected $passed;

	public function __construct($regex, $controller = null, $func = null, $args = null) {
		$this->regex = $this->generateRegex($regex);
		$this->controller = $controller;
		$this->func = $func;
		$this->args = $args;
		$this->request = Request::getInstance();
	}

	protected function generateRegex($regex) {
		$regex = str_replace('(', '(?:', $regex);
		$regex = str_replace(')', ')?', $regex);
		$regex = preg_replace('`:args`', self::ARGS_PATTERN, $regex);
		$regex = preg_replace(self::VARIABLE, self::VARIABLE_PATTERN, $regex);
		return '`^' . $regex . '$`';
	}

	public function regex($regex) {
		$this->regex = $this->generateRegex($regex);
		return $this;
	}

	public function controller($controller) {
		$this->controller = $controller;
		return $this;
	}

	public function func($func) {
		$this->func = $func;
		return $this;
	}

	public function args($args) {
		$this->args = $args;
		return $this;
	}

	public function method($method) {
		$this->method = $method;
		return $this;
	}

	public function format($format) {
		$this->format = $format;
		return $this;
	}

	public function transforms($transforms) {
		$this->transforms = $transforms;
		return $this;
	}

	public function rules($rules) {
		$this->rules = $rules;
		return $this;
	}

	public function defaults($defaults) {
		$this->defaults = $defaults;
		return $this;
	}

	public function catchAll($catchAll) {
		$this->catchAll = $catchAll;
		return $this;
	}

	public function callHidden($callHidden) {
		$this->callHidden = $callHidden;
		return $this;
	}

	public function argsFormat($argsFormat) {
		$this->argsFormat = $argsFormat;
		return $this;
	}

	public function url($url) {
		$this->url = $url;
		return $this;
	}

	public function test($source) {
		if (!preg_match($this->regex, $source, $vars)) {
			return false;
		}
		//Check if the request method is supported by this route.
		if ($this->method) {
			if (!in_array(strtoupper($this->request->method()), $this->method)) {
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
		//get controller and function from either matches or supplied defaults.
		if (!isset($vars['controller'])) {
			$vars['controller'] = $this->controller;
		}
		if (!isset($vars['func'])) {
			$vars['func'] = $this->func;
		}
		//should have a controller and function by now.
		if (!$vars['controller'] | !$vars['func']) {
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
		$this->func = $vars['func'];
		unset($vars['func']);
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
		foreach ($args as $name => $value) {
			if (!isset($args[$name])) {
				$args[$name] = $value;
			}
		}

		//Gather numerically indexed args for auto rules
		if (isset($vars['args'])) {
			switch ($this->argsFormat) {
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
			if (!$v->validate())
				return false;
		}
		$this->passed = true;
		return true;
	}

	public function getAction() {
		return $this->passed ? array($this->controller, $this->func, $this->args) : null;
	}

}
//sample usage
// $d = Dispatcher::getInstance();
// $d->route('/url')->controller('foo')->function('index');
?>

