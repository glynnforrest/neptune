<?php

namespace Neptune\Helpers;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestHelper
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RequestHelper {

	protected $request;

	public function __construct(Request $request) {
		$this->request = $request;
	}

	public function setRequest(Request $request) {
		$this->request = $request;
	}

	public function getRequest() {
		return $this->request;
	}

	/**
	 * Get the best format suitable to respond to the request.
	 *
	 * If Accept headers are not set explicitly then the extension of
	 * the path will assumed to be the preferred format. If Accept
	 * headers are set then they will be used as the format of the
	 * request.
	 */
	public function getBestFormat() {
		$path = $this->request->getPathInfo();
		//remove trailing slashes if path is not one character, i.e. /
		if(strlen($path) > 1) {
			$path = rtrim($path, '/');
		}
		if(!$dot = strrpos($path, '.')) {
			return 'html';
		}
		//check there is no / after the dot
		if(strpos($path, '/', $dot)) {
			//there is a / after the dot, so it can't be treated
			//as the start of a format. bail out with html as the
			//default.
			return 'html';
		}
		if ($dot != strlen($path) - 1) {
			return substr($path, $dot + 1);
		}
		return 'html';
	}

	/**
	 * Get the path of a request without the extension.
	 */
	public function getBarePath() {
		$path = $this->request->getPathInfo();
		//remove trailing slashes if path is not one character, i.e. /
		if(strlen($path) > 1) {
			$path = rtrim($path, '/');
		}
		if(!$dot = strrpos($path, '.')) {
			return $path;
		}
		if (!strpos($path, '/', $dot)) {
			$path = substr($path, 0, $dot);
		}
		//strip trailing slashes after removing the extension
		return rtrim($path, '/');
	}

}
