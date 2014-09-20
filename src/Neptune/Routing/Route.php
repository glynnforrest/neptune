<?php

namespace Neptune\Routing;

use Neptune\Routing\RouteUntestedException;
use Neptune\Routing\RouteFailedException;

use Symfony\Component\HttpFoundation\Request;

/**
 * Route
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Route {

    const UNTESTED = 1;
    const PASSED = 2;
    const FAILURE_URL = 3;
    const FAILURE_CONTROLLER = 4;
    const FAILURE_ACTION = 5;
    const FAILURE_FORMAT = 6;
    const FAILURE_METHOD = 7;
    const FAILURE_VALIDATION = 8;

    protected $name;
	protected $url;
	protected $controller;
    protected $action;
    protected $processed_args = [];
    protected $default_args = [];
    protected $transforms = [];
    protected $rules = [];
    protected $format;
    protected $methods = [];
    protected $status;
	protected $args_regex = '[^/.]+';
	protected $auto_args;
	protected $auto_args_regex = '[^/.]+';

	public function __construct($name, $url, $controller = null, $action = null, array $args = array()) {
        $this->name = $name;
		$this->url = $url;
		$this->controller = $controller;
		$this->action = $action;
		$this->default_args = $args;
		$this->status = self::UNTESTED;
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

	public function action($action) {
        $this->action = $action;

		return $this;
	}

	public function args(array $args) {
        $this->default_args = $args;
		return $this;
	}

	public function method($methods)
    {
        $this->methods = array_map(function($method) {
            return strtoupper($method);
        }, (array) $methods);

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

	public function rules(array $rules) {
		$this->rules = $rules;
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
        //controller
		if (!$this->controller) {
			$this->status = self::FAILURE_CONTROLLER;
			return false;
		}

        //action
		if (!$this->action) {
            //allow if controller is callable?
			$this->status = self::FAILURE_ACTION;
			return false;
		}

		//method
		if ($this->methods) {
			if (!in_array($request->getMethod(), $this->methods)) {
				$this->status = self::FAILURE_METHOD;
				return false;
			}
		}

        //url
		$path = $request->getPathInfo();
        //for convenience, trim / off the end of the url
		if(strlen($path) > 1) {
			$path = rtrim($path, '/');
		}
		if (!preg_match($this->getRegex(), $path, $vars)) {
			$this->status = self::FAILURE_URL;
			return false;
		}

        //format
        $format = isset($vars['_format']) ? $vars['_format'] : 'html';
        if ($this->format) {
            if (!in_array($format, $this->format) && !in_array('any', $this->format)) {
                    $this->status = self::FAILURE_FORMAT;
                    return false;
            }
        } else {
            if ($format !== 'html') {
                $this->status = self::FAILURE_FORMAT;
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
		$args = [];
		//gather named variables from regex
		foreach ($vars as $k => $v) {
			if (!is_numeric($k)) {
				$args[$k] = $vars[$k];
			}
		}

        $args = array_merge($this->default_args, $args);

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
            foreach ($this->rules as $name => $regex) {
                if (!preg_match('`' . $regex . '`', $args[$name])) {
                    return false;
                }
            }
		}
		if(!empty($args)) {
			$this->processed_args = $args;
		}
		$this->status = self::PASSED;
		return true;
	}

    /**
     *  Get the controller class, action and arguments to run. An
     *  exception will be thrown if the route is untested or has
     *  failed testing.
     *
     * @return array The action
     */
    public function getControllerAction()
    {
        if($this->status === self::PASSED) {
            return [$this->controller, $this->action, $this->processed_args];
        }
        if($this->status === self::UNTESTED) {
            throw new RouteUntestedException(sprintf('Route "%s" is untested, unable to get controller action.', $this->name));
        }
        throw new RouteFailedException(sprintf('Route "%s" failed, unable to get controller action.', $this->name));
    }

	/**
	 * Return the status code of this Route:
	 *
	 * Route::PASSED if the Route has been tested and is passing.
	 * Route::UNTESTED if the Route has not been tested.
	 * Route::FAILURE_<reason> if the Route failed testing because of
	 * <reason>.
	 *
	 * @return int The result code.
	 */
    public function getStatus()
    {
        return $this->status;
    }

}
