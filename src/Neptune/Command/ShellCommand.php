<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\Console\Shell;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * ShellCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ShellCommand extends Command {

	protected $name = 'shell';
	protected $description = 'Run commands in a shell';

	public function go(Console $console) {
		$app = $this->getApplication();
		$shell = new Shell($app);
		$shell->run();
		return 0;
	}

}
