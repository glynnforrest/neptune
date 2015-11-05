<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Neptune\Core\Neptune;

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

    protected $name;
    protected $description;
    protected $help;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
        $this->config = $neptune['config'];
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->name)
            ->setDescription($this->description)
            ->setHelp($this->help);
    }

    public function getRootDirectory()
    {
        return $this->neptune->getRootDirectory();
    }

    /**
     * Get a loaded module from the 'module' argument or by prompting
     * for it.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function getModuleArgument(InputInterface $input, OutputInterface $output)
    {
        $module_name = $input->getArgument('module');
        if (!$module_name) {
            $dialog = $this->getHelper('dialog');
            $modules = array_keys($this->neptune->getModules());
            $index = $dialog->select($output, 'Module:', $modules);
            $module_name = $modules[$index];
        }

        return $this->neptune->getModule($module_name);
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
}
