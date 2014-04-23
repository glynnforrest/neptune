<?php

namespace Neptune\Command;

use Neptune\Command\EnvListCommand;
use Neptune\Console\Console;

use Symfony\Component\Console\Input\InputArgument;

use \DirectoryIterator;

/**
 * EnvRemoveCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvRemoveCommand extends EnvListCommand {

	protected $name = 'env:remove';
	protected $description = 'Remove an application environment';

	protected function configure() {
		$this->setName($this->name)
			 ->setDescription($this->description)
			 ->addArgument(
				 'name',
				 InputArgument::OPTIONAL,
				 'The name of the new environment.'
			 );
	}

	public function go(Console $console) {
		$name = $this->input->getArgument('name');
		$dialog = $this->getHelper('dialog');
		if(!$name) {
			$index = $dialog->select($this->output, 'Remove environment:', $this->getEnvsHighlightCurrent());
			$name = $this->getEnvs()[$index];
		}
		$overwrite = $dialog->askConfirmation($this->output, "Are you sure you want to remove the environment <info>$name</info>? ", false);
		if($overwrite) {
			$this->removeEnv($name);
		}
	}

    protected function removeEnv($name)
    {
        $config = $this->getRootDirectory() . 'config/env/' . $name . '.php';
        if(!file_exists($config)) {
            $this->output->writeln("<error>$config not found</error>");
            return false;
        }
        if(unlink($config)) {
            $this->output->writeln("Deleted <info>$config</info>");
            return true;
        }
        $this->output->writeln("<error>Unable to delete <info>$config</info>");
        return false;
    }

}
