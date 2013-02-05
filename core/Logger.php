<?php

namespace neptune\core;

use neptune\core\Config;
use neptune\http\Request;
use \SplFileObject;

/**
 * Logger
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Logger {

	protected static $instance;
	protected static $enabled;
	protected $logs = array();
	protected $temp = false;
	protected $format = ':date :time [:type] :message';

	protected function __construct() {
		if(Config::get('log.format')) {
			$this->format = Config::get('log.format');
		}
	}

	public function __destruct() {
		$this->save();
	}

	public static function enable() {
		self::$enabled = true;
	}

	public static function disable() {
		self::$enabled = false;
	}

	public static function __callStatic($name, $args) {
		return self::getInstance()->createLog($name, $args[0]);
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function isLogTypeEnabled($type) {
		$key = 'log.type.' . $type;
		if (Config::get($key)) {
			return true;
		}
		return false;
	}

	protected function createLog($type, $message) {
		if (!self::$enabled) {
			return false;
		}
		if ($this->isLogTypeEnabled($type)) {
			$this->logs[] = $this->parseLog($type, $message);
			return true;
		}
	}

	protected function parseLog($type, $message) {
		if(is_array($message)){
			$message = var_export($message, true);
		}
		$log = $this->format;
		$log = str_replace(':message', $message, $log);
		$log = str_replace(':date', date('d/m/y'), $log);
		$log = str_replace(':time', date('H:i:s'), $log);
		$log = str_replace(':type', $type, $log);
		$log = str_replace(':ip', Request::getInstance()->ip(), $log);
		return $log;
	}

	public static function save() {
		$me = self::getInstance();
		if (!$me->temp && !empty($me->logs)) {
			$file = new SplFileObject(Config::getRequired('log.file'), 'a');
			$content = '';
			foreach ($me->logs as $log) {
				$content .= $log . PHP_EOL;
			}
			$file->fwrite($content);
			self::flush();
		}
		return;
	}

	public static function getLogs() {
		return self::getInstance()->logs;
	}

	public static function temp() {
		self::getInstance()->temp = true;
	}

	public static function saving() {
		self::getInstance()->temp = false;
	}

	public static function flush() {
		self::getInstance()->logs = array();
	}

	public static function setFormat($format) {
		self::getInstance()->format = $format;
	}

}

?>
