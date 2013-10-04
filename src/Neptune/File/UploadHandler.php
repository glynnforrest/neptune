<?php

namespace Neptune\File;

use Neptune\Exceptions\FileException;
use Neptune\Helpers\String;

/**
 * UploadHandler
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class UploadHandler {

	protected $name;
	protected $filename;
	protected $extension;
	protected $location;
	protected $overwrite = false;
	protected $scramble_chars = String::ALPHANUM;
	protected $scramble_length = 16;

	public function __construct($name) {
		$this->checkErrors($name);
		$this->name = $name;
		$this->filename = $_FILES[$name]['name'];
		$this->location = $this->getDirectoryFromPath($_FILES[$name]['tmp_name']);
		$this->extension = $this->getExtensionFromPath($_FILES[$name]['name']);
	}

	protected function checkErrors($name) {
		$code = $_FILES[$name]['error'];
		if($code === UPLOAD_ERR_OK) {
			return true;
		}
		switch ($code) {
		case UPLOAD_ERR_INI_SIZE:
			throw new FileException("File exceeds the maximum file size.");
		case UPLOAD_ERR_FORM_SIZE:
			throw new FileException("File exceeds the maximum file size.");
		case UPLOAD_ERR_PARTIAL:
			throw new FileException("File was only partially uploaded.");
		case UPLOAD_ERR_NO_FILE:
			throw new FileException("No file submitted.");
		case UPLOAD_ERR_NO_TMP_DIR:
			throw new FileException("Unable to find temporary location for file.");
		case UPLOAD_ERR_CANT_WRITE:
			throw new FileException("Failed to write file $name to disk.");
		case UPLOAD_ERR_EXTENSION:
			throw new FileException("A PHP extension prevented the upload from succeeding.");
		default:
			throw new FileException("The upload failed.");
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
		$this->filename = $filename;
	}

	public function scrambleFilename() {
		$this->filename = String::random($this->scramble_length,
										 $this->scramble_chars) . $this->extension;
	}

	public function setScrambleOptions($length, $characters = String::ALPHANUM) {
		$this->scramble_length = $length;
		$this->scramble_chars = $characters;
	}

	public function getExtension() {
		return $this->extension;
	}

	public function setExtension($ext) {
		$this->extension = $ext;
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
			} catch (\Neptune\Exceptions\FileException $e) {
				if ($i + 1 === $attempts) {
					throw $e;
				} else {
					$this->scrambleFilename();
				}
			}
		}
	}

}
