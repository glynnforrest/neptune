<?php

namespace Neptune\File;

use Neptune\Exceptions\FileException;
use Neptune\Helpers\String;

use Symfony\Component\HttpFoundation\Request;

/**
 * UploadHandler
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class UploadHandler {

	/**
	 * The index in the $_FILES array for this UploadHandler instance.
	 * For uploads from another input field, create another instance.
	 */
	protected $name;
	protected $completed = array();
	protected $chunks = array();

	const CHUNK_START = 1;
	const CHUNK_PARTIAL = 2;
	const CHUNK_END = 3;

	public function __construct(Request $request, $name, $scramble_files = true) {
		$method = $request->getMethod();
		if($method != 'POST') {
			throw new \Exception("$method upload method not implemented.");
		}
		$this->checkErrors($name);
		$this->name = $name;
	}

	protected function checkErrors($name) {
		if(!isset($_FILES[$name])) {
			throw new FileException("File not submitted: $name");
		}
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

	/**
	 * Move all fully uploaded files into $directory. If an upload is
	 * a chunk of a file it will be either be appended to the relevant
	 * file or it will form the first chunk of a new file. This
	 * decision is made automatically by reading the Content-Range
	 * header.
	 */
	public function moveAll($directory) {
		if(!file_exists($directory)) {
			throw new FileException(
				"Destination upload directory $directory doesn't exist."
			);
		}
		$upload = $_FILES[$this->name];
		if(is_array($upload['tmp_name'])) {
			//more than one file was uploaded, process each
			foreach ($upload['tmp_name'] as $k => $tmp_name) {
				$file = array(
					'name' => $upload['name'][$k],
					'type' => $upload['type'][$k],
					'tmp_name' => $tmp_name,
					'error' => $upload['error'][$k],
					'size' => $upload['size'][$k],
				);
				$this->processFile($file, $directory);
			}
		} else {
			//one file uploaded
			$this->processFile($upload, $directory);
		}
		return true;
	}

	/**
	 * Move a given file to $directory, appending to an existing file
	 * if the upload is a chunk.
	 * @param array $file An array with information from $_FILES.
	 * @param string $directory The directory to move the file to.
	 */
	protected function processFile(array $file, $directory) {
		if(!is_uploaded_file($file['tmp_name'])) {
			throw new FileException($file['name'] . ' failed to upload.');
		}
		$chunked = $this->chunkStatus($file);
		switch ($chunked) {
		case self::CHUNK_START:
			$file_path = $this->getChunkUploadPath($file['name'], $directory);
			if(move_uploaded_file ($file['tmp_name'], $file_path)) {
				$file['path'] = $file_path;
				$this->chunks[] = $file;
				return true;
			} else {
				throw new FileException(
					'Failed to move ' . $file['tmp_name'] . ' to ' . $file_path);
			}
			break;
		case self::CHUNK_PARTIAL:
			$file_path = $this->getChunkUploadPath($file['name'], $directory);
			file_put_contents(
				$file_path, fopen($file['tmp_name'], 'r'), FILE_APPEND);
			$file['path'] = $file_path;
			$this->chunks[] = $file;
			break;
		case self::CHUNK_END:
			$file_path = $this->getChunkUploadPath($file['name'], $directory);
			file_put_contents(
				$file_path, fopen($file['tmp_name'], 'r'), FILE_APPEND);
			$new_file_path = $this->getFreeUploadPath($file['name'], $directory);
			rename($file_path, $new_file_path);
			$file['path'] = $new_file_path;
			$this->completed[] = $file;
			break;
		default:
			$file_path = $this->getFreeUploadPath($file['name'], $directory);
			if(move_uploaded_file ($file['tmp_name'], $file_path)) {
				$file['path'] = $file_path;
				$this->completed[] = $file;
				return true;
			} else {
				throw new FileException(
					'Failed to move ' . $file['tmp_name'] . ' to ' . $file_path);
			}
		}
		return true;
	}

	/**
	 * Create an available file path in $directory for $filename.
	 */
	protected function getFreeUploadPath($filename, $directory) {
		$padding = 0;
		$extension = $this->getExtensionFromPath($filename);
		while(true) {
			$path = $directory . md5($filename . $padding) . $extension;
			if(!file_exists($path)) {
				return $path;
			} else {
				$padding++;
			}
		}
	}

	/**
	 * Return the path name for the chunked upload of $file in $directory.
	 */
	protected function getChunkUploadPath($filename, $directory) {
		$extension = $this->getExtensionFromPath($filename);
		return $directory . 'chunk-' . md5($filename) . $extension;
	}

	/**
	 * Decide whether $file is a complete file or the beginning,
	 * middle or end of a chunked upload.
	 */
	protected function chunkStatus($file) {
		//look at the Content-Range header for signs of a chunked
		//upload, e.g.
		//Content-Range: bytes 0-50000/1000000
		$header = isset($_SERVER['HTTP_CONTENT_RANGE']) ?
			$_SERVER['HTTP_CONTENT_RANGE']: null;
		if(!$header) {
			return false;
		}
		$pieces = preg_split('/[^0-9]+/', $header);
		for ($i = 1; $i < 4; $i++) {
			if(!is_numeric($pieces[$i])) {
				throw new \Exception(
					"Malformed Content-Range header recieved: $header");
			}
		}
		$chunk_size = $pieces[2] - $pieces[1];
		$total_size = $pieces[3];
		if($chunk_size === $total_size) {
			return false;
		}
		if((int) $pieces[1] === 0) {
			return self::CHUNK_START;
		}
		if((int) $pieces[2] + 1 === (int) $pieces[3]) {
			return self::CHUNK_END;
		}
		return self::CHUNK_PARTIAL;
	}

	/**
	 * Get all files that were successfully processed.
	 * @return array An array of files, each of which are an array
	 * with the follwing format:.
	 *
	 * array (
	 * 'name' => 'example.mp3',
	 * 'type' => 'audio/mp3',
	 * 'tmp_name' => '/tmp/phpvREVWv',
	 * 'error' => 0,
	 * 'size' => 1493811,
	 * 'path' => '/full/path/to/example.mp3',
	 * )
	 */
	public function getUploadedFiles() {
		return array_merge($this->completed, $this->chunks);
	}

	/**
	 * Get all files that have only partially uploaded.
	 * @return array An array of files, each of which are an array
	 * with the follwing format:.
	 *
	 * array (
	 * 'name' => 'example.mp3',
	 * 'type' => 'audio/mp3',
	 * 'tmp_name' => '/tmp/phpvREVWv',
	 * 'error' => 0,
	 * 'size' => 1493811,
	 * 'path' => '/full/path/to/example.mp3',
	 * )
	 */
	public function getPartialFiles() {
		return $this->chunks;
	}

	/**
	 * Get all files that have uploaded completely.
	 * @return array An array of files, each of which are an array
	 * with the follwing format:.
	 *
	 * array (
	 * 'name' => 'example.mp3',
	 * 'type' => 'audio/mp3',
	 * 'tmp_name' => '/tmp/phpvREVWv',
	 * 'error' => 0,
	 * 'size' => 1493811,
	 * 'path' => '/full/path/to/example.mp3',
	 * )
	 */
	public function getCompletedFiles() {
		return $this->completed;
	}

	protected function getExtensionFromPath($path) {
		return substr($path, strrpos($path, '.'));
	}

}
