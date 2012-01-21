<?php

namespace neptune\http;

use neptune\helpers\Url;

/**
 * Response
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Response {

	protected static $headers = array();
	protected static $body;
	protected static $status_code = 200;
	protected static $status_codes = array(
		 100 => 'Continue',
		 101 => 'Switching Protocols',
		 102 => 'Processing',
		 200 => 'OK',
		 201 => 'Created',
		 202 => 'Accepted',
		 203 => 'Non-Authoritative Information',
		 204 => 'No Content',
		 205 => 'Reset Content',
		 206 => 'Partial Content',
		 207 => 'Multi-Status',
		 300 => 'Multiple Choices',
		 301 => 'Moved Permanently',
		 302 => 'Found',
		 303 => 'See Other',
		 304 => 'Not Modified',
		 305 => 'Use Proxy',
		 307 => 'Temporary Redirect',
		 400 => 'Bad Request',
		 401 => 'Unauthorized',
		 402 => 'Payment Required',
		 403 => 'Forbidden',
		 404 => 'Not Found',
		 405 => 'Method Not Allowed',
		 406 => 'Not Acceptable',
		 407 => 'Proxy Authentication Required',
		 408 => 'Request Timeout',
		 409 => 'Conflict',
		 410 => 'Gone',
		 411 => 'Length Required',
		 412 => 'Precondition Failed',
		 413 => 'Request Entity Too Large',
		 414 => 'Request-URI Too Long',
		 415 => 'Unsupported Media Type',
		 416 => 'Requested Range Not Satisfiable',
		 417 => 'Expectation Failed',
		 418 => 'I\'m a teapot',
		 422 => 'Unprocessable Entity',
		 423 => 'Locked',
		 424 => 'Failed Dependency',
		 500 => 'Internal Server Error',
		 501 => 'Not Implemented',
		 502 => 'Bad Gateway',
		 503 => 'Service Unavailable',
		 504 => 'Gateway Timeout',
		 505 => 'HTTP Version Not Supported',
		 507 => 'Insufficient Storage',
		 509 => 'Bandwidth Limit Exceeded'
	);
	protected static $format;
	protected static $formats = array(
		 'html' => 'text/html',
		 'xml' => 'text/xml',
		 'json' => 'application/json',
		 'txt' => 'text/plain'
	);

	public static function body($body) {
		self::$body = $body;
	}

	public static function header($name, $value) {
		self::$headers[$name] = $value;
	}

	public static function format($format) {
		self::$format = $format;
	}

	public static function getFormat() {
		return self::$format;
	}

	public static function sendHeaders() {
		if (!headers_sent()) {
			header('HTTP/1.1 ' . self::$status_code . ' ' . self::$status_codes[self::$status_code]);
			if (array_key_exists(self::$format, self::$formats)) {
				self::header('Content-Type', self::$formats[self::$format]);
			} else {
				self::header('Content-Type', self::$format);
			}
			foreach (self::$headers as $key => $value) {
				header($key . ': ' . $value);
			}
		}
	}

	public static function send() {
		self::sendHeaders();
		echo self::$body;
	}

	public static function redirect($url, $protocol = 'http') {
		$url = Url::to($url, $protocol);
		self::$status_code = 302;
		self::header('Location', $url);
		self::sendHeaders();
		exit();
	}

}

?>
