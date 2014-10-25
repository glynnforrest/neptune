<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * EnvSwitchCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvSwitchCommand extends EnvListCommand
{
    protected $name = 'env:switch';
    protected $description = 'Switch to a different application environment';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'name',
                 InputArgument::OPTIONAL,
                 'The name of the environment to switch to.'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $envs = $this->getEnvs();

        if (!$name) {
            $dialog = $this->getHelper('dialog');
            $index = $dialog->select($output, 'Select environment:', $this->getEnvsHighlightCurrent());
            $name = $envs[$index];
        }

        if (!in_array($name, $envs)) {
            $output->writeln("<error>Environment not found: $name</error>");

            return false;
        }

        $this->config->set('env', $name);
        $this->config->save();
        $output->writeln("Switched to <info>$name</info> environment.");
    }

}
