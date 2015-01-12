<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * ConfigGetCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigGetCommand extends Command
{
    protected $name = 'config:get';
    protected $description = 'Get a configuration setting';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'key',
                 InputArgument::OPTIONAL,
                 'The configuration key.'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->hasArgument('key') ? $input->getArgument('key') : '';
        $value = $this->neptune['config']->getRequired($key);
        $output->writeln(var_export($value));
    }
}
