<?php

namespace neptune\helpers;

use neptune\helpers\Html;

/**
 * Assets
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Assets {

	protected static $instance;
	protected static $js = array();	
	protected static $css = array();

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function addJs($name, $src, $dependencies = array(), $options = array()) {
		self::$js[$name] = array('src' => $src,
			'deps' => (array) $dependencies,
			'opts' => (array) $options);
	}

	public static function js() {
		$content ='';
		foreach(self::getInstance()->sort(self::$js) as $k => $v) {
			$content .= Html::js($v, self::$js[$k]['opts']);
		}
		return $content;
	}

	public static function addCss($href, $options = array()) {
		self::$css[] = array('href' => $href,
			'opts' => (array) $options);
	}

	public static function css() {
		$content = '';
		foreach(self::$css as $k => $v) {
			$content .= Html::css($v['href'], $v['opts']);
		}
		return $content;

	}

	public static function clear() {
		self::$js = array();
		self::$css = array();
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
