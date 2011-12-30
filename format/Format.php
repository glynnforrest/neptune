<?php
namespace neptune\format;

/**
 * Format
 * @author Glynn Forrest me@glynnforrest.com
 **/
abstract class Format {

	protected $content = array();

	protected function __construct() {
	
	}

	public static function create($content = null, $key = null) {
		$class = get_called_class();
		$me = new $class();
		if($content) {
			$me->add($content, $key);
		}
		return $me;
	}

	public function add($content, $key = null) {
		if(empty($content)) {
			return false;
		}
		if($key) {
			$this->content[$key] = $content;
		} else {
			$this->content[] = $content;
		}
	}

	public static function parse($content) {
		$class = get_called_class();
		$me = new $class();
		$me->read($content);
		return $me;
	}

	abstract public function read($content);

	abstract public function encode();

	public function decode(){
		return $this->content;
	}

	public function clear() {
		$this->content = array();
	}
}
?>
