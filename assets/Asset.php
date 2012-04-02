<?php

namespace neptune\assets;

use neptune\core\Config;

/**
 * Asset
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Asset {

	protected $content;
	protected $source;
	protected $target;
	protected $web_target;
	protected $dependencies = array();
	protected $filters = array();

	public function __construct($source = null, $dependencies) {
		$this->dependencies = (array) $dependencies;
		if($source) {
			$this->setSource($source);
		}
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getContent() {
		if(!$this->content && $this->source) {
			$this->content = file_get_contents($this->source);
		}
		return $this->content;
	}

	public function getSource() {
		return $this->source;
	}

	public function setSource($source) {
		$pos = strpos($source, '#');
		if($pos) {
			$prefix = substr($source, 0, $pos);
			$this->source = Config::get($prefix . '#assets.dir') . substr($source, $pos + 1);
		} else {
			$this->source = Config::get('assets.dir') . $source;
		}
	}
	
	public function getTarget() {
		return $this->target;
	}

	public function setTarget($target) {
		$this->target = $target;
	}

	public function getWebTarget() {
		return $this->web_target;
	}

	public function setWebTarget($web_target) {
		$this->web_target = $web_target;
	}

	public function getDependencies() {
		return $this->dependencies;
	}

	public function prepare() {
		foreach($this->filters as $filter) {
			AssetsManager::getInstance()->applyFilter($this, $filter);
		}
		$this->web_target = $this->source;
		return true;
	}

}
?>
