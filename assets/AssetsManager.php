<?php

namespace neptune\assets;

use neptune\helpers\Html;
use neptune\assets\Asset;

/**
 * AssetsManager
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsManager {

	protected static $instance;
	protected $js = array();	
	protected $css = array();
	protected $filters = array();

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
	}

	public function registerFilter($name, $class_name) {
		$this->filters[$name] = $class_name;
	}

	public function getJs($name) {
		return isset($this->js[$name]) ? $this->js[$name] : null;
	}

	public function getCss($name) {
		return isset($this->css[$name]) ? $this->css[$name] : null;
	}

	public function addJs($name, $src, $dependencies = array(), $options = array()) {
		$this->js[$name] = new Asset($src, $dependencies);
		$this->options['js' . $name] = $options;
	}

	public function addExternalJs($name, $src, $dependencies = array(), $options = array()) {
		$this->js[$name] = new Asset(null, $dependencies);
		$this->js[$name]->setWebTarget($src);
		$this->options['js' . $name] = $options;
	}


	public static function js() {
		$content ='';
		$me = self::getInstance();
		foreach($me->sort($me->js) as $k => $v) {
			$content .= Html::js($v, $me->options['js' . $k]);
		}
		return $content;
	}

	public function addCss($name, $src, $dependencies = array(), $options = array()) {
		$this->css[$name] = new Asset($src, $dependencies);
		$this->options['css' . $name] = $options;
	}

	public function addExternalCss($name, $src, $dependencies = array(), $options = array()) {
		$this->css[$name] = new Asset(null, $dependencies);
		$this->css[$name]->setWebTarget($src);
		$this->options['css' . $name] = $options;
	}

	public static function css() {
		$content = '';
		$me = self::getInstance();
		foreach($me->sort($me->css) as $k => $v) {
			$content .= Html::css($v, $me->options['css' . $k]);
		}
		return $content;

	}

	public function clear() {
		$this->js = array();
		$this->css = array();
	}

	protected function sort($assets) {
		$sorted = array();
		foreach($assets as $k => $v) {
			foreach($v->getDependencies() as $dep) {
				if($dep !== $k) {
					if(isset($assets[$dep])) {
						$this->addDeps($dep, $assets[$dep], $sorted, $assets);
					}
				}
			}
			$v->prepare();
			$sorted[$k] = $v->getWebTarget();
		}
		return $sorted;
	}

	protected function addDeps($key, $value, &$sorted, &$assets) {
		$deps = $value->getDependencies();
		if(!empty($deps)) {
			foreach($deps as $dep) {
				if($dep !== $key) {
					if(isset($assets[$dep])) {
						$this->addDeps($dep, $assets[$dep], $sorted, $assets);
					}
				}
			}
		}
		$sorted[$key] = $value;
		unset($assets[$key]);
	}

	public function applyFilter(&$asset, $filter) {
		if(!isset($this->filters[$filter])) {
			return false;
		}
		$filter = new $this->filters[$filter];
		$filter->filterAsset($asset);
		return true;
	}

}
?>
