<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Neptune\Core\Neptune;
use Neptune\Core\NeptuneAwareInterface;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class Command extends SymfonyCommand {

    protected $neptune;
	protected $config;
	protected $input;
	protected $output;
	protected $console;

	protected $name;
	protected $description;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
        $this->config = $neptune['config'];
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description);
    }

	public function execute(InputInterface $input, OutputInterface $output) {
		$this->input = $input;
		$this->output = $output;
		//add a neptune Console helper for useful functions
		$this->console = new Console($input, $output);
		//set helper set
		$this->console->setHelperSet($this->getHelperSet());
		$this->go($this->console);
		//return status code here
	}

	/**
	 * Run the command. The following are available:
	 * $console ==> Console instance
	 * $this->input ==> InputInterface
	 * $this->output ==> OutputInterface
	 * $this->console ==> Console
	 * $this->config ==> 'neptune' Config instance
     * $this->neptune ==> Neptune
	 */
	abstract public function go(Console $console);

    public function getRootDirectory()
    {
        return $this->neptune->getRootDirectory();
    }

	/**
	 * Get the namespace of a module with no beginning slash.
	 *
	 * @param string $module the name of the module
	 */
	public function getModuleDirectory($module) {
		return $this->neptune->getModuleDirectory($module);
	}

	/**
	 * Get the namespace of a module with no beginning slash.
	 *
	 * @param string $module the name of the module
	 */
	public function getModuleNamespace($module) {
		return $this->neptune->getModuleNamespace($module);
	}

	/**
	 * Get the name of the first module in the neptune config file.
	 */
	public function getDefaultModule() {
        return $this->neptune->getDefaultModule();
	}

	/**
	 * Check if the neptune config has been setup.
	 *
	 * Return false if the neptune cli config hasn't been setup.
	 */
	public function neptuneConfigSetup() {
		$root = $this->getRootDirectory();
		if(!file_exists($root . 'config/neptune.php')) {
			return false;
		}
		//check to see if config settings required for neptune have been set
		return true;
	}

	/**
	 * Check if app, config, public and storage directories have been
	 * created.
	 */
	public function directoriesCreated() {
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
