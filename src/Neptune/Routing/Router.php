<?php

namespace Neptune\Routing;

use Neptune\Routing\Route;
use Neptune\Core\Neptune;
use Neptune\Routing\Url;
use Neptune\Routing\RouteNotFoundException;
use Neptune\Service\AbstractModule;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\HttpFoundation\Request;

/**
 * Router
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Router {

	const CACHE_KEY = 'Router.routes';

	protected $routes = array();
	protected $cache;
	protected $current_name;
    protected $current_module;
    protected $url;
    protected $unknown_count = 0;

	public function __construct(Url $url) {
        $this->url = $url;
	}

	public function setCache(Cache $driver) {
		$this->cache = $driver;
	}

	public function getCache() {
		return $this->cache;
	}

    protected function createRouteName()
    {
        if ($this->current_name) {
            return $this->current_name;
        }

        $count = $this->unknown_count;
        $this->unknown_count++;

        if($this->current_module) {
            return $this->current_module . ':_unknown_' . $count;
        }

        return '_unknown_' . $count;
    }

	/**
	 * Create a new Route for the Router to handle with $url.
	 */
	public function route($url, $controller = null, $action = null, array $args = array()) {
		//add a slash if the given url doesn't start with one
		if(substr($url, 0, 1) !== '/' && $url !== '.*') {
			$url = '/' . $url;
		}

        $name = $this->createRouteName();

        if (isset($this->routes[$name])) {
            throw new \Exception(sprintf('A route named "%s" already exists.', $name));
        }

		$this->routes[$name] = new Route($name, $url, $controller, $action, $args);
        $this->current_name = null;

		return $this->routes[$name];
	}

    /**
     * Load the routes for a module, using $prefix to namespace to
     * urls.
     *
     * @param AbstractModule $module The module
     * @param Neptune $neptune
     */
    public function routeModule(AbstractModule $module, Neptune $neptune)
    {
        //set the current module name for naming routes
        $this->current_module = $module->getName();

        $module->loadRoutes($this, $neptune);

        //reset the current module name
        $this->current_module = null;

        return true;
    }

    public function routeModules(Neptune $neptune)
    {
        foreach ($neptune->getModules() as $module) {
            $this->routeModule($module, $neptune);
        }
    }

	public function catchAll($controller, $method ='index', array $args = array()) {
		$url = '.*';
		return $this->name('neptune.catch_all')
            ->route($url, $controller, $method, $args)
            ->format(true);
	}

    /**
     * Give the next defined route a name.
     *
     * @param string $name The name of the route
     */
    public function name($name)
    {
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
	 * Return all defined routes in an array.
	 */
	public function getRoutes() {
		return array_values($this->routes);
	}

	public function clearRoutes() {
		$this->routes = array();
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
            $action =  $route->getControllerAction();
            $this->cacheRoutes($request, $action);
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
     * Cache the current Routes.
     */
    protected function cacheRoutes()
    {
        if ($this->cache) {
            $this->cache->save(static::CACHE_KEY, $this->routes);
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
        if (!$cached = $this->cache->fetch(static::CACHE_KEY)) {
            return false;
        }
        if (!is_array($cached)) {
            throw new \Exception("Cache value $key is not an array");
        }
        $this->routes = $cached;

        return $this->match($request);
    }

	/**
     * Get the url for a named route. If routing has been skipped due
     * to caching, this method will attempt to fetch the route names
     * from the cache.
	 */
	public function getNamedUrl($name) {
		if(empty($this->routes)) {
				throw new \Exception("No routes defined");
		}
		if(!isset($this->routes[$name])) {
			throw new \Exception("Unknown route '$name'");
		}
		return $this->routes[$name]->getUrl();
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
