<?php

namespace Neptune\Command;

use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * DatabaseMigrateLatestCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseMigrateLatestCommand extends Command
{
    protected $name = 'database:migrate:latest';
    protected $description = 'Migrate to the latest version of the database schema';

    protected function configure()
    {
        parent::configure();
        $this->addArgument(
                 'module',
                 InputArgument::OPTIONAL,
                 'The module containing the migrations'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($output));
        $module = $this->getModuleArgument($input, $output);
        $runner->migrateLatest($module);
        $output->writeln(sprintf('Migrated <info>%s</info> to the latest version.', $module->getName()));
    }
}
