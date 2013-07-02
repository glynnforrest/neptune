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

	public function __construct($file = null) {
		if($file) {
			$this->loadFile($file);
		}
	}

	/**
	 * Set the content of this asset to the contents of $file.
	 */
	public function loadFile($file) {
		if(is_readable($file)) {
			$this->content = file_get_contents($file);
			$this->filename = $file;
		} else {
			throw new FileException('Asset file not found: ' . $file);
		}
	}

	/**
	 * Get the filename of the currently loaded asset.
	 */
	public function getFileName() {
		return $this->filename;
	}

	/**
	 * Set the content of this asset to $content.
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Get the content of this asset.
	 */
	public function getContent() {
		return $this->content;
	}

}
