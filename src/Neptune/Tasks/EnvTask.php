<?php

namespace Neptune\Tasks;

use Neptune\Tasks\Task;

use \DirectoryIterator;

/**
 * EnvTask
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvTask extends Task {

	public function show() {
		foreach ($this->getEnvs() as $env) {
			$this->console->write($env);
		}
	}

	protected function getEnvs() {
		$envs = array();
		$env_dir = $this->getRootDirectory() . 'config/env';
		$i = new DirectoryIterator($env_dir);
		foreach ($i as $file) {
			if(!$file->isDot() && !$file->isDir()) {
				//remove .php
				$file = substr($file, 0, strrpos($file, '.' ));
				$envs[] = $file;
			}
		}
		sort($envs);
		return $envs;
	}

	public function change($name = null) {
		if(!$name) {
			$name = $this->console->readOptions($this->getEnvs());
		}
		$config_file = $this->getRootDirectory(). 'config/env/' . $name . '.php';
		if(!file_exists($config_file)) {
			$this->console->error("Configuration file not found: $config_file");
			return false;
		}
		$this->config->set('env', $name);
		$this->config->save();
		$this->console->write("Switched to $name environment.");
		return true;
	}


}
