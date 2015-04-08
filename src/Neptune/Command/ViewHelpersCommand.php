<?php

namespace Neptune\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Neptune\Helper\ReflectionHelper;

/**
 * ViewHelpersCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ViewHelpersCommand extends Command
{
    protected $name = 'view:helpers';
    protected $description = 'Show available view helpers';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $view_creator = $this->neptune['view'];
        $reflection_helper = new ReflectionHelper();

        $output->writeln('<info>Available view helpers</info>');

        foreach ($view_creator->getHelpers() as $name => $helper) {
            $output->writeln($name.$reflection_helper->displayFunctionParameters($helper));
        }
    }
}
