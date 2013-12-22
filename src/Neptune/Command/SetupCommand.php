<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\Core\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

use Stringy\StaticStringy as S;

/**
 * SetupCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SetupCommand extends Command {

	protected $name = 'setup';
	protected $description = 'Setup a new application';
	protected $dirs = array(
		'app',
		'config',
		'env',
		'public',
		'storage/logs'
	);

	protected function configure() {
		$this->setName($this->name)
			 ->setDescription($this->description)
			 ->addArgument(
				 'name',
				 InputArgument::OPTIONAL,
				 'The name of the first application module to create.'
			 )
			 ->addArgument(
				 'namespace',
				 InputArgument::OPTIONAL,
				 'The class namespace of the first application module to create.'
			 )
			 ->addArgument(
				 'env',
				 InputArgument::OPTIONAL,
				 'The name of the first environment to create.'
			 );
	}

	public function go(Console $console) {
		$console->writeln('<info>Creating a new neptune application</info>');
		//create dirs
		$this->createDirectories($this->getRootDirectory());
		//create neptune.php
		$this->populateNeptuneConfig();
		//create public/index.php
		//create public/.htaccess
		//add to .gitignore?
		//create module task
		$this->createFirstModule();
		//create env task
	}

	public function isEnabled() {
		return true;
	}

	protected function createFirstModule() {
		$this->console->writeln('<info>Creating the first module of this application</info>');
		$name = $this->input->getArgument('name');
		if(!$name) {
			$default = S::slugify(basename($this->getRootDirectory()));
			$name = $this->console->ask('Enter the name of the first application module: ', $default);
		}
		$name = strtolower($name);
		$namespace = $this->input->getArgument('namespace');
		if(!$namespace) {
			$namespace = $this->console->ask('Enter the namespace for this module: ', S::upperCamelize($name));
		}
		$command = $this->getApplication()->find('create:module');
		$arguments = array(
			'name' => $name,
			'namespace' => $namespace,
		);
		$input = new ArrayInput($arguments);
		$returnCode = $command->run($input, $this->output);
	}

	protected function populateNeptuneConfig() {
		//a list of neptune config values to set as a starter. Flatten
		//the config so the output messages are more meaningful.
		$values = array(
			'log.type.fatal' => true,
			'log.type.warn' => true,
			'log.type.debug' => true,
			'log.file' => 'storage/logs/logs.log',
			'cache.default.host' => 'localhost',
			'cache.default.driver' => 'debug',
			'cache.default.prefix' => '-',
			'assets.url' => 'assets/',
			'security' => '',
			'env' => ''
		);
		foreach ($values as $key => $value) {
			$this->config->set($key, $value);
			if(is_string($value) && !empty($value)) {
				$this->console->veryVerbose(sprintf(
					"Neptune config: Setting <info>%s</info> to <info>%s</info>",
					$key, $value));
			} else {
				$this->console->veryVerbose(sprintf(
					"Neptune config: Setting <info>%s</info>",
					$key));
			}
		}
		$this->config->save();
		return $this->config;
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
