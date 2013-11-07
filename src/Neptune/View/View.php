<?php

namespace Neptune\View;

use Neptune\Exceptions\ViewNotFoundException;
use Neptune\Core\Config;

class View {
	const EXTENSION = '.php';

	protected $vars = array();
	//complete file path to the view template.
	protected $view;

	protected function __construct() {
	}

	public function __set($key, $value) {
		return $this->set($key, $value);
	}

	public function set($key, $value) {
		$this->vars[$key] = $value;
	}

	public function __get($key) {
		return $this->get($key);
	}

	public function get($key) {
		return isset($this->vars[$key]) ? $this->vars[$key] : null;
	}

	public function __isset($key) {
		return isset($this->vars[$key]) ? true : false;
	}

	public function setValues(array $values=array()) {
		foreach ($values as $k => $v) {
			$this->vars[$k] = $v;
		}
		return $this;
	}

	public function getValues() {
		return $this->vars;
	}

	public function __toString() {
		try {
			$content = $this->render();
		} catch (\Exception $e) {
			return $e->getMessage();
		}
		return $content;
	}

	/**
	 * @return View
	 */
	public static function load($view, $vars = array(), $absolute = false) {
		$class = get_called_class();
		$me = new $class();
		$me->setView($view, $absolute);
		$me->setValues($vars);
		return $me;
	}

	public static function loadAbsolute($view, $vars = array()) {
		return self::load($view, $vars, true);
	}

	/**
	 * Set the template file to use for this View instance. If
	 * $absolute is true, $view will be treated as an absolute
	 * path. Otherwise, $view will be appended to the values of
	 * `dir.root` and `view.dir` in the neptune config file. If $view
	 * contains a prefix (content before a #) then the config file
	 * with that name will be used instead.
	 */
	public function setView($view, $absolute = false) {
		if(!$absolute) {
			$pos = strpos($view, '#');
			if($pos) {
				$name = substr($view, 0, $pos);
				$view = Config::load($name)->getRequired('view.dir') . substr($view, $pos + 1);
			} else {
				$view = Config::load()->getRequired('view.dir') . $view;
			}
			$view = Config::load('neptune')->getRequired('dir.root') . $view;
		}
		$view = $view . self::EXTENSION;
		$this->view = $view;
	}

	/**
	 * Get the file path of the template for this View instance.
	 */
	public function getView() {
		return $this->view;
	}

	public function render() {
		if (!file_exists($this->view)) {
			throw new ViewNotFoundException("Could not load view $this->view");
		}
		ob_start();
		include $this->view;
		return ob_get_clean();
	}

}
