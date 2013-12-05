<?php

namespace Neptune\Command;

use Neptune\Command\EnvListCommand;

use Symfony\Component\Console\Input\InputArgument;

use \DirectoryIterator;

/**
 * EnvSwitchCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvSwitchCommand extends EnvListCommand {

	protected $name = 'env:switch';
	protected $description = 'Switch to a different application environment';

	protected function configure() {
		$this->setName($this->name)
			 ->setDescription($this->description)
			 ->addArgument(
				 'name',
				 InputArgument::OPTIONAL,
				 'The name of the environment to switch to.'
			 );
	}

	public function go() {
		$name = $this->input->getArgument('name');
		$dialog = $this->getHelper('dialog');
		$envs = $this->getEnvs();
		if(!$name) {
			$index = $dialog->select($this->output, 'Select environment:', $this->getEnvsHighlightCurrent());
			$name = $envs[$index];
		}
		if(!in_array($name, $envs)) {
			$this->output->writeln("<error>Environment not found: $name</error>");
			return false;
		}
		$this->config->set('env', $name);
		$this->config->save();
		$this->output->writeln("Switched to <info>$name</info> environment.");
		return true;
	}

}
