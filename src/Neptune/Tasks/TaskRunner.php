<?php

namespace Neptune\Tasks;

use Neptune\Core\Config;
use Neptune\Console\Console;
use Neptune\Exceptions\ClassNotFoundException;
use Neptune\Helpers\String;

/**
 * TaskRunner
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TaskRunner {

	protected static $instance;
	protected $flags = array();
	protected $flag_aliases = array(
		'v' => 'verbose',
		'i' => 'interactive',
		'V' => 'version'
	);

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
		//if the command string is empty, default to something useful
		if(trim($string) === '') {
			$string = 'setup:help';
		}
		$pieces = explode(' ', $string);
		//collect flags and strip them out so they won't be args
		$flags = array();
		foreach ($pieces as $k => $v) {
			if(substr($v, 0, 1) === '-') {
				$flags[] = $v;
				unset($pieces[$k]);
			}
		}
		//collect args
		$args = array();
		for ($i = 1; $i < count($pieces); $i++) {
			$args[] = $pieces[$i];
		}
		//get the task name and method to run from the first argument
		$task_pieces = explode(':', $pieces[0]);
		$return = array();
		$return['task'] = $task_pieces[0];
		if(isset($task_pieces[1])) {
			$return['method'] = String::CamelCase($task_pieces[1], false);
		} else {
			$return['method'] = 'run';
		}
		$return['args'] = $args;
		$return['flags'] = $flags;
		return $return;
	}

	public function run($command) {
		$pieces = $this->parse($command);
		try {
			$class = $this->getTaskClass($pieces['task']);
			$task = new $class();
			$this->addFlags($pieces['flags']);
			return call_user_func_array(array($task, $pieces['method']), $pieces['args']);
		} catch (\Exception $e){
			//this should probably be something else so we can track
			//errors when run from a webpage
			Console::getInstance()->error($e->getMessage());
		}
	}


	/**
	 * Get the full class name of a task.
	 * This function will attempt to load a task in the following order:
	 * - Application\Tasks\<Task>
	 * - Neptune\Tasks\<Task>
	 * - From every entry in the `task_paths` Config setting.
	 **/
	public function getTaskClass($task) {
		$task =	String::CamelCase($task) . 'Task';
		$candidates = array(
			Config::get('namespace') . '\\Tasks\\'. $task,
			'Neptune\\Tasks\\' . $task,
		);
		$more = array_map(function($namespace) use ($task) {
				return $namespace . '\\' . $task;
			}, Config::get('task.namespaces', array()));
		$candidates = array_merge($candidates, $more);
		foreach ($candidates as $class) {
				if(class_exists($class)) {
					return $class;
				}
		}
		throw new ClassNotFoundException("$task not found.");
	}



	/**
	 * Add flags to the current task.
	 * This will not overwrite any previously set flags.
	 **/
	public function addFlags(array $flags) {
		foreach ($flags as $flag) {
			$flag = $this->lookupFlagName($flag);
			if(!in_array($flag, $this->flags)) {
				$this->flags[] = $flag;
			}
		}
	}

	/**
	 * Get the full flag name of a given flag.
	 * This will account for leading dashes and short forms.
	 **/
	protected function lookupFlagName($flag) {
		$flag = trim($flag, '-');
		if(isset($this->flag_aliases[$flag])) {
			return $this->flag_aliases[$flag];
		}
		return strtolower($flag);
	}

	/**
	 * Set the flags for the current task.
	 * This will overwrite any previously set flags.
	 **/
	public function setFlags(array $flags) {
		$this->flags = array();
		$this->addFlags($flags);
	}

	/**
	 * Get the flags in use for the current task.
	 **/
	public function getFlags() {
		return $this->flags;
	}


}
