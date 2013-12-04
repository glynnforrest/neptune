<?php

namespace Neptune\Console;

use Neptune\Core\Config;
use Neptune\Exceptions\ClassNotFoundException;
use Neptune\Console\Shell;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Stringy\StaticStringy as S;

/**
 * Application
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Application extends SymfonyApplication {

	public function __construct() {
		parent::__construct('Neptune', '0.2');
	}

	/**
	 * Runs the current application.
	 *
	 * @param InputInterface  $input  An Input instance
	 * @param OutputInterface $output An Output instance
	 *
	 * @return integer 0 if everything went fine, or an error code
	 */
	public function doRun(InputInterface $input, OutputInterface $output) {
		/* if (!$this->commandsRegistered) { */
		/* 	$this->registerCommands(); */
		/* } */
		return parent::doRun($input, $output);
	}

}
