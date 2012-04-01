<?php

namespace neptune\helpers;

use neptune\helpers\Html;
use neptune\core\Config;

/**
 * Assets
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Assets {

	protected static $instance;
	protected $js = array();	
	protected $css = array();

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
	}

	public function addJs($name, $src, $dependencies = array(), $options = array()) {
		$this->js[$name] = array('src' => $src,
			'deps' => (array) $dependencies,
			'opts' => (array) $options);
	}

	public static function js() {
		$content ='';
		$me = self::getInstance();
		foreach(self::getInstance()->sort($me->js) as $k => $v) {
			$content .= Html::js($v, $me->js[$k]['opts']);
		}
		return $content;
	}

	public function addCss($name, $src, $dependencies = array(), $options = array()) {
		$this->css[$name] = array('src' => $src,
			'deps' => (array) $dependencies,
			'opts' => (array) $options);
	}

	protected function buildFileName($src) {
		$pos = strpos($src, '#');
		if($pos) {
			$name = substr($src, 0, $pos);
			$src = Config::getRequired($name . '#assets.dir') . substr($src, $pos + 1);
		} else {
			$src = Config::getRequired('assets.dir') . $src;
		}
		return $src;
	}

	public static function css() {
		$content = '';
		$me = self::getInstance();
		foreach(self::getInstance()->sort($me->css) as $k => $v) {
			$content .= Html::css($v, $me->css[$k]['opts']);
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
			foreach($v['deps'] as $dep) {
				if($dep !== $k) {
					if(isset($assets[$dep])) {
						$this->addDeps($dep, $assets[$dep], $sorted, $assets);
					}
				}
			}
			$sorted[$k] = $v['src'];
		}
		return $sorted;
	}

	protected function addDeps($key, $value, &$sorted, &$assets) {
		if(!empty($value['deps'])) {
			foreach($value['deps'] as $k => $dep) {
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

}
?>
