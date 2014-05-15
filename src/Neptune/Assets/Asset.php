<?php

namespace Neptune\Assets;

use Neptune\Exceptions\FileException;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Asset
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Asset {

	protected $content;
	protected $filename;
	protected $mime_type;

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
	 * Save the content of this asset to $filename.
	 */
	public function saveFile($filename) {
		file_put_contents($filename, $this->getContent());
	}

	/**
	 * Get the filename of the currently loaded asset.
	 */
	public function getFilename() {
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

	/**
	 * Get the mime type of this asset, if it is loaded from a
	 * file. If the asset has been created manually, null will be
	 * returned unless the mime type has been explicitly set with
	 * setMimeType().
	 *
	 * @return null The mime type of the asset file as a string, or
	 * null for no mime type
	 */
	public function getMimeType() {
		if($this->mime_type) {
			return $this->mime_type;
		}
		if(!$this->filename) {
			return null;
		}
		//the mime type guessed isn't particularly useful for .js or
		//.css (text/plain). Since these are common asset types, look
		//for these manually first. This assumes that .js =>
		//application/javascript, and .css => text/css. Remember not
		//to trust the extension of user-uploaded files. Application
		//assets are most likely coming from the developer, so the
		//following is a fair compromise.
		if(substr($this->filename, -3) === '.js') {
			return 'application/javascript';
		}
		if(substr($this->filename, -4) === '.css') {
			return 'text/css';
		}
		return MimeTypeGuesser::getInstance()->guess($this->filename);
	}

	/**
	 * Set the mime type for this asset. This will override the mime
	 * type guessing used in getMimeType().
	 *
	 * @param string $type The mime type
	 */
	public function setMimeType($type) {
		$this->mime_type = $type;
	}

	/**
	 * Get the content length of this asset.
	 *
	 * @return int The content length, in bytes
	 */
	public function getContentLength() {
		return strlen($this->content);
	}

}
