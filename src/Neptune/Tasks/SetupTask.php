<?php

namespace Neptune\Tasks;

use Neptune\Core\Config;
use Neptune\Helpers\String;
use Neptune\Tasks\Task;

/**
 * SetupTask
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SetupTask extends Task {

	//directories created by the structure function
	protected $dirs = array(
		'public' => 0775,
		'config' => 0777,
		'storage/logs' => 0777,
		'app' => 0775
	);

	//directories created by the scaffold function
	protected $scaffold_dirs = array(
		'app/:namespace/Controller' => 0775,
		'app/:namespace/Model' => 0775,
		'app/:namespace/View' => 0775,
	);

	protected function getRootDir() {
		$root = Config::load('neptune')->getRequired('dir.root');
		//make sure root has a trailing slash
		if(substr($root, -1) !== '/') {
			$root .= '/';
		}
		return $root;
	}

    /**
     * Check if config, public and storage directories have been
     * created.
     */
    public function _directoriesCreated() {
        foreach ($this->dirs as $dir => $perms) {
            if(!file_exists($dir)) {
                $this->console->error('Not found: ' . $dir);
                return false;
            }
        }
        return true;
    }

    protected function neptuneConfigSetup() {
        $t = new ConfigTask();
        return $t->_neptuneConfigSetup();
    }

	public function run($args = array()) {
		$this->structure();
        $t = new ConfigTask;
        $t->run();
		$this->scaffold();
		$this->versionControl();
	}

	/**
	 * Set up the directory structure of your application.
	 */
	public function structure() {
		$this->console->write('Creating directory structure...');
		$this->createDirs($this->dirs);
	}

	protected function createDirs(array $dirs, $namespace = false) {
		if($namespace) {
			$namespace = Config::load('neptune')->getRequired('namespace');
		} else {
			$namespace = ':namespace';
		}
		$root = $this->getRootDir();
		foreach($dirs as $dir => $perms) {
			$dir = str_replace(':namespace', $namespace, $dir);
			if(!file_exists($root . $dir)) {
				mkdir($root . $dir, $perms, true);
				//use events instead
				$this->console->write('Creating directory ' . $root . $dir);
			}
		}
	}

	public function scaffold() {
		if(!$this->neptuneConfigSetup()) {
			$this->console->error('No configuration found. Please run `./neptune config`.');
			return false;
		}
		$this->createDirs($this->scaffold_dirs, true);
		//prompt for creation of index file
		//prompt for creation of simple controller and view
	}

	public function permissions() {
		foreach($this->dirs as $dir => $perms) {
			if(file_exists($root . $dir)) {
				chmod($root . $dir, $perms);
				$this->console->write("Setting permissions to $perms: $root $dir");
			}
		}
	}

	/**
	 *
	 **/
	public function versionControl() {
	}


	/**
	 *
	 **/
	public function help() {
	}

}
