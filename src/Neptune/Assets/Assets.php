<?php

namespace Neptune\Assets;

use Reform\Helper\Html;
use Neptune\Helpers\Url;
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

	/**
	 * Return src with hashes stripped and cache busting added if
	 * assets.cache_bust is set, and src is not an external
	 * url.
	 */
	protected function createSrc($src) {
		if(Config::load()->get('assets.cache_bust') && !strpos($src, '://')) {
			return str_replace('#', '/', $src) . '?' . md5(uniqid());
		}
		return str_replace('#', '/', $src);
	}

	public function addJs($name, $src, $dependencies = array(), $options = array()) {
		$this->js[$name] = array(
			'src' => $this->createSrc($src),
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
			$content .= Html::js(Url::to($v), $me->js[$k]['opts']);
		}
		return $content;
	}

	public function addCss($name, $src, $dependencies = array(), $options = array()) {
		$this->css[$name] = array(
			'src' => $this->createSrc($src),
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
			$content .= Html::css(Url::to($v), $me->css[$k]['opts']);
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
			$sorted[$k] = $this->createUrl($v['src']);
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
		$sorted[$key] = $this->createUrl($value['src']);
		unset($assets[$key]);
	}

	protected function createUrl($src) {
		if(substr($src, 0, 1) === '/' || strpos($src, '://')) {
			return $src;
		} else {
			return Config::load()->get('assets.url') . $src;
		}
	}

}
