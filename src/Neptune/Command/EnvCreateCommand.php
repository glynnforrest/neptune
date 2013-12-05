<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Exceptions\FileException;
use Neptune\Core\Config;

use Symfony\Component\Console\Input\InputArgument;

use \DirectoryIterator;

/**
 * EnvCreateCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvCreateCommand extends Command {

	protected $name = 'env:create';
	protected $description = 'Create a new application environment';

	protected function configure() {
		$this->setName($this->name)
			 ->setDescription($this->description)
			 ->addArgument(
				 'name',
				 InputArgument::OPTIONAL,
				 'The name of the new environment.'
			 );
	}

	public function go() {
		$name = $this->input->getArgument('name');
		$dialog = $this->getHelper('dialog');
		if(!$name) {
			$name = $dialog->ask($this->output, 'Name of environment: ');
		}
		try {
			$this->newEnv($name);
		} catch (\Exception $e){
			$overwrite = $dialog->askConfirmation($this->output, "<info>$name</info> exists. Overwrite? ", false);
			if($overwrite) {
				$this->newEnv($name, true);
			}
		}
	}

	protected function newEnv($name, $overwrite = false) {
		$name = strtolower($name);
		$config = $this->getRootDirectory() . 'config/env/' . $name . '.php';
		$env = $this->getRootDirectory() . 'app/env/' . $name . '.php';
		if(file_exists($name) || file_exists($config)) {
			if(!$overwrite) {
				throw new FileException("Environment $name already exists");
			}
		}
		touch($env);
		$this->output->writeln("Created <info>$env</info>");
		$c = Config::create($name, $config);
		$c->set('root_url', '');
		$c->save();
		$this->output->writeln("Created <info>$config</info>");
	}

}
