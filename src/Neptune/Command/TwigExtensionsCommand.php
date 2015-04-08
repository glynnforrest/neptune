<?php

namespace Neptune\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Neptune\Helper\ReflectionHelper;

/**
 * TwigExtensionsCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigExtensionsCommand extends Command
{
    protected $name = 'twig:extensions';
    protected $description = 'Show available twig extensions';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $twig = $this->neptune['twig'];

        foreach (['Globals', 'Functions'] as $type) {
            $output->writeln(sprintf('<info>%s</info>', $type));
            foreach ($twig->{'get'.$type}() as $name => $item) {
                $output->writeln($this->formatItem($type, $item));
            }
        }
    }

    protected function formatItem($type, $item)
    {
        if ($type === 'Globals') {
            return $item;
        }

        $helper = new ReflectionHelper();
        $args = $helper->getParameters($item->getCallable());

        $args = array_filter($args, function ($param) {
            if(!$class = $param->getClass()) {
                return true;
            }
            return $class->getName() !== 'Twig_Environment';
        });

        return $item->getName().$helper->displayParameters($args);
    }
}
