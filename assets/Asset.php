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
	protected $prefix;
	protected $file_name;
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

	public function addFilter($name) {
		if(!in_array($name, $this->filters)) {
			$this->filters[] = $name;
		}
	}

	public function getSource() {
		return $this->source;
	}

	protected function parseSource($source) {
		$pos = strpos($source, '#');
		if($pos) {
			$this->prefix = substr($source, 0, $pos);
			$this->file_name = substr($source, $pos + 1);
		} else {
			$this->prefix = '';
			$this->file_name = $source;
		}
	}

	public function setSource($source) {
		$this->parseSource($source);
		$this->source = Config::get($this->prefix . '#assets.source') . $this->file_name;
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
		$am = AssetsManager::getInstance();
		foreach($this->filters as $filter) {
			$am->applyFilter($this, $filter);
		}
		$this->target = Config::get('assets.target') . $this->file_name;
		file_put_contents($this->target, $this->getContent()); 
		$this->web_target = Config::get('assets.url') . $this->file_name;
		// $this->web_target = $this->source;
		return true;
	}

}
?>
