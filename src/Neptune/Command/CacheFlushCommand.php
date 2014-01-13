<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\Cache\CacheFactory;

class CacheFlushCommand extends Command {

	protected $name = 'cache:flush';
	protected $description = 'Empty the cache';

	public function go(Console $console) {
		$factory = new CacheFactory($this->config);
		$driver = $factory->getDriver();
		$console->verbose("Using cache driver <info>" . get_class($driver) . "</info>");
		$driver->flush();
		$console->write('Emptied the cache.');
	}

}
