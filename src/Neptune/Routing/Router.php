<?php

namespace Neptune\Routing;

use Neptune\Routing\Route;
use Neptune\Core\Config;
use Neptune\Cache\Driver\CacheDriverInterface;
use Neptune\Helpers\Url;
use Neptune\Routing\RouteNotFoundException;

use Symfony\Component\HttpFoundation\Request;

/**
 * Router
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Router {

	const CACHE_KEY_NAMES = 'Router.names';

	protected $config;
	protected $routes = array();
	protected $names = array();
	protected $globals;
	protected $matched_url;
	protected $cache;
	protected $current_name;
    protected $current_module;

	public function __construct(Config $config) {
		$this->config = $config;
	}

	public function setCacheDriver(CacheDriverInterface $driver) {
		$this->cache = $driver;
	}

	public function getCacheDriver() {
		return $this->cache;
	}

	/**
	 * Create a new Route for the Router to handle with $url.
	 */
	public function route($url, $controller = null, $method = null, $args = null) {
		//add a slash if the given url doesn't start with one
		if(substr($url, 0, 1) !== '/' && $url !== '.*') {
			$url = '/' . $url;
		}
		$route = clone $this->globals();
		$route->url($url)->controller($controller)->method($method)->args($args);
		$this->routes[$url] = $route;
		if(!is_null($this->current_name)) {
			$this->names[$this->current_name] = $url;
			$this->current_name = null;
		}
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
		$url = $this->config->getRequired('assets.url');
		//add a slash if the given url doesn't start or end with one
		if(substr($url, 0, 1) !== '/') {
			$url = '/' . $url;
		}
		if(substr($url, -1, 1) !== '/') {
			$url .= '/';
		}
		$url = $url . ':asset';
		$route = new Route($url);
		$route->controller('Neptune\\Controller\\AssetsController')
			  ->method('serveAsset')
			  ->format('any')
			  ->argsRegex('.+');
		$this->routes[$url] = $route;
		$this->names['neptune.assets'] = $url;
		return $this->routes[$url];
	}

    /**
     * Load all routes defined in the routes.php file in $module.
     * $prefix will be used as the url prefix if specified, otherwise
     * $module will be used.
     *
     * @param string $module The name of the module
     * @param string $prefix The prefix to use for route urls
     */
    public function routeModule($module, $prefix = null)
    {
        //if no url prefix is given, use the module name
        $prefix = $prefix ? $prefix : $module;

		//store current globals so they aren't used in
		//routes.php and can be restored later
		$old_globals = $this->globals;
		//reset globals so the module can define them
		$this->globals = null;
        //set the current module name so the name() method can use it
        $this->current_module = $module;

		//include routes.php file
        $routes_file = $this->config->getPath('modules.' . $module)
			. 'routes.php';
		$routes = include($routes_file);

		//routes.php should have a returned a function that we can
		//call with this Router instance as an argument. If not,
		//error out
		if(!is_callable($routes)) {
			throw new \Exception(
				$routes_file . ' does not return a callable function.');
		}
        //call the function, passing in this Router, the module name
        //and url prefix
        $routes($this, $module, $prefix);
        //reset the module name to null and the globals to what they were before
        $this->current_module = null;
        $this->globals = $old_globals;
        return true;
    }

	public function catchAll($controller, $method ='index', $args = null) {
		$url = '.*';
		$this->names['neptune.catch_all'] = $url;
		return $this->route($url, $controller, $method, $args)->format('any');
	}

	/**
	 * Give the next defined route a name.
	 */
	public function name($name) {
        if($this->current_module) {
            $name = $this->current_module . ':' . $name;
        }
        $this->current_name = $name;
		return $this;
	}

	/**
	 * Get the name that will be assigned to the next route.
	 */
	public function getName() {
		return $this->current_name;
	}

	/**
	 * Get a list of all named routes and their urls.
	 */
	public function getNames() {
		return $this->names;
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

	public function matchCached($path) {
		$key = 'Router' . $path;
		$cached = $this->cache->get($key);
		if($cached) {
			return $this->cached;
		}
		return $this->match($path);
	}

	public function match($pathinfo, $method = 'GET') {
		$request = Request::create($pathinfo);
		$request->setMethod($method);
		return $this->matchRequest($request);
	}

	public function matchRequest(Request $request) {
		foreach($this->routes as $url => $route) {
			if(!$route->test($request)) {
				continue;
			}
			$actions = $route->getAction();
			$this->matched_url = $url;
			$this->cacheResults($request, $actions);
			return $actions;
		}
		throw new RouteNotFoundException(sprintf('No route found that matches "%s"', $request->getPathInfo()));
	}

	protected function cacheResults(Request $request, $actions) {
		if($this->cache) {
			try {
				$path = $request->getPathInfo();
				$method = $request->getMethod();
				$this->cache->set('Router.' . $path . $method, $actions);
				$this->cache->set(self::CACHE_KEY_NAMES, $this->names);
			} catch	(\Exception $e) {
				//send failed cache event
			}
		}
	}

	public function getMatchedUrl() {
		return $this->matched_url;
	}

	/**
     * Get the url for a named route. If routing has been skipped due
     * to caching, this method will attempt to fetch the route names
     * from the cache.
	 */
	public function getNamedUrl($name) {
		if(empty($this->names)) {
			//no named routes have been defined
			//attempt to fetch names from cache
			if(!$this->cache || !$result = $this->cache->get(self::CACHE_KEY_NAMES)) {
				throw new \Exception("No named routes defined");
			}
			if(!is_array($result)) {
				throw new \Exception('Cache value \'' . self::CACHE_KEY_NAMES . '\' is not an array');
			}
			$this->names = $result;
		}
		if(!isset($this->names[$name])) {
			throw new \Exception("Unknown route '$name'");
		}
		return $this->names[$name];
	}

	/**
	 * Get the url of a route called $name.
	 *
	 * Substitute any variables in the route url with the $args
	 * array. If this array contains variables that aren't in the url,
	 * they will be added as GET parameters.
	 *
	 * @param string $name The name of the route
	 * @param array $args An array of keys and values to substitute
	 * @param string $protocol The protocol to use - default is http
	 *
	 * @return string The url of the route.
	 */
	public function url($name, $args = array(), $protocol = 'http') {
		$url = $this->getNamedUrl($name);
		//replace any variables in the route definition with supplied args
		if(preg_match_all('`:([a-zA-Z][a-zA-Z0-9]+)`', $url, $matches)) {
			foreach ($matches[1] as $m) {
				if(isset($args[$m])) {
					$url = str_replace(":{$m}", $args[$m], $url);
					unset($args[$m]);
				} else {
					$url = str_replace(":{$m}", null, $url);
				}
			}
			$url = str_replace('(', '', $url);
			$url = str_replace(')', '', $url);
			$url = rtrim($url, '/');
		}
		//append get variables using any args that are left
		if(!empty($args)) {
			$url .= '?' . http_build_query($args);
		}
		return Url::to($url, $protocol);
	}

}
