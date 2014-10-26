<?php

namespace Neptune\Command;

use Neptune\Exceptions\ConfigFileException;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    protected function getModulesToProcess(InputInterface $input)
    {
        $args = $input->getArgument('modules');
        if (!$args) {
            return $this->neptune->getModules();
        }
        $modules = [];
        foreach ($args as $name) {
            $modules[$name] = $this->neptune->getModule($name);
        }

        return $modules;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modules = $this->getModulesToProcess($input);

        foreach ($modules as $name => $module) {
            try {
                $config = $this->neptune['config.manager']->loadModule($name);
            } catch (ConfigFileException $e) {
                $output->writeln("Skipping <info>$name</info>");
                continue;
            }

            if (!$command = $config->get('assets.install_cmd', false)) {
                $output->writeln("Skipping <info>$name</info>");
                continue;
            }

            $output->writeln("Installing <info>$name</info>");
            $dir = $module->getDirectory();

            passthru("cd $dir && $command");
        }
        $output->writeln('');
        $output->writeln(sprintf('Installed assets'));
    }

}
