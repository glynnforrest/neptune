<?php

namespace Neptune\Tasks;

use Neptune\Helpers\String;

/**
 * TaskRunner
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TaskRunner {

	protected static $instance;

	protected function __construct() {

	}

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Parses a command string and returns an array containing the
	 * task name, the method to run, arguments and flags..
	 */
	public function parse($string) {
		$pieces = explode(' ', $string);
		//collect flags and strip them out so they won't be args
		$flags = array();
		foreach ($pieces as $k => $v) {
			if(substr($v, 0, 1) === '-') {
				$flags[] = $v;
				unset($pieces[$k]);
			}
		}
		$this->processGlobalFlags($flags);
		//collect args
		$args = array();
		for ($i = 1; $i < count($pieces); $i++) {
			$args[] = $pieces[$i];
		}
		//get the task name and method to run from the first argument
		$task_pieces = explode(':', $pieces[0]);
		$return = array();
		$return['task'] = String::CamelCase($task_pieces[0]) . 'Task';
		if(isset($task_pieces[1])) {
			$return['method'] = String::CamelCase($task_pieces[1], false);
		} else {
			$return['method'] = 'run';
		}
		$return['args'] = $args;
		$return['flags'] = $flags;
		return $return;
	}

	public function processGlobalFlags(array $flags) {

	}


}
