<?php

namespace Neptune\Routing;

use Neptune\Routing\Route;
use Neptune\Core\Neptune;
use Neptune\Helpers\Url;
use Neptune\Routing\RouteNotFoundException;
use Neptune\Service\AbstractModule;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\HttpFoundation\Request;

/**
 * Router
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Router {

	const CACHE_KEY_NAMES = 'Router.names';

	protected $routes = array();
	protected $names = array();
	protected $globals;
	protected $matched_url;
	protected $cache;
	protected $current_name;
    protected $current_module;
    protected $url;

	public function __construct(Url $url) {
        $this->url = $url;
	}

	public function setCache(Cache $driver) {
		$this->cache = $driver;
	}

	public function getCache() {
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
	 * given url.
	 */
	public function routeAssets($url) {
		//add a slash if the given url doesn't start or end with one
		if(substr($url, 0, 1) !== '/') {
			$url = '/' . $url;
		}
		if(substr($url, -1, 1) !== '/') {
			$url .= '/';
		}
		$url = $url . ':asset';
		$route = new Route($url);
		$route->controller('\\Neptune\\Controller\\AssetsController')
			  ->method('serveAsset')
			  ->format('any')
			  ->argsRegex('.+');
		$this->routes[$url] = $route;
		$this->names['neptune.assets'] = $url;
		return $this->routes[$url];
	}

    /**
     * Load the routes for a module, using $prefix to namespace to
     * urls. A module may be routed to multiple prefixes by supplying
     * an array instead of a string.
     *
     * @param AbstractModule $module The module
     * @param mixed $prefix The routing prefix or prefixes
     */
    public function routeModule(AbstractModule $module, $prefix)
    {
		//store current globals so they aren't used in
		//the module and can be restored later
		$old_globals = $this->globals;
		//reset globals so the module can define them
		$this->globals = null;
        //set the current module name so the name() method can use it
        $this->current_module = $module->getName();

        $prefixes = (array) $prefix;
        //create the routes for every prefix, passing in this Router
        //and the module name
        foreach ($prefixes as $prefix) {
            $module->routes($this, $prefix);
        }

        //reset the module name to null and the globals to what they were before
        $this->current_module = null;
        $this->globals = $old_globals;

        return true;
    }

    public function routeModules(Neptune $neptune)
    {
        foreach ($neptune->getModules() as $name => $module) {
            if (!$prefix = $neptune->getRoutePrefix($name)) {
                continue;
            }
            $this->routeModule($module, $prefix, $name);
        }
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

    /**
     * Match a Request with a registered route and return a controller
     * action. If no route is found a RouteNotFoundException will be
     * thrown. If a cache has been defined the result will be cached.
     *
     * @param Request $request The Request to match
     * @return array An array containing the controller, method, and
     * an array of arguments.
     */
    public function match(Request $request)
    {
        foreach($this->routes as $url => $route) {
            if(!$route->test($request)) {
                continue;
            }
            $this->matched_url = $url;
            $action =  $route->getAction();
            $this->cacheAction($request, $action);
            return $action;
        }
        throw new RouteNotFoundException(sprintf('No route found that matches "%s"', $request->getPathInfo()));
    }

    /**
     * Get a key used to identify a request uniquely by it's path info
     * and http method.
     */
    protected function getRequestCacheKey(Request $request)
    {
        $path = $request->getPathInfo();
        $method = $request->getMethod();
        return 'Router.' . $path . $method;
    }

    /**
     * Cache the action used for a particular request.
     */
    protected function cacheAction(Request $request, array $action)
    {
        if ($this->cache) {
            $this->cache->save($this->getRequestCacheKey($request), $action);
            $this->cache->save(self::CACHE_KEY_NAMES, $this->names);
        }
    }

    /**
     * Match a Request with a registered route and return a controller
     * action by looking in the cache. False will be returned if no
     * route is found.
     *
     * @param Request $request The Request to match
     * @return array An array containing the controller, method, and
     * an array of arguments, or false on failure.
     */
    public function matchCached(Request $request)
    {
        if (!$this->cache) {
            return false;
        }
        $cached = $this->cache->fetch($key = $this->getRequestCacheKey($request));
        if ($cached) {
            if (!is_array($cached)) {
                throw new \Exception("Cache value $key is not an array");
            }
            return $cached;
        }
        return false;
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
			if(!$this->cache || !$result = $this->cache->fetch(self::CACHE_KEY_NAMES)) {
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
		return $this->url->to($url, $protocol);
	}

}
