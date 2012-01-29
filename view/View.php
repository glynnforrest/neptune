<?php

namespace neptune\view;

use neptune\exceptions\ViewNotFoundException;
use neptune\core\Config;
use neptune\core\Neptune;

class View {
	const EXTENSION = '.php';

	protected $vars = array();
	protected $file;

	protected function __construct() {
	}

	public function __set($name, $value) {
		$this->vars[$name] = $value;
	}

	public function __isset($name) {
		if (isset($this->vars[$name])) {
			return true;
		} elseif (Neptune::get($name)) {
			return true;
		} else {
			return false;
		}
	}

	public function __get($name) {
		return isset($this->vars[$name]) ? $this->vars[$name] :
			Neptune::get($name);
	}

	public function __toString() {
		try {
			$content = $this->render();
		} catch (\Exception $e) {
			return $e->getMessage();
		}
		return $content;
	}

	public function set(array $values=array()) {
		foreach ($values as $k => $v) {
			$this->vars[$k] = $v;
		}
		return $this;
	}

	public function getVars() {
		return $this->vars;
	}

	/**
	 * @return View 
	 */
	public static function load($view, $vars = array()) {
		$view = Config::getRequired('view_dir') . $view;
		return self::loadAbsolute($view, $vars);
	}

	public static function loadAbsolute($view, $vars = array()) {
		$class = get_called_class();
		$me = new $class();
		$me->file = $view . self::EXTENSION;
		$me->set($vars);
		return $me;
	}

	public function render() {
		if (!file_exists($this->file)) {
			throw new ViewNotFoundException("Could not load view file $this->file");
		}
		ob_start();
		include $this->file;
		return ob_get_clean();
	}

}

?>
