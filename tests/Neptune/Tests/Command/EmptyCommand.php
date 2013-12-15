<?php

namespace Neptune\Tests\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;

/**
 * EmptyCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EmptyCommand extends Command {

	protected $name = 'empty';

	public function go(Console $c) {
	}

}
