<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * EnvRemoveCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvRemoveCommand extends EnvListCommand
{
    protected $name = 'env:remove';
    protected $description = 'Remove an application environment';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'name',
                 InputArgument::OPTIONAL,
                 'The name of the new environment.'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $dialog = $this->getHelper('dialog');

        if (!$name) {
            $index = $dialog->select($output, 'Remove environment:', $this->getEnvsHighlightCurrent());
            $name = $this->getEnvs()[$index];
        }

        $prompt = "Are you sure you want to remove the environment <info>$name</info>? ";
        if ($dialog->askConfirmation($output, $prompt, false)) {
            $this->removeEnv($name, $output);
        }
    }

    protected function removeEnv($name, OutputInterface $output)
    {
        $config = $this->getRootDirectory() . 'config/env/' . $name . '.php';
        if (!file_exists($config)) {
            $output->writeln("<error>$config not found</error>");

            return false;
        }
        if (unlink($config)) {
            $output->writeln("Deleted <info>$config</info>");

            return true;
        }
        $output->writeln("<error>Unable to delete <info>$config</info>");

        return false;
    }

}
