<?php

namespace neptune\assets;

use neptune\core\Config;
use neptune\exceptions\FileException;

/**
 * Asset
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Asset {

	protected $content;
	protected $filters = array();

	public function __construct($file = null) {
		if($file) {
		   if(is_readable($file)) {
			$this->content = file_get_contents($file);
		   } else {
			   throw new FileException('Asset file not found: ' . $file);
		   }
		}
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getContent() {
		return $this->content;
	}

	public function filterContent() {
		$a = Assets::getInstance();
		foreach($this->filters as $filter) {
			$a->applyFilter($this, $filter);
		}
		return true;
	}

	public function addFilter($name) {
		if(!in_array($name, $this->filters)) {
			$this->filters[] = $name;
		}
	}

	public function getFilters() {
		return $this->filters;
	}
}
?>
