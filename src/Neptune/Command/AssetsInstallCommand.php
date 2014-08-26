<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\Exceptions\ConfigFileException;

use Symfony\Component\Console\Input\InputArgument;

/**
 * AssetsInstallCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsInstallCommand extends Command
{
    protected $name = 'assets:install';
    protected $description = 'Install module assets';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'modules',
                 InputArgument::IS_ARRAY,
                 'A list of modules to install instead of all.'
             );
    }

    protected function getModulesToProcess()
    {
        $args = $this->input->getArgument('modules');
        if (!$args) {
            return $this->neptune->getModules();
        }
        $modules = [];
        foreach ($args as $name) {
            $modules[$name] = $this->neptune->getModule($name);
        }

        return $modules;
    }

    public function go(Console $console)
    {
        $modules = $this->getModulesToProcess();

        foreach ($modules as $name => $module) {
            try {
                $config = $this->neptune['config.manager']->loadModule($name);
            } catch (ConfigFileException $e) {
                $console->writeln("Skipping <info>$name</info>");
                continue;
            }

            $command = $config->get('assets.install_cmd', false);
            if (!$command) {
                $console->writeln("Skipping <info>$name</info>");
                continue;
            }

            $console->writeln("Installing <info>$name</info>");
            $dir = $module->getDirectory();

            passthru("cd $dir && $command");
        }
        $console->writeln('');
        $console->writeln(sprintf('Installed assets'));
    }

}
