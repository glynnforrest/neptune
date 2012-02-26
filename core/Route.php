<?php

namespace neptune\core;

/**
 * Route
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Route {

	const VARIABLE = '`:([a-zA-Z][a-zA-Z0-9]+)`';
	const VARIABLE_PATTERN = '(?P<\1>[^/]+)';
	const ARGS_PATTERN = '(?P<args>.+)';

	protected $regex, $controller, $function, $args, $method;
	protected $format, $transforms, $rules, $defaults, $catchAll;
	protected $callHidden, $argsFormat, $url;

	public function __construct($regex, $controller = null, $func = null, $args = null) {
		$this->regex = $this->generateRegex($regex);
		$this->controller = $controller;
		$this->func = $func;
		$this->args = $args;
	}

	protected function generateRegex($regex) {
		$regex = str_replace('(', '(?:', $regex);
		$regex = str_replace(')', ')?', $regex);
		$regex = preg_replace('`:args`', self::ARGS_PATTERN, $regex);
		$regex = preg_replace(self::VARIABLE, self::VARIABLE_PATTERN, $regex);
		return '`^' . $regex . '$`';
	}

	public function test($source) {
		if (!preg_match($this->regex, $source, $vars)) {
			return false;
		}
		return true;
	}

	public function regex($regex) {
		$this->regex = $this->generateRegex($regex);
	}

	public function controller($controller) {
		$this->controller = $controller;
	}

	public function func($func) {
		$this->func = $func;
	}

	public function args($args) {
		$this->args = $args;
	}

	public function method($method) {
		$this->method = $method;
	}

	public function format($format) {
		$this->format = $format;
	}

	public function transforms($transforms) {
		$this->transforms = $transforms;
	}

	public function rules($rules) {
		$this->rules = $rules;
	}

	public function defaults($defaults) {
		$this->defaults = $defaults;
	}

	public function catchAll($catchAll) {
		$this->catchAll = $catchAll;
	}

	public function callHidden($callHidden) {
		$this->callHidden = $callHidden;
	}

	public function argsFormat($argsFormat) {
		$this->argsFormat = $argsFormat;
	}

	public function url($url) {
		$this->url = $url;
	}
}
//sample usage
// $d = Dispatcher::getInstance();
// $d->route('/url')->controller('foo')->function('index');
?>

