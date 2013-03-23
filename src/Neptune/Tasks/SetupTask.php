<?php

namespace Neptune\Tasks;

use Neptune\Core\Config;
use Neptune\Helpers\String;

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

	protected $neptune_settings = array(
		'namespace' => '',
		/* 'dir.root' => '', */
		/* 'dir.app' => '' */
	);

	protected $config_settings = array(
		'view.dir' => 'app/:namespace/View',
		'root_url' => ''
	);

	protected function getRootDir() {
		$root = Config::getRequired('neptune#dir.root');
		//make sure root has a trailing slash
		if(substr($root, -1) !== '/') {
			$root .= '/';
		}
		return $root;
	}

	public function run($args = array()) {
		$this->structure();
		$this->config();
		$this->scaffold();
		$this->versionControl();
	}

	/**
	 * Returns true if configuration files have been made for this application.
	 */
	protected function configSetup() {
		$root = $this->getRootDir();
		foreach (array('/config/neptune.php', '/config/devconfig.php') as $file) {
			if(!file_exists($root . $file)) {
				return false;
			}
		}
		//check to see if config settings required for neptune have been set
		return true;
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
			$namespace = Config::getRequired('neptune#namespace');
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

	/**
	 * Set up configuration for your application.
	 */
	public function config() {
		if($this->configSetup()) {
			if(!$this->console->readYesNo('Configuration exists. Create new?')) {
				return true;
			}
		}
		$this->console->write('Creating configuration file.');
		$pieces = explode('/', trim($this->getRootDir(), '/'));
		$this->neptune_settings['namespace'] = String::camelCase(array_pop($pieces), true);
		$file = 'config/neptune.php';
		Config::load($file);
		foreach ($this->neptune_settings as $setting => $default) {
			Config::set('neptune#' . $setting, $this->console->read("$setting:", $default));
		}
		Config::save($file);
		$file = 'config/devconfig.php';
		Config::create($file, 'devconfig');
		$namespace = Config::getRequired('neptune#namespace');
		foreach ($this->config_settings as $setting => $default) {
			$default = str_replace(':namespace', $namespace, $default);
			Config::set('devconfig#' . $setting, $this->console->read("$setting:", $default));
		}
		Config::save($file);
		$this->console->write("Saved file $file.");
	}

	public function scaffold() {
		if(!$this->configSetup()) {
			$this->console->error('No configuration found. Please create configuration first.');
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
