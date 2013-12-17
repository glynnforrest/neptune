<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\Core\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Stringy\StaticStringy as S;

/**
 * CreateModuleCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateModuleCommand extends Command {

	protected $name = 'create:module';
	protected $description = 'Create a new module';
	protected $dirs = array(
		'Controller',
		'Model',
		'Thing',
		'Command',
		'views',
		'assets'
	);
	protected $module_directory;

	protected function configure() {
		$this->setName($this->name)
			 ->setDescription($this->description)
			 ->addArgument(
				 'name',
				 InputArgument::OPTIONAL,
				 'The name of the new module.'
			 )
			 ->addArgument(
				 'namespace',
				 InputArgument::OPTIONAL,
				 'The class namespace of the new module.'
			 );
	}

	public function go(Console $console) {
		$name = $this->input->getArgument('name');
		if(!$name) {
			$name = $console->ask('Name of module: ');
		}
		$name = strtolower($name);
		$namespace = $this->input->getArgument('namespace');
		if(!$namespace) {
			$namespace = $console->ask('Namespace for this module: ', S::upperCamelize($name));
		}
		//create dirs
		$this->createDirectories($this->createModuleDirectory($namespace));
		//create config.php
		$config = Config::create('new-module', $this->createModuleDirectory($namespace) . 'config.php');
		$config->set('namespace', $namespace);
		$config->set('assets.dir', 'assets/');
		$config->save();
		//add module to neptune modules config
		$this->config->set('modules.' . $name, $this->createModuleDirectory($namespace, false));
		$this->config->save();
		//create controller
		//$this->runCommand
		//create view
		//create services.php
		//create routes.php
		//create command
		//update composer.json autoload
	}

	public function isEnabled() {
		return true;
	}

	protected function createModuleDirectory($namespace, $absolute = true) {
		if(substr($namespace, -1) !== '/') {
			$namespace .= '/';
		}
		if($absolute) {
			return $this->getRootDirectory() . 'app/' . $namespace;
		}
		return 'app/' . $namespace;
	}

	protected function createDirectories($root) {
		foreach($this->dirs as $dir) {
			$dir = $root . $dir;
			if(!file_exists($dir)) {
				mkdir($dir, 0755, true);
				$this->console->verbose(sprintf("Creating directory <info>%s</info>", $dir));
			}
		}
	}

}
