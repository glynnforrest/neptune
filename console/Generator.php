<?php

namespace neptune\console;

use neptune\console\Console;
use neptune\core\Neptune;

/**
 * Generator
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Generator {

	protected static $instance;
	protected $dirs = array(
		'application/controller',
		'application/model',
		'application/view',
		'lib',
		'public',
		'scripts'
	);
	protected $writable_dirs = array(
		'storage/logs',
		'config'
	);
	protected $blank_files = array(
		'storage/logs/.gitignore'
	);
	protected $skeleton_files = array(
		'public/index.php' => 'lib/neptune/skeletons/index.php',
		'application/controller/HomeController.php' => '/srv/http/neptune/skeletons/HomeController.php'
	);

	protected function __construct() {
		$this->console = console::getInstance();
	}

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function populateAppDirectory($root) {
		if(!is_dir($root)) {
			echo $root . ' is not a directory.';
			return false;
		}
		if(substr($root, -1) != '/') {
			$root .= '/';
		}
		//create directory structure
		foreach($this->dirs as $dir) {
			if(!file_exists($root . $dir)) {
				mkdir($root . $dir, 0755, true);
				$this->console->write('Creating directory ' . $root.$dir);
			}
		}
		//create writable directories
		//todo::move the chmod stuff to an install script that can be run when put on a server
		foreach($this->writable_dirs as $dir) {
			if(!file_exists($root . $dir)) {
				mkdir($root . $dir, 0777, true);
				chmod($root . $dir, 0777);
				$this->console->write('Creating directory ' . $root.$dir);
				$this->console->write('Setting permissions to 777: ' . $root.$dir);
			}
		}
		//initialise git
		chdir($root);
		if(!file_exists('.git')) {
			$this->console->write(exec('git init'));
		}
		//checkout neptune as a submodule
		if(!file_exists('lib/neptune')) {
			$this->console->write('Cloning neptune...');
			$this->console->write(exec('git submodule add https://github.com/glynnforrest/neptune.git lib/neptune'));
		}
		//create blank files
		foreach ($this->blank_files as $file) {
			if(!file_exists($file)) {
				try {
					touch($file);
					$this->console->write('Creating '. $file);
				} catch (\Exception $e){
					$this->console->error("Unable to create $file");
				}
			}
		}
		//copy skeleton files
		foreach ($this->skeleton_files as $target => $source) {
			if(!file_exists($target)) {
				try {
					copy($source, $target);
					$file = @file_get_contents($target);
					if($file) {
						$file = str_replace('{namespace}', Neptune::get('root_namespace', 'application'), $file);
					}
					file_put_contents($target, $file);
					$this->console->write("Copying skeleton file $source to $target");
				} catch (\Exception $e){
					$this->console->error("Unable to copy skeleton file $source to $target");
				}
			}
		}

	}
}
?>
