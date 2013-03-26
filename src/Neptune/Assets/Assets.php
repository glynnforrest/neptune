<?php

namespace Neptune\Assets;

use Neptune\Helpers\Html;
use Neptune\Core\Config;

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
		$this->js[$name] = array('src' => str_replace('#', '%23', $src),
			'deps' => (array) $dependencies,
			'opts' => (array) $options);
	}

	public function removeJs($name) {
		unset($this->js[$name]);
	}

	public static function js() {
		$content ='';
		$me = self::getInstance();
		foreach($me->sort($me->js) as $k => $v) {
			$content .= Html::js($v, $me->js[$k]['opts']);
		}
		return $content;
	}

	public function addCss($name, $src, $dependencies = array(), $options = array()) {
		$this->css[$name] = array('src' => str_replace('#', '%23', $src),
			'deps' => (array) $dependencies,
			'opts' => (array) $options);
	}

	public function removeCss($name) {
		unset($this->css[$name]);
	}

	public static function css() {
		$content = '';
		$me = self::getInstance();
		foreach($me->sort($me->css) as $k => $v) {
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
			if(strpos($v['src'], '://')) {
				$sorted[$k] = $v['src'];
			} else {
				$sorted[$k] = Config::load()->get('assets.url') . $v['src'];
			}
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
		$sorted[$key] = $value['src'];
		unset($assets[$key]);
	}
}
