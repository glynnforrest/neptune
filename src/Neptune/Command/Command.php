<?php

namespace Neptune\Command;

use \ReflectionMethod;
use Neptune\Console\Console;
use Neptune\Core\Config;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class Command extends SymfonyCommand {

	protected $config;
	protected $input;
	protected $output;
	protected $console;

	protected $name;
	protected $description;

	/**
	 * Create a new Command instance. Neptune config must be loaded.
	 */
	public function __construct(Config $config) {
		parent::__construct();
		$this->config = $config;
	}

	protected function configure() {
		$this->setName($this->name)
             ->setDescription($this->description);
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$this->input = $input;
		$this->output = $output;
		//add a neptune Console helper for useful functions
		/* $this->console = new Console($input, $output); */
		$this->go();
		//return status code here
	}

	/**
	 * Run the command. The following are available:
	 * $this->input ==> InputInterface
	 * $this->output ==> OutputInterface
	 * $this->console ==> instance of Console helper
	 * $this->config ==> 'neptune' Config instance
	 */
	abstract public function go();

	protected function getAppDirectory() {
		return $this->config->getRequired('dir.app') .
			'/' . $this->config->getRequired('namespace') . '/';
	}

	protected function getRootDirectory() {
		$root = $this->config->getRequired('dir.root');
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
		//check to see if config settings required for neptune have been set
		return $this->config->get('namespace', false);
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
