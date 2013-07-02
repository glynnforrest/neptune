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



	//Helper functions for Tasks
	protected function getAppDirectory() {
		$c = Config::load('neptune');
		return $c->getRequired('dir.app') . '/' . $c->getRequired('namespace') . '/';
	}

	protected function getRootDirectory() {
		$root = Config::load('neptune')->getRequired('dir.root');
		//make sure root has a trailing slash
		if(substr($root, -1) !== '/') {
			$root .= '/';
		}
		return $root;
	}

	/**
	 * Check if any configuration profiles have been setup.
	 *
	 * An array of profiles wil be returned if any exist.
	 * Return false if the neptune cli config hasn't been setup.
	 */
	protected function neptuneConfigSetup() {
		$root = $this->getRootDirectory();
		if(!file_exists($root . 'config/neptune.php')) {
			return false;
		}
		$c = Config::load();
		//check to see if config settings required for neptune have been set
		return $c->get('namespace', false);
	}

    /**
     * Check if app, config, public and storage directories have been
     * created.
     */
    protected function directoriesCreated() {
		$dirs = array('app', 'config', 'public', 'storage/logs');
        foreach ($dirs as $dir) {
            if(!file_exists($dir)) {
                $this->console->error('Not found: ' . $dir);
                return false;
            }
        }
        return true;
    }

}
