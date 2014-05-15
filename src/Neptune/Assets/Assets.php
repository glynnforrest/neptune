<?php

namespace Neptune\Assets;

use Reform\Helper\Html;
use Neptune\Helpers\Url;
use Neptune\Config\ConfigManager;

/**
 * Assets
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Assets {

    protected $config;
    protected $url;
	protected $js = array();
	protected $css = array();

	public function __construct(ConfigManager $manager, Url $url) {
        $this->config = $manager;
        $this->url = $url;
	}

	/**
	 * Return src with hashes stripped and cache busting added if
	 * assets.cache_bust is set, and src is not an external
	 * url.
	 */
	protected function createSrc($src) {
		if($this->config->load()->get('assets.cache_bust') && !strpos($src, '://')) {
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

	public function js() {
		$content ='';
		foreach($this->sort($this->js) as $k => $v) {
			$content .= Html::js($this->url->to($v), $this->js[$k]['opts']);
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

	public function css() {
		$content = '';
		foreach($this->sort($this->css) as $k => $v) {
			$content .= Html::css($this->url->to($v), $this->css[$k]['opts']);
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
			return $this->config->load()->get('assets.url') . $src;
		}
	}

}
