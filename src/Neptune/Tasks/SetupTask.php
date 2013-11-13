<?php

namespace Neptune\Tasks;

use Neptune\Core\Config;
use Neptune\Tasks\Task;

/**
 * SetupTask
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SetupTask extends Task {

	//directories created by the structure function
	protected $dirs = array(
		'app' => 0775,
		'config' => 0777,
		'public' => 0775,
		'storage/logs' => 0777
	);

	//directories created by the scaffold function
	protected $scaffold_dirs = array(
		'app/:namespace/Controller' => 0775,
		'app/:namespace/Model' => 0775,
		'app/:namespace/View' => 0775,
		'app/:namespace/Thing' => 0775,
	);

	public function run($args = array()) {
		$this->structure();
		$t = new ConfigTask();
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
		$root = $this->getRootDirectory();
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
	 * Setup the dir array in neptune.php. This is useful when
	 * transferring a codebase to different directories and machines.
	 */
	public function dirs() {
	//The neptune command runner sets dir.*, but for the life of the
	//command only. Calling save on the neptune config instance makes
	//these settings permanent.
		$this->console->write('Setting dir.root to ' . $this->config->get('dir.root'));
		$this->console->write('Setting dir.neptune to ' . $this->config->get('dir.neptune'));
		$this->console->write('Setting dir.app to ' . $this->config->get('dir.app'));
		$this->config->save();
		$this->console->write('Saved config/neptune.php');
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
