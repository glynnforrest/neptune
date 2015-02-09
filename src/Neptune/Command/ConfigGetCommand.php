<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

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
             )
             ->addOption(
                 'php',
                 '',
                 InputOption::VALUE_NONE,
                 'Output php instead of yaml'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->hasArgument('key') ? $input->getArgument('key') : '';
        $value = $this->neptune['config']->getRequired($key);

        if ($input->getOption('php')) {
            $output->write(var_export($value).PHP_EOL);
            return;
        }

        $output->write(Yaml::dump($value, 100, 2));
    }
}
