<?php

namespace neptune\core;

use neptune\core\Loader;
use neptune\view\View;
use neptune\http\Response;
use neptune\http\Request;
use neptune\validate\Validator;
use neptune\exceptions\NeptuneError;
use neptune\exceptions\MethodNotFoundException;
use neptune\exceptions\ArgumentMissingException;

/**
 * Handles an application request
 * and launches the required controller and action.
 */
class Dispatcher {
	const VARIABLE = '`:([a-zA-Z][a-zA-Z0-9]+)`';
	const VARIABLE_PATTERN = '(?P<\1>[^/]+)';
	const ARGS_PATTERN = '(?P<args>.+)';

	const ARGS_EXPLODE = 0;
	const ARGS_SINGLE = 1;

	protected static $instance;
	protected $routes = array();
	protected $names = array();
	protected $globals = array();
	protected $request;

	protected function __construct() {
		$this->request = Request::getInstance();
		$this->response = Response::getInstance();
		$this->globals = new Route('.*');
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function route($url, $controller = null, $method = null, $args = null) {
		$route = $this->globals;
		$route->controller($controller)->method($method)->args($args);
		$this->routes[$url] = $route;
		return $this->routes[$url];
	}

	public function globals() {
		return $this->globals;
	}

	public function catchAll($controller, $method ='index', $args = null) {
		$url = '.*';
		$this->routes[$url] = new Route($url, $controller, $method, $args);
		return $this->routes[$url];
	}

	public function clearRoutes() {
		$this->routes = array();
		return $this;
	}

	public function resetPointer() {
		$this->pointer = 0;
		return $this;
	}

	public function go() {
		//TODO: Check for a cached response to this exact request.
		foreach($this->routes as $k => $v) {
			if($v->test($k)) {
				//cache details for action
				$actions = $v->getAction();
				$format = $v->getFormat();
				if($this->runMethod($actions, $format)) {
					return true;
				}
			}

		}
		return false;
	}

	public static function missingArgsHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		$str = "Missing argument";
		if ($str === substr($errstr, 0, strlen($str))) {
			throw new ArgumentMissingException();
		} else {
			throw new NeptuneError($errno, $errstr, $errfile, $errline, $errcontext);
		}
	}

	protected function getAllowedResponseFormat() {
		if ($this->routes[$this->pointer - 1]['format']) {
			if (!$this->routes[$this->pointer - 1]['catchAll']) {
				return $this->request->format();
			}
		}
		return 'html';
	}

	protected function runMethod($actions, $format = 'html') {
		if (Loader::softLoad($actions[0])) {
			$c = new $actions[0]();
			try {
				set_error_handler('\neptune\core\Dispatcher::missingArgsHandler');
				ob_start();
				// if ($this->routes[$this->pointer - 1]['callHidden']) {
				// 	$body = $c->callHidden($actions[1], $actions[2]);
				// } else {
					$body = call_user_func_array(array($c, $actions[1]), $actions[2]);
				//}
				$other = ob_get_clean();
				restore_error_handler();
				if (!$this->response->getFormat()) {
					$this->response->format('html');
				}
				$this->response->sendHeaders();
				// if ($this->routes[$this->pointer - 1]['format']) {
				// 	if(!$this->formatBody($body)) {
				// 		echo $other;
				// 	}
				// } else {
				// 	echo $other;
				// }
				$this->response->body($body);
				$this->response->send();
			} catch (MethodNotFoundException $e) {
				restore_error_handler();
				return false;
			} catch (ArgumentMissingException $e) {
				restore_error_handler();
				return false;
			}
			restore_error_handler();
			return true;
		}
		return false;
	}

	protected function formatBody(&$body) {
		if($body instanceof View) {
			$view = 'neptune\\view\\' . ucfirst($this->response->getFormat()) . 'View';
			if(get_class($body) !== $view) {
				if (Loader::softLoad($view)) {
					$body = $view::load(null, $body->getValues());
				}
			}
		} else {
			$view = 'neptune\\view\\' . ucfirst($this->response->getFormat()) . 'View';
			if (Loader::softLoad($view)) {
				$body = $view::load(null, array($body));
			} else {
				return false;
			}
		}
		return true;
	}

	public function setRouteName($name, $url) {
		$this->names[$name] = $url;
	}

	public function getRouteUrl($name) {
		return isset($this->names[$name]) ? $this->names[$name] : null;
	}

}

?>
