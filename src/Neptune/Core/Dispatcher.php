<?php

namespace Neptune\Core;

use Neptune\View\View;
use Neptune\Http\Response;
use Neptune\Http\Request;
use Neptune\Cache\CacheFactory;
use Neptune\Exceptions\NeptuneError;
use Neptune\Exceptions\MethodNotFoundException;
use Neptune\Exceptions\ArgumentMissingException;

/**
 * Handles an application request
 * and launches the required controller and action.
 */
class Dispatcher {

	protected static $instance;
	protected $routes = array();
	protected $names = array();
	protected $globals;
	protected $request;

	protected function __construct() {
		$this->request = Request::getInstance();
		$this->response = Response::getInstance();
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Create a new Route for the Dispatcher to handle with $url.
	 */
	public function route($url, $controller = null, $method = null, $args = null) {
		//add a slash if the given url doesn't start with one
		if(substr($url, 0, 1) !== '/' && $url !== '.*') {
			$url = '/' . $url;
		}
		$route = clone $this->globals();
		$route->url($url)->controller($controller)->method($method)->args($args);
		$this->routes[$url] = $route;
		return $this->routes[$url];
	}

	public function globals() {
		if(!$this->globals) {
			$this->globals = new Route('.*');
		}
		return $this->globals;
	}

	/**
	 * Serve assets with Neptune\Controller\AssetsController at the
	 * config key assets.url.
	 */
	public function routeAssets() {
		$url = Config::load('neptune')->getRequired('assets.url');
		//add a slash if the given url doesn't start or end with one
		if(substr($url, 0, 1) !== '/') {
			$url = '/' . $url;
		}
		if(substr($url, -1, 1) !== '/') {
			$url .= '/';
		}
		$url = $url . ':args';
		$route = new Route($url);
		$route->controller('Neptune\\Controller\\AssetsController')
			  ->method('serveAsset')
			  ->format('any')
			  ->argsFormat(Route::ARGS_SINGLE);
		$this->routes[$url] = $route;
		return $this->routes[$url];
	}

	public function catchAll($controller, $method ='index', $args = null) {
		$url = '.*';
		return $this->route($url, $controller, $method, $args)->format('any');
	}

	public function clearRoutes() {
		$this->routes = array();
		return $this;
	}

	public function goCached($source = null) {
		if(!$source) {
			$source = $this->request->path();
		}
		$key = 'Router' . $source . $this->request->method();
		$cached = CacheFactory::getDriver()->get($key);
		if($cached) {
			if($this->runMethod($cached)) {
				return true;
			}
		}
		return false;
	}

	public function go($source = null) {
		if(!$source) {
			$source = $this->request->path();
		}
		foreach($this->routes as $k => $v) {
			if($v->test($source)) {
				$actions = $v->getAction();
				if($this->runMethod($actions)) {
					try {
						$cm = CacheFactory::getDriver();
						$key = 'Router' . $source . $this->request->method();
						$cm->set($key, $actions);
						$cm->set('Router.names', $this->names);
					} catch	(\Exception $e) {
					}
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

	protected function runMethod($actions) {
		if (class_exists($actions[0])) {
			$c = new $actions[0]();
			try {
				set_error_handler('\Neptune\Core\Dispatcher::missingArgsHandler');
				ob_start();
				$body = $c->_runMethod($actions[1], $actions[2]);
				$other = ob_get_clean();
				// if(!$body && !$other) {
				// 	return false;
				// }
				restore_error_handler();
				$format = $this->request->format();
				if (!$this->response->getFormat()) {
					$this->response->setFormat($format);
				}
				$this->response->sendHeaders();
				echo $other;
				$this->formatBody($body, $format);
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

	protected function formatBody(&$body, $format) {
		if($body instanceof View) {
			$view = 'Neptune\\View\\' . ucfirst($format) . 'View';
			if(get_class($body) !== $view) {
				if (class_exists($view)) {
					$body = $view::load(null, $body->getValues());
				}
			}
		} else {
			$view = 'Neptune\\View\\' . ucfirst($format) . 'View';
			if (class_exists($view)) {
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
		if(empty($this->names)) {
			$this->names = CacheFactory::getDriver()->get('Router.names');
		}
		return isset($this->names[$name]) ? $this->names[$name] : null;
	}

}

?>
