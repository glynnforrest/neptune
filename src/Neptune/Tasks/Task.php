<?php

namespace Neptune\Tasks;

use \ReflectionMethod;
use Neptune\Console\Console;
use Neptune\Core\Config;

/**
 * Task
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class Task {

	protected $console;

	public function __construct() {
		$this->console = Console::getInstance();
	}

	public function run($args = array()) {
		//if no args are supplied, offer methods available for the
		//current task.
		if($empty($args)) {
		}
	}

	protected function getTaskMethods() {
		$methods = get_class_methods($this);
		foreach ($methods as $k => $method) {
			if(substr($method, 0, 1) === '_') {
				unset($methods[$k]);
				continue;
			}
			$r = new ReflectionMethod($this, $method);
			if(!$r->isPublic()) {
				unset($methods[$k]);
			}
		}
		sort($methods);
		return $methods;
	}

	public function help() {
		//print out all methods and their docblocks
	}

    protected function getAppDirectory() {
        $c = Config::load('neptune');
        return $c->getRequired('dir.app') . '/' . $c->getRequired('namespace') . '/';
    }

}
