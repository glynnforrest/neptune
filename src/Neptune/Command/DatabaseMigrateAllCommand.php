<?php

namespace Neptune\Command;

use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;
use Neptune\Database\Migration\Exception\MigrationNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;

/**
 * DatabaseMigrateAllCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseMigrateAllCommand extends DatabaseMigrateListCommand
{

    protected $name = 'database:migrate:all';
    protected $description = 'Migrate all modules to the latest database version';

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Log each migration as successful even if it fails.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($output));

        if ($input->getOption('force')) {
            $runner->ignoreExceptions();
        }

        foreach ($this->neptune->getModules() as $module) {
            try {
                $runner->migrateLatest($module);
                $output->writeln(sprintf('Module <info>%s</info> up to date', $module->getName()));
            } catch (MigrationNotFoundException $e) {
                //this means the module doesn't have a migrations
                //folder, so skip it
                continue;
            }
        }
    }

}
