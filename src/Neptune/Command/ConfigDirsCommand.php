<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\Core\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * ConfigDirsCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigDirsCommand extends Command {

	protected $name = 'config:dirs';
	protected $description = 'Setup the dir array in neptune.php for the current directory.';

	public function go(Console $console) {
		//The neptune command runner sets dir.*, but for the life of the
		//command only. Calling save on the neptune config instance makes
		//these settings permanent.
		$console->verbose(sprintf('Setting <info>dir.root</info> to <info>%s</info>', $this->config->get('dir.root')));
		$console->verbose(sprintf('Setting <info>dir.neptune</info> to <info>%s', $this->config->get('dir.neptune')));
		$this->config->save();
		$this->console->writeln('Saved config/neptune.php');
	}

	public function isEnabled() {
		return $this->neptuneConfigSetup();
	}

}
