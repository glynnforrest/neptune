<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Neptune\Console\Shell;

/**
 * ShellCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ShellCommand extends Command {

	protected $name = 'shell';
	protected $description = 'Run commands in a shell';

	public function go() {
		$app = $this->getApplication();
		$shell = new Shell($app);
		$shell->run();
		return 0;
	}

}
