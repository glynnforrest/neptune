<?php

namespace neptune\file;

use neptune\exceptions\FileException;

/**
 * UploadHandler
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class UploadHandler {

	protected $name;
	protected $filename;
	protected $extension;
	protected $location;
	protected $scramble_length = 16;
	protected $scramble_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	protected $overwrite = false;
	protected $keep_extension = true;

	function __construct($name) {
		if (isset($_FILES[$name])) {
			$this->name = $name;
			$this->filename = $_FILES[$name]['name'];
			$this->location = $this->getDirectoryFromPath($_FILES[$name]['tmp_name']);
			$this->extension = $this->getExtensionFromPath($_FILES[$name]['name']);
		} else {
			throw new \Exception("Uploaded file not found: $name");
		}
	}

	protected function getFilenameFromPath($path) {
		return substr(strrchr($path, '/'), 1);
	}

	protected function getDirectoryFromPath($path) {
		return substr($path, 0, strrpos($path, '/') + 1);
	}

	protected function getExtensionFromPath($path) {
		return substr($path, strrpos($path, '.'));
	}

	public function getName() {
		return $this->name;
	}

	public function getFilename() {
		return $this->filename;
	}

	public function setFilename($filename) {
		if ($this->keep_extension) {
			$this->filename = $filename . $this->extension;
		} else {
			$this->filename = $filename;
		}
	}

	public function keepExtension() {
		$this->keep_extension = true;
	}

	public function loseExtension() {
		$this->keep_extension = false;
	}

	public function getExtension() {
		return $this->extension;
	}

	public function setExtension($ext) {
		$this->extension = $ext;
	}

	protected function scrambleFilename() {
		$max = strlen($this->scramble_chars) - 1;
		$name = '';
		for ($i = 0; $i < $this->scramble_length; $i++) {
			$index = rand(0, $max);
			$name .= $this->scramble_chars[$index];
		}
		if ($this->keep_extension) {
			$this->filename = $name . $this->extension;
		} else {
			$this->filename = $name;
		}
	}

	public function getTempFilename() {
		return $this->getFilenameFromPath($_FILES[$name]['tmp_name']);
	}

	public function getLocation() {
		return $this->location;
	}

	public function getFiletype() {
		return $_FILES[$this->name]['type'];
	}

	public function getFilesize() {
		return $_FILES[$this->name]['size'];
	}

	public function moveTo($directory) {
		if (file_exists($directory . $this->filename) && $this->overwrite == false) {
			throw new FileException($this->filename . ' already exists');
		}
		if (move_uploaded_file($_FILES[$this->name]['tmp_name'], $directory . $this->filename)) {
			$this->location = $directory;
			return true;
		}
		return false;
	}

	public function allowOverwrite() {
		$this->overwrite = true;
	}

	public function setScrambleOptions($chars, $len) {
		$this->scramble_chars = $chars;
		$this->scramble_length = $len;
	}

	public function moveToAndRename($directory, $filename) {
		$this->setFilename($filename);
		return $this->moveTo($directory);
	}

	public function moveToAndScramble($directory, $attempts = 10) {
		$this->scrambleFilename();
		for ($i = 0; $i < $attempts; $i++) {
			try {
				$this->moveTo($directory);
				return true;
			} catch (\neptune\exceptions\FileException $e) {
				if ($i + 1 === $attempts) {
					throw $e;
				} else {
					$this->scrambleFilename();
				}
			}
		}
	}

}

?>
