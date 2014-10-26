<?php

namespace Neptune\Command;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CacheFlushCommand extends Command
{
    protected $name = 'cache:flush';
    protected $description = 'Empty the cache';

    protected function configure()
    {
        parent::configure();
        $this->addOption(
                 'cache',
                 'c',
                 InputOption::VALUE_OPTIONAL,
                 'The name of the cache service',
                 'cache'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $input->getOption('cache');
        if (!$service) {
            $service = 'cache';
        }
        $cache = $this->neptune[$service];

        if (!$cache instanceof Cache) {
            throw new \Exception(sprintf('Service "%s" must be an instance of Doctrine\Common\Cache\Cache', $service));
        }

        if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("Using cache driver <info>" . get_class($cache) . "</info>");
        }
        $cache->flushAll();
        $output->writeln(sprintf('Emptied cache service <info>"%s"</info>', $service));
    }

}
