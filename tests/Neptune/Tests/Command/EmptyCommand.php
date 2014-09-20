<?php

namespace Neptune\Tests\Command;

use Neptune\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EmptyCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EmptyCommand extends Command
{
    protected $name = 'empty';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

}
