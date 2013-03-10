<?php

namespace Neptune\Assets;

use Neptune\Core\Config;
use Neptune\Exceptions\FileException;

/**
 * Asset
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Asset {

	protected $content;
	protected $filters = array();

	public function __construct($file = null) {
		if($file) {
			$this->loadFile($file);
		}
	}

	public function loadFile($file) {
		if(is_readable($file)) {
			$this->content = file_get_contents($file);
		} else {
			throw new FileException('Asset file not found: ' . $file);
		}
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getContent() {
		return $this->content;
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
