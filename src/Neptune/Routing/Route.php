<?php

namespace Neptune\Routing;

use Neptune\Validate\Validator;
use Neptune\Routing\RouteUntestedException;
use Neptune\Routing\RouteFailedException;
use Neptune\Helpers\RequestHelper;

use Symfony\Component\HttpFoundation\Request;

/**
 * Route
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Route {

	const PASSED = 1;
	const UNTESTED = 2;
	const FAILURE_REGEXP = 3;
	const FAILURE_HTTP_METHOD = 4;
	const FAILURE_FORMAT = 5;
	const FAILURE_CONTROLLER = 6;
	const FAILURE_METHOD = 7;
	const FAILURE_VALIDATION = 8;

	protected $url;
	protected $controller;
	protected $method;
	protected $args = array();
	protected $format;
	protected $transforms = array();
	protected $rules = array();
	protected $default_args = array();
	protected $http_methods = array();
	protected $result;
	protected $args_regex = '[^/.]+';
	protected $auto_args;
	protected $auto_args_regex = '[^/.]+';

	public function __construct($url, $controller = null, $method = null, $args = null) {
		$this->url($url);
		$this->controller = $controller;
		$this->method = $method;
		$this->args = $args;
		$this->result = self::UNTESTED;
	}

	/**
	 * Generate the regex that represents this route.
	 */
	public function getRegex() {
		//add an optional, internal _format variable to the url, used
		//for automatically checking the format at the end of the
		//url. This will only be matched if args_regex does not allow
		//for '.' .
		$url = $this->url . '(\.:_format)';

		//replace all optional variable definitions with the regex equivalents
		$regex = str_replace('(', '(?:', $url);
		$regex = str_replace(')', ')?', $regex);

		//if using auto args for this route, replace :args with a
		//regex capture group for them. Throw an exception if :args
		//isn't in the url definition
		if($this->auto_args) {
			if(!strpos($regex, ':args')) {
				throw new RouteFailedException("A route with auto args must contain ':args' in the url");
			}
			$regex = preg_replace('`:args`', '(?P<args>.+)', $regex);
		}

		//regex of args in the url defintion
		$definition_args = '`:([a-zA-Z_][a-zA-Z0-9_]+)`';
		//replace with regex of args in the url to be tested
		$url_args = '(?P<\1>' . $this->args_regex . ')';
		$regex = preg_replace($definition_args, $url_args, $regex);
		return '`^' . $regex . '$`';
	}

	public function url($url) {
		$this->url = $url;
		return $this;
	}

	public function controller($controller) {
		if($controller) {
			$this->controller = $controller;
		}
		return $this;
	}

	public function method($method) {
		if($method) {
			$this->method = $method;
		}
		return $this;
	}

	public function args($args) {
		if($args) {
			$this->args = $args;
		}
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

	public function argsRegex($regex) {
		$this->args_regex = $regex;
		return $this;
	}

	public function autoArgs($regex = null) {
		if($regex) {
			$this->auto_args_regex = $regex;
		}
		$this->auto_args = true;
		return $this;
	}

	public function getUrl() {
		return $this->url;
	}

	public function test(Request $request) {
        //check there is a controller and method before checking the
        //regex
		if (!$this->controller) {
			$this->result = self::FAILURE_CONTROLLER;
			return false;
		}
		if (!$this->method) {
			$this->result = self::FAILURE_METHOD;
			return false;
		}
		$path = $request->getPathInfo();
		if(strlen($path) > 1) {
			$path = rtrim($path, '/');
		}
		if (!preg_match($this->getRegex(), $path, $vars)) {
			$this->result = self::FAILURE_REGEXP;
			return false;
		}
		//Check if the request method is supported by this route.
		if ($this->http_methods) {
			if (!in_array($request->getMethod(), $this->http_methods)) {
				$this->result = self::FAILURE_HTTP_METHOD;
				return false;
			}
		}
		//Check if the format requested is supported by this route.
		$format = isset($vars['_format']) ? $vars['_format'] : 'html';
		if ($this->format) {
			if (!in_array($format, $this->format) && !in_array('any', $this->format)) {
					$this->result = self::FAILURE_FORMAT;
					return false;
			}
		} else {
			if ($format !== 'html') {
				$this->result = self::FAILURE_FORMAT;
				return false;
			}
		}
		unset($vars['_format']);
		//process the transforms.
		foreach ($this->transforms as $k => $v) {
			if (isset($vars[$k])) {
				$vars[$k] = $v($vars[$k]);
			}
		}

		//get args
		$args = array();
		//gather named variables from regex
		foreach ($vars as $k => $v) {
			if (!is_numeric($k)) {
				$args[$k] = $vars[$k];
			}
		}
		//add default variables if they don't exist.
		foreach ($this->default_args as $name => $value) {
			if (!isset($args[$name])) {
				$args[$name] = $value;
			}
		}

		//gather numerically indexed args if auto_args is set
		if ($this->auto_args && isset($vars['args'])) {
			$regex = '`' . $this->auto_args_regex . '`';
			if(!preg_match_all($regex, $vars['args'], $auto_args)) {
				throw new RouteFailedException(sprintf('Unable to parse auto args with regex %s', $regex));
			}
			foreach ($auto_args[0] as $k => $v) {
				$args[$k] = $v;
			}
			unset($args['args']);
		}
		//test the variables using validator
		if (!empty($this->rules)) {
			$v = new Validator($args, $this->rules);
			$v->controller = $this->controller;
			$v->method = $this->method;
			if (!$v->validate()) {
				return false;
			}
		}
		if(!empty($args)) {
			$this->args = $args;
		}
		$this->result = self::PASSED;
		return true;
	}

	/**
	 * @return array An array with the controller class, method and
	 * arguments to run.
	 * @throws RouteUntestedException
	 * @throws RouteFailedException
	 */
	public function getAction() {
		if($this->result === self::PASSED) {
			return array($this->controller, $this->method, (array) $this->args);
		}
		if($this->result === self::UNTESTED) {
			throw new RouteUntestedException('Route untested, unable to get action.');
		}
		throw new RouteFailedException('Route failed, unable to get action.');
	}

	/**
	 * Return the result code of this Route:
	 *
	 * Route::PASSED if the Route has been tested and is passing.
	 * Route::UNTESTED if the Route has not been tested.
	 * Route::FAILURE_<reason> if the Route failed testing because of
	 * <reason>.
	 *
	 * @return int The result code.
	 */
	public function getResult() {
		return $this->result;
	}

}
