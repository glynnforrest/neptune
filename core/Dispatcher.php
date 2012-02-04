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
	protected $pointer = 0;
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
	 *
	 * @param type $url
	 * @param type $options 
	 * method - 'get', 'post', 'get|post'
	 * controller
	 * transforms - array('controller' => '\app\controller\{ucfirst:controller}Controller')
	 * rules (create a validator) - array('id' => 'num', 'category' => 'alpha|required')
	 * defaults (defaults of other variables) - array('id' => 1, 'category' => 'foo')
	 * function
	 * args
	 * format - 'html', 'xml', 'html|json'
	 */
	public function route($url, array $options = array()) {
		$options = $this->mergeOptions($this->globals, $options);
		$this->routes[] = array(
			'regex' => $this->generateRegex($url),
			'controller' => isset($options['controller']) ? $options['controller'] : null,
			'function' => isset($options['function']) ? $options['function'] : null,
			'args' => isset($options['args']) ? $options['args'] : null,
			'method' => isset($options['method']) ? explode('|', strtoupper($options['method'])) : null,
			'format' => isset($options['format']) ? explode('|', $options['format']) : null,
			'transforms' => isset($options['transforms']) ? $options['transforms'] : null,
			'rules' => isset($options['rules']) ? $options['rules'] : null,
			'defaults' => isset($options['defaults']) ? $options['defaults'] : null,
			'catchAll' => isset($options['catchAll']) ? true : null,
			'callHidden' => isset($options['callHidden']) ? true : null,
			'argsFormat' => isset($options['argsFormat']) ? $options['argsFormat'] : self::ARGS_EXPLODE,
			'url' => $url,
		);
		if(isset($options['name'])) {
			end($this->routes);
			$this->names[$options['name']] = key($this->routes);
		}
	}

	protected function mergeOptions($defaults, $overrides) {
		foreach ($overrides as $k => $v) {
			if (isset($defaults[$k]) && is_array($v)) {
				$defaults[$k] = $this->mergeOptions($defaults[$k], $overrides[$k]);
			} else {
				$defaults[$k] = $v;
			}
		}
		return $defaults;
	}

	protected function generateRegex($url) {
		$url = str_replace('(', '(?:', $url);
		$url = str_replace(')', ')?', $url);
		$url = preg_replace('`:args`', self::ARGS_PATTERN, $url);
		$url = preg_replace(self::VARIABLE, self::VARIABLE_PATTERN, $url);
		return '`^' . $url . '$`';
	}

	public function catchAll($controller, $function ='index', $args = null) {
		$this->route('.*', array('controller' => $controller,
			'function' => $function,
			'args' => $args,
			'format' => 'any',
			'catchAll' => true
		));
	}

	/** tests a single route. If everything passes, returns an array with 
	 * namespaced controller, function and args ready to be called.
	 */
	protected function testRoute($rule) {
		//Check if the regex matches.
		if (!preg_match($rule['regex'], $this->request->path(), $vars)) {
			return false;
		}
		//Check if the request method is supported by this route.
		if ($rule['method']) {
			if (!in_array(strtoupper($this->request->method()), $rule['method'])) {
				return false;
			}
		}
		//Check if the format requested is supported by this route.
		if ($rule['format']) {
			if (!in_array($this->request->format(), $rule['format'])) {
				if (!in_array('any', $rule['format'])) {
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
			$vars['controller'] = $rule['controller'];
		}
		if (!isset($vars['function'])) {
			$vars['function'] = $rule['function'];
		}
		//process the transforms.
		if (isset($rule['transforms']) && is_array($rule['transforms'])) {
			foreach ($rule['transforms'] as $k => $v) {
				if (isset($vars[$k])) {
					$vars[$k] = $v($vars[$k]);
				}
			}
		}
		$controller = $vars['controller'];
		unset($vars['controller']);
		$function = $vars['function'];
		unset($vars['function']);
		//should have a controller and function by now.
		if (!$controller | !$function) {
			return false;
		}
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
		if (isset($rule['args']) && is_array($rule['args'])) {
			foreach ($rule['args'] as $name => $value) {
				if (!isset($args[$name])) {
					$args[$name] = $value;
				}
			}
		}
		//Gather numerically indexed args for auto rules
		if (isset($vars['args'])) {
			switch ($rule['argsFormat']) {
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
		if (isset($rule['rules'])) {
			$v = new Validator($args, $rule['rules']);
			if (!$v->validate())
				return false;
		}
		return array('controller' => $controller,
			'function' => $function,
			'args' => $args
		);
	}

	public function globals(array $options) {
		$this->globals = $options;
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
		$this->resetPointer();
		while ($vars = $this->getNextAction()) {
			if ($this->runAction($vars['controller'], $vars['function'], $vars['args'])) {
				return true;
			}
		}
		return false;
	}

	public function getNextAction() {
		$count = count($this->routes);
		while ($this->pointer < $count) {
			$rule = $this->routes[$this->pointer];
			$vars = $this->testRoute($rule);
			$this->pointer++;
			if ($vars) {
				return $vars;
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

	protected function runAction($controller, $method, $args = array()) {
		if (Loader::softLoad($controller)) {
			$c = new $controller();
			try {
				set_error_handler('\neptune\core\Dispatcher::missingArgsHandler');
				ob_start();
				if ($this->routes[$this->pointer - 1]['callHidden']) {
					$body = $c->callHidden($method, $args);
				} else {
					$body = call_user_func_array(array($c, $method), $args);
				}
				$other = ob_get_clean();
				restore_error_handler();
				if (!$this->response->getFormat()) {
					$this->response->format($this->getAllowedResponseFormat());
				}
				$this->response->sendHeaders();
				if ($this->routes[$this->pointer - 1]['format']) {
					if(!$this->formatBody($body)) {
						echo $other;
					}
				} else {
					echo $other;
				}
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

	public function getRouteUrl($name) {
		if(array_key_exists($name, $this->names)) {
			return $this->routes[$this->names[$name]]['url'];
		}
		return null;
	}

}

?>
