<?php

namespace Neptune\Core;

use Neptune\View\View;
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
	protected $matched_url;
	protected $other;
	protected $prefix;

	protected function __construct() {
		$this->request = Request::getInstance();
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
		//substitute prefix as required
		if($this->prefix) {
			$url = str_replace(':prefix', $this->prefix, $url);
		}
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

	/**
	 * Load all routes defined in the routes.php file in $module_name.
	 * $prefix will be used as the url prefix if specified, otherwise
	 * $module_name will be used.
	 */
	public function routeModule($module_name, $prefix = null) {
		//store current globals and prefix so they aren't used in
		//routes.php and can be restored later
		$old_globals = $this->globals;
		$old_prefix = $this->prefix;
		//reset globals so the module can define them
		$this->globals = null;

		// set prefix as the one we've been given, or otherwise the
		// name of the module
		$this->setPrefix($prefix ? $prefix : $module_name);

		//include routes.php file
		$neptune = Config::load('neptune');
		$routes_file = $neptune->getRequired('dir.root')
			. $neptune->getRequired('modules.' . $module_name)
			. 'routes.php';
		$routes = include($routes_file);

		//routes.php should have a returned a function that we can
		//call with this Dispatcher instance as an argument. If not,
		//error out
		if(!is_callable($routes)) {
			throw new \Exception(
				$routes_file . ' does not return a callable function.');
		}
		$routes($this);
		//reset the prefix name and the globals as what they were before
		$this->setPrefix($old_prefix);
		$this->globals = $old_globals;
		return true;
	}

	public function catchAll($controller, $method ='index', $args = null) {
		$url = '.*';
		return $this->route($url, $controller, $method, $args)->format('any');
	}

	/**
	 * Return all defined routes in an array.
	 */
	public function getRoutes() {
		return array_values($this->routes);
	}

	public function clearRoutes() {
		$this->routes = array();
		return $this;
	}

	public function clearGlobals() {
		$this->globals = false;
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
				$this->matched_url = $k;
				$response = $this->runMethod($actions);
				if($response) {
					try {
						$cm = CacheFactory::getDriver();
						$key = 'Router' . $source . $this->request->method();
						$cm->set($key, $actions);
						$cm->set('Router.names', $this->names);
					} catch		(\Exception $e) {
					}
					return $response;
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
				//$body is the return from the controller. $other is
				//anything captured by output buffering, like echo and
				//print.
				$body = $c->_runMethod($actions[1], $actions[2]);
				$this->other = ob_get_clean();
				//return false if there is no $body or $other. If
				//there is no $body but $other exists, use that as the
				//response.
				if(!$body) {
					if(!$this->other) {
						restore_error_handler();
						return false;
					}
					restore_error_handler();
					return $this->other;
				}
				restore_error_handler();
				return $body;
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

	public function setRouteName($name, $url) {
		$this->names[$name] = $url;
	}

	public function getRouteUrl($name) {
		if(empty($this->names)) {
			$this->names = CacheFactory::getDriver()->get('Router.names');
		}
		return isset($this->names[$name]) ? $this->names[$name] : null;
	}

	public function getMatchedUrl() {
		return $this->matched_url;
	}

	public function getOther() {
		return $this->other;
	}

	/**
	 * Set the prefix on all future routes. :prefix is substituted with the
	 * prefix string in the route url.
	 */
	public function setPrefix($prefix) {
		//remove leading and trailing slashes if present
		if(substr($prefix, 0, 1) == '/') {
			$prefix = substr($prefix, 1);
		}
		if(substr($prefix, -1) == '/') {
			$prefix = substr($prefix, 0, -1);
		}
		$this->prefix = $prefix;
	}

	/**
	 * Get the prefix for route urls.
	 **/
	public function getPrefix() {
		return $this->prefix;
	}
}
